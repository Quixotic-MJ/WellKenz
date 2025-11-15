<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchasingController extends Controller
{
    /**
     * Show the purchasing dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Approved requisitions awaiting PO
        $approvedReqs = DB::table('requisitions')->where('req_status', 'approved')->count();

        // PO statuses
        $draftPOs = DB::table('purchase_orders')->where('po_status', 'draft')->count();
        $orderedPOs = DB::table('purchase_orders')->where('po_status', 'ordered')->count();
        $deliveredPOs = DB::table('purchase_orders')->where('po_status', 'delivered')->count();

        // Overdue POs
        $overdue = DB::table('purchase_orders')
            ->where('po_status', 'ordered')
            ->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date', '<', now())
            ->orderBy('expected_delivery_date')
            ->limit(5)
            ->get();

        // Delivered awaiting stock-in
        $awaiting = DB::table('purchase_orders')
            ->where('po_status', 'delivered')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        // Recent POs
        $totalPOs = DB::table('purchase_orders')->count();
        $recentPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('po.*', 's.sup_name')
            ->orderByDesc('po.created_at')
            ->limit(10)
            ->get()
            ->map(function($po) {
                $po->supplier = (object)['sup_name' => $po->sup_name];
                $po->created_at = \Carbon\Carbon::parse($po->created_at);
                return $po;
            });

        // Supplier snapshot
        $totalSuppliers = DB::table('suppliers')->count();
        $activeSuppliersThisMonth = DB::table('suppliers')
            ->where('sup_status', 'active')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // Top suppliers by quantity and value (simplified)
        $topSupplierQty = DB::table('purchase_orders as po')
            ->join('purchase_items as pi', 'pi.po_id', '=', 'po.po_id')
            ->join('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('s.sup_name', DB::raw('SUM(pi.pi_quantity) as total_qty'))
            ->groupBy('s.sup_id', 's.sup_name')
            ->orderByDesc('total_qty')
            ->first();
        $topSupplierQtyName = $topSupplierQty ? $topSupplierQty->sup_name : null;

        $topSupplierVal = DB::table('purchase_orders as po')
            ->join('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('s.sup_name', DB::raw('SUM(po.total_amount) as total_val'))
            ->groupBy('s.sup_id', 's.sup_name')
            ->orderByDesc('total_val')
            ->first();
        $topSupplierValName = $topSupplierVal ? $topSupplierVal->sup_name : null;

        // Items missing price assignment (draft or ordered POs with null/0 unit price)
        $missingPrices = DB::table('purchase_items as pi')
            ->join('purchase_orders as po','po.po_id','=','pi.po_id')
            ->leftJoin('items as i','i.item_id','=','pi.item_id')
            ->select('po.po_ref','po.po_status','i.item_name','pi.pi_quantity')
            ->whereIn('po.po_status',['draft','ordered'])
            ->where(function($q){ $q->whereNull('pi.pi_unit_price')->orWhere('pi.pi_unit_price','=',0); })
            ->orderByDesc('po.created_at')
            ->limit(5)
            ->get();

        // Recent PO notifications
        $notifs = DB::table('notifications')
            ->where('related_type', 'purchase_order')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($n) {
                $n->created_at = \Carbon\Carbon::parse($n->created_at);
                return $n;
            });

        // Render dashboard (minimal to avoid breaking until full vars are wired)
        return view('Purchasing.dashboard');
    }

    public function notificationsIndex()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(function($n) {
                $n->created_at = \Carbon\Carbon::parse($n->created_at);
                return $n;
            });

        if (view()->exists('Purchasing.Notification.notification')) {
            return view('Purchasing.Notification.notification', compact('notifications'));
        }
        if (view()->exists('Admin.Notification.notification')) {
            return view('Admin.Notification.notification', compact('notifications'));
        }
        return view('Purchasing.dashboard', compact('notifications'));
    }

    public function notificationsView($id)
    {
        $notif = DB::table('notifications')->where('id',$id)->first();
        if(!$notif) abort(404);
        if (is_null($notif->read_at)) {
            DB::table('notifications')->where('id',$id)->update(['read_at'=>now(), 'updated_at'=>now()]);
            $notif->read_at = now();
        }
        return view('Purchasing.Notification.view', compact('notif'));
    }

    public function notificationsMarkAllRead(Request $request)
    {
        DB::table('notifications')->whereNull('read_at')->update(['read_at'=>now(), 'updated_at'=>now()]);
        if ($request->expectsJson()) return response()->json(['success'=>true]);
        return back()->with('status','All notifications marked as read');
    }

    public function notificationsJump($id)
    {
        // Simple jump placeholder; redirect to purchasing dashboard for now
        return redirect()->route('purchasing.dashboard');
    }
}