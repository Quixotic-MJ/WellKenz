<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Http\Requests\Supervisor\Requisition\ApproveRequisitionRequest;
use App\Http\Requests\Supervisor\Requisition\RejectRequisitionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequisitionApprovalService
{
    /**
     * Get requisitions with filtering and pagination for supervisor view
     */
    public function getFilteredRequisitions(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Requisition::with([
            'requestedBy',
            'requisitionItems.item.unit',
            'requisitionItems.currentStockRecord'
        ]);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Order by creation date (newest first)
        return $query->latest('created_at')->paginate(15);
    }

    /**
     * Get requisition statistics for dashboard
     */
    public function getStatistics(): array
    {
        $today = Carbon::today();

        return [
            'pending' => Requisition::pending()->count(),
            'approved_today' => Requisition::approvedToday()->count(),
            'total_approved_this_week' => Requisition::approved()
                ->whereBetween('approved_at', [Carbon::now()->startOfWeek(), Carbon::now()])
                ->count(),
            'total_rejected_this_week' => Requisition::rejected()
                ->whereBetween('rejected_at', [Carbon::now()->startOfWeek(), Carbon::now()])
                ->count(),
            'high_stock_usage' => $this->getHighStockUsageCount(),
            'critical_items' => $this->getCriticalItemsCount(),
        ];
    }

    /**
     * Approve a requisition
     */
    public function approveRequisition(Requisition $requisition, ApproveRequisitionRequest $request): array
    {
        return DB::transaction(function () use ($requisition, $request) {
            // Check if requisition can be approved
            if (!$requisition->canBeApproved()) {
                throw new \Exception('Requisition cannot be approved in its current status.');
            }

            // Stock validation (unless overridden)
            $stockAnalysis = $requisition->getStockAnalysis();
            if ($stockAnalysis['insufficient_stock'] > 0 && !$request->boolean('override_stock')) {
                throw new \Exception('Insufficient stock for some items. Please check stock levels or use override.');
            }

            // Store original status for audit log
            $originalStatus = $requisition->status;

            // Update requisition
            $requisition->update([
                'status' => Requisition::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'notes' => $request->get('notes'),
            ]);

            // Create audit log
            $this->createAuditLog($requisition, 'UPDATE', [
                'status' => $originalStatus
            ], [
                'status' => 'approved',
                'notes' => $request->get('notes'),
                'approved_by' => Auth::id(),
            ]);

            // Create notification for requester
            $this->createNotification(
                $requisition->requested_by,
                'Requisition Approved',
                "Your requisition {$requisition->requisition_number} has been approved.",
                'requisition_approved'
            );

            return [
                'success' => true,
                'message' => 'Requisition approved successfully',
                'stock_analysis' => $stockAnalysis
            ];
        });
    }

    /**
     * Reject a requisition
     */
    public function rejectRequisition(Requisition $requisition, RejectRequisitionRequest $request): array
    {
        return DB::transaction(function () use ($requisition, $request) {
            // Check if requisition can be rejected
            if (!$requisition->canBeRejected()) {
                throw new \Exception('Requisition cannot be rejected in its current status.');
            }

            // Store original status for audit log
            $originalStatus = $requisition->status;

            // Combine reason and comments
            $rejectReason = $request->getCombinedReason();

            // Update requisition
            $requisition->update([
                'status' => Requisition::STATUS_REJECTED,
                'rejected_by' => Auth::id(),
                'rejected_at' => Carbon::now(),
                'reject_reason' => $rejectReason,
            ]);

            // Create audit log
            $this->createAuditLog($requisition, 'UPDATE', [
                'status' => $originalStatus
            ], [
                'status' => 'rejected',
                'reject_reason' => $rejectReason,
                'rejected_by' => Auth::id(),
            ]);

            // Create notification for requester
            $this->createNotification(
                $requisition->requested_by,
                'Requisition Rejected',
                "Your requisition {$requisition->requisition_number} has been rejected. Reason: {$rejectReason}",
                'requisition_rejected'
            );

            return [
                'success' => true,
                'message' => 'Requisition rejected successfully',
                'reason' => $rejectReason
            ];
        });
    }

    /**
     * Get detailed requisition information for modal
     */
    public function getRequisitionDetails(Requisition $requisition): array
    {
        $requisition->load([
            'requestedBy',
            'requisitionItems.item.unit',
            'requisitionItems.currentStockRecord'
        ]);

        $stockAnalysis = $requisition->getStockAnalysis();

        // Format items for the view
        $formattedItems = $requisition->requisitionItems->map(function ($item) {
            $currentStock = $item->currentStockRecord?->current_quantity ?? 0;
            $requestedQty = $item->quantity_requested;
            $stockPercentage = $currentStock > 0 ? round(($requestedQty / $currentStock) * 100, 1) : 0;

            return [
                'id' => $item->id,
                'item_id' => $item->item_id, // Add the item_id field for frontend compatibility
                'item_name' => $item->item?->name ?? 'Unknown Item',
                'item_code' => $item->item?->item_code ?? '',
                'unit_symbol' => $item->item?->unit?->symbol ?? '',
                'quantity_requested' => number_format($requestedQty, 3),
                'current_stock' => number_format($currentStock, 3),
                'stock_percentage' => $stockPercentage,
                'unit_cost_estimate' => number_format($item->unit_cost_estimate, 2),
                'total_estimated_value' => number_format($item->total_estimated_value, 2),
                'stock_status' => $item->stock_status,
                'can_fulfill' => $currentStock >= $requestedQty && $currentStock > 0
            ];
        });

        return [
            'id' => $requisition->id,
            'requisition_number' => $requisition->requisition_number,
            'requested_by' => $requisition->requestedBy?->name ?? 'Unknown',
            'department' => $requisition->department,
            'purpose' => $requisition->purpose,
            'status' => $requisition->status,
            'total_estimated_value' => $requisition->total_estimated_value,
            'created_at' => $requisition->created_at->format('Y-m-d H:i:s'),
            'notes' => $requisition->notes,
            'reject_reason' => $requisition->reject_reason,
            'total_items' => $requisition->requisitionItems->count(),
            'items' => $formattedItems,
            'stock_analysis' => $stockAnalysis,
            'can_approve' => $requisition->canBeApproved(),
            'can_reject' => $requisition->canBeRejected(),
            'can_modify' => $requisition->canBeModified(),
        ];
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, array $filters): void
    {
        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->byStatus($filters['status']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Department filter
        if (!empty($filters['department'])) {
            $query->byDepartment($filters['department']);
        }

        // High stock usage filter
        if (isset($filters['high_stock']) && $filters['high_stock']) {
            $query->whereHas('requisitionItems', function($q) {
                $q->whereHas('currentStockRecord', function($stockQuery) {
                    $stockQuery->whereRaw('(requisition_items.quantity_requested / current_stock.current_quantity) * 100 > 80');
                });
            });
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Get count of requisitions with high stock usage
     */
    private function getHighStockUsageCount(): int
    {
        return Requisition::pending()->whereHas('requisitionItems', function($q) {
            $q->whereHas('currentStockRecord', function($stockQuery) {
                $stockQuery->whereRaw('(requisition_items.quantity_requested / current_stock.current_quantity) * 100 > 80');
            });
        })->count();
    }

    /**
     * Get count of critical items (very low stock)
     */
    private function getCriticalItemsCount(): int
    {
        return Requisition::pending()->whereHas('requisitionItems.currentStockRecord', function($q) {
            $q->where('current_quantity', '<=', 5);
        })->count();
    }

    /**
     * Create audit log entry
     */
    private function createAuditLog(Requisition $requisition, string $action, array $oldValues, array $newValues): void
    {
        AuditLog::create([
            'table_name' => 'requisitions',
            'record_id' => $requisition->id,
            'action' => $action,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Create notification
     */
    private function createNotification(int $userId, string $title, string $message, string $type): void
    {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => 'normal',
            'action_url' => "/employee/requisitions/history",
        ]);
    }

    /**
     * Bulk approve multiple requisitions
     */
    public function bulkApproveRequisitions(array $requisitionIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total_processed' => 0
        ];

        DB::transaction(function() use ($requisitionIds, &$results) {
            foreach ($requisitionIds as $requisitionId) {
                try {
                    $requisition = Requisition::find($requisitionId);
                    
                    if (!$requisition) {
                        $results['failed'][] = "Requisition ID {$requisitionId} not found";
                        continue;
                    }

                    if (!$requisition->canBeApproved()) {
                        $results['failed'][] = "Requisition {$requisition->requisition_number} cannot be approved";
                        continue;
                    }

                    // Check stock availability
                    $stockAnalysis = $requisition->getStockAnalysis();
                    if ($stockAnalysis['insufficient_stock'] > 0) {
                        $results['failed'][] = "Insufficient stock for requisition {$requisition->requisition_number}";
                        continue;
                    }

                    // Approve the requisition
                    $originalStatus = $requisition->status;
                    $requisition->update([
                        'status' => Requisition::STATUS_APPROVED,
                        'approved_by' => Auth::id(),
                        'approved_at' => Carbon::now(),
                    ]);

                    // Create audit log
                    $this->createAuditLog($requisition, 'UPDATE', [
                        'status' => $originalStatus
                    ], [
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'bulk_approval' => true
                    ]);

                    $results['success'][] = $requisition->requisition_number;
                    $results['total_processed']++;

                } catch (\Exception $e) {
                    $results['failed'][] = "Requisition ID {$requisitionId}: " . $e->getMessage();
                }
            }
        });

        return $results;
    }
}