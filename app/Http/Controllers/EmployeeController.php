<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $uid  = $user?->user_id;

        // requisition counters (schema: requested_by, req_status)
        $myReqTotal    = DB::table('requisitions')->where('requested_by', $uid)->count();
        $myReqPending  = DB::table('requisitions')->where('requested_by', $uid)->where('req_status','pending')->count();
        $myReqApproved = DB::table('requisitions')->where('requested_by', $uid)->where('req_status','approved')->count();
        $myReqRejected = DB::table('requisitions')->where('requested_by', $uid)->where('req_status','rejected')->count();

        // pending item requests (schema: item_requests with requested_by, item_req_status)
        $pendingItems = DB::table('item_requests as ir')
            ->where('ir.requested_by', $uid)
            ->where('ir.item_req_status', 'pending')
            ->select('ir.item_req_name as item_name', 'ir.item_req_quantity as quantity', 'ir.created_at')
            ->orderByDesc('ir.created_at')
            ->get();

        // notifications (schema: notifications.user_id)
        $notifs = DB::table('notifications')
            ->where('user_id', $uid)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // low stock (use function when available)
        try {
            $lowStock = collect(DB::select('select * from get_low_stock_items()'))
                ->map(function($r){ $r=(array)$r; return (object)[
                    'item_name' => $r['item_name'] ?? '',
                    'item_stock'=> $r['current_stock'] ?? 0,
                    'item_unit' => $r['item_unit'] ?? '',
                    'reorder_level' => $r['reorder_level'] ?? 0,
                ];});
        } catch (\Throwable $e) {
            $lowStock = DB::table('items')
                ->whereColumn('item_stock','<=','reorder_level')
                ->select('item_name','item_stock','item_unit','reorder_level')
                ->get();
        }
        $lowStock = $lowStock->take(4);

        // recent requisitions of employee
        $recReqs = DB::table('requisitions')
            ->where('requested_by', $uid)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $userName = $user?->name ?? 'Employee';

        return view('Employee.dashboard', compact(
            'userName',
            'myReqTotal','myReqPending','myReqApproved','myReqRejected',
            'pendingItems','notifs','lowStock','recReqs'
        ));
    }

    public function markNotifsRead(Request $request)
    {
        $uid = Auth::user()?->user_id;
        if ($uid) {
            DB::table('notifications')->where('user_id', $uid)->update(['is_read' => true, 'updated_at' => now()]);
        }
        return response()->json(['ok' => true]);
    }
}
