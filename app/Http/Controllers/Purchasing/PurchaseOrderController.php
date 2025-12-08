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
use App\Models\Notification;
use App\Models\User;

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
        // Get approved purchase request IDs
        $approvedPRIds = PurchaseRequest::where('status', 'approved')->pluck('id')->toArray();
        
        // Calculate pending items count for each supplier
        $suppliers = Supplier::where('is_active', true)->get()->map(function($supplier) use ($approvedPRIds) {
            if (empty($approvedPRIds)) {
                $supplier->pending_items_count = 0;
                return $supplier;
            }
            
            // Get supplier's items that are in approved PRs
            $supplierItemIds = SupplierItem::where('supplier_id', $supplier->id)
                ->pluck('item_id')
                ->toArray();
                
            if (empty($supplierItemIds)) {
                $supplier->pending_items_count = 0;
                return $supplier;
            }
            
            // Get ordered quantities for these items across all approved PRs
            $orderedQuantities = DB::table('purchase_request_items as pri')
                ->select('pri.item_id', DB::raw('COALESCE(SUM(poi.quantity_ordered), 0) as ordered_quantity'))
                ->leftJoin('purchase_request_purchase_order_link as link', 'link.purchase_request_id', '=', 'pri.purchase_request_id')
                ->leftJoin('purchase_order_items as poi', function ($join) {
                    $join->on('poi.purchase_order_id', '=', 'link.purchase_order_id')
                        ->on('poi.item_id', '=', 'pri.item_id');
                })
                ->whereIn('pri.purchase_request_id', $approvedPRIds)
                ->whereIn('pri.item_id', $supplierItemIds)
                ->groupBy('pri.item_id')
                ->pluck('ordered_quantity', 'item_id');
            
            // Count items with remaining quantities
            $pendingCount = PurchaseRequestItem::whereIn('purchase_request_id', $approvedPRIds)
                ->whereIn('item_id', $supplierItemIds)
                ->get()
                ->filter(function ($item) use ($orderedQuantities) {
                    $ordered = $orderedQuantities[$item->item_id] ?? 0;
                    return $ordered < (float) $item->quantity_requested;
                })
                ->count();
                
            $supplier->pending_items_count = $pendingCount;
            return $supplier;
        })->sortByDesc(function($supplier) {
            return [$supplier->pending_items_count, $supplier->name];
        })->values();

        return view('Purchasing.purchase_orders.create_po', compact('suppliers'));
    }

    /**
     * Get supplier data for order builder
     * Returns catalog items, approved PR items, and supplier details
     */
    public function getSupplierData(Request $request, $supplierId)
    {
        try {
            $supplier = Supplier::where('is_active', true)->findOrFail($supplierId);

            // Get catalog items - all items this supplier sells
            $catalogItems = SupplierItem::where('supplier_id', $supplierId)
                ->with(['item.unit', 'item.category'])
                ->where('unit_price', '>', 0)
                ->get()
                ->map(function ($supplierItem) {
                    return [
                        'item_id' => $supplierItem->item_id,
                        'item_name' => $supplierItem->item->name,
                        'item_code' => $supplierItem->item->item_code,
                        'category' => $supplierItem->item->category->name ?? 'No Category',
                        'unit_symbol' => $supplierItem->item->unit->symbol ?? '',
                        'unit_price' => (float) $supplierItem->unit_price,
                        'is_preferred' => (bool) $supplierItem->is_preferred,
                        'minimum_order_quantity' => (float) $supplierItem->minimum_order_quantity,
                        'lead_time_days' => $supplierItem->lead_time_days,
                        'source' => 'catalog'
                    ];
                })
                ->sortByDesc('is_preferred')
                ->values();

            // Get approved PR items that this supplier can fulfill, grouped by Item ID
            $approvedPRIds = PurchaseRequest::where('status', 'approved')->pluck('id')->toArray();
            
            if (empty($approvedPRIds)) {
                $prItems = [];
            } else {
                // Get ordered quantities for these PRs
                $orderedQuantities = $this->getOrderedQuantitiesForPRs($approvedPRIds);
                
                // Get PR items for items that this supplier sells
                $supplierItemIds = $catalogItems->pluck('item_id')->toArray();
                
                // Create a price lookup map for the supplier
                $priceMap = SupplierItem::where('supplier_id', $supplierId)->pluck('unit_price', 'item_id');
                
                $prItems = PurchaseRequestItem::with([
                        'item.unit',
                        'purchaseRequest:id,pr_number,department,priority,request_date'
                    ])
                    ->whereIn('purchase_request_id', $approvedPRIds)
                    ->whereIn('item_id', $supplierItemIds)
                    ->get()
                    ->groupBy('item_id')
                    ->map(function ($items, $itemId) use ($orderedQuantities) {
                        $firstItem = $items->first();
                        $totalRequested = $items->sum('quantity_requested');
                        
                        // Calculate total ordered so far for this item across all PRs
                        $totalOrdered = 0;
                        foreach ($items as $item) {
                            $prId = $item->purchase_request_id;
                            $totalOrdered += $orderedQuantities[$prId][$itemId] ?? 0;
                        }
                        
                        $remainingQuantity = $totalRequested - $totalOrdered;
                        
                        if ($remainingQuantity <= 0) {
                            return null; // Skip fully ordered items
                        }
                        
                        // Get source PRs with remaining quantities
                        $sourcePRs = $items->map(function ($item) use ($orderedQuantities) {
                            $prId = $item->purchase_request_id;
                            $ordered = $orderedQuantities[$prId][$item->item_id] ?? 0;
                            $remaining = $item->quantity_requested - $ordered;
                            
                            return [
                                'pr_id' => $prId,
                                'pr_number' => $item->purchaseRequest->pr_number,
                                'department' => $item->purchaseRequest->department,
                                'priority' => $item->purchaseRequest->priority,
                                'requested_quantity' => (float) $item->quantity_requested,
                                'ordered_quantity' => (float) $ordered,
                                'remaining_quantity' => (float) $remaining
                            ];
                        })->filter(function ($pr) {
                            return $pr['remaining_quantity'] > 0;
                        })->values();

                        return [
                            'item_id' => (int) $itemId,
                            'item_name' => $firstItem->item->name,
                            'item_code' => $firstItem->item->item_code,
                            'category' => $firstItem->item->category->name ?? 'No Category',
                            'unit_symbol' => $firstItem->item->unit->symbol ?? '',
                            'total_requested_quantity' => (float) $totalRequested,
                            'total_ordered_quantity' => (float) $totalOrdered,
                            'remaining_quantity' => (float) $remainingQuantity,
                            'unit_price' => (float) ($priceMap[$itemId] ?? 0),
                            'source_prs' => $sourcePRs,
                            'source' => 'requests'
                        ];
                    })
                    ->filter()
                    ->values();
            }

            // Get supplier details
            $supplierDetails = [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'supplier_code' => $supplier->supplier_code,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->display_phone,
                'payment_terms' => $supplier->payment_terms,
                'credit_limit' => (float) $supplier->credit_limit,
                'rating' => $supplier->rating,
                'full_address' => $supplier->full_address,
                'notes' => $supplier->notes
            ];

            return response()->json([
                'supplier_details' => $supplierDetails,
                'pending_items' => $prItems,
                'catalog_items' => $catalogItems,
                'summary' => [
                    'pending_items_count' => $prItems->count(),
                    'catalog_items_count' => $catalogItems->count(),
                    'total_potential_items' => $catalogItems->count() + $prItems->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supplier data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch supplier data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storePurchaseOrder(Request $request)
    {
        // Debug logging for order builder PO creation
        Log::info('Order Builder PO Creation Attempt', [
            'all_input' => $request->all(),
            'items' => $request->input('items'),
            'supplier_id' => $request->input('supplier_id'),
            'method' => $request->method(),
            'url' => $request->url(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        try {
            $validatedData = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'expected_delivery_date' => 'required|date|after_or_equal:today',
                'payment_terms' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0.01',
            ]);
            
            Log::info('Order Builder PO Validation Passed', [
                'validated_items' => $validatedData['items'],
                'count' => count($validatedData['items'] ?? [])
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Order Builder PO Validation Failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $poNumber = $this->generatePONumber();
        $totalAmount = 0;
        $poItems = [];

        // Process items from the order builder
        $itemsData = $request->input('items', []);

        Log::info('Processing Order Builder PO Items', [
            'raw_items' => $itemsData,
            'items_count' => count($itemsData)
        ]);

        foreach ($itemsData as $itemData) {
            $itemId = (int) ($itemData['item_id'] ?? 0);
            $quantity = (float) ($itemData['quantity'] ?? 0);
            $unitPrice = (float) ($itemData['unit_price'] ?? 0);

            Log::debug('Processing Item', [
                'item_id' => $itemId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'raw_item_data' => $itemData
            ]);

            if ($itemId <= 0) {
                Log::warning('Invalid item ID in order builder PO creation', ['item_data' => $itemData]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid item ID found in selection.'
                ], 422);
            }
            
            if ($quantity <= 0) {
                Log::warning('Invalid quantity in order builder PO creation', ['item_id' => $itemId, 'quantity' => $quantity]);
                return response()->json([
                    'success' => false,
                    'message' => 'All items must have valid quantity (greater than 0).'
                ], 422);
            }
            
            if ($unitPrice <= 0) {
                Log::warning('Invalid unit price in order builder PO creation', ['item_id' => $itemId, 'unit_price' => $unitPrice]);
                return response()->json([
                    'success' => false,
                    'message' => 'All items must have valid unit price (greater than 0).'
                ], 422);
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
            Log::error('No valid items in order builder PO creation', [
                'items_data' => $itemsData,
                'user_id' => Auth::id(),
                'supplier_id' => $request->supplier_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one item to include in the purchase order.'
            ], 422);
        }

        $status = 'sent';

        try {
            DB::beginTransaction();
            
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'order_date' => Carbon::now()->toDateString(),
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $status,
                'total_amount' => $totalAmount,
                'grand_total' => $totalAmount,
                'payment_terms' => $request->payment_terms ?? 30,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($poItems as $item) {
                PurchaseOrderItem::create(array_merge($item, [
                    'purchase_order_id' => $purchaseOrder->id,
                    'created_at' => Carbon::now(),
                ]));
            }

            DB::commit();

            // NEW: Notify Supervisors/Admins about new PO
            try {
                $managers = User::whereIn('role', ['supervisor', 'admin'])->get();
                foreach ($managers as $manager) {
                    Notification::create([
                        'user_id' => $manager->id,
                        'title' => 'New Purchase Order',
                        'message' => "PO {$poNumber} created for {$purchaseOrder->supplier->name} (₱" . number_format($totalAmount, 2) . ")",
                        'type' => 'purchasing',
                        'priority' => 'normal',
                        'action_url' => route('purchasing.po.open'),
                        'created_at' => Carbon::now()
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send PO creation notification: ' . $e->getMessage());
            }

            Log::info('Order Builder PO Created Successfully', [
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'total_amount' => $totalAmount,
                'items_count' => count($poItems)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Purchase Order {$poNumber} created successfully!",
                'po_number' => $poNumber,
                'redirect' => route('purchasing.po.open')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Order Builder PO Creation Failed', [
                'error' => $e->getMessage(),
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
        }
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



    /**
     * Bulk confirm multiple purchase orders
     */
    public function bulkConfirmOrders(Request $request)
    {
        try {
            $request->validate([
                'order_ids' => 'required|array',
                'order_ids.*' => 'exists:purchase_orders,id'
            ]);

            $orderIds = array_map('intval', $request->order_ids);
            $confirmedCount = 0;
            $errors = [];

            DB::beginTransaction();
            
            try {
                foreach ($orderIds as $orderId) {
                    $purchaseOrder = PurchaseOrder::find($orderId);
                    
                    // Check if PO can be confirmed (must be in 'sent' status)
                    if ($purchaseOrder->status !== 'sent') {
                        $errors[] = "PO {$purchaseOrder->po_number}: Only sent orders can be confirmed.";
                        continue;
                    }

                    // Confirm the order
                    $purchaseOrder->update([
                        'status' => 'confirmed',
                        'acknowledged_by' => Auth::id(),
                        'acknowledged_at' => Carbon::now(),
                    ]);

                    $confirmedCount++;
                }

                DB::commit();

                $message = "Bulk confirmation completed. {$confirmedCount} order(s) confirmed successfully.";
                if (!empty($errors)) {
                    $message .= " Errors: " . implode('; ', $errors);
                }

                $status = empty($errors) ? 'success' : (empty($confirmedCount) ? 'error' : 'warning');

                return response()->json([
                    'success' => $confirmedCount > 0,
                    'message' => $message,
                    'confirmed_count' => $confirmedCount,
                    'error_count' => count($errors),
                    'errors' => $errors
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error in bulk confirm orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm orders: ' . $e->getMessage(),
                'confirmed_count' => 0,
                'error_count' => 0,
                'errors' => []
            ], 500);
        }
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

        return redirect()->route('purchasing.po.create')
            ->with($status, $message);
    }
}
