<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseItem;
use App\Models\Requisition;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;   // composer require barryvdh/laravel-dompdf

class PurchaseOrderController extends Controller
{
    /* -------------------------------------------------
     * APPROVED-REQUISITION DASHBOARD (KPI + cards)
     * -------------------------------------------------*/
    public function create_purchase_order()
    {
        // KPI counters
        $approvedReqs = Requisition::approved()->count();
        $totalPOs     = PurchaseOrder::count();
        $suppliers    = Supplier::where('sup_status', 'active')->count();
        $lowStock     = DB::table('items')
            ->whereRaw('item_stock <= reorder_level')
            ->where('is_active', true)
            ->count();

        // latest 20 approved requisitions
        $approvedReqsList = Requisition::with(['requester', 'items.item'])
            ->approved()
            ->latest()
            ->limit(20)
            ->get();

        // latest 20 POs
        $recentPOs = PurchaseOrder::with(['supplier', 'requisition'])
            ->latest()
            ->limit(20)
            ->get();

        return view('Purchasing.create_purchase_order', compact(
            'approvedReqs',
            'totalPOs',
            'suppliers',
            'lowStock',
            'approvedReqsList',
            'recentPOs'
        ));
    }

    /* -------------------------------------------------
     * CREATE PO FROM A SINGLE APPROVED REQUISITION
     * -------------------------------------------------*/
    public function createFromRequisition(Requisition $requisition)
    {
        // security: only approved requisitions
        abort_if($requisition->req_status !== 'approved', 404);

        $suppliers = Supplier::where('sup_status', 'active')->get();
        $requisition->load('items.item');

        return view('Purchasing.create_purchase_order', compact('requisition', 'suppliers'));
    }

    /* -------------------------------------------------
     * STORE NEW PO (WITH ITEMS)
     * -------------------------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'req_id'                   => 'required|exists:requisitions,req_id',
            'sup_id'                   => 'required|exists:suppliers,sup_id',
            'delivery_address'         => 'required|string',
            'expected_delivery_date'   => 'nullable|date|after_or_equal:today',
            'items'                    => 'required|array|min:1',
            'items.*.item_id'          => 'required|exists:items,item_id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            // generate unique PO reference
            $poRef = 'PO-' . now()->format('Y') . '-' . str_pad(PurchaseOrder::max('po_id') + 1, 5, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_ref'                  => $poRef,
                'po_status'               => 'ordered',
                'order_date'              => now(),
                'delivery_address'        => $request->delivery_address,
                'expected_delivery_date'  => $request->expected_delivery_date,
                'total_amount'            => 0, // computed below
                'sup_id'                  => $request->sup_id,
                'req_id'                  => $request->req_id,
            ]);

            $total = 0;
            foreach ($request->items as $row) {
                $sub = $row['quantity'] * $row['unit_price'];
                PurchaseItem::create([
                    'po_id'         => $po->po_id,
                    'item_id'       => $row['item_id'],
                    'pi_quantity'   => $row['quantity'],
                    'pi_unit_price' => $row['unit_price'],
                    'pi_subtotal'   => $sub,
                ]);
                $total += $sub;
            }
            $po->update(['total_amount' => $total]);

            // Create notifications for Inventory staff and Supervisors
            $inventoryUsers = DB::table('users')->where('role', 'inventory')->pluck('user_id');
            $supervisors    = DB::table('users')->where('role', 'supervisor')->pluck('user_id');
            $notifyUsers    = $inventoryUsers->merge($supervisors)->unique();
            if ($notifyUsers->count() > 0) {
                $supplierName = optional(DB::table('suppliers')->where('sup_id', $po->sup_id)->first())->sup_name;
                $payloads = [];
                foreach ($notifyUsers as $uid) {
                    $payloads[] = [
                        'notif_title'   => 'Purchase Order Created',
                        'notif_content' => 'PO '.$po->po_ref.' for '.($supplierName ?: 'Supplier')." has been created.",
                        'related_id'    => (string)$po->po_id,
                        'related_type'  => 'PurchaseOrder',
                        'is_read'       => false,
                        'user_id'       => $uid,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
                DB::table('notifications')->insert($payloads);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully.',
                'redirect' => route('purchase_orders.print', $po->po_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating PO: ' . $e->getMessage()
            ], 500);
        }
    }

    public function kpi()
    {
        return response()->json([
            'totalPOs'      => PurchaseOrder::count(),
            'approvedReqs'  => Requisition::approved()->count(),
        ]);
    }

    /* -------------------------------------------------
     * PRINTABLE PDF
     * -------------------------------------------------*/
    public function print(PurchaseOrder $po)
    {
        $po->load(['supplier', 'requisition.requester', 'items.item']);

        $pdf = Pdf::loadView('Purchasing.print_purchase_order', compact('po'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'       => 'DejaVu Sans',
                'isRemoteEnabled'   => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'      => true,
            ]);

        return $pdf->stream($po->po_ref . '.pdf');
    }

    /* -------------------------------------------------
     * SIMPLE API LIST (OPTIONAL)
     * -------------------------------------------------*/
    public function index()
    {
        return PurchaseOrder::with(['supplier', 'requisition'])
            ->latest()
            ->paginate(15);
    }
}
