<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\PurchaseItem;
use App\Models\InventoryTransaction;
use App\Models\AcknowledgeReceipt;
use App\Models\Memo;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:inventory');
    }

    // 🟦 1. Inventory Dashboard
    public function index()
    {
        try {
            // Get recent transactions for dashboard
            $recentTransactions = InventoryTransaction::with('item')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            // Provide $recentTx for blade expecting this variable name
            $recentTx = $recentTransactions->map(function ($t) {
                $t->item_name = $t->item->item_name ?? '';
                return $t;
            });

            return view('Inventory.dashboard', compact('recentTransactions', 'recentTx'));
        } catch (\Exception $e) {
            return view('Inventory.dashboard', ['recentTransactions' => collect(), 'recentTx' => collect()]);
        }
    }

    // Dashboard API endpoints
    public function dashboardStats()
    {
        try {
            $criticalStock = Item::where('item_stock', '<=', DB::raw('min_stock_level'))->count();
            $lowStock = Item::where('item_stock', '<=', DB::raw('reorder_level'))
                           ->where('item_stock', '>', DB::raw('min_stock_level'))
                           ->count();
            $expiringItems = $this->getExpiryAlerts(30)->count();
            $incomingDeliveries = PurchaseOrder::where('po_status', 'ordered')->count();
            
            // Today's stock-in transactions
            $todaysStockIn = InventoryTransaction::whereDate('created_at', Carbon::today())
                ->where('trans_type', 'in')
                ->sum('trans_quantity');

            // Weekly summary (last 7 days)
            $weeklyTotal = InventoryTransaction::where('created_at', '>=', Carbon::now()->subDays(7))
                ->where('trans_type', 'in')
                ->sum('trans_quantity');

            return response()->json([
                'success' => true,
                'critical_stock' => $criticalStock,
                'low_stock' => $lowStock,
                'expiring_items' => $expiringItems,
                'incoming_deliveries' => $incomingDeliveries,
                'todays_stock_in' => $todaysStockIn,
                'weekly_total' => $weeklyTotal
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading dashboard stats'], 500);
        }
    }

    public function expiryAlerts()
    {
        try {
            $expiringItems = $this->getExpiryAlerts(30);
            return response()->json([
                'success' => true,
                'items' => $expiringItems
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading expiry alerts'], 500);
        }
    }

    public function incomingDeliveries()
    {
        try {
            $incomingDeliveries = PurchaseOrder::with('supplier')
                ->where('po_status', 'ordered')
                ->get()
                ->map(function ($po) {
                    $today = Carbon::today();
                    $expectedDate = Carbon::parse($po->expected_delivery_date);
                    
                    return [
                        'po_id' => $po->po_id,
                        'po_ref' => $po->po_ref,
                        'supplier' => $po->supplier->sup_name ?? 'N/A',
                        'expected_delivery_date' => $po->expected_delivery_date,
                        'total_amount' => $po->total_amount,
                        'status' => $expectedDate >= $today ? 'on-time' : 'overdue',
                        'days_overdue' => $expectedDate < $today ? $today->diffInDays($expectedDate) : 0
                    ];
                });

            return response()->json([
                'success' => true,
                'deliveries' => $incomingDeliveries
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading incoming deliveries'], 500);
        }
    }

    public function weeklyStockInSummary()
    {
        try {
            $summary = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $total = InventoryTransaction::whereDate('created_at', $date)
                    ->where('trans_type', 'in')
                    ->sum('trans_quantity');
                
                $summary[] = [
                    'date' => $date->format('Y-m-d'),
                    'total' => $total
                ];
            }

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading weekly summary'], 500);
        }
    }

    // 🟦 2. Item List Page
    public function itemsList()
    {
        $categories = Category::orderBy('cat_name')->get();
        return view('Inventory.Items.item_list', compact('categories'));
    }

    public function itemsListData(Request $request)
    {
        try {
            $query = Item::with('category');
            
            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('item_code', 'like', "%{$search}%")
                      ->orWhere('item_name', 'like', "%{$search}%")
                      ->orWhere('item_description', 'like', "%{$search}%");
                });
            }
            
            // Category filter
            if ($request->has('category') && !empty($request->category)) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('cat_name', $request->category);
                });
            }
            
            // Status filter
            if ($request->has('status') && !empty($request->status)) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }
            
            // Get items with pagination
            $items = $query->orderBy('item_name')
                ->paginate(20);
            
            // Transform data for frontend
            $transformedItems = $items->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_description,
                    'item_unit' => $item->item_unit,
                    'item_stock' => $item->item_stock,
                    'reorder_level' => $item->reorder_level,
                    'min_stock_level' => $item->min_stock_level,
                    'max_stock_level' => $item->max_stock_level,
                    'item_expire_date' => $item->item_expire_date,
                    'is_active' => $item->is_active,
                    'is_custom' => $item->is_custom,
                    'cat_name' => $item->category->cat_name ?? null,
                    'cat_id' => $item->cat_id
                ];
            });
            
            return response()->json([
                'success' => true,
                'items' => $transformedItems,
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading items list: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading items'], 500);
        }
    }

    public function itemShow($id)
    {
        try {
            $item = Item::with('category')->findOrFail($id);
            return response()->json([
                'success' => true,
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }
    }

    // 🟦 3. Add Item Page
    public function storeItem(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'item_unit' => 'required|string',
            'cat_id' => 'required|exists:categories,cat_id',
            'item_stock' => 'required|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'reorder_level' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'is_custom' => 'boolean'
        ]);

        try {
            DB::beginTransaction();
            
            $item = Item::create([
                'item_code' => $request->item_code,
                'item_name' => $request->item_name,
                'item_description' => $request->item_description,
                'item_unit' => $request->item_unit,
                'cat_id' => $request->cat_id,
                'item_stock' => $request->item_stock,
                'item_expire_date' => $request->item_expire_date,
                'reorder_level' => $request->reorder_level,
                'min_stock_level' => $request->min_stock_level,
                'max_stock_level' => $request->max_stock_level,
                'is_custom' => $request->is_custom ?? false,
                'is_active' => true
            ]);

            // Create initial inventory transaction if stock > 0
            if ($request->item_stock > 0) {
                InventoryTransaction::create([
                    'trans_ref' => 'INIT-' . time(),
                    'trans_type' => 'in',
                    'trans_quantity' => $request->item_stock,
                    'trans_date' => Carbon::now(),
                    'trans_remarks' => 'Initial stock',
                    'item_id' => $item->item_id,
                    'trans_by' => Auth::id()
                ]);
            }

            // Send notification to Admin
            $this->createNotification('admin', 'New item added to inventory', 
                "New item '{$item->item_name}' has been added to inventory.", 
                route('admin.item-management'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error adding item'], 500);
        }
    }

    // 🟦 4. Edit Item Page
    public function itemUpdate(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'item_description' => 'nullable|string',
            'item_unit' => 'required|string',
            'cat_id' => 'required|exists:categories,cat_id',
            'item_expire_date' => 'nullable|date',
            'reorder_level' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'is_custom' => 'boolean'
        ]);

        try {
            $item = Item::findOrFail($id);
            
            $item->update([
                'item_name' => $request->item_name,
                'item_description' => $request->item_description,
                'item_unit' => $request->item_unit,
                'cat_id' => $request->cat_id,
                'item_expire_date' => $request->item_expire_date,
                'reorder_level' => $request->reorder_level,
                'min_stock_level' => $request->min_stock_level,
                'max_stock_level' => $request->max_stock_level,
                'is_custom' => $request->is_custom ?? false
            ]);

            // Send notification to Admin
            $this->createNotification('admin', 'Item updated', 
                "Item '{$item->item_name}' has been updated.", 
                route('inventory.items.show', $item->item_id));

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating item'], 500);
        }
    }

    // 🟦 5. Stock Adjustment Page
    public function adjustmentsIndex()
    {
        return view('Inventory.adjustments');
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'trans_type' => 'required|in:in,out,adjustment',
            'trans_quantity' => 'required|numeric|min:0.01',
            'trans_remarks' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            $item = Item::findOrFail($request->item_id);
            $quantity = $request->trans_quantity;
            
            // Validate negative stock for OUT transactions
            if ($request->trans_type === 'out' && $item->item_stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for this transaction'
                ], 422);
            }

            // Calculate new stock level
            $stockChange = $request->trans_type === 'out' ? -$quantity : $quantity;
            $newStock = $item->item_stock + $stockChange;

            // Update item stock
            $item->update(['item_stock' => $newStock]);

            // Create inventory transaction
            $transaction = InventoryTransaction::create([
                'trans_ref' => $this->generateTransactionReference(),
                'trans_type' => $request->trans_type,
                'trans_quantity' => $quantity,
                'trans_date' => Carbon::now()->toDateString(), // Use DATE for trans_date
                'trans_remarks' => $request->trans_remarks,
                'item_id' => $item->item_id,
                'trans_by' => Auth::id()
            ]);

            // Send notification to Admin
            $this->createNotification('admin', 'Stock manually adjusted', 
                "Stock for '{$item->item_name}' has been manually adjusted: {$request->trans_type} {$quantity}", 
                route('inventory.items.show', $item->item_id));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment completed successfully',
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error adjusting stock'], 500);
        }
    }

    // 🟦 6. Incoming Deliveries List
    public function deliveriesIndex()
    {
        return view('Inventory.Deliveries.incoming_deliveries');
    }

    public function deliveriesIncoming()
    {
        try {
            $deliveries = PurchaseOrder::with('supplier')
                ->where('po_status', 'ordered')
                ->get()
                ->map(function ($po) {
                    $today = Carbon::today();
                    $expectedDate = Carbon::parse($po->expected_delivery_date);
                    
                    return [
                        'po_id' => $po->po_id,
                        'po_ref' => $po->po_ref,
                        'supplier' => $po->supplier->sup_name ?? 'N/A',
                        'expected_delivery_date' => $po->expected_delivery_date,
                        'total_amount' => $po->total_amount,
                        'status' => $expectedDate >= $today ? 'on-time' : 'overdue',
                        'days_overdue' => $expectedDate < $today ? $today->diffInDays($expectedDate) : 0
                    ];
                });

            return response()->json([
                'success' => true,
                'deliveries' => $deliveries
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading deliveries'], 500);
        }
    }

    // 🟦 7. Delivery Receive Page (Memo Creation)
    public function deliveryMemo($poId)
    {
        try {
            $purchaseOrder = PurchaseOrder::with('supplier')->findOrFail($poId);
            $memoRef = $this->generateMemoReference();
            
            return view('Inventory.Deliveries.delivery_receive', compact('purchaseOrder', 'memoRef'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.deliveries.incoming')->with('error', 'Purchase order not found');
        }
    }

    public function deliveryMemoStore(Request $request)
    {
        $request->validate([
            'memo_ref' => 'required|string|unique:memos,memo_ref',
            'received_date' => 'required|date',
            'memo_remarks' => 'nullable|string',
            'po_id' => 'required|exists:purchase_orders,po_id'
        ]);

        try {
            DB::beginTransaction();
            
            // Create memo
            $po = PurchaseOrder::findOrFail($request->po_id);

            $memo = Memo::create([
                'memo_ref' => $request->memo_ref,
                'memo_remarks' => $request->memo_remarks,
                'received_date' => $request->received_date,
                'received_by' => Auth::id(),
                // Store the human-readable PO reference string as per spec
                'po_ref' => $po->po_ref
            ]);

            // Update purchase order status
            PurchaseOrder::where('po_id', $request->po_id)->update(['po_status' => 'delivered']);

            // Send notifications
            $this->createNotification('supervisor', 'Delivery for PO completed', 
                "Delivery memo {$request->memo_ref} has been recorded for PO {$po->po_ref}");
            $this->createNotification('purchasing', 'Delivery memo recorded', 
                "Delivery memo {$request->memo_ref} has been recorded. Proceed to stock-in.");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Memo created successfully',
                'memo_ref' => $request->memo_ref
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error creating memo'], 500);
        }
    }

    // 🟦 8. Stock-In Processing Page
    public function stockInIndex()
    {
        return view('Inventory.Stock_In.stock_in_processing');
    }

    public function stockInItems($poId)
    {
        try {
            $purchaseItems = PurchaseItem::where('po_id', $poId)->get();
            
            return response()->json([
                'success' => true,
                'items' => $purchaseItems
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading items'], 500);
        }
    }

    public function stockInProcess(Request $request)
    {
        $request->validate([
            'memo_ref' => 'required|string',
            'po_id' => 'required|exists:purchase_orders,po_id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,item_id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            $memo = Memo::where('memo_ref', $request->memo_ref)->firstOrFail();
            $purchaseOrder = PurchaseOrder::findOrFail($request->po_id);
            
            $totalValue = 0;
            $allFulfilled = true;

            // Process each item
            foreach ($request->items as $itemData) {
                $receivedQty = $itemData['received_quantity'];
                if ($receivedQty > 0) {
                    // Update item stock
                    $this->updateItemStock($itemData['item_id'], $receivedQty, 'in');
                    
                    // Create inventory transaction
                    InventoryTransaction::create([
                        'trans_ref' => $this->generateTransactionReference(),
                        'trans_type' => 'in',
                        'trans_quantity' => $receivedQty,
                        'trans_date' => Carbon::today()->toDateString(), // Use DATE for trans_date
                        'trans_remarks' => "Stock-in from PO {$purchaseOrder->po_ref}",
                        'item_id' => $itemData['item_id'],
                        'po_id' => $purchaseOrder->po_id,
                        'trans_by' => Auth::id()
                    ]);
                    
                    $totalValue += $receivedQty * $itemData['unit_price'];
                }
                
                // Check if item is fully fulfilled
                $purchaseItem = PurchaseItem::where('po_id', $purchaseOrder->po_id)
                    ->where('item_id', $itemData['item_id'])->first();
                
                if ($purchaseItem && $receivedQty < $purchaseItem->pi_quantity) {
                    $allFulfilled = false;
                }
            }

            // Update requisition items status
            $this->updateRequisitionItemsStatus($purchaseOrder->po_id, $allFulfilled);

            // Send notifications
            $this->createNotification('employee', 'Stock-in complete', 
                "Items from PO {$purchaseOrder->po_ref} have been processed and stock updated.", 
                route('staff.ar'));
            $this->createNotification('supervisor', 'Stock-in complete', 
                "All items from PO {$purchaseOrder->po_ref} have been processed.", 
                route('supervisor.inventory-overview'));
            $this->createNotification('purchasing', 'Stock-in complete', 
                "Stock-in processing completed for PO {$purchaseOrder->po_id}.", 
                route('purchasing.purchase.view', $purchaseOrder->po_id));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock-in processing completed successfully',
                'total_value' => $totalValue
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error processing stock-in'], 500);
        }
    }

    // 🟦 9. Stock-Out Processing Page
    public function stockOutIndex()
    {
        return view('Inventory.Stock_Out.stock_out_processing');
    }

    public function stockOutRequisitions()
    {
        try {
            $requisitions = DB::table('requisitions')
                ->select('requisitions.*', 'users.name as requester_name')
                ->join('users', 'requisitions.requested_by', '=', 'users.user_id')
                ->whereIn('req_status', ['approved','pending'])
                ->get()
                ->map(function ($req) {
                    // Get items count for this requisition
                    $totalItems = DB::table('requisition_items')
                        ->where('req_id', $req->req_id)
                        ->count();
                    
                    $pendingItems = DB::table('requisition_items')
                        ->where('req_id', $req->req_id)
                        ->whereIn('req_item_status', ['pending','partially_fulfilled'])
                        ->count();
                    
                    return [
                        'req_id' => $req->req_id,
                        'req_ref' => $req->req_ref,
                        'req_purpose' => $req->req_purpose,
                        'req_date' => $req->req_date,
                        'req_priority' => $req->req_priority,
                        'requester_name' => $req->requester_name,
                        'total_items' => $totalItems,
                        'pending_items' => $pendingItems,
                        'ar_ref' => null,
                        'issued_date' => null
                    ];
                });

            return response()->json([
                'success' => true,
                'requisitions' => $requisitions
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading requisitions'], 500);
        }
    }

    public function stockOutRequisitionItems($reqId)
    {
        try {
            $items = DB::table('requisition_items')
                ->select('requisition_items.*', 'items.item_code', 'items.item_stock', 'items.reorder_level')
                ->leftJoin('items', 'requisition_items.item_id', '=', 'items.item_id')
                ->where('requisition_items.req_id', $reqId)
                ->where('requisition_items.req_item_status', '!=', 'fulfilled')
                ->get();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading items'], 500);
        }
    }

    public function stockOutProcess(Request $request)
    {
        $request->validate([
            'ar_ref' => 'required|string|unique:acknowledgement_receipts,ar_ref',
            'ar_remarks' => 'nullable|string',
            'req_id' => 'required|exists:requisitions,req_id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,item_id',
            'items.*.quantity' => 'required|numeric|min:0.01'
        ]);

        try {
            DB::beginTransaction();
            
            $requisition = \App\Models\Requisition::findOrFail($request->req_id);
            $employee = User::findOrFail($requisition->requested_by);
            
            // Create acknowledgment receipt
            $ar = AcknowledgeReceipt::create([
                'ar_ref' => $request->ar_ref,
                'ar_status' => 'issued',
                'issued_date' => Carbon::today(),
                'req_id' => $requisition->req_id,
                'issued_by' => Auth::id(),
                'issued_to' => $employee->id,
                'ar_remarks' => $request->ar_remarks
            ]);

            // Process each item
            foreach ($request->items as $itemData) {
                $issueQty = $itemData['quantity'];
                
                // Update item stock
                $this->updateItemStock($itemData['item_id'], $issueQty, 'out');
                
                // Create inventory transaction
                InventoryTransaction::create([
                    'trans_ref' => $this->generateTransactionReference(),
                    'trans_type' => 'out',
                    'trans_quantity' => $issueQty,
                    'trans_date' => Carbon::today()->toDateString(), // Use DATE for trans_date
                    'trans_remarks' => "Issued for requisition {$requisition->req_ref}",
                    'item_id' => $itemData['item_id'],
                    'trans_by' => Auth::id()
                ]);

                // Update approved request item status
                $this->updateApprovedRequestItemStatus($itemData['item_id'], $issueQty);
            }

            // Update requisition AR reference and status
            $requisition->update([
                'ar_ref' => $request->ar_ref,
                'issued_date' => Carbon::today(),
                'req_status' => 'issued'
            ]);

            // Send notifications
            $this->createNotification('employee', 'Items issued', 
                "Your requisition {$requisition->req_ref} has been processed. AR: {$request->ar_ref}", 
                route('staff.ar'));
            $this->createNotification('supervisor', 'AR issued', 
                "Acknowledgment receipt {$request->ar_ref} has been issued for requisition {$requisition->req_ref}", 
                route('supervisor.inventory-overview'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items issued successfully',
                'ar_ref' => $request->ar_ref
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error issuing items'], 500);
        }
    }

    // 🟦 10. Reports Module
    public function reportsIndex()
    {
        return view('Inventory.reports');
    }

    public function stockCardReport($itemId)
    {
        try {
            $item = Item::findOrFail($itemId);
            $transactions = InventoryTransaction::where('item_id', $itemId)
                ->orderBy('trans_date', 'desc')
                ->get();

            return view('Inventory.reports.stock_card', compact('item', 'transactions'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.dashboard')->with('error', 'Item not found');
        }
    }

    public function lowStockReport()
    {
        try {
            $lowStockItems = Item::where('item_stock', '<=', DB::raw('reorder_level'))
                ->with('category')
                ->get();

            return response()->json([
                'success' => true,
                'items' => $lowStockItems
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading report'], 500);
        }
    }

    public function expiryReport()
    {
        try {
            $expiringItems = $this->getExpiryAlerts(30);
            
            return response()->json([
                'success' => true,
                'items' => $expiringItems
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading report'], 500);
        }
    }

    // 🟦 11. Transactions
    public function transactionsIndex()
    {
        return view('Inventory.transactions');
    }

    public function transactionsList()
    {
        try {
            $transactions = InventoryTransaction::with('item', 'user')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading transactions'], 500);
        }
    }

    // 🟦 12. Notifications
    public function notificationsIndex()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('Inventory.notifications.notifications', compact('notifications'));
    }

    public function notificationShow($id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())
                ->findOrFail($id);
            
            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            return view('Inventory.notifications.view', compact('notification'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.dashboard')->with('error', 'Notification not found');
        }
    }

    public function notificationMarkRead($id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())
                ->findOrFail($id);
            
            $notification->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error marking notification'], 500);
        }
    }

    public function notificationsMarkAllRead()
    {
        try {
            Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error marking notifications'], 500);
        }
    }

    // 🟦 13. Helper Methods
    private function getExpiryAlerts($days)
    {
        return Item::where('item_expire_date', '<=', Carbon::now()->addDays($days))
            ->whereNotNull('item_expire_date')
            ->with('category')
            ->get();
    }

    private function updateItemStock($itemId, $quantity, $type)
    {
        $item = Item::findOrFail($itemId);
        $stockChange = $type === 'out' ? -$quantity : $quantity;
        $newStock = $item->item_stock + $stockChange;
        
        $item->update(['item_stock' => max(0, $newStock)]);
    }

    private function updateRequisitionItemsStatus($poId, $allFulfilled)
    {
        try {
            // Update requisition items status based on fulfillment
            // This depends on your specific database schema
            
            $purchaseItems = PurchaseItem::where('po_id', $poId)->get();
            $allFulfilled = true;
            
            foreach ($purchaseItems as $purchaseItem) {
                $receivedQty = $purchaseItem->received_quantity ?? 0;
                $orderedQty = $purchaseItem->ordered_quantity;
                
                if ($receivedQty >= $orderedQty) {
                    // Update to fulfilled status
                    // This would be implemented based on your schema
                } elseif ($receivedQty > 0) {
                    // Update to partially fulfilled status
                    // This would be implemented based on your schema
                } else {
                    $allFulfilled = false;
                }
            }
            
            return $allFulfilled;
        } catch (\Exception $e) {
            \Log::error('Error updating requisition items status: ' . $e->getMessage());
            return false;
        }
    }

    private function updateApprovedRequestItemStatus($itemId, $issueQty)
    {
        try {
            // Update approved request item status based on issue quantity
            // This would be implemented based on your specific schema
            \Log::info("Updating approved request item status for item {$itemId} with quantity {$issueQty}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error updating approved request item status: ' . $e->getMessage());
            return false;
        }
    }

    // Additional helper methods for missing routes
    public function alertsIndex()
    {
        try {
            $lowStockItems = $this->getLowStockItems();
            $criticalStockItems = Item::where('item_stock', '<=', DB::raw('min_stock_level'))->get();
            $expiringItems = $this->getExpiryAlerts(30);
            
            return view('Inventory.alerts', compact('lowStockItems', 'criticalStockItems', 'expiringItems'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.dashboard')->with('error', 'Error loading alerts');
        }
    }

    public function notificationsJump($id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())
                ->findOrFail($id);
            
            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }
            
            // Simplify redirection
            return redirect($notification->notif_url ?? route('inventory.dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.dashboard')->with('error', 'Notification not found');
        }
    }

    public function notifyLowStock()
    {
        try {
            $lowStockItems = $this->getLowStockItems();
            
            foreach ($lowStockItems as $item) {
                // Check if notification already exists today
                $existingNotification = Notification::where('user_id', Auth::id())
                    ->where('notif_title', 'Low Stock Alert')
                    ->where('notif_content', 'like', "%{$item->item_name}%")
                    ->whereDate('created_at', Carbon::today())
                    ->first();
                
                if (!$existingNotification) {
                    $this->createNotification('supervisor', 'Low Stock Alert',
                        "Item '{$item->item_name}' has reached reorder level: {$item->item_stock}");
                    
                    $this->createNotification('inventory', 'Low Stock Alert',
                        "Item '{$item->item_name}' has reached reorder level: {$item->item_stock}");
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Low stock notifications sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error sending notifications'], 500);
        }
    }

    public function approvedRequestItems()
    {
        try {
            // Get approved request items that could become inventory items
            $items = DB::table('approved_request_items')
                ->select('approved_request_items.*', 'requisitions.user_id', 'users.name as requester_name')
                ->join('requisitions', 'approved_request_items.req_id', '=', 'requisitions.req_id')
                ->join('users', 'requisitions.user_id', '=', 'users.id')
                ->where('req_item_status', 'approved')
                ->whereNotNull('item_description')
                ->where('item_description', '!=', '')
                ->get();
                
            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading approved request items'], 500);
        }
    }

    private function getLowStockItems()
    {
        return Item::where('item_stock', '<=', DB::raw('reorder_level'))
            ->where('item_stock', '>', DB::raw('min_stock_level'))
            ->with('category')
            ->get();
    }

    // Store transaction method
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'trans_type' => 'required|in:in,out,adjustment',
            'trans_quantity' => 'required|numeric|min:0.01',
            'trans_remarks' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            $item = Item::findOrFail($request->item_id);
            $quantity = $request->trans_quantity;
            
            // Validate negative stock for OUT transactions
            if ($request->trans_type === 'out' && $item->item_stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for this transaction'
                ], 422);
            }

            // Calculate new stock level
            $stockChange = $request->trans_type === 'out' ? -$quantity : $quantity;
            $newStock = $item->item_stock + $stockChange;

            // Update item stock
            $item->update(['item_stock' => $newStock]);

            // Create inventory transaction
            $transaction = InventoryTransaction::create([
                'trans_ref' => $this->generateTransactionReference(),
                'trans_type' => $request->trans_type,
                'trans_quantity' => $quantity,
                'trans_date' => Carbon::now()->toDateString(), // Use DATE for trans_date
                'trans_remarks' => $request->trans_remarks,
                'item_id' => $item->item_id,
                'trans_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction recorded successfully',
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error recording transaction'], 500);
        }
    }

    public function transactionShow($id)
    {
        try {
            $transaction = InventoryTransaction::with('item', 'user')
                ->findOrFail($id);
                
            return response()->json([
                'success' => true,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }
    }

    public function itemTransactions($itemId)
    {
        try {
            $item = Item::findOrFail($itemId);
            $transactions = InventoryTransaction::where('item_id', $itemId)
                ->with('user')
                ->orderBy('trans_date', 'desc')
                ->paginate(20);
                
            return view('Inventory.item_transactions', compact('item', 'transactions'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.dashboard')->with('error', 'Item not found');
        }
    }

    // Additional bulk stock-in method
    public function storeBulkStockIn(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:purchase_orders,po_id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,item_id',
            'items.*.quantity' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();
            
            $totalProcessed = 0;
            
            foreach ($request->items as $itemData) {
                if ($itemData['quantity'] > 0) {
                    $this->updateItemStock($itemData['item_id'], $itemData['quantity'], 'in');
                    
                    InventoryTransaction::create([
                        'trans_ref' => $this->generateTransactionReference(),
                        'trans_type' => 'in',
                        'trans_quantity' => $itemData['quantity'],
                        'trans_date' => Carbon::now()->toDateString(), // Use DATE for trans_date
                        'trans_remarks' => 'Bulk stock-in processing',
                        'item_id' => $itemData['item_id'],
                        'po_id' => $request->po_id,
                        'trans_by' => Auth::id()
                    ]);
                    
                    $totalProcessed++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$totalProcessed} items",
                'processed_count' => $totalProcessed
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error processing bulk stock-in'], 500);
        }
    }

    private function generateTransactionReference()
    {
        return 'TX-' . Carbon::now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function generateMemoReference()
    {
        return 'MEMO-' . Carbon::now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    private function createNotification($role, $title, $message, $url = null)
    {
        $users = User::where('role', $role)->get();
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->user_id,
                'notif_title' => $title,
                'notif_content' => $message, // Write notif_content
                'notif_url' => $url,
                'is_read' => false,
                'created_at' => Carbon::now()
            ]);
        }
    }

    // Bulk operations
    public function bulkUpdateItems(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,item_id',
            'updates' => 'required|array'
        ]);

        try {
            Item::whereIn('item_id', $request->item_ids)->update($request->updates);
            
            return response()->json([
                'success' => true,
                'message' => 'Items updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating items'], 500);
        }
    }

    public function itemDeactivate($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->update(['is_active' => false]);
            
            return response()->json([
                'success' => true,
                'message' => 'Item deactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deactivating item'], 500);
        }
    }

    public function acknowledgeReceiptsIndex()
    {
        $receipts = AcknowledgeReceipt::with('requisition', 'receiver')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('Inventory.AR.acknowledge_receipts', compact('receipts'));
    }

    // 🟦 14. Acknowledge Receipt Actions
    public function acknowledgeReceiptView($id)
    {
        try {
            $receipt = AcknowledgeReceipt::with('requisition', 'receiver', 'issuer')
                ->findOrFail($id);
                
            return view('Inventory.AR.view', compact('receipt'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.acknowledge-receipts.index')->with('error', 'Receipt not found');
        }
    }

    public function acknowledgeReceiptPrint($id)
    {
        try {
            $receipt = AcknowledgeReceipt::with('requisition', 'receiver', 'issuer')
                ->findOrFail($id);
                
            // Extract the related data for the print template
            $receiver = $receipt->receiver;
            $issuer = $receipt->issuer;
            $items = $receipt->requisition ? $receipt->requisition->items : collect();
                
            return view('Inventory.AR.print', compact('receipt', 'receiver', 'issuer', 'items'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.acknowledge-receipts.index')->with('error', 'Receipt not found');
        }
    }

    public function acknowledgeReceiptExport()
    {
        try {
            $receipts = AcknowledgeReceipt::with('requisition', 'receiver', 'issuer')
                ->orderBy('created_at', 'desc')
                ->get();
                
            return view('Inventory.AR.export', compact('receipts'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.acknowledge-receipts.index')->with('error', 'Error loading receipts for export');
        }
    }

    public function recentTransactions()
    {
        try {
            $transactions = InventoryTransaction::with('item')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error loading transactions'], 500);
        }
    }
}