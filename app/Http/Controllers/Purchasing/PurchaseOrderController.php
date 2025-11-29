<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Get IDs of purchase requests that have remaining items (not fully ordered)
     */
    private function getPRIdsWithRemainingItems(): array
    {
        // Get all approved PR IDs
        $approvedPrIds = PurchaseRequest::where('status', 'approved')->pluck('id')->toArray();
        
        if (empty($approvedPrIds)) {
            return [];
        }

        // Get ordered quantities for all PRs at once
        $orderedQuantities = $this->getOrderedQuantitiesForPRs($approvedPrIds);
        
        $prIdsWithRemainingItems = [];
        
        foreach ($approvedPrIds as $prId) {
            $purchaseRequest = PurchaseRequest::where('id', $prId)->with('purchaseRequestItems')->first();
            
            if (!$purchaseRequest) {
                continue;
            }
            
            // Check if this PR has any items with remaining quantities
            $hasRemainingItems = $purchaseRequest->purchaseRequestItems->some(function ($item) use ($orderedQuantities, $prId) {
                $ordered = $orderedQuantities[$prId][$item->item_id] ?? 0;
                return $ordered < (float) $item->quantity_requested;
            });
            
            if ($hasRemainingItems) {
                $prIdsWithRemainingItems[] = $prId;
            }
        }
        
        return $prIdsWithRemainingItems;
    }

    public function createPurchaseOrder()
    {
        $orderedQuantitiesSub = DB::table('purchase_request_items as pri2')
            ->select('pri2.id as pr_item_id', DB::raw('COALESCE(SUM(poi.quantity_ordered), 0) as ordered_qty'))
            ->leftJoin('purchase_request_purchase_order_link as link', 'link.purchase_request_id', '=', 'pri2.purchase_request_id')
            ->leftJoin('purchase_order_items as poi', function ($join) {
                $join->on('poi.purchase_order_id', '=', 'link.purchase_order_id')
                    ->on('poi.item_id', '=', 'pri2.item_id');
            })
            ->groupBy('pri2.id');

        // Simple approach: Show all approved PRs first, then filter out fully ordered ones
        $baseApprovedQuery = PurchaseRequest::where('status', 'approved');
        
        // Get PR IDs that have remaining items
        $prIdsWithRemainingItems = $this->getPRIdsWithRemainingItems();
        
        // Only include PRs that have remaining items
        $baseApprovedQuery->whereIn('id', $prIdsWithRemainingItems);

        $approvedRequests = (clone $baseApprovedQuery)
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->paginate(15);
        

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $departments = (clone $baseApprovedQuery)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $approvedRequestsForJS = (clone $baseApprovedQuery)
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
            'selected_pr_ids' => 'required',
            'save_option' => 'required|in:create',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'nullable|numeric|min:0.001',
            'items.*.quantity_ordered' => 'nullable|numeric|min:0.001',
            'items.*.price' => 'nullable|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0.01',
            'items.*.selected' => 'nullable|boolean',
        ]);

        $selectedPRInput = $request->selected_pr_ids;
        if (is_string($selectedPRInput)) {
            $selectedPRIds = array_map('intval', array_filter(explode(',', $selectedPRInput)));
        } elseif (is_array($selectedPRInput)) {
            $selectedPRIds = array_map('intval', array_filter($selectedPRInput));
        } else {
            $selectedPRIds = [];
        }
        $selectedPRIds = array_values(array_unique($selectedPRIds));

        if (empty($selectedPRIds)) {
            throw ValidationException::withMessages([
                'selected_pr_ids' => 'Please select at least one purchase request.',
            ]);
        }

        $purchaseRequests = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->where('status', 'approved')
            ->with('purchaseRequestItems')
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
            $isSelected = filter_var($itemData['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$isSelected) {
                continue;
            }

            $itemId = (int) ($itemData['item_id'] ?? 0);
            $quantity = isset($itemData['quantity'])
                ? (float) $itemData['quantity']
                : (float) ($itemData['quantity_ordered'] ?? 0);
            $unitPrice = isset($itemData['price'])
                ? (float) $itemData['price']
                : (float) ($itemData['unit_price'] ?? 0);

            if ($itemId <= 0 || $quantity <= 0 || $unitPrice <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Selected items must include valid quantity and price values.',
                ]);
            }

            $totalPrice = $quantity * $unitPrice;
            $totalAmount += $totalPrice;

            $poItems[] = [
                'item_id' => $itemId,
                'quantity_ordered' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        if (empty($poItems)) {
            throw ValidationException::withMessages([
                'items' => 'Please select at least one item to include in the purchase order.',
            ]);
        }

        $status = 'sent';

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
            PurchaseRequestPurchaseOrderLink::updateOrCreate(
                [
                    'purchase_request_id' => $prId,
                    'purchase_order_id' => $purchaseOrder->id,
                ],
                [
                    'consolidated_by' => Auth::id(),
                    'consolidated_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                ]
            );
        }

        $orderedQuantities = $this->getOrderedQuantitiesForPRs($selectedPRIds);

        foreach ($purchaseRequests as $purchaseRequest) {
            $prId = $purchaseRequest->id;
            $items = $purchaseRequest->purchaseRequestItems;

            $fullyOrdered = $items->every(function ($item) use ($orderedQuantities, $prId) {
                $ordered = $orderedQuantities[$prId][$item->item_id] ?? 0;
                return $ordered >= (float) $item->quantity_requested;
            });

            if ($fullyOrdered && $purchaseRequest->status !== 'converted') {
                $purchaseRequest->update(['status' => 'converted']);
            }
        }

        $message = "Purchase Order {$poNumber} created successfully from " . count($selectedPRIds) . " purchase request(s)!";
        
        // Add information about remaining items if any PRs have partial orders
        $remainingPRs = [];
        foreach ($purchaseRequests as $pr) {
            if ($pr->status === 'approved') { // Only check PRs that weren't fully converted
                $prId = $pr->id;
                $items = $pr->purchaseRequestItems;
                $hasRemainingItems = $items->some(function ($item) use ($orderedQuantities, $prId) {
                    $ordered = $orderedQuantities[$prId][$item->item_id] ?? 0;
                    return $ordered < (float) $item->quantity_requested;
                });
                
                if ($hasRemainingItems) {
                    $remainingPRs[] = $pr->pr_number;
                }
            }
        }
        
        if (!empty($remainingPRs)) {
            $message .= " Remaining items can be ordered from PRs: " . implode(', ', $remainingPRs);
        }

        // Redirect back to PO creation page so users can create more POs for remaining items
        return redirect()->route('purchasing.po.create')
            ->with('success', $message)
            ->with('refresh_needed', true);
    }

    /**
     * Aggregate PR items, assign default suppliers based on tie-breakers, and return grouped buckets
     */
    public function groupPurchaseRequestItems(Request $request)
    {
        try {
            $validated = $request->validate([
                'pr_ids' => 'required|array|min:1',
                'pr_ids.*' => 'integer'
            ]);

            $prIds = array_values(array_unique(array_map('intval', $validated['pr_ids'])));

            $approvedCount = PurchaseRequest::whereIn('id', $prIds)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount !== count($prIds)) {
                return response()->json([
                    'message' => 'Some selected purchase requests are invalid or not approved.',
                    'buckets' => [],
                    'unassignedItems' => [],
                ], 422);
            }

            $purchaseRequestItems = PurchaseRequestItem::with([
                    'item:id,name,item_code,unit_id',
                    'item.unit:id,symbol',
                    'purchaseRequest:id,pr_number'
                ])
                ->whereIn('purchase_request_id', $prIds)
                ->get();

            if ($purchaseRequestItems->isEmpty()) {
                return response()->json([
                    'buckets' => [],
                    'unassignedItems' => [],
                ]);
            }

            $orderedQuantities = DB::table('purchase_request_items as pri')
                ->select('pri.id as pr_item_id', DB::raw('COALESCE(SUM(poi.quantity_ordered), 0) as quantity_ordered'))
                ->leftJoin('purchase_request_purchase_order_link as link', 'link.purchase_request_id', '=', 'pri.purchase_request_id')
                ->leftJoin('purchase_order_items as poi', function ($join) {
                    $join->on('poi.purchase_order_id', '=', 'link.purchase_order_id')
                        ->on('poi.item_id', '=', 'pri.item_id');
                })
                ->whereIn('pri.purchase_request_id', $prIds)
                ->groupBy('pri.id')
                ->pluck('quantity_ordered', 'pr_item_id');

            $aggregatedItems = [];

            foreach ($purchaseRequestItems as $prItem) {
                $requested = (float) $prItem->quantity_requested;
                $ordered = (float) ($orderedQuantities[$prItem->id] ?? 0);
                $remaining = round($requested - $ordered, 3);

                if ($remaining <= 0) {
                    continue;
                }

                $itemId = (int) $prItem->item_id;

                if (!isset($aggregatedItems[$itemId])) {
                    $aggregatedItems[$itemId] = [
                        'item_id' => $itemId,
                        'item_name' => $prItem->item->name ?? 'Unknown Item',
                        'item_code' => $prItem->item->item_code ?? '',
                        'unit_symbol' => $prItem->item->unit->symbol ?? '',
                        'qty_remaining' => 0,
                        'source_prs' => [],
                    ];
                }

                $aggregatedItems[$itemId]['qty_remaining'] += $remaining;
                $aggregatedItems[$itemId]['source_prs'][] = [
                    'pr_id' => (int) $prItem->purchase_request_id,
                    'pr_number' => $prItem->purchaseRequest->pr_number ?? '',
                    'qty_remaining' => $remaining,
                ];
            }

            if (empty($aggregatedItems)) {
                return response()->json([
                    'buckets' => [],
                    'unassignedItems' => [],
                ]);
            }

            $itemIds = array_keys($aggregatedItems);

            $supplierItems = SupplierItem::with(['supplier:id,name,supplier_code,payment_terms'])
                ->whereIn('item_id', $itemIds)
                ->get()
                ->groupBy('item_id');

            $buckets = [];
            $unassignedItems = [];
            $totalItems = 0;

            foreach ($aggregatedItems as $itemId => $itemData) {
                $supplierOptions = $supplierItems->get($itemId, collect());

                if ($supplierOptions->isEmpty()) {
                    $unassignedItems[] = $itemData;
                    continue;
                }

                $rankedSuppliers = $supplierOptions->map(function (SupplierItem $supplierItem) {
                    $supplier = $supplierItem->supplier;
                    return [
                        'supplier_id' => (int) $supplierItem->supplier_id,
                        'supplier_name' => $supplier->name ?? 'Unknown Supplier',
                        'supplier_code' => $supplier->supplier_code ?? null,
                        'payment_terms' => $supplier->payment_terms ?? null,
                        'unit_price' => (float) $supplierItem->unit_price,
                        'lead_time_days' => $supplierItem->lead_time_days ?? null,
                        'is_preferred' => (bool) $supplierItem->is_preferred,
                    ];
                })->values()->all();

                usort($rankedSuppliers, function ($a, $b) {
                    if ($a['is_preferred'] !== $b['is_preferred']) {
                        return $a['is_preferred'] ? -1 : 1;
                    }

                    $priceComparison = $a['unit_price'] <=> $b['unit_price'];
                    if ($priceComparison !== 0) {
                        return $priceComparison;
                    }

                    $leadA = $a['lead_time_days'] ?? PHP_INT_MAX;
                    $leadB = $b['lead_time_days'] ?? PHP_INT_MAX;
                    $leadComparison = $leadA <=> $leadB;
                    if ($leadComparison !== 0) {
                        return $leadComparison;
                    }

                    return $a['supplier_id'] <=> $b['supplier_id'];
                });

                $defaultSupplier = $rankedSuppliers[0];
                $defaultSupplierId = $defaultSupplier['supplier_id'];
                $defaultPrice = $defaultSupplier['unit_price'];

                $alternateSuppliers = array_map(function ($option) use ($defaultPrice) {
                    return [
                        'supplier_id' => $option['supplier_id'],
                        'supplier_name' => $option['supplier_name'],
                        'unit_price' => $option['unit_price'],
                        'lead_time_days' => $option['lead_time_days'],
                        'is_preferred' => $option['is_preferred'],
                        'price_difference' => round($option['unit_price'] - $defaultPrice, 2),
                    ];
                }, array_slice($rankedSuppliers, 1));

                if (!isset($buckets[$defaultSupplierId])) {
                    $buckets[$defaultSupplierId] = [
                        'supplier' => [
                            'id' => $defaultSupplier['supplier_id'],
                            'name' => $defaultSupplier['supplier_name'],
                            'supplier_code' => $defaultSupplier['supplier_code'],
                            'payment_terms' => $defaultSupplier['payment_terms'],
                        ],
                        'items' => [],
                        'totals' => [
                            'estimated_amount' => 0,
                        ],
                    ];
                }

                $lineTotal = $itemData['qty_remaining'] * $defaultPrice;

                $buckets[$defaultSupplierId]['items'][] = [
                    'item_id' => $itemData['item_id'],
                    'item_name' => $itemData['item_name'],
                    'item_code' => $itemData['item_code'],
                    'unit_symbol' => $itemData['unit_symbol'],
                    'qty_remaining' => $itemData['qty_remaining'],
                    'source_prs' => $itemData['source_prs'],
                    'suggested_price' => $defaultPrice,
                    'estimated_total' => round($lineTotal, 2),
                    'default_supplier_id' => $defaultSupplierId,
                    'current_supplier_id' => $defaultSupplierId,
                    'alternate_suppliers' => $alternateSuppliers,
                ];

                $buckets[$defaultSupplierId]['totals']['estimated_amount'] += $lineTotal;
                $totalItems++;
            }

            $bucketList = array_map(function ($bucket) {
                $bucket['totals']['item_count'] = count($bucket['items']);
                $bucket['totals']['estimated_amount'] = round($bucket['totals']['estimated_amount'], 2);
                return $bucket;
            }, array_values($buckets));

            return response()->json([
                'buckets' => $bucketList,
                'unassignedItems' => array_values($unassignedItems),
                'summary' => [
                    'total_buckets' => count($bucketList),
                    'total_items' => $totalItems,
                    'unassigned_items' => count($unassignedItems),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error grouping PR items for shopping cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to analyze items for selected purchase requests.',
                'error' => $e->getMessage(),
                'buckets' => [],
                'unassignedItems' => [],
            ], 500);
        }
    }

    /**
     * API endpoint used by the shopping cart UI to fetch PR items with remaining quantities
     */
    public function getPurchaseRequestItems(Request $request)
    {
        try {
            $validated = $request->validate([
                'pr_ids' => 'required|array|min:1',
                'pr_ids.*' => 'integer',
            ]);

            $prIds = array_values(array_unique(array_map('intval', $validated['pr_ids'])));

            $approvedCount = PurchaseRequest::whereIn('id', $prIds)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount !== count($prIds)) {
                return response()->json([
                    'message' => 'Some selected purchase requests are invalid or not approved.',
                    'items' => [],
                ], 422);
            }

            $purchaseRequestItems = PurchaseRequestItem::with([
                    'item:id,name,item_code',
                    'purchaseRequest:id,pr_number',
                ])
                ->whereIn('purchase_request_id', $prIds)
                ->get();

            if ($purchaseRequestItems->isEmpty()) {
                return response()->json([
                    'items' => [],
                ]);
            }

            $orderedQuantities = DB::table('purchase_request_items as pri')
                ->select('pri.id as pr_item_id', DB::raw('COALESCE(SUM(poi.quantity_ordered), 0) as quantity_ordered'))
                ->leftJoin('purchase_request_purchase_order_link as link', 'link.purchase_request_id', '=', 'pri.purchase_request_id')
                ->leftJoin('purchase_order_items as poi', function ($join) {
                    $join->on('poi.purchase_order_id', '=', 'link.purchase_order_id')
                        ->on('poi.item_id', '=', 'pri.item_id');
                })
                ->whereIn('pri.purchase_request_id', $prIds)
                ->groupBy('pri.id')
                ->pluck('quantity_ordered', 'pr_item_id');

            $suggestedPrices = SupplierItem::query()
                ->select('item_id', DB::raw('MIN(unit_price) as price'))
                ->whereIn('item_id', $purchaseRequestItems->pluck('item_id')->unique())
                ->where('unit_price', '>', 0)
                ->groupBy('item_id')
                ->pluck('price', 'item_id');

            $items = $purchaseRequestItems->map(function ($prItem) use ($orderedQuantities, $suggestedPrices) {
                $qtyRequested = (float) $prItem->quantity_requested;
                $ordered = (float) ($orderedQuantities[$prItem->id] ?? 0);
                $remaining = round($qtyRequested - $ordered, 3);

                if ($remaining <= 0) {
                    return null;
                }

                return [
                    'item_id' => (int) $prItem->item_id,
                    'item_name' => $prItem->item->name ?? 'Unknown Item',
                    'item_code' => $prItem->item->item_code ?? '',
                    'pr_number' => $prItem->purchaseRequest->pr_number ?? '',
                    'pr_id' => (int) $prItem->purchase_request_id,
                    'qty_requested' => $qtyRequested,
                    'qty_remaining' => $remaining,
                    'suggested_price' => (float) ($suggestedPrices[$prItem->item_id] ?? 0),
                ];
            })
                ->filter()
                ->values();

            return response()->json([
                'items' => $items,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching PR items for shopping cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch items for selected purchase requests.',
                'error' => $e->getMessage(),
                'items' => [],
            ], 500);
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

        $sort = $request->get('sort', 'latest');

        if ($sort === 'due_date') {
            $query->orderByRaw('COALESCE(expected_delivery_date, order_date, created_at) ASC');
        } else {
            $query->orderByDesc('created_at');
        }

        $openOrders = $query
            ->paginate($request->get('per_page', 15))
            ->appends($request->query());

        return view('Purchasing.purchase_orders.open_orders', compact('openOrders', 'sort'));
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

    /**
     * Download purchase order as PDF
     */
    public function downloadPDF(PurchaseOrder $purchaseOrder)
    {
        try {
            // Load relationships
            $purchaseOrder->load([
                'supplier',
                'purchaseOrderItems.item.unit',
                'purchaseOrderItems.item.category',
                'createdBy',
                'approvedBy',
                'sourcePurchaseRequests'
            ]);

            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('Purchasing.purchase_orders.pdf', compact('purchaseOrder'));
            
            // Set PDF options for better quality
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultPaperSize' => 'a4',
                'defaultPaperOrientation' => 'portrait'
            ]);

            // Generate filename
            $filename = 'PO-' . $purchaseOrder->po_number . '-' . date('Y-m-d-H-i-s') . '.pdf';

            // Download PDF
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('PDF generation failed for PO: ' . $purchaseOrder->id, [
                'error' => $e->getMessage(),
                'po_number' => $purchaseOrder->po_number
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    public function submitPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['sent', 'confirmed'])) {
            return redirect()->back()->with('error', 'Only purchase orders that are sent or confirmed can be processed.');
        }
        
        // Since POs are now created with 'sent' status directly, this method
        // primarily handles acknowledgment workflow
        if ($purchaseOrder->status === 'sent') {
            $purchaseOrder->update([
                'status' => 'confirmed',
                'acknowledged_by' => Auth::id(),
                'acknowledged_at' => Carbon::now(),
            ]);
            $message = "Purchase Order {$purchaseOrder->po_number} acknowledged successfully!";
        } else {
            $message = "Purchase Order {$purchaseOrder->po_number} is already confirmed.";
        }
        
        return redirect()->route('purchasing.po.open')
            ->with('success', $message);
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
        // Purchase orders can no longer be edited after creation
        // Redirect to create page with message that editing is not supported
        return redirect()->route('purchasing.po.create')
            ->with('info', 'Purchase orders cannot be edited after creation. Please create a new order if changes are needed.');
    }

    public function destroyPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        // Purchase orders can no longer be deleted after creation for data integrity
        // Only allow deletion if the PO has no linked PRs and is in 'sent' status
        if ($purchaseOrder->sourcePurchaseRequests->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete purchase order that has linked purchase requests. Contact administrator if deletion is necessary.');
        }
        
        if ($purchaseOrder->status !== 'sent') {
            return redirect()->back()->with('error', 'Only sent purchase orders without linked requests can be deleted. Contact administrator for other cases.');
        }
        
        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();
        return redirect()->route('purchasing.po.open')
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
     * Get purchase request details for purchasing users with partial consolidation information
     */
    public function getPurchaseRequestDetails(PurchaseRequest $purchaseRequest)
    {
        try {
            if ($purchaseRequest->status !== 'approved') {
                return response()->json([
                    'message' => 'Purchase request is not approved or not available for viewing.'
                ], 403);
            }

            // Load basic relationships
            $purchaseRequest->load([
                'requestedBy:id,name,email',
                'approvedBy:id,name,email',
                'purchaseRequestItems' => function($query) {
                    $query->with([
                        'item:id,name,item_code,unit_id',
                        'item.unit:id,name,symbol',
                        'item.category:id,name'
                    ]);
                }
            ]);

            // Get quantity ordered so far for this specific PR
            $prOrderedQuantities = $this->getOrderedQuantitiesForPRs([$purchaseRequest->id]);

            // Enhance purchase request items with partial consolidation data
            $enhancedItems = $purchaseRequest->purchaseRequestItems->map(function ($item) use ($purchaseRequest, $prOrderedQuantities) {
                $itemId = $item->item_id;
                $orderedSoFar = $prOrderedQuantities[$purchaseRequest->id][$itemId] ?? 0;
                $remainingQuantity = $item->quantity_requested - $orderedSoFar;
                
                // Get available suppliers for this item
                $availableSuppliers = SupplierItem::where('item_id', $itemId)
                    ->where('is_active', true)
                    ->with('supplier:id,name,supplier_code')
                    ->get()
                    ->map(function ($supplierItem) {
                        return [
                            'supplier_id' => $supplierItem->supplier->id,
                            'supplier_name' => $supplierItem->supplier->name,
                            'supplier_code' => $supplierItem->supplier->supplier_code,
                            'unit_price' => (float) $supplierItem->unit_price,
                            'is_preferred' => (bool) $supplierItem->is_preferred
                        ];
                    })
                    ->sortByDesc('is_preferred')
                    ->values();

                // Get purchase order history for this item
                $poHistory = PurchaseRequestPurchaseOrderLink::where('purchase_request_id', $purchaseRequest->id)
                    ->with(['purchaseOrder' => function($query) use ($itemId) {
                        $query->with(['purchaseOrderItems' => function($q) use ($itemId) {
                            $q->where('item_id', $itemId);
                        }]);
                    }])
                    ->get()
                    ->map(function ($link) use ($itemId) {
                        $poItem = $link->purchaseOrder->purchaseOrderItems->first();
                        return [
                            'po_id' => $link->purchaseOrder->id,
                            'po_number' => $link->purchaseOrder->po_number,
                            'supplier_id' => $link->purchaseOrder->supplier_id,
                            'quantity_ordered' => $poItem ? (float) $poItem->quantity_ordered : 0,
                            'unit_price' => $poItem ? (float) $poItem->unit_price : 0,
                            'status' => $link->purchaseOrder->status,
                            'order_date' => $link->purchaseOrder->order_date,
                            'supplier_name' => $link->purchaseOrder->supplier->name ?? 'Unknown'
                        ];
                    });

                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'category_name' => $item->item->category->name ?? 'No Category',
                    'unit_symbol' => $item->item->unit->symbol ?? '',
                    'quantity_requested' => (float) $item->quantity_requested,
                    'unit_price_estimate' => (float) $item->unit_price_estimate,
                    'total_estimated_cost' => (float) $item->total_estimated_cost,
                    
                    // Partial Consolidation Data
                    'quantity_ordered_so_far' => (float) $orderedSoFar,
                    'remaining_quantity' => (float) $remainingQuantity,
                    'completion_percentage' => $item->quantity_requested > 0 ? 
                        round(($orderedSoFar / $item->quantity_requested) * 100, 1) : 0,
                    
                    // Supplier Information
                    'available_suppliers' => $availableSuppliers,
                    
                    // Purchase Order History
                    'po_history' => $poHistory,
                    
                    // Status
                    'is_fully_ordered' => $remainingQuantity <= 0,
                    'has_partial_orders' => $orderedSoFar > 0,
                    'can_order_more' => $remainingQuantity > 0
                ];
            });

            // Calculate PR-level summary
            $totalRequested = $enhancedItems->sum('quantity_requested');
            $totalOrdered = $enhancedItems->sum('quantity_ordered_so_far');
            $totalRemaining = $enhancedItems->sum('remaining_quantity');
            $fullyOrderedItems = $enhancedItems->where('is_fully_ordered', true)->count();
            $partiallyOrderedItems = $enhancedItems->where('has_partial_orders', true)->where('can_order_more', true)->count();
            $notOrderedItems = $enhancedItems->where('has_partial_orders', false)->count();

            $summary = [
                'total_items' => $enhancedItems->count(),
                'total_requested_quantity' => (float) $totalRequested,
                'total_ordered_quantity' => (float) $totalOrdered,
                'total_remaining_quantity' => (float) $totalRemaining,
                'overall_completion_percentage' => $totalRequested > 0 ? 
                    round(($totalOrdered / $totalRequested) * 100, 1) : 0,
                'items_status' => [
                    'fully_ordered' => $fullyOrderedItems,
                    'partially_ordered' => $partiallyOrderedItems,
                    'not_ordered' => $notOrderedItems
                ]
            ];

            $itemsPayload = $enhancedItems->values();

            $purchaseRequestPayload = [
                'id' => $purchaseRequest->id,
                'pr_number' => $purchaseRequest->pr_number,
                'request_date' => optional($purchaseRequest->request_date)->format('Y-m-d'),
                'department' => $purchaseRequest->department,
                'priority' => $purchaseRequest->priority,
                'status' => $purchaseRequest->status,
                'notes' => $purchaseRequest->notes,
                'reject_reason' => $purchaseRequest->reject_reason,
                'total_estimated_cost' => (float) ($purchaseRequest->total_estimated_cost ?? 0),
                'created_at' => optional($purchaseRequest->created_at)->toISOString(),
                'approved_at' => optional($purchaseRequest->approved_at)->toISOString(),
                'requestedBy' => [
                    'id' => $purchaseRequest->requestedBy->id ?? null,
                    'name' => $purchaseRequest->requestedBy->name ?? null,
                    'email' => $purchaseRequest->requestedBy->email ?? null,
                ],
                'approvedBy' => [
                    'id' => $purchaseRequest->approvedBy->id ?? null,
                    'name' => $purchaseRequest->approvedBy->name ?? null,
                ],
                'purchaseRequestItems' => $itemsPayload,
                'items' => $itemsPayload,
                'consolidation_summary' => $summary,
                'summary' => $summary,
            ];

            return response()->json([
                'purchaseRequest' => $purchaseRequestPayload
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching purchase request details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch purchase request details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get all items for selected purchase requests (before supplier filtering)
     * This shows what items will be available for supplier selection
     */
    public function getItemsForPRs(Request $request)
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
                ->with(['purchaseRequestItems.item.unit', 'purchaseRequestItems.item.category'])
                ->get();

            if ($purchaseRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No valid approved purchase requests found.',
                    'items' => []
                ]);
            }

            // Get quantity_ordered_so_far for each PR-item combination
            $prOrderedQuantities = $this->getOrderedQuantitiesForPRs($prIds);

            // Process all items from selected PRs
            $allItems = [];
            $sourcePRsMap = [];

            foreach ($purchaseRequests as $pr) {
                foreach ($pr->purchaseRequestItems as $prItem) {
                    $itemId = $prItem->item_id;
                    
                    if (!isset($allItems[$itemId])) {
                        $allItems[$itemId] = [
                            'item_id' => (int)$itemId,
                            'item_name' => $prItem->item->name,
                            'item_code' => $prItem->item->item_code,
                            'unit_symbol' => $prItem->item->unit->symbol ?? '',
                            'category' => $prItem->item->category->name ?? 'No Category',
                            'total_requested_quantity' => 0,
                            'quantity_ordered_so_far' => 0,
                            'source_prs' => []
                        ];
                    }

                    $allItems[$itemId]['total_requested_quantity'] += $prItem->quantity_requested;
                    $allItems[$itemId]['quantity_ordered_so_far'] += $prOrderedQuantities[$pr->id][$itemId] ?? 0;
                    
                    $allItems[$itemId]['source_prs'][] = [
                        'pr_id' => $pr->id,
                        'pr_number' => $pr->pr_number,
                        'quantity' => $prItem->quantity_requested,
                        'ordered_so_far' => $prOrderedQuantities[$pr->id][$itemId] ?? 0,
                        'remaining_quantity' => $prItem->quantity_requested - ($prOrderedQuantities[$pr->id][$itemId] ?? 0)
                    ];
                }
            }

            // Calculate remaining quantities and filter out fully ordered items
            $processedItems = [];
            foreach ($allItems as $item) {
                $item['remaining_quantity'] = $item['total_requested_quantity'] - $item['quantity_ordered_so_far'];
                
                if ($item['remaining_quantity'] > 0) {
                    $processedItems[] = $item;
                }
            }

            return response()->json([
                'items' => array_values($processedItems),
                'summary' => [
                    'total_items' => count($processedItems),
                    'total_requested_items' => count($allItems),
                    'fully_ordered_items' => count($allItems) - count($processedItems)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching items for PRs: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch items for selected purchase requests.',
                'error' => $e->getMessage(),
                'items' => []
            ], 500);
        }
    }

    /**
     * API endpoint to get supplier items for selected purchase requests with Partial Consolidation
     * Implements the logic for converting Purchase Requests (PR) to Purchase Orders (PO)
     * 
     * Features:
     * - Filters items to ONLY include those that exist in supplier_items table for the selected supplier_id
     * - Calculates quantity_ordered_so_far for each item by summing purchase_order_items linked to PR
     * - Returns remaining_quantity = quantity_requested - quantity_ordered_so_far
     * - Excludes items where remaining_quantity <= 0
     * - Returns JSON matching the structure expected by JavaScript renderSupplierItems function
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

            // Step 1: Get all item IDs from the selected PRs
            $prItemIds = collect();
            foreach ($purchaseRequests as $pr) {
                foreach ($pr->purchaseRequestItems as $item) {
                    $prItemIds->push($item->item_id);
                }
            }
            $prItemIds = $prItemIds->unique()->values();

            // Step 2: CRITICAL FILTER - Only include items that exist in supplier_items for this supplier
            $supplierItems = SupplierItem::where('supplier_id', $supplier->id)
                ->whereIn('item_id', $prItemIds)
                ->with(['item.unit', 'item.category'])
                ->get();

            if ($supplierItems->isEmpty()) {
                return response()->json([
                    'supplier' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'supplier_code' => $supplier->supplier_code,
                        'payment_terms' => $supplier->payment_terms
                    ],
                    'items' => [
                        'available' => [],
                        'unavailable' => []
                    ],
                    'summary' => [
                        'total_items_requested' => 0,
                        'items_available_from_supplier' => 0,
                        'items_not_available' => 0,
                        'total_estimated_cost' => 0.00
                    ]
                ]);
            }

            // Step 3: Get quantity_ordered_so_far for each PR-item combination
            $prOrderedQuantities = $this->getOrderedQuantitiesForPRs($prIds);

            // Step 4: Process items with partial consolidation logic
            $processedItems = $supplierItems->map(function ($supplierItem) use ($purchaseRequests, $prOrderedQuantities) {
                $totalRequestedQuantity = 0;
                $sourcePRs = [];
                $totalOrderedSoFar = 0;

                foreach ($purchaseRequests as $pr) {
                    foreach ($pr->purchaseRequestItems as $prItem) {
                        if ($prItem->item_id === $supplierItem->item_id) {
                            $totalRequestedQuantity += $prItem->quantity_requested;
                            $totalOrderedSoFar += $prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0;
                            
                            $sourcePRs[] = [
                                'pr_id' => $pr->id,
                                'pr_number' => $pr->pr_number,
                                'quantity' => $prItem->quantity_requested,
                                'ordered_so_far' => $prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0,
                                'remaining_quantity' => $prItem->quantity_requested - ($prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0)
                            ];
                        }
                    }
                }

                $remainingQuantity = $totalRequestedQuantity - $totalOrderedSoFar;

                return [
                    'item_id' => (int)$supplierItem->item_id,
                    'item_name' => $supplierItem->item->name,
                    'item_code' => $supplierItem->item->item_code,
                    'unit_price' => (float)$supplierItem->unit_price,
                    'quantity_ordered_so_far' => (float)$totalOrderedSoFar,
                    'remaining_quantity' => (float)$remainingQuantity,
                    'source_prs' => collect($sourcePRs)->pluck('pr_number')->toArray()
                ];
            })->filter(function($item) {
                // Step 5: CRITICAL - Exclude items where remaining_quantity <= 0
                return $item['remaining_quantity'] > 0 && $item['unit_price'] > 0;
            })
            ->sortByDesc('is_preferred')
            ->values();

            $unavailableItems = $supplierItems->filter(function($supplierItem) use ($purchaseRequests, $prOrderedQuantities) {
                $totalRequestedQuantity = 0;
                $totalOrderedSoFar = 0;

                foreach ($purchaseRequests as $pr) {
                    foreach ($pr->purchaseRequestItems as $prItem) {
                        if ($prItem->item_id === $supplierItem->item_id) {
                            $totalRequestedQuantity += $prItem->quantity_requested;
                            $totalOrderedSoFar += $prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0;
                        }
                    }
                }

                $remainingQuantity = $totalRequestedQuantity - $totalOrderedSoFar;
                return $remainingQuantity <= 0 || $supplierItem->unit_price <= 0;
            })->map(function ($supplierItem) use ($purchaseRequests, $prOrderedQuantities) {
                $totalRequestedQuantity = 0;
                $totalOrderedSoFar = 0;
                $sourcePRs = [];

                foreach ($purchaseRequests as $pr) {
                    foreach ($pr->purchaseRequestItems as $prItem) {
                        if ($prItem->item_id === $supplierItem->item_id) {
                            $totalRequestedQuantity += $prItem->quantity_requested;
                            $totalOrderedSoFar += $prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0;
                            
                            $sourcePRs[] = [
                                'pr_id' => $pr->id,
                                'pr_number' => $pr->pr_number,
                                'quantity' => $prItem->quantity_requested,
                                'ordered_so_far' => $prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0,
                                'remaining_quantity' => $prItem->quantity_requested - ($prOrderedQuantities[$pr->id][$supplierItem->item_id] ?? 0)
                            ];
                        }
                    }
                }

                $remainingQuantity = $totalRequestedQuantity - $totalOrderedSoFar;

                return [
                    'item_id' => (int)$supplierItem->item_id,
                    'item_name' => $supplierItem->item->name,
                    'item_code' => $supplierItem->item->item_code,
                    'unit_price' => (float)$supplierItem->unit_price,
                    'quantity_ordered_so_far' => (float)$totalOrderedSoFar,
                    'remaining_quantity' => (float)$remainingQuantity,
                    'source_prs' => collect($sourcePRs)->pluck('pr_number')->toArray()
                ];
            })->values();

            return response()->json([
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'supplier_code' => $supplier->supplier_code,
                    'payment_terms' => $supplier->payment_terms
                ],
                'items' => [
                    'available' => $processedItems,
                    'unavailable' => $unavailableItems
                ],
                'summary' => [
                    'total_items_requested' => (int)$prItemIds->count(),
                    'items_available_from_supplier' => (int)$processedItems->count(),
                    'items_not_available' => (int)$unavailableItems->count(),
                    'total_estimated_cost' => (float)$processedItems->sum('estimated_total')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier items for PRs (Partial Consolidation): ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch supplier items for selected purchase requests.',
                'error' => $e->getMessage(),
                'items' => []
            ], 500);
        }
    }

    /**
     * Helper method to calculate quantity_ordered_so_far for each item in PRs
     * 
     * @param array $prIds Array of purchase request IDs
     * @return array Associative array [pr_id => [item_id => quantity_ordered_so_far]]
     */
    private function getOrderedQuantitiesForPRs(array $prIds): array
    {
        // Get all PO links for these PRs
        $poLinks = PurchaseRequestPurchaseOrderLink::whereIn('purchase_request_id', $prIds)
            ->with(['purchaseOrder.purchaseOrderItems'])
            ->get();

        $orderedQuantities = [];

        foreach ($poLinks as $link) {
            $prId = $link->purchase_request_id;
            $poId = $link->purchase_order_id;

            // Initialize PR if not exists
            if (!isset($orderedQuantities[$prId])) {
                $orderedQuantities[$prId] = [];
            }

            // Sum quantities for each item in this PO
            foreach ($link->purchaseOrder->purchaseOrderItems as $poItem) {
                $itemId = $poItem->item_id;
                if (!isset($orderedQuantities[$prId][$itemId])) {
                    $orderedQuantities[$prId][$itemId] = 0;
                }
                $orderedQuantities[$prId][$itemId] += $poItem->quantity_ordered;
            }
        }

        return $orderedQuantities;
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

    public function bulkConfigure()
    {
        // Get the same data as createPurchaseOrder for PR selection
        $prIdsWithRemainingItems = $this->getPRIdsWithRemainingItems();
        
        $baseApprovedQuery = PurchaseRequest::where('status', 'approved')
            ->whereIn('id', $prIdsWithRemainingItems);

        $approvedRequests = (clone $baseApprovedQuery)
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->paginate(15);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $departments = (clone $baseApprovedQuery)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        return view('Purchasing.purchase_orders.bulk_configure', compact('approvedRequests', 'suppliers', 'departments'));
    }

    public function bulkCreatePurchaseOrders(Request $request)
    {
        $request->validate([
            'selected_pr_ids' => 'required',
            'bulk_config' => 'required|array',
            'bulk_config.*.supplier_id' => 'required|exists:suppliers,id',
            'bulk_config.*.expected_delivery_date' => 'required|date|after_or_equal:today',
            'bulk_config.*.payment_terms' => 'nullable|integer|min:0',
            'bulk_config.*.notes' => 'nullable|string',
        ]);

        $selectedPRIds = array_map('intval', array_filter(explode(',', $request->selected_pr_ids)));
        
        if (empty($selectedPRIds)) {
            return redirect()->back()->with('error', 'Please select at least one purchase request.');
        }

        $purchaseRequests = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->where('status', 'approved')
            ->with('purchaseRequestItems')
            ->get();

        if ($purchaseRequests->count() !== count($selectedPRIds)) {
            return redirect()->back()->with('error', 'Some selected purchase requests are invalid or not approved.');
        }

        $createdPOs = [];
        $errors = [];

        // Group PR items by supplier based on bulk configuration
        foreach ($request->bulk_config as $config) {
            $supplierId = $config['supplier_id'];
            
            // Get items that can be supplied by this supplier
            $supplierItems = SupplierItem::where('supplier_id', $supplierId)
                ->pluck('item_id')
                ->toArray();

            // Filter PR items that this supplier can provide
            $relevantItems = [];
            foreach ($purchaseRequests as $pr) {
                foreach ($pr->purchaseRequestItems as $prItem) {
                    if (in_array($prItem->item_id, $supplierItems)) {
                        $relevantItems[] = [
                            'pr' => $pr,
                            'pr_item' => $prItem,
                            'item_id' => $prItem->item_id,
                            'quantity' => $prItem->quantity_requested,
                        ];
                    }
                }
            }

            if (empty($relevantItems)) {
                $supplier = Supplier::find($supplierId);
                $errors[] = "Supplier {$supplier->name}: No items available from selected purchase requests.";
                continue;
            }

            try {
                // Create PO for this supplier
                $poNumber = $this->generatePONumber();
                $totalAmount = 0;
                $poItems = [];

                // Get supplier item prices
                $supplierItemPrices = SupplierItem::where('supplier_id', $supplierId)
                    ->whereIn('item_id', array_column($relevantItems, 'item_id'))
                    ->get()
                    ->keyBy('item_id');

                foreach ($relevantItems as $itemData) {
                    $itemId = $itemData['item_id'];
                    $quantity = (float) $itemData['quantity'];
                    
                    $supplierItem = $supplierItemPrices->get($itemId);
                    if (!$supplierItem || $supplierItem->unit_price <= 0) {
                        continue;
                    }

                    $unitPrice = (float) $supplierItem->unit_price;
                    $totalPrice = $quantity * $unitPrice;
                    $totalAmount += $totalPrice;

                    $poItems[] = [
                        'item_id' => $itemId,
                        'quantity_ordered' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ];
                }

                if (empty($poItems)) {
                    $supplier = Supplier::find($supplierId);
                    $errors[] = "Supplier {$supplier->name}: No valid items with prices found.";
                    continue;
                }

                $purchaseOrder = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplierId,
                    'order_date' => Carbon::now()->toDateString(),
                    'expected_delivery_date' => $config['expected_delivery_date'],
                    'status' => 'sent',
                    'total_amount' => $totalAmount,
                    'grand_total' => $totalAmount,
                    'payment_terms' => $config['payment_terms'] ?? 30,
                    'notes' => $config['notes'],
                    'created_by' => Auth::id(),
                ]);

                foreach ($poItems as $item) {
                    PurchaseOrderItem::create(array_merge($item, [
                        'purchase_order_id' => $purchaseOrder->id,
                        'created_at' => Carbon::now(),
                    ]));
                }

                // Link PRs to this PO
                foreach ($selectedPRIds as $prId) {
                    PurchaseRequestPurchaseOrderLink::updateOrCreate(
                        [
                            'purchase_request_id' => $prId,
                            'purchase_order_id' => $purchaseOrder->id,
                        ],
                        [
                            'consolidated_by' => Auth::id(),
                            'consolidated_at' => Carbon::now(),
                            'created_at' => Carbon::now(),
                        ]
                    );
                }

                $supplier = Supplier::find($supplierId);
                $createdPOs[] = "{$poNumber} ({$supplier->name})";

            } catch (\Exception $e) {
                Log::error('Error creating bulk PO: ' . $e->getMessage());
                $supplier = Supplier::find($supplierId);
                $errors[] = "Supplier {$supplier->name}: Failed to create PO - " . $e->getMessage();
            }
        }

        $message = "Bulk PO creation completed. ";
        if (!empty($createdPOs)) {
            $message .= count($createdPOs) . " PO(s) created: " . implode(', ', $createdPOs) . ". ";
        }
        if (!empty($errors)) {
            $message .= "Errors: " . implode('; ', $errors);
        }

        $status = empty($errors) ? 'success' : (empty($createdPOs) ? 'error' : 'warning');

        return redirect()->route('purchasing.po.bulk-configure')
            ->with($status, $message);
    }
}
