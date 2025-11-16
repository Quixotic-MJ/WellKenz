<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Still needed for transactions
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Item;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\AcknowledgeReceipt;
use Carbon\Carbon;

class StaffController extends Controller
{
    /**
     * Show the staff (employee) dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $userName = $user->name;

        // Personal requisition metrics
        $myReqTotal    = $user->requisitions()->count();
        $myReqPending  = $user->requisitions()->where('req_status', 'pending')->count();
        $myReqApproved = $user->requisitions()->where('req_status', 'approved')->count();
        $myReqRejected = $user->requisitions()->where('req_status', 'rejected')->count();

        // Item requests awaiting supervisor approval (for this user)
        $pendingItems = $user->itemRequests()
            ->where('item_req_status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Personal notifications (latest 6)
        $notifs = Notification::forUser($user->user_id)
            ->orderByDesc('created_at')
            ->limit(6)->get();

        // Low stock alerts (read-only preview)
        $lowStock = Item::whereColumn('item_stock', '<=', 'reorder_level')
            ->orderBy('item_name')
            ->limit(5)->get();

        // Recent requisitions for this user
        $recReqs = $user->requisitions()
            ->orderByDesc('created_at')
            ->limit(6)->get();

        return view('Employee.dashboard', compact(
            'userName', 'myReqTotal', 'myReqPending', 'myReqApproved', 'myReqRejected', 'pendingItems', 'notifs', 'lowStock', 'recReqs'
        ));
    }

    // ===== Requisitions (Employee) =====
    public function requisitionsIndex()
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->user_id;

        $totalCount    = $user->requisitions()->count();
        $pendingCount  = $user->requisitions()->where('req_status', 'pending')->count();
        $approvedCount = $user->requisitions()->where('req_status', 'approved')->count();
        $rejectedCount = $user->requisitions()->where('req_status', 'rejected')->count();

        // withCount('items') automatically adds 'items_count' to each requisition
        $requisitions = $user->requisitions()
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return view('Employee.Requisition.my_requisition', compact('totalCount', 'pendingCount', 'approvedCount', 'rejectedCount', 'requisitions'));
    }

    public function requisitionsCreate()
    {
        /** @var User $user */
        $user = Auth::user();

        $items = Item::select('item_id', 'item_name', 'item_unit', 'item_stock')->orderBy('item_name')->get();

        // Requisitions data for history table
        $reqTotal    = $user->requisitions()->count();
        $reqPending  = $user->requisitions()->where('req_status', 'pending')->count();
        $reqApproved = $user->requisitions()->where('req_status', 'approved')->count();
        $reqRejected = $user->requisitions()->where('req_status', 'rejected')->count();

        $requisitions = $user->requisitions()
            ->select('req_id', 'req_ref', 'req_status', 'created_at') // Only select what's needed
            ->orderByDesc('created_at')
            ->get();

