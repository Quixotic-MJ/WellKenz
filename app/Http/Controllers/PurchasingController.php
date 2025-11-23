<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\CurrentStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchasingController extends Controller
{
    /**
     * Display the purchasing dashboard
     */
    public function home()
    {
        // 1. Get Low Stock Items (Below Reorder Point)
        $allItems = Item::with(['currentStockRecord', 'unit'])->where('is_active', true)->get();
        
        $lowStockItems = $allItems->filter(function($item) {
            $currentStock = $item->currentStockRecord ? floatval($item->currentStockRecord->current_quantity) : 0;
            $reorderPoint = floatval($item->reorder_point);
            $minStock = floatval($item->min_stock_level);
            
            // Consider low stock if current stock is below reorder point or min stock level
            $isLowStock = ($reorderPoint > 0 && $currentStock <= $reorderPoint) || 
                         ($minStock > 0 && $currentStock <= $minStock) ||
                         ($currentStock <= 0 && ($reorderPoint > 0 || $minStock > 0));
            
            return $isLowStock;
        })->sortBy(function($item) {
            $currentStock = $item->currentStockRecord ? floatval($item->currentStockRecord->current_quantity) : 0;
            return $currentStock;
        })->take(5)->map(function($item) {
            $currentStock = $item->currentStockRecord ? floatval($item->currentStockRecord->current_quantity) : 0;
            $reorderPoint = floatval($item->reorder_point);
            $minStock = floatval($item->min_stock_level);
            
            return [
                'name' => $item->name,
                'current_stock' => $currentStock,
                'min_stock' => max($reorderPoint, $minStock),
                'unit' => $item->unit->symbol ?? 'pcs',
                'item_id' => $item->id
            ];
        })->values();

        // 2. Get Open Purchase Orders (Financial Commitment)
        $openPurchaseOrders = PurchaseOrder::whereIn('status', ['sent', 'confirmed'])
            ->with(['supplier', 'purchaseOrderItems'])
            ->get();
        
        $openPoValue = $openPurchaseOrders->sum('grand_total');
        $openPoCount = $openPurchaseOrders->count();

        // 3. Get Overdue Deliveries
        $overdueDeliveries = PurchaseOrder::whereIn('status', ['sent', 'confirmed'])
            ->where('expected_delivery_date', '<', now()->toDateString())
            ->with(['supplier'])
            ->orderBy('expected_delivery_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($po) {
                // Calculate days overdue correctly (current date - expected date)
                $currentDate = now()->toDateString();
                $expectedDate = $po->expected_delivery_date->toDateString();
                $daysOverdue = \Carbon\Carbon::parse($currentDate)->diffInDays(\Carbon\Carbon::parse($expectedDate));
                
                return [
                    'supplier_name' => $po->supplier->name,
                    'supplier_phone' => $po->supplier->phone,
                    'supplier_email' => $po->supplier->email,
                    'supplier_contact_person' => $po->supplier->contact_person,
                    'po_number' => $po->po_number,
                    'days_overdue' => $daysOverdue,
                    'expected_date' => $po->expected_delivery_date->format('Y-m-d')
                ];
            });

        // 4. Get Recent Purchase Orders
        $recentPurchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems.item'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($po) {
                $itemCount = $po->purchaseOrderItems->count();
                $totalQuantity = $po->purchaseOrderItems->sum('quantity_ordered');
                
                return [
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier->name,
                    'total_amount' => $po->grand_total,
                    'status' => $po->status,
                    'item_count' => $itemCount,
                    'total_quantity' => $totalQuantity,
                    'created_at' => $po->created_at
                ];
            });

        // 5. Get Frequent Suppliers (for quick lookup)
        $frequentSuppliers = Supplier::where('is_active', true)
            ->withCount(['purchaseOrders' => function($query) {
                $query->where('created_at', '>=', now()->subMonths(3));
            }])
            ->orderBy('purchase_orders_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($supplier) {
                return [
                    'name' => $supplier->name,
                    'phone' => $supplier->phone,
                    'order_count' => $supplier->purchase_orders_count
                ];
            });

        return view('Purchasing.home', compact(
            'lowStockItems',
            'openPoValue',
            'openPoCount',
            'overdueDeliveries',
            'recentPurchaseOrders',
            'frequentSuppliers'
        ));
    }

    /**
     * Display a listing of purchase orders
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.item', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('Inventory.purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
        
        return view('Inventory.purchase-orders.create', compact('suppliers', 'items'));
    }

    /**
     * Display the specified purchase order
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier', 
            'items.item.unit', 
            'createdBy',
            'approvedBy'
        ])->findOrFail($id);

        return view('Inventory.purchase-orders.show', compact('purchaseOrder'));
    }
}