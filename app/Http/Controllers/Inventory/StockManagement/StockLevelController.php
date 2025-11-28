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
            $query = CurrentStock::with([
                'item.unit', 
                'item.category'
            ])
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });

            // Apply filters
            if ($request->has('category_id') && $request->category_id !== 'all') {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if ($request->has('stock_status') && $request->stock_status !== 'all') {
                switch ($request->stock_status) {
                    case 'out_of_stock':
                        $query->where('current_quantity', 0);
                        break;
                    case 'low_stock':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('current_stock.current_quantity <= items.min_stock_level');
                        });
                        break;
                    case 'good_stock':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('current_stock.current_quantity > items.min_stock_level');
                        });
                        break;
                }
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->whereHas('item', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('item_code', 'like', '%' . $search . '%');
                });
            }

            $stockLevels = $query->orderBy('updated_at', 'desc')
                ->paginate(20)
                ->withQueryString();

            // Get filter options
            $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);

            // Calculate statistics
            $totalItems = Item::where('is_active', true)->count();
            $outOfStock = CurrentStock::whereHas('item', function($q) {
                $q->where('is_active', true);
            })->where('current_quantity', 0)->count();
            $lowStock = CurrentStock::whereHas('item', function($q) {
                $q->where('is_active', true);
            })->whereRaw('current_stock.current_quantity <= items.min_stock_level')->count();
            $goodStock = $totalItems - $outOfStock - $lowStock;

            $stats = [
                'total_items' => $totalItems,
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock,
                'good_stock' => $goodStock,
                'stock_value' => $this->calculateTotalStockValue()
            ];

            return view('Inventory.stock_management.stock_level', compact('stockLevels', 'categories', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Error loading stock levels: ' . $e->getMessage());
            return view('Inventory.stock_management.stock_level', [
                'stockLevels' => collect(),
                'categories' => collect(),
                'stats' => ['total_items' => 0, 'out_of_stock' => 0, 'low_stock' => 0, 'good_stock' => 0, 'stock_value' => 0]
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
                'item.category'
            ])
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });

            // Apply category filter
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            // Apply stock level filters
            if ($request->has('stock_level') && $request->stock_level !== 'all') {
                switch ($request->stock_level) {
                    case 'out_of_stock':
                        $query->where('current_quantity', 0);
                        break;
                    case 'low_stock':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('current_stock.current_quantity <= items.min_stock_level');
                        });
                        break;
                    case 'good_stock':
                        $query->whereHas('item', function($q) {
                            $q->whereRaw('current_stock.current_quantity > items.min_stock_level');
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
                ->whereRaw('current_stock.current_quantity <= items.min_stock_level')
                ->get()
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
}