<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AcknowledgementReceiptController extends Controller
{
    /**
     * Employee page: list own acknowledgement receipts with KPI counts.
     */
    public function employeeIndex()
    {
        $userId = Auth::id();
        if (!$userId) {
            return view('Employee.AR.acknowledgement_receipt', [
                'receipts' => collect(),
                'totalCount' => 0,
                'issuedCount' => 0,
                'receivedCount' => 0,
                'thisMonthCount' => 0,
            ]);
        }

        // Base counts
        $totalCount = DB::table('acknowledge_receipts')->where('issued_to', $userId)->count();
        $issuedCount = DB::table('acknowledge_receipts')->where('issued_to', $userId)->where('ar_status', 'issued')->count();
        $receivedCount = DB::table('acknowledge_receipts')->where('issued_to', $userId)->where('ar_status', 'received')->count();
        $thisMonthCount = DB::table('acknowledge_receipts')
            ->where('issued_to', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Receipts list with issuer name
        $rows = DB::table('acknowledge_receipts as ar')
            ->leftJoin('users as u1', 'u1.user_id', '=', 'ar.issued_by')
            ->where('ar.issued_to', $userId)
            ->orderBy('ar.created_at', 'desc')
            ->select('ar.*', 'u1.name as issuer_name')
            ->get();

        $receipts = collect($rows)->map(function ($r) {
            // Ensure date cast and issuer object for Blade compatibility
            $r->issued_date = $r->issued_date ? Carbon::parse($r->issued_date) : null;
            $r->issuer = (object) ['name' => $r->issuer_name];
            return $r;
        });

        return view('Employee.AR.acknowledgement_receipt', compact(
            'receipts', 'totalCount', 'issuedCount', 'receivedCount', 'thisMonthCount'
        ));
    }
    /**
     * Create a new Acknowledgement Receipt (AR) and perform Stock-Out
     * Payload shape:
     * {
     *   "issued_to": <user_id>,
     *   "req_id": <optional requisition id>,
     *   "ar_remarks": <optional>,
     *   "items": [{"item_id":1, "quantity":2}]
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'issued_to'           => 'required|exists:users,user_id',
            'req_id'              => 'nullable|exists:requisitions,req_id',
            'ar_remarks'          => 'nullable|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.item_id'     => 'required|exists:items,item_id',
            'items.*.quantity'    => 'required|numeric|gt:0',
        ]);

        DB::beginTransaction();
        try {
            $issuedBy = Auth::id();
            if (!$issuedBy) {
                return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
            }

            $arRef = 'AR-'.now()->format('Ymd-His');
            // Create AR
            $arId = DB::table('acknowledge_receipts')->insertGetId([
                'ar_ref'      => $arRef,
                'ar_remarks'  => $request->ar_remarks,
                'ar_status'   => 'issued',
                'issued_date' => now(),
                'req_id'      => $request->req_id,
                'issued_by'   => $issuedBy,
                'issued_to'   => $request->issued_to,
                'created_at'  => now(),
                'updated_at'  => now(),
            ], 'ar_id');

            // For each item, validate stock and create OUT transaction
            foreach ($request->items as $row) {
                $item = DB::table('items')->where('item_id', $row['item_id'])->lockForUpdate()->first();
                if (!$item) throw new \RuntimeException('Item not found: '.$row['item_id']);
                if ($item->item_stock < $row['quantity']) {
                    throw new \RuntimeException('Insufficient stock for item '.$item->item_code.' (have '.$item->item_stock.', need '.$row['quantity'].')');
                }

                // Create inventory transaction OUT
                DB::table('inventory_transactions')->insert([
                    'trans_ref'     => 'TRX-'.now()->format('YmdHis').'-OUT',
                    'trans_type'    => 'out',
                    'trans_quantity'=> $row['quantity'],
                    'trans_date'    => now(),
                    'trans_remarks' => $request->ar_remarks,
                    'po_id'         => null,
                    'trans_by'      => $issuedBy,
                    'item_id'       => $row['item_id'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Update item stock via stored function (decrement)
                DB::select('SELECT update_item_stock(?, ?, ?)', [
                    $row['item_id'], -1 * $row['quantity'], 'OUT'
                ]);
            }

            // Notify employee who received
            DB::table('notifications')->insert([
                'notif_title'   => 'Items Issued',
                'notif_content' => 'An Acknowledgement Receipt '.$arRef.' has been issued to you.',
                'related_id'    => (string)$arId,
                'related_type'  => 'AcknowledgementReceipt',
                'is_read'       => false,
                'user_id'       => $request->issued_to,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::commit();
            return response()->json(['success'=>true,'message'=>'AR created and stock-out recorded.','ar_id'=>$arId,'ar_ref'=>$arRef]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AR create error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $ar = DB::table('acknowledge_receipts as ar')
            ->leftJoin('users as u1','u1.user_id','=','ar.issued_by')
            ->leftJoin('users as u2','u2.user_id','=','ar.issued_to')
            ->where('ar.ar_id', $id)
            ->select('ar.*','u1.name as issued_by_name','u2.name as issued_to_name')
            ->first();
        if (!$ar) return response()->json(['error'=>'AR not found'], 404);

        $items = DB::table('inventory_transactions as t')
            ->where('t.trans_type','out')
            ->whereDate('t.trans_date', '=', date('Y-m-d', strtotime($ar->issued_date)))
            ->orderBy('t.item_id')
            ->get();

        return response()->json(['ar'=>$ar,'items'=>$items]);
    }

    /**
     * Confirm receipt of acknowledgement receipt (employee)
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $ar = DB::table('acknowledge_receipts')->where('ar_id', $request->id)->where('issued_to', $userId)->first();
        if (!$ar) {
            return response()->json(['success' => false, 'message' => 'AR not found or not authorized'], 404);
        }

        if ($ar->ar_status !== 'issued') {
            return response()->json(['success' => false, 'message' => 'AR is not in issued status'], 400);
        }

        DB::table('acknowledge_receipts')->where('ar_id', $request->id)->update([
            'ar_status' => 'received',
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Receipt confirmed successfully']);
    }

    /**
     * Print acknowledgement receipt
     */
    public function print($id)
    {
        // For now, just return a view or redirect
        // You can implement PDF generation here
        return view('Employee.AR.print', compact('id'));
    }
}
