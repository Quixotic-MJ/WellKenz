<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\Requisition;
use App\Models\StockMovement;
use App\Models\CurrentStock;
use App\Models\User;
use App\Models\Notification;
use App\Models\Category;
use App\Models\Unit;
use App\Models\RtvTransaction;
use App\Models\RtvItem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Display inventory dashboard home with auto-expiry notifications
     */
    public function home()
    {
        try {
            // Pending Purchase Orders
            $pendingPurchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->orderBy('expected_delivery_date', 'desc')
                ->limit(5)
                ->get();

            // Expiring batches with auto-notification check
            $expiringBatches = Batch::with(['item.unit', 'supplier'])
                ->whereIn('status', ['active', 'quarantine'])
                ->where(function($query) {
                    $query->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                        ->orWhereBetween('expiry_date', ['2024-01-01', '2024-12-31']);
                })
                ->orderBy('expiry_date')
                ->limit(5)
                ->get();

            // Auto-create expiry notifications for critical batches
            $this->createAutoExpiryNotifications($expiringBatches);

            // Two separate requisition widgets
            $pendingApprovalRequisitions = Requisition::with(['requestedBy', 'requisitionItems'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $readyForPickingRequisitions = Requisition::with(['requestedBy', 'requisitionItems'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            // Inventory calculations
            $inventoryValue = DB::table('current_stock')
                ->join('items', 'current_stock.item_id', '=', 'items.id')
                ->where('items.is_active', true)
                ->select(DB::raw('COALESCE(SUM(current_stock.current_quantity * current_stock.average_cost), 0) as total_value'))
                ->value('total_value') ?? 0;

            $lowStockItemsCount = DB::table('items')
                ->join('current_stock', 'items.id', '=', 'current_stock.item_id')
                ->where('items.is_active', true)
                ->whereRaw('current_stock.current_quantity <= items.min_stock_level')
                ->where('current_stock.current_quantity', '>', 0)
                ->count();

            $activeItemsCount = Item::where('is_active', true)->count();
            $todayMovementsCount = StockMovement::count();

            return view('Inventory.home', compact(
                'pendingPurchaseOrders',
                'expiringBatches',
                'pendingApprovalRequisitions',
                'readyForPickingRequisitions',
                'inventoryValue',
                'lowStockItemsCount',
                'activeItemsCount',
                'todayMovementsCount'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading inventory dashboard: ' . $e->getMessage());
            
            return view('Inventory.home', [
                'pendingPurchaseOrders' => collect(),
                'expiringBatches' => collect(),
                'pendingApprovalRequisitions' => collect(),
                'readyForPickingRequisitions' => collect(),
                'inventoryValue' => 0,
                'lowStockItemsCount' => 0,
                'activeItemsCount' => 0,
                'todayMovementsCount' => 0
            ]);
        }
    }

    /**
     * Automatically create notifications for expiring batches
     */
    private function createAutoExpiryNotifications($expiringBatches)
    {
        try {
            foreach ($expiringBatches as $batch) {
                $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
                
                // Only create notifications for batches expiring within 3 days
                if ($daysUntilExpiry <= 3) {
                    $this->notifyExpiringBatch($batch, $daysUntilExpiry);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error creating auto expiry notifications: ' . $e->getMessage());
        }
    }

    /**
     * Notify relevant users about expiring batch
     */
    private function notifyExpiringBatch($batch, $daysUntilExpiry)
    {
        try {
            // Check if notification already exists for this batch today
            $existingNotification = Notification::where('metadata->batch_id', $batch->id)
                ->whereDate('created_at', today())
                ->where('type', 'batch_expiry')
                ->first();

            if ($existingNotification) {
                return; // Notification already sent today
            }

            $priority = $this->getExpiryPriority($daysUntilExpiry);
            $message = $this->getExpiryMessage($batch, $daysUntilExpiry, $priority);

            // Notify Production Staff (employees)
            $productionUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            // Notify Supervisors
            $supervisorUsers = User::where('role', 'supervisor')
                ->where('is_active', true)
                ->get();

            $allUsers = $productionUsers->merge($supervisorUsers);

            foreach ($allUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => "🚨 {$priority} Priority: Item Expiring Soon",
                    'message' => $message,
                    'type' => 'batch_expiry',
                    'priority' => $priority,
                    'action_url' => '/inventory/batches?highlight=' . $batch->id,
                    'metadata' => [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'quantity' => $batch->quantity,
                        'unit' => $batch->item->unit->symbol,
                        'priority_level' => $priority
                    ]
                ]);
            }

            \Log::info("Auto expiry notification sent for batch {$batch->batch_number}", [
                'batch_id' => $batch->id,
                'days_until_expiry' => $daysUntilExpiry,
                'notified_users' => $allUsers->count(),
                'priority' => $priority
            ]);

        } catch (\Exception $e) {
            \Log::error('Error notifying expiring batch: ' . $e->getMessage());
        }
    }

    /**
     * Determine priority level based on days until expiry
     */
    private function getExpiryPriority($daysUntilExpiry)
    {
        if ($daysUntilExpiry <= 1) return 'urgent';
        if ($daysUntilExpiry <= 2) return 'high';
        if ($daysUntilExpiry <= 3) return 'normal';
        return 'low';
    }

    /**
     * Generate appropriate expiry message
     */
    private function getExpiryMessage($batch, $daysUntilExpiry, $priority)
    {
        $urgencyText = [
            'urgent' => 'EXPIRES TODAY',
            'high' => 'Expires tomorrow',
            'normal' => "Expires in {$daysUntilExpiry} days",
            'low' => "Expires in {$daysUntilExpiry} days"
        ];

        return "🕒 {$urgencyText[$priority]}: {$batch->item->name} (Batch: {$batch->batch_number})\n\n" .
               "📦 Quantity: {$batch->quantity} {$batch->item->unit->symbol}\n" .
               "📅 Expiry Date: " . Carbon::parse($batch->expiry_date)->format('M d, Y') . "\n" .
               "📍 Location: " . ($batch->location ?? 'Main Storage') . "\n\n" .
               "💡 Action Required: Please prioritize usage of this batch in production.";
    }

    public function pickBatch($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])->findOrFail($batchId);
            
            if ($batch->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch is not available for picking. Status: ' . $batch->status
                ], 400);
            }

            $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
            $isSampleData = $batch->expiry_date < now()->toDateString();

            DB::beginTransaction();

            // Use 'quarantine' status instead of 'reserved'
            $batch->update([
                'status' => 'quarantine', // This will work with your constraint
                'location' => 'FEFO_PRIORITY_ZONE',
                'updated_at' => now()
            ]);

            // Create FEFO reservation record
            $fefoNote = $isSampleData 
                ? "FEFO Priority (Sample Data)" 
                : "FEFO Priority - Expires in {$daysUntilExpiry} days";

            DB::table('stock_movements')->insert([
                'item_id' => $batch->item_id,
                'movement_type' => 'adjustment',
                'reference_number' => 'FEFO-' . $batch->batch_number . '-' . time(),
                'quantity' => 0,
                'unit_cost' => $batch->unit_cost,
                'total_cost' => 0,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'location' => 'FEFO_PRIORITY_ZONE',
                'notes' => $fefoNote,
                'user_id' => Auth::id(),
                'created_at' => now()
            ]);

            // Notify production team about FEFO reservation
            $this->notifyFefoReservation($batch, $daysUntilExpiry, $isSampleData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSampleData
                    ? "✅ FEFO Priority: Batch {$batch->batch_number} quarantined for priority usage! Production team notified."
                    : "✅ FEFO Priority: Batch {$batch->batch_number} quarantined for priority usage! Expires in {$daysUntilExpiry} days. Production team notified.",
                'is_sample_data' => $isSampleData,
                'batch_status' => 'quarantine'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error picking batch for FEFO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to quarantine batch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify production team about FEFO reservation
     */
    private function notifyFefoReservation($batch, $daysUntilExpiry, $isSampleData)
    {
        try {
            $productionUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            $message = $isSampleData
                ? "📍 FEFO QUARANTINE: {$batch->item->name} (Batch: {$batch->batch_number}) has been quarantined for priority FEFO usage. Please use this batch first in production."
                : "📍 FEFO QUARANTINE: {$batch->item->name} (Batch: {$batch->batch_number}) has been quarantined for priority FEFO usage. Expires in {$daysUntilExpiry} days. Please use this batch first in production.";

            foreach ($productionUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '🎯 FEFO Priority Batch Quarantined',
                    'message' => $message,
                    'type' => 'fefo_reservation',
                    'priority' => $daysUntilExpiry <= 2 ? 'high' : 'normal',
                    'action_url' => '/employee/recipes?highlight=' . $batch->item_id,
                    'metadata' => [
                        'batch_id' => $batch->id,
                        'item_id' => $batch->item_id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'reserved_by' => Auth::user()->name,
                        'status' => 'quarantine'
                    ]
                ]);
            }

            \Log::info("FEFO quarantine notification sent", [
                'batch_id' => $batch->id,
                'notified_production_users' => $productionUsers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error notifying FEFO quarantine: ' . $e->getMessage());
        }
    }

    /**
     * Display purchase requests for inventory staff
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
     * Get categories that have items (for RTV bulk operations)
     */
    public function getCategoriesForRtvBulk()
    {
        try {
            $categories = Category::where('is_active', true)
                ->whereHas('items', function($query) {
                    $query->where('is_active', true);
                })
                ->withCount(['items' => function($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
                ->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'items_count' => $category->items_count
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching categories for RTV bulk: ' . $e->getMessage());
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

    /**
     * Quick action: Start picking for requisition
     */
    public function startPicking($requisitionId)
    {
        try {
            // Use requisitionItems relationship (exists) instead of items (doesn't exist)
            $requisition = Requisition::with(['requisitionItems', 'requestedBy'])->findOrFail($requisitionId);
            
            if ($requisition->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Requisition is not approved for picking. Current status: ' . $requisition->status
                ], 400);
            }

            // Update requisition status to fulfilled
            $requisition->update([
                'status' => 'fulfilled',
                'fulfilled_by' => Auth::id(),
                'fulfilled_at' => now()
            ]);

            // Notify requesting employee
            try {
                Notification::create([
                    'user_id' => $requisition->requested_by,
                    'title' => 'Requisition Fulfilled',
                    'message' => "Requisition {$requisition->requisition_number} has been fulfilled and is ready for pickup.",
                    'type' => 'requisition_update',
                    'priority' => 'normal',
                    'action_url' => route('employee.requisitions.details', $requisition->id),
                    'metadata' => [
                        'requisition_number' => $requisition->requisition_number,
                        'requisition_status' => 'fulfilled',
                        'status_changed_by' => Auth::user()->name ?? 'Inventory',
                        'status_changed_at' => now()->toDateTimeString(),
                    ],
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee about fulfillment: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Picking started for requisition ' . $requisition->requisition_number . ' with ' . $requisition->requisitionItems->count() . ' items'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error starting picking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start picking: ' . $e->getMessage()
            ], 500);
        }
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

    public function receiveDelivery()
    {
        try {
            // Get purchase orders that are ready for delivery (sent, confirmed, partial)
            $purchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems.item.unit'])
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->orderBy('expected_delivery_date', 'asc')
                ->get();

            return view('Inventory.inbound.receive_delivery', compact('purchaseOrders'));

        } catch (\Exception $e) {
            \Log::error('Error loading receive delivery: ' . $e->getMessage());
            return view('Inventory.inbound.receive_delivery', ['purchaseOrders' => collect()]);
        }
    }

    /**
     * Get purchase order details for receiving
     */
    public function getPurchaseOrder($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with([
                'supplier',
                'purchaseOrderItems.item' => function($query) {
                    $query->with(['unit', 'category']);
                }
            ])->findOrFail($id);

            if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This purchase order is not available for receiving'
                ], 400);
            }

            $formattedItems = $purchaseOrder->purchaseOrderItems->map(function($item) {
                $isPerishable = $item->item->shelf_life_days > 0;
                
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'sku' => $item->item->item_code,
                    'quantity_ordered' => (float) $item->quantity_ordered,
                    'quantity_received' => (float) $item->quantity_received,
                    'quantity_remaining' => (float) $item->quantity_ordered - (float) $item->quantity_received,
                    'unit_price' => (float) $item->unit_price,
                    'unit_symbol' => $item->item->unit->symbol ?? 'pcs',
                    'is_perishable' => $isPerishable,
                    'shelf_life_days' => $item->item->shelf_life_days,
                    'category_name' => $item->item->category->name ?? 'General'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'supplier_name' => $purchaseOrder->supplier->name,
                    'supplier_code' => $purchaseOrder->supplier->supplier_code,
                    'order_date' => $purchaseOrder->order_date,
                    'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                    'status' => $purchaseOrder->status,
                    'items' => $formattedItems,
                    'total_items' => $formattedItems->count(),
                    'can_receive' => in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase order details'
            ], 500);
        }
    }

    /**
     * Process received delivery with comprehensive blind count methodology
     */
    public function processDelivery(Request $request)
    {
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'items' => 'required|array|min:1',
                'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
                'items.*.quantity_received' => 'required|numeric|min:0',
                'items.*.batch_number' => 'nullable|string|max:100',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.condition' => 'required|in:good,damaged,wet_stained,thawed,leaking',
                'items.*.receiving_notes' => 'nullable|string|max:500',
                'items.*.damage_description' => 'nullable|string|max:500',
                'items.*.estimated_expiry_days' => 'nullable|integer|min:1'
            ]);

            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])->findOrFail($request->purchase_order_id);
            $user = Auth::user();
            $allItemsComplete = true;
            $discrepancies = [];
            $quarantineItems = [];
            $newlyCreatedBatches = [];

            foreach ($request->items as $receivedItem) {
                $poItem = PurchaseOrderItem::findOrFail($receivedItem['purchase_order_item_id']);
                $item = $poItem->item;
                
                $quantityReceived = (float) $receivedItem['quantity_received'];
                $condition = $receivedItem['condition'];
                $batchNumber = $receivedItem['batch_number'] ?? null;
                $expiryDate = $receivedItem['expiry_date'] ?? null;
                $receivingNotes = $receivedItem['receiving_notes'] ?? '';
                $damageDescription = $receivedItem['damage_description'] ?? '';
                $estimatedExpiryDays = $receivedItem['estimated_expiry_days'] ?? null;

                // Skip if no quantity received
                if ($quantityReceived <= 0) {
                    continue;
                }

                // Update purchase order item received quantity
                $newReceivedQuantity = $poItem->quantity_received + $quantityReceived;
                DB::table('purchase_order_items')
                    ->where('id', $poItem->id)
                    ->update([
                        'quantity_received' => $newReceivedQuantity
                    ]);

                // Check for discrepancies (partial receipts)
                $orderedQuantity = (float) $poItem->quantity_ordered;
                if ($newReceivedQuantity < $orderedQuantity) {
                    $allItemsComplete = false;
                    $discrepancies[] = [
                        'item_name' => $item->name,
                        'ordered' => $orderedQuantity,
                        'received' => $quantityReceived,
                        'remaining' => $orderedQuantity - $newReceivedQuantity,
                        'unit' => $item->unit->symbol ?? 'pcs'
                    ];
                }

                // Auto-generate batch number if not provided
                if (!$batchNumber) {
                    $batchNumber = $this->generateBatchNumber($item, $purchaseOrder->supplier);
                }

                // Auto-calculate expiry date for perishable items
                if ($item->shelf_life_days > 0 && !$expiryDate) {
                    $baseExpiryDays = $estimatedExpiryDays ?? $item->shelf_life_days;
                    $expiryDate = now()->addDays($baseExpiryDays)->toDateString();
                }

                // Determine batch status based on quality condition
                $batchStatus = $this->determineBatchStatus($condition);
                if ($batchStatus === 'quarantine') {
                    $quarantineItems[] = $item->name;
                }

                // Create comprehensive batch record
                $batch = Batch::create([
                    'batch_number' => $batchNumber,
                    'item_id' => $item->id,
                    'quantity' => $quantityReceived,
                    'unit_cost' => $poItem->unit_price,
                    'manufacturing_date' => now()->toDateString(),
                    'expiry_date' => $expiryDate,
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'location' => 'Main Storage',
                    'status' => $batchStatus
                ]);

                $newlyCreatedBatches[] = [
                    'batch_number' => $batchNumber,
                    'item_name' => $item->name,
                    'quantity' => $quantityReceived,
                    'expiry_date' => $expiryDate,
                    'condition' => $condition
                ];

                // Create detailed stock movement with quality information
                $movementNotes = $this->buildMovementNotes(
                    $purchaseOrder, 
                    $condition, 
                    $receivingNotes, 
                    $damageDescription,
                    $quantityReceived,
                    $orderedQuantity
                );

                StockMovement::create([
                    'item_id' => $item->id,
                    'movement_type' => 'purchase',
                    'reference_number' => $purchaseOrder->po_number,
                    'quantity' => $quantityReceived,
                    'unit_cost' => $poItem->unit_price,
                    'total_cost' => $quantityReceived * $poItem->unit_price,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                    'location' => 'Main Storage',
                    'notes' => $movementNotes,
                    'user_id' => $user->id
                ]);

                // Update current stock using trigger (handled automatically)
                // $this->updateCurrentStock($item->id, $quantityReceived, $poItem->unit_price);
            }

            // Update purchase order status and actual delivery date
            $newStatus = $allItemsComplete ? 'completed' : 'partial';
            $purchaseOrder->update([
                'status' => $newStatus,
                'actual_delivery_date' => now()->toDateString()
            ]);

            // Create comprehensive notifications
            $this->createDeliveryNotifications($purchaseOrder, $user, $discrepancies, $quarantineItems, $newlyCreatedBatches, $newStatus);

            // Log delivery receipt for audit trail
            $this->logDeliveryReceipt($purchaseOrder, $user, $discrepancies, $quarantineItems);

            DB::commit();

            $responseMessage = $this->buildDeliveryResponseMessage($newStatus, $discrepancies, $quarantineItems, count($newlyCreatedBatches));

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'po_status' => $newStatus,
                'batches_created' => count($newlyCreatedBatches),
                'discrepancies_count' => count($discrepancies),
                'quarantine_items' => count($quarantineItems),
                'batch_labels_url' => '/inventory/inbound/labels?batches=' . implode(',', array_column($newlyCreatedBatches, 'batch_number')),
                'next_actions' => $this->getNextActions($newStatus, $quarantineItems, $discrepancies)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error processing delivery: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'purchase_order_id' => $request->purchase_order_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process delivery: ' . $e->getMessage(),
                'error_code' => 'DELIVERY_PROCESSING_FAILED'
            ], 500);
        }
    }

    /**
     * Search purchase orders by barcode/PO number
     */
    public function searchPurchaseOrder(Request $request)
    {
        try {
            $searchTerm = $request->get('search');
            
            \Log::info("Searching for PO: {$searchTerm}");
            
            if (!$searchTerm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a PO number or barcode'
                ], 400);
            }

            $purchaseOrder = PurchaseOrder::with(['supplier'])
                ->where('po_number', 'like', '%' . $searchTerm . '%')
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->first();

            \Log::info("Search results: " . ($purchaseOrder ? "Found PO {$purchaseOrder->po_number}" : "No PO found"));

            if (!$purchaseOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active purchase order found with that number'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'supplier_name' => $purchaseOrder->supplier->name,
                    'status' => $purchaseOrder->status
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error searching purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching purchase order'
            ], 500);
        }
    }

    /**
     * Get condition text for display
     */
    private function getConditionText($condition)
    {
        $conditions = [
            'good' => 'Good',
            'damaged' => 'Damaged',
            'wet_stained' => 'Wet/Stained',
            'thawed' => 'Thawed (Reject)',
            'leaking' => 'Leaking'
        ];

        return $conditions[$condition] ?? 'Unknown';
    }

    /**
     * Update current stock directly (as backup to trigger)
     */
    private function updateCurrentStock($itemId, $quantity, $unitCost)
    {
        try {
            $currentStock = CurrentStock::where('item_id', $itemId)->first();
            
            if ($currentStock) {
                // Update existing stock
                $newQuantity = $currentStock->current_quantity + $quantity;
                $newAverageCost = (($currentStock->current_quantity * $currentStock->average_cost) + ($quantity * $unitCost)) / $newQuantity;
                
                $currentStock->update([
                    'current_quantity' => $newQuantity,
                    'average_cost' => $newAverageCost,
                    'last_updated' => now()
                ]);
            } else {
                // Create new stock record
                CurrentStock::create([
                    'item_id' => $itemId,
                    'current_quantity' => $quantity,
                    'average_cost' => $unitCost,
                    'last_updated' => now()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating current stock: ' . $e->getMessage());
        }
    }

    /**
     * Generate automatic batch number based on item, supplier and date
     */
    private function generateBatchNumber($item, $supplier)
    {
        $prefix = 'BATCH';
        $itemCode = strtoupper(substr($item->item_code, 0, 3));
        $supplierCode = strtoupper(substr($supplier->supplier_code, 0, 3));
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$itemCode}-{$supplierCode}-{$date}-{$random}";
    }

    /**
     * Determine batch status based on quality condition
     */
    private function determineBatchStatus($condition)
    {
        // Quality conditions that require quarantine
        $quarantineConditions = ['damaged', 'wet_stained', 'thawed', 'leaking'];
        
        if (in_array($condition, $quarantineConditions)) {
            return 'quarantine';
        }
        
        return 'active';
    }

    /**
     * Build detailed movement notes with quality information
     */
    private function buildMovementNotes($purchaseOrder, $condition, $receivingNotes, $damageDescription, $quantityReceived, $orderedQuantity)
    {
        $notes = [];
        
        // Basic delivery information
        $notes[] = "Delivery received for PO: {$purchaseOrder->po_number}";
        
        // Quantity information
        if ($quantityReceived < $orderedQuantity) {
            $notes[] = "PARTIAL RECEIPT: {$quantityReceived} of {$orderedQuantity} units";
        } else {
            $notes[] = "Full receipt: {$quantityReceived} units";
        }
        
        // Quality condition
        $notes[] = "Condition: " . $this->getConditionText($condition);
        
        // Add receiving notes if provided
        if (!empty($receivingNotes)) {
            $notes[] = "Notes: {$receivingNotes}";
        }
        
        // Add damage description if applicable
        if (!empty($damageDescription) && $condition !== 'good') {
            $notes[] = "Damage details: {$damageDescription}";
        }
        
        // Add condition-specific notes
        $conditionNotes = $this->getConditionSpecificNotes($condition);
        if ($conditionNotes) {
            $notes[] = $conditionNotes;
        }
        
        return implode(' | ', $notes);
    }

    /**
     * Get condition-specific notes for detailed tracking
     */
    private function getConditionSpecificNotes($condition)
    {
        $conditionNotes = [
            'damaged' => 'REQUIRES DAMAGE ASSESSMENT - Contact supplier',
            'wet_stained' => 'MOISTURE DAMAGE - Check storage conditions',
            'thawed' => 'TEMPERATURE ABUSE - Temperature chain broken',
            'leaking' => 'PACKAGING FAILURE - Potential contamination risk'
        ];
        
        return $conditionNotes[$condition] ?? null;
    }

    /**
     * Create comprehensive notifications for delivery receipt
     */
    private function createDeliveryNotifications($purchaseOrder, $user, $discrepancies, $quarantineItems, $newlyCreatedBatches, $newStatus)
    {
        try {
            $notificationRecipients = User::whereIn('role', ['inventory', 'supervisor'])
                ->where('is_active', true)
                ->get();

            foreach ($notificationRecipients as $recipient) {
                // Main delivery completion notification
                Notification::create([
                    'user_id' => $recipient->id,
                    'title' => '📦 Delivery Received: ' . $purchaseOrder->po_number,
                    'message' => $this->buildDeliveryNotificationMessage($purchaseOrder, $newStatus, $discrepancies, $quarantineItems),
                    'type' => 'delivery_received',
                    'priority' => $this->getDeliveryPriority($discrepancies, $quarantineItems),
                    'action_url' => '/inventory/inbound/receive?po=' . $purchaseOrder->id,
                    'metadata' => [
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_number' => $purchaseOrder->po_number,
                        'supplier_name' => $purchaseOrder->supplier->name,
                        'status' => $newStatus,
                        'batches_created' => count($newlyCreatedBatches),
                        'discrepancies_count' => count($discrepancies),
                        'quarantine_items_count' => count($quarantineItems),
                        'received_by' => $user->name,
                        'received_at' => now()->toISOString()
                    ]
                ]);

                // Separate notification for quality issues
                if (!empty($quarantineItems)) {
                    Notification::create([
                        'user_id' => $recipient->id,
                        'title' => '⚠️ Quality Issues Detected',
                        'message' => "The following items require quality assessment: " . implode(', ', $quarantineItems),
                        'type' => 'quality_issue',
                        'priority' => 'high',
                        'action_url' => '/inventory/batches?status=quarantine',
                        'metadata' => [
                            'purchase_order_id' => $purchaseOrder->id,
                            'quarantine_items' => $quarantineItems,
                            'purchase_order_number' => $purchaseOrder->po_number
                        ]
                    ]);
                }

                // Separate notification for discrepancies
                if (!empty($discrepancies)) {
                    $discrepancySummary = collect($discrepancies)->map(function($disc) {
                        return "{$disc['item_name']}: {$disc['received']}/{$disc['ordered']} {$disc['unit']}";
                    })->implode('; ');

                    Notification::create([
                        'user_id' => $recipient->id,
                        'title' => '📋 Partial Receipts Detected',
                        'message' => "Partial receipts found in PO {$purchaseOrder->po_number}: " . $discrepancySummary,
                        'type' => 'partial_receipt',
                        'priority' => 'normal',
                        'action_url' => '/purchasing/po/' . $purchaseOrder->id,
                        'metadata' => [
                            'purchase_order_id' => $purchaseOrder->id,
                            'discrepancies' => $discrepancies,
                            'purchase_order_number' => $purchaseOrder->po_number
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error creating delivery notifications: ' . $e->getMessage());
        }
    }

    /**
     * Build delivery notification message
     */
    private function buildDeliveryNotificationMessage($purchaseOrder, $status, $discrepancies, $quarantineItems)
    {
        $message = "Delivery completed for {$purchaseOrder->po_number} from {$purchaseOrder->supplier->name}. ";
        $message .= "PO Status: {$status}. ";
        $message .= "Batches created: " . count($purchaseOrder->purchaseOrderItems) . ". ";

        if (!empty($quarantineItems)) {
            $message .= count($quarantineItems) . " items quarantined for quality assessment. ";
        }

        if (!empty($discrepancies)) {
            $message .= count($discrepancies) . " items partially received - follow up required. ";
        }

        return $message;
    }

    /**
     * Get delivery priority based on issues
     */
    private function getDeliveryPriority($discrepancies, $quarantineItems)
    {
        if (!empty($quarantineItems)) {
            return 'high';
        }
        
        if (!empty($discrepancies)) {
            return 'normal';
        }
        
        return 'low';
    }

    /**
     * Log delivery receipt for audit trail
     */
    private function logDeliveryReceipt($purchaseOrder, $user, $discrepancies, $quarantineItems)
    {
        try {
            $auditData = [
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_number' => $purchaseOrder->po_number,
                'supplier_name' => $purchaseOrder->supplier->name,
                'received_by' => $user->name,
                'received_at' => now()->toISOString(),
                'status' => $purchaseOrder->status,
                'total_items' => $purchaseOrder->purchaseOrderItems->count(),
                'discrepancies' => count($discrepancies),
                'quarantine_items' => count($quarantineItems),
                'batch_numbers' => $purchaseOrder->purchaseOrderItems->pluck('batch_number')->filter()->toArray()
            ];

            DB::table('audit_logs')->insert([
                'table_name' => 'purchase_orders',
                'record_id' => $purchaseOrder->id,
                'action' => 'UPDATE',
                'old_values' => json_encode(['status' => $purchaseOrder->getOriginal('status')]),
                'new_values' => json_encode(['status' => $purchaseOrder->status, 'delivery_data' => $auditData]),
                'user_id' => $user->id,
                'created_at' => now()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error logging delivery receipt: ' . $e->getMessage());
        }
    }

    /**
     * Build delivery response message
     */
    private function buildDeliveryResponseMessage($status, $discrepancies, $quarantineItems, $batchCount)
    {
        $message = "✅ Delivery processed successfully. ";
        $message .= "PO status updated to '{$status}'. ";
        $message .= "{$batchCount} batches created. ";

        if (!empty($quarantineItems)) {
            $message .= "⚠️ " . count($quarantineItems) . " items moved to quarantine for quality assessment. ";
        }

        if (!empty($discrepancies)) {
            $message .= "📋 " . count($discrepancies) . " items partially received - supplier follow-up required. ";
        }

        $message .= "Stock levels updated and notifications sent to team.";

        return $message;
    }

    /**
     * Get next actions based on delivery results
     */
    private function getNextActions($status, $quarantineItems, $discrepancies)
    {
        $actions = [];

        if (!empty($quarantineItems)) {
            $actions[] = [
                'type' => 'quality_check',
                'title' => 'Quality Assessment Required',
                'description' => 'Review and approve quarantined items',
                'url' => '/inventory/batches?status=quarantine',
                'priority' => 'high'
            ];
        }

        if (!empty($discrepancies)) {
            $actions[] = [
                'type' => 'supplier_followup',
                'title' => 'Follow up with Supplier',
                'description' => 'Contact supplier about partial deliveries',
                'url' => '/purchasing/po/' . request('purchase_order_id'),
                'priority' => 'normal'
            ];
        }

        if ($status === 'completed') {
            $actions[] = [
                'type' => 'print_labels',
                'title' => 'Print Batch Labels',
                'description' => 'Print labels for newly received items',
                'url' => '/inventory/inbound/labels',
                'priority' => 'low'
            ];
        }

        return $actions;
    }

    /**
     * Validate delivery data with comprehensive checks
     */
    public function validateDeliveryData(Request $request)
    {
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id'
            ]);

            $purchaseOrder = PurchaseOrder::with(['supplier', 'purchaseOrderItems.item'])->findOrFail($request->purchase_order_id);

            // Validate PO is in receivable status
            if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'PO is not in receivable status',
                    'valid' => false
                ]);
            }

            // Pre-validate all items for potential issues
            $validationResults = [];
            foreach ($purchaseOrder->purchaseOrderItems as $poItem) {
                $item = $poItem->item;
                $remaining = $poItem->quantity_ordered - $poItem->quantity_received;
                
                $validation = [
                    'purchase_order_item_id' => $poItem->id,
                    'item_name' => $item->name,
                    'item_code' => $item->item_code,
                    'quantity_ordered' => (float) $poItem->quantity_ordered,
                    'quantity_received' => (float) $poItem->quantity_received,
                    'quantity_remaining' => (float) $remaining,
                    'is_perishable' => $item->shelf_life_days > 0,
                    'shelf_life_days' => $item->shelf_life_days,
                    'unit_symbol' => $item->unit->symbol ?? 'pcs',
                    'warnings' => []
                ];

                // Check for potential issues
                if ($remaining <= 0) {
                    $validation['warnings'][] = 'Item already fully received';
                }

                if ($item->shelf_life_days > 0) {
                    $validation['warnings'][] = 'Perishable item - expiry date required';
                }

                // Check for temperature-sensitive items
                $categoryName = $item->category->name ?? '';
                if (stripos($categoryName, 'dairy') !== false || stripos($categoryName, 'frozen') !== false) {
                    $validation['warnings'][] = 'Temperature-sensitive item - check condition carefully';
                }

                $validationResults[] = $validation;
            }

            return response()->json([
                'success' => true,
                'valid' => true,
                'purchase_order' => [
                    'id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'supplier_name' => $purchaseOrder->supplier->name,
                    'status' => $purchaseOrder->status,
                    'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                    'items' => $validationResults
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error validating delivery data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate delivery data',
                'valid' => false
            ], 500);
        }
    }

    /**
     * Get receiving statistics for dashboard
     */
    public function getReceivingStatistics()
    {
        try {
            $user = Auth::user();
            
            // Today's receipts
            $todayReceipts = StockMovement::where('movement_type', 'purchase')
                ->whereDate('created_at', today())
                ->where('user_id', $user->id)
                ->count();

            // This week's receipts
            $weekReceipts = StockMovement::where('movement_type', 'purchase')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('user_id', $user->id)
                ->count();

            // Quality issues this month
            $qualityIssues = DB::table('stock_movements')
                ->join('items', 'stock_movements.item_id', '=', 'items.id')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->where('movement_type', 'purchase')
                ->whereMonth('stock_movements.created_at', now()->month)
                ->where('stock_movements.user_id', $user->id)
                ->where(function($query) {
                    $query->where('notes', 'like', '%damaged%')
                          ->orWhere('notes', 'like', '%wet_stained%')
                          ->orWhere('notes', 'like', '%thawed%')
                          ->orWhere('notes', 'like', '%leaking%');
                })
                ->count();

            // Partial receipts this month
            $partialReceipts = DB::table('purchase_order_items')
                ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->whereMonth('purchase_orders.updated_at', now()->month)
                ->where('purchase_order_items.quantity_received', '>', 0)
                ->whereColumn('purchase_order_items.quantity_received', '<', 'purchase_order_items.quantity_ordered')
                ->count();

            // Average processing time (simplified calculation)
            $avgProcessingTime = 15; // Placeholder - would need detailed timing data

            return response()->json([
                'success' => true,
                'data' => [
                    'today_receipts' => $todayReceipts,
                    'week_receipts' => $weekReceipts,
                    'quality_issues' => $qualityIssues,
                    'partial_receipts' => $partialReceipts,
                    'avg_processing_time_minutes' => $avgProcessingTime,
                    'performance_score' => $this->calculateReceivingPerformanceScore($todayReceipts, $qualityIssues, $partialReceipts)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting receiving statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Calculate receiving performance score
     */
    private function calculateReceivingPerformanceScore($receipts, $qualityIssues, $partialReceipts)
    {
        $baseScore = 100;
        
        // Deduct for quality issues (high penalty)
        $baseScore -= ($qualityIssues * 10);
        
        // Deduct for partial receipts (medium penalty)
        $baseScore -= ($partialReceipts * 5);
        
        // Ensure score stays within 0-100 range
        return max(0, min(100, $baseScore));
    }

    /**
     * Display fulfill requests page for inventory staff
     */
    public function fulfillRequests(Request $request)
    {
        try {
            // Get approved requisitions ready for picking
            $requisitions = Requisition::with([
                'requestedBy:id,name',
                'requisitionItems.item.unit',
                'requisitionItems.item.currentStockRecord'
            ])
            ->whereIn('status', ['approved', 'fulfilled'])
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->orderBy('request_date', 'asc')
            ->get();

            // If specific requisition is requested to be expanded
            $expandedRequisitionId = $request->get('expand');
            if ($expandedRequisitionId) {
                // Reorder to put the expanded one first
                $expandedRequisition = $requisitions->firstWhere('id', $expandedRequisitionId);
                if ($expandedRequisition) {
                    $requisitions = $requisitions->reject(function($req) use ($expandedRequisitionId) {
                        return $req->id == $expandedRequisitionId;
                    });
                    $requisitions->prepend($expandedRequisition);
                }
            }

            return view('Inventory.outbound.fullfill_request', compact('requisitions'));

        } catch (\Exception $e) {
            \Log::error('Error loading fulfill requests: ' . $e->getMessage());
            return view('Inventory.outbound.fullfill_request', ['requisitions' => collect()]);
        }
    }

    /**
     * Track item picking status (AJAX)
     */
    public function trackPicking(Request $request)
    {
        try {
            $request->validate([
                'requisition_item_id' => 'required|exists:requisition_items,id',
                'picked' => 'required|boolean'
            ]);

            // Store in session
            session()->put("picking.{$request->requisition_item_id}", $request->picked);

            return response()->json([
                'success' => true,
                'message' => 'Picking status updated'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error tracking picking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update picking status'
            ], 500);
        }
    }

    /**
     * Confirm issuance of requisition (AJAX)
     */
    public function confirmIssuance(Request $request)
    {
        try {
            \Log::info('Confirm issuance request received:', $request->all());
            
            // Use more flexible validation - updated to handle multi_batch_selections
            $validatedData = $request->validate([
                'requisition_id' => 'required|exists:requisitions,id',
                'multi_batch_selections' => 'sometimes|array',
                'processed_items' => 'sometimes|array', 
                'shortages' => 'sometimes|array',
                'partial_fulfillment' => 'sometimes|boolean',
                'auto_generate_pr' => 'sometimes|boolean'
            ]);

            \Log::info('Validation passed, starting transaction');
            DB::beginTransaction();

            $requisition = Requisition::with('requisitionItems.item')->findOrFail($request->requisition_id);
            $user = Auth::user();

            // Handle multi-batch selections from frontend
            $multiBatchSelections = $request->multi_batch_selections ?? [];
            $processedItems = $request->processed_items ?? [];
            $shortages = $request->shortages ?? [];
            $partialFulfillment = $request->partial_fulfillment ?? false;
            $autoGeneratePR = $request->auto_generate_pr ?? false;
            
            \Log::info('Processing requisition with multi-batch selections:', [
                'requisition_id' => $requisition->id,
                'requisition_number' => $requisition->requisition_number,
                'multi_batch_selections_count' => count($multiBatchSelections),
                'processed_items_count' => count($processedItems),
                'partial_fulfillment' => $partialFulfillment,
                'auto_generate_pr' => $autoGeneratePR,
                'multi_batch_selections' => $multiBatchSelections
            ]);

            foreach ($requisition->requisitionItems as $requisitionItem) {
                $item = $requisitionItem->item;
                
                \Log::info('Processing requisition item:', [
                    'requisition_item_id' => $requisitionItem->id,
                    'item_name' => $item->name,
                    'quantity_requested' => $requisitionItem->quantity_requested
                ]);

                // Check if we have multi-batch selections for this item
                $batchSelections = $multiBatchSelections[$requisitionItem->id] ?? [];
                
                if (empty($batchSelections)) {
                    // No batch selections provided, try auto-selection (legacy fallback)
                    \Log::info('No multi-batch selections, trying auto-selection for item:', ['item_name' => $item->name]);
                    
                    $batch = Batch::where('item_id', $item->id)
                        ->whereIn('status', ['active', 'quarantine'])
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->first();

                    if (!$batch) {
                        \Log::warning('No available batches found for item:', $item->name);
                        // Mark as fully backordered
                        $requisitionItem->update([
                            'quantity_issued' => 0
                        ]);
                        continue;
                    }

                    // Allow partial fulfillment - issue what we have available
                    $quantityToIssue = min($batch->quantity, $requisitionItem->quantity_requested);
                    
                    // Update batch quantity
                    $batch->decrement('quantity', $quantityToIssue);

                    // Update requisition item issued quantity
                    $requisitionItem->update([
                        'quantity_issued' => $quantityToIssue
                    ]);

                    // Create stock movement
                    StockMovement::create([
                        'item_id' => $item->id,
                        'movement_type' => 'transfer',
                        'reference_number' => $requisition->requisition_number,
                        'quantity' => -$quantityToIssue,
                        'unit_cost' => $batch->unit_cost,
                        'total_cost' => -($quantityToIssue * $batch->unit_cost),
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'location' => 'Kitchen/Production',
                        'notes' => "Auto-issuance for requisition {$requisition->requisition_number}. Issued: {$quantityToIssue}, Requested: {$requisitionItem->quantity_requested}",
                        'user_id' => $user->id
                    ]);

                    \Log::info('Auto-issuance completed:', [
                        'item_id' => $item->id,
                        'quantity_issued' => $quantityToIssue,
                        'quantity_requested' => $requisitionItem->quantity_requested
                    ]);

                } else {
                    // Process multi-batch selections from frontend
                    \Log::info('Processing multi-batch selections for item:', ['item_name' => $item->name]);
                    
                    $totalIssued = 0;
                    
                    foreach ($batchSelections as $batchSelection) {
                        $batch = Batch::find($batchSelection['batch_id']);
                        if (!$batch) {
                            \Log::warning('Selected batch not found:', $batchSelection['batch_id']);
                            continue;
                        }
                        
                        $quantityToIssue = (float) $batchSelection['quantity'];
                        $actualQuantity = min($quantityToIssue, $batch->quantity);
                        
                        // Update batch quantity
                        $batch->decrement('quantity', $actualQuantity);
                        $totalIssued += $actualQuantity;
                        
                        // Create stock movement for this batch
                        StockMovement::create([
                            'item_id' => $item->id,
                            'movement_type' => 'transfer',
                            'reference_number' => $requisition->requisition_number,
                            'quantity' => -$actualQuantity,
                            'unit_cost' => $batch->unit_cost,
                            'total_cost' => -($actualQuantity * $batch->unit_cost),
                            'batch_number' => $batch->batch_number,
                            'expiry_date' => $batch->expiry_date,
                            'location' => 'Kitchen/Production',
                            'notes' => "Partial issuance for requisition {$requisition->requisition_number} (Multi-batch). Issued: {$actualQuantity} from Batch {$batch->batch_number}",
                            'user_id' => $user->id
                        ]);
                        
                        \Log::info('Batch issuance completed:', [
                            'batch_id' => $batch->id,
                            'batch_number' => $batch->batch_number,
                            'quantity_issued' => $actualQuantity
                        ]);
                    }
                    
                    // Update requisition item issued quantity
                    $requisitionItem->update([
                        'quantity_issued' => $totalIssued
                    ]);
                    
                    \Log::info('Total multi-batch issuance for item:', [
                        'item_name' => $item->name,
                        'total_issued' => $totalIssued,
                        'requested' => $requisitionItem->quantity_requested
                    ]);
                }

            }

            // Update requisition status to fulfilled (even if partial)
            $requisition->update([
                'status' => 'fulfilled',
                'fulfilled_by' => $user->id,
                'fulfilled_at' => now()
            ]);

            // Create notifications about partial fulfillment if applicable
            if ($partialFulfillment && !empty($shortages)) {
                foreach ($shortages as $shortage) {
                    // Create purchase request for shortages if auto_generate_pr is true
                    if ($autoGeneratePR) {
                        $this->createAutoPurchaseRequest($shortage, $user);
                    }
                }
            }

            DB::commit();

            // Prepare response based on fulfillment type
            $response = [
                'success' => true,
                'message' => 'Requisition issuance confirmed successfully'
            ];

            if ($partialFulfillment) {
                $response['partial_fulfillment'] = true;
                $response['will_issue'] = count($processedItems);
                $response['will_backorder'] = count($shortages);
                $response['message'] .= ' - Partial fulfillment processed';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error confirming issuance: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm issuance: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Create automatic purchase request for shortages
     */
    private function createAutoPurchaseRequest($shortage, $user)
    {
        try {
            // Generate PR number
            $prNumber = 'PR-AUTO-' . date('Y') . '-' . str_pad(\App\Models\PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            // Find or create item
            $item = \App\Models\Item::find($shortage['itemId']);
            if (!$item) {
                \Log::warning('Item not found for auto PR:', $shortage['itemId']);
                return;
            }

            // Create purchase request
            $purchaseRequest = \App\Models\PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requested_by' => $user->id,
                'department' => 'Auto-Replenishment',
                'priority' => 'normal',
                'request_date' => now()->toDateString(),
                'notes' => "Auto-generated due to shortage in requisition fulfillment. Original request: {$shortage['requestedQty']}, Shortage: {$shortage['shortageQty']}",
                'status' => 'pending',
                'total_estimated_cost' => $shortage['shortageQty'] * ($item->cost_price ?? 0)
            ]);

            // Create purchase request item
            \App\Models\PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'item_id' => $item->id,
                'quantity_requested' => $shortage['shortageQty'],
                'unit_price_estimate' => $item->cost_price ?? 0,
                'total_estimated_cost' => $shortage['shortageQty'] * ($item->cost_price ?? 0)
            ]);

            \Log::info('Auto purchase request created:', [
                'pr_number' => $prNumber,
                'item_name' => $item->name,
                'shortage_qty' => $shortage['shortageQty']
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating auto purchase request: ' . $e->getMessage(), [
                'shortage_data' => $shortage,
                'user_id' => $user->id
            ]);
        }
    }







    /**
     * Show notifications page
     */
    public function notifications(Request $request)
    {
        // Get filter from request (default to 'all')
        $filter = $request->get('filter', 'all');
        
        // Build the notifications query
        $query = \App\Models\Notification::forCurrentUser($filter);
        
        // Paginate results
        $notifications = $query->paginate(20);
        
        // Get notification statistics for the current user
        $stats = [
            'total' => \App\Models\Notification::forCurrentUser()->count(),
            'unread' => \App\Models\Notification::forCurrentUser('unread')->count(),
            'high_priority' => \App\Models\Notification::forCurrentUser('high')->count(),
            'urgent' => \App\Models\Notification::forCurrentUser('urgent')->count(),
        ];

        return view('Inventory.notification', compact('notifications', 'stats', 'filter'));
    }

    /**
     * Get notification statistics for AJAX updates
     */
    public function getNotificationStats()
    {
        $stats = [
            'total' => \App\Models\Notification::forCurrentUser()->count(),
            'unread' => \App\Models\Notification::forCurrentUser('unread')->count(),
            'high_priority' => \App\Models\Notification::forCurrentUser('high')->count(),
            'urgent' => \App\Models\Notification::forCurrentUser('urgent')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Mark specific notification as read
     */
    public function markNotificationAsRead(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark specific notification as unread
     */
    public function markNotificationAsUnread(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        try {
            \Log::info('Mark all read called for user: ' . auth()->id());
            
            $query = \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false);
            
            $count = $query->count();
            \Log::info("Found {$count} unread notifications to mark as read");
            
            $query->update(['is_read' => true]);
            
            \Log::info("Marked {$count} notifications as read");
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in markAllNotificationsAsRead: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
     /* Display batch labels printing interface
     */
    /**
     * Display batch logs interface
     */
    public function batchLogs(Request $request)
    {
        try {
            // Build query for all batch records
            $query = Batch::with(['item.unit', 'supplier', 'item.category'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                });

            // Apply filters based on request parameters
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('category_id') && $request->category_id !== 'all') {
                $query->whereHas('item.category', function($q) use ($request) {
                    $q->where('id', $request->category_id);
                });
            }

            // Date range filters
            if ($request->has('date_range') && $request->date_range !== 'all') {
                $now = now();
                switch ($request->date_range) {
                    case 'today':
                        $query->whereDate('created_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                        break;
                    case 'quarter':
                        $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                        break;
                }
            }

            // Advanced date filters
            if ($request->has('manufacturing_date_from') && $request->manufacturing_date_from) {
                $query->whereDate('manufacturing_date', '>=', $request->manufacturing_date_from);
            }
            if ($request->has('manufacturing_date_to') && $request->manufacturing_date_to) {
                $query->whereDate('manufacturing_date', '<=', $request->manufacturing_date_to);
            }
            if ($request->has('expiry_date_from') && $request->expiry_date_from) {
                $query->whereDate('expiry_date', '>=', $request->expiry_date_from);
            }
            if ($request->has('expiry_date_to') && $request->expiry_date_to) {
                $query->whereDate('expiry_date', '<=', $request->expiry_date_to);
            }

            // Supplier filter
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Item type filter
            if ($request->has('item_type') && $request->item_type) {
                $query->whereHas('item', function($q) use ($request) {
                    $q->where('item_type', $request->item_type);
                });
            }

            // Quantity filters
            if ($request->has('min_quantity') && $request->min_quantity) {
                $query->where('quantity', '>=', $request->min_quantity);
            }
            if ($request->has('max_quantity') && $request->max_quantity) {
                $query->where('quantity', '<=', $request->max_quantity);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('batch_number', 'like', '%' . $search . '%')
                      ->orWhereHas('item', function($itemQuery) use ($search) {
                          $itemQuery->where('name', 'like', '%' . $search . '%')
                                   ->orWhere('item_code', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                          $supplierQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            // Sorting
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            
            $allowedSortFields = ['batch_number', 'item_name', 'quantity', 'manufacturing_date', 'supplier', 'status', 'created_at'];
            if (in_array($sortField, $allowedSortFields)) {
                switch ($sortField) {
                    case 'item_name':
                        $query->join('items', 'batches.item_id', '=', 'items.id')
                              ->orderBy('items.name', $sortOrder)
                              ->select('batches.*');
                        break;
                    case 'supplier':
                        $query->join('suppliers', 'batches.supplier_id', '=', 'suppliers.id')
                              ->orderBy('suppliers.name', $sortOrder)
                              ->select('batches.*');
                        break;
                    default:
                        $query->orderBy($sortField, $sortOrder);
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $batches = $query->paginate(15)->withQueryString();

            // Get filter options
            $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            
            // Get batch statistics
            $stats = [
                'total' => Batch::has('item')->count(),
                'active' => Batch::has('item')->where('status', 'active')->count(),
                'quarantine' => Batch::has('item')->where('status', 'quarantine')->count(),
                'expired' => Batch::has('item')->where('status', 'expired')->count(),
                'consumed' => Batch::has('item')->where('status', 'consumed')->count(),
                'expiring_soon' => Batch::has('item')
                    ->whereIn('status', ['active', 'quarantine'])
                    ->whereBetween('expiry_date', [now(), now()->addDays(7)])
                    ->count(),
            ];

            return view('Inventory.inbound.batch_logs', compact('batches', 'categories', 'suppliers', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Error loading batch logs: ' . $e->getMessage());
            return view('Inventory.inbound.batch_logs', [
                'batches' => collect(),
                'categories' => collect(),
                'suppliers' => collect(),
                'stats' => ['total' => 0, 'active' => 0, 'quarantine' => 0, 'expired' => 0, 'consumed' => 0, 'expiring_soon' => 0]
            ]);
        }
    }

    /**
     * Edit batch form
     */
    public function editBatch($batchId)
    {
        // This would typically redirect to an edit form
        // For now, we'll return a simple response
        return redirect()->route('inventory.inbound.batch-logs')
                        ->with('info', 'Batch editing functionality not yet implemented.');
    }

    /**
     * Update batch status
     */
    public function updateBatchStatus(Request $request, $batchId)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,quarantine,expired,consumed'
            ]);

            $batch = Batch::findOrFail($batchId);
            $oldStatus = $batch->status;
            $batch->status = $request->status;
            $batch->save();

            // Log the status change
            \Log::info("Batch status updated", [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch status updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating batch status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update batch status'
            ], 500);
        }
    }

    /**
     * Export batch logs
     */
    public function exportBatchLogs(Request $request)
    {
        try {
            // This would typically generate an Excel/CSV file
            // For now, we'll return a simple response
            return response()->json([
                'success' => true,
                'message' => 'Export functionality not yet implemented'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting batch logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export batch logs'
            ], 500);
        }
    }

    /**
     * Get batch details for printing
     */
    public function getBatchForPrint($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])
                ->where('id', $batchId)
                ->whereIn('status', ['active', 'quarantine'])
                ->first();

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or not available for printing'
                ], 404);
            }

            // Generate QR code data
            $qrCodeData = [
                'batch_number' => $batch->batch_number,
                'item_name' => $batch->item->name,
                'item_code' => $batch->item->item_code,
                'quantity' => $batch->quantity,
                'unit' => $batch->item->unit->symbol ?? 'pcs',
                'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                'supplier' => $batch->supplier->name ?? 'N/A',
                'location' => $batch->location ?? 'Main Storage',
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'item_code' => $batch->item->item_code,
                        'sku' => $batch->item->item_code,
                        'quantity' => (float) $batch->quantity,
                        'unit_symbol' => $batch->item->unit->symbol ?? 'pcs',
                        'manufacturing_date' => $batch->manufacturing_date?->format('M d, Y'),
                        'expiry_date' => $batch->expiry_date?->format('M d, Y'),
                        'supplier_name' => $batch->supplier->name ?? 'N/A',
                        'location' => $batch->location ?? 'Main Storage',
                        'status' => $batch->status,
                        'is_perishable' => $batch->item->shelf_life_days > 0,
                        'days_until_expiry' => $batch->expiry_date ? now()->diffInDays($batch->expiry_date, false) : null,
                    ],
                    'qr_code_data' => json_encode($qrCodeData)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting batch for print: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get batch details'
            ], 500);
        }
    }

    /**
     * Delete specific notification
     */
    public function deleteNotification(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Bulk operations on notifications
     */
    public function bulkNotificationOperations(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_unread,delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $notificationIds = $request->notification_ids;
        $action = $request->action;

        // Ensure all notifications belong to the current user
        $notifications = \App\Models\Notification::whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->get();

        if ($notifications->count() !== count($notificationIds)) {
            return response()->json(['success' => false, 'message' => 'Some notifications not found or unauthorized'], 403);
        }

        switch ($action) {
            case 'mark_read':
                \App\Models\Notification::markMultipleAsRead($notificationIds);
                $message = 'Notifications marked as read';
                break;
            case 'mark_unread':
                foreach ($notifications as $notification) {
                    $notification->markAsUnread();
                }
                $message = 'Notifications marked as unread';
                break;
            case 'delete':
                \App\Models\Notification::whereIn('id', $notificationIds)
                    ->where('user_id', auth()->id())
                    ->delete();
                $message = 'Notifications deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get border color class (alias for getPriorityColorClass for blade compatibility)
     */
    public function getBorderColorClass(): string
    {
        return $this->getPriorityColorClass();
    }

    /**
     * Get icon type for blade template compatibility
     */
    public function getIconType(): string
    {
        $iconClass = $this->getIconClass();
        $parts = explode(' ', $iconClass);
        // Return the icon classes without the background color
        return $parts[0] . ' ' . $parts[1] . ' ' . $parts[2];
    }

    /**
     * Get action button color class
     */
    public function getActionButtonClass(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-600 hover:bg-red-700',
            'high' => 'bg-amber-600 hover:bg-amber-700',
            'normal' => 'bg-blue-600 hover:bg-blue-700',
            'low' => 'bg-gray-600 hover:bg-gray-700',
            default => 'bg-chocolate hover:bg-chocolate-dark'
        };
    }

    /**
     * Get unread dot color
     */
    public function getUnreadDotColor(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-500',
            'high' => 'bg-amber-500',
            'normal' => 'bg-blue-500',
            'low' => 'bg-gray-400',
            default => 'bg-chocolate'
        };
    }

    /**
     * Get the icon background color class only
     */
    public function getIconBackgroundClass(): string
    {
        $iconClass = $this->getIconClass();
        $parts = explode(' ', $iconClass);
        return $parts[3] ?? 'bg-gray-100';
    }
    
    /**
     *Process batch labels printing
     */
    public function printBatchLabelsProcess(Request $request)
    {
        try {
            $request->validate([
                'batch_selections' => 'required|array|min:1',
                'batch_selections.*.batch_id' => 'required|exists:batches,id',
                'batch_selections.*.quantity' => 'required|integer|min:1|max:1000'
            ]);

            $user = Auth::user();
            $printedBatches = [];
            $errors = [];

            foreach ($request->batch_selections as $selection) {
                try {
                    $batch = Batch::with(['item.unit', 'supplier'])
                        ->where('id', $selection['batch_id'])
                        ->whereIn('status', ['active', 'quarantine'])
                        ->first();

                    if (!$batch) {
                        $errors[] = "Batch not found: ID {$selection['batch_id']}";
                        continue;
                    }

                    $quantity = (int) $selection['quantity'];

                    // Create print job record (you might want to create a print_jobs table)
                    $printJob = [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'quantity' => $quantity,
                        'printed_by' => $user->id,
                        'printed_at' => now(),
                        'qr_code_data' => json_encode([
                            'batch_number' => $batch->batch_number,
                            'item_name' => $batch->item->name,
                            'item_code' => $batch->item->item_code,
                            'quantity' => $batch->quantity,
                            'unit' => $batch->item->unit->symbol ?? 'pcs',
                            'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                            'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                            'supplier' => $batch->supplier->name ?? 'N/A',
                        ])
                    ];

                    $printedBatches[] = $printJob;

                    // Log the printing activity
                    \Log::info("Batch labels printed", [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $quantity,
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "Error processing batch ID {$selection['batch_id']}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($printedBatches) . ' batch label jobs created successfully',
                'printed_count' => count($printedBatches),
                'error_count' => count($errors),
                'errors' => $errors,
                'print_jobs' => $printedBatches,
                'print_ready' => true // This would trigger the browser print dialog
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing batch labels printing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process batch labels printing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display batch lookup page
     */
    public function batchLookup()
    {
        try {
            // Get recent active batches for initial display
            $recentBatches = Batch::with(['item.unit', 'supplier'])
                ->whereIn('status', ['active', 'quarantine'])
                ->whereHas('item', function($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('Inventory.stock_management.batch_lookup', compact('recentBatches'));

        } catch (\Exception $e) {
            \Log::error('Error loading batch lookup: ' . $e->getMessage());
            return view('Inventory.stock_management.batch_lookup', ['recentBatches' => collect()]);
        }
    }

    /**
     * Search batches by various criteria
     */
    public function searchBatches(Request $request)
    {
        try {
            $request->validate([
                'search' => 'required|string|min:1'
            ]);

            $searchTerm = trim($request->search);
            
            $query = Batch::with(['item.unit', 'supplier'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                });

            // Search by batch number, item name, item code, or barcode
            $query->where(function($q) use ($searchTerm) {
                $q->where('batch_number', 'ilike', "%{$searchTerm}%")
                  ->orWhereHas('item', function($itemQuery) use ($searchTerm) {
                      $itemQuery->where('name', 'ilike', "%{$searchTerm}%")
                               ->orWhere('item_code', 'ilike', "%{$searchTerm}%")
                               ->orWhere('barcode', 'ilike', "%{$searchTerm}%");
                  })
                  ->orWhereHas('supplier', function($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'ilike', "%{$searchTerm}%");
                  });
            });

            // Filter by status if specified
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by expiry status if specified
            if ($request->has('expiry_filter') && $request->expiry_filter !== 'all') {
                $now = now();
                switch ($request->expiry_filter) {
                    case 'expired':
                        $query->where('expiry_date', '<', $now->toDateString());
                        break;
                    case 'expiring_soon':
                        $query->whereBetween('expiry_date', [
                            $now->toDateString(),
                            $now->copy()->addDays(7)->toDateString()
                        ]);
                        break;
                    case 'no_expiry':
                        $query->whereNull('expiry_date');
                        break;
                }
            }

            $batches = $query->orderByRaw('CASE WHEN expiry_date IS NOT NULL THEN expiry_date ELSE \'9999-12-31\'::date END ASC')
                           ->orderBy('created_at', 'desc')
                           ->limit(20)
                           ->get();

            // Transform batches for display
            $transformedBatches = $batches->map(function($batch) {
                return $this->transformBatchForDisplay($batch);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedBatches,
                'total' => $transformedBatches->count(),
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            \Log::error('Error searching batches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search batches: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get batch details by ID
     */
    public function getBatchDetails($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])
                ->whereHas('item', function($query) {
                    $query->where('is_active', true);
                })
                ->findOrFail($batchId);

            return response()->json([
                'success' => true,
                'data' => $this->transformBatchForDisplay($batch)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting batch details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }
    }

    /**
     * Transform batch data for display
     */
    private function transformBatchForDisplay($batch)
    {
        $now = now();
        $expiryDate = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date) : null;
        $manufacturingDate = $batch->manufacturing_date ? \Carbon\Carbon::parse($batch->manufacturing_date) : null;
        
        // Calculate expiry status
        $expiryStatus = 'no_expiry';
        $expiryDays = null;
        $isExpiringSoon = false;
        $isExpired = false;

        if ($expiryDate) {
            $expiryDays = $now->diffInDays($expiryDate, false);
            $isExpired = $expiryDays < 0;
            $isExpiringSoon = !$isExpired && $expiryDays <= 7;
            
            if ($isExpired) {
                $expiryStatus = 'expired';
            } elseif ($isExpiringSoon) {
                $expiryStatus = 'expiring_soon';
            } else {
                $expiryStatus = 'active';
            }
        }

        // Determine status badge
        $statusBadge = match($batch->status) {
            'active' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Active'],
            'quarantine' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Quarantine'],
            'expired' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Expired'],
            'consumed' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Consumed'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($batch->status)]
        };

        // Add expiry warning for status badge
        if ($isExpiringSoon && $batch->status === 'active') {
            $statusBadge = ['class' => 'bg-red-100 text-red-800 animate-pulse', 'text' => 'Expiring Soon'];
        }

        return [
            'id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'item' => [
                'id' => $batch->item->id,
                'name' => $batch->item->name,
                'item_code' => $batch->item->item_code,
                'barcode' => $batch->item->barcode,
                'unit' => [
                    'symbol' => $batch->item->unit->symbol ?? 'pcs'
                ]
            ],
            'supplier' => [
                'name' => $batch->supplier->name ?? 'N/A'
            ],
            'quantity' => (float) $batch->quantity,
            'unit_cost' => (float) $batch->unit_cost,
            'manufacturing_date' => $manufacturingDate ? $manufacturingDate->format('M d, Y') : 'N/A',
            'expiry_date' => $expiryDate ? $expiryDate->format('M d, Y') : 'No Expiry',
            'expiry_date_raw' => $expiryDate ? $expiryDate->format('Y-m-d') : null,
            'location' => $batch->location ?? 'Main Storage',
            'status' => $batch->status,
            'status_badge' => $statusBadge,
            'expiry_status' => $expiryStatus,
            'expiry_days' => $expiryDays,
            'is_expired' => $isExpired,
            'is_expiring_soon' => $isExpiringSoon,
            'created_at' => $batch->created_at->format('M d, Y H:i'),
            'icon' => $this->getBatchIcon($batch->item, $batch->status),
            'priority_color' => $this->getBatchPriorityColor($batch, $isExpired, $isExpiringSoon)
        ];
    }

    /**
     * Get icon for batch based on item type and status
     */
    private function getBatchIcon($item, $status)
    {
        $itemType = $item->item_type ?? 'supply';
        
        $iconMap = [
            'raw_material' => ['class' => 'fas fa-seedling', 'bg' => 'bg-green-100', 'color' => 'text-green-700'],
            'finished_good' => ['class' => 'fas fa-birthday-cake', 'bg' => 'bg-purple-100', 'color' => 'text-purple-700'],
            'semi_finished' => ['class' => 'fas fa-cookie-bite', 'bg' => 'bg-orange-100', 'color' => 'text-orange-700'],
            'supply' => ['class' => 'fas fa-box', 'bg' => 'bg-blue-100', 'color' => 'text-blue-700'],
        ];

        $baseIcon = $iconMap[$itemType] ?? $iconMap['supply'];
        
        // Add status-based modifications
        if ($status === 'quarantine') {
            $baseIcon['color'] = 'text-yellow-700';
        } elseif ($status === 'expired') {
            $baseIcon['color'] = 'text-red-700';
        }
        
        return $baseIcon;
    }

    /**
     * Get priority color for batch border
     */
    private function getBatchPriorityColor($batch, $isExpired, $isExpiringSoon)
    {
        if ($isExpired) {
            return 'border-red-500';
        } elseif ($isExpiringSoon) {
            return 'border-yellow-500';
        } elseif ($batch->status === 'quarantine') {
            return 'border-yellow-500';
        } else {
            return 'border-green-500';
        }
    }

    // ============================================================================
    // RTV (Return to Vendor) Methods
    // ============================================================================

    /**
     * Display the RTV (Return to Vendor) Dock Log
     */
    public function indexRtv(Request $request)
    {
        try {
            // Build the query for RTV transactions with relationships
            $query = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->orderBy('created_at', 'desc');

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('rtv_number', 'ilike', "%{$search}%")
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'ilike', "%{$search}%");
                      })
                      ->orWhere('notes', 'ilike', "%{$search}%")
                      ->orWhereHas('rtvItems', function($rtvi) use ($search) {
                          $rtvi->where('reason', 'ilike', "%{$search}%")
                               ->orWhereHas('item', function($itemq) use ($search) {
                                   $itemq->where('name', 'ilike', "%{$search}%")
                                         ->orWhere('item_code', 'ilike', "%{$search}%");
                               });
                      });
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Apply supplier filter
            if ($request->has('supplier_id') && $request->supplier_id !== 'all') {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Apply date range filter
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('return_date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('return_date', '<=', $request->date_to);
            }

            // Paginate results
            $rtvRecords = $query->paginate($request->get('per_page', 15))->withQueryString();

            // Get summary statistics
            $summary = [
                'total_transactions' => RtvTransaction::count(),
                'pending_transactions' => RtvTransaction::where('status', 'pending')->count(),
                'processed_transactions' => RtvTransaction::where('status', 'processed')->count(),
                'completed_transactions' => RtvTransaction::where('status', 'completed')->count(),
                'total_value' => RtvTransaction::sum('total_value'),
            ];

            // Get suppliers for filter dropdown
            $suppliers = Supplier::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('Inventory.inbound.RTV', compact('rtvRecords', 'summary', 'suppliers'));

        } catch (\Exception $e) {
            \Log::error('Error loading RTV records: ' . $e->getMessage());
            
            return view('Inventory.inbound.RTV', [
                'rtvRecords' => collect(),
                'summary' => [
                    'total_transactions' => 0,
                    'pending_transactions' => 0,
                    'processed_transactions' => 0,
                    'completed_transactions' => 0,
                    'total_value' => 0,
                ],
                'suppliers' => collect()
            ]);
        }
    }

    /**
     * Get items for RTV creation (AJAX)
     */
    public function getItemsForRtv(Request $request)
    {
        try {
            $query = Item::with(['unit', 'currentStockRecord', 'supplierItems.supplier'])
                ->where('is_active', true);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('item_code', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            // Apply category filter
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // Get total count for pagination
            $total = $query->count();

            // Apply pagination
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            $offset = ($page - 1) * $perPage;

            $items = $query->orderBy('name')
                ->offset($offset)
                ->limit($perPage)
                ->get()
                ->map(function($item) {
                    $currentStock = $item->currentStockRecord ? 
                        $item->currentStockRecord->current_quantity : 0;
                    
                    return [
                        'id' => $item->id,
                        'item_code' => $item->item_code ?? 'N/A',
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'unit' => [
                            'id' => $item->unit->id ?? 0,
                            'symbol' => $item->unit->symbol ?? 'pcs'
                        ],
                        'current_stock' => (float) $currentStock,
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'has_suppliers' => $item->supplierItems->count() > 0,
                        'suppliers' => $item->supplierItems->take(3)->map(function($supplierItem) {
                            return [
                                'id' => $supplierItem->supplier->id,
                                'name' => $supplierItem->supplier->name,
                                'unit_price' => (float) $supplierItem->unit_price
                            ];
                        })->values()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching items for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get suppliers for RTV creation (AJAX)
     */
    public function getSuppliersForRtv(Request $request)
    {
        try {
            $query = Supplier::where('is_active', true)
                ->with(['supplierItems.item']);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('supplier_code', 'ilike', "%{$search}%")
                      ->orWhere('contact_person', 'ilike', "%{$search}%");
                });
            }

            $suppliers = $query->orderBy('name')
                ->limit(15)
                ->get()
                ->map(function($supplier) {
                    return [
                        'id' => $supplier->id,
                        'supplier_code' => $supplier->supplier_code,
                        'name' => $supplier->name,
                        'contact_person' => $supplier->contact_person,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'city' => $supplier->city ?? '',
                        'items_supplied' => $supplier->supplierItems->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'total' => $suppliers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching suppliers for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get purchase orders for supplier (AJAX)
     */
    public function getPurchaseOrdersForRtv(Request $request)
    {
        try {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id'
            ]);

            $purchaseOrders = PurchaseOrder::with(['purchaseOrderItems.item.unit'])
                ->where('supplier_id', $request->supplier_id)
                ->whereIn('status', ['sent', 'confirmed', 'partial', 'completed'])
                ->orderBy('order_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'order_date' => $po->order_date->format('Y-m-d'),
                        'expected_delivery_date' => $po->expected_delivery_date?->format('Y-m-d'),
                        'status' => $po->status,
                        'total_items' => $po->purchaseOrderItems->count(),
                        'total_amount' => (float) $po->grand_total
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $purchaseOrders,
                'total' => $purchaseOrders->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching purchase orders for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase orders',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Store a new RTV transaction
     */
    public function storeRtv(Request $request)
    {
        try {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'purchase_order_id' => 'nullable|exists:purchase_orders,id',
                'return_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity_returned' => 'required|numeric|min:0.001',
                'items.*.unit_cost' => 'required|numeric|min:0.01',
                'items.*.reason' => 'required|string|max:500'
            ]);

            DB::beginTransaction();

            $user = Auth::user();

            // Generate RTV number
            $rtvCount = RtvTransaction::whereYear('created_at', now()->year)->count();
            $rtvNumber = 'RTV-' . now()->format('Y') . '-' . str_pad($rtvCount + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total value
            $totalValue = 0;
            foreach ($request->items as $item) {
                $totalValue += ($item['quantity_returned'] * $item['unit_cost']);
            }

            // Create RTV transaction
            $rtv = RtvTransaction::create([
                'rtv_number' => $rtvNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'return_date' => $request->return_date,
                'status' => 'pending',
                'total_value' => $totalValue,
                'notes' => $request->notes,
                'created_by' => $user->id
            ]);

            // Create RTV items
            foreach ($request->items as $itemData) {
                RtvItem::create([
                    'rtv_id' => $rtv->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_returned' => $itemData['quantity_returned'],
                    'unit_cost' => $itemData['unit_cost'],
                    'reason' => $itemData['reason']
                ]);

                // Create stock movement for return
                StockMovement::create([
                    'item_id' => $itemData['item_id'],
                    'movement_type' => 'return',
                    'reference_number' => $rtvNumber,
                    'quantity' => -$itemData['quantity_returned'], // Negative for outgoing stock
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => -($itemData['quantity_returned'] * $itemData['unit_cost']),
                    'notes' => "RTV Return - Reason: {$itemData['reason']}",
                    'user_id' => $user->id
                ]);
            }

            // Create notification for relevant users
            $this->createRtvNotifications($rtv, $user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RTV transaction created successfully',
                'rtv_number' => $rtvNumber,
                'rtv_id' => $rtv->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating RTV transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create RTV transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RTV transaction details (AJAX)
     */
    public function getRtvDetails($id)
    {
        try {
            $rtv = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->findOrFail($id);

            $formattedItems = $rtv->rtvItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'quantity_returned' => (float) $item->quantity_returned,
                    'unit_symbol' => $item->item->unit->symbol ?? 'pcs',
                    'unit_cost' => (float) $item->unit_cost,
                    'total_cost' => (float) $item->total_cost,
                    'reason' => $item->reason,
                    'formatted_quantity' => $item->formatted_quantity,
                    'formatted_unit_cost' => $item->formatted_unit_cost,
                    'formatted_total_cost' => $item->formatted_total_cost
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $rtv->id,
                    'rtv_number' => $rtv->rtv_number,
                    'supplier_name' => $rtv->supplier->name,
                    'supplier_code' => $rtv->supplier->supplier_code,
                    'po_number' => $rtv->purchaseOrder ? $rtv->purchaseOrder->po_number : null,
                    'return_date' => $rtv->return_date->format('Y-m-d'),
                    'return_date_formatted' => $rtv->return_date_formatted,
                    'status' => $rtv->status,
                    'status_badge' => $rtv->status_badge,
                    'total_value' => (float) $rtv->total_value,
                    'formatted_total_value' => $rtv->formatted_total_value,
                    'notes' => $rtv->notes,
                    'created_by' => $rtv->createdBy->name ?? 'Unknown',
                    'created_at' => $rtv->created_at->format('Y-m-d H:i:s'),
                    'items' => $formattedItems,
                    'total_items' => $formattedItems->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching RTV details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RTV details'
            ], 500);
        }
    }

    /**
     * Delete RTV transaction
     */
    public function deleteRtv($id)
    {
        try {
            DB::beginTransaction();

            $rtv = RtvTransaction::findOrFail($id);

            // Only allow deletion of pending RTVs
            if ($rtv->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending RTV transactions can be deleted'
                ], 400);
            }

            // Delete associated RTV items (this will cascade)
            RtvItem::where('rtv_id', $rtv->id)->delete();

            // Delete the RTV transaction
            $rtv->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RTV transaction deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting RTV transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete RTV transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print RTV slip
     */
    public function printRtvSlip($id)
    {
        try {
            $rtv = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->findOrFail($id);

            return view('Inventory.inbound.print_rtv_slip', compact('rtv'));

        } catch (\Exception $e) {
            \Log::error('Error printing RTV slip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate RTV slip'
            ], 500);
        }
    }

    /**
     * Update RTV status
     */
    public function updateRtvStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processed,completed,cancelled'
            ]);

            $rtv = RtvTransaction::findOrFail($id);
            $rtv->update(['status' => $request->status]);

            // Create notification for status change
            $user = Auth::user();
            $this->createRtvStatusChangeNotification($rtv, $user, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'RTV status updated successfully',
                'status_badge' => $rtv->fresh()->status_badge
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating RTV status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RTV status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create notifications for RTV operations
     */
    private function createRtvNotifications($rtv, $user)
    {
        try {
            // Notify purchasing and supervisor users
            $notificationRecipients = User::whereIn('role', ['purchasing', 'supervisor', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($notificationRecipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'title' => '📦 New RTV Transaction Created',
                    'message' => "RTV {$rtv->rtv_number} created for {$rtv->supplier->name} with total value of {$rtv->formatted_total_value}",
                    'type' => 'rtv_created',
                    'priority' => 'normal',
                    'action_url' => '/inventory/inbound/rtv',
                    'metadata' => [
                        'rtv_id' => $rtv->id,
                        'rtv_number' => $rtv->rtv_number,
                        'supplier_name' => $rtv->supplier->name,
                        'total_value' => $rtv->total_value,
                        'created_by' => $user->name
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error creating RTV notifications: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for RTV status changes
     */
    private function createRtvStatusChangeNotification($rtv, $user, $newStatus)
    {
        try {
            $statusLabels = [
                'pending' => 'Pending Credit',
                'processed' => 'In Process',
                'completed' => 'Credit Received',
                'cancelled' => 'Cancelled'
            ];

            $notificationRecipients = User::whereIn('role', ['purchasing', 'supervisor', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($notificationRecipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'title' => '🔄 RTV Status Updated',
                    'message' => "RTV {$rtv->rtv_number} status changed to '{$statusLabels[$newStatus]}' by {$user->name}",
                    'type' => 'rtv_status_change',
                    'priority' => 'normal',
                    'action_url' => '/inventory/inbound/rtv',
                    'metadata' => [
                        'rtv_id' => $rtv->id,
                        'rtv_number' => $rtv->rtv_number,
                        'old_status' => $rtv->getOriginal('status'),
                        'new_status' => $newStatus,
                        'updated_by' => $user->name
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error creating RTV status change notification: ' . $e->getMessage());
        }
    }

}