<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        // Fetch Procurement Workflow Data
        $requestsToOrder = $this->getRequestsToOrder();
        $activeOrders = $this->getActiveOrders();
        $lateOrders = $this->getLateOrders();
        
        // Calculate KPIs
        $kpis = [
            'to_order' => $requestsToOrder->count(),
            'open_orders' => $activeOrders->count(),
            'overdue' => $lateOrders->count()
        ];

        return view('Purchasing.home', compact(
            'requestsToOrder',
            'activeOrders', 
            'lateOrders',
            'kpis'
        ));
    }

    /**
     * Fetch Purchase Requests ready to be converted to Purchase Orders
     * Status: 'approved', Order by request_date ASC (Oldest first)
     */
    private function getRequestsToOrder()
    {
        return PurchaseRequest::where('status', 'approved')
            ->with(['requestedBy', 'purchaseRequestItems'])
            ->orderBy('request_date', 'asc')
            ->get()
            ->map(function ($pr) {
                $daysOpen = $pr->request_date->diffInDays(Carbon::now());
                return [
                    'id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                    'department' => $pr->department,
                    'request_date' => $pr->request_date,
                    'days_open' => $daysOpen,
                    'priority' => $pr->priority,
                    'total_estimated_cost' => $pr->total_estimated_cost,
                    'requested_by' => $pr->requestedBy ? $pr->requestedBy->name : 'Unknown',
                    'item_count' => $pr->purchaseRequestItems->count()
                ];
            });
    }

    /**
     * Fetch Active Purchase Orders
     * Status: 'sent', 'confirmed', 'partial', Order by expected_delivery_date ASC
     */
    private function getActiveOrders()
    {
        return PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->with(['supplier', 'purchaseOrderItems'])
            ->orderBy('expected_delivery_date', 'asc')
            ->get()
            ->map(function ($po) {
                $isOverdue = $po->expected_delivery_date && $po->expected_delivery_date->isPast();
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'status' => $po->status,
                    'grand_total' => $po->grand_total,
                    'is_overdue' => $isOverdue,
                    'item_count' => $po->purchaseOrderItems->count()
                ];
            });
    }

    /**
     * Count Late Purchase Orders
     * expected_delivery_date < Today and status not 'completed'
     */
    private function getLateOrders()
    {
        return PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->where('expected_delivery_date', '<', Carbon::now()->toDateString())
            ->whereNull('actual_delivery_date')
            ->with('supplier')
            ->get()
            ->map(function ($po) {
                $daysOverdue = Carbon::parse($po->expected_delivery_date)->diffInDays(Carbon::now());
                return [
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'days_overdue' => $daysOverdue,
                    'grand_total' => $po->grand_total
                ];
            })
            ->sortByDesc('days_overdue');
    }
}
