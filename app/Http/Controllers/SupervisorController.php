<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Requisition;
use App\Models\ItemRequest;
use App\Models\Notification;

class SupervisorController extends Controller
{
    /**
     * Supervisor dashboard with KPIs and recent items.
     */
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
            return redirect()->route('login');
        }

        // Pending counts
        $pendingReqs = Requisition::where('req_status', 'pending')->count();
        $pendingItemReqs = ItemRequest::where('item_req_status', 'pending')->count();

        // Pending lists (limit 5)
        $pendingReqsList = Requisition::select('req_id as requisition_id', 'req_ref', 'req_purpose', 'req_priority', 'created_at')
            ->where('req_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingItemReqsList = ItemRequest::select('item_req_id as item_request_id', 'item_req_name', 'item_req_unit', 'item_req_quantity', 'created_at')
            ->where('item_req_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Inventory alerts
        $lowStock = DB::table('items')
            ->select('item_name', 'item_stock as current_stock', 'reorder_level', 'item_unit as unit')
            ->whereNotNull('reorder_level')
            ->whereColumn('item_stock', '<=', 'reorder_level')
            ->orderBy('item_name')
            ->limit(10)
            ->get();

        $expiry = DB::table('items')
            ->select('item_name', 'item_expire_date as expiry_date')
            ->whereNotNull('item_expire_date')
            ->whereBetween('item_expire_date', [Carbon::now(), Carbon::now()->copy()->addDays(30)])
            ->orderBy('item_expire_date')
            ->limit(10)
            ->get();

        // Notifications (recent for current supervisor)
        $recentNotifications = Notification::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Ratios this month
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $approvedThisMonth = Requisition::where('req_status', 'approved')
            ->whereBetween('approved_date', [$startMonth, $endMonth])
            ->count();
        $rejectedThisMonth = Requisition::where('req_status', 'rejected')
            ->whereBetween('approved_date', [$startMonth, $endMonth])
            ->count();
        $totalDecided = $approvedThisMonth + $rejectedThisMonth;
        $approvalRatio = $totalDecided > 0 ? ($approvedThisMonth / $totalDecided) * 100 : 0;

        $issuedAckThisMonth = DB::table('acknowledge_receipts')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->where('ar_status', 'issued')
            ->count();
        $receivedAckThisMonth = DB::table('acknowledge_receipts')
            ->whereBetween('created_at', [$startMonth, $endMonth])
            ->where('ar_status', 'received')
            ->count();
        $ackTotal = $issuedAckThisMonth > 0 ? $issuedAckThisMonth : 0;
        $ackReceiptRatio = $ackTotal > 0 ? ($receivedAckThisMonth / $ackTotal) * 100 : 0;

        return view('Supervisor.Dashboard.dashboard', compact(
            'pendingReqs',
            'pendingItemReqs',
            'lowStock',
            'expiry',
            'pendingReqsList',
            'pendingItemReqsList',
            'recentNotifications',
            'approvedThisMonth',
            'rejectedThisMonth',
            'approvalRatio',
            'issuedAckThisMonth',
            'receivedAckThisMonth',
            'ackReceiptRatio'
        ));
    }
}
