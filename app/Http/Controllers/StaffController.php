<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    /**
     * Show the staff (employee) dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(){
        $userId = Auth::id();

        $user = DB::table('users')->where('user_id', $userId)->first();
        $userName = $user->name ?? ($user->username ?? 'User');

        // Personal requisition metrics
        $myReqTotal    = DB::table('requisitions')->where('requested_by', $userId)->count();
        $myReqPending  = DB::table('requisitions')->where('requested_by', $userId)->where('req_status','pending')->count();
        $myReqApproved = DB::table('requisitions')->where('requested_by', $userId)->where('req_status','approved')->count();
        $myReqRejected = DB::table('requisitions')->where('requested_by', $userId)->where('req_status','rejected')->count();

        // Item requests awaiting supervisor approval (for this user)
        $pendingItems = DB::table('item_requests')
            ->where('requested_by', $userId)
            ->where('item_req_status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Personal notifications (latest 6)
        $notifs = DB::table('notifications')
            ->where(function($q) use ($userId){ $q->whereNull('user_id')->orWhere('user_id',$userId); })
            ->orderByDesc('created_at')
            ->limit(6)->get();

        // Low stock alerts (read-only preview)
        $lowStock = DB::table('items')
            ->whereColumn('item_stock','<=','reorder_level')
            ->orderBy('item_name')
            ->limit(5)->get();

        // Recent requisitions for this user
        $recReqs = DB::table('requisitions')
            ->where('requested_by', $userId)
            ->orderByDesc('created_at')
            ->limit(6)->get();

        return view('Employee.dashboard', compact(
            'userName','myReqTotal','myReqPending','myReqApproved','myReqRejected','pendingItems','notifs','lowStock','recReqs'
        ));
    }

    // ===== Requisitions (Employee) =====
    public function requisitionsIndex()
    {
        $userId = Auth::id();
        $totalCount    = DB::table('requisitions')->where('requested_by',$userId)->count();
        $pendingCount  = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','pending')->count();
        $approvedCount = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','approved')->count();
        $rejectedCount = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','rejected')->count();

        $requisitions = DB::table('requisitions as r')
            ->where('r.requested_by', $userId)
            ->select('r.*', DB::raw('(SELECT COUNT(*) FROM requisition_items ri WHERE ri.req_id = r.req_id) as items_count'))
            ->orderByDesc('r.created_at')
            ->get()
            ->map(function($r){
                $r->items = collect(array_fill(0, (int)($r->items_count ?? 0), 1));
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                return $r;
            });

        return view('Employee.Requisition.my_requisition', compact('totalCount','pendingCount','approvedCount','rejectedCount','requisitions'));
    }

    public function requisitionsCreate()
    {
        $userId = Auth::id();
        $items = DB::table('items')->select('item_id','item_name','item_unit','item_stock')->orderBy('item_name')->get();

        // Requisitions data for history table
        $reqTotal    = DB::table('requisitions')->where('requested_by',$userId)->count();
        $reqPending  = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','pending')->count();
        $reqApproved = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','approved')->count();
        $reqRejected = DB::table('requisitions')->where('requested_by',$userId)->where('req_status','rejected')->count();

        $requisitions = DB::table('requisitions as r')
            ->where('r.requested_by', $userId)
            ->select('r.*')
            ->orderByDesc('r.created_at')
            ->get()
            ->map(function($r){
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                return $r;
            });

        return view('Employee.Requisition.create_requisition', compact('items','reqTotal','reqPending','reqApproved','reqRejected','requisitions'));
    }

    public function requisitionsStore(Request $request)
    {
        $data = $request->validate([
            'req_purpose' => 'required|string|max:255',
            'req_priority'=> 'required|in:low,medium,high',
            'items'       => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $reqId = DB::table('requisitions')->insertGetId([
            'req_ref' => 'RQ-'.str_pad((string)((DB::table('requisitions')->max('req_id') ?? 0)+1), 5, '0', STR_PAD_LEFT),
            'req_purpose' => $data['req_purpose'],
            'req_priority'=> $data['req_priority'],
            'req_status'  => 'pending',
            'req_date'    => now()->toDateString(),
            'requested_by'=> $userId,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], 'req_id');

        foreach ($data['items'] as $row) {
            $item = DB::table('items')->where('item_id', $row['item_id'])->first();
            DB::table('requisition_items')->insert([
                'req_id' => $reqId,
                'item_id'=> $row['item_id'],
                'req_item_quantity' => $row['quantity'],
                'item_unit' => $item->item_unit ?? 'unit',
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);
        }

        $message = 'Requisition submitted successfully';
        return $request->expectsJson()
            ? response()->json(['success'=>true,'message'=>$message])
            : redirect()->route('staff.requisitions.index')->with('success',$message);
    }

    public function requisitionsShow($id)
    {
        $userId = Auth::id();
        $req = DB::table('requisitions')->where('req_id', $id)->where('requested_by',$userId)->first();
        if (!$req) return response()->json(['error' => 'Not found'], 404);
        $items = DB::table('requisition_items as ri')
            ->join('items as i','i.item_id','=','ri.item_id')
            ->where('ri.req_id', $id)
            ->select('ri.req_item_quantity','ri.item_unit','i.item_name')
            ->get()
            ->map(fn($r)=> [
                'item' => ['item_name' => $r->item_name],
                'req_item_quantity' => $r->req_item_quantity,
                'item_unit' => $r->item_unit,
            ]);
        return response()->json([
            'req_ref' => $req->req_ref,
            'req_status' => $req->req_status,
            'req_priority' => $req->req_priority,
            'created_at' => $req->created_at,
            'req_purpose' => $req->req_purpose,
            'req_reject_reason' => $req->req_reject_reason ?? null,
            'items' => $items,
        ]);
    }

    public function requisitionsEdit($id)
    {
        $userId = Auth::id();
        $requisition = DB::table('requisitions')->where('req_id',$id)->where('requested_by',$userId)->first();
        if (!$requisition) abort(404);
        $items = DB::table('items')->select('item_id','item_name','item_unit','item_stock')->orderBy('item_name')->get();
        // Attach items list for this requisition
        $reqItems = DB::table('requisition_items as ri')
            ->join('items as i','i.item_id','=','ri.item_id')
            ->where('ri.req_id',$id)
            ->select('ri.*','i.item_unit','i.item_stock','i.item_name')
            ->get()
            ->map(function($r){
                $r->item = (object)['item_unit'=>$r->item_unit,'item_stock'=>$r->item_stock,'item_name'=>$r->item_name];
                return $r;
            });
        $requisition = (object) array_merge((array)$requisition, ['items'=>$reqItems]);
        return view('Employee.Requisition.edit', compact('requisition','items'));
    }

    public function requisitionsUpdate(Request $request, $id)
    {
        $userId = Auth::id();
        $exists = DB::table('requisitions')->where('req_id',$id)->where('requested_by',$userId)->where('req_status','pending')->exists();
        if (!$exists) return back()->with('error','Cannot update this requisition');

        $data = $request->validate([
            'req_purpose' => 'required|string|max:255',
            'req_priority'=> 'required|in:low,medium,high',
            'items'       => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        DB::table('requisitions')->where('req_id',$id)->update([
            'req_purpose' => $data['req_purpose'],
            'req_priority'=> $data['req_priority'],
            'updated_at'  => now(),
        ]);
        // reset items
        DB::table('requisition_items')->where('req_id',$id)->delete();
        foreach ($data['items'] as $row) {
            $item = DB::table('items')->where('item_id', $row['item_id'])->first();
            DB::table('requisition_items')->insert([
                'req_id' => $id,
                'item_id'=> $row['item_id'],
                'req_item_quantity' => $row['quantity'],
                'item_unit' => $item->item_unit ?? 'unit',
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);
        }

        return redirect()->route('staff.requisitions.index')->with('success','Requisition updated');
    }

    public function requisitionsDestroy(Request $request, $id)
    {
        $userId = Auth::id();
        $exists = DB::table('requisitions')
            ->where('req_id',$id)
            ->where('requested_by',$userId)
            ->where('req_status','pending')
            ->exists();

        if (!$exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete this requisition'], 422);
            }
            return back()->with('error','Cannot delete this requisition');
        }

        DB::table('requisition_items')->where('req_id',$id)->delete();
        DB::table('requisitions')->where('req_id',$id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('staff.requisitions.index')->with('success','Requisition cancelled');
    }

    // ===== Item Requests (Employee) =====
    public function itemRequestsIndex()
    {
        $userId = Auth::id();
        $totalCount   = DB::table('item_requests')->where('requested_by',$userId)->count();
        $pendingCount = DB::table('item_requests')->where('requested_by',$userId)->where('item_req_status','pending')->count();
        $approvedCount= DB::table('item_requests')->where('requested_by',$userId)->where('item_req_status','approved')->count();
        $rejectedCount= DB::table('item_requests')->where('requested_by',$userId)->where('item_req_status','rejected')->count();

        $requests = DB::table('item_requests')
            ->where('requested_by',$userId)
            ->orderByDesc('created_at')->get()
            ->map(function($r){ $r->created_at = \Carbon\Carbon::parse($r->created_at); return $r; });

        return view('Employee.Item_Request.item_request', compact('totalCount','pendingCount','approvedCount','rejectedCount','requests'));
    }

    public function itemRequestsStore(Request $request)
    {
        $data = $request->validate([
            'item_req_name' => 'required|string|max:255',
            'item_req_unit' => 'required|string|max:255',
            'item_req_quantity' => 'required|integer|min:1',
            'item_req_description' => 'required|string',
        ]);
        DB::table('item_requests')->insert([
            'item_req_name' => $data['item_req_name'],
            'item_req_unit' => $data['item_req_unit'],
            'item_req_quantity' => $data['item_req_quantity'],
            'item_req_description' => $data['item_req_description'],
            'item_req_status' => 'pending',
            'requested_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->back()->with('success','Request submitted');
    }

    public function itemRequestsShow($id)
    {
        $userId = Auth::id();
        $req = DB::table('item_requests as ir')
            ->leftJoin('users as u1','u1.user_id','=','ir.requested_by')
            ->leftJoin('users as u2','u2.user_id','=','ir.approved_by')
            ->where('ir.item_req_id',$id)
            ->where('ir.requested_by',$userId)
            ->select('ir.*','u1.name as requester_name','u2.name as approver_name')
            ->first();
        if (!$req) return response()->json(['error'=>'Not found'],404);
        return response()->json([
            'item_req_name' => $req->item_req_name,
            'item_req_unit' => $req->item_req_unit,
            'item_req_quantity' => $req->item_req_quantity,
            'item_req_status' => $req->item_req_status,
            'item_req_description' => $req->item_req_description,
            'item_req_reject_reason' => $req->item_req_reject_reason ?? null,
            'requester' => ['name' => $req->requester_name],
            'approver' => $req->approver_name ? ['name'=>$req->approver_name] : null,
            'created_at' => $req->created_at,
            'updated_at' => $req->updated_at,
        ]);
    }

    public function itemRequestsEdit($id)
    {
        $userId = Auth::id();
        $requestRow = DB::table('item_requests')->where('item_req_id',$id)->where('requested_by',$userId)->first();
        if (!$requestRow) abort(404);
        return view('Employee.Item_Request.edit', ['request' => $requestRow]);
    }

    public function itemRequestsUpdate(Request $request, $id)
    {
        $userId = Auth::id();
        $exists = DB::table('item_requests')->where('item_req_id',$id)->where('requested_by',$userId)->where('item_req_status','pending')->exists();
        if (!$exists) return back()->with('error','Cannot update this request');
        $data = $request->validate([
            'item_req_name' => 'required|string|max:255',
            'item_req_unit' => 'required|string|max:255',
            'item_req_quantity' => 'required|integer|min:1',
            'item_req_description' => 'required|string',
        ]);
        DB::table('item_requests')->where('item_req_id',$id)->update(array_merge($data,[
            'updated_at'=>now(),
        ]));
        return redirect()->route('staff.item-requests.index')->with('success','Request updated');
    }

    public function itemRequestsCancel(Request $request)
    {
        $data = $request->validate(['id'=>'required|integer']);
        $userId = Auth::id();
        $updated = DB::table('item_requests')
            ->where('item_req_id',$data['id'])
            ->where('requested_by',$userId)
            ->where('item_req_status','pending')
            ->update(['item_req_status'=>'cancelled','updated_at'=>now()]);
        return redirect()->back()->with($updated? 'success' : 'error', $updated? 'Request cancelled' : 'Unable to cancel');
    }

    // ===== AR (Acknowledgement Receipts) =====
    public function arIndex()
    {
        $userId = Auth::id();
        $receipts = DB::table('acknowledge_receipts as ar')
            ->leftJoin('users as u','u.user_id','=','ar.issued_by')
            ->leftJoin('requisitions as r','r.req_id','=','ar.req_id')
            ->where('ar.issued_to', $userId)
            ->select('ar.*','r.req_ref','u.name as issuer_name')
            ->orderByDesc('ar.created_at')
            ->get()
            ->map(function($ar){
                $ar->issued_date = \Carbon\Carbon::parse($ar->issued_date);
                $ar->issuer = (object)['name' => $ar->issuer_name];
                return $ar;
            });

        $totalCount = $receipts->count();
        $issuedCount = $receipts->where('ar_status', 'issued')->count();
        $receivedCount = $receipts->where('ar_status', 'received')->count();
        $thisMonthCount = $receipts->whereBetween('issued_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return view('Employee.AR.acknowledgement_receipt', compact('receipts','totalCount','issuedCount','receivedCount','thisMonthCount'));
    }

    public function arConfirm(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);
        $userId = Auth::id();
        $updated = DB::table('acknowledge_receipts')
            ->where('ar_id', $data['id'])
            ->where('issued_to', $userId)
            ->where('ar_status', 'issued')
            ->update(['ar_status' => 'received', 'updated_at' => now()]);
        return redirect()->back()->with($updated ? 'success' : 'error', $updated ? 'Receipt confirmed successfully' : 'Unable to confirm receipt');
    }

    // ===== Notifications =====
    public function notificationsIndex()
    {
        $userId = Auth::id();
        $notifications = DB::table('notifications')
            ->where(function($q) use ($userId){ $q->whereNull('user_id')->orWhere('user_id',$userId); })
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalCount = $notifications->total();
        $unreadCount = DB::table('notifications')
            ->where(function($q) use ($userId){ $q->whereNull('user_id')->orWhere('user_id',$userId); })
            ->where('is_read', false)
            ->count();
        $readCount = $totalCount - $unreadCount;
        $thisWeekCount = DB::table('notifications')
            ->where(function($q) use ($userId){ $q->whereNull('user_id')->orWhere('user_id',$userId); })
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return view('Employee.Notification.notification', compact('notifications','totalCount','unreadCount','readCount','thisWeekCount'));
    }

    // ===== Notifications =====
    public function notificationsMarkAllRead()
    {
        $userId = Auth::id();
        DB::table('notifications')->where('user_id',$userId)->update(['is_read'=>true]);
        return response()->json(['success'=>true]);
    }

    // ===== Print Requisition =====
    public function printRequisition($id)
    {
        $userId = Auth::id();
        $req = DB::table('requisitions')->where('req_id', $id)->where('requested_by', $userId)->first();
        if (!$req) abort(404);
        $req->created_at = \Carbon\Carbon::parse($req->created_at);
        $items = DB::table('requisition_items as ri')
            ->join('items as i','i.item_id','=','ri.item_id')
            ->where('ri.req_id', $id)
            ->select('ri.req_item_quantity','ri.item_unit','i.item_name')
            ->get();
        return view('Employee.Requisition.print', compact('req','items'));
    }
}