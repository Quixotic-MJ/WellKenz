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
     * Process received delivery
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
                'items.*.condition' => 'required|in:good,damaged,wet_stained,thawed,leaking'
            ]);

            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::with(['purchaseOrderItems'])->findOrFail($request->purchase_order_id);
            $user = Auth::user();
            $allItemsComplete = true;

            foreach ($request->items as $receivedItem) {
                $poItem = PurchaseOrderItem::findOrFail($receivedItem['purchase_order_item_id']);
                
                $quantityReceived = (float) $receivedItem['quantity_received'];
                $batchNumber = $receivedItem['batch_number'] ?? 'BATCH-' . strtoupper(Str::random(8));
                $expiryDate = $receivedItem['expiry_date'] ?? null;
                $condition = $receivedItem['condition'];

                // Skip if no quantity received
                if ($quantityReceived <= 0) {
                    continue;
                }

                // Update purchase order item received quantity using query builder to avoid timestamp issues
                $newReceivedQuantity = $poItem->quantity_received + $quantityReceived;
                
                // Use DB query builder instead of Eloquent to avoid updated_at
                DB::table('purchase_order_items')
                    ->where('id', $poItem->id)
                    ->update([
                        'quantity_received' => $newReceivedQuantity
                    ]);

                // Check if this item is now complete
                if ($newReceivedQuantity < $poItem->quantity_ordered) {
                    $allItemsComplete = false;
                }

                // Create batch record if perishable or batch number provided
                $item = $poItem->item;
                if ($item->shelf_life_days > 0 || !empty($receivedItem['batch_number'])) {
                    $batch = Batch::create([
                        'batch_number' => $batchNumber,
                        'item_id' => $item->id,
                        'quantity' => $quantityReceived,
                        'unit_cost' => $poItem->unit_price,
                        'manufacturing_date' => now()->toDateString(),
                        'expiry_date' => $expiryDate ?? ($item->shelf_life_days > 0 ? 
                            now()->addDays($item->shelf_life_days)->toDateString() : null),
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'location' => 'Main Storage',
                        'status' => $condition === 'good' ? 'active' : 'quarantine'
                    ]);
                }

                // Create stock movement
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
                    'notes' => "Delivery received - Condition: " . $this->getConditionText($condition),
                    'user_id' => $user->id
                ]);

                // Update current stock (trigger will handle this, but we can also update directly)
                $this->updateCurrentStock($item->id, $quantityReceived, $poItem->unit_price);
            }

            // Update purchase order status
            $newStatus = $allItemsComplete ? 'completed' : 'partial';
            $purchaseOrder->update([
                'status' => $newStatus,
                'actual_delivery_date' => now()->toDateString()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery received successfully. PO status: ' . $newStatus,
                'po_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error processing delivery: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process delivery: ' . $e->getMessage()
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

}