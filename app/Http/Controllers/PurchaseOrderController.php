<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseItem;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /* ----------------------------------------------------------
       MAIN PURCHASING DASHBOARD (single-file blade)
    ---------------------------------------------------------- */
    public function dashboard()
    {
        $approvedReqs = Requisition::with(['requester', 'items.item'])
                        ->where('req_status', 'approved')
                        ->latest()
                        ->limit(20)
                        ->get();

        $pos       = PurchaseOrder::with(['supplier', 'requisition'])
                        ->latest()
                        ->limit(20)
                        ->get();

        $suppliers = Supplier::where('sup_status', 'active')->get();

        return view('Purchasing.dashboard', compact('approvedReqs', 'pos', 'suppliers'));
    }

    /* ----------------------------------------------------------
       CREATE PO FROM REQUISITION
    ---------------------------------------------------------- */
    public function createFromRequisition(Requisition $requisition)
    {
        $suppliers = Supplier::where('sup_status', 'active')->get();
        $items     = $requisition->items()->with('item')->get();

        return view('Purchasing.create_purchase_order', compact('requisition', 'suppliers', 'items'));
    }

    /* ----------------------------------------------------------
       STORE NEW PO
    ---------------------------------------------------------- */
    public function store(Request $request)
    {
        $request->validate([
            'req_id' => 'required|exists:requisitions,req_id',
            'sup_id' => 'required|exists:suppliers,sup_id',
            'delivery_address' => 'required|string',
            'expected_delivery_date' => 'nullable|date|after:today',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        $po = DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->items as $it) {
                $total += $it['quantity'] * $it['unit_price'];
            }

            $po = PurchaseOrder::create([
                'po_ref' => 'PO-' . now()->format('YmdHis'),
                'po_status' => 'ordered',
                'order_date' => now(),
                'delivery_address' => $request->delivery_address,
                'expected_delivery_date' => $request->expected_delivery_date,
                'total_amount' => $total,
                'sup_id' => $request->sup_id,
                'req_id' => $request->req_id,
            ]);

            foreach ($request->items as $it) {
                PurchaseItem::create([
                    'po_id' => $po->po_id,
                    'item_id' => $it['item_id'],
                    'pi_quantity' => $it['quantity'],
                    'pi_unit_price' => $it['unit_price'],
                    'pi_subtotal' => $it['quantity'] * $it['unit_price'],
                ]);
            }

            return $po;   // returned so $po exists after transaction
        });

        return redirect()
               ->route('purchase_orders.print', $po->po_id)
               ->with('success', 'Purchase order created & ready to print.');
    }

    /* ----------------------------------------------------------
       PRINT PO
    ---------------------------------------------------------- */
    public function print(PurchaseOrder $po)
    {
        $po->load(['supplier', 'requisition', 'items.item']);

        return view('Purchasing.print_purchase_order', compact('po'));
    }
}