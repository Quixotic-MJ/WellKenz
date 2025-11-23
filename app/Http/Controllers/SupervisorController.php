<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\CurrentStock;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StockMovement;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    /**
     * Display the supervisor dashboard with dynamic data.
     */
    public function home()
    {
        $criticalStockItems = $this->getCriticalStockItems();
        $usageVsSalesData = $this->getUsageVsSalesData();
        $pendingApprovals = $this->getPendingApprovals();
        $recentRequisitions = $this->getRecentRequisitions();
        
        return view('Supervisor.Home', compact(
            'criticalStockItems',
            'usageVsSalesData', 
            'pendingApprovals',
            'recentRequisitions'
        ));
    }

    /**
     * Get critical stock items (low stock below reorder point)
     */
    private function getCriticalStockItems()
    {
        return Item::with(['currentStockRecord', 'unit'])
            ->where('is_active', true)
            ->get()
            ->filter(function($item) {
                $currentStock = $item->currentStockRecord;
                if (!$currentStock) return false;
                
                return $currentStock->current_quantity <= $item->reorder_point;
            })
            ->sortBy(function($item) {
                $currentStock = $item->currentStockRecord;
                return $currentStock ? ($currentStock->current_quantity / max($item->reorder_point, 0.001)) : 1;
            })
            ->take(10)
            ->values()
            ->map(function($item) {
                $currentStock = $item->currentStockRecord;
                return [
                    'name' => $item->name,
                    'quantity' => $currentStock ? number_format($currentStock->current_quantity, 1) : '0.0',
                    'unit' => $item->unit->symbol ?? '',
                    'reorder_point' => $item->reorder_point,
                    'is_critical' => $currentStock ? ($currentStock->current_quantity <= ($item->reorder_point * 0.5)) : false
                ];
            });
    }

    /**
     * Get usage vs sales data for the last 3 days
     */
    private function getUsageVsSalesData()
    {
        $days = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Usage = Stock movements out (consumption/production)
            $usage = StockMovement::whereDate('created_at', $date)
                ->whereIn('movement_type', ['production', 'consumption', 'transfer', 'adjustment'])
                ->sum('quantity');
                
            // Sales = Stock movements in (sales or finished goods movement)
            $sales = StockMovement::whereDate('created_at', $date)
                ->where('movement_type', 'sale')
                ->sum('quantity');
                
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'usage' => $usage > 0 ? min(100, ($usage / 100) * 100) : 0, // Normalize to percentage for display
                'sales' => $sales > 0 ? min(100, ($sales / 100) * 100) : 0, // Normalize to percentage for display
                'usage_raw' => $usage,
                'sales_raw' => $sales
            ];
        }
        
        return $days;
    }

    /**
     * Get pending approvals summary
     */
    private function getPendingApprovals()
    {
        $pendingRequisitions = Requisition::where('status', 'pending')->count();
        $pendingPurchaseRequests = PurchaseOrder::where('status', 'draft')->count();
        $totalPending = $pendingRequisitions + $pendingPurchaseRequests;
        
        return [
            'total' => $totalPending,
            'requisitions' => $pendingRequisitions,
            'purchase_requests' => $pendingPurchaseRequests
        ];
    }

    /**
     * Get recent requisitions for approval
     */
    private function getRecentRequisitions()
    {
        return Requisition::with(['requestedBy', 'requisitionItems'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($requisition) {
                $totalItems = $requisition->requisitionItems->count();
                $mainItem = $requisition->requisitionItems->first();
                
                return [
                    'id' => $requisition->id,
                    'requisition_number' => $requisition->requisition_number,
                    'requester_name' => $requisition->requestedBy->name ?? 'Unknown',
                    'department' => $requisition->department,
                    'time_ago' => $this->formatTimeAgo($requisition->created_at),
                    'main_item' => $mainItem ? [
                        'name' => $mainItem->item->name ?? 'Unknown Item',
                        'quantity' => number_format($mainItem->quantity_requested, 1) . ' ' . ($mainItem->item->unit->symbol ?? ''),
                    ] : null,
                    'purpose' => $requisition->purpose,
                    'notes' => $requisition->notes,
                    'total_items' => $totalItems
                ];
            });
    }

    /**
     * Format time ago string for display
     */
    private function formatTimeAgo($timestamp)
    {
        $diff = Carbon::now()->diffForHumans($timestamp, true);
        
        if (strpos($diff, 'minute') !== false) {
            return 'Just now';
        } elseif (strpos($diff, 'hour') !== false) {
            $hours = (int) filter_var($diff, FILTER_SANITIZE_NUMBER_INT);
            return $hours <= 1 ? '1 hr ago' : $hours . ' hrs ago';
        } elseif (strpos($diff, 'day') !== false) {
            $days = (int) filter_var($diff, FILTER_SANITIZE_NUMBER_INT);
            return $days === 1 ? '1 day ago' : $days . ' days ago';
        }
        
        return $diff;
    }

    /**
     * Get stock overview for dashboard
     */
    public function getStockOverview()
    {
        $totalItems = Item::where('is_active', true)->count();
        $lowStockItems = Item::with('currentStockRecord')
            ->where('is_active', true)
            ->whereHas('currentStockRecord', function($query) {
                $query->where('current_quantity', '<=', function($subQuery) {
                    $subQuery->select('reorder_point')
                             ->from('items')
                             ->whereColumn('items.id', 'current_stock.item_id');
                });
            })->count();
            
        $outOfStockItems = Item::with('currentStockRecord')
            ->where('is_active', true)
            ->whereHas('currentStockRecord', function($query) {
                $query->where('current_quantity', '<=', 0);
            })->count();

        return response()->json([
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'critical_threshold' => 10 // Items with less than 24h supply
        ]);
    }

    /**
     * Get production metrics
     */
    public function getProductionMetrics()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        
        $todayProductions = ProductionOrder::whereDate('planned_start_date', $today)->count();
        $thisWeekProductions = ProductionOrder::whereBetween('planned_start_date', [$thisWeek, Carbon::now()])->count();
        $completedThisWeek = ProductionOrder::whereBetween('planned_start_date', [$thisWeek, Carbon::now()])
            ->where('status', 'completed')->count();
            
        $efficiency = $thisWeekProductions > 0 ? ($completedThisWeek / $thisWeekProductions) * 100 : 0;

        return response()->json([
            'today_productions' => $todayProductions,
            'this_week_productions' => $thisWeekProductions,
            'completed_this_week' => $completedThisWeek,
            'efficiency_percentage' => round($efficiency, 1)
        ]);
    }

    /**
     * Approve a purchase request
     */
    public function approvePurchaseRequest(PurchaseRequest $purchaseRequest, Request $request)
    {
        try {
            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request not found'
                ], 404);
            }

            if ($purchaseRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request has already been processed (Status: ' . $purchaseRequest->status . ')'
                ], 400);
            }

            // Update purchase request status
            $updated = $purchaseRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now()
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update purchase request status'
                ], 500);
            }

            // Create audit log
            try {
                AuditLog::create([
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->id,
                    'action' => 'UPDATE',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => json_encode(['status' => 'approved'])
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to create audit log for purchase request approval: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase request approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error approving purchase request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a purchase request
     */
    public function rejectPurchaseRequest(PurchaseRequest $purchaseRequest, Request $request)
    {
        try {
            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request not found'
                ], 404);
            }

            if ($purchaseRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request has already been processed (Status: ' . $purchaseRequest->status . ')'
                ], 400);
            }

            // Update purchase request status
            $updated = $purchaseRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now()
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update purchase request status'
                ], 500);
            }

            // Create audit log
            try {
                AuditLog::create([
                    'table_name' => 'purchase_requests',
                    'record_id' => $purchaseRequest->id,
                    'action' => 'UPDATE',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => json_encode(['status' => 'rejected'])
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to create audit log for purchase request rejection: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase request rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error rejecting purchase request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase request details for modal
     */
    public function getPurchaseRequestDetails(PurchaseRequest $purchaseRequest)
    {
        try {
            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request not found'
                ], 404);
            }

            $purchaseRequest = PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit'
            ])->find($purchaseRequest->id);

            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'error' => 'Purchase request not found'
                ], 404);
            }

            // Process purchase request items
            $items = $purchaseRequest->purchaseRequestItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item ? ($item->item->name ?? 'Unknown Item') : 'Unknown Item',
                    'item_code' => $item->item ? ($item->item->item_code ?? '') : '',
                    'unit_symbol' => $item->item && $item->item->unit ? ($item->item->unit->symbol ?? '') : '',
                    'quantity_requested' => number_format($item->quantity_requested, 3),
                    'unit_price_estimate' => number_format($item->unit_price_estimate, 2),
                    'total_estimated_cost' => number_format($item->total_estimated_cost, 2)
                ];
            });

            // Get requester info safely
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
                    'time_ago' => $this->formatTimeAgo($purchaseRequest->created_at),
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
                'error' => 'Failed to load purchase request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve multiple purchase requests
     */
    public function bulkApprovePurchaseRequests(Request $request)
    {
        try {
            $request->validate([
                'purchase_request_ids' => 'required|array|min:1',
                'purchase_request_ids.*' => 'exists:purchase_requests,id'
            ]);

            $approvedCount = 0;
            $errors = [];

            foreach ($request->purchase_request_ids as $purchaseRequestId) {
                $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
                
                if ($purchaseRequest && $purchaseRequest->status === 'pending') {
                    $purchaseRequest->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => Carbon::now()
                    ]);

                    // Create audit log
                    AuditLog::create([
                        'table_name' => 'purchase_requests',
                        'record_id' => $purchaseRequest->id,
                        'action' => 'UPDATE',
                        'user_id' => Auth::id(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'new_values' => json_encode(['status' => 'approved', 'bulk_approval' => true])
                    ]);

                    $approvedCount++;
                } else {
                    $errors[] = "Purchase Request ID {$purchaseRequestId} could not be processed";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} purchase request(s)",
                'approved_count' => $approvedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process bulk approval'
            ], 500);
        }
    }

    /**
     * Approve a requisition
     */
    public function approveRequisition(Requisition $requisition, Request $request)
    {
        try {
            // Check if requisition exists and is pending
            if (!$requisition) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition not found'
                ], 404);
            }

            if ($requisition->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition has already been processed (Status: ' . $requisition->status . ')'
                ], 400);
            }

            // Load requisition items to check stock
            $requisition->load('requisitionItems.item');

            // Verify stock availability before approval
            $insufficientItems = [];
            foreach ($requisition->requisitionItems as $item) {
                $currentStock = CurrentStock::where('item_id', $item->item_id)->first();
                if (!$currentStock || $currentStock->current_quantity < $item->quantity_requested) {
                    $insufficientItems[] = ($item->item->name ?? 'Unknown Item') . 
                        ' (Requested: ' . $item->quantity_requested . ', Available: ' . 
                        ($currentStock ? $currentStock->current_quantity : 0) . ')';
                }
            }

            if (!empty($insufficientItems)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient stock for: ' . implode(', ', $insufficientItems)
                ], 422);
            }

            // Update requisition status
            $updated = $requisition->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now()
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update requisition status'
                ], 500);
            }

            // Create audit log
            try {
                AuditLog::create([
                    'table_name' => 'requisitions',
                    'record_id' => $requisition->id,
                    'action' => 'UPDATE',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => json_encode(['status' => 'approved'])
                ]);
            } catch (\Exception $e) {
                // Audit log creation failed, but don't fail the main operation
                \Log::warning('Failed to create audit log for requisition approval: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Requisition approved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error approving requisition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve requisition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modify requisition quantity before approval
     */
    public function modifyRequisitionQuantity(Requisition $requisition, Request $request)
    {
        try {
            if ($requisition->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition has already been processed'
                ], 400);
            }

            // Validate the request - item_id should be requisition_item ID
            $request->validate([
                'item_id' => 'required|integer|exists:requisition_items,id',
                'new_quantity' => 'required|numeric|min:0.001|max:999999.999',
                'reason' => 'required|string|max:255',
                'remarks' => 'nullable|string|max:500'
            ]);

            // Find the specific requisition item and ensure it belongs to this requisition
            $requisitionItem = RequisitionItem::where('id', $request->item_id)
                ->where('requisition_id', $requisition->id)
                ->first();

            if (!$requisitionItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found in this requisition'
                ], 404);
            }

            // Check if new quantity exceeds available stock
            $currentStock = CurrentStock::where('item_id', $requisitionItem->item_id)->first();
            if ($currentStock && $request->new_quantity > $currentStock->current_quantity) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requested quantity (' . $request->new_quantity . ') exceeds available stock (' . $currentStock->current_quantity . ')'
                ], 422);
            }

            // Update the quantity
            $oldQuantity = $requisitionItem->quantity_requested;
            $newQuantity = $request->new_quantity;
            
            $requisitionItem->update([
                'quantity_requested' => $newQuantity,
                'total_estimated_value' => $newQuantity * $requisitionItem->unit_cost_estimate
            ]);

            // Auto-approve after modification
            $modifiedNotes = ($requisition->notes ?? '') . 
                "\nModified quantity from {$oldQuantity} to {$newQuantity}. " . 
                "Reason: " . $request->reason . 
                ($request->remarks ? ". Remarks: " . $request->remarks : "");

            $requisition->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'notes' => $modifiedNotes
            ]);

            // Create audit log
            AuditLog::create([
                'table_name' => 'requisition_items',
                'record_id' => $requisitionItem->id,
                'action' => 'UPDATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => json_encode(['quantity_requested' => $oldQuantity]),
                'new_values' => json_encode([
                    'quantity_requested' => $newQuantity, 
                    'reason' => $request->reason, 
                    'remarks' => $request->remarks
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Requisition modified and approved successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Flatten the validation errors into a single string
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error modifying requisition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to modify requisition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requisition details for modal
     */
    public function getRequisitionDetails(Requisition $requisition)
    {
        try {
            // Check if requisition exists
            if (!$requisition) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition not found'
                ], 404);
            }

            // Load requisition with all required relationships - simplify the loading
            $requisition = Requisition::with([
                'requestedBy:id,name,email',
                'requisitionItems.item.unit',
                'requisitionItems.currentStockRecord'
            ])->find($requisition->id);

            if (!$requisition) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition not found'
                ], 404);
            }

            // Process requisition items
            $items = $requisition->requisitionItems->map(function($item) {
                $stockRecord = $item->currentStockRecord;
                $currentStock = $stockRecord ? (float) $stockRecord->current_quantity : 0.0;
                $requestedQty = (float) $item->quantity_requested;

                // Get item details safely
                $itemName = $item->item ? ($item->item->name ?? 'Unknown Item') : 'Unknown Item';
                $unitSymbol = $item->item && $item->item->unit ? ($item->item->unit->symbol ?? '') : '';

                // Calculate stock percentage safely
                $stockPercentage = $currentStock > 0 && $requestedQty > 0 ? 
                    round(($requestedQty / $currentStock) * 100, 1) : 0;

                // Determine fulfillment status
                $canFulfillFull = $currentStock >= $requestedQty && $currentStock > 0;
                $isLowStock = $currentStock < $requestedQty && $currentStock > 0;

                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $itemName,
                    'quantity_requested' => number_format($requestedQty, 3),
                    'unit_symbol' => $unitSymbol,
                    'current_stock' => number_format($currentStock, 3),
                    'stock_percentage' => $stockPercentage,
                    'can_fulfill_full' => $canFulfillFull,
                    'is_low_stock' => $isLowStock,
                    'stock_status' => $currentStock === 0 ? 'out_of_stock' : 
                        ($currentStock < $requestedQty ? 'insufficient' : 'sufficient')
                ];
            });

            // Get requester info safely
            $requesterName = $requisition->requestedBy ? 
                ($requisition->requestedBy->name ?? 'Unknown User') : 'Unknown User';
            $department = $requisition->department ?? 'General';

            // Calculate summary data safely
            $totalEstimatedValue = $requisition->requisitionItems->sum('total_estimated_value');
            $criticalItemsCount = $items->where('is_low_stock', true)->count();
            $fulfillableItemsCount = $items->where('can_fulfill_full', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $requisition->id,
                    'requisition_number' => $requisition->requisition_number,
                    'requested_by' => $requesterName,
                    'department' => $department,
                    'purpose' => $requisition->purpose ?? 'No purpose specified',
                    'notes' => $requisition->notes,
                    'created_at' => $requisition->created_at->toISOString(),
                    'time_ago' => $this->formatTimeAgo($requisition->created_at),
                    'items' => $items->values(), // Ensure it's a proper array
                    'total_items' => $items->count(),
                    'status' => $requisition->status,
                    'summary' => [
                        'total_requested_value' => $totalEstimatedValue,
                        'critical_items_count' => $criticalItemsCount,
                        'fulfillable_items_count' => $fulfillableItemsCount
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading requisition details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load requisition details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a requisition
     */
    public function rejectRequisition(Requisition $requisition, Request $request)
    {
        try {
            // Check if requisition exists and is pending
            if (!$requisition) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition not found'
                ], 404);
            }

            if ($requisition->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition has already been processed (Status: ' . $requisition->status . ')'
                ], 400);
            }

            // Update requisition status to rejected
            $updated = $requisition->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now()
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update requisition status'
                ], 500);
            }

            // Create audit log
            try {
                AuditLog::create([
                    'table_name' => 'requisitions',
                    'record_id' => $requisition->id,
                    'action' => 'UPDATE',
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => json_encode(['status' => 'rejected'])
                ]);
            } catch (\Exception $e) {
                // Audit log creation failed, but don't fail the main operation
                \Log::warning('Failed to create audit log for requisition rejection: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Requisition rejected successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error rejecting requisition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject requisition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requisitions for approval page
     */
    public function requisitionApprovals(Request $request)
    {
        $query = Requisition::with([
            'requestedBy',
            'requisitionItems.item.unit',
            'requisitionItems.currentStockRecord'
        ])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('requisition_number', 'like', "%{$search}%")
                  ->orWhereHas('requestedBy', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('requisitionItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status (for completed requisitions)
        if ($request->filled('status') && $request->status !== 'pending') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $requisitions = $query->paginate(10)->withQueryString();

        // Get statistics
        $pendingCount = Requisition::where('status', 'pending')->count();
        $approvedToday = Requisition::where('status', 'approved')
            ->whereDate('approved_at', today())
            ->count();

        return view('Supervisor.approvals.requisition', compact('requisitions', 'pendingCount', 'approvedToday'));
    }

    /**
     * Get purchase request approvals page
     */
    public function purchaseRequestApprovals(Request $request)
    {
        $query = PurchaseRequest::with([
            'requestedBy',
            'purchaseRequestItems.item.unit'
        ]);

        // Apply status filter - default to pending if no status specified
        $status = $request->get('status', 'pending');
        $query->where('status', $status);

        $query->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhereHas('requestedBy', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('purchaseRequestItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by high value
        if ($request->filled('high_value')) {
            $query->where('total_estimated_cost', '>=', 10000);
        }

        $purchaseRequests = $query->paginate(10)->withQueryString();

        // Enhanced statistics for dynamic template
        $statistics = $this->getPurchaseRequestStatistics();
        
        return view('Supervisor.approvals.purchase_request', compact(
            'purchaseRequests', 
            'statistics'
        ));
    }

    /**
     * Get comprehensive purchase request statistics
     */
    private function getPurchaseRequestStatistics()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();

        // Base counts
        $totalRequests = PurchaseRequest::count();
        $pendingCount = PurchaseRequest::where('status', 'pending')->count();
        $approvedToday = PurchaseRequest::where('status', 'approved')
            ->whereDate('approved_at', $today)->count();
        $highValueCount = PurchaseRequest::where('status', 'pending')
            ->where('total_estimated_cost', '>=', 10000)->count();
        $totalValue = PurchaseRequest::where('status', 'pending')
            ->sum('total_estimated_cost');

        // Priority breakdown
        $priorityBreakdown = [
            'urgent' => PurchaseRequest::where('status', 'pending')->where('priority', 'urgent')->count(),
            'high' => PurchaseRequest::where('status', 'pending')->where('priority', 'high')->count(),
            'normal' => PurchaseRequest::where('status', 'pending')->where('priority', 'normal')->count(),
            'low' => PurchaseRequest::where('status', 'pending')->where('priority', 'low')->count(),
        ];

        // Risk level breakdown
        $riskBreakdown = [
            'high' => PurchaseRequest::where('status', 'pending')
                ->where(function($q) {
                    $q->where('total_estimated_cost', '>=', 50000)
                      ->orWhere('priority', 'urgent');
                })->count(),
            'medium' => PurchaseRequest::where('status', 'pending')
                ->where(function($q) {
                    $q->whereBetween('total_estimated_cost', [20000, 49999])
                      ->orWhere('priority', 'high');
                })->count(),
            'low' => PurchaseRequest::where('status', 'pending')
                ->where('total_estimated_cost', '<', 20000)
                ->where('priority', '!=', 'urgent')
                ->where('priority', '!=', 'high')->count(),
        ];

        // Department breakdown
        $departmentBreakdown = PurchaseRequest::where('status', 'pending')
            ->selectRaw('department, COUNT(*) as count, SUM(total_estimated_cost) as total_cost')
            ->groupBy('department')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->department ?? 'General' => [
                    'count' => $item->count,
                    'total_cost' => $item->total_cost
                ]];
            });

        // Average processing time (PostgreSQL compatible)
        $avgProcessingTime = PurchaseRequest::where('status', '!=', 'pending')
            ->whereNotNull('approved_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM approved_at - created_at) / 86400) as avg_days')
            ->first()->avg_days ?? 0;

        // Overdue requests (PostgreSQL compatible)
        $overdueCount = PurchaseRequest::where('status', 'pending')
            ->whereRaw("created_at < NOW() - INTERVAL '7 days'")
            ->count();

        // Recent activity (last 24 hours)
        $recentActivity = PurchaseRequest::where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();

        return [
            'total_requests' => $totalRequests,
            'pending_count' => $pendingCount,
            'approved_today' => $approvedToday,
            'high_value_count' => $highValueCount,
            'total_value' => $totalValue,
            'priority_breakdown' => $priorityBreakdown,
            'risk_breakdown' => $riskBreakdown,
            'department_breakdown' => $departmentBreakdown,
            'avg_processing_time' => round($avgProcessingTime, 1),
            'overdue_count' => $overdueCount,
            'recent_activity' => $recentActivity,
            'completion_rate' => $totalRequests > 0 ? round((($totalRequests - $pendingCount) / $totalRequests) * 100, 1) : 0
        ];
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
     * Export stock data to CSV
     */
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

    /**
     * Print stock report
     */
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
                $timeDiff = Carbon::now()->diffForHumans($lastMovement->created_at, true);
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

    /**
     * Stock level management
     */
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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
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
                        $stockQuery->where('current_quantity', '>', \DB::raw('COALESCE(items.reorder_point, 0)'));
                    } elseif ($status === 'low') {
                        $stockQuery->where('current_quantity', '>', 0)
                                   ->where('current_quantity', '<=', \DB::raw('COALESCE(items.reorder_point, COALESCE(items.min_stock_level, 10))'));
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

        return view('Supervisor.inventory.stock_level', compact('stockItems', 'metrics', 'categories'));
    }

    /**
     * Stock history listing page
     */
    public function stockHistory()
    {
        $items = Item::with(['currentStockRecord', 'unit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('Supervisor.inventory.stock_history', compact('items'));
    }

    /**
     * Stock card/history for specific item
     */
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
            $movementsQuery = StockMovement::with(['user'])
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
            $sevenDaysAgo = Carbon::now()->subDays(7);
            $stockOutMovements = StockMovement::where('item_id', $item->id)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->where('quantity', '<', 0) // Negative quantities are stock outs
                ->sum('quantity');

            $averageDailyUsage = abs($stockOutMovements) / 7; // Convert to positive and divide by 7 days

            // Get last restock date
            $lastRestock = StockMovement::where('item_id', $item->id)
                ->where('quantity', '>', 0) // Positive quantities are stock ins
                ->where('movement_type', 'purchase')
                ->latest('created_at')
                ->first();

            $lastRestockDate = null;
            $lastRestockDaysAgo = null;
            if ($lastRestock) {
                $lastRestockDate = $lastRestock->created_at;
                // Use diffForHumans for better time formatting
                $lastRestockDaysAgo = Carbon::now()->diffForHumans($lastRestock->created_at, true);
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
     * Inventory adjustments
     */
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

    /**
     * Get item details for stock display
     */
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

    /**
     * Create new inventory adjustment
     */
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
                'user_id' => Auth::id(),
                'created_at' => Carbon::now()
            ]);

            // Update current stock
            $this->updateCurrentStock($item->id, $quantity);

            // Create audit log
            AuditLog::create([
                'table_name' => 'stock_movements',
                'record_id' => $stockMovement->id,
                'action' => 'CREATE',
                'user_id' => Auth::id(),
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

    /**
     * Get adjustment history
     */
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
     * Update current stock after adjustment
     */
    private function updateCurrentStock($itemId, $quantity)
    {
        $currentStock = CurrentStock::where('item_id', $itemId)->first();
        
        if ($currentStock) {
            $currentStock->update([
                'current_quantity' => $currentStock->current_quantity + $quantity,
                'last_updated' => Carbon::now()
            ]);
        } else {
            // Create new current stock record if it doesn't exist
            CurrentStock::create([
                'item_id' => $itemId,
                'current_quantity' => $quantity,
                'average_cost' => Item::find($itemId)->cost_price ?? 0,
                'last_updated' => Carbon::now()
            ]);
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
        $now = Carbon::now();
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
     * Print Use First List
     */
    public function printUseFirstList(Request $request)
    {
        // Get priority batches (critical and warning)
        $priorityBatches = Batch::with(['item.unit', 'supplier'])
            ->where('status', 'active')
            ->where(function($query) {
                $query->where('expiry_date', '<=', Carbon::now()->addDays(7))
                      ->orWhere('expiry_date', '<', Carbon::now());
            })
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Calculate summary statistics
        $criticalCount = $priorityBatches->filter(function($batch) {
            return Carbon::parse($batch->expiry_date)->diffInDays(Carbon::now(), false) <= 1;
        })->count();

        $warningCount = $priorityBatches->filter(function($batch) {
            $daysUntil = Carbon::parse($batch->expiry_date)->diffInDays(Carbon::now(), false);
            return $daysUntil > 1 && $daysUntil <= 7;
        })->count();

        $totalCount = $priorityBatches->count();
        $totalValue = $priorityBatches->sum(function($batch) {
            return $batch->quantity * $batch->unit_cost;
        });

        $batches = $priorityBatches;

        return view('Supervisor.reports.print_use_first_list', compact(
            'batches',
            'criticalCount',
            'warningCount', 
            'totalCount',
            'totalValue'
        ));
    }

    /**
     * Alert Bakers - Send notification about expiring items
     */
    public function alertBakers(Request $request)
    {
        try {
            // Get critical batches (expiring in next 48 hours)
            $criticalBatches = Batch::with(['item.unit'])
                ->where('status', 'active')
                ->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(2)])
                ->orderBy('expiry_date', 'asc')
                ->get();

            // Get warning batches (expiring in next 7 days)
            $warningBatches = Batch::with(['item.unit'])
                ->where('status', 'active')
                ->where('expiry_date', '>', Carbon::now()->addDays(2))
                ->where('expiry_date', '<=', Carbon::now()->addDays(7))
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($criticalBatches->isEmpty() && $warningBatches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No expiring items found to alert about.'
                ]);
            }

            // Create notifications for bakers (users with 'employee' role)
            $bakerUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            $notificationsCreated = 0;

            foreach ($bakerUsers as $user) {
                // Critical alert
                foreach ($criticalBatches as $batch) {
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'URGENT: Items Expiring Soon',
                        'message' => "{$batch->item->name} (Batch: {$batch->batch_number}) expires on " . 
                                   Carbon::parse($batch->expiry_date)->format('M j, Y') . 
                                   ". Use immediately to avoid waste.",
                        'type' => 'expiry_alert',
                        'priority' => 'urgent',
                        'is_read' => false,
                        'created_at' => Carbon::now()
                    ]);
                    $notificationsCreated++;
                }

                // Warning alert
                foreach ($warningBatches as $batch) {
                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'Notice: Items Expiring Soon',
                        'message' => "{$batch->item->name} (Batch: {$batch->batch_number}) expires on " . 
                                   Carbon::parse($batch->expiry_date)->format('M j, Y') . 
                                   ". Please plan usage accordingly.",
                        'type' => 'expiry_alert',
                        'priority' => 'high',
                        'is_read' => false,
                        'created_at' => Carbon::now()
                    ]);
                    $notificationsCreated++;
                }
            }

            // Create audit log
            AuditLog::create([
                'table_name' => 'notifications',
                'record_id' => 0, // Multiple records created
                'action' => 'CREATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => json_encode([
                    'type' => 'batch_expiry_alert',
                    'critical_batches' => $criticalBatches->count(),
                    'warning_batches' => $warningBatches->count(),
                    'recipients' => $bakerUsers->count(),
                    'total_notifications' => $notificationsCreated
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Alert sent to {$bakerUsers->count()} baker(s) about {$criticalBatches->count()} critical and {$warningBatches->count()} warning items.",
                'data' => [
                    'critical_count' => $criticalBatches->count(),
                    'warning_count' => $warningBatches->count(),
                    'bakers_notified' => $bakerUsers->count(),
                    'total_notifications' => $notificationsCreated
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending baker alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send alerts. Please try again.'
            ], 500);
        }
    }

    /**
     * Expiry report
     */
    public function expiryReport(Request $request)
    {
        $filter = $request->get('filter', '7days'); // Default to next 7 days
        $search = $request->get('search', '');

        // Calculate expiry categories
        $now = Carbon::now();
        $criticalBatches = $this->getExpiringBatches('critical', $search); // < 48 hours
        $warningBatches = $this->getExpiringBatches('warning', $search); // < 7 days
        $expiredBatches = $this->getExpiringBatches('expired', $search); // Already expired

        // Calculate summary statistics
        $summary = $this->calculateExpirySummary($criticalBatches, $warningBatches, $expiredBatches);

        // Get batches based on filter
        $expiringBatches = $this->getFilteredBatches($filter, $search);

        return view('Supervisor.reports.expiry_report', compact(
            'expiringBatches', 
            'summary', 
            'filter', 
            'search',
            'criticalBatches',
            'warningBatches',
            'expiredBatches'
        ));
    }

    /**
     * Get expiring batches by category
     */
    private function getExpiringBatches($category, $search = '')
    {
        $now = Carbon::now();
        $query = Batch::with(['item.unit', 'supplier']);

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        switch ($category) {
            case 'critical':
                return $query->where('status', 'active')
                    ->whereBetween('expiry_date', [$now, $now->copy()->addDays(2)])
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });

            case 'warning':
                return $query->where('status', 'active')
                    ->where('expiry_date', '>', $now->copy()->addDays(2))
                    ->where('expiry_date', '<=', $now->copy()->addDays(7))
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });

            case 'expired':
                return $query->where('status', 'active')
                    ->where('expiry_date', '<', $now)
                    ->orderBy('expiry_date', 'asc')
                    ->get()
                    ->map(function($batch) {
                        return $this->formatBatchData($batch);
                    });
        }

        return collect();
    }

    /**
     * Get filtered batches for table display
     */
    private function getFilteredBatches($filter, $search = '')
    {
        $now = Carbon::now();
        $query = Batch::with(['item.unit', 'supplier'])
            ->where('status', 'active');

        // Apply search filter
        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        switch ($filter) {
            case '7days':
                $query->where('expiry_date', '<=', $now->copy()->addDays(7));
                break;
            case '30days':
                $query->where('expiry_date', '<=', $now->copy()->addDays(30));
                break;
            case 'expired':
                $query->where('expiry_date', '<', $now);
                break;
        }

        return $query->orderBy('expiry_date', 'asc')->paginate(20);
    }

    /**
     * Format batch data for display
     */
    private function formatBatchData($batch)
    {
        $now = Carbon::now();
        $expiryDate = Carbon::parse($batch->expiry_date);
        $daysUntilExpiry = $now->diffInDays($expiryDate, false); // false for absolute difference
        $isPastExpiry = $expiryDate->isPast();

        // Determine priority status
        $priority = 'Normal';
        $priorityClass = 'text-green-600';
        $statusClass = 'text-green-600';
        $urgentAction = false;

        if ($isPastExpiry) {
            $priority = 'EXPIRED';
            $priorityClass = 'text-red-600 font-bold';
            $statusClass = 'text-red-600 font-bold';
            $urgentAction = true;
        } elseif ($daysUntilExpiry <= 1) {
            $priority = 'CRITICAL';
            $priorityClass = 'text-red-600 font-bold';
            $statusClass = 'text-red-600 font-bold';
            $urgentAction = true;
        } elseif ($daysUntilExpiry <= 3) {
            $priority = 'High Priority';
            $priorityClass = 'text-orange-600';
            $statusClass = 'text-orange-600';
        } elseif ($daysUntilExpiry <= 7) {
            $priority = 'Monitor';
            $priorityClass = 'text-amber-600';
            $statusClass = 'text-amber-600';
        }

        // Format countdown text
        $countdownText = '';
        $countdownClass = '';
        
        if ($isPastExpiry) {
            $countdownText = 'EXPIRED';
            $countdownClass = 'bg-red-600 text-white animate-pulse';
        } elseif ($daysUntilExpiry == 0) {
            $countdownText = 'EXPIRES TODAY';
            $countdownClass = 'bg-red-600 text-white animate-pulse';
        } elseif ($daysUntilExpiry == 1) {
            $countdownText = '1 Day Left';
            $countdownClass = 'bg-red-600 text-white';
        } else {
            $countdownText = $daysUntilExpiry . ' Days Left';
            if ($daysUntilExpiry <= 3) {
                $countdownClass = 'bg-red-100 text-red-800 border border-red-200';
            } elseif ($daysUntilExpiry <= 7) {
                $countdownClass = 'bg-amber-100 text-amber-800';
            } else {
                $countdownClass = 'bg-gray-100 text-gray-600';
            }
        }

        return [
            'id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'item_name' => $batch->item->name ?? 'Unknown Item',
            'item_code' => $batch->item->item_code ?? '',
            'unit_symbol' => $batch->item->unit->symbol ?? '',
            'quantity' => number_format($batch->quantity, 1),
            'unit_cost' => number_format($batch->unit_cost, 2),
            'total_value' => number_format($batch->quantity * $batch->unit_cost, 2),
            'expiry_date' => $expiryDate->format('M j, Y'),
            'manufacturing_date' => $batch->manufacturing_date ? Carbon::parse($batch->manufacturing_date)->format('M j, Y') : 'N/A',
            'days_until_expiry' => $daysUntilExpiry,
            'countdown_text' => $countdownText,
            'countdown_class' => $countdownClass,
            'priority' => $priority,
            'priority_class' => $priorityClass,
            'status_class' => $statusClass,
            'supplier_name' => $batch->supplier->name ?? 'Unknown Supplier',
            'location' => $batch->location ?? 'Storage',
            'urgent_action' => $urgentAction,
            'is_expired' => $isPastExpiry,
            'is_critical' => $daysUntilExpiry <= 1,
            'is_warning' => $daysUntilExpiry > 1 && $daysUntilExpiry <= 7
        ];
    }

    /**
     * Calculate expiry summary statistics
     */
    private function calculateExpirySummary($criticalBatches, $warningBatches, $expiredBatches)
    {
        // Count batches
        $criticalCount = $criticalBatches->count();
        $warningCount = $warningBatches->count();
        $expiredCount = $expiredBatches->count();

        // Calculate total value at risk
        $criticalValue = $criticalBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $warningValue = $warningBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $expiredValue = $expiredBatches->sum(function($batch) {
            return floatval(str_replace(',', '', $batch['total_value']));
        });

        $totalValueAtRisk = $criticalValue + $warningValue + $expiredValue;

        return [
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'expired_count' => $expiredCount,
            'total_batches' => $criticalCount + $warningCount + $expiredCount,
            'critical_value' => $criticalValue,
            'warning_value' => $warningValue,
            'expired_value' => $expiredValue,
            'total_value_at_risk' => $totalValueAtRisk,
            'formatted_total_value' => '₱' . number_format($totalValueAtRisk, 2)
        ];
    }



    /**
     * Branch settings - Minimum Stock Configuration
     */
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
                        $stockQuery->whereRaw('current_quantity <= COALESCE(items.reorder_point, items.min_stock_level, 10)');
                    })->orWhereDoesntHave('currentStockRecord');
                } elseif ($status === 'critical') {
                    $q->whereHas('currentStockRecord', function($stockQuery) {
                        $stockQuery->where('current_quantity', '<=', 0);
                    })->orWhereDoesntHave('currentStockRecord');
                } elseif ($status === 'healthy') {
                    $q->whereHas('currentStockRecord', function($stockQuery) {
                        $stockQuery->whereRaw('current_quantity > COALESCE(items.reorder_point, items.min_stock_level, 10)');
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
            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            $reorderPoint = $item->reorder_point ?? 0;
            
            if ($currentStock <= 0) {
                $outOfStock++;
            } elseif ($currentStock <= ($reorderPoint * 0.5)) {
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
            'critical_stock' => $criticalStock,
            'out_of_stock' => $outOfStock
        ];
    }

    /**
     * Update minimum stock level for an item
     */
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

            if ($request->has('min_stock_level')) {
                $request->validate([
                    'min_stock_level' => 'numeric|min:0'
                ]);
                $updateData['min_stock_level'] = $request->min_stock_level;
            }

            if ($request->has('reorder_point')) {
                $request->validate([
                    'reorder_point' => 'numeric|min:0'
                ]);
                $updateData['reorder_point'] = $request->reorder_point;
            }

            if ($request->has('max_stock_level')) {
                $request->validate([
                    'max_stock_level' => 'numeric|min:0'
                ]);
                $updateData['max_stock_level'] = $request->max_stock_level;
            }

            // If we have both min and max stock levels, validate the relationship
            if (isset($updateData['min_stock_level']) && isset($updateData['max_stock_level'])) {
                if ($updateData['max_stock_level'] < $updateData['min_stock_level']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Maximum stock level must be greater than or equal to minimum stock level'
                    ], 422);
                }
            }

            // If we have min_stock_level but not max_stock_level, validate against existing max
            if (isset($updateData['min_stock_level']) && !isset($updateData['max_stock_level'])) {
                if ($item->max_stock_level < $updateData['min_stock_level']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Maximum stock level must be greater than or equal to minimum stock level'
                    ], 422);
                }
            }

            // If we have max_stock_level but not min_stock_level, validate against existing min
            if (isset($updateData['max_stock_level']) && !isset($updateData['min_stock_level'])) {
                if ($updateData['max_stock_level'] < $item->min_stock_level) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Maximum stock level must be greater than or equal to minimum stock level'
                    ], 422);
                }
            }

            // Update only the provided fields
            if (!empty($updateData)) {
                $item->update($updateData);
            }

            // Create audit log with only updated fields
            $newValues = [];
            foreach ($updateData as $key => $value) {
                $newValues[$key] = $value;
            }
            
            AuditLog::create([
                'table_name' => 'items',
                'record_id' => $item->id,
                'action' => 'UPDATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => json_encode(array_intersect_key($oldData, $updateData)),
                'new_values' => json_encode($newValues)
            ]);

            // Prepare response data with current values
            $responseData = [
                'item_name' => $item->name,
                'min_stock_level' => $item->min_stock_level,
                'reorder_point' => $item->reorder_point,
                'max_stock_level' => $item->max_stock_level
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
            \Log::error('Error updating minimum stock level: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update stock level'
            ], 500);
        }
    }

    /**
     * Apply seasonal adjustment to stock levels
     */
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

            foreach ($items as $item) {
                try {
                    $oldData = [
                        'min_stock_level' => $item->min_stock_level,
                        'reorder_point' => $item->reorder_point
                    ];

                    $newData = [];

                    $percentage = $request->adjustment_percentage / 100;

                    if ($request->adjustment_type === 'min_stock_level' || $request->adjustment_type === 'both') {
                        $newMinLevel = ($item->min_stock_level ?? 0) * (1 + $percentage);
                        $newData['min_stock_level'] = round($newMinLevel, 3);
                    }

                    if ($request->adjustment_type === 'reorder_point' || $request->adjustment_type === 'both') {
                        $newReorderPoint = ($item->reorder_point ?? 0) * (1 + $percentage);
                        $newData['reorder_point'] = round($newReorderPoint, 3);
                    }

                    if (!empty($newData)) {
                        $item->update($newData);

                        // Create audit log
                        AuditLog::create([
                            'table_name' => 'items',
                            'record_id' => $item->id,
                            'action' => 'UPDATE',
                            'user_id' => Auth::id(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'old_values' => json_encode($oldData),
                            'new_values' => json_encode($newData),
                            'metadata' => json_encode([
                                'seasonal_adjustment' => true,
                                'category_id' => $request->category_id,
                                'adjustment_percentage' => $request->adjustment_percentage,
                                'adjustment_type' => $request->adjustment_type
                            ])
                        ]);

                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Item {$item->item_code}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Seasonal adjustment applied to {$updatedCount} items in {$category->name}",
                'data' => [
                    'updated_count' => $updatedCount,
                    'category_name' => $category->name,
                    'adjustment_percentage' => $request->adjustment_percentage,
                    'adjustment_type' => $request->adjustment_type,
                    'errors' => $errors
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
            \Log::error('Error applying seasonal adjustment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to apply seasonal adjustment'
            ], 500);
        }
    }

    /**
     * Get stock configuration data for AJAX updates
     */
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
     * Notifications page with filtering and statistics
     */
    public function notifications(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        // Get notifications based on filter
        $query = \App\Models\Notification::forCurrentUser($filter);
        $notifications = $query->paginate(20)->withQueryString();
        
        // Calculate statistics for tabs
        $stats = $this->getNotificationStats();
        
        return view('Supervisor.notification', compact('notifications', 'filter', 'stats'));
    }

    /**
     * Get notification statistics for the current user
     */
    private function getNotificationStats()
    {
        $userId = Auth::id();
        
        return [
            'total' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->count(),
            'unread' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->unread()
                ->count(),
            'high_priority' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->whereIn('priority', ['high', 'urgent'])
                ->count(),
            'urgent' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->where('priority', 'urgent')
                ->count(),
        ];
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllNotificationsAsRead()
    {
        try {
            \App\Models\Notification::markAllAsReadForCurrentUser();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationAsRead(\App\Models\Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark a single notification as unread
     */
    public function markNotificationAsUnread(\App\Models\Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->markAsUnread();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as unread: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as unread'
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(\App\Models\Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete notification'
            ], 500);
        }
    }

    /**
     * Bulk notification operations (mark read, mark unread, delete)
     */
    public function bulkNotificationOperations(Request $request)
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'exists:notifications,id',
                'operation' => 'required|in:mark_read,mark_unread,delete'
            ]);

            $notificationIds = $request->notification_ids;
            $operation = $request->operation;
            $userId = Auth::id();

            // Verify all notifications belong to the current user
            $validNotifications = \App\Models\Notification::whereIn('id', $notificationIds)
                ->where('user_id', $userId)
                ->get();

            if ($validNotifications->count() !== count($notificationIds)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Some notifications do not belong to you'
                ], 403);
            }

            $affectedCount = 0;

            switch ($operation) {
                case 'mark_read':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->update(['is_read' => true]);
                    break;
                    
                case 'mark_unread':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->update(['is_read' => false]);
                    break;
                    
                case 'delete':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->delete();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$affectedCount} notification(s)",
                'affected_count' => $affectedCount
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
            \Log::error('Error in bulk notification operation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process bulk operation'
            ], 500);
        }
    }

    /**
     * Get unread notification count for header badge
     */
    public function getUnreadNotificationCount()
    {
        try {
            $count = \App\Models\Notification::unreadCountForCurrentUser();
            
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0
            ]);
        }
    }

    /**
     * Get header notifications (recent unread notifications for dropdown)
     */
    public function getHeaderNotifications()
    {
        try {
            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->notExpired()
                ->unread()
                ->latest()
                ->limit(5)
                ->get();
            
            $formattedNotifications = $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'time_ago' => $notification->getTimeAgoAttribute(),
                    'icon_class' => $notification->getIconClass(),
                    'action_url' => $notification->action_url
                ];
            });
            
            return response()->json([
                'success' => true,
                'notifications' => $formattedNotifications,
                'count' => $notifications->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching header notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'notifications' => [],
                'count' => 0
            ]);
        }
    }

    /**
     * Bulk approve multiple requisitions
     */
    public function bulkApproveRequisitions(Request $request)
    {
        try {
            $request->validate([
                'requisition_ids' => 'required|array|min:1',
                'requisition_ids.*' => 'exists:requisitions,id'
            ]);

            $approvedCount = 0;
            $errors = [];

            foreach ($request->requisition_ids as $requisitionId) {
                $requisition = Requisition::find($requisitionId);
                
                if ($requisition && $requisition->status === 'pending') {
                    // Check stock availability
                    $insufficientItems = [];
                    foreach ($requisition->requisitionItems as $item) {
                        $currentStock = CurrentStock::where('item_id', $item->item_id)->first();
                        if (!$currentStock || $currentStock->current_quantity < $item->quantity_requested) {
                            $insufficientItems[] = $item->item->name ?? 'Unknown Item';
                        }
                    }

                    if (empty($insufficientItems)) {
                        $requisition->update([
                            'status' => 'approved',
                            'approved_by' => Auth::id(),
                            'approved_at' => Carbon::now()
                        ]);

                        // Create audit log
                        AuditLog::create([
                            'table_name' => 'requisitions',
                            'record_id' => $requisition->id,
                            'action' => 'UPDATE',
                            'user_id' => Auth::id(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'new_values' => json_encode(['status' => 'approved', 'bulk_approval' => true])
                        ]);

                        $approvedCount++;
                    } else {
                        $errors[] = "Requisition #{$requisition->requisition_number}: Insufficient stock for " . implode(', ', $insufficientItems);
                    }
                } else {
                    $errors[] = "Requisition ID {$requisitionId} could not be processed";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} requisition(s)",
                'approved_count' => $approvedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process bulk approval'
            ], 500);
        }
    }

    /**
     * Get requisition statistics for dashboard
     */
    public function getRequisitionStatistics()
    {
        try {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();

            $stats = [
                'pending' => Requisition::where('status', 'pending')->count(),
                'approved_today' => Requisition::where('status', 'approved')
                    ->whereDate('approved_at', $today)->count(),
                'approved_this_week' => Requisition::where('status', 'approved')
                    ->whereBetween('approved_at', [$thisWeek, Carbon::now()])->count(),
                'rejected_this_week' => Requisition::where('status', 'rejected')
                    ->whereBetween('approved_at', [$thisWeek, Carbon::now()])->count(),
                'average_processing_time' => $this->getAverageProcessingTime(),
                'priority_items_count' => $this->getPriorityItemsCount(),
                'low_stock_impact' => $this->getLowStockImpact()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load statistics'
            ], 500);
        }
    }

    /**
     * Get average processing time for requisitions
     */
    private function getAverageProcessingTime()
    {
        $processedRequisitions = Requisition::where('status', '!=', 'pending')
            ->whereNotNull('approved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_hours')
            ->first();

        return $processedRequisitions->avg_hours ? round($processedRequisitions->avg_hours, 1) : 0;
    }

    /**
     * Get count of requisitions with priority items
     */
    private function getPriorityItemsCount()
    {
        return Requisition::where('status', 'pending')
            ->whereHas('requisitionItems', function($query) {
                $query->whereHas('currentStockRecord', function($stockQuery) {
                    $stockQuery->whereRaw('current_quantity <= 10');
                });
            })->count();
    }

    /**
     * Get impact of low stock on pending requisitions
     */
    private function getLowStockImpact()
    {
        $pendingRequisitions = Requisition::where('status', 'pending')
            ->with('requisitionItems.currentStockRecord')
            ->get();

        $impactCount = 0;
        $totalAffectedItems = 0;

        foreach ($pendingRequisitions as $requisition) {
            $affectedItems = 0;
            foreach ($requisition->requisitionItems as $item) {
                $currentStock = $item->currentStockRecord;
                if ($currentStock && $currentStock->current_quantity < $item->quantity_requested) {
                    $affectedItems++;
                    $totalAffectedItems++;
                }
            }
            if ($affectedItems > 0) {
                $impactCount++;
            }
        }

        return [
            'affected_requisitions' => $impactCount,
            'affected_items' => $totalAffectedItems,
            'severity' => $impactCount > 5 ? 'high' : ($impactCount > 2 ? 'medium' : 'low')
        ];
    }
}