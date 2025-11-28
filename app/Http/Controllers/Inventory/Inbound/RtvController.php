<?php

namespace App\Http\Controllers\Inventory\Inbound;

use App\Http\Controllers\Controller;
use App\Models\RtvTransaction;
use App\Models\RtvItem;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RtvController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the RTV (Return to Vendor) Dock Log
     */
    public function indexRtv(Request $request)
    {
        try {
            // Build the query for RTV transactions with relationships
            $query = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->orderBy('created_at', 'desc');

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('rtv_number', 'ilike', "%{$search}%")
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'ilike', "%{$search}%");
                      })
                      ->orWhere('notes', 'ilike', "%{$search}%")
                      ->orWhereHas('rtvItems', function($rtvi) use ($search) {
                          $rtvi->where('reason', 'ilike', "%{$search}%")
                               ->orWhereHas('item', function($itemq) use ($search) {
                                   $itemq->where('name', 'ilike', "%{$search}%")
                                         ->orWhere('item_code', 'ilike', "%{$search}%");
                               });
                      });
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Apply supplier filter
            if ($request->has('supplier_id') && $request->supplier_id !== 'all') {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Apply date range filter
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('return_date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('return_date', '<=', $request->date_to);
            }

            // Paginate results
            $rtvRecords = $query->paginate($request->get('per_page', 15))->withQueryString();

            // Get summary statistics
            $summary = [
                'total_transactions' => RtvTransaction::count(),
                'pending_transactions' => RtvTransaction::where('status', 'pending')->count(),
                'processed_transactions' => RtvTransaction::where('status', 'processed')->count(),
                'completed_transactions' => RtvTransaction::where('status', 'completed')->count(),
                'total_value' => RtvTransaction::sum('total_value'),
            ];

            // Get suppliers for filter dropdown
            $suppliers = Supplier::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('Inventory.inbound.RTV', compact('rtvRecords', 'summary', 'suppliers'));

        } catch (\Exception $e) {
            \Log::error('Error loading RTV records: ' . $e->getMessage());
            
            return view('Inventory.inbound.RTV', [
                'rtvRecords' => collect(),
                'summary' => [
                    'total_transactions' => 0,
                    'pending_transactions' => 0,
                    'processed_transactions' => 0,
                    'completed_transactions' => 0,
                    'total_value' => 0,
                ],
                'suppliers' => collect()
            ]);
        }
    }

    /**
     * Get items for RTV creation (AJAX)
     */
    public function getItemsForRtv(Request $request)
    {
        try {
            $query = Item::with(['unit', 'currentStockRecord', 'supplierItems.supplier'])
                ->where('is_active', true);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('item_code', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            // Apply category filter
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // Get total count for pagination
            $total = $query->count();

            // Apply pagination
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            $offset = ($page - 1) * $perPage;

            $items = $query->orderBy('name')
                ->offset($offset)
                ->limit($perPage)
                ->get()
                ->map(function($item) {
                    $currentStock = $item->currentStockRecord ? 
                        $item->currentStockRecord->current_quantity : 0;
                    
                    return [
                        'id' => $item->id,
                        'item_code' => $item->item_code ?? 'N/A',
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'unit' => [
                            'id' => $item->unit->id ?? 0,
                            'symbol' => $item->unit->symbol ?? 'pcs'
                        ],
                        'current_stock' => (float) $currentStock,
                        'cost_price' => (float) ($item->cost_price ?? 0),
                        'has_suppliers' => $item->supplierItems->count() > 0,
                        'suppliers' => $item->supplierItems->take(3)->map(function($supplierItem) {
                            return [
                                'id' => $supplierItem->supplier->id,
                                'name' => $supplierItem->supplier->name,
                                'unit_price' => (float) $supplierItem->unit_price
                            ];
                        })->values()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching items for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get suppliers for RTV creation (AJAX)
     */
    public function getSuppliersForRtv(Request $request)
    {
        try {
            $query = Supplier::where('is_active', true)
                ->with(['supplierItems.item']);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('supplier_code', 'ilike', "%{$search}%")
                      ->orWhere('contact_person', 'ilike', "%{$search}%");
                });
            }

            $suppliers = $query->orderBy('name')
                ->limit(15)
                ->get()
                ->map(function($supplier) {
                    return [
                        'id' => $supplier->id,
                        'supplier_code' => $supplier->supplier_code,
                        'name' => $supplier->name,
                        'contact_person' => $supplier->contact_person,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'city' => $supplier->city ?? '',
                        'items_supplied' => $supplier->supplierItems->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'total' => $suppliers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching suppliers for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get purchase orders for supplier (AJAX)
     */
    public function getPurchaseOrdersForRtv(Request $request)
    {
        try {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id'
            ]);

            $purchaseOrders = PurchaseOrder::with(['purchaseOrderItems.item.unit'])
                ->where('supplier_id', $request->supplier_id)
                ->whereIn('status', ['sent', 'confirmed', 'partial', 'completed'])
                ->orderBy('order_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'order_date' => $po->order_date->format('Y-m-d'),
                        'expected_delivery_date' => $po->expected_delivery_date?->format('Y-m-d'),
                        'status' => $po->status,
                        'total_items' => $po->purchaseOrderItems->count(),
                        'total_amount' => (float) $po->grand_total
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $purchaseOrders,
                'total' => $purchaseOrders->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching purchase orders for RTV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase orders',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Store a new RTV transaction
     */
    public function storeRtv(Request $request)
    {
        try {
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'purchase_order_id' => 'nullable|exists:purchase_orders,id',
                'return_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity_returned' => 'required|numeric|min:0.001',
                'items.*.unit_cost' => 'required|numeric|min:0.01',
                'items.*.reason' => 'required|string|max:500'
            ]);

            DB::beginTransaction();

            $user = Auth::user();

            // Generate RTV number
            $rtvCount = RtvTransaction::whereYear('created_at', now()->year)->count();
            $rtvNumber = 'RTV-' . now()->format('Y') . '-' . str_pad($rtvCount + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total value
            $totalValue = 0;
            foreach ($request->items as $item) {
                $totalValue += ($item['quantity_returned'] * $item['unit_cost']);
            }

            // Create RTV transaction
            $rtv = RtvTransaction::create([
                'rtv_number' => $rtvNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'return_date' => $request->return_date,
                'status' => 'pending',
                'total_value' => $totalValue,
                'notes' => $request->notes,
                'created_by' => $user->id
            ]);

            // Create RTV items
            foreach ($request->items as $itemData) {
                RtvItem::create([
                    'rtv_id' => $rtv->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_returned' => $itemData['quantity_returned'],
                    'unit_cost' => $itemData['unit_cost'],
                    'reason' => $itemData['reason']
                ]);

                // Create stock movement for return
                StockMovement::create([
                    'item_id' => $itemData['item_id'],
                    'movement_type' => 'return',
                    'reference_number' => $rtvNumber,
                    'quantity' => -$itemData['quantity_returned'], // Negative for outgoing stock
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => -($itemData['quantity_returned'] * $itemData['unit_cost']),
                    'notes' => "RTV Return - Reason: {$itemData['reason']}",
                    'user_id' => $user->id
                ]);
            }

            // Create notification for relevant users
            $this->createRtvNotifications($rtv, $user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RTV transaction created successfully',
                'rtv_number' => $rtvNumber,
                'rtv_id' => $rtv->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating RTV transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create RTV transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RTV transaction details (AJAX)
     */
    public function getRtvDetails($id)
    {
        try {
            $rtv = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->findOrFail($id);

            $formattedItems = $rtv->rtvItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item->name,
                    'item_code' => $item->item->item_code,
                    'quantity_returned' => (float) $item->quantity_returned,
                    'unit_symbol' => $item->item->unit->symbol ?? 'pcs',
                    'unit_cost' => (float) $item->unit_cost,
                    'total_cost' => (float) $item->total_cost,
                    'reason' => $item->reason,
                    'formatted_quantity' => $item->formatted_quantity,
                    'formatted_unit_cost' => $item->formatted_unit_cost,
                    'formatted_total_cost' => $item->formatted_total_cost
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $rtv->id,
                    'rtv_number' => $rtv->rtv_number,
                    'supplier_name' => $rtv->supplier->name,
                    'supplier_code' => $rtv->supplier->supplier_code,
                    'po_number' => $rtv->purchaseOrder ? $rtv->purchaseOrder->po_number : null,
                    'return_date' => $rtv->return_date->format('Y-m-d'),
                    'return_date_formatted' => $rtv->return_date_formatted,
                    'status' => $rtv->status,
                    'status_badge' => $rtv->status_badge,
                    'total_value' => (float) $rtv->total_value,
                    'formatted_total_value' => $rtv->formatted_total_value,
                    'notes' => $rtv->notes,
                    'created_by' => $rtv->createdBy->name ?? 'Unknown',
                    'created_at' => $rtv->created_at->format('Y-m-d H:i:s'),
                    'items' => $formattedItems,
                    'total_items' => $formattedItems->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching RTV details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RTV details'
            ], 500);
        }
    }

    /**
     * Delete RTV transaction
     */
    public function deleteRtv($id)
    {
        try {
            DB::beginTransaction();

            $rtv = RtvTransaction::findOrFail($id);

            // Only allow deletion of pending RTVs
            if ($rtv->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending RTV transactions can be deleted'
                ], 400);
            }

            // Delete associated RTV items (this will cascade)
            RtvItem::where('rtv_id', $rtv->id)->delete();

            // Delete the RTV transaction
            $rtv->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RTV transaction deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting RTV transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete RTV transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print RTV slip
     */
    public function printRtvSlip($id)
    {
        try {
            $rtv = RtvTransaction::with([
                'supplier',
                'purchaseOrder',
                'rtvItems.item.unit',
                'createdBy:id,name'
            ])->findOrFail($id);

            return view('Inventory.inbound.print_rtv_slip', compact('rtv'));

        } catch (\Exception $e) {
            \Log::error('Error printing RTV slip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate RTV slip'
            ], 500);
        }
    }

    /**
     * Update RTV status
     */
    public function updateRtvStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processed,completed,cancelled'
            ]);

            $rtv = RtvTransaction::findOrFail($id);
            $rtv->update(['status' => $request->status]);

            // Create notification for status change
            $user = Auth::user();
            $this->createRtvStatusChangeNotification($rtv, $user, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'RTV status updated successfully',
                'status_badge' => $rtv->fresh()->status_badge
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating RTV status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update RTV status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create notifications for RTV operations
     */
    private function createRtvNotifications($rtv, $user)
    {
        try {
            // Notify purchasing and supervisor users
            $notificationRecipients = User::whereIn('role', ['purchasing', 'supervisor', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($notificationRecipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'title' => '📦 New RTV Transaction Created',
                    'message' => "RTV {$rtv->rtv_number} created for {$rtv->supplier->name} with total value of {$rtv->formatted_total_value}",
                    'type' => 'rtv_created',
                    'priority' => 'normal',
                    'action_url' => '/inventory/inbound/rtv',
                    'metadata' => [
                        'rtv_id' => $rtv->id,
                        'rtv_number' => $rtv->rtv_number,
                        'supplier_name' => $rtv->supplier->name,
                        'total_value' => $rtv->total_value,
                        'created_by' => $user->name
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error creating RTV notifications: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for RTV status changes
     */
    private function createRtvStatusChangeNotification($rtv, $user, $newStatus)
    {
        try {
            $statusLabels = [
                'pending' => 'Pending Credit',
                'processed' => 'In Process',
                'completed' => 'Credit Received',
                'cancelled' => 'Cancelled'
            ];

            $notificationRecipients = User::whereIn('role', ['purchasing', 'supervisor', 'admin'])
                ->where('is_active', true)
                ->get();

            foreach ($notificationRecipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'title' => '🔄 RTV Status Updated',
                    'message' => "RTV {$rtv->rtv_number} status changed to '{$statusLabels[$newStatus]}' by {$user->name}",
                    'type' => 'rtv_status_change',
                    'priority' => 'normal',
                    'action_url' => '/inventory/inbound/rtv',
                    'metadata' => [
                        'rtv_id' => $rtv->id,
                        'rtv_number' => $rtv->rtv_number,
                        'old_status' => $rtv->getOriginal('status'),
                        'new_status' => $newStatus,
                        'updated_by' => $user->name
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error creating RTV status change notification: ' . $e->getMessage());
        }
    }
}