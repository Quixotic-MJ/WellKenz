<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    /**
     * Show the supervisor dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Pending requisitions count
        $pendingReqs = DB::table('requisitions')->where('req_status', 'pending')->count();

        // Pending item requests count
        $pendingItemReqs = DB::table('item_requests')->where('item_req_status', 'pending')->count();

        // Low-stock items
        $lowStock = DB::table('items')
            ->whereColumn('item_stock', '<=', 'reorder_level')
            ->select('item_name', 'item_stock as current_stock', 'reorder_level', 'item_unit as unit')
            ->get();

        // Expiry alerts (30 days)
        $expiry = DB::table('items')
            ->whereNotNull('item_expire_date')
            ->whereRaw("item_expire_date <= CURRENT_DATE + INTERVAL '30 day'")
            ->select('item_name', 'item_expire_date as expiry_date')
            ->get();

        // Pending requisitions list (latest 5)
        $pendingReqsList = DB::table('requisitions as r')
            ->leftJoin('users as u', 'u.user_id', '=', 'r.requested_by')
            ->where('r.req_status', 'pending')
            ->select('r.req_id as requisition_id', 'r.req_ref', 'r.req_purpose', 'r.req_priority', 'r.created_at')
            ->orderByDesc('r.created_at')
            ->limit(5)
            ->get()
            ->map(function($r) {
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                return $r;
            });

        // Pending item requests list (latest 5)
        $pendingItemReqsList = DB::table('item_requests')
            ->where('item_req_status', 'pending')
            ->select('item_req_id as item_request_id', 'item_req_name', 'item_req_unit', 'item_req_quantity', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($ir) {
                $ir->created_at = \Carbon\Carbon::parse($ir->created_at);
                return $ir;
            });

        // Recent notifications
        $recentNotifications = DB::table('notifications')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($n) {
                $n->created_at = \Carbon\Carbon::parse($n->created_at);
                return $n;
            });

        // This month's approval stats
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();

        $approvedThisMonth = DB::table('requisitions')
            ->where('req_status', 'approved')
            ->whereBetween('approved_date', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $rejectedThisMonth = DB::table('requisitions')
            ->where('req_status', 'rejected')
            ->whereBetween('updated_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $totalDecisions = $approvedThisMonth + $rejectedThisMonth;
        $approvalRatio = $totalDecisions > 0 ? ($approvedThisMonth / $totalDecisions) * 100 : 0;

        // This month's acknowledgement stats
        $issuedAckThisMonth = DB::table('acknowledge_receipts')
            ->whereBetween('issued_date', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $receivedAckThisMonth = DB::table('acknowledge_receipts')
            ->where('ar_status', 'received')
            ->whereBetween('updated_at', [$thisMonthStart, $thisMonthEnd])
            ->count();

        $ackReceiptRatio = $issuedAckThisMonth > 0 ? ($receivedAckThisMonth / $issuedAckThisMonth) * 100 : 0;

        return view('Supervisor.Dashboard.dashboard', compact(
            'pendingReqs', 'pendingItemReqs', 'lowStock', 'expiry',
            'pendingReqsList', 'pendingItemReqsList', 'recentNotifications',
            'approvedThisMonth', 'rejectedThisMonth', 'approvalRatio',
            'issuedAckThisMonth', 'receivedAckThisMonth', 'ackReceiptRatio'
        ));
    }

    // ===== Requisitions Review =====
    public function requisitionsIndex()
    {
        // counts
        $pendingCount  = DB::table('requisitions')->where('req_status','pending')->count();
        $approvedCount = DB::table('requisitions')->where('req_status','approved')->count();
        $rejectedCount = DB::table('requisitions')->where('req_status','rejected')->count();
        $thisMonthCount= DB::table('requisitions')->whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->count();

        // pending list
        $pendingList = DB::table('requisitions as r')
            ->leftJoin('users as u','u.user_id','=','r.requested_by')
            ->select('r.*','u.name as requester_name')
            ->where('r.req_status','pending')
            ->orderByDesc('r.created_at')
            ->get()
            ->map(function($r){
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                $r->requester = (object)['name'=>$r->requester_name];
                $r->items = collect(range(1, (int)(DB::table('requisition_items')->where('req_id',$r->req_id)->count())));
                return $r;
            });

        // past decisions
        $pastList = DB::table('requisitions as r')
            ->leftJoin('users as req','req.user_id','=','r.requested_by')
            ->leftJoin('users as app','app.user_id','=','r.approved_by')
            ->select('r.*','req.name as requester_name','app.name as approver_name')
            ->whereIn('r.req_status',['approved','rejected','completed'])
            ->orderByDesc('r.updated_at')
            ->limit(100)
            ->get()
            ->map(function($r){
                $r->updated_at = \Carbon\Carbon::parse($r->updated_at);
                $r->requester = (object)['name'=>$r->requester_name];
                $r->approver  = (object)['name'=>$r->approver_name];
                return $r;
            });

        return view('Supervisor.Requisition.requisition', compact('pendingCount','approvedCount','rejectedCount','thisMonthCount','pendingList','pastList'));
    }

    public function requisitionsShow(Request $request, $id)
    {
        $req = DB::table('requisitions as r')
            ->leftJoin('users as reqr','reqr.user_id','=','r.requested_by')
            ->leftJoin('users as appr','appr.user_id','=','r.approved_by')
            ->where('r.req_id',$id)
            ->select('r.*','reqr.name as requester_name','appr.name as approver_name')
            ->first();
        if(!$req) abort(404);
        $items = DB::table('requisition_items as ri')
            ->leftJoin('items as i','i.item_id','=','ri.item_id')
            ->leftJoin('categories as c','c.cat_id','=','i.cat_id')
            ->where('ri.req_id',$id)
            ->select('ri.req_item_quantity','ri.item_unit','i.item_name','i.item_description','c.cat_name')
            ->get()
            ->map(function($item){
                $item->category = (object)['category_name' => $item->cat_name];
                return $item;
            });
        $req->requester = (object)['name'=>$req->requester_name];
        $req->approver = $req->approver_name ? (object)['name'=>$req->approver_name] : null;
        $req->items = $items;

        if ($request->expectsJson()) {
            return response()->json([
                'req_ref' => $req->req_ref,
                'requester' => $req->requester,
                'req_priority' => $req->req_priority,
                'req_status' => $req->req_status,
                'req_purpose' => $req->req_purpose,
                'items' => $req->items,
                'req_reject_reason' => $req->req_reject_reason,
                'approver' => $req->approver,
                'created_at' => $req->created_at,
                'updated_at' => $req->updated_at,
            ]);
        }

        return view('Supervisor.Requisition.review', compact('req'));
    }

    public function requisitionsUpdateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'req_status' => 'required|in:approved,rejected',
            'req_reject_reason' => 'nullable|string',
        ]);
        $row = DB::table('requisitions')->where('req_id',$id)->where('req_status','pending')->first();
        if(!$row) return back()->with('error','Invalid or non-pending requisition');
        $update = [
            'req_status' => $data['req_status'],
            'approved_by'=> Auth::id(),
            'updated_at' => now(),
        ];
        if($data['req_status']==='rejected'){
            $update['req_reject_reason'] = $data['req_reject_reason'] ?? 'No reason provided';
        } else {
            $update['req_reject_reason'] = null;
            $update['approved_date'] = now();
        }
        DB::table('requisitions')->where('req_id',$id)->update($update);
        return redirect()->route('supervisor.requisitions.index')->with('success','Decision recorded');
    }

    // ===== Item Requests Approvals =====
    public function itemRequestsIndex()
    {
        $pendingCount   = DB::table('item_requests')->where('item_req_status','pending')->count();
        $approvedCount  = DB::table('item_requests')->where('item_req_status','approved')->count();
        $rejectedCount  = DB::table('item_requests')->where('item_req_status','rejected')->count();
        $thisMonthCount = DB::table('item_requests')->whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->count();

        $pendingList = DB::table('item_requests as ir')
            ->leftJoin('users as u','u.user_id','=','ir.requested_by')
            ->select('ir.*','u.name as requester_name')
            ->where('ir.item_req_status','pending')
            ->orderByDesc('ir.created_at')
            ->get()
            ->map(function($r){ $r->created_at = \Carbon\Carbon::parse($r->created_at); $r->requester=(object)['name'=>$r->requester_name]; return $r; });

        $pastList = DB::table('item_requests as ir')
            ->leftJoin('users as u','u.user_id','=','ir.requested_by')
            ->select('ir.*','u.name as requester_name')
            ->whereIn('ir.item_req_status',['approved','rejected'])
            ->orderByDesc('ir.updated_at')
            ->limit(100)
            ->get()
            ->map(function($r){ $r->updated_at = \Carbon\Carbon::parse($r->updated_at); $r->requester=(object)['name'=>$r->requester_name]; return $r; });

        return view('Supervisor.Item_Request.item_request', compact('pendingCount','approvedCount','rejectedCount','thisMonthCount','pendingList','pastList'));
    }

    public function itemRequestsShow($id)
    {
        $req = DB::table('item_requests as ir')
            ->leftJoin('users as u1','u1.user_id','=','ir.requested_by')
            ->leftJoin('users as u2','u2.user_id','=','ir.approved_by')
            ->where('ir.item_req_id',$id)
            ->select('ir.*','u1.name as requester_name','u2.name as approver_name')
            ->first();
        if(!$req) return response()->json(['error'=>'Not found'],404);
        return response()->json([
            'item_req_name' => $req->item_req_name,
            'item_req_unit' => $req->item_req_unit,
            'item_req_quantity' => $req->item_req_quantity,
            'item_req_status' => $req->item_req_status,
            'item_req_description' => $req->item_req_description,
            'item_req_reject_reason' => $req->item_req_reject_reason ?? null,
            'requester' => ['name'=>$req->requester_name],
            'approver' => $req->approver_name ? ['name'=>$req->approver_name] : null,
            'created_at' => $req->created_at,
            'updated_at' => $req->updated_at,
        ]);
    }

    public function itemRequestsUpdateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'item_req_status' => 'required|in:approved,rejected',
            'item_req_reject_reason' => 'nullable|string',
        ]);
        $exists = DB::table('item_requests')->where('item_req_id',$id)->where('item_req_status','pending')->exists();
        if(!$exists){
            return $request->expectsJson()
                ? response()->json(['success'=>false,'message'=>'Invalid or non-pending request'],422)
                : back()->with('error','Invalid or non-pending request');
        }
        $update = [
            'item_req_status'=>$data['item_req_status'],
            'approved_by'=>Auth::id(),
            'updated_at'=>now(),
        ];
        if(($data['item_req_status']??'')==='rejected'){
            $update['item_req_reject_reason'] = $data['item_req_reject_reason'] ?? 'No reason provided';
        } else {
            $update['item_req_reject_reason'] = null;
        }
        DB::table('item_requests')->where('item_req_id',$id)->update($update);

        if ($request->expectsJson()) {
            return response()->json(['success'=>true,'message'=>'Decision recorded']);
        }
        return redirect()->route('supervisor.item-requests.index')->with('success','Decision recorded');
    }

    // ===== Inventory Overview =====
    public function inventoryOverview()
    {
        $items = DB::table('items as i')
            ->leftJoin('categories as c','c.cat_id','=','i.cat_id')
            ->select('i.*','c.cat_name')
            ->orderBy('i.item_name')
            ->get()
            ->map(function($item){
                $item->category = (object)['cat_name' => $item->cat_name];
                return $item;
            });

        $totalItems = $items->count();
        $lowStockCount = $items->where('item_stock', '<=', 'reorder_level')->count();
        $expiringCount = $items->whereNotNull('item_expire_date')
            ->where('item_expire_date', '<=', now()->addDays(30)->toDateString())
            ->count();
        $zeroStockCount = $items->where('item_stock', 0)->count();

        $lowStockTop = $items->where('item_stock', '<=', 'reorder_level')->take(5);
        $expiryTop = $items->whereNotNull('item_expire_date')
            ->where('item_expire_date', '<=', now()->addDays(30)->toDateString())
            ->sortBy('item_expire_date')
            ->take(5);

        return view('Supervisor.Inventory.inventory_overview', compact('items','totalItems','lowStockCount','expiringCount','zeroStockCount','lowStockTop','expiryTop'));
    }

    public function showItem($id)
    {
        $item = DB::table('items as i')
            ->leftJoin('categories as c','c.cat_id','=','i.cat_id')
            ->where('i.item_id', $id)
            ->select('i.*','c.cat_name')
            ->first();
        if (!$item) return response()->json(['error' => 'Not found'], 404);
        return response()->json($item);
    }

    // ===== Purchase Orders =====
    public function purchaseOrdersIndex()
    {
        $purchaseOrders = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->leftJoin('requisitions as r','r.req_id','=','po.req_id')
            ->select('po.*','s.sup_name','r.req_ref')
            ->orderByDesc('po.created_at')
            ->get()
            ->map(function($po){
                $po->supplier = (object)['sup_name' => $po->sup_name];
                $po->requisition = (object)['req_ref' => $po->req_ref];
                return $po;
            });
        $totalPOs = $purchaseOrders->count();
        $draftCount = $purchaseOrders->where('po_status', 'draft')->count();
        $orderedCount = $purchaseOrders->where('po_status', 'ordered')->count();
        $deliveredCount = $purchaseOrders->where('po_status', 'delivered')->count();
        $cancelledCount = $purchaseOrders->where('po_status', 'cancelled')->count();
        return view('Supervisor.Purchase.purchase_order', compact('purchaseOrders','totalPOs','draftCount','orderedCount','deliveredCount','cancelledCount'));
    }
    public function poApprove($id)
    {
        DB::table('purchase_orders')->where('po_id',$id)->update(['po_status'=>'approved','approved_by'=>Auth::id(),'updated_at'=>now()]);
        return back()->with('success','PO approved');
    }
    public function poReject(Request $request, $id)
    {
        DB::table('purchase_orders')->where('po_id',$id)->update(['po_status'=>'rejected','approved_by'=>Auth::id(),'updated_at'=>now()]);
        return back()->with('success','PO rejected');
    }

    // ===== Notifications =====
    public function notificationsIndex()
    {
        $notifications = DB::table('notifications')->orderByDesc('created_at')->paginate(20);
        $totalCount = DB::table('notifications')->count();
        $unreadCount = DB::table('notifications')->where('is_read', false)->count();
        $readCount = DB::table('notifications')->where('is_read', true)->count();
        $todayCount = DB::table('notifications')->whereDate('created_at', today())->count();
        return view('Supervisor.Notification.notification', compact('notifications','totalCount','unreadCount','readCount','todayCount'));
    }
    public function notificationsMarkAllRead()
    {
        DB::table('notifications')->update(['is_read'=>true]);
        return response()->json(['success'=>true]);
    }

    // ===== Reports =====
    public function reportsIndex()
    {
        $transactions = DB::table('inventory_transactions as t')
            ->leftJoin('items as i','i.item_id','=','t.item_id')
            ->leftJoin('users as u','u.user_id','=','t.trans_by')
            ->select('t.*','i.item_name','u.name as user_name')
            ->orderByDesc('t.created_at')
            ->limit(50)
            ->get()
            ->map(function($row){
                $row->item  = (object)['item_name' => $row->item_name];
                $row->user  = (object)['name' => $row->user_name];
                $row->quantity = $row->trans_quantity;
                return $row;
            });

        return view('Supervisor.Report.report', compact('transactions'));
    }
    public function printRequisition(){ return view('Supervisor.Report.print_requisition'); }
    public function printItemRequest(){ return view('Supervisor.Report.print_item_request'); }
    public function printPurchaseOrder(){ return view('Supervisor.Report.print_purchase_order'); }
    public function printInventoryHealth(){ return view('Supervisor.Report.print_inventory_health'); }
}