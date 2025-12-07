<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\CurrentStock;
use App\Models\Requisition;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the supervisor dashboard with dynamic data.
     */
    public function home()
    {
        $stats = $this->getStats();
        $recentRequisitions = $this->getRecentRequisitions();
        $recentPurchaseRequests = $this->getRecentPurchaseRequests();

        return view('Supervisor.Home', compact(
            'stats',
            'recentRequisitions',
            'recentPurchaseRequests'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getStats()
    {
        $pendingRequisitions = Requisition::where('status', 'pending')->count();
        $pendingPurchaseRequests = PurchaseRequest::where('status', 'pending')->count();
        $criticalStock = Item::where('is_active', true)
            ->whereHas('currentStockRecord', function($query) {
                $query->whereRaw('current_quantity <= items.reorder_point');
            })->count();

        return [
            'pending_requisitions' => $pendingRequisitions,
            'pending_purchase_requests' => $pendingPurchaseRequests,
            'critical_stock' => $criticalStock
        ];
    }

    /**
     * Get recent purchase requests for approval (Pending status, urgent first)
     */
    private function getRecentPurchaseRequests()
    {
        try {
            \Log::info('getRecentPurchaseRequests: Starting method execution');

            $purchaseRequests = PurchaseRequest::with(['requestedBy'])
                ->where('status', 'pending')
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            \Log::info('getRecentPurchaseRequests: Found ' . $purchaseRequests->count() . ' pending purchase requests');

            $formattedRequests = $purchaseRequests->map(function($request) {
                return [
                    'id' => $request->id,
                    'pr_number' => $request->pr_number,
                    'requester_name' => $request->requestedBy->name ?? 'Unknown',
                    'department' => $request->department,
                    'priority' => $request->priority,
                    'total_cost' => $request->formatted_total,
                    'time_ago' => $this->formatTimeAgo($request->created_at),
                    'notes' => $request->notes,
                    'request_date' => $request->request_date->format('M d, Y')
                ];
            });

            \Log::info('getRecentPurchaseRequests: Returning ' . $formattedRequests->count() . ' formatted purchase requests');

            return $formattedRequests;

        } catch (\Exception $e) {
            \Log::error('getRecentPurchaseRequests: Error occurred - ' . $e->getMessage());
            // Return empty collection on error
            return collect([]);
        }
    }

    /**
     * Get recent requisitions for approval (Pending status, oldest first)
     */
    private function getRecentRequisitions()
    {
        try {
            \Log::info('getRecentRequisitions: Starting method execution');

            $requisitions = Requisition::with(['requestedBy', 'requisitionItems.item.unit'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'asc') // Oldest first
                ->limit(10)
                ->get();

            \Log::info('getRecentRequisitions: Found ' . $requisitions->count() . ' pending requisitions');

            $formattedRequisitions = $requisitions->map(function($requisition) {
                $totalItems = $requisition->requisitionItems->count();
                $mainItem = $requisition->requisitionItems->first();

                \Log::debug("Processing requisition: {$requisition->id} - {$requisition->requisition_number}");

                $formattedItem = null;
                if ($mainItem && $mainItem->item) {
                    $formattedItem = [
                        'name' => $mainItem->item->name ?? 'Unknown Item',
                        'quantity' => number_format($mainItem->quantity_requested, 1) . ' ' . ($mainItem->item->unit->symbol ?? ''),
                    ];
                    \Log::debug("Main item: {$formattedItem['name']}");
                }

                return [
                    'id' => $requisition->id, // Ensure this is an integer, not array
                    'requisition_number' => $requisition->requisition_number,
                    'requester_name' => $requisition->requestedBy->name ?? 'Unknown',
                    'department' => $requisition->department,
                    'time_ago' => $this->formatTimeAgo($requisition->created_at),
                    'main_item' => $formattedItem,
                    'purpose' => $requisition->purpose,
                    'notes' => $requisition->notes,
                    'total_items' => $totalItems,
                    'request_date' => $requisition->request_date->format('M d, Y')
                ];
            });

            \Log::info('getRecentRequisitions: Returning ' . $formattedRequisitions->count() . ' formatted requisitions');

            return $formattedRequisitions;

        } catch (\Exception $e) {
            \Log::error('getRecentRequisitions: Error occurred - ' . $e->getMessage());
            // Return empty collection on error
            return collect([]);
        }
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
