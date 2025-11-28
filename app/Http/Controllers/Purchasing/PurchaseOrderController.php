<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestPurchaseOrderLink;
use App\Models\SupplierItem;
use App\Models\Item;
use App\Models\PurchaseRequestItem;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createPurchaseOrder()
    {
        $approvedRequests = PurchaseRequest::where('status', 'approved')
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->paginate(15);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $departments = PurchaseRequest::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $approvedRequestsForJS = PurchaseRequest::where('status', 'approved')
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->get()
            ->map(function ($pr) {
                return [
                    'id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                    'department' => $pr->department,
                    'priority' => $pr->priority,
                    'total_estimated_cost' => (float) $pr->total_estimated_cost,
                    'request_date' => $pr->request_date ? Carbon::parse($pr->request_date)->format('Y-m-d') : null,
                    'requestedBy' => [
                        'name' => $pr->requestedBy->name ?? 'N/A'
                    ],
                    'purchaseRequestItems' => $pr->purchaseRequestItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_id' => $item->item_id,
                            'quantity_requested' => (float) $item->quantity_requested,
                            'unit_price_estimate' => (float) $item->unit_price_estimate,
                            'total_estimated_cost' => (float) $item->total_estimated_cost,
                            'item' => [
                                'id' => $item->item->id,
                                'name' => $item->item->name,
                                'item_code' => $item->item->item_code,
                                'category' => [
                                    'name' => $item->item->category->name ?? 'No Category'
                                ],
                                'unit' => [
                                    'symbol' => $item->item->unit->symbol ?? 'pcs'
                                ]
                            ]
                        ];
                    })->toArray()
                ];
            })->toArray();

        return view('Purchasing.purchase_orders.create_po', compact('approvedRequests', 'suppliers', 'departments', 'approvedRequestsForJS'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'selected_pr_ids' => 'required|string',
            'save_option' => 'required|in:draft,create',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.source_pr_id' => 'required|exists:purchase_requests,id',
        ]);

        $selectedPRIds = array_map('intval', explode(',', $request->selected_pr_ids));

        $purchaseRequests = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->where('status', 'approved')
            ->get();

        if ($purchaseRequests->count() !== count($selectedPRIds)) {
            return redirect()->back()
                ->with('error', 'Some selected purchase requests are invalid or not approved.')
                ->withInput();
        }

        $poNumber = $this->generatePONumber();

        $totalAmount = 0;
        $poItems = [];

        foreach ($request->items as $itemData) {
            $quantity = (float) $itemData['quantity_ordered'];
            $unitPrice = (float) $itemData['unit_price'];
            $totalPrice = $quantity * $unitPrice;
            $totalAmount += $totalPrice;

            $poItems[] = [
                'item_id' => $itemData['item_id'],
                'quantity_ordered' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'source_pr_id' => $itemData['source_pr_id'],
            ];
        }

        $status = $request->save_option === 'create' ? 'sent' : 'draft';

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $request->supplier_id,
            'order_date' => Carbon::now()->toDateString(),
            'expected_delivery_date' => $request->expected_delivery_date,
            'status' => $status,
            'total_amount' => $totalAmount,
            'grand_total' => $totalAmount,
            'payment_terms' => 30,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        foreach ($poItems as $item) {
            PurchaseOrderItem::create(array_merge($item, [
                'purchase_order_id' => $purchaseOrder->id,
                'created_at' => Carbon::now(),
            ]));
        }

        foreach ($selectedPRIds as $prId) {
            PurchaseRequestPurchaseOrderLink::create([
                'purchase_request_id' => $prId,
                'purchase_order_id' => $purchaseOrder->id,
                'consolidated_by' => Auth::id(),
                'consolidated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        }

        PurchaseRequest::whereIn('id', $selectedPRIds)->update(['status' => 'converted']);

        if ($request->save_option === 'create') {
            return redirect()->route('purchasing.po.open')
                ->with('success', "Purchase Order {$poNumber} created and sent successfully from " . count($selectedPRIds) . " purchase request(s)!");
        } else {
            return redirect()->route('purchasing.po.drafts')
                ->with('success', "Purchase Order {$poNumber} saved as draft successfully from " . count($selectedPRIds) . " purchase request(s)!");
        }
    }

    public function openOrders(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
            ->whereIn('status', ['sent', 'confirmed', 'partial']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('supplier_code', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('expected_delivery_date', 'asc');

        $openOrders = $query->paginate($request->get('per_page', 15));

        return view('Purchasing.purchase_orders.open_orders', compact('openOrders'));
    }

    public function drafts()
    {
        $draftOrders = PurchaseOrder::where('status', 'draft')
            ->with([
                'supplier', 
                'purchaseOrderItems',
                'sourcePurchaseRequests',
                'createdBy'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $draftOrders->getCollection()->transform(function ($order) {
            $order->total_items_count = $order->purchaseOrderItems->count();
            $order->total_quantity_ordered = $order->purchaseOrderItems->sum('quantity_ordered');
            $order->total_quantity_received = $order->purchaseOrderItems->sum('quantity_received');
            $order->formatted_total = '₱' . number_format($order->grand_total, 2);
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-800',
                'sent' => 'bg-blue-100 text-blue-800',
                'confirmed' => 'bg-yellow-100 text-yellow-800',
                'partial' => 'bg-orange-100 text-orange-800',
                'completed' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800'
            ];
            $order->status_badge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ' . 
                                  ($statusColors[$order->status] ?? 'bg-gray-100 text-gray-800') . '">' . 
                                  ucfirst($order->status) . '</span>';
            if ($order->expected_delivery_date) {
                $daysDiff = $order->expected_delivery_date->diffInDays(now());
                if ($daysDiff < 0) {
                    $order->delivery_status = ['text' => abs($daysDiff) . ' days overdue', 'class' => 'text-red-500'];
                } elseif ($daysDiff <= 3) {
                    $order->delivery_status = ['text' => $daysDiff . ' days remaining', 'class' => 'text-orange-500'];
                } else {
                    $order->delivery_status = ['text' => $daysDiff . ' days remaining', 'class' => 'text-green-500'];
                }
            } else {
                $order->delivery_status = ['text' => 'Not scheduled', 'class' => 'text-gray-500'];
            }
            $order->action_capabilities = [
                'can_edit' => in_array($order->status, ['draft']),
                'can_submit' => in_array($order->status, ['draft']),
                'can_delete' => in_array($order->status, ['draft']),
            ];
            return $order;
        });

        return view('Purchasing.purchase_orders.drafts', compact('draftOrders'));
    }

    public function showPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'purchaseOrderItems.item', 'createdBy', 'approvedBy']);
        return view('Purchasing.purchase_orders.show', compact('purchaseOrder'));
    }

    public function printPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'purchaseOrderItems.item.unit',
            'purchaseOrderItems.item.category',
            'createdBy',
            'approvedBy',
            'sourcePurchaseRequests'
        ]);
        return view('Purchasing.purchase_orders.print_po', compact('purchaseOrder'));
    }

    public function submitPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be submitted.');
        }
        $purchaseOrder->update([
            'status' => 'sent',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);
        return redirect()->route('purchasing.po.drafts')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} submitted for approval successfully!");
    }

    public function acknowledgePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'sent') {
            return redirect()->back()->with('error', 'Only sent purchase orders can be acknowledged.');
        }
        $purchaseOrder->update([
            'status' => 'confirmed',
            'acknowledged_by' => Auth::id(),
            'acknowledged_at' => Carbon::now(),
        ]);
        return redirect()->route('purchasing.po.open')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} acknowledged successfully!");
    }

    public function editPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be edited.');
        }
        return redirect()->route('purchasing.po.create')
            ->with('edit_order', $purchaseOrder->id);
    }

    public function destroyPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be deleted.');
        }
        if ($purchaseOrder->sourcePurchaseRequests->count() > 0) {
            $prIds = $purchaseOrder->sourcePurchaseRequests->pluck('id');
            PurchaseRequest::whereIn('id', $prIds)->update(['status' => 'approved']);
            PurchaseRequestPurchaseOrderLink::where('purchase_order_id', $purchaseOrder->id)->delete();
        }
        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();
        return redirect()->route('purchasing.po.drafts')
            ->with('success', "Purchase Order {$poNumber} deleted successfully!");
    }

    /**
     * Show partial orders - redirect to history since partial orders view was removed
     */
    public function partialOrders()
    {
        return redirect('/purchasing/po/history');
    }

    /**
     * API endpoint to search items for PO creation
     */
    public function searchItems(Request $request)
    {
        $query = $request->get('q', '');
        $items = \App\Models\Item::where('is_active', true)
            ->where(function($q2) use ($query) {
                if ($query) {
                    $q2->where('name', 'ilike', "%{$query}%")
                       ->orWhere('item_code', 'ilike', "%{$query}%");
                }
            })
            ->with(['unit', 'currentStockRecord'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'unit' => $item->unit ? $item->unit->symbol : '',
                'current_stock' => $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0,
                'cost_price' => $item->cost_price,
                'reorder_point' => $item->reorder_point
            ];
        }));
    }

    /**
     * Get purchase request details for purchasing users
     */
    public function getPurchaseRequestDetails(PurchaseRequest $purchaseRequest)
    {
        try {
            if ($purchaseRequest->status !== 'approved') {
                return response()->json([
                    'message' => 'Purchase request is not approved or not available for viewing.'
                ], 403);
            }

            $purchaseRequest->load([
                'requestedBy:id,name,email',
                'approvedBy:id,name,email',
                'purchaseRequestItems' => function($query) {
                    $query->with([
                        'item:id,name,item_code,unit_id',
                        'item.unit:id,name,symbol'
                    ]);
                }
            ]);

            return response()->json([
                'purchaseRequest' => $purchaseRequest
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching purchase request details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch purchase request details.'
            ], 500);
        }
    }

    /**
     * API endpoint to get supplier items for selected purchase requests
     * Only returns items that both: 1) are in the selected PRs, and 2) the supplier provides
     */
    public function getSupplierItemsForPRs(Request $request, Supplier $supplier)
    {
        try {
            $request->validate([
                'pr_ids' => 'required|string'
            ]);

            $prIds = array_map('intval', explode(',', $request->pr_ids));
            $prIds = array_filter($prIds);

            if (empty($prIds)) {
                return response()->json([
                    'message' => 'At least one purchase request must be selected.',
                    'items' => []
                ], 422);
            }

            $existingPRs = PurchaseRequest::whereIn('id', $prIds)->where('status', 'approved')->count();
            if ($existingPRs !== count($prIds)) {
                return response()->json([
                    'message' => 'Some selected purchase requests are invalid or not approved.',
                    'items' => []
                ], 422);
            }

            $purchaseRequests = PurchaseRequest::whereIn('id', $prIds)
                ->where('status', 'approved')
                ->with(['purchaseRequestItems.item.unit'])
                ->get();

            if ($purchaseRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No valid approved purchase requests found.',
                    'items' => []
                ]);
            }

            $prItemIds = collect();
            foreach ($purchaseRequests as $pr) {
                foreach ($pr->purchaseRequestItems as $item) {
                    $prItemIds->push($item->item_id);
                }
            }
            $prItemIds = $prItemIds->unique()->values();

            $supplierItems = SupplierItem::where('supplier_id', $supplier->id)
                ->whereIn('item_id', $prItemIds)
                ->with(['item.unit', 'item.category'])
                ->get()
                ->map(function ($supplierItem) use ($purchaseRequests) {
                    $totalRequestedQuantity = 0;
                    $sourcePRs = [];
                    foreach ($purchaseRequests as $pr) {
                        foreach ($pr->purchaseRequestItems as $prItem) {
                            if ($prItem->item_id === $supplierItem->item_id) {
                                $totalRequestedQuantity += $prItem->quantity_requested;
                                $sourcePRs[] = [
                                    'pr_id' => $pr->id,
                                    'pr_number' => $pr->pr_number,
                                    'quantity' => $prItem->quantity_requested
                                ];
                            }
                        }
                    }
                    return [
                        'item_id' => (int)$supplierItem->item_id,
                        'item_name' => $supplierItem->item->name,
                        'item_code' => $supplierItem->item->item_code,
                        'unit_symbol' => $supplierItem->item->unit->symbol ?? '',
                        'category' => $supplierItem->item->category->name ?? 'No Category',
                        'supplier_item_code' => $supplierItem->supplier_item_code,
                        'unit_price' => (float)$supplierItem->unit_price,
                        'minimum_order_quantity' => (float)$supplierItem->minimum_order_quantity,
                        'lead_time_days' => (int)$supplierItem->lead_time_days,
                        'is_preferred' => (bool)$supplierItem->is_preferred,
                        'total_requested_quantity' => (float)$totalRequestedQuantity,
                        'source_prs' => $sourcePRs,
                        'estimated_total' => (float)($totalRequestedQuantity * $supplierItem->unit_price)
                    ];
                })
                ->sortByDesc('is_preferred')
                ->values();

            $availableItems = $supplierItems->where('unit_price', '>', 0);
            $unavailableItems = $supplierItems->filter(function($item) {
                return $item['unit_price'] <= 0;
            });

            return response()->json([
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'supplier_code' => $supplier->supplier_code,
                    'payment_terms' => $supplier->payment_terms
                ],
                'items' => [
                    'available' => $availableItems->values(),
                    'unavailable' => $unavailableItems->values()
                ],
                'summary' => [
                    'total_items_requested' => (int)$prItemIds->count(),
                    'items_available_from_supplier' => (int)$availableItems->count(),
                    'items_not_available' => (int)$unavailableItems->count(),
                    'total_estimated_cost' => (float)$availableItems->sum('estimated_total')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier items for PRs: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch supplier items for selected purchase requests.',
                'error' => $e->getMessage(),
                'items' => []
            ], 500);
        }
    }

    private function generatePONumber()
    {
        $year = date('Y');
        $lastPo = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
            ->orderBy('po_number', 'desc')
            ->first();
        if ($lastPo) {
            $lastNumber = (int) substr($lastPo->po_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return "PO-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
