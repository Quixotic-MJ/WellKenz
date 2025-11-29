<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $lowStockItems = $this->getLowStockItems();
        $openPoValue = $this->getOpenPurchaseOrderValue();
        $openPoCount = $this->getOpenPurchaseOrderCount();
        $overdueDeliveries = $this->getOverdueDeliveries();
        $recentPurchaseOrders = $this->getRecentPurchaseOrders();
        $frequentSuppliers = $this->getFrequentSuppliers();

        return view('Purchasing.home', compact(
            'lowStockItems',
            'openPoValue',
            'openPoCount',
            'overdueDeliveries',
            'recentPurchaseOrders',
            'frequentSuppliers'
        ));
    }

    public function getDashboardSummary()
    {
        $today = Carbon::now()->toDateString();
        $thisWeek = Carbon::now()->startOfWeek()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();

        $summary = [
            'today_orders' => PurchaseOrder::whereDate('order_date', $today)->count(),
            'week_orders' => PurchaseOrder::whereDate('order_date', '>=', $thisWeek)->count(),
            'month_orders' => PurchaseOrder::whereDate('order_date', '>=', $thisMonth)->count(),
            'awaiting_delivery' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])->count(),
            'low_stock_alerts' => $this->getLowStockItems()->count(),
            'overdue_orders' => $this->getOverdueDeliveries()->count(),
            'total_active_suppliers' => Supplier::where('is_active', true)->count(),
            'average_delivery_time' => $this->getAverageDeliveryTime(),
            'last_updated' => Carbon::now()->toISOString()
        ];

        return response()->json($summary);
    }

    public function getDashboardMetrics()
    {
        return response()->json([
            'low_stock_count' => $this->getLowStockItems()->count(),
            'open_po_value' => $this->getOpenPurchaseOrderValue(),
            'open_po_count' => $this->getOpenPurchaseOrderCount(),
            'overdue_deliveries' => $this->getOverdueDeliveries()->count(),
            'last_updated' => Carbon::now()->toISOString()
        ]);
    }

    private function getLowStockItems()
    {
        $items = Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->map(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                if ($currentStock <= $item->reorder_point) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'current_stock' => $currentStock,
                        'min_stock' => $item->reorder_point,
                        'max_stock' => $item->max_stock_level,
                        'unit' => $item->unit ? $item->unit->symbol : '',
                        'percentage' => $item->reorder_point > 0 ? round(($currentStock / $item->reorder_point) * 100, 1) : 0
                    ];
                }
                return null;
            })
            ->filter()
            ->sortByDesc('percentage')
            ->values();

        return $items;
    }

    private function getOpenPurchaseOrderValue()
    {
        $value = PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->sum('grand_total');
        return $value ?: 0;
    }

    private function getOpenPurchaseOrderCount()
    {
        return PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])->count();
    }

    private function getOverdueDeliveries()
    {
        $overduePOs = PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->where('expected_delivery_date', '<', Carbon::now()->toDateString())
            ->whereNull('actual_delivery_date')
            ->with('supplier')
            ->get()
            ->map(function ($po) {
                $daysOverdue = Carbon::parse($po->expected_delivery_date)->diffInDays(Carbon::now());
                return [
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'supplier_contact_person' => $po->supplier ? $po->supplier->contact_person : null,
                    'supplier_phone' => $po->supplier ? $po->supplier->phone : null,
                    'supplier_email' => $po->supplier ? $po->supplier->email : null,
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'days_overdue' => $daysOverdue,
                    'grand_total' => $po->grand_total,
                    'priority' => $daysOverdue > 7 ? 'urgent' : ($daysOverdue > 3 ? 'high' : 'normal')
                ];
            })
            ->sortByDesc('days_overdue')
            ->values();
        return $overduePOs;
    }

    private function getRecentPurchaseOrders()
    {
        $recentOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'status' => $po->status,
                    'total_amount' => $po->grand_total,
                    'order_date' => $po->order_date,
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'item_count' => $po->purchaseOrderItems->count(),
                    'created_at' => $po->created_at,
                    'is_overdue' => $this->isOrderOverdue($po)
                ];
            });
        return $recentOrders;
    }

    private function getFrequentSuppliers()
    {
        $frequentSuppliers = PurchaseOrder::selectRaw('supplier_id, COUNT(*) as order_count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['sent', 'confirmed', 'partial', 'completed'])
            ->groupBy('supplier_id')
            ->orderByDesc('order_count')
            ->limit(10)
            ->with('supplier')
            ->get()
            ->map(function ($result) {
                if ($result->supplier) {
                    return [
                        'id' => $result->supplier->id,
                        'name' => $result->supplier->name,
                        'supplier_code' => $result->supplier->supplier_code,
                        'contact_person' => $result->supplier->contact_person,
                        'phone' => $result->supplier->phone,
                        'email' => $result->supplier->email,
                        'order_count' => $result->order_count,
                        'rating' => $result->supplier->rating
                    ];
                }
                return null;
            })
            ->filter()
            ->values();

        return $frequentSuppliers;
    }

    private function isOrderOverdue($purchaseOrder)
    {
        return $purchaseOrder->expected_delivery_date && 
               $purchaseOrder->expected_delivery_date->isPast() && 
               !in_array($purchaseOrder->status, ['completed', 'cancelled']);
    }

    private function getAverageDeliveryTime()
    {
        $completedOrders = PurchaseOrder::where('status', 'completed')
            ->whereNotNull('actual_delivery_date')
            ->whereNotNull('order_date')
            ->limit(50)
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedOrders as $order) {
            $days = $order->order_date->diffInDays($order->actual_delivery_date);
            $totalDays += $days;
            $count++;
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }
}
