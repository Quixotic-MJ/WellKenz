<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisor\RequisitionController;
use App\Http\Controllers\Inventory\Outbound\PurchaseRequestController;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
    protected $requisitionController;
    protected $purchaseRequestController;

    public function __construct(
        RequisitionController $requisitionController,
        PurchaseRequestController $purchaseRequestController
    ) {
        $this->middleware('auth');
        $this->requisitionController = $requisitionController;
        $this->purchaseRequestController = $purchaseRequestController;
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function requisitionApprovals(Request $request)
    {
        return $this->requisitionController->index($request);
    }

    /**
     * Purchase Request Approvals
     */
    public function purchaseRequestApprovals(Request $request)
    {
        try {
            // Get purchase requests for approval (pending status)
            $query = \App\Models\PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'all') {
                    // Show all statuses
                } elseif ($status === 'pending') {
                    $query->where('status', 'pending');
                } elseif ($status === 'approved') {
                    $query->where('status', 'approved');
                } elseif ($status === 'rejected') {
                    $query->where('status', 'rejected');
                }
            } else {
                // Default to pending if no status filter
                $query->where('status', 'pending');
            }

            // Apply priority filter
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Apply high value filter
            if ($request->boolean('high_value')) {
                $query->where('total_estimated_cost', '>=', 10000);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('pr_number', 'like', '%' . $search . '%')
                      ->orWhere('department', 'like', '%' . $search . '%')
                      ->orWhereHas('requestedBy', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            // Get paginated results
            $purchaseRequests = $query->latest('created_at')
                ->paginate(15)
                ->withQueryString();

            // Calculate statistics
            $pendingCount = \App\Models\PurchaseRequest::where('status', 'pending')->count();
            $approvedToday = \App\Models\PurchaseRequest::where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count();

            return view('Supervisor.approvals.purchase_request', compact(
                'purchaseRequests',
                'pendingCount',
                'approvedToday'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading purchase request approvals: ' . $e->getMessage());
            return view('Supervisor.approvals.purchase_request', [
                'purchaseRequests' => collect(),
                'pendingCount' => 0,
                'approvedToday' => 0
            ]);
        }
    }

    /**
     * Approve a requisition (legacy implementation)
     */
    public function approveRequisition(\App\Models\Requisition $requisition, Request $request)
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
                $currentStock = \App\Models\CurrentStock::where('item_id', $item->item_id)->first();
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
            $oldStatus = $requisition->status;
            $updated = $requisition->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => \Carbon\Carbon::now()
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update requisition status'
                ], 500);
            }

            // Create audit log
            try {
                \App\Models\AuditLog::create([
                    'table_name' => 'requisitions',
                    'record_id' => $requisition->id,
                    'action' => 'UPDATE',
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'old_values' => json_encode(['status' => $oldStatus]),
                    'new_values' => json_encode(['status' => 'approved'])
                ]);
            } catch (\Exception $e) {
                // Audit log creation failed, but don't fail the main operation
                \Log::warning('Failed to create audit log for requisition approval: ' . $e->getMessage());
            }

            // Notify the requesting employee that the requisition was approved
            try {
                \App\Models\Notification::create([
                    'user_id' => $requisition->requested_by,
                    'title' => 'Requisition Approved',
                    'message' => "Requisition {$requisition->requisition_number} has been approved by " . (auth()->user()->name ?? 'Supervisor') . '. Items are now ready for fulfillment.',
                    'type' => 'requisition_update',
                    'priority' => in_array($requisition->priority, ['high', 'urgent']) ? 'high' : 'normal',
                    'action_url' => route('employee.requisitions.details', $requisition),
                    'metadata' => [
                        'requisition_number' => $requisition->requisition_number,
                        'requisition_status' => 'approved',
                        'approved_by' => auth()->user()->name ?? 'Supervisor',
                        'approved_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'department' => $requisition->department,
                        'purpose' => $requisition->purpose ?? 'No purpose specified',
                        'total_estimated_value' => $requisition->total_estimated_value,
                        'items_count' => $requisition->requisitionItems->count(),
                        'priority' => $requisition->priority ?? 'normal'
                    ],
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee of requisition approval: ' . $e->getMessage());
            }

            // Notify inventory team about approved requisition ready for fulfillment
            try {
                $inventoryUsers = \App\Models\User::where('role', 'inventory')->get();
                foreach ($inventoryUsers as $inventoryUser) {
                    \App\Models\Notification::create([
                        'user_id' => $inventoryUser->id,
                        'title' => 'Requisition Ready for Fulfillment',
                        'message' => "Requisition {$requisition->requisition_number} from {$requisition->department} department has been approved and is ready for fulfillment.",
                        'type' => 'inventory',
                        'priority' => $requisition->priority === 'urgent' ? 'urgent' : 'normal',
                        'action_url' => route('inventory.outbound.fulfill'),
                        'metadata' => [
                            'requisition_number' => $requisition->requisition_number,
                            'department' => $requisition->department,
                            'requested_by' => $requisition->requestedBy->name ?? 'Unknown',
                            'purpose' => $requisition->purpose ?? 'No purpose specified',
                            'total_estimated_value' => $requisition->total_estimated_value,
                            'items_count' => $requisition->requisitionItems->count(),
                            'priority' => $requisition->priority ?? 'normal',
                            'approved_by' => auth()->user()->name ?? 'Supervisor',
                            'approved_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ],
                        'created_at' => \Carbon\Carbon::now(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to notify inventory team of approved requisition: ' . $e->getMessage());
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
     * Reject a requisition (legacy implementation)
     */
    public function rejectRequisition(\App\Models\Requisition $requisition, Request $request)
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

            // Get rejection reason from request
            $reason = $request->input('reason', 'No reason provided');
            $comments = $request->input('comments', '');

            // Combine reason and comments for storage
            $rejectionNotes = $reason;
            if (!empty($comments)) {
                $rejectionNotes .= ' - ' . $comments;
            }

            // Update requisition status to rejected
            $oldStatus = $requisition->status;
            $updated = $requisition->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => \Carbon\Carbon::now(),
                'reject_reason' => $reason,
                'notes' => $rejectionNotes
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update requisition status'
                ], 500);
            }

            // Create audit log
            try {
                \App\Models\AuditLog::create([
                    'table_name' => 'requisitions',
                    'record_id' => $requisition->id,
                    'action' => 'UPDATE',
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'old_values' => json_encode(['status' => $oldStatus]),
                    'new_values' => json_encode([
                        'status' => 'rejected',
                        'reject_reason' => $reason,
                        'comments' => $comments
                    ])
                ]);
            } catch (\Exception $e) {
                // Audit log creation failed, but don't fail the main operation
                \Log::warning('Failed to create audit log for requisition rejection: ' . $e->getMessage());
            }

            // Notify the requesting employee that the requisition was rejected
            try {
                \App\Models\Notification::create([
                    'user_id' => $requisition->requested_by,
                    'title' => 'Requisition Rejected',
                    'message' => "Requisition {$requisition->requisition_number} has been rejected by " . (auth()->user()->name ?? 'Supervisor') . ".",
                    'type' => 'requisition_update',
                    'priority' => 'normal',
                    'action_url' => route('employee.requisitions.details', $requisition),
                    'metadata' => [
                        'requisition_number' => $requisition->requisition_number,
                        'requisition_status' => 'rejected',
                        'rejected_by' => auth()->user()->name ?? 'Supervisor',
                        'rejected_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'department' => $requisition->department,
                        'total_estimated_value' => $requisition->total_estimated_value,
                        'rejection_reason' => $requisition->notes ?? 'No specific reason provided',
                        'items_count' => $requisition->requisitionItems->count()
                    ],
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee of requisition rejection: ' . $e->getMessage());
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
     * Get requisition details for modal
     */
    public function getRequisitionDetails(\App\Models\Requisition $requisition)
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
            $requisition = \App\Models\Requisition::with([
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
     * Format time ago string for display
     */
    private function formatTimeAgo($timestamp)
    {
        $diff = \Carbon\Carbon::now()->diffForHumans($timestamp, true);

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
                $requisition = \App\Models\Requisition::find($requisitionId);

                if ($requisition && $requisition->status === 'pending') {
                    // Check stock availability
                    $insufficientItems = [];
                    foreach ($requisition->requisitionItems as $item) {
                        $currentStock = \App\Models\CurrentStock::where('item_id', $item->item_id)->first();
                        if (!$currentStock || $currentStock->current_quantity < $item->quantity_requested) {
                            $insufficientItems[] = $item->item->name ?? 'Unknown Item';
                        }
                    }

                    if (empty($insufficientItems)) {
                        $oldStatus = $requisition->status;
                        $requisition->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => \Carbon\Carbon::now()
                        ]);

                        // Create audit log
                        \App\Models\AuditLog::create([
                            'table_name' => 'requisitions',
                            'record_id' => $requisition->id,
                            'action' => 'UPDATE',
                            'user_id' => auth()->id(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'old_values' => json_encode(['status' => $oldStatus]),
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
     * Purchase Request Approval
     */
    public function approvePurchaseRequest(\App\Models\PurchaseRequest $purchaseRequest, Request $request)
    {
        try {
            // Validate that the request is pending
            if (!$purchaseRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending purchase requests can be approved'
                ], 422);
            }

            // Update the purchase request
            $oldStatus = $purchaseRequest->status;
            $purchaseRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create audit log entry
            \App\Services\AuditLogHelper::logAction(
                'purchase_requests',
                $purchaseRequest->id,
                'UPDATE',
                [
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ],
                $request,
                [
                    'action_type' => 'approval',
                    'previous_status' => 'pending',
                    'new_status' => 'approved',
                    'approved_by' => auth()->user()->name,
                ]
            );

            // Notify the requesting user about approval
            try {
                \App\Models\Notification::create([
                    'user_id' => $purchaseRequest->requested_by,
                    'title' => 'Purchase Request Approved',
                    'message' => "Your purchase request {$purchaseRequest->pr_number} has been approved by " . (auth()->user()->name ?? 'Supervisor') . ".",
                    'type' => 'purchasing',
                    'priority' => in_array($purchaseRequest->priority, ['high', 'urgent']) ? 'high' : 'normal',
                    'action_url' => route('employee.requisitions.history'),
                    'metadata' => [
                        'purchase_request_number' => $purchaseRequest->pr_number,
                        'request_status' => 'approved',
                        'approved_by' => auth()->user()->name ?? 'Supervisor',
                        'approved_at' => now()->toDateTimeString(),
                        'department' => $purchaseRequest->department,
                        'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                        'priority' => $purchaseRequest->priority,
                        'items_count' => $purchaseRequest->purchaseRequestItems->count()
                    ],
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee of purchase request approval: ' . $e->getMessage());
            }

            // Notify purchasing team about approved request ready for conversion
            try {
                $purchasingUsers = \App\Models\User::where('role', 'purchasing')->get();
                foreach ($purchasingUsers as $purchasingUser) {
                    \App\Models\Notification::create([
                        'user_id' => $purchasingUser->id,
                        'title' => 'Purchase Request Ready for PO Creation',
                        'message' => "Purchase request {$purchaseRequest->pr_number} from {$purchaseRequest->department} department has been approved and is ready for conversion to Purchase Order.",
                        'type' => 'purchasing',
                        'priority' => $purchaseRequest->priority === 'urgent' ? 'urgent' : 'high',
                        'action_url' => route('purchasing.po.create'),
                        'metadata' => [
                            'purchase_request_number' => $purchaseRequest->pr_number,
                            'department' => $purchaseRequest->department,
                            'requested_by' => $purchaseRequest->requestedBy->name ?? 'Unknown',
                            'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                            'priority' => $purchaseRequest->priority,
                            'items_count' => $purchaseRequest->purchaseRequestItems->count(),
                            'approved_by' => auth()->user()->name ?? 'Supervisor',
                            'approved_at' => now()->toDateTimeString()
                        ],
                        'created_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to notify purchasing team of approved request: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase request approved successfully',
                'data' => [
                    'pr_number' => $purchaseRequest->pr_number,
                    'approved_at' => $purchaseRequest->approved_at->format('M d, Y H:i'),
                    'approved_by' => $purchaseRequest->approvedBy->name ?? 'Unknown User',
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error approving purchase request: ' . $e->getMessage(), [
                'pr_id' => $purchaseRequest->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to approve purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase Request Rejection
     */
    public function rejectPurchaseRequest(\App\Models\PurchaseRequest $purchaseRequest, Request $request)
    {
        try {
            // Validate that the request is pending
            if (!$purchaseRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending purchase requests can be rejected'
                ], 422);
            }

            // Validate request data
            $validated = $request->validate([
                'reason' => 'required|string|max:255',
                'comments' => 'nullable|string|max:1000'
            ]);

            // Get rejection reason from request
            $reason = $validated['reason'];
            $comments = $validated['comments'] ?? '';

            // Combine reason and comments for storage
            $rejectionNotes = $reason;
            if (!empty($comments)) {
                $rejectionNotes .= ' - ' . $comments;
            }

            // Prepare rejection data
            $oldStatus = $purchaseRequest->status;
            $rejectionData = [
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'reject_reason' => $reason,
                'notes' => $rejectionNotes
            ];

            // Update the purchase request
            $purchaseRequest->update($rejectionData);

            // Create audit log entry
            \App\Services\AuditLogHelper::logAction(
                'purchase_requests',
                $purchaseRequest->id,
                'UPDATE',
                $rejectionData,
                $request,
                [
                    'action_type' => 'rejection',
                    'previous_status' => 'pending',
                    'new_status' => 'rejected',
                    'rejected_by' => auth()->user()->name,
                    'reason' => $reason,
                    'comments' => $comments,
                ]
            );

            // Notify the requesting user about rejection
            try {
                \App\Models\Notification::create([
                    'user_id' => $purchaseRequest->requested_by,
                    'title' => 'Purchase Request Rejected',
                    'message' => "Your purchase request {$purchaseRequest->pr_number} has been rejected by " . (auth()->user()->name ?? 'Supervisor') . ".",
                    'type' => 'purchasing',
                    'priority' => 'normal',
                    'action_url' => route('employee.requisitions.history'),
                    'metadata' => [
                        'purchase_request_number' => $purchaseRequest->pr_number,
                        'request_status' => 'rejected',
                        'rejected_by' => auth()->user()->name ?? 'Supervisor',
                        'rejected_at' => now()->toDateTimeString(),
                        'department' => $purchaseRequest->department,
                        'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                        'priority' => $purchaseRequest->priority,
                        'rejection_reason' => $purchaseRequest->notes ?? 'No specific reason provided'
                    ],
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee of purchase request rejection: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase request rejected successfully',
                'data' => [
                    'pr_number' => $purchaseRequest->pr_number,
                    'rejected_at' => $purchaseRequest->rejected_at->format('M d, Y H:i'),
                    'rejected_by' => $purchaseRequest->approvedBy->name ?? 'Unknown User',
                    'reason' => $purchaseRequest->reject_reason,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error rejecting purchase request: ' . $e->getMessage(), [
                'pr_id' => $purchaseRequest->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to reject purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase Request Details
     */
    public function getPurchaseRequestDetails(\App\Models\PurchaseRequest $purchaseRequest)
    {
        try {
            // Load relationships
            $purchaseRequest->load([
                'requestedBy:id,name,email',
                'approvedBy:id,name',
                'purchaseRequestItems.item.unit:id,symbol',
                'purchaseRequestItems.item.category:id,name'
            ]);

            // Format the data for the frontend
            $data = [
                'id' => $purchaseRequest->id,
                'pr_number' => $purchaseRequest->pr_number,
                'request_date' => $purchaseRequest->request_date->format('M d, Y'),
                'requested_by' => $purchaseRequest->requestedBy->name ?? 'Unknown User',
                'department' => $purchaseRequest->department ?? 'General',
                'priority' => $purchaseRequest->priority,
                'status' => $purchaseRequest->status,
                'total_estimated_cost' => $purchaseRequest->total_estimated_cost ? (float) $purchaseRequest->total_estimated_cost : 0.00,
                'notes' => $purchaseRequest->notes,
                'reject_reason' => $purchaseRequest->reject_reason,
                'total_items' => $purchaseRequest->purchaseRequestItems->count(),
                'created_at' => $purchaseRequest->created_at->format('M d, Y H:i'),
                'approved_at' => $purchaseRequest->approved_at?->format('M d, Y H:i'),
                'approved_by' => $purchaseRequest->approvedBy?->name,
                'items' => $purchaseRequest->purchaseRequestItems->map(function($item) {
                    return [
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->name ?? 'Unknown Item',
                        'item_code' => $item->item->item_code ?? '',
                        'quantity_requested' => $item->quantity_requested,
                        'unit_symbol' => $item->item->unit->symbol ?? '',
                        'unit_price_estimate' => $item->unit_price_estimate ? (float) $item->unit_price_estimate : 0.00,
                        'total_estimated_cost' => $item->total_estimated_cost ? (float) $item->total_estimated_cost : 0.00,
                        'category' => $item->item->category->name ?? 'Uncategorized',
                    ];
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching purchase request details: ' . $e->getMessage(), [
                'pr_id' => $purchaseRequest->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load purchase request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Purchase Request Bulk Approval
     */
    public function bulkApprovePurchaseRequests(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'requisition_ids' => 'required|array|min:1',
                'requisition_ids.*' => 'integer|exists:purchase_requests,id'
            ]);

            $approvedCount = 0;
            $errors = [];
            $approvedRequests = [];

            foreach ($validated['requisition_ids'] as $prId) {
                try {
                    $purchaseRequest = \App\Models\PurchaseRequest::findOrFail($prId);

                    // Check if already approved
                    if ($purchaseRequest->isApproved()) {
                        $errors[] = "PR #{$purchaseRequest->pr_number} is already approved";
                        continue;
                    }

                    // Check if pending
                    if (!$purchaseRequest->isPending()) {
                        $errors[] = "PR #{$purchaseRequest->pr_number} is not in pending status";
                        continue;
                    }

                    // Approve the request
                    $purchaseRequest->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    $approvedRequests[] = [
                        'id' => $purchaseRequest->id,
                        'pr_number' => $purchaseRequest->pr_number,
                        'approved_at' => $purchaseRequest->approved_at->format('M d, Y H:i'),
                    ];

                    $approvedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to approve PR ID {$prId}: " . $e->getMessage();
                }
            }

            // Create audit log entry for bulk operation
            if ($approvedCount > 0) {
                \App\Services\AuditLogHelper::logAction(
                    'purchase_requests',
                    null, // No single record ID for bulk operations
                    'UPDATE',
                    [
                        'bulk_approval' => true,
                        'approved_count' => $approvedCount,
                        'approved_requests' => $approvedRequests,
                        'errors' => $errors
                    ],
                    $request,
                    [
                        'action_type' => 'bulk_approval',
                        'approved_count' => $approvedCount,
                        'total_requested' => count($validated['requisition_ids']),
                        'approved_by' => auth()->user()->name,
                        'approved_requests' => $approvedRequests,
                        'errors' => $errors
                    ]
                );
            }

            $response = [
                'success' => true,
                'message' => "Successfully approved {$approvedCount} purchase request(s)",
                'data' => [
                    'approved_count' => $approvedCount,
                    'total_requested' => count($validated['requisition_ids']),
                    'approved_requests' => $approvedRequests,
                    'errors' => $errors,
                    'has_errors' => !empty($errors)
                ]
            ];

            if (!empty($errors)) {
                $response['warning'] = 'Some requests could not be approved';
            }

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk approving purchase requests: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to bulk approve purchase requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function getRequisitionStatistics()
    {
        return $this->requisitionController->getStatistics();
    }

    /**
     * Modify single requisition item quantity
     */
    public function modifyRequisitionQuantity(\App\Models\Requisition $requisition, Request $request)
    {
        try {
            // Log request data for debugging
            \Log::info('Modification request data:', $request->all());
            
            // Validate requisition status
            if (!$requisition->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending requisitions can be modified'
                ], 422);
            }

            // Validate request data
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:items,id',
                'new_quantity' => 'required|numeric|min:0.001',
                'reason' => 'required|string|max:500'
            ]);

            // Find the requisition item
            $requisitionItem = $requisition->requisitionItems()
                ->where('item_id', $validated['item_id'])
                ->first();

            if (!$requisitionItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requisition item not found'
                ], 404);
            }

            // Check stock availability
            $currentStock = $requisitionItem->currentStock;
            $newQuantity = (float) $validated['new_quantity'];

            if ($newQuantity > $currentStock) {
                return response()->json([
                    'success' => false,
                    'error' => "Requested quantity ({$newQuantity}) exceeds available stock ({$currentStock})"
                ], 422);
            }

            // Store original values for audit log
            $originalQuantity = $requisitionItem->quantity_requested;

            // Update the quantity
            $requisitionItem->update([
                'quantity_requested' => $newQuantity
            ]);

            // Create audit log entry
            \App\Services\AuditLogHelper::logAction(
                'requisition_items',
                $requisitionItem->id,
                'UPDATE',
                [
                    'original_quantity' => $originalQuantity,
                    'new_quantity' => $newQuantity,
                    'item_id' => $validated['item_id'],
                    'reason' => $validated['reason'],
                    'stock_available' => $currentStock
                ],
                request(),
                [
                    'modification_type' => 'quantity_change',
                    'original_quantity' => $originalQuantity,
                    'new_quantity' => $newQuantity,
                    'item_id' => $validated['item_id'],
                    'reason' => $validated['reason'],
                    'stock_available' => $currentStock
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Requisition item quantity modified successfully',
                'data' => [
                    'original_quantity' => $originalQuantity,
                    'new_quantity' => $newQuantity,
                    'item_name' => $requisitionItem->item->name,
                    'stock_available' => $currentStock
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for requisition modification:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            // Flatten validation errors properly
            $flattenedErrors = [];
            foreach ($e->errors() as $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $flattenedErrors[] = $error;
                }
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $flattenedErrors),
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error modifying requisition quantity: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to modify requisition quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modify multiple requisition items at once
     */
    public function modifyMultipleRequisitionItems(\App\Models\Requisition $requisition, Request $request)
    {
        try {
            // Validate requisition status
            if (!$requisition->isPending()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending requisitions can be modified'
                ], 422);
            }

            // Validate request data
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.new_quantity' => 'required|numeric|min:0.001',
                'reason' => 'required|string|max:500'
            ]);

            $modifications = [];
            $errors = [];

            foreach ($validated['items'] as $itemData) {
                try {
                    // Find the requisition item
                    $requisitionItem = $requisition->requisitionItems()
                        ->where('item_id', $itemData['item_id'])
                        ->first();

                    if (!$requisitionItem) {
                        $errors[] = "Item ID {$itemData['item_id']}: Not found in requisition";
                        continue;
                    }

                    // Check stock availability
                    $currentStock = $requisitionItem->currentStock;
                    $newQuantity = (float) $itemData['new_quantity'];

                    if ($newQuantity > $currentStock) {
                        $errors[] = "{$requisitionItem->item->name}: Requested quantity ({$newQuantity}) exceeds available stock ({$currentStock})";
                        continue;
                    }

                    // Store original values for audit log
                    $originalQuantity = $requisitionItem->quantity_requested;

                    // Update the quantity
                    $requisitionItem->update([
                        'quantity_requested' => $newQuantity
                    ]);

                    $modifications[] = [
                        'item_id' => $itemData['item_id'],
                        'item_name' => $requisitionItem->item->name,
                        'original_quantity' => $originalQuantity,
                        'new_quantity' => $newQuantity,
                        'stock_available' => $currentStock
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Item ID {$itemData['item_id']}: " . $e->getMessage();
                }
            }

            // If there are successful modifications, create audit log
            if (!empty($modifications)) {
                \App\Services\AuditLogHelper::logAction(
                    'requisitions',
                    $requisition->id,
                    'UPDATE',
                    [
                        'modifications' => $modifications,
                        'reason' => $validated['reason'],
                        'total_items_modified' => count($modifications)
                    ],
                    request(),
                    [
                        'modification_type' => 'multiple_quantities',
                        'modifications' => $modifications,
                        'reason' => $validated['reason'],
                        'total_items_modified' => count($modifications)
                    ]
                );
            }

            // Prepare response
            $response = [
                'success' => true,
                'message' => 'Requisition items modified successfully',
                'data' => [
                    'modified_items' => $modifications,
                    'errors' => $errors,
                    'total_modified' => count($modifications),
                    'total_errors' => count($errors)
                ]
            ];

            if (!empty($errors)) {
                $response['warning'] = 'Some items could not be modified';
            }

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error modifying multiple requisition items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to modify requisition items'
            ], 500);
        }
    }
}
