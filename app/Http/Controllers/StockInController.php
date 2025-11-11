<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Memo;

class StockInController extends Controller
{
    /* ---------- main stock-in screen ---------- */
    public function index()
    {
        /* KPI from PostgreSQL function */
        $kpi = DB::select("SELECT * FROM stock_in_summary(7)")[0];

        /* recent stock-in transactions (last 30 days) */
        $transactions = InventoryTransaction::stockIn()
            ->with(['item.category','purchaseOrder.supplier','user'])
            ->recent(30)
            ->latest('trans_date')
            ->paginate(15);

        /* pending & overdue POs */
        $pendingPOs   = PurchaseOrder::pendingDelivery()->with('supplier')->get();
        $overduePOs   = PurchaseOrder::overdue()->with('supplier')->get();

        /* eligible POs for stock-in selection (statuses: draft, ordered, delivered) */
        $eligiblePOs  = PurchaseOrder::whereIn('po_status', ['draft','ordered','delivered'])
            ->with('supplier')
            ->orderByDesc('created_at')
            ->get();
        $eligiblePOOptions = $eligiblePOs->map(function($po){
            return [
                'id' => $po->po_id,
                'ref' => $po->po_ref,
                'supplier' => optional($po->supplier)->sup_name,
                'status' => $po->po_status,
            ];
        })->values();

        /* for dropdowns */
        $items        = Item::where('is_active',true)->with('category')->get();
        $categories   = Category::all();
        $itemOptions  = $items->map(function($it){
            return [
                'id' => $it->item_id,
                'code' => $it->item_code,
                'name' => $it->item_name,
                'unit' => $it->item_unit,
                'cat_id' => $it->cat_id,
                'cat_name' => optional($it->category)->cat_name,
            ];
        })->values();
        $suppliers    = Supplier::where('sup_status','active')->get();

        /* receiving memo options */
        $memoOptions = DB::table('memos as m')
            ->leftJoin('users as u','u.user_id','=','m.received_by')
            ->select('m.memo_ref','m.po_ref','m.received_date','u.name as received_by_name')
            ->orderByDesc('m.received_date')
            ->get();

        return view('Inventory.stock_in',compact(
            'kpi','transactions','pendingPOs','overduePOs','eligiblePOs','eligiblePOOptions','items','itemOptions','categories','suppliers','memoOptions'
        ));
    }

