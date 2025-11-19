<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str; // Import Str facade for slugs
use Illuminate\Support\Facades\Hash; // Import Hash facade

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // High-level metrics
        $metrics = [
            'users'        => DB::table('users')->count(),
            'suppliers'    => DB::table('suppliers')->count(),
            'items'        => DB::table('items')->count(),
            'req_pending'  => DB::table('requisitions')->where('req_status', 'pending')->count(),
            'req_approved' => DB::table('requisitions')->where('req_status', 'approved')->count(),
            'req_rejected' => DB::table('requisitions')->where('req_status', 'rejected')->count(),
            'po_draft'     => DB::table('purchase_orders')->where('po_status', 'draft')->count(),
            'po_ordered'   => DB::table('purchase_orders')->where('po_status', 'ordered')->count(),
            'po_delivered' => DB::table('purchase_orders')->where('po_status', 'delivered')->count(),
        ];

        // Low-stock items: prefer stored function if available
        try {
            $lowStock = collect(DB::select('SELECT * FROM get_low_stock_items()'));
        } catch (Throwable $e) {
            $lowStock = DB::table('items')
                ->whereColumn('item_stock', '<=', 'reorder_level')
                ->select('item_name as name', 'item_stock as current_stock', 'item_unit as unit', 'reorder_level')
                ->get();
        }
        $lowStock = $lowStock->take(4);

        // Near-expiry items (30-day horizon): prefer stored function
        try {
            $expiry = collect(DB::select('SELECT * FROM get_expiry_alerts(?)', [30]));
        } catch (Throwable $e) {
            // Fallback for PostgreSQL-compatible raw
            $expiry = DB::table('items')
                ->whereNotNull('item_expire_date')
                ->whereRaw("item_expire_date <= CURRENT_DATE + INTERVAL '30 day'")
                ->select('item_name', 'item_expire_date as expiry_date')
                ->get();
        }
        $expiry = $expiry->take(4);

        // Latest inventory transactions
        $txns = DB::table('inventory_transactions as t')
            ->join('items as i', 'i.item_id', '=', 't.item_id')
            ->select('i.item_name as name', 't.trans_type as type', 't.trans_quantity as quantity', 't.created_at')
            ->orderByDesc('t.created_at')
            ->limit(5)
            ->get();

        // Recent activities (notifications)
        $acts = DB::table('notifications')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('Admin.system_overview', compact('metrics', 'lowStock', 'expiry', 'txns', 'acts'));
    }

    public function requisitions()
    {
        $totalCount     = DB::table('requisitions')->count();
        $pendingCount   = DB::table('requisitions')->where('req_status','pending')->count();
        $approvedCount  = DB::table('requisitions')->where('req_status','approved')->count();
        $rejectedCount  = DB::table('requisitions')->where('req_status','rejected')->count();
        $completedCount = DB::table('requisitions')->where('req_status','completed')->count();

        $requisitions = DB::table('requisitions as r')
            ->leftJoin('users as u1','u1.user_id','=','r.requested_by')
            ->leftJoin('users as u2','u2.user_id','=','r.approved_by')
            ->select(
                'r.*',
                DB::raw('(SELECT COUNT(*) FROM requisition_items ri WHERE ri.req_id = r.req_id) as items_count'),
                'u1.name as requester_name',
                'u2.name as approver_name'
            )
            ->orderByDesc('r.created_at')
            ->paginate(15);

        $requisitions->getCollection()->transform(function($row){
            $row->requester = (object)['name' => $row->requester_name];
            $row->approver  = (object)['name' => $row->approver_name];
            $row->items     = collect(array_fill(0, (int)($row->items_count ?? 0), 1));
            return $row;
        });

        return view('Admin.Requisition.requisition', compact('totalCount','pendingCount','approvedCount','rejectedCount','completedCount','requisitions'));
    }

    public function itemRequests()
    {
        $totalRequests    = DB::table('item_requests')->count();
        $pendingRequests  = DB::table('item_requests')->where('item_req_status','pending')->count();
        $approvedRequests = DB::table('item_requests')->where('item_req_status','approved')->count();
        $rejectedRequests = DB::table('item_requests')->where('item_req_status','rejected')->count();

        $requests = DB::table('item_requests as ir')
            ->leftJoin('users as u1','u1.user_id','=','ir.requested_by')
            ->leftJoin('users as u2','u2.user_id','=','ir.approved_by')
            ->select('ir.*','u1.name as requester_name','u2.name as approver_name')
            ->orderByDesc('ir.created_at')
            ->paginate(15);

        $requests->getCollection()->transform(function($row){
            $row->requester = (object)['name' => $row->requester_name];
            $row->approver  = (object)['name' => $row->approver_name];
            return $row;
        });

        return view('Admin.Requisition.item_request', compact('requests','totalRequests','pendingRequests','approvedRequests','rejectedRequests'));
    }

    public function inventoryTransactions()
    {
        $totalCount    = DB::table('inventory_transactions')->count();
        $inCount       = DB::table('inventory_transactions')->where('trans_type','in')->count();
        $outCount      = DB::table('inventory_transactions')->where('trans_type','out')->count();
        $adjCount      = DB::table('inventory_transactions')->where('trans_type','adjustment')->count();
        $ackCount      = DB::table('acknowledge_receipts')->count();
        $negStockCount = DB::table('items')->where('item_stock','<',0)->count();

        $transactions = DB::table('inventory_transactions as t')
            ->leftJoin('items as i','i.item_id','=','t.item_id')
            ->leftJoin('users as u','u.user_id','=','t.trans_by')
            ->leftJoin('purchase_orders as po','po.po_id','=','t.po_id')
            ->leftJoin('memos as m','m.po_ref','=','po.po_ref')
            ->leftJoin('acknowledge_receipts as ar','ar.req_id','=','po.req_id')
            ->select('t.*','i.item_name','i.item_stock','u.name as user_name','po.po_ref','m.memo_id','m.memo_ref','ar.ar_id','ar.ar_ref')
            ->orderByDesc('t.created_at')
            ->paginate(15);

        $transactions->getCollection()->transform(function($row){
            $row->item  = (object)['item_name' => $row->item_name, 'current_stock' => $row->item_stock];
            $row->user  = (object)['name' => $row->user_name];
            $row->quantity = $row->trans_quantity;
            $row->inventory_transaction_id = $row->trans_id;
            
            if ($row->trans_type === 'out' && $row->ar_id) {
                $row->acknowledgeReceipt = (object)['id' => $row->ar_id, 'ar_ref' => $row->ar_ref];
            } else {
                $row->acknowledgeReceipt = null;
            }
            
            if ($row->trans_type === 'in' && $row->memo_id) {
                $row->memo = (object)['id' => $row->memo_id, 'memo_ref' => $row->memo_ref];
            } else {
                $row->memo = null;
            }
            
            return $row;
        });

        $users = DB::table('users')->select('user_id','name')->orderBy('name')->get();

        return view('Admin.Inventory.inventory_transaction', compact('totalCount','inCount','outCount','adjCount','ackCount','negStockCount','transactions','users'));
    }

    public function itemManagement()
    {
        $categoriesCount = DB::table('categories')->count();
        $totalItems      = DB::table('items')->count();
        $lowStockCount   = DB::table('items')->whereColumn('item_stock','<=','reorder_level')->count();
        $expiringCount   = DB::table('items')->whereNotNull('item_expire_date')
            ->whereRaw("item_expire_date <= CURRENT_DATE + INTERVAL '30 day'")
            ->count();

        $items = DB::table('items as i')
            ->leftJoin('categories as c','c.cat_id','=','i.cat_id')
            ->select('i.*','c.cat_name')
            ->orderBy('i.item_name')
            ->get();

        $categories = DB::table('categories')->select('cat_id','cat_name')->orderBy('cat_name')->get();

        $pendingItemCreations = DB::table('approved_request_items as ari')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'ari.req_id')
            ->leftJoin('users as u_req', 'u_req.user_id', '=', 'r.requested_by')
            ->leftJoin('item_requests as ir', DB::raw("ari.req_ref"), '=', DB::raw("'IR-' || ir.item_req_id"))
            ->leftJoin('users as u_ir', 'u_ir.user_id', '=', 'ir.requested_by')
            ->where('ari.created_as_item', false)
            ->whereNull('ari.item_id') 
            ->select(
                'ari.req_item_id', 
                'ari.item_name', 
                'ari.item_unit', 
                'ari.item_description', 
                DB::raw('COALESCE(u_req.name, u_ir.name, \'Unknown\') as requester_name')
            )
            ->get();
        
        return view('Admin.Inventory.item_management', compact(
            'categoriesCount','totalItems','lowStockCount','expiringCount','items','categories',
            'pendingItemCreations'
        ));
    }

    public function reports()
    {
        $activeUsers            = DB::table('users')->where('status','active')->count();
        $pendingItemRequests    = DB::table('item_requests')->where('item_req_status','pending')->count();
        $pendingRequisitions    = DB::table('requisitions')->where('req_status','pending')->count();
        $orderedPOs             = DB::table('purchase_orders')->where('po_status','ordered')->count();
        $movementCount          = DB::table('inventory_transactions')->count();
        $lowStockCount          = DB::table('items')->whereColumn('item_stock','<=','reorder_level')->count();
        $suppliersCount         = DB::table('suppliers')->count();
        $weeklyStockInCount     = DB::table('inventory_transactions')
            ->where('trans_type','in')
            ->whereBetween('trans_date',[now()->subDays(7)->toDateString(), now()->toDateString()])
            ->count();
        $negativeStockCount     = DB::table('items')->where('item_stock','<',0)->count();
        $arIssuedCount          = DB::table('acknowledge_receipts')->where('ar_status','issued')->count();

        return view('Admin.Report.report', compact(
            'activeUsers','pendingItemRequests','pendingRequisitions','orderedPOs','movementCount','lowStockCount','suppliersCount','weeklyStockInCount','negativeStockCount','arIssuedCount'
        ));
    }

    public function purchaseOrders()
    {
        $totalPOs       = DB::table('purchase_orders')->count();
        $draftCount     = DB::table('purchase_orders')->where('po_status','draft')->count();
        $orderedCount   = DB::table('purchase_orders')->where('po_status','ordered')->count();
        $deliveredCount = DB::table('purchase_orders')->where('po_status','delivered')->count();
        $thisMonthCount = DB::table('purchase_orders')->whereBetween('created_at',[now()->startOfMonth(), now()->endOfMonth()])->count();

        $purchaseOrders = DB::table('purchase_orders as p')
            ->leftJoin('suppliers as s','s.sup_id','=','p.sup_id')
            ->leftJoin('requisitions as r','r.req_id','=','p.req_id')
            ->select('p.*','s.sup_name','r.req_ref', DB::raw('(SELECT COUNT(*) FROM purchase_items pi WHERE pi.po_id = p.po_id) as items_count'))
            ->orderByDesc('p.created_at')
            ->paginate(15);

        $purchaseOrders->getCollection()->transform(function($row){
            $row->supplier    = (object)['sup_name' => $row->sup_name];
            $row->requisition = (object)['req_ref'  => $row->req_ref];
            $row->items       = collect(array_fill(0, (int)($row->items_count ?? 0), 1));
            return $row;
        });

        return view('Admin.Purchasing.Purchase.purchase', compact('totalPOs','draftCount','orderedCount','deliveredCount','thisMonthCount','purchaseOrders'));
    }

    public function purchaseOrderShow($id)
    {
        $po = DB::table('purchase_orders as p')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'p.sup_id')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'p.req_id')
            ->select('p.po_id', 'p.po_ref', 'p.po_status', 'p.expected_delivery_date', 'p.total_amount', 'p.delivery_address', 's.sup_name', 'r.req_ref')
            ->where('p.po_id', $id)
            ->first();

        if (!$po) {
            return response()->json(['success' => false, 'message' => 'Purchase order not found'], 404);
        }

        $items = DB::table('purchase_items as pi')
            ->leftJoin('items as i', 'i.item_id', '=', 'pi.item_id')
            ->select('i.item_name', 'pi.pi_quantity as quantity', 'i.item_unit as unit', 'pi.pi_unit_price as unit_price', 'pi.pi_subtotal as subtotal')
            ->where('pi.po_id', $id)
            ->get();

        return response()->json(['success' => true, 'po' => $po, 'items' => $items]);
    }

    public function purchaseOrderStatusUpdate(Request $request, $id)
    {
        $data = $request->validate(['po_status' => 'required|in:draft,ordered,delivered,cancelled']);

        $updated = DB::table('purchase_orders')
            ->where('po_id', $id)
            ->update(['po_status' => $data['po_status'], 'updated_at' => now()]);

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'Purchase order not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Purchase order status updated successfully']);
    }

    public function transactionShow($id)
    {
        try {
            $transaction = DB::table('inventory_transactions as t')
                ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
                ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
                ->leftJoin('purchase_orders as po', 'po.po_id', '=', 't.po_id')
                ->select('t.*', 'i.item_name', 'i.item_code', 'i.item_unit', 'u.name as user_name', 'po.po_ref')
                ->where('t.trans_id', $id)
                ->first();

            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            return response()->json([
                'date' => \Carbon\Carbon::parse($transaction->trans_date ?? $transaction->created_at)->format('M d, Y H:i'),
                'type' => ucfirst($transaction->trans_type),
                'item_code' => $transaction->item_code,
                'item_name' => $transaction->item_name,
                'item_unit' => $transaction->item_unit,
                'quantity' => $transaction->trans_quantity,
                'user' => $transaction->user_name,
                'po_ref' => $transaction->po_ref,
                'remarks' => $transaction->trans_remarks
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error loading transaction details'], 500);
        }
    }

    public function acknowledgeReceipts()
    {
        $ackReceipts = DB::table('acknowledge_receipts as ar')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'ar.req_id')
            ->leftJoin('users as u1', 'u1.user_id', '=', 'ar.issued_by')
            ->leftJoin('users as u2', 'u2.user_id', '=', 'ar.issued_to')
            ->select('ar.*', 'r.req_ref', 'u1.name as issued_by_name', 'u2.name as issued_to_name')
            ->orderByDesc('ar.created_at')
            ->paginate(15);

        $ackReceipts->getCollection()->transform(function($row){
            $row->requisition = (object)['req_ref' => $row->req_ref];
            $row->issuedBy = (object)['name' => $row->issued_by_name];
            $row->issuedTo = (object)['name' => $row->issued_to_name];
            return $row;
        });

        return view('Admin.Inventory.acknowledge_receipts', compact('ackReceipts'));
    }

    public function showAcknowledgeReceipt($id)
    {
        $ar = DB::table('acknowledge_receipts as ar')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'ar.req_id')
            ->leftJoin('users as u1', 'u1.user_id', '=', 'ar.issued_by')
            ->leftJoin('users as u2', 'u2.user_id', '=', 'ar.issued_to')
            ->select('ar.*', 'r.req_ref', 'u1.name as issued_by_name', 'u2.name as issued_to_name')
            ->where('ar.ar_id', $id)
            ->first();

        if (!$ar) {
            return response()->json(['error' => 'Acknowledge receipt not found'], 404);
        }

        $ar->requisition = (object)['req_ref' => $ar->req_ref];
        $ar->issuedBy = (object)['name' => $ar->issued_by_name];
        $ar->issuedTo = (object)['name' => $ar->issued_to_name];

        return response()->json(['success' => true, 'acknowledge_receipt' => $ar]);
    }

    public function memos()
    {
        $memos = DB::table('memos as m')
            ->leftJoin('users as u', 'u.user_id', '=', 'm.received_by')
            ->select('m.*', 'u.name as received_by_name')
            ->orderByDesc('m.created_at')
            ->paginate(15);

        $memos->getCollection()->transform(function($row){
            $row->receivedBy = (object)['name' => $row->received_by_name];
            return $row;
        });

        return view('Admin.Inventory.memos', compact('memos'));
    }

    public function showMemo($id)
    {
        $memo = DB::table('memos as m')
            ->leftJoin('users as u', 'u.user_id', '=', 'm.received_by')
            ->select('m.*', 'u.name as received_by_name')
            ->where('m.memo_id', $id)
            ->first();

        if (!$memo) {
            return response()->json(['error' => 'Memo not found'], 404);
        }

        $memo->receivedBy = (object)['name' => $memo->received_by_name];

        return response()->json(['success' => true, 'memo' => $memo]);
    }

    public function suppliers()
    {
        $suppliers = DB::table('suppliers')->orderBy('sup_name')->get();
        $totalSuppliers   = $suppliers->count();
        $activeSuppliers  = DB::table('suppliers')->where('sup_status','active')->count();
        $inactiveSuppliers= DB::table('suppliers')->where('sup_status','!=','active')->count();
        $thisMonthSuppliers= DB::table('suppliers')->whereBetween('created_at',[now()->startOfMonth(), now()->endOfMonth()])->count();

        return view('Admin.Purchasing.Supplier.supplier', compact('suppliers','totalSuppliers','activeSuppliers','inactiveSuppliers','thisMonthSuppliers'));
    }

    public function users()
    {
        $users = DB::table('users')->orderBy('name')->get();
        $totalUsers    = $users->count();
        $activeUsers   = DB::table('users')->where('status','active')->count();
        $inactiveUsers = DB::table('users')->where('status','inactive')->count();
        $adminsCount   = DB::table('users')->where('role','admin')->count();

        return view('Admin.User.user_management', compact('users','totalUsers','activeUsers','inactiveUsers','adminsCount'));
    }

    public function notifications()
    {
        $notifications = DB::table('notifications')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function ($n) {
                $n->created_at = \Carbon\Carbon::parse($n->created_at);
                $n->updated_at = $n->updated_at ? \Carbon\Carbon::parse($n->updated_at) : null;
                return $n;
            });
        $totalNotifications  = DB::table('notifications')->count();
        $unreadNotifications = DB::table('notifications')->where('is_read', false)->count();
        $readNotifications   = DB::table('notifications')->where('is_read', true)->count();
        $weekNotifications   = DB::table('notifications')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $users = DB::table('users')->select('user_id', 'name')->orderBy('name')->get();

        return view('Admin.Notification.notification', compact('notifications','totalNotifications','unreadNotifications','readNotifications','weekNotifications','users'));
    }

    // --- Notification AJAX endpoints ---
    public function notificationMarkRead($id)
    {
        $userId = Auth::id();
        $updated = DB::table('notifications')
            ->where('notif_id', $id)
            ->when($userId, fn($q)=>$q->where('user_id', $userId))
            ->update(['is_read' => true]);
        return response()->json(['success' => (bool)$updated]);
    }

    public function notificationMarkAllRead()
    {
        $userId = Auth::id();
        $updated = DB::table('notifications')
            ->when($userId, fn($q)=>$q->where('user_id', $userId))
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function notificationUnreadCount()
    {
        $userId = Auth::id();
        $count = DB::table('notifications')
            ->when($userId, fn($q)=>$q->where('user_id', $userId))
            ->where('is_read', false)
            ->count();
        return response()->json(['count' => $count]);
    }

    public function notificationShow($id)
    {
        $notification = DB::table('notifications as n')
            ->leftJoin('users as u', 'u.user_id', '=', 'n.user_id')
            ->select('n.*', 'u.name as user_name')
            ->where('n.notif_id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->is_read) {
            DB::table('notifications')->where('notif_id', $id)->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'notification' => [
                'title' => $notification->notif_title,
                'content' => $notification->notif_content,
                'related_type' => $notification->related_type,
                'related_id' => $notification->related_id,
                'user' => $notification->user_name,
                'created_at' => optional($notification->created_at)->format('M d, Y H:i'),
            ],
        ]);
    }

    // --- Category create (JSON) ---
    public function storeCategory(Request $request)
    {
        $data = $request->validate(['cat_name' => 'required|string|max:255']);
        $exists = DB::table('categories')->where('cat_name', $data['cat_name'])->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Category already exists']);
        }
        DB::table('categories')->insert(['cat_name' => $data['cat_name'], 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    // --- Items CRUD + stock (JSON) ---
    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'item_name' => 'required|string|max:255',
            'cat_id' => 'required|integer',
            'item_unit' => 'required|string|max:255',
            'item_stock' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'item_description' => 'nullable|string',
            'approved_item_req_id' => 'nullable|integer|exists:approved_request_items,req_item_id',
        ]);
        
        $code = 'ITM-'.strtoupper(substr(preg_replace('/\s+/', '', $data['item_name']),0,3)).'-'.str_pad((string) (DB::table('items')->max('item_id') + 1), 4, '0', STR_PAD_LEFT);
        
        $newItemId = DB::table('items')->insertGetId([
            'item_code' => $code,
            'item_name' => $data['item_name'],
            'item_description' => $data['item_description'] ?? null,
            'item_unit' => $data['item_unit'],
            'cat_id' => $data['cat_id'],
            'item_stock' => $data['item_stock'],
            'item_expire_date' => $data['item_expire_date'] ?? null,
            'reorder_level' => $data['reorder_level'],
            'min_stock_level' => 0,
            'last_updated' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'item_id');

        if (!empty($data['approved_item_req_id'])) {
            DB::table('approved_request_items')
                ->where('req_item_id', $data['approved_item_req_id'])
                ->update([
                    'created_as_item' => true,
                    'item_id' => $newItemId
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function showItem($id)
    {
        $item = DB::table('items')->where('item_id', $id)->first();
        if (!$item) return response()->json(['error' => 'Not found'], 404);
        return response()->json($item);
    }

    public function updateItem(Request $request, $id)
    {
        $data = $request->validate([
            'item_name' => 'required|string|max:255',
            'cat_id' => 'required|integer',
            'item_unit' => 'required|string|max:255',
            'reorder_level' => 'required|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'item_description' => 'nullable|string',
        ]);
        $updated = DB::table('items')->where('item_id', $id)->update([
            'item_name' => $data['item_name'],
            'item_description' => $data['item_description'] ?? null,
            'item_unit' => $data['item_unit'],
            'cat_id' => $data['cat_id'],
            'item_expire_date' => $data['item_expire_date'] ?? null,
            'reorder_level' => $data['reorder_level'],
            'last_updated' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => (bool)$updated]);
    }

    public function stockItem(Request $request, $id)
    {
        $data = $request->validate(['current_stock' => 'required|numeric|min:0']);
        $updated = DB::table('items')->where('item_id', $id)->update([
            'item_stock' => $data['current_stock'],
            'last_updated' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => (bool)$updated]);
    }

    public function deleteItem($id)
    {
        $hasTx = DB::table('inventory_transactions')->where('item_id', $id)->exists();
        if ($hasTx) return response()->json(['success' => false, 'message' => 'Cannot delete item with existing inventory transactions']);
        $deleted = DB::table('items')->where('item_id', $id)->delete();
        return response()->json(['success' => (bool)$deleted, 'message' => $deleted ? 'Item deleted successfully' : 'Item not found']);
    }

    public function showRequisition($id)
    {
        $items = DB::table('requisition_items as ri')
            ->join('items as i', 'i.item_id', '=', 'ri.item_id')
            ->where('ri.req_id', $id)
            ->select('ri.req_item_quantity', 'ri.item_unit', 'i.item_name')
            ->get()
            ->map(fn($r)=> [
                'item' => ['item_name' => $r->item_name],
                'req_item_quantity' => $r->req_item_quantity,
                'item_unit' => $r->item_unit,
            ]);
        return response()->json(['items' => $items]);
    }

    public function updateRequisitionStatus(Request $request, $id)
    {
        $data = $request->validate([
            'req_status' => 'required|in:approved,rejected', 
            'remarks' => 'required_if:req_status,rejected|nullable|string|max:255', 
        ]);

        try {
            $requisition = DB::table('requisitions')->where('req_id', $id)->first();

            if (!$requisition) {
                return response()->json(['success' => false, 'message' => 'Requisition not found.'], 404);
            }

            if ($requisition->req_status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'This requisition has already been processed.'], 422);
            }

            $updateData = [
                'req_status' => $data['req_status'], 
                'approved_by' => Auth::id(),
                'updated_at' => now(),
            ];

            if ($data['req_status'] === 'approved') { 
                $updateData['approved_date'] = now()->toDateString();
                $updateData['req_reject_reason'] = null;
            } else {
                $updateData['req_reject_reason'] = $data['remarks']; 
                $updateData['approved_date'] = null;
            }

            DB::table('requisitions')->where('req_id', $id)->update($updateData);

            $requesterId = $requisition->requested_by;
            if ($requesterId) {
                DB::table('notifications')->insert([
                    'notif_title' => 'Requisition ' . $data['req_status'], 
                    'notif_content' => 'Your requisition ' . $requisition->req_ref . ' has been ' . $data['req_status'] . '.', 
                    'related_id' => $id,
                    'related_type' => 'requisition',
                    'user_id' => $requesterId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Requisition has been ' . $data['req_status'] . '.' 
            ]);

        } catch (\Exception $e) {
            \Log::error('Requisition status update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the status.'], 500);
        }
    }
    
    public function updateItemRequestStatus(Request $request, $id)
    {
        $data = $request->validate([
            'item_req_status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:item_req_status,rejected|nullable|string|max:255',
        ]);

        try {
            $itemRequest = DB::table('item_requests')->where('item_req_id', $id)->first();

            if (!$itemRequest) {
                return response()->json(['success' => false, 'message' => 'Item Request not found.'], 404);
            }

            if ($itemRequest->item_req_status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'This request has already been processed.'], 422);
            }

            $updateData = [
                'item_req_status' => $data['item_req_status'],
                'approved_by' => Auth::id(),
                'updated_at' => now(),
            ];

            if ($data['item_req_status'] === 'approved') {
                $updateData['item_req_reject_reason'] = null;

                DB::table('approved_request_items')->insert([
                    'req_id' => null,
                    'item_id' => null,
                    'item_name' => $itemRequest->item_req_name,
                    'item_description' => $itemRequest->item_req_description,
                    'item_unit' => $itemRequest->item_req_unit,
                    'requested_quantity' => $itemRequest->item_req_quantity,
                    'approved_quantity' => $itemRequest->item_req_quantity,
                    'req_ref' => 'IR-' . $itemRequest->item_req_id,
                    'created_as_item' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            } else {
                $updateData['item_req_reject_reason'] = $data['remarks'];
            }

            DB::table('item_requests')->where('item_req_id', $id)->update($updateData);

            $requesterId = $itemRequest->requested_by;
            if ($requesterId) {
                DB::table('notifications')->insert([
                    'notif_title' => 'Item Request ' . $data['item_req_status'],
                    'notif_content' => 'Your item request for "' . $itemRequest->item_req_name . '" has been ' . $data['item_req_status'] . '.',
                    'related_id' => $id,
                    'related_type' => 'item_request',
                    'user_id' => $requesterId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item Request has been ' . $data['item_req_status'] . '.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Item Request status update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the status.'], 500);
        }
    }

    // Notification compose (JSON)
    public function composeNotification(Request $request)
    {
        $data = $request->validate([
            'notif_title' => 'required|string|max:255',
            'notif_content' => 'required|string',
            'related_type' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer',
        ]);
        DB::table('notifications')->insert([
            'notif_title' => $data['notif_title'],
            'notif_content' => $data['notif_content'],
            'related_type' => $data['related_type'] ?? null,
            'related_id' => null,
            'is_read' => false,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    // --- Supplier CRUD ---
    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'sup_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'sup_email' => 'required|email|max:255',
            'sup_address' => 'required|string',
        ]);
        $exists = DB::table('suppliers')->where('sup_name', $data['sup_name'])->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Supplier already exists']);
        }
        DB::table('suppliers')->insert([
            'sup_name' => $data['sup_name'],
            'contact_person' => $data['contact_person'],
            'contact_number' => $data['contact_number'],
            'sup_email' => $data['sup_email'],
            'sup_address' => $data['sup_address'],
            'sup_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function showSupplier($id)
    {
        $supplier = DB::table('suppliers')->where('sup_id', $id)->first();
        if (!$supplier) return response()->json(['error' => 'Not found'], 404);
        return response()->json($supplier);
    }

    public function updateSupplier(Request $request, $id)
    {
        $data = $request->validate([
            'sup_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'sup_email' => 'required|email|max:255',
            'sup_address' => 'required|string',
            'sup_status' => 'required|in:active,inactive',
        ]);
        $exists = DB::table('suppliers')->where('sup_name', $data['sup_name'])->where('sup_id', '!=', $id)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Supplier name already exists']);
        }
        $updated = DB::table('suppliers')->where('sup_id', $id)->update([
            'sup_name' => $data['sup_name'],
            'contact_person' => $data['contact_person'],
            'contact_number' => $data['contact_number'],
            'sup_email' => $data['sup_email'],
            'sup_address' => $data['sup_address'],
            'sup_status' => $data['sup_status'],
            'updated_at' => now(),
        ]);
        return response()->json(['success' => (bool)$updated]);
    }

    public function toggleSupplier(Request $request, $id)
    {
        $supplier = DB::table('suppliers')->where('sup_id', $id)->first();
        if (!$supplier) return response()->json(['success' => false, 'message' => 'Supplier not found']);
        $newStatus = $supplier->sup_status === 'active' ? 'inactive' : 'active';
        $updated = DB::table('suppliers')->where('sup_id', $id)->update([
            'sup_status' => $newStatus,
            'updated_at' => now(),
        ]);
        return response()->json(['success' => (bool)$updated]);
    }

    public function deleteSupplier($id)
    {
        $hasPOs = DB::table('purchase_orders')->where('sup_id', $id)->exists();
        if ($hasPOs) return response()->json(['success' => false, 'message' => 'Cannot delete supplier with existing purchase orders']);
        $deleted = DB::table('suppliers')->where('sup_id', $id)->delete();
        return response()->json(['success' => (bool)$deleted, 'message' => $deleted ? 'Supplier deleted successfully' : 'Supplier not found']);
    }

    // --- User CRUD ---
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,employee,inventory,purchasing,supervisor',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'contact' => 'required|string|max:255',
        ]);

        try {
            DB::table('users')->insert([
                'username' => $data['username'],
                'password' => Hash::make($data['password']), // Use Hash::make
                'role' => $data['role'],
                'name' => $data['name'],
                'position' => $data['position'],
                'email' => $data['email'],
                'contact' => $data['contact'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = 'User created successfully';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            } else {
                return redirect()->back()->with('error', $message);
            }
        }
    }

    public function showUser($id)
    {
        $user = DB::table('users')->where('user_id', $id)->first();
        if (!$user) return response()->json(['error' => 'Not found'], 404);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id . ',user_id',
            'role' => 'required|in:admin,employee,inventory,purchasing,supervisor',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id . ',user_id',
            'contact' => 'required|string|max:255',
        ]);

        try {
            $updated = DB::table('users')->where('user_id', $id)->update([
                'username' => $data['username'],
                'role' => $data['role'],
                'name' => $data['name'],
                'position' => $data['position'],
                'email' => $data['email'],
                'contact' => $data['contact'],
                'updated_at' => now(),
            ]);
            $message = $updated ? 'User updated successfully' : 'User not found';
            if ($request->expectsJson()) {
                return response()->json(['success' => (bool)$updated, 'message' => $message]);
            } else {
                return redirect()->back()->with($updated ? 'success' : 'error', $message);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            } else {
                return redirect()->back()->with('error', $message);
            }
        }
    }

    public function toggleUserStatus(Request $request, $id)
    {
        // **FIX**: Removed call to non-existent function
        try {
            $user = DB::table('users')->where('user_id', $id)->first();
            if ($user) {
                $newStatus = $user->status === 'active' ? 'inactive' : 'active';
                DB::table('users')->where('user_id', $id)->update(['status' => $newStatus]);
                $result = ['success' => true, 'message' => 'User status toggled successfully.'];
            } else {
                $result = ['success' => false, 'message' => 'User not found.'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }
        return response()->json($result);
    }

    // ******* THIS IS THE FUNCTION WITH THE FIX *******
    public function changeUserPassword(Request $request, $id)
    {
        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $result = [];
        try {
            // **FIX**: Instead of DB::select, we use DB::table()->update()
            $updated = DB::table('users')
                ->where('user_id', $id)
                ->update([
                    'password' => Hash::make($data['password']), // Use Hash::make
                    'updated_at' => now()
                ]);

            if ($updated) {
                $result = ['success' => true, 'message' => 'Password updated successfully.'];
            } else {
                $result = ['success' => false, 'message' => 'User not found.'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }
        
        // This part is correct and handles the redirect
        if ($request->expectsJson()) {
            return response()->json($result);
        } else {
            return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
        }
    }

    public function deleteUser($id)
    {
         // **FIX**: Removed call to non-existent function
        $result = [];
        try {
            // You should add checks here (e.g., cannot delete user with transactions)
            // For now, we just perform the delete.
            $deleted = DB::table('users')->where('user_id', $id)->delete();
            if ($deleted) {
                $result = ['success' => true, 'message' => 'User deleted successfully.'];
            } else {
                $result = ['success' => false, 'message' => 'User not found.'];
            }
        } catch (\Exception $e) {
             // Handle foreign key constraints, etc.
            $result = ['success' => false, 'message' => 'Cannot delete this user. They may be linked to other records.'];
        }
        return response()->json($result);
    }

    // --- Notification compose page ---
    public function composeNotificationPage()
    {
        $users = DB::table('users')->select('user_id', 'name')->orderBy('name')->get();
        // **FIX**: The view path must match your file structure.
        return view('Admin.Notification.compose', compact('users'));
    }

    // --- Reports ---
    public function generateReport(Request $request, $report)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $format = $request->get('format', 'web');

        $data = collect();
        $title = '';
        $chart = null;

        switch ($report) {
            case 'user-activity':
                $title = 'User Activity Report';
                $data = DB::table('users')
                    ->select('name', 'email', 'status', 'created_at', 'updated_at')
                    ->whereBetween('created_at', [$start, $end])
                    ->get();
                break;
            
            case 'item-requests':
                $title = 'Item Requests Report';
                $data = DB::table('item_requests as ir')
                    ->leftJoin('users as u1', 'u1.user_id', '=', 'ir.requested_by')
                    ->leftJoin('users as u2', 'u2.user_id', '=', 'ir.approved_by')
                    ->select('ir.item_req_name', 'ir.item_req_unit', 'ir.item_req_quantity', 'ir.item_req_status', 'u1.name as requester', 'u2.name as approver', 'ir.created_at')
                    ->whereBetween('ir.created_at', [$start, $end])
                    ->get();
                break;

            case 'requisitions':
                $title = 'Requisition History Report';
                $data = DB::table('requisitions as r')
                    ->leftJoin('users as u1', 'u1.user_id', '=', 'r.requested_by')
                    ->leftJoin('users as u2', 'u2.user_id', '=', 'r.approved_by')
                    ->select('r.req_ref', 'r.req_purpose', 'r.req_status', 'u1.name as requester', 'u2.name as approver', 'r.req_date', 'r.approved_date')
                    ->whereBetween('r.req_date', [$start, $end])
                    ->get();
                break;
            
            case 'purchase-orders':
                $title = 'Purchase Order Report';
                $data = DB::table('purchase_orders as po')
                    ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
                    ->select('po.po_ref', 's.sup_name', 'po.po_status', 'po.order_date', 'po.expected_delivery_date', 'po.total_amount')
                    ->whereBetween('po.order_date', [$start, $end])
                    ->get();
                break;

            case 'inventory-movements':
                $title = 'Inventory Movement Report';
                $data = DB::table('inventory_transactions as t')
                    ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
                    ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
                    ->select('t.trans_date', 'i.item_name', 't.trans_type', 't.trans_quantity', 'i.item_unit', 'u.name as user', 't.trans_remarks')
                    ->whereBetween('t.trans_date', [$start, $end])
                    ->orderBy('t.trans_date', 'desc')
                    ->get();
                break;

            case 'expiry-low-stock':
                $title = 'Expiry & Low-Stock Report';
                
                 $low = DB::table('items')
                    ->whereColumn('item_stock', '<=', 'reorder_level')
                    ->where('item_stock', '>', 0) 
                    ->select('item_name', 'item_code', 'item_stock', 'reorder_level', 'item_unit')
                    ->get();
                
                $exp = DB::table('items')
                    ->whereNotNull('item_expire_date')
                    ->whereBetween('item_expire_date', [$start, $end])
                    ->select('item_name', 'item_code', 'item_stock', 'item_expire_date')
                    ->get();

                $data = [
                    'low_stock' => $low,
                    'expiry' => $exp
                ];
                break;
            
            case 'supplier-performance':
                $title = 'Supplier Performance Report';
                $data = DB::table('suppliers as s')
                    ->leftJoin('purchase_orders as po', 'po.sup_id', '=', 's.sup_id')
                    ->select('s.sup_name', 's.sup_status', DB::raw('COUNT(po.po_id) as total_orders'), DB::raw('SUM(po.total_amount) as total_value'))
                    ->whereBetween('po.order_date', [$start, $end])
                    ->groupBy('s.sup_name', 's.sup_status')
                    ->orderBy('total_value', 'desc')
                    ->get();
                break;

            case 'weekly-stock-in':
                $title = 'Stock-In Summary by Item';
                $data = DB::table('inventory_transactions as t')
                    ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
                    ->select('i.item_name', DB::raw('SUM(t.trans_quantity) as total_quantity_in'))
                    ->where('t.trans_type', 'in')
                    ->whereBetween('t.trans_date', [$start, $end])
                    ->groupBy('i.item_name')
                    ->orderBy('total_quantity_in', 'desc')
                    ->get();
                break;
            
            case 'negative-stock':
                $title = 'Negative Stock Report';
                $data = DB::table('items')
                    ->select('item_name', 'item_code', 'item_stock', 'item_unit')
                    ->where('item_stock', '<', 0)
                    ->get();
                break;

            case 'ar-issuance':
                $title = 'AR Issuance Report';
                $data = DB::table('acknowledge_receipts as ar')
                    ->leftJoin('users as u1', 'u1.user_id', '=', 'ar.issued_by')
                    ->leftJoin('users as u2', 'u2.user_id', '=', 'ar.issued_to')
                    ->leftJoin('requisitions as r', 'r.req_id', '=', 'ar.req_id')
                    ->select('ar.ar_ref', 'r.req_ref', 'u1.name as issued_by', 'u2.name as issued_to', 'ar.issued_date', 'ar.ar_status')
                    ->whereBetween('ar.issued_date', [$start, $end])
                    ->get();
                break;

            default:
                if ($format === 'web') {
                    return response()->json(['error' => 'Report not found or not yet implemented.'], 404);
                } else {
                    abort(404, 'Report not found.');
                }
        } // End switch

        
        // --- Generic Render Block ---

        $isEmpty = false;
        if (is_array($data)) {
            $isEmpty = collect($data['low_stock'])->isEmpty() && collect($data['expiry'])->isEmpty();
        } else {
            $isEmpty = $data->isEmpty();
        }

        if ($isEmpty) {
             if ($format === 'web') {
                return response()->json([
                    'title' => $title, 
                    'html' => '<p class="text-gray-500 text-center py-8">No data found for the selected criteria.</p>', 
                    'empty' => true
                ]);
             } else {
                $title = $title ?? 'Report';
                $html = "<h1>$title</h1><p>No data found for the selected criteria from $start to $end.</p>";
                $pdf = Pdf::loadHTML($html);
                return $pdf->download(Str::slug($title) . '.pdf');
             }
        }
        
        if ($format === 'pdf') {
            // **FIX**: The view path must match your file structure.
            $pdf = Pdf::loadView('Admin.Report.pdf', compact('title', 'report', 'start', 'end', 'data'));
            return $pdf->setPaper('a4', 'landscape')->download(Str::slug($title) . '.pdf');
        
        } elseif ($format === 'csv') {
            $dataToCsv = is_array($data) ? $data['low_stock'] : $data; 
            
            if ($dataToCsv->isEmpty()) {
                 return response("No data for CSV export.", 200, ['Content-Type' => 'text/csv']);
            }
            
            $headers = array_keys((array)$dataToCsv->first());
            $csv = implode(',', $headers) . "\n";
            foreach ($dataToCsv as $row) {
                 $csv .= implode(',', array_map(function($val) {
                    return '"' . str_replace('"', '""', $val) . '"';
                 }, array_values((array)$row))) . "\n";
            }
            
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . Str::slug($title) . '.csv"',
            ]);

        } else {
            // Web / JSON response
            $html = "";
            if(is_array($data)) {
                 if(isset($data['low_stock']) && $data['low_stock']->isNotEmpty()) {
                    $html .= "<h3>Low-Stock Items</h3>";
                    // **FIX**: The view path must match your file structure.
                    $html .= view('Admin.Report.table', ['data' => $data['low_stock']])->render();
                 }
                 if(isset($data['expiry']) && $data['expiry']->isNotEmpty()) {
                    $html .= "<h3 class='mt-6'>Expiry Alerts</h3>";
                    // **FIX**: The view path must match your file structure.
                    $html .= view('Admin.Report.table', ['data' => $data['expiry']])->render();
                 }
            } else {
                 // **FIX**: The view path must match your file structure.
                 $html = view('Admin.Report.table', compact('data'))->render();
            }

            return response()->json(['title' => $title, 'html' => $html, 'chart' => $chart]);
        }
    }
}