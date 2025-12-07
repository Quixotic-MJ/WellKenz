<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function branchSetting(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $status = $request->get('status', '');
        $perPage = $request->get('per_page', 20);

        // Build the query for items with stock data
        $query = Item::with(['currentStockRecord', 'unit', 'category'])
            ->where('is_active', true);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if (!empty($category)) {
            $query->whereHas('category', function($q) use ($category) {
                $q->where('id', $category);
            });
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where(function($q) use ($status) {
                if ($status === 'low') {
                    $q->whereHas('currentStockRecord', function($stockQuery) {
                        $stockQuery->whereColumn('current_quantity', '<=', 'reorder_point');
                    })->orWhereDoesntHave('currentStockRecord');
                } elseif ($status === 'critical') {
                    $q->whereHas('currentStockRecord', function($stockQuery) {
                        $stockQuery->where('current_quantity', '<=', 0);
                    })->orWhereDoesntHave('currentStockRecord');
                } elseif ($status === 'healthy') {
                    $q->whereHas('currentStockRecord', function($stockQuery) {
                        $stockQuery->whereColumn('current_quantity', '>', 'reorder_point');
                    });
                }
            });
        }

        // Get paginated results
        $stockItems = $query->orderBy('name')->paginate($perPage)->withQueryString();

        // Get categories for filter dropdown and seasonal adjustment
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Calculate metrics for the header
        $metrics = $this->calculateStockConfigurationMetrics();

        return view('Supervisor.branch_setting', compact('stockItems', 'categories', 'metrics'));
    }

    public function updateMinimumStockLevel(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id'
            ]);

            $item = Item::findOrFail($request->item_id);

            // Only validate and update fields that are present in the request
            $updateData = [];
            $oldData = [
                'min_stock_level' => $item->min_stock_level,
                'reorder_point' => $item->reorder_point,
                'max_stock_level' => $item->max_stock_level
            ];

            // Validate and collect min_stock_level if provided
            if ($request->has('min_stock_level') && $request->min_stock_level !== null && $request->min_stock_level !== '') {
                $minStockLevel = (float) $request->min_stock_level;
                if ($minStockLevel < 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Minimum stock level must be greater than or equal to 0'
                    ], 422);
                }
                $updateData['min_stock_level'] = $minStockLevel;
            }

            // Validate and collect reorder_point if provided
            if ($request->has('reorder_point') && $request->reorder_point !== null && $request->reorder_point !== '') {
                $reorderPoint = (float) $request->reorder_point;
                if ($reorderPoint < 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Reorder point must be greater than or equal to 0'
                    ], 422);
                }
                $updateData['reorder_point'] = $reorderPoint;
            }

            // Validate and collect max_stock_level if provided
            if ($request->has('max_stock_level') && $request->max_stock_level !== null && $request->max_stock_level !== '') {
                $maxStockLevel = (float) $request->max_stock_level;
                if ($maxStockLevel < 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Maximum stock level must be greater than or equal to 0'
                    ], 422);
                }
                $updateData['max_stock_level'] = $maxStockLevel;
            }

            // Validate stock level relationships
            $newMinStock = isset($updateData['min_stock_level']) ? $updateData['min_stock_level'] : $item->min_stock_level;
            $newMaxStock = isset($updateData['max_stock_level']) ? $updateData['max_stock_level'] : $item->max_stock_level;
            $newReorderPoint = isset($updateData['reorder_point']) ? $updateData['reorder_point'] : $item->reorder_point;

            // Validate that max >= min (if both are being set or one is being changed)
            if ((isset($updateData['min_stock_level']) || isset($updateData['max_stock_level'])) && $newMaxStock < $newMinStock) {
                return response()->json([
                    'success' => false,
                    'error' => 'Maximum stock level must be greater than or equal to minimum stock level'
                ], 422);
            }

            // Validate that reorder point is reasonable (between min and max if they exist)
            if (isset($updateData['reorder_point'])) {
                if ($newReorderPoint > $newMaxStock && $newMaxStock > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Reorder point should not exceed maximum stock level'
                    ], 422);
                }
                if ($newReorderPoint < $newMinStock && $newMinStock > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Reorder point should not be less than minimum stock level'
                    ], 422);
                }
            }

            // Update only the provided fields
            if (!empty($updateData)) {
                $item->update($updateData);
                
                // Refresh the item to get updated values
                $item->refresh();
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid stock level data provided for update'
                ], 422);
            }



            // Prepare response data with current values
            $responseData = [
                'item_name' => $item->name,
                'min_stock_level' => (float) $item->min_stock_level,
                'reorder_point' => (float) $item->reorder_point,
                'max_stock_level' => (float) $item->max_stock_level
            ];

            return response()->json([
                'success' => true,
                'message' => 'Stock levels updated successfully',
                'data' => $responseData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating stock level: ' . $e->getMessage(), [
                'item_id' => $request->item_id,
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to update stock level: ' . $e->getMessage()
            ], 500);
        }
    }

    public function applySeasonalAdjustment(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'adjustment_percentage' => 'required|numeric|min:1|max:500',
                'adjustment_type' => 'required|in:min_stock_level,reorder_point,both'
            ]);

            $category = Category::findOrFail($request->category_id);

            // Get all items in the category
            $items = Item::where('category_id', $request->category_id)
                ->where('is_active', true)
                ->get();

            $updatedCount = 0;
            $errors = [];
            $processedItems = [];

            foreach ($items as $item) {
                try {
                    $oldData = [
                        'min_stock_level' => $item->min_stock_level,
                        'reorder_point' => $item->reorder_point,
                        'max_stock_level' => $item->max_stock_level
                    ];

                    $newData = [];

                    $percentage = $request->adjustment_percentage / 100;

                    if ($request->adjustment_type === 'min_stock_level' || $request->adjustment_type === 'both') {
                        $currentMinLevel = $item->min_stock_level ?? 0;
                        $newMinLevel = $currentMinLevel * (1 + $percentage);
                        $newData['min_stock_level'] = round($newMinLevel, 3);
                    }

                    if ($request->adjustment_type === 'reorder_point' || $request->adjustment_type === 'both') {
                        $currentReorderPoint = $item->reorder_point ?? 0;
                        $newReorderPoint = $currentReorderPoint * (1 + $percentage);
                        $newData['reorder_point'] = round($newReorderPoint, 3);
                    }

                    // Validate relationships if both min and max exist
                    if (isset($newData['min_stock_level']) && $item->max_stock_level > 0) {
                        if ($newData['min_stock_level'] > $item->max_stock_level) {
                            $errors[] = "Item {$item->item_code}: Minimum level ({$newData['min_stock_level']}) would exceed maximum level ({$item->max_stock_level})";
                            continue;
                        }
                    }

                    if (!empty($newData)) {
                        $item->update($newData);
                        $item->refresh();



                        $updatedCount++;
                        $processedItems[] = [
                            'item_code' => $item->item_code,
                            'item_name' => $item->name,
                            'changes' => $newData
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = "Item {$item->item_code}: " . $e->getMessage();
                    \Log::error("Seasonal adjustment error for item {$item->id}: " . $e->getMessage());
                }
            }

            $responseData = [
                'updated_count' => $updatedCount,
                'category_name' => $category->name,
                'category_id' => $category->id,
                'adjustment_percentage' => (float) $request->adjustment_percentage,
                'adjustment_type' => $request->adjustment_type,
                'total_items_in_category' => $items->count(),
                'processed_items' => $processedItems,
                'errors' => $errors
            ];

            $message = "Seasonal adjustment completed for {$category->name}. ";
            
            if ($updatedCount > 0) {
                $message .= "Updated {$updatedCount} items successfully.";
            }
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " items had errors.";
            }

            return response()->json([
                'success' => $updatedCount > 0,
                'message' => $message,
                'data' => $responseData
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error applying seasonal adjustment: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to apply seasonal adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStockConfigurationData(Request $request)
    {
        try {
            $itemId = $request->get('item_id');

            if (!$itemId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item ID is required'
                ], 400);
            }

            $item = Item::with(['currentStockRecord', 'unit', 'category'])
                ->where('is_active', true)
                ->findOrFail($itemId);

            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            $reorderPoint = $item->reorder_point ?? 0;
            $minStockLevel = $item->min_stock_level ?? 0;

            // Determine stock status
            $status = 'healthy';
            $statusClass = 'text-green-600';
            $statusText = 'Healthy';

            if ($currentStock <= 0) {
                $status = 'critical';
                $statusClass = 'text-red-600';
                $statusText = 'Out of Stock';
            } elseif ($currentStock <= ($reorderPoint * 0.5)) {
                $status = 'critical';
                $statusClass = 'text-red-600';
                $statusText = 'Critical';
            } elseif ($currentStock <= $reorderPoint) {
                $status = 'low';
                $statusClass = 'text-amber-600';
                $statusText = 'Low Buffer';
            }

            // Calculate stock percentage for progress bar
            $maxStockLevel = $item->max_stock_level ?? 0;
            $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'category_name' => $item->category->name ?? 'Unknown',
                        'unit_symbol' => $item->unit->symbol ?? ''
                    ],
                    'current_stock' => $currentStock,
                    'min_stock_level' => $minStockLevel,
                    'reorder_point' => $reorderPoint,
                    'max_stock_level' => $maxStockLevel,
                    'stock_percentage' => $percentage,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'status_text' => $statusText
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load item data'
            ], 500);
        }
    }

    /**
     * Calculate metrics for stock configuration page
     */
    private function calculateStockConfigurationMetrics()
    {
        $totalItems = Item::where('is_active', true)->count();

        $itemsWithStock = Item::with(['currentStockRecord'])
            ->where('is_active', true)
            ->get();

        $healthyStock = 0;
        $lowStock = 0;
        $criticalStock = 0;
        $outOfStock = 0;

        foreach ($itemsWithStock as $item) {
            $currentStock = $item->currentStockRecord ? (float) $item->currentStockRecord->current_quantity : 0;
            $reorderPoint = (float) ($item->reorder_point ?? 0);
            $minStockLevel = (float) ($item->min_stock_level ?? 0);

            // Determine stock status based on current stock levels
            if ($currentStock <= 0) {
                $outOfStock++;
            } elseif ($currentStock <= $minStockLevel && $minStockLevel > 0) {
                // Below minimum level = critical
                $criticalStock++;
            } elseif ($currentStock <= $reorderPoint && $reorderPoint > 0) {
                // Between min and reorder point = low stock
                $lowStock++;
            } elseif ($reorderPoint > 0 && $currentStock > $reorderPoint) {
                // Above reorder point = healthy
                $healthyStock++;
            } elseif ($reorderPoint == 0 && $minStockLevel == 0) {
                // No thresholds defined, consider as healthy if has stock
                if ($currentStock > 0) {
                    $healthyStock++;
                } else {
                    $outOfStock++;
                }
            } else {
                // Default to healthy if no specific conditions met
                $healthyStock++;
            }
        }

        return [
            'total_items' => $totalItems,
            'healthy_stock' => $healthyStock,
            'low_stock' => $lowStock,
            'critical_stock' => $criticalStock,
            'out_of_stock' => $outOfStock
        ];
    }
}
