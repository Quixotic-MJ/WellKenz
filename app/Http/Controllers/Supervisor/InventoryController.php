<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\CurrentStock;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Views
    public function stockLevel(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $status = $request->get('status', '');
        $perPage = $request->get('per_page', 20);

        // Build the query
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

        // Calculate metrics
        $metrics = $this->calculateStockMetrics();

        // Get categories for filter dropdown
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Get active items with current stock for adjustment modal dropdown
        $items = Item::with(['unit', 'currentStockRecord'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_symbol' => $item->unit->symbol ?? '',
                    'current_stock' => $currentStock,
                    'cost_price' => $item->cost_price
                ];
            });

        // Get today's stats for modal
        $todayLossValue = StockMovement::whereDate('created_at', today())
            ->where('movement_type', 'adjustment')
            ->where('quantity', '<', 0)
            ->sum('total_cost');

        $stats = [
            'today_loss_value' => $todayLossValue,
            'total_adjustments_today' => StockMovement::whereDate('created_at', today())
                ->where('movement_type', 'adjustment')
                ->count(),
        ];

        return view('Supervisor.inventory.stock_level', compact('stockItems', 'metrics', 'categories', 'items', 'stats'));
    }

    public function stockHistory()
    {
        $items = Item::with(['currentStockRecord', 'unit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('Supervisor.inventory.stock_history', compact('items'));
    }

    public function stockCard(Request $request, Item $item)
    {
        try {
            // Check if item exists and is active
            if (!$item || !$item->is_active) {
                return redirect()->route('supervisor.inventory.index')
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

            return view('Supervisor.inventory.stock_card', compact('item', 'movements', 'allItems', 'metrics'));

        } catch (\Exception $e) {
            \Log::error('Error loading stock card for item ' . $item->id . ': ' . $e->getMessage());

            return redirect()->route('supervisor.inventory.index')
                ->with('error', 'Unable to load stock card. Please try again.');
        }
    }

    public function inventoryAdjustments()
    {
        // Get active items with current stock for dropdown
        $items = Item::with(['unit', 'currentStockRecord'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_symbol' => $item->unit->symbol ?? '',
                    'current_stock' => $currentStock,
                    'cost_price' => $item->cost_price
                ];
            });

        // Get today's total loss value (negative adjustments)
        $todayLossValue = StockMovement::whereDate('created_at', today())
            ->where('movement_type', 'adjustment')
            ->where('quantity', '<', 0)
            ->sum('total_cost');

        // Get recent adjustments for history
        $recentAdjustments = StockMovement::with(['item', 'user'])
            ->where('movement_type', 'adjustment')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($movement) {
                return [
                    'id' => $movement->id,
                    'item_name' => $movement->item->name ?? 'Unknown Item',
                    'item_code' => $movement->item->item_code ?? '',
                    'quantity' => $movement->quantity,
                    'unit_symbol' => $movement->item->unit->symbol ?? '',
                    'reason' => $this->getAdjustmentReason($movement->notes),
                    'remarks' => $movement->notes,
                    'total_cost' => abs($movement->total_cost),
                    'movement_type' => $movement->quantity > 0 ? 'add' : 'remove',
                    'status' => 'Approved', // For now, default to approved
                    'created_at' => $movement->created_at,
                    'formatted_date' => $this->formatAdjustmentDate($movement->created_at),
                    'user_name' => $movement->user->name ?? 'System'
                ];
            });

        // Calculate statistics
        $stats = [
            'today_loss_value' => $todayLossValue,
            'total_adjustments_today' => StockMovement::whereDate('created_at', today())
                ->where('movement_type', 'adjustment')
                ->count(),
            'total_adjustments' => StockMovement::where('movement_type', 'adjustment')
                ->count(),
        ];

        return view('Supervisor.inventory.adjustments', compact('items', 'recentAdjustments', 'stats'));
    }

    public function printStockReport(Request $request)
    {
        // Get print-specific data (no pagination)
        $stockData = Item::with(['currentStockRecord', 'unit', 'category', 'stockMovements' => function($query) {
            $query->latest()->limit(1);
        }])
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function($item) {
            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            $reorderPoint = $item->reorder_point ?? 0;
            $minStockLevel = $item->min_stock_level ?? 0;
            $maxStockLevel = $item->max_stock_level ?? 0;

            // Determine stock status and color
            $status = 'Good';
            $statusClass = 'text-green-600';
            if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                $status = 'Critical';
                $statusClass = 'text-red-600';
            } elseif ($currentStock <= $reorderPoint) {
                $status = 'Low';
                $statusClass = 'text-amber-600';
            }

            // Calculate stock percentage
            $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;

            // Get last movement
            $lastMovement = $item->stockMovements->first();
            $lastMovementText = 'No movement';
            if ($lastMovement) {
                $timeDiff = \Carbon\Carbon::now()->diffForHumans($lastMovement->created_at, true);
                $lastMovementText = $timeDiff . ' ago (' . ucfirst($lastMovement->movement_type) . ')';
            }

            return [
                'item' => $item,
                'current_stock' => $currentStock,
                'reorder_point' => $reorderPoint,
                'min_stock_level' => $minStockLevel,
                'max_stock_level' => $maxStockLevel,
                'percentage' => $percentage,
                'status' => $status,
                'status_class' => $statusClass,
                'last_movement' => $lastMovementText
            ];
        });

        // Calculate metrics for print header
        $metrics = $this->calculateStockMetrics();

        return view('Supervisor.inventory.print_stock_report', compact('stockData', 'metrics'));
    }

    public function exportStockCSV()
    {
        $stockData = Item::with(['currentStockRecord', 'unit', 'category'])
            ->where('is_active', true)
            ->get()
            ->map(function($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                $reorderPoint = $item->reorder_point ?? 0;
                $minStockLevel = $item->min_stock_level ?? 0;

                // Determine stock status
                $status = 'Good';
                if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                    $status = 'Critical';
                } elseif ($currentStock <= $reorderPoint) {
                    $status = 'Low';
                }

                // Calculate stock percentage
                $maxStockLevel = $item->max_stock_level ?? 0;
                $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;

                return [
                    'Item Code' => $item->item_code,
                    'Item Name' => $item->name,
                    'Category' => $item->category->name ?? '',
                    'Current Stock' => $currentStock,
                    'Unit' => $item->unit->symbol ?? '',
                    'Min Stock Level' => $minStockLevel,
                    'Reorder Point' => $reorderPoint,
                    'Max Stock Level' => $maxStockLevel,
                    'Stock Percentage' => $percentage . '%',
                    'Status' => $status
                ];
            });

        // Create CSV content
        $filename = 'stock_level_report_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($stockData) {
            $file = fopen('php://output', 'w');

            // Write header row
            if ($stockData->isNotEmpty()) {
                fputcsv($file, array_keys($stockData->first()));

                // Write data rows
                foreach ($stockData as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStockPDF(Request $request)
    {
        try {
            // Increase memory limit and execution time for PDF generation
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', 300);

            // Get filter parameters (same as stockLevel method)
            $search = $request->get('search', '');
            $category = $request->get('category', '');
            $status = $request->get('status', '');

            // Build the query (same as stockLevel method) with chunking for memory efficiency
            $query = Item::with(['currentStockRecord', 'unit', 'category', 'stockMovements' => function($query) {
                $query->latest()->limit(1);
            }])->where('is_active', true);

            // Apply filters (same as stockLevel method)
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

            // Get filtered data for PDF with memory-efficient processing
            $stockData = $query->orderBy('name')->get()->map(function($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                $reorderPoint = $item->reorder_point ?? 0;
                $minStockLevel = $item->min_stock_level ?? 0;
                $maxStockLevel = $item->max_stock_level ?? 0;

                // Determine stock status and color
                $status = 'Good';
                $statusClass = 'text-green-600';
                if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                    $status = 'Critical';
                    $statusClass = 'text-red-600';
                } elseif ($currentStock <= $reorderPoint) {
                    $status = 'Low';
                    $statusClass = 'text-amber-600';
                }

                // Calculate stock percentage
                $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;

                // Get last movement
                $lastMovement = $item->stockMovements->first();
                $lastMovementText = 'No movement';
                if ($lastMovement) {
                    $timeDiff = \Carbon\Carbon::now()->diffForHumans($lastMovement->created_at, true);
                    $lastMovementText = $timeDiff . ' ago (' . ucfirst($lastMovement->movement_type) . ')';
                }

                return [
                    'item' => $item,
                    'current_stock' => $currentStock,
                    'reorder_point' => $reorderPoint,
                    'min_stock_level' => $minStockLevel,
                    'max_stock_level' => $maxStockLevel,
                    'percentage' => $percentage,
                    'status' => $status,
                    'status_class' => $statusClass,
                    'last_movement' => $lastMovementText
                ];
            });

            // Calculate metrics for PDF header
            $metrics = $this->calculateStockMetrics();

            // Generate PDF with error handling - use simplified version for stability
            try {
                $pdf = \PDF::loadView('Supervisor.inventory.simple_stock_report', compact('stockData', 'metrics'));
            } catch (\Exception $e) {
                \Log::error('PDF generation failed: ' . $e->getMessage());
                throw new \Exception('Failed to generate PDF report. Please try again.');
            }

            // Set conservative PDF options for stability
            $pdf->setOptions([
                'defaultFont' => 'serif',
                'isHtml5ParserEnabled' => false,
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false,
                'margin_top' => 15,
                'margin_right' => 10,
                'margin_bottom' => 15,
                'margin_left' => 10,
                'dpi' => 96,
                'enable_font_subsetting' => true,
                'defaultPaperSize' => 'a4',
                'defaultPaperOrientation' => 'portrait',
            ]);

            $filename = 'stock_level_report_' . date('Y-m-d_H-i-s') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF report. Please try again or contact support.');
        }
    }

    // APIs
    public function getItemDetails(Item $item)
    {
        try {
            $item->load(['unit', 'currentStockRecord']);

            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            $averageCost = $item->currentStockRecord ? $item->currentStockRecord->average_cost : $item->cost_price;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_symbol' => $item->unit->symbol ?? '',
                    'current_stock' => $currentStock,
                    'cost_price' => $averageCost,
                    'total_value' => $currentStock * $averageCost
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load item details'
            ], 500);
        }
    }

    public function createAdjustment(Request $request)
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
                        'error' => 'Insufficient stock. Available: ' . $availableStock . ' ' . ($item->unit->symbol ?? '')
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
            $stockMovement = StockMovement::create([
                'item_id' => $item->id,
                'movement_type' => 'adjustment',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $request->remarks,
                'user_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now()
            ]);

            // Create audit log
            \App\Models\AuditLog::create([
                'table_name' => 'stock_movements',
                'record_id' => $stockMovement->id,
                'action' => 'CREATE',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => json_encode([
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'reason_code' => $request->reason_code,
                    'remarks' => $request->remarks,
                    'photo_path' => $photoPath
                ])
            ]);

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
                'error' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating adjustment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create adjustment'
            ], 500);
        }
    }

    public function getAdjustmentHistory(Request $request)
    {
        try {
            $query = StockMovement::with(['item', 'user'])
                ->where('movement_type', 'adjustment');

            // Apply filters
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('item_id')) {
                $query->where('item_id', $request->item_id);
            }

            $adjustments = $query->orderBy('created_at', 'desc')->paginate(20);

            $data = $adjustments->getCollection()->map(function($movement) {
                return [
                    'id' => $movement->id,
                    'item_name' => $movement->item->name ?? 'Unknown Item',
                    'item_code' => $movement->item->item_code ?? '',
                    'quantity' => $movement->quantity,
                    'unit_symbol' => $movement->item->unit->symbol ?? '',
                    'reason' => $this->getAdjustmentReason($movement->notes),
                    'remarks' => $movement->notes,
                    'total_cost' => abs($movement->total_cost),
                    'movement_type' => $movement->quantity > 0 ? 'add' : 'remove',
                    'status' => 'Approved',
                    'created_at' => $movement->created_at,
                    'formatted_date' => $this->formatAdjustmentDate($movement->created_at),
                    'user_name' => $movement->user->name ?? 'System'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $adjustments->currentPage(),
                    'last_page' => $adjustments->lastPage(),
                    'per_page' => $adjustments->perPage(),
                    'total' => $adjustments->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load adjustment history'
            ], 500);
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
     * Get adjustment reason from notes
     */
    private function getAdjustmentReason($notes)
    {
        if (!$notes) return 'General Adjustment';

        $notesLower = strtolower($notes);

        if (strpos($notesLower, 'spoil') !== false || strpos($notesLower, 'expire') !== false) {
            return 'Spoilage / Expired';
        } elseif (strpos($notesLower, 'damage') !== false || strpos($notesLower, 'break') !== false) {
            return 'Damaged / Broken';
        } elseif (strpos($notesLower, 'spill') !== false) {
            return 'Spillage (Production)';
        } elseif (strpos($notesLower, 'theft') !== false || strpos($notesLower, 'missing') !== false) {
            return 'Theft / Missing';
        } elseif (strpos($notesLower, 'audit') !== false || strpos($notesLower, 'variance') !== false) {
            return 'Audit Variance Correction';
        } elseif (strpos($notesLower, 'found') !== false) {
            return 'Found Item';
        }

        return 'General Adjustment';
    }

    /**
     * Format adjustment date for display
     */
    private function formatAdjustmentDate($timestamp)
    {
        $now = \Carbon\Carbon::now();
        $diff = $now->diffForHumans($timestamp, true);

        if ($timestamp->isToday()) {
            return 'Today, ' . $timestamp->format('h:i A');
        } elseif ($timestamp->isYesterday()) {
            return 'Yesterday, ' . $timestamp->format('h:i A');
        } else {
            return $timestamp->format('M j, h:i A');
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
}
