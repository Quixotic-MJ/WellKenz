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

        return view('Purchasing.dashboard', compact(
            'approvedReqs', 'draftPOs', 'orderedPOs', 'deliveredPOs',
            'overdue', 'awaiting', 'recentPOs', 'totalPOs',
            'totalSuppliers', 'activeSuppliersThisMonth',
            'topSupplierQtyName', 'topSupplierValName', 'missingPrices', 'notifs'
        ));
    }

    // ===== Approved Requisitions → Create PO =====
    public function approvedIndex(Request $request)
    {
        // live counters
        $approvedReqsCount = DB::table('requisitions')->where('req_status','approved')->count();
        $pendingPOCount = DB::table('purchase_orders')->where('po_status','draft')->count();
        $convertedTodayCount = DB::table('purchase_orders')->whereDate('created_at', today())->count();
        $approvedThisWeekCount = DB::table('requisitions')->where('req_status','approved')->whereBetween('updated_at',[now()->startOfWeek(), now()->endOfWeek()])->count();

        // overdue deliveries (POs expected earlier than today)
        $overdue = DB::table('purchase_orders')
            ->where('po_status','ordered')
            ->whereNotNull('expected_delivery_date')
            ->where('expected_delivery_date','<', now()->toDateString())
            ->orderBy('expected_delivery_date')
            ->limit(5)->get();

        // low stock items
        $lowStockItems = DB::table('items')
            ->whereColumn('item_stock','<=','reorder_level')
            ->select('item_name','item_stock as current_stock','reorder_level','item_unit')
            ->orderBy('item_name')->limit(8)->get();

        // approved requisitions list (paginated with filters)
        $approvedReqsQuery = DB::table('requisitions as r')
            ->leftJoin('users as u','u.user_id','=','r.requested_by')
            ->leftJoin('users as a','a.user_id','=','r.approved_by')
            ->where('r.req_status','approved')
            ->select('r.*','u.name as requester_name','a.name as approver_name', DB::raw('(SELECT COUNT(*) FROM requisition_items ri WHERE ri.req_id = r.req_id) as items_count'))
            ->orderByDesc('r.updated_at');

        // apply filters
        if ($request->filled('priority')) {
            $approvedReqsQuery->where('r.req_priority', $request->input('priority'));
        }
        if ($request->filled('requester')) {
            $approvedReqsQuery->where('u.name', 'like', '%'.$request->input('requester').'%');
        }
        if ($request->filled('date_from')) {
            $approvedReqsQuery->whereDate('r.updated_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $approvedReqsQuery->whereDate('r.updated_at', '<=', $request->input('date_to'));
        }

        $approvedReqs = $approvedReqsQuery->paginate(15)->through(function($r){
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                $r->requester = (object)['name'=>$r->requester_name];
                $r->approver = (object)['name'=>$r->approver_name];
                $r->items = collect(array_fill(0, (int)($r->items_count ?? 0), 1));
                return $r;
            });

        // recent Draft and Ordered POs lists for quick access
        $draftPOsList = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select('po.po_id','po.po_ref','po.total_amount','po.expected_delivery_date','po.created_at','po.po_status','s.sup_name')
            ->where('po.po_status','draft')
            ->orderByDesc('po.created_at')
            ->limit(10)->get();
        $orderedPOsList = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select('po.po_id','po.po_ref','po.total_amount','po.expected_delivery_date','po.created_at','po.po_status','s.sup_name')
            ->where('po.po_status','ordered')
            ->orderByDesc('po.created_at')
            ->limit(10)->get();

        return view('Purchasing.Purchase.create_purchase_order', compact(
            'approvedReqsCount','pendingPOCount','convertedTodayCount','approvedThisWeekCount',
            'overdue','lowStockItems','approvedReqs','draftPOsList','orderedPOsList'
        ));
    }

    public function requisitionsMarkRead(Request $request)
    {
        $ids = $request->input('ids', []);
        // This is a placeholder to mark notifications related to reqs as read
        // Implement actual logic if you track purchasing notifications per requisition
        return response()->json(['success'=>true]);
    }

    // Suppliers JSON for modal select; returns JSON if requested, page otherwise
    public function suppliersIndex(Request $request)
    {
        if ($request->expectsJson()) {
            return DB::table('suppliers')->select('sup_id','sup_name')->orderBy('sup_name')->get();
        }
        $suppliers = DB::table('suppliers')->orderBy('sup_name')->paginate(20);
        return view('Purchasing.Supplier.supplier', compact('suppliers'));
    }

    // Create purchase order from requisitions
    public function purchaseOrdersStore(Request $request)
    {
        $data = $request->validate([
            'sup_id' => 'required|integer',
            'delivery_address' => 'required|string',
            'expected_delivery_date' => 'nullable|date',
            'req_id' => 'required|string', // comma-separated ids
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        $reqIds = collect(explode(',', $data['req_id']))->filter()->map(fn($v)=>(int)$v)->values();
        $isSubmit = $request->input('action') === 'submit';
        $nextSeq = (int) ((DB::table('purchase_orders')->max('po_id') ?? 0) + 1);
        $poRef  = 'PO-'.now()->format('Ymd').'-'.str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

        $poId = DB::table('purchase_orders')->insertGetId([
            'po_ref' => $poRef,
            'sup_id' => $data['sup_id'],
            'req_id' => $reqIds->first(),
            'po_status' => $isSubmit ? 'ordered' : 'draft',
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'delivery_address' => $data['delivery_address'],
            'total_amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'po_id');

        $total = 0;
        foreach ($data['items'] as $row) {
            $qty = (float)$row['quantity'];
            $price = (float)$row['unit_price'];
            $line = $qty * $price;
            $total += $line;
            DB::table('purchase_items')->insert([
                'po_id' => $poId,
                'item_id' => $row['item_id'],
                'pi_quantity' => $qty,
                'pi_unit_price' => $price,
                'pi_subtotal' => $line,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('purchase_orders')->where('po_id',$poId)->update(['total_amount'=>$total, 'updated_at'=>now()]);

        // Notifications on finalize
        if ($isSubmit) {
            $title = 'Purchase Order Finalized';
            $content = 'PO '.$poRef.' has been submitted to supplier.';
            DB::table('notifications')->insert([
                [
                    'notif_title' => $title,
                    'notif_content' => $content,
                    'related_type' => 'purchase_order',
                    'related_id' => $poId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        return response()->json(['success'=>true,'message'=>'Purchase order created successfully']);
    }

    // ===== Memo =====
    public function memoIndex()
    {
        $orderedPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select(
                'po.*',
                's.sup_name',
                DB::raw('(SELECT COUNT(*) FROM purchase_items pi WHERE pi.po_id = po.po_id) as items_count')
            )
            ->where('po.po_status','ordered')
            ->orderByDesc('po.created_at')
            ->paginate(15);
        return view('Purchasing.Memo.memo', compact('orderedPOs'));
    }

    public function memoRecord()
    {
        return view('Purchasing.Memo.record');
    }

    // ===== Notifications =====
    public function notificationsIndex()
    {
        return view('Purchasing.Notification.notification');
    }
    public function notificationsMarkAllRead()
    {
        // mark all purchasing notifications as read (placeholder)
        DB::table('notifications')->where('related_type','purchase_order')->update(['is_read'=>true]);
        return response()->json(['success'=>true]);
    }
    public function notificationsView($id)
    {
        return view('Purchasing.Notification.view', compact('id'));
    }
    public function notificationsJump($id)
    {
        // simple jump handler; redirect to dashboard for now
        return redirect()->route('purchasing.dashboard');
    }

    // ===== Purchase =====
    public function purchaseCreatePo()
    {
        // approved requisitions for selection
        $approvedReqs = DB::table('requisitions as r')
            ->leftJoin('users as u','u.user_id','=','r.requested_by')
            ->where('r.req_status','approved')
            ->select('r.*','u.name as requester_name', DB::raw('(SELECT COUNT(*) FROM requisition_items ri WHERE ri.req_id = r.req_id) as items_count'))
            ->orderByDesc('r.updated_at')
            ->get()
            ->map(function($r){
                $r->created_at = \Carbon\Carbon::parse($r->created_at);
                $r->requester = (object)['name'=>$r->requester_name];
                $r->items = collect(array_fill(0, (int)($r->items_count ?? 0), 1));
                return $r;
            });

        // suppliers
        $suppliers = DB::table('suppliers')->select('sup_id','sup_name')->orderBy('sup_name')->get();

        return view('Purchasing.Purchase.create-po', compact('approvedReqs', 'suppliers'));
    }

    // Create a draft PO from multiple requisitions (combine items), return JSON {success, po_id}
    public function purchaseCreateFromReqs(Request $request)
    {
        $reqIds = collect($request->input('req_ids', []))->filter()->map(fn($v)=>(int)$v)->values();
        if ($reqIds->isEmpty()) {
            return response()->json(['success'=>false,'message'=>'No requisitions provided'], 422);
        }

        // Aggregate items across requisitions
        $items = DB::table('requisition_items as ri')
            ->select('ri.item_id', DB::raw('SUM(ri.req_item_quantity) as total_qty'))
            ->whereIn('ri.req_id', $reqIds)->groupBy('ri.item_id')->get();

        // Build requester names list
        $requesterNames = DB::table('requisitions as r')
            ->leftJoin('users as u','u.user_id','=','r.requested_by')
            ->whereIn('r.req_id', $reqIds)->pluck('u.name')->filter()->unique()->values();

        // Create PO (draft, without supplier yet)
        $nextSeq = (int) ((DB::table('purchase_orders')->max('po_id') ?? 0) + 1);
        $poRef  = 'PO-'.now()->format('Ymd').'-'.str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
        $poId = DB::table('purchase_orders')->insertGetId([
            'po_ref' => $poRef,
            'sup_id' => null,
            'req_id' => $reqIds->first(),
            'po_status' => 'draft',
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => null,
            'delivery_address' => 'To be determined',
            'total_amount' => 0,
            'notes' => $requesterNames->isNotEmpty() ? ('Requesters: '.$requesterNames->implode(', ')) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'po_id');

        foreach ($items as $it) {
            DB::table('purchase_items')->insert([
                'po_id' => $poId,
                'item_id' => $it->item_id,
                'pi_quantity' => (float)$it->total_qty,
                'pi_unit_price' => 0.00,
                'pi_subtotal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success'=>true, 'po_id'=>$poId]);
    }

    // View PO details page
    public function purchaseView($poId)
    {
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select('po.*','s.sup_name', 's.sup_address as sup_address','s.contact_person')
            ->where('po.po_id',$poId)->first();
        if(!$po) abort(404);
        $items = DB::table('purchase_items as pi')
            ->leftJoin('items as i','i.item_id','=','pi.item_id')
            ->where('pi.po_id',$poId)
            ->select('i.item_name','i.item_unit as unit','pi.item_id','pi.pi_quantity as quantity','pi.pi_unit_price as unit_price')
            ->get();
        $suppliers = DB::table('suppliers')->select('sup_id','sup_name')->orderBy('sup_name')->get();
        $purchaseOrder = (object) [
            'po_id' => $po->po_id,
            'po_ref' => $po->po_ref,
            'po_status' => $po->po_status,
            'created_at' => $po->created_at,
            'expected_delivery_date' => $po->expected_delivery_date,
            'delivery_address' => $po->delivery_address,
            'supplier' => (object)['name'=>$po->sup_name],
            'items' => $items,
        ];
        return view('Purchasing.Purchase.create-po', compact('purchaseOrder','suppliers'));
    }

    // Update a draft PO (supplier, expected date, delivery address, unit prices). Optionally finalize when action=submit
    public function purchaseUpdate(Request $request, $poId)
    {
        $po = DB::table('purchase_orders')->where('po_id',$poId)->first();
        if(!$po) abort(404);
        // Allow updates only if draft
        $isSubmit = $request->input('action') === 'submit';

        $data = $request->validate([
            'sup_id' => 'required|integer',
            'expected_delivery_date' => 'nullable|date',
            'delivery_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Update PO header
        DB::table('purchase_orders')->where('po_id',$poId)->update([
            'sup_id' => $data['sup_id'],
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'delivery_address' => $data['delivery_address'],
            'po_status' => $isSubmit ? 'ordered' : 'draft',
            'order_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

        // Update line prices and recompute totals
        $total = 0;
        foreach ($data['items'] as $row) {
            $line = ((float)($row['unit_price'])) * (float) DB::table('purchase_items')->where('po_id',$poId)->where('item_id',$row['item_id'])->value('pi_quantity');
            $total += $line;
            DB::table('purchase_items')
                ->where('po_id',$poId)
                ->where('item_id',$row['item_id'])
                ->update([
                    'pi_unit_price' => (float)$row['unit_price'],
                    'pi_subtotal' => $line,
                    'updated_at' => now(),
                ]);
        }
        DB::table('purchase_orders')->where('po_id',$poId)->update(['total_amount'=>$total, 'updated_at'=>now()]);

        // Notification on finalize
        if ($isSubmit) {
            $poRef = DB::table('purchase_orders')->where('po_id',$poId)->value('po_ref');
            DB::table('notifications')->insert([
                [
                    'notif_title' => 'Purchase Order Finalized',
                    'notif_content' => 'PO '.$poRef.' has been submitted to supplier.',
                    'related_type' => 'purchase_order',
                    'related_id' => $poId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        if ($isSubmit) {
            return redirect()->route('purchasing.purchase.view', $poId)->with('status','PO submitted');
        }
        return redirect()->route('purchasing.approved.index')->with('status','Draft saved');
    }

    // Delete a draft PO (and its items). Disallow deleting non-draft.
    public function purchaseDestroy($poId)
    {
        $po = DB::table('purchase_orders')->where('po_id',$poId)->first();
        if(!$po) abort(404);
        if ($po->po_status !== 'draft') {
            return back()->with('error','Only draft POs can be deleted.');
        }
        DB::table('purchase_items')->where('po_id',$poId)->delete();
        DB::table('purchase_orders')->where('po_id',$poId)->delete();
        return back()->with('status','Draft PO deleted');
    }

    // Printable PO page
    public function purchasePrint($poId)
    {
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select('po.*','s.sup_name','s.sup_address as sup_address','s.contact_person')
            ->where('po.po_id',$poId)->first();
        if(!$po) abort(404);
        $items = DB::table('purchase_items as pi')
            ->leftJoin('items as i','i.item_id','=','pi.item_id')
            ->where('pi.po_id',$poId)
            ->select('i.item_name','i.item_unit as unit','pi.pi_quantity as quantity','pi.pi_unit_price as unit_price')
            ->get();
        // Extract requester names from notes if present
        $requestorNames = null;
        if (!empty($po->notes) && str_starts_with($po->notes, 'Requesters: ')) {
            $requestorNames = substr($po->notes, strlen('Requesters: '));
        }
        $purchaseOrder = (object) [
            'po_id' => $po->po_id,
            'po_ref' => $po->po_ref,
            'po_status' => $po->po_status,
            'created_at' => $po->created_at,
            'expected_delivery_date' => $po->expected_delivery_date,
            'notes' => $po->notes,
            'supplier' => (object)['name'=>$po->sup_name, 'address'=>$po->sup_address, 'contact_person'=>$po->contact_person],
            'items' => $items,
        ];
        return view('Purchasing.Purchase.print', compact('purchaseOrder','requestorNames'));
    }

    // ===== Supplier pages (Purchasing namespace) =====
    public function supplierCreate()
    {
        return view('Purchasing.Supplier.create');
    }
    public function supplierEdit($id)
    {
        return view('Purchasing.Supplier.edit', compact('id'));
    }
    public function supplierView($id)
    {
        return view('Purchasing.Supplier.view', compact('id'));
    }
    public function supplierPOs($id)
    {
        return view('Purchasing.Supplier.pos', compact('id'));
    }

    // ===== Reports =====
    public function report()
    {
        $recentPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.sup_id','=','po.sup_id')
            ->select('po.*','s.sup_name')
            ->orderByDesc('po.created_at')
            ->paginate(10);
        return view('Purchasing.report', compact('recentPOs'));
    }

    // ===== Requisition details (JSON) for Purchasing modals =====
    public function requisitionShow($id)
    {
        $req = DB::table('requisitions as r')
            ->leftJoin('users as u','u.user_id','=','r.requested_by')
            ->leftJoin('users as a','a.user_id','=','r.approved_by')
            ->where('r.req_id',$id)
            ->select('r.*','u.name as requester_name','a.name as approver_name')
            ->first();
        if(!$req) return response()->json(['error'=>'Not found'],404);
        $items = DB::table('requisition_items as ri')
            ->leftJoin('items as i','i.item_id','=','ri.item_id')
            ->where('ri.req_id',$id)
            ->select('ri.item_id','ri.req_item_quantity','ri.item_unit','ri.req_item_status','i.item_name','i.item_stock')
            ->get()
            ->map(function($row){
                return [
                    'item_id' => $row->item_id,
                    'req_item_quantity' => $row->req_item_quantity,
                    'item_unit' => $row->item_unit,
                    'req_item_status' => $row->req_item_status,
                    'item' => ['item_name' => $row->item_name, 'current_stock' => $row->item_stock],
                ];
            });
        return response()->json([
            'req_ref' => $req->req_ref,
            'req_status' => $req->req_status,
            'req_priority' => $req->req_priority,
            'req_purpose' => $req->req_purpose,
            'requester' => ['name'=>$req->requester_name],
            'approver' => ['name'=>$req->approver_name],
            'approved_date' => $req->approved_date,
            'req_remarks' => $req->req_reject_reason ?? null,
            'items' => $items,
            'created_at' => $req->created_at,
            'updated_at' => $req->updated_at,
        ]);
    }
}