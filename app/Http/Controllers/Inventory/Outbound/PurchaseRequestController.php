<?php

namespace App\Http\Controllers\Inventory\Outbound;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Item;
use App\Models\Category;
use App\Models\CurrentStock;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display purchase request interface - now focused on replenishment
     */
    public function index()
    {
        return $this->replenish();
    }

    /**
     * Show replenishment-focused interface with low stock items
     */
    public function replenish()
    {
        $user = Auth::user();
        
        // Get items that need replenishment:
        // 1. Items with currentStockRecord WHERE current_quantity <= reorder_point OR current_quantity <= min_stock_level
        // 2. Items WITHOUT currentStockRecord WHERE reorder_point > 0 OR min_stock_level > 0
        $lowStockItems = Item::with([
            'category',
            'unit', 
            'currentStockRecord'
        ])
        ->where('is_active', true)
        ->where(function($query) {
            // Case 1: Items that have currentStockRecord and are below reorder_point OR min_stock_level
            $query->whereHas('currentStockRecord', function($stockQuery) {
                $stockQuery->where(function($q) {
                    $q->whereColumn('current_stock.current_quantity', '<=', 'items.reorder_point')
                      ->orWhereColumn('current_stock.current_quantity', '<=', 'items.min_stock_level');
                });
            })
            // Case 2: Items without currentStockRecord but have reorder_point > 0 OR min_stock_level > 0
            ->orWhere(function($noStockQuery) {
                $noStockQuery->whereDoesntHave('currentStockRecord')
                           ->where(function($q) {
                               $q->where('reorder_point', '>', 0)
                                 ->orWhere('min_stock_level', '>', 0);
                           });
            });
        })
        ->orderBy('name')
        ->get()
        ->map(function($item) {
            $currentStock = $item->currentStockRecord ? 
                $item->currentStockRecord->current_quantity : 0;
            
            // Calculate target level as max of reorder_point, min_stock_level, and 1
            $reorderPoint = $item->reorder_point ?? 0;
            $minStockLevel = $item->min_stock_level ?? 0;
            $maxStockLevel = $item->max_stock_level ?? 0;
            $targetLevel = max($reorderPoint, $minStockLevel, 1);
            
            // Calculate suggested quantity
            $suggestedQty = 0;
            if ($maxStockLevel > 0) {
                $suggestedQty = max(0, $maxStockLevel - $currentStock);
            } else {
                $suggestedQty = max(0, $targetLevel - $currentStock);
            }
            
            // Ensure minimum suggested quantity for items that are truly needed
            if ($currentStock <= $targetLevel && $suggestedQty == 0) {
                $suggestedQty = 1;
            }
            
            return (object) [
                'id' => $item->id,
                'item_code' => $item->item_code ?? 'N/A',
                'name' => $item->name,
                'description' => $item->description ?? '',
                'category' => $item->category->name ?? 'General',
                'unit' => $item->unit->symbol ?? 'pcs',
                'current_stock' => (float) $currentStock,
                'reorder_point' => (float) $reorderPoint,
                'min_stock_level' => (float) $minStockLevel,
                'max_stock_level' => (float) $maxStockLevel,
                'target_level' => (float) $targetLevel,
                'suggested_qty' => (int) $suggestedQty,
                'cost_price' => (float) ($item->cost_price ?? 0),
                'stock_status' => $this->getStockStatus($item)
            ];
        });

        // Get user's purchase request statistics
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

        // Get recent purchase request history for reference
        $recentRequests = PurchaseRequest::with([
            'requestedBy:id,name,email',
            'purchaseRequestItems.item.unit'
        ])
        ->where('requested_by', $user->id)
        ->latest('created_at')
        ->limit(5)
        ->get();

        return view('Inventory.outbound.purchase_request', compact(
            'lowStockItems',
            'stats',
            'recentRequests'
        ));
    }

    /**
     * Show the form for creating a new purchase request
     * This now serves as the main replenishment interface
     */
    public function create()
    {
        return $this->replenish();
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
            $items = Item::select('id', 'name', 'item_code', 'unit_id')
                ->with('unit:id,name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
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
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // Apply stock level filter
            if ($request->has('stock_filter') && $request->stock_filter !== 'all') {
                switch ($request->stock_filter) {
                    case 'low_stock':
                        $query->whereHas('currentStockRecord', function($q) {
                            $q->whereHas('item', function($itemQuery) {
                                $itemQuery->whereColumn('current_stock.current_quantity', '<=', 'items.min_stock_level');
                            });
                        });
                        break;
                    case 'out_of_stock':
                        $query->whereHas('currentStockRecord', function($q) {
                            $q->where('current_quantity', 0);
                        });
                        break;
                    case 'good_stock':
                        $query->whereHas('currentStockRecord', function($q) {
                            $q->whereHas('item', function($itemQuery) {
                                $itemQuery->whereColumn('current_stock.current_quantity', '>', 'items.min_stock_level');
                            });
                        });
                        break;
                }
            }

            // Apply reorder point filter
            if ($request->has('reorder_filter') && $request->reorder_filter !== 'all') {
                switch ($request->reorder_filter) {
                    case 'below_reorder':
                        $query->whereHas('currentStockRecord', function($q) {
                            $q->whereHas('item', function($itemQuery) {
                                $itemQuery->whereColumn('current_stock.current_quantity', '<=', 'items.reorder_point');
                            });
                        });
                        break;
                    case 'above_reorder':
                        $query->whereHas('currentStockRecord', function($q) {
                            $q->whereHas('item', function($itemQuery) {
                                $itemQuery->whereColumn('current_stock.current_quantity', '>', 'items.reorder_point');
                            });
                        });
                        break;
                }
            }

            $items = $query->orderBy('name')
                ->limit(50)
                ->get()
                ->map(function($item) {
                    $currentStock = $item->currentStockRecord ? 
                        $item->currentStockRecord->current_quantity : 0;
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'description' => $item->description,
                        'category' => $item->category->name ?? 'General',
                        'unit' => [
                            'id' => $item->unit->id ?? 0,
                            'symbol' => $item->unit->symbol ?? 'pcs'
                        ],
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'current_stock' => (float) $currentStock,
                        'min_stock_level' => (float) ($item->min_stock_level ?? 0),
                        'reorder_point' => (float) ($item->reorder_point ?? 0),
                        'max_stock_level' => (float) ($item->max_stock_level ?? 0),
                        'stock_status' => $this->getStockStatus($item),
                        'needs_reorder' => $currentStock <= ($item->reorder_point ?? 0)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'total' => $items->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get categories for dropdown (API endpoint)
     */
    public function getCategories()
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'total' => $categories->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get categories for RTV bulk operations (API endpoint)
     */
    public function getCategoriesForRtvBulk()
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'total' => $categories->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching categories for RTV bulk: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get departments for filter (API endpoint)
     */
    public function getDepartments()
    {
        try {
            $user = Auth::user();
            
            // Get unique departments that the user has access to
            $departments = PurchaseRequest::where('requested_by', $user->id)
                ->distinct()
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->pluck('department')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $departments,
                'total' => $departments->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Create purchase request (API endpoint)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'department' => 'required|string|max:100',
                'priority' => 'required|in:low,normal,high,urgent',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity_requested' => 'required|numeric|min:0.01',
                'items.*.unit_price_estimate' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();

            $user = Auth::user();

            // Generate PR number
            $prNumber = 'PR-' . now()->format('Y') . '-' . str_pad(PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total estimated cost
            $totalEstimatedCost = 0;
            foreach ($request->items as $item) {
                $itemPrice = $item['unit_price_estimate'] ?? 0;
                $totalEstimatedCost += ($item['quantity_requested'] * $itemPrice);
            }

            // Create purchase request
            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requested_by' => $user->id,
                'department' => $request->department,
                'priority' => $request->priority,
                'request_date' => now()->toDateString(),
                'notes' => $request->notes,
                'status' => 'pending',
                'total_estimated_cost' => $totalEstimatedCost
            ]);

            // Create purchase request items
            foreach ($request->items as $itemData) {
                $item = Item::find($itemData['item_id']);
                $unitPrice = $itemData['unit_price_estimate'] ?? $item->cost_price ?? 0;
                
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity_requested'],
                    'unit_price_estimate' => $unitPrice,
                    'total_estimated_cost' => $itemData['quantity_requested'] * $unitPrice
                ]);
            }

            DB::commit();

            // Create notifications for supervisors
            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'New Purchase Request Submitted',
                    'message' => "New purchase request {$prNumber} has been submitted by " . $user->name . " for approval.",
                    'type' => 'approval_req',
                    'priority' => $request->priority ?? 'normal',
                    'action_url' => route('supervisor.approvals.purchase-requests') . '?pr=' . $purchaseRequest->id,
                    'metadata' => [
                        'pr_number' => $prNumber,
                        'request_id' => $purchaseRequest->id,
                        'requester' => $user->name,
                        'department' => $request->department,
                        'total_estimated_cost' => $totalEstimatedCost,
                        'priority' => $request->priority ?? 'normal'
                    ],
                    'created_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase request created successfully!',
                'pr_number' => $prNumber,
                'purchase_request_id' => $purchaseRequest->id
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
     * Get stock status for item
     */
    private function getStockStatus($item)
    {
        $currentStock = $item->currentStockRecord ? 
            $item->currentStockRecord->current_quantity : 0;
        $minStockLevel = $item->min_stock_level ?? 0;
        $reorderPoint = $item->reorder_point ?? 0;

        if ($currentStock <= 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= $reorderPoint) {
            return 'below_reorder';
        } elseif ($currentStock <= $minStockLevel) {
            return 'low_stock';
        } elseif ($currentStock >= ($item->max_stock_level ?? 0)) {
            return 'overstocked';
        } else {
            return 'good_stock';
        }
    }

    /**
     * Requisition history view
     */
    public function requisitionHistory(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get requisition history with filtering
            $purchaseRequests = PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit'
            ])
            ->where('requested_by', $user->id)
            ->when($request->status, function($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->when($request->department, function($query, $department) {
                if ($department !== 'all') {
                    $query->where('department', 'like', '%' . $department . '%');
                }
            })
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('pr_number', 'like', '%' . $search . '%')
                      ->orWhere('department', 'like', '%' . $search . '%')
                      ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

            // Calculate statistics
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

            return view('Employee.requisition.history', compact('purchaseRequests', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Error loading requisition history: ' . $e->getMessage());
            return view('Employee.requisition.history', [
                'purchaseRequests' => collect(),
                'stats' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'draft' => 0]
            ]);
        }
    }

    /**
     * Get item details for requisition (AJAX)
     */
    public function getRequisitionDetails($purchaseRequestId)
    {
        try {
            $purchaseRequest = PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit'
            ])->findOrFail($purchaseRequestId);

            $items = $purchaseRequest->purchaseRequestItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'quantity_requested' => (float) $item->quantity_requested,
                    'unit_symbol' => $item->item->unit->symbol ?? 'pcs',
                    'unit_price_estimate' => (float) ($item->unit_price_estimate ?? 0),
                    'total_estimated_cost' => (float) ($item->total_estimated_cost ?? 0),
                    'formatted_quantity' => number_format($item->quantity_requested, 2) . ' ' . ($item->item->unit->symbol ?? 'pcs'),
                    'formatted_unit_price' => '₱' . number_format($item->unit_price_estimate ?? 0, 2),
                    'formatted_total' => '₱' . number_format($item->total_estimated_cost ?? 0, 2)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchaseRequest->id,
                    'pr_number' => $purchaseRequest->pr_number,
                    'requested_by' => $purchaseRequest->requestedBy->name ?? 'Unknown',
                    'department' => $purchaseRequest->department,
                    'priority' => $purchaseRequest->priority,
                    'status' => $purchaseRequest->status,
                    'notes' => $purchaseRequest->notes,
                    'request_date' => $purchaseRequest->request_date,
                    'items' => $items,
                    'total_items' => $items->count(),
                    'total_estimated_cost' => (float) $purchaseRequest->total_estimated_cost,
                    'formatted_total' => '₱' . number_format($purchaseRequest->total_estimated_cost, 2)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching requisition details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch requisition details'
            ], 500);
        }
    }

    /**
     * Get items available for requisition (AJAX)
     */
    public function getItemsForRequisition(Request $request)
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
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            $items = $query->orderBy('name')
                ->limit(20)
                ->get()
                ->map(function($item) {
                    $currentStock = $item->currentStockRecord ? 
                        $item->currentStockRecord->current_quantity : 0;
                    
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'description' => $item->description,
                        'category' => $item->category->name ?? 'General',
                        'unit' => [
                            'symbol' => $item->unit->symbol ?? 'pcs'
                        ],
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'current_stock' => (float) $currentStock,
                        'min_stock_level' => (float) ($item->min_stock_level ?? 0),
                        'stock_status' => $this->getStockStatus($item),
                        'formatted_stock' => number_format($currentStock, 2) . ' ' . ($item->unit->symbol ?? 'pcs')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'total' => $items->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching items for requisition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }
}