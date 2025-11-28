<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Services\RequisitionApprovalService;
use App\Http\Requests\Supervisor\Requisition\ApproveRequisitionRequest;
use App\Http\Requests\Supervisor\Requisition\RejectRequisitionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RequisitionController extends Controller
{
    protected $approvalService;

    public function __construct(RequisitionApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
        $this->middleware('auth');
        $this->middleware('role:supervisor');
    }

    /**
     * Display the requisition approvals dashboard
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->get('status', 'pending'),
                'search' => $request->get('search'),
                'department' => $request->get('department'),
                'high_stock' => $request->boolean('high_stock'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $requisitions = $this->approvalService->getFilteredRequisitions($filters);
            $statistics = $this->approvalService->getStatistics();

            return view('Supervisor.approvals.requisition', compact(
                'requisitions',
                'statistics'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading requisition approvals: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to load requisition data. Please try again.');
        }
    }

    /**
     * Get requisition statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->approvalService->getStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting requisition statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load statistics'
            ], 500);
        }
    }

    /**
     * Get detailed requisition information for modal
     */
    public function getDetails(Requisition $requisition): JsonResponse
    {
        try {
            $details = $this->approvalService->getRequisitionDetails($requisition);
            
            return response()->json([
                'success' => true,
                'data' => $details
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting requisition details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load requisition details'
            ], 500);
        }
    }

    /**
     * Approve a requisition
     */
    public function approve(Requisition $requisition, ApproveRequisitionRequest $request): JsonResponse
    {
        try {
            $result = $this->approvalService->approveRequisition($requisition, $request);
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['message'] ?? 'Failed to approve requisition'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Error approving requisition: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve requisition'
            ], 500);
        }
    }

    /**
     * Reject a requisition
     */
    public function reject(Requisition $requisition, RejectRequisitionRequest $request): JsonResponse
    {
        try {
            $result = $this->approvalService->rejectRequisition($requisition, $request);
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['message'] ?? 'Failed to reject requisition'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Error rejecting requisition: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject requisition'
            ], 500);
        }
    }

    /**
     * Bulk approve multiple requisitions
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'requisition_ids' => 'required|array|min:1',
                'requisition_ids.*' => 'exists:requisitions,id'
            ]);

            $results = $this->approvalService->bulkApproveRequisitions($request->requisition_ids);
            
            return response()->json([
                'success' => true,
                'message' => "Bulk approval completed. {$results['total_processed']} requisitions approved.",
                'data' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('Error bulk approving requisitions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to bulk approve requisitions'
            ], 500);
        }
    }

    /**
     * Get filtered requisitions (for AJAX updates)
     */
    public function getFiltered(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'department' => $request->get('department'),
                'high_stock' => $request->boolean('high_stock'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $requisitions = $this->approvalService->getFilteredRequisitions($filters);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'requisitions' => $requisitions->items(),
                    'pagination' => [
                        'current_page' => $requisitions->currentPage(),
                        'total' => $requisitions->total(),
                        'per_page' => $requisitions->perPage(),
                        'last_page' => $requisitions->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error filtering requisitions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to filter requisitions'
            ], 500);
        }
    }

    /**
     * Refresh requisition data
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $statistics = $this->approvalService->getStatistics();
            $filters = [
                'status' => $request->get('status', 'pending'),
                'search' => $request->get('search'),
                'department' => $request->get('department'),
                'high_stock' => $request->boolean('high_stock'),
            ];

            $requisitions = $this->approvalService->getFilteredRequisitions($filters);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $statistics,
                    'requisitions' => $requisitions->items(),
                    'pagination' => [
                        'current_page' => $requisitions->currentPage(),
                        'total' => $requisitions->total(),
                        'per_page' => $requisitions->perPage(),
                        'last_page' => $requisitions->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error refreshing requisition data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh data'
            ], 500);
        }
    }

    /**
     * Export requisitions to CSV
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'department' => $request->get('department'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $requisitions = $this->approvalService->getFilteredRequisitions($filters);
            
            // Generate CSV content
            $csv = $this->generateCsv($requisitions);
            
            $filename = 'requisitions_' . date('Y-m-d_H-i-s') . '.csv';
            
            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Error exporting requisitions: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to export requisitions. Please try again.');
        }
    }

    /**
     * Generate CSV content from requisitions
     */
    private function generateCsv($requisitions): string
    {
        $output = fopen('php://temp', 'w+');
        
        // Add CSV header
        fputcsv($output, [
            'Requisition Number',
            'Requested By',
            'Department',
            'Status',
            'Total Items',
            'Total Value',
            'Request Date',
            'Approval Date',
            'Created At'
        ]);

        // Add data rows
        foreach ($requisitions as $requisition) {
            fputcsv($output, [
                $requisition->requisition_number,
                $requisition->requestedBy?->name ?? 'Unknown',
                $requisition->department,
                ucfirst($requisition->status),
                $requisition->requisitionItems->count(),
                '₱' . number_format($requisition->total_estimated_value, 2),
                $requisition->request_date->format('Y-m-d'),
                $requisition->approved_at ? $requisition->approved_at->format('Y-m-d H:i:s') : '',
                $requisition->created_at->format('Y-m-d H:i:s')
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}