    /* ---------- record new stock-in ---------- */
    public function store(Request $request)
    {
        $request->validate([
            'item_id'     => 'required|exists:items,item_id',
            'quantity'    => 'required|numeric|min:0.001',
            'trans_date'  => 'required|date',
            'po_id'       => 'nullable|exists:purchase_orders,po_id',
            'expiry_date' => 'nullable|date|after:trans_date',
            'remarks'     => 'nullable|string|max:500',
            'memo_ref'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try{
            // Validate memo_ref belongs to selected PO (if both provided)
            if ($request->memo_ref && $request->po_id) {
                $memo = DB::table('memos')->where('memo_ref', $request->memo_ref)->first();
                $po   = PurchaseOrder::find($request->po_id);
                if (!$memo) {
                    return response()->json(['success'=>false,'message'=>'Receiving memo not found.'],422);
                }
                if (!$po) {
                    return response()->json(['success'=>false,'message'=>'Purchase Order not found.'],422);
                }
                if ($memo->po_ref !== $po->po_ref) {
                    return response()->json(['success'=>false,'message'=>'Selected memo does not match the selected PO.'],422);
                }
            }
            /* 1. create inventory transaction */
            $memoPrefix = $request->memo_ref ? ('Memo: '.$request->memo_ref) : null;
            $remarks = trim(implode(' | ', array_filter([$memoPrefix, $request->remarks], fn($v) => $v)));

            $trans = InventoryTransaction::create([
                'trans_ref'    => 'TRX-'.now()->format('YmdHis'),
                'trans_type'   => 'in',
                'trans_quantity'=> $request->quantity,
                'trans_date'   => $request->trans_date,
                'trans_remarks'=> $remarks ?: null,
                'memo_ref'     => $request->memo_ref ?: null,
                'po_id'        => $request->po_id,
                'trans_by'     => Auth::id(),
                'item_id'      => $request->item_id,
            ]);

            /* 2. update item stock */
            DB::select("SELECT update_item_stock(?,?::numeric,'IN')",[
                $request->item_id,
                $request->quantity
            ]);

            /* 3. optional: update PO status if fully delivered */
            if($request->po_id){
                $po = PurchaseOrder::find($request->po_id);
                $ordered = $po->items->sum('pi_quantity');
                $received = InventoryTransaction::where('po_id',$request->po_id)
                              ->stockIn()->sum('trans_quantity');
                if($received >= $ordered){
                    $po->update(['po_status'=>'delivered']);
                }
            }

            DB::commit();
            return response()->json(['success'=>true,'message'=>'Stock-in recorded.']);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
        }
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'rows'                  => 'required|array|min:1',
            'rows.*.item_id'        => 'required|exists:items,item_id',
            'rows.*.quantity'       => 'required|numeric|gt:0',
            'rows.*.trans_date'     => 'required|date',
            'rows.*.expiry_date'    => 'nullable|date|after_or_equal:rows.*.trans_date',
            'rows.*.po_id'          => 'nullable|exists:purchase_orders,po_id',
            'rows.*.damaged'        => 'nullable|boolean',
            'rows.*.remarks'        => 'nullable|string|max:500',
            'memo_ref'              => 'nullable|string|max:255',
            'memo_remarks'          => 'nullable|string|max:500',
        ]);

        $result = DB::transaction(function () use ($request) {
            // Determine global PO (if all rows belong to same PO)
            $globalPoId = null;
            if (!empty($request->rows)) {
                $firstPo = $request->rows[0]['po_id'] ?? null;
                $samePo = collect($request->rows)->every(function($row) use ($firstPo){ return ($row['po_id'] ?? null) === $firstPo; });
                if ($samePo) { $globalPoId = $firstPo; }
            }

            // Auto-create memo if none provided but PO present
            $memoRef = $request->memo_ref;
            if (!$memoRef && $globalPoId) {
                $po = PurchaseOrder::with('supplier')->find($globalPoId);
                if ($po) {
                    $memoRef = 'RM-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
                    DB::table('memos')->insert([
                        'memo_ref'      => $memoRef,
                        'po_ref'        => $po->po_ref,
                        'received_date' => now(),
                        'received_by'   => Auth::id(),
                        'memo_remarks'  => $request->memo_remarks,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            // Validate provided memo matches PO
            if ($memoRef && $globalPoId) {
                $memo = DB::table('memos')->where('memo_ref', $memoRef)->first();
                $po   = PurchaseOrder::find($globalPoId);
                if (!$memo || !$po || $memo->po_ref !== $po->po_ref) {
                    throw new \RuntimeException('Selected memo does not match the selected PO.');
                }
            }

            // Record transactions and stock updates
            foreach ($request->rows as $row) {
                $ref = 'IN-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
                $flags = [];
                if (!empty($row['damaged'])) { $flags[] = 'DAMAGED'; }
                $memoPrefix = $memoRef ? ('Memo: '.$memoRef) : null;
                $remarks = trim(implode(' | ', array_filter([$memoPrefix, $row['remarks'] ?? null, implode(',', $flags)], fn($v) => $v)));

                InventoryTransaction::create([
                    'trans_ref'     => $ref,
                    'trans_type'    => 'in',
                    'trans_quantity'=> $row['quantity'],
                    'trans_date'    => $row['trans_date'],
                    'trans_remarks' => $remarks ?: null,
                    'memo_ref'      => $memoRef ?: null,
                    'po_id'         => $row['po_id'] ?: null,
                    'trans_by'      => Auth::id(),
                    'item_id'       => $row['item_id'],
                ]);

                DB::select('SELECT update_item_stock(?, ?, ?)', [
                    $row['item_id'], $row['quantity'], 'IN'
                ]);
            }

            // Update PO status based on received quantities
            $poStatus = null;
            if ($globalPoId) {
                $po = PurchaseOrder::with(['items'])->find($globalPoId);
                if ($po && $po->items->count()>0) {
                    $fullyReceived = true; $anyReceived = false;
                    foreach ($po->items as $pi) {
                        $ordered = (float)$pi->pi_quantity;
                        $received = (float) InventoryTransaction::where('po_id',$pi->po_id)
                            ->where('item_id',$pi->item_id)->where('trans_type','in')->sum('trans_quantity');
                        if ($received > 0) { $anyReceived = true; }
                        if ($received + 1e-9 < $ordered) { $fullyReceived = false; }
                    }
                    if ($fullyReceived) { $poStatus = 'delivered'; }
                    elseif ($anyReceived) { $poStatus = 'partial'; }
                    if ($poStatus) { $po->update(['po_status'=>$poStatus]); }
                }
            }

            // Send notifications
            if ($globalPoId) {
                $po = PurchaseOrder::find($globalPoId);
                $message = 'Receiving completed for PO '.($po? $po->po_ref : '#').($poStatus? ' • Status: '.ucfirst($poStatus):'');
                $now = now();
                $usersPurchasing = DB::table('users')->where('role','purchasing')->pluck('user_id')->all();
                $usersSupervisor = DB::table('users')->where('role','supervisor')->pluck('user_id')->all();
                $rows = [];
                foreach (array_unique(array_merge($usersPurchasing,$usersSupervisor)) as $uid) {
                    $rows[] = [
                        'user_id' => $uid,
                        'notif_title' => 'Receiving Memo',
                        'notif_content' => $message,
                        'related_type' => 'purchase_order',
                        'related_id' => $globalPoId,
                        'is_read' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($rows) { DB::table('notifications')->insert($rows); }
            }

            return ['memo_ref'=>$memoRef, 'po_id'=>$globalPoId, 'po_status'=>$poStatus];
        });

        return response()->json(['success'=>true,'message'=>'Bulk stock-in saved','memo_ref'=>$result['memo_ref'],'po_status'=>$result['po_status']]);
    }

    // Helper: return PO details with items for receiving UI (JSON)
    public function poDetails($poId)
    {
        $po = PurchaseOrder::with(['supplier','items.item'])->findOrFail($poId);
        $payload = [
            'po_id'    => $po->po_id,
            'po_ref'   => $po->po_ref,
            'po_status'=> $po->po_status,
            'supplier' => optional($po->supplier)->sup_name,
            'items'    => $po->items->map(function($pi){
                return [
                    'item_id'   => $pi->item_id,
                    'item_code' => optional($pi->item)->item_code,
                    'item_name' => optional($pi->item)->item_name,
                    'item_unit' => optional($pi->item)->item_unit,
                    'ordered'   => (float)$pi->pi_quantity,
                    'received'  => (float) InventoryTransaction::where('po_id',$pi->po_id)->where('item_id',$pi->item_id)->where('trans_type','in')->sum('trans_quantity'),
                ];
            })->values(),
        ];
        return response()->json($payload);
    }

    // List POs for receiving page
    public function poList(Request $request)
    {
        $status = strtolower($request->get('status',''));
        $search = strtolower($request->get('search',''));

        // Map UI status to DB statuses (pending => draft)
        $statusMap = [
            'pending' => ['draft','pending'],
            'ordered' => ['ordered'],
            'partial' => ['partial'],
        ];

        $defaultStatuses = ['draft','ordered','partial'];

        $q = PurchaseOrder::query()
            ->with('supplier')
            ->when(in_array($status, array_keys($statusMap)), function($qq) use($status,$statusMap){
                $vals = array_map('strtolower', $statusMap[$status]);
                $qq->whereIn(DB::raw('LOWER(po_status::text)'), $vals);
            }, function($qq) use ($defaultStatuses){
                $vals = array_map('strtolower', $defaultStatuses);
                $qq->whereIn(DB::raw('LOWER(po_status::text)'), $vals);
            })
            ->orderByDesc('created_at')
            ->limit(50);

        $pos = $q->get()->map(function($po){
            return [
                'po_id'        => $po->po_id,
                'po_ref'       => $po->po_ref,
                'supplier'     => optional($po->supplier)->sup_name,
                'total_amount' => (float) $po->total_amount,
                'po_status'    => $po->po_status,
                'created_at'   => optional($po->created_at)->toDateTimeString(),
            ];
        })->filter(function($row) use ($search){
            if (!$search) return true;
            return str_contains(strtolower($row['po_ref'] ?? ''), $search)
                || str_contains(strtolower($row['supplier'] ?? ''), $search);
        })->values();

        return response()->json($pos);
    }
}