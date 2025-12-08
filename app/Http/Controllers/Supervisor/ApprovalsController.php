<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisor\RequisitionController;
use App\Http\Controllers\Inventory\Outbound\PurchaseRequestController;
use App\Services\RequisitionApprovalService;
use Illuminate\Http\Request;
use App\Http\Requests\Supervisor\Requisition\ApproveRequisitionRequest;
use App\Http\Requests\Supervisor\Requisition\RejectRequisitionRequest;

class ApprovalsController extends Controller
{
    protected $requisitionController;
    protected $purchaseRequestController;
    protected $approvalService;

    public function __construct(
        RequisitionController $requisitionController,
        PurchaseRequestController $purchaseRequestController,
        RequisitionApprovalService $approvalService
    ) {
        $this->middleware('auth');
        $this->requisitionController = $requisitionController;
        $this->purchaseRequestController = $purchaseRequestController;
        $this->approvalService = $approvalService;
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
     * Approve a requisition
     */
    public function approveRequisition(\App\Models\Requisition $requisition, ApproveRequisitionRequest $request)
    {
        try {
            $result = $this->approvalService->approveRequisition($requisition, $request);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'stock_analysis' => $result['stock_analysis'] ?? null
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
     * Reject a requisition
     */
    public function rejectRequisition(\App\Models\Requisition $requisition, RejectRequisitionRequest $request)
    {
        try {
            $result = $this->approvalService->rejectRequisition($requisition, $request);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'reason' => $result['reason'] ?? null
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

            // Use the service method to get requisition details
            $details = $this->approvalService->getRequisitionDetails($requisition);

            return response()->json([
                'success' => true,
                'data' => $details
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



                        $approvedCount++;

                        // NEW: Notify Requester
                        try {
                            \App\Models\Notification::create([
                                'user_id' => $requisition->requested_by,
                                'title' => 'Requisition Approved',
                                'message' => "Requisition {$requisition->requisition_number} has been approved via bulk action.",
                                'type' => 'requisition_update',
                                'priority' => 'normal',
                                'action_url' => route('employee.requisitions.details', $requisition->id),
                                'created_at' => now()
                            ]);
                        } catch (\Exception $e) {}
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

                    // NEW: Notify Requester
                    try {
                        \App\Models\Notification::create([
                            'user_id' => $purchaseRequest->requested_by,
                            'title' => 'Purchase Request Approved',
                            'message' => "PR {$purchaseRequest->pr_number} has been approved via bulk action.",
                            'type' => 'purchasing',
                            'priority' => 'normal',
                            'action_url' => route('employee.requisitions.history'),
                            'created_at' => now()
                        ]);
                    } catch (\Exception $e) {}

                } catch (\Exception $e) {
                    $errors[] = "Failed to approve PR ID {$prId}: " . $e->getMessage();
                }
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
            // Validate request data
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:items,id',
                'new_quantity' => 'required|numeric|min:0.001',
                'reason' => 'required|string|max:500'
            ]);

            // Convert to array format expected by service
            $requestData = [
                'item_id' => $validated['item_id'],
                'new_quantity' => $validated['new_quantity'],
                'reason' => $validated['reason']
            ];

            $result = $this->approvalService->modifyRequisitionQuantity($requisition, $requestData);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data']
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
     * Modify single requisition item quantity (new method with exact signature)
     */
    public function modifyItemQuantity(\App\Models\Requisition $requisition, Request $request)
    {
        try {
            \Log::info('modifyItemQuantity called', [
                'requisition_id' => $requisition->id,
                'requisition_status' => $requisition->status,
                'request_data' => $request->all()
            ]);

            // Validate request data
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:items,id',
                'new_quantity' => 'required|numeric|min:0.001',
                'reason' => 'required|string|max:500'
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            // Call service method with exact signature
            $result = $this->approvalService->modifyItemQuantity(
                $requisition,
                $validated['item_id'],
                (float) $validated['new_quantity'],
                $validated['reason']
            );
            
            \Log::info('Service method completed', ['result' => $result]);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data']
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
            \Log::error('Error modifying requisition item quantity: ' . $e->getMessage(), [
                'requisition_id' => $requisition->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to modify requisition item quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modify multiple requisition items at once
     */
    public function modifyMultipleRequisitionItems(\App\Models\Requisition $requisition, Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.new_quantity' => 'required|numeric|min:0.001',
                'reason' => 'required|string|max:500'
            ]);

            // Convert to array format expected by service
            $requestData = [
                'items' => $validated['items'],
                'reason' => $validated['reason']
            ];

            $result = $this->approvalService->modifyMultipleRequisitionItems($requisition, $requestData);
            
            $response = [
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data']
            ];

            if (!empty($result['data']['errors'] ?? [])) {
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
                'error' => 'Failed to modify requisition items: ' . $e->getMessage()
            ], 500);
        }
    }
}