        return view('Employee.Requisition.create_requisition', compact('items', 'reqTotal', 'reqPending', 'reqApproved', 'reqRejected', 'requisitions'));
    }

    public function requisitionsStore(Request $request)
    {
        $data = $request->validate([
            'req_purpose'  => 'required|string|max:255',
            'req_priority' => 'required|in:low,medium,high',
            'items'        => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        // Use a database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // 1. Create the Requisition
            // The Requisition model's 'boot' method will automatically handle:
            // - req_status ('pending')
            // - req_date (now)
            // - requested_by (Auth::id())
            // - req_ref (on a separate update, e.g., 'RQ-00005')
            $requisition = Requisition::create([
                'req_purpose'  => $data['req_purpose'],
                'req_priority' => $data['req_priority'],
            ]);

            // 2. Prepare and Insert the Requisition Items
            
            // Get all item units in one query to avoid N+1 queries
            $itemUnits = Item::whereIn('item_id', array_column($data['items'], 'item_id'))
                             ->pluck('item_unit', 'item_id');

            $itemsToInsert = [];
            foreach ($data['items'] as $row) {
                $itemsToInsert[] = [
                    'req_id'            => $requisition->req_id,
                    'item_id'           => $row['item_id'],
                    'req_item_quantity' => $row['quantity'],
                    'item_unit'         => $itemUnits[$row['item_id']] ?? 'unit',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            
            // Insert all items in a single query
            RequisitionItem::insert($itemsToInsert);

            DB::commit();

            $message = 'Requisition submitted successfully';
            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : redirect()->route('staff.requisitions.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Requisition store error: ' . $e->getMessage());

            $message = 'Failed to submit requisition: ' . $e->getMessage();
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->withInput()->with('error', $message);
        }
    }

    public function requisitionsShow($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Eager load the items and the item details
        $req = $user->requisitions()->with('items.item')->find($id);

        if (!$req) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Re-format items to match your old structure
        $formattedItems = $req->items->map(fn($ri) => [
            'item' => ['item_name' => $ri->item->item_name],
            'req_item_quantity' => $ri->req_item_quantity,
            'item_unit' => $ri->item_unit,
        ]);

        return response()->json([
            'req_ref' => $req->req_ref,
            'req_status' => $req->req_status,
            'req_priority' => $req->req_priority,
            'created_at' => $req->created_at->toDateTimeString(), // Use casts in model
            'req_purpose' => $req->req_purpose,
            'req_reject_reason' => $req->req_reject_reason ?? null,
            'items' => $formattedItems,
        ]);
    }

    public function requisitionsEdit($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $requisition = $user->requisitions()->with('items.item')->find($id);
        
        if (!$requisition) {
            abort(404);
        }
        
        $items = Item::select('item_id', 'item_name', 'item_unit', 'item_stock')->orderBy('item_name')->get();
        
        return view('Employee.Requisition.edit', compact('requisition', 'items'));
    }

    public function requisitionsUpdate(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $requisition = $user->requisitions()
            ->where('req_id', $id)
            ->where('req_status', 'pending')
            ->first();

        if (!$requisition) {
            return back()->with('error', 'Cannot update this requisition');
        }

        $data = $request->validate([
            'req_purpose'  => 'required|string|max:255',
            'req_priority' => 'required|in:low,medium,high',
            'items'        => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.quantity'=> 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $requisition->update([
                'req_purpose'  => $data['req_purpose'],
                'req_priority' => $data['req_priority'],
            ]);
            
            // reset items
            $requisition->items()->delete();

            // Same logic as store
            $itemUnits = Item::whereIn('item_id', array_column($data['items'], 'item_id'))
                             ->pluck('item_unit', 'item_id');
            $itemsToInsert = [];
            foreach ($data['items'] as $row) {
                $itemsToInsert[] = [
                    'req_id'            => $id,
                    'item_id'           => $row['item_id'],
                    'req_item_quantity' => $row['quantity'],
                    'item_unit'         => $itemUnits[$row['item_id']] ?? 'unit',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            RequisitionItem::insert($itemsToInsert);
            
            DB::commit();

            return redirect()->route('staff.requisitions.index')->with('success', 'Requisition updated');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Requisition update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update requisition: ' . $e->getMessage());
        }
    }

    public function requisitionsDestroy(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $requisition = $user->requisitions()
            ->where('req_id', $id)
            ->where('req_status', 'pending')
            ->first();
        
        if (!$requisition) {
            $message = 'Cannot delete this requisition';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }
        
        // The database schema has ON DELETE CASCADE,
        // so deleting the requisition will also delete its items.
        $requisition->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('staff.requisitions.index')->with('success', 'Requisition cancelled');
    }

    // ===== Item Requests (Employee) =====
    public function itemRequestsIndex()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $totalCount    = $user->itemRequests()->count();
        $pendingCount  = $user->itemRequests()->where('item_req_status', 'pending')->count();
        $approvedCount = $user->itemRequests()->where('item_req_status', 'approved')->count();
        $rejectedCount = $user->itemRequests()->where('item_req_status', 'rejected')->count();

        $requests = $user->itemRequests()->orderByDesc('created_at')->get();

        return view('Employee.Item_Request.item_request', compact('totalCount', 'pendingCount', 'approvedCount', 'rejectedCount', 'requests'));
    }

    public function itemRequestsStore(Request $request)
    {
        $data = $request->validate([
            'item_req_name'        => 'required|string|max:255',
            'item_req_unit'        => 'required|string|max:255',
            'item_req_quantity'    => 'required|integer|min:1',
            'item_req_description' => 'required|string',
        ]);

        Auth::user()->itemRequests()->create($data); // Eloquent handles the rest

        return redirect()->back()->with('success', 'Request submitted');
    }

    public function itemRequestsShow($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $req = $user->itemRequests()->with(['requester', 'approver'])->find($id);

        if (!$req) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'item_req_name' => $req->item_req_name,
            'item_req_unit' => $req->item_req_unit,
            'item_req_quantity' => $req->item_req_quantity,
            'item_req_status' => $req->item_req_status,
            'item_req_description' => $req->item_req_description,
            'item_req_reject_reason' => $req->item_req_reject_reason ?? null,
            'requester' => ['name' => $req->requester->name],
            'approver' => $req->approver ? ['name' => $req->approver->name] : null,
            'created_at' => $req->created_at->toDateTimeString(),
            'updated_at' => $req->updated_at->toDateTimeString(),
        ]);
    }

    public function itemRequestsEdit($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $requestRow = $user->itemRequests()->find($id);
        
        if (!$requestRow) {
            abort(404);
        }
        return view('Employee.Item_Request.edit', ['request' => $requestRow]);
    }

    public function itemRequestsUpdate(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $itemRequest = $user->itemRequests()
            ->where('item_req_id', $id)
            ->where('item_req_status', 'pending')
            ->first();

        if (!$itemRequest) {
            return back()->with('error', 'Cannot update this request');
        }

        $data = $request->validate([
            'item_req_name'        => 'required|string|max:255',
            'item_req_unit'        => 'required|string|max:255',
            'item_req_quantity'    => 'required|integer|min:1',
            'item_req_description' => 'required|string',
        ]);

        $itemRequest->update($data);

        return redirect()->route('staff.item-requests.index')->with('success', 'Request updated');
    }

    public function itemRequestsCancel(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);
        
        /** @var User $user */
        $user = Auth::user();

        $updated = $user->itemRequests()
            ->where('item_req_id', $data['id'])
            ->where('item_req_status', 'pending')
            ->update(['item_req_status' => 'cancelled']);

        return redirect()->back()->with($updated ? 'success' : 'error', $updated ? 'Request cancelled' : 'Unable to cancel');
    }

    // ===== AR (Acknowledgement Receipts) =====
    public function arIndex()
    {
        /** @var User $user */
        $user = Auth::user();

        $receipts = $user->receivedReceipts()
            ->with(['issuer', 'requisition'])
            ->orderByDesc('created_at')
            ->get();

        $totalCount     = $receipts->count();
        $issuedCount    = $receipts->where('ar_status', 'issued')->count();
        $receivedCount  = $receipts->where('ar_status', 'received')->count();
        $thisMonthCount = $receipts->whereBetween('issued_date', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return view('Employee.AR.acknowledgement_receipt', compact('receipts', 'totalCount', 'issuedCount', 'receivedCount', 'thisMonthCount'));
    }

    public function arConfirm(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);
        
        /** @var User $user */
        $user = Auth::user();

        $updated = $user->receivedReceipts()
            ->where('ar_id', $data['id'])
            ->where('ar_status', 'issued')
            ->update(['ar_status' => 'received']);
            
        return redirect()->back()->with($updated ? 'success' : 'error', $updated ? 'Receipt confirmed successfully' : 'Unable to confirm receipt');
    }

    public function arShow($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $ar = $user->receivedReceipts()->with('issuer')->find($id);

        if (!$ar) {
            return response()->json(['error' => 'Acknowledgement receipt not found'], 404);
        }

        // Get related items
        // This logic is a bit complex, but aims to find items delivered for this AR's requisition
        $items = DB::table('inventory_transactions as it')
            ->join('purchase_orders as po', 'po.po_id', '=', 'it.po_id')
            ->join('items as i', 'i.item_id', '=', 'it.item_id')
            ->where('po.req_id', $ar->req_id)
            ->where('it.trans_type', 'in')
            ->select('i.item_name', 'i.item_unit', 'it.trans_quantity', 'it.trans_date')
            ->get();

        return response()->json([
            'ar' => [
                'ar_ref'         => $ar->ar_ref,
                'ar_status'      => $ar->ar_status,
                'issued_date'    => $ar->issued_date->toDateString(),
                'issued_by_name' => $ar->issuer->name,
                'ar_remarks'     => $ar->ar_remarks,
            ],
            'items' => $items
        ]);
    }

    public function arPrint($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $ar = $user->receivedReceipts()
            ->with(['issuer', 'receiver', 'requisition'])
            ->find($id);

        if (!$ar) {
            abort(404, 'Acknowledgement receipt not found');
        }

        // Get related items (same logic as arShow)
        $items = DB::table('inventory_transactions as it')
            ->join('items as i', 'i.item_id', '=', 'it.item_id')
            ->join('purchase_orders as po', 'po.po_id', '=', 'it.po_id')
            ->where('po.req_id', $ar->req_id)
            ->where('it.trans_type', 'in')
            ->select('i.item_name', 'it.trans_quantity', 'it.trans_type', 'it.trans_date', 'i.item_unit')
            ->get();
            
        return view('Employee.AR.print_view', compact('ar', 'items'));
    }

    // ===== Notifications =====
    public function notificationsIndex()
    {
        $userId = Auth::id();
        $query = Notification::forUser($userId);
        
        $totalCount = (clone $query)->count();
        $unreadCount = (clone $query)->where('is_read', false)->count();
        $readCount = $totalCount - $unreadCount;
        $thisWeekCount = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        
        $notifications = $query->orderByDesc('created_at')->paginate(20);

        return view('Employee.Notification.notification', compact('notifications', 'totalCount', 'unreadCount', 'readCount', 'thisWeekCount'));
    }

    public function notificationsMarkAllRead()
    {
        $userId = Auth::id();
        Notification::where('user_id', $userId)->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // ===== Print Requisition =====
    public function printRequisition($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $req = $user->requisitions()->find($id);

        if (!$req) {
            abort(404);
        }
        
        $items = DB::table('requisition_items as ri')
            ->join('items as i', 'i.item_id', '=', 'ri.item_id')
            ->where('ri.req_id', $id)
            ->select('ri.req_item_quantity', 'ri.item_unit', 'i.item_name')
            ->get();
            
        return view('Employee.Requisition.print', compact('req', 'items'));
    }
}