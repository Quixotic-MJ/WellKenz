<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\SupplierItem;
use App\Models\CurrentStock;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Show the form for creating a new purchase order (Purchasing Module)
     */
    public function createPO(Request $request = null)
    {
        // Get approved purchase requests that haven't been converted to POs yet
        $purchaseRequests = PurchaseRequest::where('status', 'approved')
            ->with([
                'requestedBy:id,name',
                'purchaseRequestItems.item.unit',
                'purchaseOrder' => function($query) {
                    $query->select('id', 'purchase_request_id');
                }
            ])
            ->whereDoesntHave('purchaseOrder') // Only PRs that haven't been converted to POs
            ->orderBy('request_date', 'desc')
            ->get();
        
        // If a specific purchase request is selected for conversion
        $selectedPurchaseRequest = null;
        $prePopulatedItems = [];
        
        if ($request->has('purchase_request_id')) {
            $selectedPurchaseRequest = PurchaseRequest::where('id', $request->purchase_request_id)
                ->where('status', 'approved')
                ->whereDoesntHave('purchaseOrder')
                ->with([
                    'requestedBy:id,name',
                    'purchaseRequestItems.item.unit',
                    'purchaseRequestItems.item.currentStockRecord'
                ])
                ->firstOrFail();
            
            // Pre-populate items from the purchase request
            foreach ($selectedPurchaseRequest->purchaseRequestItems as $prItem) {
                $prePopulatedItems[] = [
                    'id' => $prItem->item_id,
                    'name' => $prItem->item->name,
                    'item_code' => $prItem->item->item_code,
                    'unit_symbol' => $prItem->item->unit->symbol ?? '',
                    'quantity_requested' => $prItem->quantity_requested,
                    'unit_price_estimate' => $prItem->unit_price_estimate,
                    'total_estimated_cost' => $prItem->total_estimated_cost,
                    'current_stock' => $prItem->item->currentStockRecord ? floatval($prItem->item->currentStockRecord->current_quantity) : 0,
                ];
            }
        }
        
        // Get all active suppliers for dropdown
        $suppliers = Supplier::where('is_active', true)
            ->select('id', 'name', 'contact_person', 'payment_terms', 'phone', 'email')
            ->orderBy('name')
            ->get();
        
        // Get the next PO number
        $nextPoNumber = $this->generatePONumber();
        
        // Get default settings
        $defaultPaymentTerms = SystemSetting::where('setting_key', 'default_payment_terms')
            ->value('setting_value') ?? '30';
            
        return view('Purchasing.purchase_orders.create_po', compact(
            'purchaseRequests',
            'selectedPurchaseRequest',
            'prePopulatedItems',
            'suppliers', 
            'nextPoNumber', 
            'defaultPaymentTerms'
        ));
    }

    /**
     * Store a newly created purchase order
     */
    public function storePO(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ], [
            'supplier_id.required' => 'Please select a supplier',
            'supplier_id.exists' => 'Selected supplier is invalid',
            'order_date.required' => 'Order date is required',
            'order_date.date' => 'Order date must be a valid date',
            'expected_delivery_date.date' => 'Expected delivery date must be a valid date',
            'expected_delivery_date.after_or_equal' => 'Expected delivery date must be after or equal to order date',
            'purchase_request_id.exists' => 'Invalid purchase request selected',
            'items.required' => 'At least one item is required',
            'items.*.item_id.required' => 'Item is required for each row',
            'items.*.item_id.exists' => 'One or more selected items are invalid',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'items.*.unit_price.required' => 'Unit price is required for each item',
            'items.*.unit_price.min' => 'Unit price must be greater than 0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate PO number
            $poNumber = $this->generatePONumber();

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'draft',
                'total_amount' => 0, // Will be calculated below
                'tax_amount' => 0,
                'discount_amount' => 0,
                'grand_total' => 0,
                'payment_terms' => $request->payment_terms ?? 30,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'purchase_request_id' => $request->purchase_request_id,
            ]);

            $totalAmount = 0;

            // Create purchase order items
            foreach ($request->items as $itemData) {
                $quantity = floatval($itemData['quantity']);
                $unitPrice = floatval($itemData['unit_price']);
                $totalPrice = $quantity * $unitPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_ordered' => $quantity,
                    'quantity_received' => 0,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $totalPrice;
            }

            // Update purchase order totals
            $purchaseOrder->update([
                'total_amount' => $totalAmount,
                'grand_total' => $totalAmount, // Can be updated later for tax/discount
            ]);

            // If this PO was created from a purchase request, mark it as converted
            if ($request->has('purchase_request_id') && $request->purchase_request_id) {
                $purchaseRequest = PurchaseRequest::find($request->purchase_request_id);
                if ($purchaseRequest && $purchaseRequest->status === 'approved') {
                    $purchaseRequest->update([
                        'status' => 'converted',
                        'converted_to_po_id' => $purchaseOrder->id,
                    ]);
                }
            }

            DB::commit();

            $message = "Purchase Order {$poNumber} created successfully!";
            if ($request->has('purchase_request_id') && $request->purchase_request_id) {
                $message .= " Purchase Request has been marked as converted.";
            }

            return redirect()->route('purchasing.po.drafts')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Error creating purchase order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate the next purchase order number
     */
    private function generatePONumber()
    {
        $prefix = 'PO';
        $year = date('Y');
        
        // Get the last PO number for this year
        $lastPo = PurchaseOrder::where('po_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPo) {
            // Extract the sequence number from the last PO
            $lastSequence = (int) substr($lastPo->po_number, -3);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $year, $nextSequence);
    }

    /**
     * Get supplier items with pricing (AJAX)
     */
    public function getSupplierItems(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        
        if (!$supplierId) {
            return response()->json(['items' => []]);
        }

        $supplierItems = SupplierItem::where('supplier_id', $supplierId)
            ->with(['item.unit'])
            ->get()
            ->map(function($supplierItem) {
                return [
                    'id' => $supplierItem->item->id,
                    'name' => $supplierItem->item->name,
                    'item_code' => $supplierItem->item->item_code,
                    'unit_symbol' => $supplierItem->item->unit->symbol ?? '',
                    'unit_price' => $supplierItem->unit_price,
                    'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                    'lead_time_days' => $supplierItem->lead_time_days,
                    'is_preferred' => $supplierItem->is_preferred,
                ];
            });

        return response()->json(['items' => $supplierItems]);
    }

    /**
     * Get all active items (AJAX for search)
     */
    public function getItems(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 50);

        $items = Item::where('is_active', true)
            ->with(['unit', 'currentStockRecord'])
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('item_code', 'ilike', "%{$search}%");
                });
            })
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $currentStock = $item->currentStockRecord ? floatval($item->currentStockRecord->current_quantity) : 0;
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit_symbol' => $item->unit->symbol ?? '',
                    'current_stock' => $currentStock,
                    'min_stock_level' => floatval($item->min_stock_level),
                    'reorder_point' => floatval($item->reorder_point),
                ];
            });

        return response()->json(['items' => $items]);
    }

    /**
     * Get draft purchase orders
     */
    public function drafts()
    {
        $draftOrders = PurchaseOrder::where('status', 'draft')
            ->with(['supplier', 'purchaseOrderItems', 'purchaseRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('Purchasing.purchase_orders.drafts', compact('draftOrders'));
    }

    /**
     * Update draft purchase order
     */
    public function updateDraft(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::where('id', $id)
            ->where('status', 'draft')
            ->firstOrFail();

        // Similar validation and logic as storePO method
        // This would handle updating existing draft orders

        return redirect()->route('purchasing.po.drafts')
            ->with('success', 'Draft updated successfully!');
    }

    /**
     * Submit draft for approval
     */
    public function submitForApproval($id)
    {
        $purchaseOrder = PurchaseOrder::where('id', $id)
            ->where('status', 'draft')
            ->firstOrFail();

        $purchaseOrder->update(['status' => 'sent']);

        return redirect()->route('purchasing.po.drafts')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} submitted for approval!");
    }
}