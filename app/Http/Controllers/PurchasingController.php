<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;      // <-- Main query builder
use Illuminate\Support\Facades\Auth;    // <-- For getting the logged-in user
use Carbon\Carbon;                      // <-- For date manipulation

// --- All "use App\Models\..." imports have been removed ---

class PurchasingController extends Controller
{

    /**
     * Display the purchasing dashboard with all required data.
     */
    public function index()
    {
        // 1. Approved Reqs (The variable that caused the error)
        $approvedReqs = DB::table('requisitions')
            ->where('req_status', 'approved')
            ->whereNotIn('req_id', function ($query) {
                $query->select('req_id')->from('purchase_orders')->whereNotNull('req_id');
            })
            ->count();

        // 2. PO Status Counts
        $draftPOs = DB::table('purchase_orders')->where('po_status', 'draft')->count();
        $orderedPOs = DB::table('purchase_orders')->where('po_status', 'ordered')->count();
        $deliveredPOs = DB::table('purchase_orders')->where('po_status', 'delivered')->count();

        // 3. Quick Alerts: Overdue
        $overdue = DB::table('purchase_orders')
            ->where('po_status', 'ordered')
            ->where('expected_delivery_date', '<', Carbon::today())
            ->get();

        // 3. Quick Alerts: Awaiting Stock-In
        // This query is now correct, because deliveryStore() no longer creates transactions.
        $awaiting = DB::table('purchase_orders as po')
            ->join('memos as m', 'po.po_ref', '=', 'm.po_ref')
            ->where('po.po_status', 'delivered')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('inventory_transactions as it')
                      ->whereRaw('it.po_id = po.po_id');
            })
            ->select('po.po_ref', 'po.total_amount', 'm.received_date as expected_delivery_date')
            ->get();

        // 4. Recent POs
        $recentPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->select('po.*', 's.sup_name')
            ->orderBy('po.created_at', 'desc')
            ->limit(10)
            ->get();
            
        $totalPOs = DB::table('purchase_orders')->count();

        // 5. Supplier Snapshot
        $totalSuppliers = DB::table('suppliers')->count();
        
        $activeSuppliersThisMonth = DB::table('purchase_orders')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->distinct('sup_id')
            ->count('sup_id');

        $topSupplierQty = DB::table('purchase_items as pi')
            ->join('purchase_orders as po', 'pi.po_id', '=', 'po.po_id')
            ->join('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->select('s.sup_name', DB::raw('SUM(pi.pi_quantity) as total_qty'))
            ->groupBy('s.sup_name')
            ->orderByDesc('total_qty')
            ->first();
        
        $topSupplierVal = DB::table('purchase_orders as po')
            ->join('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->select('s.sup_name', DB::raw('SUM(po.total_amount) as total_val'))
            ->groupBy('s.sup_name')
            ->orderByDesc('total_val')
            ->first();

        // 6. PO-related notifications
        $notifs = DB::table('notifications')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Purchasing.dashboard', [
            'approvedReqs' => $approvedReqs,
            'draftPOs' => $draftPOs,
            'orderedPOs' => $orderedPOs,
            'deliveredPOs' => $deliveredPOs,
            'overdue' => $overdue,
            'awaiting' => $awaiting,
            'recentPOs' => $recentPOs,
            'totalPOs' => $totalPOs,
            'totalSuppliers' => $totalSuppliers,
            'activeSuppliersThisMonth' => $activeSuppliersThisMonth,
            'topSupplierQtyName' => $topSupplierQty->sup_name ?? '-',
            'topSupplierValName' => $topSupplierVal->sup_name ?? '-',
            'notifs' => $notifs
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /* PURCHASE ORDER (from Reqs)                           */
    /* -------------------------------------------------------------------------- */

    public function approvedIndex()
    {
        // Data for 'create_purchase_order.blade.php'
        $approvedReqs = DB::table('requisitions as r')
            ->leftJoin('users as u_req', 'r.requested_by', '=', 'u_req.user_id')
            ->leftJoin('users as u_app', 'r.approved_by', '=', 'u_app.user_id')
            ->where('r.req_status', 'approved')
            ->whereNotIn('r.req_id', function ($query) {
                 $query->select('req_id')->from('purchase_orders')->whereNotNull('req_id');
            })
            ->select('r.*', 'u_req.name as requester_name', 'u_app.name as approver_name')
            ->paginate(10); // Using paginate as the blade has total()

        $data = [
            'approvedReqs' => $approvedReqs,
            'draftPOsList' => DB::table('purchase_orders as po')->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')->where('po_status', 'draft')->select('po.*', 's.sup_name')->get(),
            'orderedPOsList' => DB::table('purchase_orders as po')->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')->where('po_status', 'ordered')->select('po.*', 's.sup_name')->get(),
            'approvedReqsCount' => DB::table('requisitions')->where('req_status', 'approved')->count(),
            'pendingPOCount' => $this->index()->getData()['approvedReqs'], // Reuse logic
            'convertedTodayCount' => DB::table('purchase_orders')->whereNotNull('req_id')->whereDate('created_at', Carbon::today())->count(),
            'approvedThisWeekCount' => DB::table('requisitions')->where('req_status', 'approved')->whereBetween('approved_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'overdue' => DB::table('purchase_orders')->where('po_status', 'ordered')->where('expected_delivery_date', '<', Carbon::today())->get(),
            'lowStockItems' => DB::table('items')->where('is_active', true)->whereNotNull('reorder_level')->whereColumn('item_stock', '<=', 'reorder_level')->get(),
        ];
        return view('Purchasing.Purchase.create_purchase_order', $data);
    }

    public function purchaseFromReqs(Request $request)
    {
        $req_ids = $request->input('req_ids', []);
        if (empty($req_ids)) {
            return response()->json(['success' => false, 'message' => 'No requisitions selected'], 400);
        }

        try {
            $po_id = DB::transaction(function () use ($req_ids) {
                // 1. Create the Draft PO with a temporary po_ref
                $po_id = DB::table('purchase_orders')->insertGetId([
                    'po_ref' => 'TEMP-' . time(), // Temporary reference
                    'po_status' => 'draft',
                    'order_date' => Carbon::now(),
                    'delivery_address' => 'N/A (Update in Draft)', // Default
                    'notes' => 'Created from Requisition(s): ' . implode(', ', $req_ids),
                    'total_amount' => 0, // Will be updated later
                    'req_id' => $req_ids[0], // Link to the first req_id
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ], 'po_id');

                // 2. Generate and update the PO-Ref
                $po_ref = 'PO-' . str_pad($po_id, 5, '0', STR_PAD_LEFT);
                DB::table('purchase_orders')->where('po_id', $po_id)->update(['po_ref' => $po_ref]);

                // 3. Get all items from selected requisitions
                $items = DB::table('requisition_items as ri')
                    ->join('items as i', 'ri.item_id', '=', 'i.item_id')
                    ->whereIn('ri.req_id', $req_ids)
                    ->select('ri.item_id', 'i.item_name', 'i.item_unit', DB::raw('SUM(ri.req_item_quantity) as total_quantity'))
                    ->groupBy('ri.item_id', 'i.item_name', 'i.item_unit')
                    ->get();

                // 4. Prepare and insert PO items
                $poItemsData = [];
                foreach ($items as $item) {
                    $poItemsData[] = [
                        'po_id' => $po_id,
                        'item_id' => $item->item_id,
                        'pi_quantity' => $item->total_quantity,
                        'pi_unit_price' => 0.00, // To be filled in by purchasing
                        'pi_subtotal' => 0.00,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                DB::table('purchase_items')->insert($poItemsData);
                
                return $po_id;
            });

            return response()->json(['success' => true, 'po_id' => $po_id, 'message' => 'Draft PO Created!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function purchaseView($id)
    {
        // For 'create-po.blade.php'
        $po = DB::table('purchase_orders')->where('po_id', $id)->first();
        
        if(!$po) {
            abort(404, 'Purchase Order not found');
        }

        // We manually attach the 'items' and 'supplier' objects
        $po->items = DB::table('purchase_items as pi')
            ->leftJoin('items as i', 'pi.item_id', '=', 'i.item_id')
            ->where('pi.po_id', $id)
            ->select('pi.*', 'i.item_name', 'i.item_unit as unit')
            ->get();
            
        $po->supplier = DB::table('suppliers')->where('sup_id', $po->sup_id)->first();

        $data = [
            'purchaseOrder' => $po,
            'suppliers' => DB::table('suppliers')->where('sup_status', 'active')->get(),
        ];
        return view('Purchasing.Purchase.create-po', $data);
    }

    public function purchaseUpdate(Request $request, $id)
    {
        // This handles "Save Draft" or "Submit" from 'create-po.blade.php'
        $action = $request->input('action'); // 'save' or 'submit'
        
        try {
            DB::transaction(function () use ($request, $id, $action) {
                $poData = [
                    'sup_id' => $request->input('sup_id'),
                    'expected_delivery_date' => $request->input('expected_delivery_date'),
                    'delivery_address' => $request->input('delivery_address'),
                    'updated_at' => Carbon::now(),
                ];

                if ($action === 'submit') {
                    $poData['po_status'] = 'ordered';
                    $poData['order_date'] = Carbon::now(); // Set order date on submit
                }

                $total_amount = 0;
                $items = $request->input('items', []);

                foreach ($items as $itemData) {
                    $unit_price = (float)($itemData['unit_price'] ?? 0);
                    
                    // We need to get the quantity from the DB, as it's not on the form
                    // This now correctly uses 'pi_id' from the hidden form input
                    $pi_quantity = DB::table('purchase_items')
                                    ->where('pi_id', $itemData['pi_id']) // <-- CORRECTED
                                    ->value('pi_quantity');
                    
                    $lineTotal = (float)$pi_quantity * $unit_price;
                    $total_amount += $lineTotal;

                    DB::table('purchase_items')
                        ->where('pi_id', $itemData['pi_id']) // <-- CORRECTED
                        ->where('po_id', $id) // <-- Safety check
                        ->update([
                            'pi_unit_price' => $unit_price,
                            'pi_subtotal' => $lineTotal,
                            'updated_at' => Carbon::now()
                        ]);
                }

                // Now update the PO with the calculated total
                $poData['total_amount'] = $total_amount;
                DB::table('purchase_orders')->where('po_id', $id)->update($poData);
            });

            return redirect()->route('purchasing.purchase.view', $id)->with('success', 'Purchase Order updated!');
        } catch (\Exception $e) {
            return redirect()->route('purchasing.purchase.view', $id)->with('error', $e->getMessage());
        }
    }

    public function purchaseDestroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Only allow deleting 'draft' POs
                $deletedPO = DB::table('purchase_orders')->where('po_id', $id)->where('po_status', 'draft')->delete();

                if ($deletedPO) {
                    // Delete associated items
                    DB::table('purchase_items')->where('po_id', $id)->delete();
                } else {
                    throw new \Exception('PO not found or is not a draft.');
                }
            });
            return redirect()->route('purchasing.approved.index')->with('success', 'Draft PO deleted.');
        } catch (\Exception $e) {
            return redirect()->route('purchasing.approved.index')->with('error', $e->getMessage());
        }
    }

    public function purchasePrint($id)
    {
        $po = DB::table('purchase_orders')->where('po_id', $id)->first();
        if(!$po) {
            abort(404);
        }

        $po->items = DB::table('purchase_items as pi')
            ->leftJoin('items as i', 'pi.item_id', '=', 'i.item_id')
            ->where('pi.po_id', $id)
            ->select('pi.pi_id', 'pi.item_id', 'pi.po_id', 'pi.pi_quantity as quantity', 'pi.pi_unit_price as unit_price', 'pi.pi_subtotal as lineTotal', 'i.item_name', 'i.item_unit as unit')
            ->get();
            
        $po->supplier = DB::table('suppliers')->where('sup_id', $po->sup_id)->first();

        // The print template requires 'creator' and 'approver' which are not
        // in your 'purchase_orders' table. We'll set them to a default object.
        $po->creator = (object)['name' => 'N/A'];
        $po->approver = (object)['name' => 'N/A'];
        
        // The print template also requires $requestorNames
        $requestorNames = 'N/A';
        if ($po->req_id) {
             $req = DB::table('requisitions as r')
                ->join('users as u', 'r.requested_by', 'u.user_id')
                ->where('r.req_id', $po->req_id)
                ->select('u.name')
                ->first();
             if($req) {
                $requestorNames = $req->name;
             }
        }

        $data = [
            'purchaseOrder' => $po,
            'requestorNames' => $requestorNames,
        ];
        return view('Purchasing.Purchase.print', $data);
    }

    /* -------------------------------------------------------------------------- */
    /* SUPPLIER MANAGEMENT                           */
    /* -------------------------------------------------------------------------- */

    public function suppliersIndex()
    {
        $suppliers = DB::table('suppliers')
            ->leftJoin(DB::raw('(SELECT sup_id, COUNT(*) as po_count FROM purchase_orders GROUP BY sup_id) as po'), 'suppliers.sup_id', '=', 'po.sup_id')
            ->select('suppliers.*', DB::raw('COALESCE(po.po_count, 0) as po_count'))
            ->paginate(10);

        return view('Purchasing.Supplier.supplier', ['suppliers' => $suppliers]);
    }

    public function suppliersStore(Request $request)
    {
        $payload = $request->validate([
            'sup_name' => 'required|string|max:255',
            'sup_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'sup_address' => 'nullable|string',
            'sup_status' => 'required|string',
        ]);
        
        $payload['created_at'] = Carbon::now();
        $payload['updated_at'] = Carbon::now();

        DB::table('suppliers')->insert($payload);
        return response()->json(['success' => true]);
    }

    public function suppliersShow($id)
    {
        $supplier = DB::table('suppliers')->where('sup_id', $id)->first();
        if ($supplier) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }
        return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
    }

    public function suppliersUpdate(Request $request, $id)
    {
        $payload = $request->validate([
            'sup_name' => 'required|string|max:255',
            'sup_email' => 'nullable|email|max:255',
            'contact_number' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'sup_address' => 'nullable|string',
        ]);
        $payload['updated_at'] = Carbon::now();

        DB::table('suppliers')->where('sup_id', $id)->update($payload);
        return response()->json(['success' => true]);
    }

    public function suppliersToggleStatus($id)
    {
        $sup = DB::table('suppliers')->where('sup_id', $id)->first();
        if ($sup) {
            $newStatus = ($sup->sup_status == 'active') ? 'inactive' : 'active';
            DB::table('suppliers')->where('sup_id', $id)->update(['sup_status' => $newStatus]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
    }

    public function suppliersPOs($id)
    {
        $pos = DB::table('purchase_orders')
            ->where('sup_id', $id)
            ->select('po_ref', 'po_status', 'total_amount', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['success' => true, 'pos' => $pos]);
    }


    /* -------------------------------------------------------------------------- */
    /* DELIVERY MEMOS                              */
    /* -------------------------------------------------------------------------- */

    public function memoIndex()
    {
        // This query is for the top table ('Ordered Purchase Orders')
        $orderedPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->leftJoin(DB::raw('(SELECT po_id, COUNT(*) as items_count FROM purchase_items GROUP BY po_id) as pi'), 'po.po_id', '=', 'pi.po_id')
            ->where('po.po_status', 'ordered')
            ->select('po.*', 's.sup_name', DB::raw('COALESCE(pi.items_count, 0) as items_count'))
            ->paginate(10);

        // This query is for the bottom table ('Recent Delivery Memos')
        $recentMemos = DB::table('memos as m')
            ->leftJoin('purchase_orders as po', 'm.po_ref', '=', 'po.po_ref')
            ->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->select('m.*', 'po.po_ref', 's.sup_name')
            ->orderBy('m.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('Purchasing.Memo.memo', [
            'orderedPOs' => $orderedPOs,
            'recentMemos' => $recentMemos
        ]);
    }

    public function memoShow($ref)
    {
        $memo = DB::table('memos')->where('memo_ref', $ref)->first();
        if (!$memo) {
            abort(404);
        }
        
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 'po.sup_id', 's.sup_id')
            ->where('po.po_ref', $memo->po_ref)
            ->select('po.*', 's.sup_name')
            ->first();

        $items = DB::table('purchase_items as pi')
            ->join('items as i', 'pi.item_id', 'i.item_id')
            ->where('pi.po_id', $po->po_id)
            ->select('i.item_name', 'i.item_unit as unit', 'pi.pi_quantity as ordered_qty', 'pi.pi_unit_price as unit_price')
            ->get();
            
        $received_by_name = DB::table('users')->where('user_id', $memo->received_by)->value('name');

        return view('Purchasing.Memo.show', [
            'memo' => $memo,
            'po'   => $po,
            'items' => $items,
            'received_by_name' => $received_by_name ?? 'N/A'
        ]);
    }

    public function deliveryShow($id)
    {
        // For 'record.blade.php' modal
        $po = DB::table('purchase_orders')->where('po_id', $id)->first();
        $items = DB::table('purchase_items as pi')
            ->join('items as i', 'pi.item_id', 'i.item_id')
            ->where('pi.po_id', $id)
            ->select('pi.item_id', 'i.item_name', 'i.item_unit as unit', 'pi.pi_quantity as ordered_qty')
            ->get();
            
        return response()->json(['success' => true, 'po' => $po, 'items' => $items]);
    }

    public function deliveryStore(Request $request, $id)
    {
        // ** THIS IS THE CORRECTED LOGIC **
        // 1. Create Memo
        // 2. Update PO Status
        //
        // It NO LONGER creates inventory transactions or updates stock.
        // That is the Inventory module's job.
        
        $po = DB::table('purchase_orders')->where('po_id', $id)->first();
        if (!$po || $po->po_status !== 'ordered') {
             return response()->json(['success' => false, 'message' => 'PO not found or is not in "Ordered" status.'], 400);
        }

        try {
            DB::transaction(function () use ($request, $id, $po) {
                // 1. Create the Memo
                DB::table('memos')->insert([
                    'memo_ref' => $request->input('memo_ref'),
                    'memo_remarks' => $request->input('remarks'),
                    'received_date' => $request->input('received_date'),
                    'received_by' => Auth::id(), // Use logged in user
                    'po_ref' => $po->po_ref,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // 2. Update PO Status
                DB::table('purchase_orders')->where('po_id', $id)->update([
                    'po_status' => 'delivered',
                    'updated_at' => Carbon::now()
                ]);

                // --- REMOVED ---
                // The following logic has been removed.
                // The Inventory module will now handle stock-in by looking
                // for POs with a 'delivered' status.
                //
                // $items = $request->input('items', []);
                // foreach ($items as $item) { ... }
                // ---
            });

            return response()->json(['success' => true, 'message' => 'Delivery recorded! Ready for stock-in.']);

        } catch (\Exception $e) {
            // Check for unique constraint violation on memo_ref
            if(str_contains($e->getMessage(), 'memos_memo_ref_unique')) {
                 return response()->json(['success' => false, 'message' => 'Error: Memo Reference already exists. Please use a unique reference.'], 400);
            }
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /* -------------------------------------------------------------------------- */
    /* REPORTS                                  */
    /* -------------------------------------------------------------------------- */

    public function reportIndex()
    {
        $recentPOs = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 'po.sup_id', '=', 's.sup_id')
            ->where('po.po_status', '!=', 'draft')
            ->select('po.*', 's.sup_name')
            ->orderBy('po.created_at', 'desc')
            ->paginate(5); // Blade uses total() so paginate is needed

        return view('Purchasing.report', ['recentPOs' => $recentPOs]);
    }

    public function reportGenerate($type)
    {
        $title = 'Report';
        $html = '<p>Report data not available.</p>';

        // This is a stub - you would build complex queries here
        switch ($type) {
            case 'po-by-supplier':
                $title = 'Purchase Orders by Supplier';
                $data = DB::table('purchase_orders as po')
                        ->join('suppliers as s', 'po.sup_id', 's.sup_id')
                        ->select('s.sup_name', DB::raw('COUNT(*) as po_count'), DB::raw('SUM(po.total_amount) as total_value'))
                        ->groupBy('s.sup_name')
                        ->get();
                $html = $this->buildHtmlTable($data);
                break;
            case 'po-by-status':
                $title = 'Purchase Orders by Status';
                $data = DB::table('purchase_orders')
                        ->select('po_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_value'))
                        ->groupBy('po_status')
                        ->get();
                $html = $this->buildHtmlTable($data);
                break;
            case 'delivery-performance':
                $title = 'Delivery Performance';
                $on_time = DB::table('purchase_orders as po')
                         ->join('memos as m', 'po.po_ref', 'm.po_ref')
                         ->where('po.expected_delivery_date', '>=', 'm.received_date')
                         ->count();
                $overdue = DB::table('purchase_orders as po')
                         ->join('memos as m', 'po.po_ref', 'm.po_ref')
                         ->where('po.expected_delivery_date', '<', 'm.received_date')
                         ->count();
                $html = "<p>On-Time Deliveries: <strong>$on_time</strong></p><p>Overdue Deliveries: <strong>$overdue</strong></p>";
                break;
            case 'monthly-spend':
                $title = 'Monthly Spend';
                $data = DB::table('purchase_orders')
                        ->where('po_status', '!=', 'draft')
                        ->select(DB::raw("TO_CHAR(order_date, 'YYYY-MM') as month"), DB::raw('SUM(total_amount) as total_spend'))
                        ->groupBy('month')
                        ->orderBy('month', 'desc')
                        ->get();
                $html = $this->buildHtmlTable($data);
                break;
        }

        return response()->json(['title' => $title, 'html' => $html]);
    }

    // Helper function to build HTML table for reports
    private function buildHtmlTable($data)
    {
        if ($data->isEmpty()) {
            return '<p>No data found for this report.</p>';
        }
        $html = '<table class="w-full text-sm border-collapse border border-gray-300">';
        // Header
        $html .= '<thead class="bg-gray-100"><tr class="border-b border-gray-300">';
        foreach (array_keys((array)$data[0]) as $key) {
            $html .= '<th class="px-4 py-2 text-left font-semibold">' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</th>';
        }
        $html .= '</tr></thead>';
        // Body
        $html .= '<tbody class="divide-y divide-gray-200">';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td class="px-4 py-2">' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }


    /* -------------------------------------------------------------------------- */
    /* NOTIFICATIONS                               */
    /* -------------------------------------------------------------------------- */

    public function notificationsIndex()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', auth()->id())
            // ->orWhereNull('user_id') // Uncomment to include global notifications
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('Purchasing.Notification.notification', ['notifications' => $notifications]);
    }

    public function notificationsView($id)
    {
        $notif = DB::table('notifications')
            ->where('notif_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notif) {
             return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        
        // Mark as read when viewed
        DB::table('notifications')->where('notif_id', $id)->update(['is_read' => true]);

        // Simple logic for jump_url
        $jump_url = '#';
        if($notif->related_type == 'purchase_order' && $notif->related_id) {
            // Your DB schema shows related_id is a string, so we'll check against po_ref
             $po_id = DB::table('purchase_orders')->where('po_ref', $notif->related_id)->value('po_id');
             if ($po_id) {
                 $jump_url = route('purchasing.purchase.view', $po_id);
             }
        }
        // ... add more types as needed

        return response()->json([
            'notif_title' => $notif->notif_title,
            'module' => $notif->related_type ? ucwords(str_replace('_', ' ', $notif->related_type)) : 'System',
            'notif_content' => $notif->notif_content,
            'created_at' => Carbon::parse($notif->created_at)->format('M d, Y H:i A'),
            'jump_url' => $jump_url,
            'is_read' => true,
        ]);
    }

    public function notificationsMarkRead($id)
    {
        DB::table('notifications')
            ->where('notif_id', $id)
            ->where('user_id', auth()->id())
            ->update(['is_read' => true]);
            
        return response()->json(['success' => true]);
    }

    public function notificationsMarkAllRead()
    {
        DB::table('notifications')
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json(['success' => true]);
    }

    public function notificationsJump($id)
    {
        $notif = DB::table('notifications')
            ->where('notif_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notif) {
            return redirect()->route('purchasing.dashboard')->with('error', 'Notification not found.');
        }

        // Mark as read
        if (!$notif->is_read) {
             DB::table('notifications')->where('notif_id', $id)->update(['is_read' => true]);
        }

        // Redirect logic
        if($notif->related_type == 'purchase_order' && $notif->related_id) {
             // Check if PO exists
             $po_id = DB::table('purchase_orders')->where('po_ref', $notif->related_id)->value('po_id');
             if ($po_id) {
                 return redirect()->route('purchasing.purchase.view', $po_id);
             }
        }
        // ... add more redirect logic for suppliers, memos, etc. ...

        // Fallback redirect
        return redirect()->route('purchasing.notifications');
    }
    /* -------------------------------------------------------------------------- */
    /* REQUISITION DETAILS FOR VIEW MODAL                        */
    /* -------------------------------------------------------------------------- */

    public function requisitionsShow($id)
    {
        // Get the requisition details with related data
        $requisition = DB::table('requisitions as r')
            ->leftJoin('users as u_req', 'r.requested_by', '=', 'u_req.user_id')
            ->leftJoin('users as u_app', 'r.approved_by', '=', 'u_app.user_id')
            ->where('r.req_id', $id)
            ->select(
                'r.*',
                'u_req.name as requester_name',
                'u_app.name as approver_name'
            )
            ->first();

        if (!$requisition) {
            return response()->json(['success' => false, 'message' => 'Requisition not found'], 404);
        }

        // Get the requisition items with item details
        $items = DB::table('requisition_items as ri')
            ->leftJoin('items as i', 'ri.item_id', '=', 'i.item_id')
            ->where('ri.req_id', $id)
            ->select(
                'ri.*',
                'i.item_name',
                'i.item_unit',
                'i.item_stock'
            )
            ->get();

        // Format the data for the frontend with null checks
        $data = [
            'req_id' => $requisition->req_id ?? null,
            'req_ref' => $requisition->req_ref ?? null,
            'req_purpose' => $requisition->req_purpose ?? null,
            'req_priority' => $requisition->req_priority ?? null,
            'req_status' => $requisition->req_status ?? null,
            'requester_name' => $requisition->requester_name ?? null,
            'approver_name' => $requisition->approver_name ?? null,
            'approved_date' => $requisition->approved_date ?? null,
            'req_remarks' => $requisition->req_remarks ?? null,
            'items' => $items->map(function($item) {
                return [
                    'item_name' => $item->item_name ?? 'Unknown Item',
                    'item_unit' => $item->item_unit ?? '',
                    'req_item_quantity' => $item->req_item_quantity ?? 0,
                    'item_stock' => $item->item_stock ?? null,
                    'req_item_status' => $item->req_item_status ?? 'pending'
                ];
            })->toArray()
        ];

        return response()->json($data);
    }
}