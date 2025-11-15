<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Dashboard
     */
    public function index()
    {
        // Recent transactions for the table (paginate for blade footer summary)
        $recentTx = DB::table('inventory_transactions as t')
            ->leftJoin('items as i','i.item_id','=','t.item_id')
            ->select('t.*','i.item_name')
            ->orderByDesc('t.created_at')
            ->paginate(10)
            ->through(function($t){
                $t->created_at = Carbon::parse($t->created_at);
                $t->item = (object)['item_name'=>$t->item_name];
                return $t;
            });
        return view('Inventory.dashboard', compact('recentTx'));
    }

    /**
     * Reports page
     */
    public function report()
    {
        return view('Inventory.report');
    }

    /**
     * Reports JSON generator
     */
    public function generateReport(Request $request, $type)
    {
        $from = $request->query('from');
        $to   = $request->query('to');
        $from = $from ?: now()->subMonth()->toDateString();
        $to   = $to   ?: now()->toDateString();

        switch ($type) {
            case 'stock-level':
                $rows = DB::table('items')
                    ->select('item_name','item_stock','item_unit')
                    ->orderBy('item_name')
                    ->limit(200)
                    ->get();
                $title = 'Stock Level';
                $html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr>'
                    .'<th class="px-3 py-2 text-left">Item</th>'
                    .'<th class="px-3 py-2 text-right">Stock</th>'
                    .'<th class="px-3 py-2 text-left">Unit</th>'
                    .'</tr></thead><tbody class="divide-y">';
                foreach ($rows as $r) {
                    $html .= '<tr><td class="px-3 py-2">'.e($r->item_name).'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->item_stock,2).'</td>'
                        .'<td class="px-3 py-2">'.e($r->item_unit).'</td></tr>';
                }
                $html .= '</tbody></table>';
                return response()->json(['title'=>$title,'html'=>$html]);

            case 'stock-in':
            case 'stock-out':
                $dir = $type === 'stock-in' ? 'in' : 'out';
                $rows = DB::table('inventory_transactions as t')
                    ->leftJoin('items as i','i.item_id','=','t.item_id')
                    ->where('t.trans_type', $dir)
                    ->whereBetween(DB::raw('DATE(t.created_at)'), [$from, $to])
                    ->select('t.created_at','i.item_name','t.trans_quantity','t.balance_qty','t.trans_ref')
                    ->orderByDesc('t.created_at')
                    ->limit(200)
                    ->get();
                $title = $type === 'stock-in' ? 'Stock-In' : 'Stock-Out';
                $html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr>'
                    .'<th class="px-3 py-2 text-left">Date</th>'
                    .'<th class="px-3 py-2 text-left">Item</th>'
                    .'<th class="px-3 py-2 text-right">Qty</th>'
                    .'<th class="px-3 py-2 text-right">Balance</th>'
                    .'<th class="px-3 py-2 text-left">Ref</th>'
                    .'</tr></thead><tbody class="divide-y">';
                foreach ($rows as $r) {
                    $html .= '<tr><td class="px-3 py-2">'.e(Carbon::parse($r->created_at)->format('M d, Y H:i')).'</td>'
                        .'<td class="px-3 py-2">'.e($r->item_name ?? '-').'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->trans_quantity,2).'</td>'
                        .'<td class="px-3 py-2 text-right">'.($r->balance_qty!==null?number_format($r->balance_qty,2):'-').'</td>'
                        .'<td class="px-3 py-2">'.e($r->trans_ref ?? '-').'</td></tr>';
                }
                $html .= '</tbody></table>';
                return response()->json(['title'=>$title,'html'=>$html]);

            case 'expiry':
                $rows = DB::table('items')
                    ->whereNotNull('item_expire_date')
                    ->whereBetween('item_expire_date', [$from, $to])
                    ->select('item_name','item_expire_date','item_stock','item_unit')
                    ->orderBy('item_expire_date')
                    ->limit(200)
                    ->get();
                $title = 'Expiry';
                $html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr>'
                    .'<th class="px-3 py-2 text-left">Item</th>'
                    .'<th class="px-3 py-2 text-left">Expiry</th>'
                    .'<th class="px-3 py-2 text-right">Stock</th>'
                    .'<th class="px-3 py-2 text-left">Unit</th>'
                    .'</tr></thead><tbody class="divide-y">';
                foreach ($rows as $r) {
                    $html .= '<tr><td class="px-3 py-2">'.e($r->item_name).'</td>'
                        .'<td class="px-3 py-2">'.e(Carbon::parse($r->item_expire_date)->format('M d, Y')).'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->item_stock,2).'</td>'
                        .'<td class="px-3 py-2">'.e($r->item_unit).'</td></tr>';
                }
                $html .= '</tbody></table>';
                return response()->json(['title'=>$title,'html'=>$html]);

            case 'low-stock':
                $rows = DB::table('items')
                    ->whereColumn('item_stock','<=','reorder_level')
                    ->select('item_name','item_stock','reorder_level','item_unit')
                    ->orderBy('item_name')
                    ->limit(200)
                    ->get();
                $title = 'Low Stock';
                $html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr>'
                    .'<th class="px-3 py-2 text-left">Item</th>'
                    .'<th class="px-3 py-2 text-right">Stock</th>'
                    .'<th class="px-3 py-2 text-right">Reorder</th>'
                    .'<th class="px-3 py-2 text-left">Unit</th>'
                    .'</tr></thead><tbody class="divide-y">';
                foreach ($rows as $r) {
                    $html .= '<tr><td class="px-3 py-2">'.e($r->item_name).'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->item_stock,2).'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->reorder_level,2).'</td>'
                        .'<td class="px-3 py-2">'.e($r->item_unit).'</td></tr>';
                }
                $html .= '</tbody></table>';
                return response()->json(['title'=>$title,'html'=>$html]);

            case 'adjustments':
                $rows = DB::table('inventory_transactions as t')
                    ->leftJoin('items as i','i.item_id','=','t.item_id')
                    ->where('t.trans_type','adjust')
                    ->whereBetween(DB::raw('DATE(t.created_at)'), [$from, $to])
                    ->select('t.created_at','i.item_name','t.trans_quantity','t.balance_qty','t.trans_ref')
                    ->orderByDesc('t.created_at')
                    ->limit(200)
                    ->get();
                $title = 'Adjustments';
                $html = '<table class="w-full text-sm"><thead class="bg-gray-50"><tr>'
                    .'<th class="px-3 py-2 text-left">Date</th>'
                    .'<th class="px-3 py-2 text-left">Item</th>'
                    .'<th class="px-3 py-2 text-right">Qty</th>'
                    .'<th class="px-3 py-2 text-right">Balance</th>'
                    .'<th class="px-3 py-2 text-left">Ref</th>'
                    .'</tr></thead><tbody class="divide-y">';
                foreach ($rows as $r) {
                    $html .= '<tr><td class="px-3 py-2">'.e(Carbon::parse($r->created_at)->format('M d, Y H:i')).'</td>'
                        .'<td class="px-3 py-2">'.e($r->item_name ?? '-').'</td>'
                        .'<td class="px-3 py-2 text-right">'.number_format($r->trans_quantity,2).'</td>'
                        .'<td class="px-3 py-2 text-right">'.($r->balance_qty!==null?number_format($r->balance_qty,2):'-').'</td>'
                        .'<td class="px-3 py-2">'.e($r->trans_ref ?? '-').'</td></tr>';
                }
                $html .= '</tbody></table>';
                return response()->json(['title'=>$title,'html'=>$html]);
        }

        return response()->json(['title'=>'Unknown Report','html'=>'<p class="text-rose-600">Unsupported report.</p>'], 404);
    }

    /**
     * Daily summary for dashboard default cards (JSON)
     */
    public function dailySummary()
    {
        $date = Carbon::now()->format('M d, Y');
        $totalItems   = (int) DB::table('items')->count();
        $stockInToday = (float) DB::table('inventory_transactions')->where('trans_type','in')->whereDate('created_at', today())->sum('trans_quantity');
        $stockOutToday= (float) DB::table('inventory_transactions')->where('trans_type','out')->whereDate('created_at', today())->sum('trans_quantity');
        $top = DB::table('inventory_transactions as t')
            ->leftJoin('items as i','i.item_id','=','t.item_id')
            ->whereDate('t.created_at', today())
            ->orderByDesc('t.trans_quantity')
            ->limit(5)
            ->get()
            ->map(function($t){
                return [
                    'item_name' => $t->item_name,
                    'trans_type' => $t->trans_type,
                    'trans_quantity' => $t->trans_quantity,
                    'balance_qty' => $t->balance_qty,
                ];
            });
        return response()->json([
            'date' => $date,
            'totalItems' => $totalItems,
            'stockInToday' => $stockInToday,
            'stockOutToday' => $stockOutToday,
            'topMovements' => $top,
        ]);
    }

    public function stockInIndex()
    {
        // KPI data
        $kpi = DB::selectOne("SELECT * FROM stock_in_summary(7)");
        $kpi = (object) $kpi;

        // Recent transactions
        $transactions = DB::table('inventory_transactions as t')
            ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
            ->leftJoin('purchase_orders as po', 'po.po_id', '=', 't.po_id')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
            ->select('t.*', 'i.item_name', 'i.cat_id', 'po.po_ref', 's.sup_name', 'u.name')
            ->where('t.trans_type', 'in')
            ->orderByDesc('t.created_at')
            ->paginate(20)
            ->through(function($t) {
                $t->trans_date = Carbon::parse($t->trans_date);
                $t->item = (object)['item_name' => $t->item_name, 'category' => (object)['cat_name' => DB::table('categories')->where('cat_id', $t->cat_id)->value('cat_name')]];
                $t->purchaseOrder = $t->po_ref ? (object)['po_ref' => $t->po_ref, 'supplier' => (object)['sup_name' => $t->sup_name]] : null;
                $t->user = (object)['name' => $t->name];
                return $t;
            });

        // Pending POs
        $pendingPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('po.po_id', 'po.po_ref', 'po.expected_delivery_date', 's.sup_name')
            ->where('po.po_status', 'ordered')
            ->whereNotNull('po.expected_delivery_date')
            ->where('po.expected_delivery_date', '>=', now()->toDateString())
            ->orderBy('po.expected_delivery_date')
            ->limit(10)
            ->get()
            ->map(function($po) {
                $po->expected_delivery_date = Carbon::parse($po->expected_delivery_date);
                $po->supplier = (object)['sup_name' => $po->sup_name];
                return $po;
            });

        // Overdue POs
        $overduePOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('po.po_id', 'po.po_ref', 'po.expected_delivery_date', 's.sup_name')
            ->where('po.po_status', 'ordered')
            ->whereNotNull('po.expected_delivery_date')
            ->where('po.expected_delivery_date', '<', now()->toDateString())
            ->orderBy('po.expected_delivery_date')
            ->limit(10)
            ->get()
            ->map(function($po) {
                $po->expected_delivery_date = Carbon::parse($po->expected_delivery_date);
                $po->supplier = (object)['sup_name' => $po->sup_name];
                return $po;
            });

        // Categories
        $categories = DB::table('categories')->orderBy('cat_name')->get();

        // Eligible PO options for modal
        $eligiblePOOptions = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.sup_id', '=', 'po.sup_id')
            ->select('po.po_id as id', 'po.po_ref as ref', 's.sup_name as supplier', 'po.po_status as status')
            ->whereIn('po.po_status', ['draft', 'ordered'])
            ->orderBy('po.po_ref')
            ->get();

        // Item options for modal
        $itemOptions = DB::table('items as i')
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.item_id as id', 'i.item_code as code', 'i.item_name as name', 'i.cat_id', 'c.cat_name')
            ->where('i.is_active', true)
            ->orderBy('i.item_name')
            ->get();

        // Memo options
        $memoOptions = DB::table('memos as m')
            ->leftJoin('users as u', 'u.user_id', '=', 'm.received_by')
            ->select('m.memo_ref', 'm.po_ref', 'm.received_date', 'u.name as received_by_name')
            ->orderByDesc('m.received_date')
            ->get();

        // Items for unit options
        $items = DB::table('items')->select('item_unit')->distinct()->get();

        return view('Inventory.Stock_In.stock_in', compact(
            'kpi', 'transactions', 'pendingPOs', 'overduePOs', 'categories',
            'eligiblePOOptions', 'itemOptions', 'memoOptions', 'items'
        ));
    }
    public function stockOutIndex()
    {
        // Approved requisitions awaiting issuance
        $approvedReqs = DB::table('requisitions as r')
            ->leftJoin('users as u', 'u.user_id', '=', 'r.requested_by')
            ->where('r.req_status', 'approved')
            ->select('r.*', 'u.name as requester_name')
            ->orderByDesc('r.created_at')
            ->paginate(20)
            ->through(function($r) {
                $r->created_at = Carbon::parse($r->created_at);
                $r->requester = (object)['name' => $r->requester_name];
                $r->items = collect(DB::table('requisition_items')->where('req_id', $r->req_id)->get());
                return $r;
            });

        // Recent ARs
        $recentARs = DB::table('acknowledge_receipts as ar')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'ar.req_id')
            ->leftJoin('users as issued_by', 'issued_by.user_id', '=', 'ar.issued_by')
            ->leftJoin('users as issued_to', 'issued_to.user_id', '=', 'ar.issued_to')
            ->select('ar.*', 'r.req_ref', 'issued_by.name as issued_by_name', 'issued_to.name as issued_to_name')
            ->orderByDesc('ar.created_at')
            ->paginate(10)
            ->through(function($ar) {
                $ar->created_at = Carbon::parse($ar->created_at);
                $ar->requisition = (object)['req_ref' => $ar->req_ref];
                $ar->issuedBy = (object)['name' => $ar->issued_by_name];
                $ar->issuedTo = (object)['name' => $ar->issued_to_name];
                $ar->items = collect([]); // Could fetch items if needed
                return $ar;
            });

        return view('Inventory.Stock_Out.stock_out', compact('approvedReqs', 'recentARs'));
    }
    public function adjustmentsIndex(){ return view('Inventory.Adjustment.inventory_adjustment'); }
    public function alertsIndex()
    {
        // Categories for filter dropdown
        $categories = DB::table('categories')->orderBy('cat_name')->get();

        // Paginated low-stock items (<= reorder level), include category name and shape as category object
        $lowStockItems = DB::table('items as i')
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.*', 'c.cat_name')
            ->where('i.is_active', true)
            ->whereColumn('i.item_stock', '<=', 'i.reorder_level')
            ->orderBy('i.item_name')
            ->paginate(20)
            ->through(function ($it) {
                $it->category = (object) ['cat_name' => $it->cat_name];
                return $it;
            });

        return view('Inventory.Alert.low_stock_alert', compact('categories', 'lowStockItems'));
    }
    public function notificationsIndex()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function($n) {
                $n->created_at = Carbon::parse($n->created_at);
                return $n;
            });

        return view('Inventory.Notification.notification', compact('notifications'));
    }
    public function transactionsIndex()
    {
        $items = DB::table('items as i')
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.*', 'c.cat_name')
            ->where('i.is_active', true)
            ->orderBy('i.item_name')
            ->paginate(20);

        $categories = DB::table('categories')->orderBy('cat_name')->get();

        return view('Inventory.Inventory.inventory_list', compact('items', 'categories'));
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            'item_name' => 'required|string',
            'item_description' => 'nullable|string',
            'item_unit' => 'required|string',
            'cat_id' => 'required|integer|exists:categories,cat_id',
            'item_stock' => 'required|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'reorder_level' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'is_custom' => 'boolean',
        ]);

        DB::table('items')->insert(array_merge($data, [
            'last_updated' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return response()->json(['success' => true, 'message' => 'Item created successfully']);
    }

    public function bulkUpdateItems(Request $request)
    {
        $data = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:items,item_id',
            'updates' => 'required|array',
            'updates.*' => 'nullable',
        ]);

        $updates = array_filter($data['updates']); // Remove null values

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No updates provided']);
        }

        DB::table('items')
            ->whereIn('item_id', $data['item_ids'])
            ->update(array_merge($updates, ['updated_at' => now()]));

        return response()->json(['success' => true, 'message' => 'Items updated successfully']);
    }

    public function storeBulkStockIn(Request $request)
    {
        $data = $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.item_id' => 'required|integer|exists:items,item_id',
            'rows.*.quantity' => 'required|numeric|min:0.001',
            'rows.*.unit' => 'required|string',
            'rows.*.trans_date' => 'required|date',
            'rows.*.expiry_date' => 'nullable|date',
            'rows.*.po_id' => 'nullable|integer|exists:purchase_orders,po_id',
            'rows.*.remarks' => 'nullable|string',
            'memo_ref' => 'nullable|string',
        ]);

        $memoRef = $data['memo_ref'] ?? null;
        $memoRemarks = $memoRef ? "Memo: $memoRef - " : '';

        foreach ($data['rows'] as $row) {
            $transRef = 'SI-' . now()->format('Ymd') . '-' . str_pad((string) (DB::table('inventory_transactions')->max('trans_id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);

            DB::table('inventory_transactions')->insert([
                'trans_ref' => $transRef,
                'trans_type' => 'in',
                'trans_quantity' => $row['quantity'],
                'trans_date' => $row['trans_date'],
                'trans_remarks' => $memoRemarks . ($row['remarks'] ?? ''),
                'po_id' => $row['po_id'] ?? null,
                'trans_by' => auth()->id(),
                'item_id' => $row['item_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update item stock
            DB::table('items')
                ->where('item_id', $row['item_id'])
                ->increment('item_stock', $row['quantity']);

            // Update item expiry if provided
            if ($row['expiry_date']) {
                DB::table('items')
                    ->where('item_id', $row['item_id'])
                    ->update(['item_expire_date' => $row['expiry_date']]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock-in transactions recorded successfully']);
    }

    public function acknowledgeReceiptsIndex()
    {
        $ars = DB::table('acknowledge_receipts as ar')
            ->leftJoin('requisitions as r', 'r.req_id', '=', 'ar.req_id')
            ->leftJoin('users as issued_by', 'issued_by.user_id', '=', 'ar.issued_by')
            ->leftJoin('users as issued_to', 'issued_to.user_id', '=', 'ar.issued_to')
            ->select('ar.*', 'r.req_ref', 'issued_by.name as issued_by_name', 'issued_to.name as issued_to_name')
            ->orderByDesc('ar.created_at')
            ->paginate(20)
            ->through(function($ar) {
                $ar->created_at = Carbon::parse($ar->created_at);
                $ar->requisition = (object)['req_ref' => $ar->req_ref];
                $ar->issuedBy = (object)['name' => $ar->issued_by_name];
                $ar->issuedTo = (object)['name' => $ar->issued_to_name];
                return $ar;
            });

        return view('Inventory.acknowledge_receipts', compact('ars'));
    }

    public function notificationShow($id)
    {
        $notif = DB::table('notifications')->where('notif_id', $id)->where('user_id', auth()->id())->first();
        if (!$notif) return response()->json(['error' => 'Not found'], 404);

        $jumpUrl = '#';
        if ($notif->related_type === 'purchase_order') {
            $jumpUrl = route('inventory.transactions.index'); // or specific PO view
        } elseif ($notif->related_type === 'requisition') {
            $jumpUrl = route('inventory.stock-out.index');
        }
        // Add more cases as needed

        return response()->json([
            'notif_title' => $notif->notif_title,
            'module' => ucfirst(str_replace('_', ' ', $notif->related_type)),
            'notif_content' => $notif->notif_content,
            'created_at' => Carbon::parse($notif->created_at)->format('M d, Y H:i'),
            'is_read' => $notif->is_read,
            'jump_url' => $jumpUrl,
        ]);
    }

    public function notificationMarkRead($id)
    {
        DB::table('notifications')
            ->where('notif_id', $id)
            ->where('user_id', auth()->id())
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function notificationsMarkAllRead()
    {
        DB::table('notifications')
            ->where('user_id', auth()->id())
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function notificationsJump($id)
    {
        $notif = DB::table('notifications')->where('notif_id', $id)->where('user_id', auth()->id())->first();
        if (!$notif) abort(404);

        // Simple jump logic - redirect to relevant page
        if ($notif->related_type === 'purchase_order') {
            return redirect()->route('inventory.transactions.index');
        } elseif ($notif->related_type === 'requisition') {
            return redirect()->route('inventory.stock-out.index');
        }

        return redirect()->route('inventory.notifications.index');
    }

    public function notifyLowStock(Request $request)
    {
        // Get low stock items
        $lowStockItems = DB::select('SELECT * FROM get_low_stock_items()');

        if (empty($lowStockItems)) {
            return response()->json(['success' => false, 'message' => 'No low stock items found']);
        }

        // Create notifications for relevant users (supervisors, purchasing, etc.)
        $users = DB::table('users')
            ->whereIn('role', ['supervisor', 'purchasing'])
            ->get();

        foreach ($users as $user) {
            foreach ($lowStockItems as $item) {
                DB::table('notifications')->insert([
                    'notif_title' => 'Low Stock Alert',
                    'notif_content' => "Item '{$item->item_name}' is low on stock ({$item->current_stock} remaining, reorder at {$item->reorder_level})",
                    'related_type' => 'item',
                    'related_id' => $item->item_id,
                    'user_id' => $user->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Low stock notifications sent']);
    }
}