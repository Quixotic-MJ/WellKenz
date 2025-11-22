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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        ])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
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
     * Stock level management
     */
    public function stockLevel()
    {
        $stockItems = Item::with(['currentStockRecord', 'unit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        return view('Supervisor.inventory.stock_level', compact('stockItems'));
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
        $movements = StockMovement::with(['user'])
            ->where('item_id', $item->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('Supervisor.inventory.stock_card', compact('item', 'movements'));
    }

    /**
     * Inventory adjustments
     */
    public function inventoryAdjustments()
    {
        return view('Supervisor.inventory.adjustments');
    }

    /**
     * Yield variance report
     */
    public function yieldVariance()
    {
        $productionOrders = ProductionOrder::with(['recipe', 'createdBy'])
            ->where('status', 'completed')
            ->orderBy('actual_end_date', 'desc')
            ->paginate(20);

        return view('Supervisor.reports.yield_variance', compact('productionOrders'));
    }

    /**
     * Expiry report
     */
    public function expiryReport()
    {
        $expiringBatches = \App\Models\Batch::with(['item'])
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('status', 'active')
            ->orderBy('expiry_date', 'asc')
            ->paginate(20);

        return view('Supervisor.reports.expiry_report', compact('expiringBatches'));
    }

    /**
     * COGS report
     */
    public function cogsReport()
    {
        return view('Supervisor.reports.COGS');
    }

    /**
     * Branch settings
     */
    public function branchSetting()
    {
        return view('Supervisor.branch_setting');
    }

    /**
     * Notifications page
     */
    public function notifications()
    {
        $notifications = \App\Models\Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('Supervisor.notification', compact('notifications'));
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