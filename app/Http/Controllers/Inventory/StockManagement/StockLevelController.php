<?php

namespace App\Http\Controllers\Inventory\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\CurrentStock;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockLevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display stock level management interface
     */
    public function index(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search', '');
            $category = $request->get('category', '');
            $status = $request->get('status', '');
            $perPage = $request->get('per_page', 20);

            // Build the query - matching Supervisor approach
            $query = Item::with(['currentStockRecord', 'unit', 'category', 'stockMovements' => function($query) {
                $query->latest()->limit(1);
            }])->where('is_active', true);

            // Apply filters
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(item_code) LIKE ?', ['%' . strtolower($search) . '%']);
                });
            }

            if (!empty($category)) {
                $query->whereHas('category', function($q) use ($category) {
                    $q->where('id', $category);
                });
            }

            if (!empty($status)) {
                $query->where(function($q) use ($status) {
                    $q->whereHas('currentStockRecord', function($stockQuery) use ($status) {
                        if ($status === 'good') {
                            $stockQuery->whereHas('item', function($itemQuery) {
                                $itemQuery->whereColumn('current_stock.current_quantity', '>', 'items.reorder_point');
                            });
                        } elseif ($status === 'low') {
                            $stockQuery->where('current_quantity', '>', 0)
                                        ->whereHas('item', function($itemQuery) {
                                            $itemQuery->whereColumn('current_stock.current_quantity', '<=', 'items.reorder_point')
                                                      ->orWhereColumn('current_stock.current_quantity', '<=', 'items.min_stock_level');
                                        });
                        } elseif ($status === 'critical') {
                            $stockQuery->where('current_quantity', '<=', 0);
                        }
                    })->orWhere(function($noStockQuery) use ($status) {
                        // Items with no current stock record
                        if ($status === 'critical') {
                            $noStockQuery->doesntHave('currentStockRecord');
                        }
                    });
                });
            }

            // Get paginated results
            $stockItems = $query->orderBy('name')->paginate($perPage)->withQueryString();

            // Calculate metrics - matching Supervisor approach
            $metrics = $this->calculateStockMetrics();

            // Get categories for filter dropdown
            $categories = Category::where('is_active', true)->orderBy('name')->get();

            return view('Inventory.stock_management.stock_levels', compact('stockItems', 'metrics', 'categories'));

        } catch (\Exception $e) {
            \Log::error('Error loading stock levels: ' . $e->getMessage());
            
            // Return empty paginator to maintain consistency
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), // items
                0, // total
                20, // perPage
                1, // currentPage
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            return view('Inventory.stock_management.stock_levels', [
                'stockItems' => $emptyPaginator,
                'metrics' => ['total_items' => 0, 'healthy_stock' => 0, 'low_stock' => 0, 'critical_stock' => 0],
                'categories' => collect()
            ]);
        }
    }

    /**
     * Get stock levels by category (AJAX)
     */
    public function getStockLevels(Request $request)
    {
        try {
            $query = CurrentStock::with([
                'item.unit', 
                'item.category',
                'item.stockMovements'
            ])
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });

            // Apply category filter
            if ($request->has('category') && !empty($request->category)) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }

            // Apply stock level filters
            if ($request->has('status') && $request->status !== '') {
                switch ($request->status) {
                    case 'critical':
                        $query->where('current_quantity', 0);
                        break;
                    case 'low':
                        $query->whereHas('item', function($q) {
                            $q->whereColumn('items.reorder_point', '>=', 'current_stock.current_quantity');
                        });
                        break;
                    case 'good':
                        $query->whereHas('item', function($q) {
                            $q->whereColumn('current_stock.current_quantity', '>', 'items.reorder_point');
                        });
                        break;
                }
            }

            $stockLevels = $query->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($currentStock) {
                    $item = $currentStock->item;
                    $currentQuantity = (float) $currentStock->current_quantity;
                    $minStockLevel = (float) ($item->min_stock_level ?? 0);
                    $maxStockLevel = (float) ($item->max_stock_level ?? 0);
                    $reorderPoint = (float) ($item->reorder_point ?? 0);
                    
                    // Determine stock status
                    $stockStatus = 'good';
                    if ($currentQuantity <= 0) {
                        $stockStatus = 'out_of_stock';
                    } elseif ($currentQuantity <= $reorderPoint) {
                        $stockStatus = 'below_reorder';
                    } elseif ($currentQuantity <= $minStockLevel) {
                        $stockStatus = 'low_stock';
                    } elseif ($currentQuantity >= $maxStockLevel && $maxStockLevel > 0) {
                        $stockStatus = 'overstocked';
                    }

                    // Calculate stock days based on average usage (simplified)
                    $averageUsage = $currentStock->average_usage ?? 0;
                    $stockDays = $averageUsage > 0 ? round($currentQuantity / $averageUsage) : 0;

                    return [
                        'id' => $currentStock->id,
                        'item' => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'item_code' => $item->item_code,
                            'category' => $item->category->name ?? 'General',
                            'unit' => [
                                'symbol' => $item->unit->symbol ?? 'pcs'
                            ]
                        ],
                        'current_quantity' => $currentQuantity,
                        'min_stock_level' => $minStockLevel,
                        'max_stock_level' => $maxStockLevel,
                        'reorder_point' => $reorderPoint,
                        'average_cost' => (float) ($currentStock->average_cost ?? 0),
                        'stock_status' => $stockStatus,
                        'stock_days' => $stockDays,
                        'needs_reorder' => $currentQuantity <= $reorderPoint,
                        'updated_at' => $currentStock->updated_at->format('M d, Y H:i')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $stockLevels,
                'total' => $stockLevels->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting stock levels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock levels',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Update stock level manually
     */
    public function updateStockLevel(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'new_quantity' => 'required|numeric|min:0',
                'adjustment_reason' => 'required|string|max:255',
                'notes' => 'nullable|string|max:500'
            ]);

            $user = auth()->user();
            
            // Get or create current stock record
            $currentStock = CurrentStock::firstOrNew(['item_id' => $request->item_id]);
            $oldQuantity = $currentStock->current_quantity ?? 0;
            $newQuantity = (float) $request->new_quantity;
            $adjustmentQuantity = $newQuantity - $oldQuantity;

            // Update current stock
            $currentStock->update([
                'current_quantity' => $newQuantity,
                'last_adjustment_date' => now(),
                'updated_by' => $user->id
            ]);

            // Create stock movement record for adjustment
            $item = Item::find($request->item_id);
            \DB::table('stock_movements')->insert([
                'item_id' => $request->item_id,
                'movement_type' => 'adjustment',
                'reference_number' => 'ADJ-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'quantity' => $adjustmentQuantity,
                'unit_cost' => $currentStock->average_cost ?? 0,
                'total_cost' => $adjustmentQuantity * ($currentStock->average_cost ?? 0),
                'location' => 'Main Storage',
                'notes' => "Manual adjustment - {$request->adjustment_reason}. From: {$oldQuantity} to {$newQuantity}. {$request->notes}",
                'user_id' => $user->id,
                'created_at' => now()
            ]);

            // NEW: Notify Supervisors of Manual Adjustment (Audit Trail)
            try {
                // Find supervisors to notify (exclude the user making the change)
                $supervisors = \App\Models\User::where('role', 'supervisor')
                    ->where('id', '!=', $user->id) 
                    ->get();

                foreach ($supervisors as $supervisor) {
                    \App\Models\Notification::create([
                        'user_id' => $supervisor->id,
                        'title' => 'Manual Stock Adjustment',
                        'message' => "Stock for {$item->name} was manually adjusted by {$user->name}. Change: " . ($adjustmentQuantity > 0 ? '+' : '') . "{$adjustmentQuantity}.",
                        'type' => 'stock_alert',
                        'priority' => 'high',
                        'action_url' => route('inventory.stock.levels'),
                        'created_at' => now()
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send stock adjustment notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock level updated successfully',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment_quantity' => $adjustmentQuantity
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating stock level: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock level: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock level history
     */
    public function getStockHistory(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id'
            ]);

            $stockMovements = \DB::table('stock_movements')
                ->where('item_id', $request->item_id)
                ->where('movement_type', '!=', 'purchase') // Exclude initial purchase entries
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($movement) {
                    return [
                        'id' => $movement->id,
                        'reference_number' => $movement->reference_number,
                        'movement_type' => $movement->movement_type,
                        'quantity' => (float) $movement->quantity,
                        'unit_cost' => (float) ($movement->unit_cost ?? 0),
                        'location' => $movement->location,
                        'notes' => $movement->notes,
                        'created_at' => \Carbon\Carbon::parse($movement->created_at)->format('M d, Y H:i'),
                        'user_name' => 'User ID: ' . $movement->user_id // You might want to join with users table
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $stockMovements,
                'total' => $stockMovements->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting stock history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stock history',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Calculate total stock value across all items
     */
    private function calculateTotalStockValue()
    {
        try {
            return CurrentStock::whereHas('item', function($q) {
                $q->where('is_active', true);
            })->get()->sum(function($currentStock) {
                return (float) ($currentStock->current_quantity * $currentStock->average_cost);
            });
        } catch (\Exception $e) {
            \Log::error('Error calculating total stock value: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts()
    {
        try {
            $lowStockItems = CurrentStock::with(['item.unit', 'item.category'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                })
                ->get()
                ->filter(function($currentStock) {
                    $item = $currentStock->item;
                    return $currentStock->current_quantity <= ($item->reorder_point ?? 0);
                })
                ->map(function($currentStock) {
                    $item = $currentStock->item;
                    $currentQuantity = (float) $currentStock->current_quantity;
                    $minStockLevel = (float) ($item->min_stock_level ?? 0);
                    $reorderPoint = (float) ($item->reorder_point ?? 0);
                    
                    return [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'item_code' => $item->item_code,
                        'current_quantity' => $currentQuantity,
                        'min_stock_level' => $minStockLevel,
                        'reorder_point' => $reorderPoint,
                        'unit' => $item->unit->symbol ?? 'pcs',
                        'stock_status' => $currentQuantity <= 0 ? 'out_of_stock' : 'low_stock',
                        'shortage_quantity' => max(0, $minStockLevel - $currentQuantity),
                        'priority' => $currentQuantity <= 0 ? 'urgent' : 'normal'
                    ];
                })
                ->sortByDesc('priority')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $lowStockItems,
                'total' => $lowStockItems->count(),
                'urgent_count' => $lowStockItems->where('priority', 'urgent')->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting low stock alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get low stock alerts',
                'data' => [],
                'total' => 0,
                'urgent_count' => 0
            ], 500);
        }
    }

    /**
     * Export stock levels to CSV
     */
    public function exportStockLevels(Request $request)
    {
        try {
            $stockLevels = CurrentStock::with(['item.unit', 'item.category'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                })
                ->get()
                ->map(function($currentStock) {
                    $item = $currentStock->item;
                    $currentQuantity = (float) $currentStock->current_quantity;
                    
                    return [
                        'Item Code' => $item->item_code,
                        'Item Name' => $item->name,
                        'Category' => $item->category->name ?? 'General',
                        'Current Quantity' => $currentQuantity,
                        'Unit' => $item->unit->symbol ?? 'pcs',
                        'Min Stock Level' => (float) ($item->min_stock_level ?? 0),
                        'Max Stock Level' => (float) ($item->max_stock_level ?? 0),
                        'Reorder Point' => (float) ($item->reorder_point ?? 0),
                        'Average Cost' => (float) ($currentStock->average_cost ?? 0),
                        'Stock Value' => $currentQuantity * ($currentStock->average_cost ?? 0),
                        'Last Updated' => $currentStock->updated_at->format('Y-m-d H:i:s')
                    ];
                });

            // Here you would typically generate a CSV file
            // For now, return JSON with export data
            return response()->json([
                'success' => true,
                'message' => 'Export functionality ready for implementation',
                'data' => $stockLevels,
                'total' => $stockLevels->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting stock levels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export stock levels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate stock metrics for dashboard
     */
    private function calculateStockMetrics()
    {
        $totalItems = Item::where('is_active', true)->count();

        // Get items with their current stock
        $itemsWithStock = Item::with(['currentStockRecord'])
            ->where('is_active', true)
            ->get();

        $healthyStock = 0;
        $lowStock = 0;
        $criticalStock = 0;

        foreach ($itemsWithStock as $item) {
            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            $reorderPoint = $item->reorder_point ?? 0;
            $minStockLevel = $item->min_stock_level ?? 0;

            // Determine stock status
            if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                $criticalStock++;
            } elseif ($currentStock <= $reorderPoint) {
                $lowStock++;
            } else {
                $healthyStock++;
            }
        }

        return [
            'total_items' => $totalItems,
            'healthy_stock' => $healthyStock,
            'low_stock' => $lowStock,
            'critical_stock' => $criticalStock
        ];
    }

    /**
     * Display stock card for a specific item
     */
    public function stockCard(Request $request, Item $item)
    {
        try {
            // Check if item exists and is active
            if (!$item || !$item->is_active) {
                return redirect()->route('inventory.stock.levels')
                    ->with('error', 'Item not found or inactive.');
            }

            // Get all active items for dropdown selector
            $allItems = Item::with(['category', 'unit', 'currentStockRecord'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Load current item with relationships
            $item->load(['category', 'unit', 'currentStockRecord', 'stockMovements.user']);

            // Get stock movements with user relationships
            $movementsQuery = \App\Models\StockMovement::with(['user'])
                ->where('item_id', $item->id)
                ->orderBy('created_at', 'desc');

            // Apply date range filter if provided
            if ($request->filled('date_from')) {
                $movementsQuery->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $movementsQuery->whereDate('created_at', '<=', $request->date_to);
            }

            $movements = $movementsQuery->paginate(50);

            // Calculate metrics
            $metrics = $this->calculateStockCardMetrics($item);

            return view('Inventory.stock_management.stock_card', compact('item', 'movements', 'allItems', 'metrics'));

        } catch (\Exception $e) {
            \Log::error('Error loading stock card for item ' . $item->id . ': ' . $e->getMessage());

            return redirect()->route('inventory.stock.levels')
                ->with('error', 'Unable to load stock card. Please try again.');
        }
    }

    /**
     * Calculate metrics for stock card display
     */
    private function calculateStockCardMetrics($item)
    {
        try {
            $currentStock = $item->currentStockRecord ? (float) $item->currentStockRecord->current_quantity : 0.0;
            $reorderPoint = (float) ($item->reorder_point ?? 0);
            $minStockLevel = (float) ($item->min_stock_level ?? 0);
            $maxStockLevel = (float) ($item->max_stock_level ?? 0);

            // Calculate average daily usage (last 7 days)
            $sevenDaysAgo = \Carbon\Carbon::now()->subDays(7);
            $stockOutMovements = \App\Models\StockMovement::where('item_id', $item->id)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->where('quantity', '<', 0) // Negative quantities are stock outs
                ->sum('quantity');

            $averageDailyUsage = abs($stockOutMovements) / 7; // Convert to positive and divide by 7 days

            // Get last restock date
            $lastRestock = \App\Models\StockMovement::where('item_id', $item->id)
                ->where('quantity', '>', 0) // Positive quantities are stock ins
                ->where('movement_type', 'purchase')
                ->latest('created_at')
                ->first();

            $lastRestockDate = null;
            $lastRestockDaysAgo = null;
            if ($lastRestock) {
                $lastRestockDate = $lastRestock->created_at;
                // Use diffForHumans for better time formatting
                $lastRestockDaysAgo = \Carbon\Carbon::now()->diffForHumans($lastRestock->created_at, true);
            }

            return [
                'current_balance' => $currentStock,
                'reorder_level' => $reorderPoint,
                'min_stock_level' => $minStockLevel,
                'max_stock_level' => $maxStockLevel,
                'average_daily_usage' => round($averageDailyUsage, 1),
                'last_restock_date' => $lastRestockDate,
                'last_restock_days_ago' => $lastRestockDaysAgo,
                'stock_status' => $this->getStockStatus($currentStock, $reorderPoint),
                'days_of_supply' => $averageDailyUsage > 0 ? round($currentStock / $averageDailyUsage, 1) : 0
            ];

        } catch (\Exception $e) {
            \Log::error('Error calculating stock card metrics for item ' . $item->id . ': ' . $e->getMessage());

            // Return default values in case of error
            return [
                'current_balance' => 0.0,
                'reorder_level' => 0.0,
                'min_stock_level' => 0.0,
                'max_stock_level' => 0.0,
                'average_daily_usage' => 0.0,
                'last_restock_date' => null,
                'last_restock_days_ago' => null,
                'stock_status' => 'unknown',
                'days_of_supply' => 0
            ];
        }
    }

    /**
     * Get stock status based on current quantity and reorder point
     */
    private function getStockStatus($currentStock, $reorderPoint)
    {
        if ($currentStock <= 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= ($reorderPoint * 0.5)) {
            return 'critical';
        } elseif ($currentStock <= $reorderPoint) {
            return 'low';
        } else {
            return 'good';
        }
    }

    /**
     * Store inventory adjustment
     */
    public function storeAdjustment(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'adjustment_type' => 'required|in:add,remove',
                'quantity' => 'required|numeric|min:0.001',
                'reason_code' => 'required|string',
                'remarks' => 'required|string|max:500',
                'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:5120'
            ]);

            $item = Item::findOrFail($request->item_id);
            $quantity = $request->adjustment_type === 'remove' ? -abs($request->quantity) : abs($request->quantity);

            // Check stock availability for removals
            if ($request->adjustment_type === 'remove') {
                $currentStock = CurrentStock::where('item_id', $item->id)->first();
                $availableStock = $currentStock ? $currentStock->current_quantity : 0;

                if ($availableStock < abs($quantity)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available: ' . $availableStock . ' ' . ($item->unit->symbol ?? '')
                    ], 422);
                }
            }

            // Calculate costs
            $unitCost = $item->cost_price;
            $totalCost = abs($quantity) * $unitCost;

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = 'adjustment_' . time() . '.' . $photo->getClientOriginalExtension();
                $photoPath = $photo->storeAs('adjustments', $photoName, 'public');
            }

            // Create stock movement record
            $stockMovement = \App\Models\StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'adjustment',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $request->remarks,
                'user_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now()
            ]);

            // Update CurrentStock record
            $currentStock = CurrentStock::firstOrNew(['item_id' => $item->id]);
            $currentStock->current_quantity = ($currentStock->current_quantity ?? 0) + $quantity;
            $currentStock->last_adjustment_date = now();
            $currentStock->updated_by = auth()->id();
            $currentStock->save();

            return response()->json([
                'success' => true,
                'message' => 'Inventory adjustment created successfully',
                'data' => [
                    'id' => $stockMovement->id,
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'unit_symbol' => $item->unit->symbol ?? '',
                    'reason' => $request->reason_code,
                    'total_cost' => $totalCost
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating adjustment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create adjustment'
            ], 500);
        }
    }
}