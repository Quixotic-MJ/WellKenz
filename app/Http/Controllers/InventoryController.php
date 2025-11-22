<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display purchase request interface with catalog and history
     */
    public function index()
    {
        return $this->create();
    }

    /**
     * Show the form for creating a new purchase request
     * This serves as the main interface with catalog and history
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get active items with current stock information from current_stock table
        $items = Item::with([
            'category',
            'unit',
            'currentStockRecord'
        ])
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function($item) {
            // Get current stock from the relationship
            $currentStock = $item->currentStockRecord ? 
                $item->currentStockRecord->current_quantity : 0;
            
            // Ensure relationships exist with fallbacks
            $category = $item->category ? [
                'id' => $item->category->id,
                'name' => $item->category->name
            ] : ['id' => 0, 'name' => 'General'];
            
            $unit = $item->unit ? [
                'id' => $item->unit->id,
                'name' => $item->unit->name,
                'symbol' => $item->unit->symbol
            ] : ['id' => 0, 'name' => 'Piece', 'symbol' => 'pcs'];
            
            return (object) [
                'id' => $item->id,
                'item_code' => $item->item_code ?? 'N/A',
                'name' => $item->name,
                'description' => $item->description ?? '',
                'category' => (object) $category,
                'unit' => (object) $unit,
                'cost_price' => (float) ($item->cost_price ?? 0),
                'selling_price' => (float) ($item->selling_price ?? 0),
                'current_stock' => (float) $currentStock,
                'min_stock_level' => (float) ($item->min_stock_level ?? 0),
                'max_stock_level' => (float) ($item->max_stock_level ?? 0),
                'reorder_point' => (float) ($item->reorder_point ?? 0),
                'stock_status' => $this->getStockStatus($item)
            ];
        });

        // Get active categories for filtering
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get user's default department
        $defaultDepartment = $user->userProfile?->department ?? 'Inventory';

        // Get purchase request history with filtering
        $purchaseRequests = PurchaseRequest::with([
            'requestedBy:id,name,email',
            'purchaseRequestItems.item.unit'
        ])
        ->where('requested_by', $user->id) // Only show user's own requests
        ->when(request('status'), function($query, $status) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        })
        ->when(request('department'), function($query, $department) {
            if ($department !== 'all') {
                $query->where('department', 'like', '%' . $department . '%');
            }
        })
        ->when(request('search'), function($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('pr_number', 'like', '%' . $search . '%')
                  ->orWhere('department', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
        })
        ->latest('created_at')
        ->paginate(10)
        ->withQueryString();

        // Calculate statistics for the current user
        $stats = [
            'total' => PurchaseRequest::where('requested_by', $user->id)->count(),
            'pending' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'pending')->count(),
            'approved' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'approved')->count(),
            'rejected' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'rejected')->count(),
            'draft' => PurchaseRequest::where('requested_by', $user->id)
                ->where('status', 'draft')->count(),
        ];

        // Get unique departments for filter dropdown
        $departments = PurchaseRequest::where('requested_by', $user->id)
            ->distinct()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department')
            ->sort()
            ->values();

        return view('Inventory.outbound.purchase_request', compact(
            'items',
            'categories',
            'defaultDepartment',
            'stats',
            'departments',
            'purchaseRequests'
        ));
    }

    /**
     * Store a newly created purchase request
     */
    public function createPurchaseRequest(Request $request)
    {
        try {
            $request->validate([
                'department' => 'required|string|max:255',
                'priority' => 'required|in:low,normal,high,urgent',
                'request_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity_requested' => 'required|numeric|min:0.01',
                'items.*.unit_price_estimate' => 'required|numeric|min:0'
            ]);

            $user = Auth::user();

            DB::beginTransaction();

            // Generate PR number
            $prNumber = 'PR-' . date('Y') . '-' . str_pad(PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total
            $totalEstimatedCost = 0;
            foreach ($request->items as $item) {
                $totalEstimatedCost += ($item['quantity_requested'] * $item['unit_price_estimate']);
            }

            // Create purchase request
            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requested_by' => $user->id,
                'department' => $request->department,
                'priority' => $request->priority,
                'request_date' => $request->request_date,
                'notes' => $request->notes,
                'status' => 'pending',
                'total_estimated_cost' => $totalEstimatedCost
            ]);

            // Create purchase request items
            foreach ($request->items as $itemData) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity_requested'],
                    'unit_price_estimate' => $itemData['unit_price_estimate'],
                    'total_estimated_cost' => ($itemData['quantity_requested'] * $itemData['unit_price_estimate'])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Request created successfully',
                'pr_number' => $prNumber
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating purchase request: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase request
     */
    public function show($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit'
            ])->findOrFail($id);

            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase request not found'
                ], 404);
            }

            // Process purchase request items
            $items = $purchaseRequest->purchaseRequestItems->map(function($item) {
                return [
                    'item_name' => $item->item->name,
                    'quantity_requested' => number_format($item->quantity_requested, 2),
                    'unit_price_estimate' => number_format($item->unit_price_estimate, 2),
                    'total_estimated_cost' => number_format($item->total_estimated_cost, 2)
                ];
            });

            // Get requester info
            $requesterName = $purchaseRequest->requestedBy ? 
                ($purchaseRequest->requestedBy->name ?? 'Unknown User') : 'Unknown User';

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchaseRequest->id,
                    'pr_number' => $purchaseRequest->pr_number,
                    'requested_by' => $requesterName,
                    'department' => $purchaseRequest->department,
                    'priority' => $purchaseRequest->priority,
                    'status' => $purchaseRequest->status,
                    'notes' => $purchaseRequest->notes,
                    'request_date' => $purchaseRequest->request_date,
                    'created_at' => $purchaseRequest->created_at->toISOString(),
                    'items' => $items->values(),
                    'total_items' => $items->count(),
                    'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                    'formatted_total' => '₱' . number_format($purchaseRequest->total_estimated_cost, 2)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading purchase request details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase request
     */
    public function destroy($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase request not found'
                ], 404);
            }

            if (!in_array($purchaseRequest->status, ['pending', 'draft'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a request that has already been processed'
                ], 400);
            }

            // Delete related items first
            PurchaseRequestItem::where('purchase_request_id', $purchaseRequest->id)->delete();
            
            // Delete the purchase request
            $purchaseRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error cancelling purchase request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for dropdown selection with filtering (API endpoint)
     */
    public function getItems(Request $request)
    {
        try {
            $query = Item::with(['category', 'unit', 'currentStockRecord'])
                ->where('is_active', true);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('item_code', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Apply category filter
            if ($request->has('category_id') && $request->category_id !== 'all') {
                $query->where('category_id', $request->category_id);
            }

            // Apply stock status filter
            if ($request->has('stock_status') && $request->stock_status !== 'all') {
                $stockStatus = $request->stock_status;
                $query->whereHas('currentStockRecord', function($q) use ($stockStatus) {
                    // This will be filtered in the map function
                });
            }

            $items = $query->orderBy('name')
                ->get()
                ->map(function($item) {
                    // Get current stock from the relationship
                    $currentStock = $item->currentStockRecord ? 
                        $item->currentStockRecord->current_quantity : 0;
                    
                    $category = $item->category ? [
                        'id' => $item->category->id,
                        'name' => $item->category->name
                    ] : ['id' => 0, 'name' => 'General'];
                    
                    $unit = $item->unit ? [
                        'id' => $item->unit->id,
                        'name' => $item->unit->name,
                        'symbol' => $item->unit->symbol
                    ] : ['id' => 0, 'name' => 'Piece', 'symbol' => 'pcs'];
                    
                    return [
                        'id' => $item->id,
                        'item_code' => $item->item_code ?? 'N/A',
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'category' => $category,
                        'unit' => $unit,
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'selling_price' => (float) ($item->selling_price ?? 0),
                        'current_stock' => (float) $currentStock,
                        'min_stock_level' => (float) ($item->min_stock_level ?? 0),
                        'max_stock_level' => (float) ($item->max_stock_level ?? 0),
                        'reorder_point' => (float) ($item->reorder_point ?? 0),
                        'stock_status' => $this->getStockStatus($item),
                        'stock_percentage' => $item->max_stock_level > 0 ? 
                            ($currentStock / $item->max_stock_level * 100) : 0
                    ];
                });

            // Apply stock status filter if needed
            if ($request->has('stock_status') && $request->stock_status !== 'all') {
                $items = $items->filter(function($item) use ($request) {
                    return $item['stock_status'] === $request->stock_status;
                })->values();
            }

            return response()->json([
                'success' => true,
                'data' => $items->values(),
                'total' => $items->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get stock status based on current vs reorder point
     * Uses current_stock table data
     */
    private function getStockStatus($item)
    {
        // Get current stock from the relationship
        $currentStock = $item->currentStockRecord ? 
            $item->currentStockRecord->current_quantity : 0;
        
        $reorderPoint = $item->reorder_point ?? 0;
        $maxLevel = $item->max_stock_level ?? 0;

        if ($currentStock <= 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= $reorderPoint) {
            return 'low_stock';
        } elseif ($maxLevel > 0 && $currentStock >= $maxLevel * 0.8) {
            return 'high_stock';
        } else {
            return 'normal_stock';
        }
    }

    /**
     * Get categories for filter dropdown
     */
    public function getCategories()
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'description']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get user departments for autocomplete
     */
    public function getDepartments()
    {
        try {
            $departments = PurchaseRequest::distinct()
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->pluck('department')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'data' => []
            ], 500);
        }
    }
}