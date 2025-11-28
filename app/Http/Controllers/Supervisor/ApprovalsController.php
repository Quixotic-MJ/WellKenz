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
     * Purchase Request Approvals (to be implemented)
     */
    public function purchaseRequestApprovals(Request $request)
    {
        // TODO: Implement purchase request approval functionality
        return $this->purchaseRequestController->index($request);
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function approveRequisition(\App\Models\Requisition $requisition, Request $request)
    {
        return $this->requisitionController->approve($requisition, $request);
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function rejectRequisition(\App\Models\Requisition $requisition, Request $request)
    {
        return $this->requisitionController->reject($requisition, $request);
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function getRequisitionDetails(\App\Models\Requisition $requisition)
    {
        return $this->requisitionController->getDetails($requisition);
    }

    /**
     * Legacy method - redirect to new requisition system
     */
    public function bulkApproveRequisitions(Request $request)
    {
        return $this->requisitionController->bulkApprove($request);
    }

    /**
     * Purchase Request Approval (to be implemented)
     */
    public function approvePurchaseRequest(\App\Models\PurchaseRequest $purchaseRequest, Request $request)
    {
        // TODO: Implement purchase request approval functionality
        return response()->json([
            'success' => false,
            'error' => 'Purchase request approval not yet implemented'
        ], 501);
    }

    /**
     * Purchase Request Rejection (to be implemented)
     */
    public function rejectPurchaseRequest(\App\Models\PurchaseRequest $purchaseRequest, Request $request)
    {
        // TODO: Implement purchase request rejection functionality
        return response()->json([
            'success' => false,
            'error' => 'Purchase request rejection not yet implemented'
        ], 501);
    }

    /**
     * Purchase Request Details (to be implemented)
     */
    public function getPurchaseRequestDetails(\App\Models\PurchaseRequest $purchaseRequest)
    {
        // TODO: Implement purchase request details functionality
        return response()->json([
            'success' => false,
            'error' => 'Purchase request details not yet implemented'
        ], 501);
    }

    /**
     * Purchase Request Bulk Approval (to be implemented)
     */
    public function bulkApprovePurchaseRequests(Request $request)
    {
        // TODO: Implement purchase request bulk approval functionality
        return response()->json([
            'success' => false,
            'error' => 'Purchase request bulk approval not yet implemented'
        ], 501);
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
