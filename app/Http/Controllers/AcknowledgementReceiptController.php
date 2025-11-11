<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AcknowledgementReceiptController extends Controller
{
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

                // Update item stock (decrement)
                DB::update('UPDATE items SET item_stock = item_stock - ? , last_updated = NOW(), updated_at = NOW() WHERE item_id = ?', [
                    $row['quantity'], $row['item_id']
                ]);

                // If linked to requisition, mark requisition_items as Issued when fully issued
                if ($request->req_id) {
                    // Reduce outstanding quantities logic can be added here; for now mark as issued if any issuance exists
                    DB::table('requisition_items')
                        ->where('req_id', $request->req_id)
                        ->where('item_id', $row['item_id'])
                        ->update(['req_item_status' => 'issued', 'updated_at'=> now()]);
                }
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
}
