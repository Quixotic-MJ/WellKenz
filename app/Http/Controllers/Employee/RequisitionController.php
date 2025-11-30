<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Notification;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCreateRequisition()
    {
        $user = Auth::user();

        $items = Item::where('is_active', true)
            ->with(['unit', 'category', 'currentStockRecord'])
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                if ($currentStock <= 0) { $stockStatus = 'Out of Stock'; $stockClass = 'text-red-600'; }
                elseif ($currentStock <= $item->reorder_point) { $stockStatus = 'Low'; $stockClass = 'text-amber-500'; }
                elseif ($currentStock <= ($item->min_stock_level * 1.5)) { $stockStatus = 'OK'; $stockClass = 'text-blue-600'; }
                else { $stockStatus = 'High'; $stockClass = 'text-green-600'; }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'description' => $item->description,
                    'category' => $item->category ? $item->category->name : 'Uncategorized',
                    'unit' => $item->unit ? $item->unit->symbol : 'pcs',
                    'unit_name' => $item->unit ? $item->unit->name : 'piece',
                    'current_stock' => $currentStock,
                    'stock_status' => $stockStatus,
                    'stock_class' => $stockClass,
                    'cost_price' => $item->cost_price ?? 0,
                    'reorder_point' => $item->reorder_point ?? 0,
                    'min_stock_level' => $item->min_stock_level ?? 0,
                    'max_stock_level' => $item->max_stock_level ?? 0
                ];
            });

        $categories = Category::where('is_active', true)
            ->withCount(['items' => function($query) { $query->where('is_active', true); }])
            ->orderBy('name')
            ->get();

        $department = $user->profile->department ?? 'Production';

        return view('Employee.requisition.create', compact('items', 'categories', 'department'));
    }

    public function createRequisition(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_items' => 'required|json',
                'purpose' => 'required|string|max:255',
                'department' => 'required|string|max:100'
            ]);

            $cartItems = json_decode($validated['cart_items'], true);
            if (!$cartItems || count($cartItems) === 0) {
                return redirect()->back()->withInput()->withErrors(['cart_items' => 'Please add at least one item to your requisition.']);
            }

            foreach ($cartItems as $itemData) {
                if (!isset($itemData['id']) || !isset($itemData['quantity'])) {
                    return redirect()->back()->withInput()->withErrors(['cart_items' => 'Invalid cart data. Please try again.']);
                }
                $item = Item::where('id', $itemData['id'])->where('is_active', true)->first();
                if (!$item) { return redirect()->back()->withInput()->withErrors(['cart_items' => "Item with ID {$itemData['id']} is not available for requisition."]); }
                if ($itemData['quantity'] <= 0) {
                    return redirect()->back()->withInput()->withErrors(['cart_items' => "Invalid quantity for item: {$item->name}."]);
                }
            }

            $requisitionNumber = 'REQ-' . date('Y') . '-' . str_pad((Requisition::count() + 1), 4, '0', STR_PAD_LEFT);

            $requisition = Requisition::create([
                'requisition_number' => $requisitionNumber,
                'request_date' => Carbon::now(),
                'requested_by' => Auth::id(),
                'department' => $validated['department'],
                'purpose' => $validated['purpose'],
                'status' => 'pending'
            ]);

            $totalEstimatedValue = 0;
            foreach ($cartItems as $itemData) {
                $item = Item::find($itemData['id']);
                $quantity = $itemData['quantity'];
                $estimatedCost = $item->cost_price * $quantity;
                $totalEstimatedValue += $estimatedCost;
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_id' => $itemData['id'],
                    'quantity_requested' => $quantity,
                    'unit_cost_estimate' => $item->cost_price,
                    'total_estimated_value' => $estimatedCost
                ]);
            }

            $requisition->update(['total_estimated_value' => $totalEstimatedValue]);

            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'New Requisition Submitted',
                    'message' => "New requisition {$requisitionNumber} has been submitted by " . Auth::user()->name . " for approval.",
                    'type' => 'approval_req',
                    'priority' => 'normal',
                    'action_url' => route('supervisor.requisitions.details', $requisition->id),
                    'metadata' => [
                        'requisition_number' => $requisitionNumber,
                        'requisition_id' => $requisition->id,
                        'requester' => Auth::user()->name,
                        'department' => $validated['department'],
                        'total_estimated_value' => $totalEstimatedValue,
                        'purpose' => $validated['purpose']
                    ],
                    'created_at' => Carbon::now()
                ]);
            }

            return redirect()->route('employee.requisitions.history')
                ->with('success', "Requisition {$requisitionNumber} created successfully and sent for approval.")
                ->with('new_requisition', true);

        } catch (\Exception $e) {
            Log::error('Requisition creation failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['cart_items' => 'An error occurred while creating your requisition. Please try again.']);
        }
    }

    public function requisitionHistory(Request $request)
    {
        $isNewRequisition = session('new_requisition', false);
        $query = Requisition::where('requested_by', Auth::id())
            ->with(['requisitionItems' => function($query) { $query->with('item.unit'); }, 'approvedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('requisition_number', 'ilike', "%{$search}%")
                  ->orWhereHas('requisitionItems', function($itemQuery) use ($search) {
                      $itemQuery->whereHas('item', function($itemQ) use ($search) {
                          $itemQ->where('name', 'ilike', "%{$search}%")
                                ->orWhere('item_code', 'ilike', "%{$search}%");
                      });
                  });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($isNewRequisition && !$request->filled('search') && !$request->filled('status')) {
            $requisitions = $query->orderBy('created_at', 'desc')->limit(20)->get();
            $request->session()->forget('new_requisition');
        } else {
            if ($request->has('page') && ($request->filled('search') || $request->filled('status'))) {
                $request->merge(['page' => 1]);
            }
            $requisitions = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('Employee.requisition.history', compact('requisitions', 'isNewRequisition'));
    }

    public function getRequisitionDetails(Requisition $requisition)
    {
        if ($requisition->requested_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $requisition->load(['requisitionItems.item.unit']);
        return response()->json([
            'success' => true,
            'requisition' => [
                'id' => $requisition->id,
                'requisition_number' => $requisition->requisition_number,
                'request_date' => $requisition->request_date,
                'department' => $requisition->department,
                'purpose' => $requisition->purpose,
                'status' => $requisition->status,
                'total_estimated_value' => $requisition->total_estimated_value,
                'requisition_items' => $requisition->requisitionItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'quantity_requested' => $item->quantity_requested,
                        'unit_cost_estimate' => $item->unit_cost_estimate,
                        'total_estimated_value' => $item->total_estimated_value,
                        'item' => [
                            'id' => $item->item->id,
                            'name' => $item->item->name,
                            'item_code' => $item->item->item_code,
                            'unit' => [ 'symbol' => $item->item->unit->symbol, 'name' => $item->item->unit->name ]
                        ]
                    ];
                })
            ]
        ]);
    }

    public function confirmReceipt(Request $request, Requisition $requisition)
    {
        if ($requisition->requested_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        if ($requisition->status !== 'fulfilled') {
            return back()->with('error', 'Requisition must be fulfilled by inventory before confirming receipt.');
        }
        $requisition->update([
            'status' => 'completed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => Carbon::now()
        ]);
        $currentUser = Auth::user();
        if ($requisition->approved_by) {
            Notification::create([
                'user_id' => $requisition->approved_by,
                'title' => 'Requisition Completed',
                'message' => "Requisition {$requisition->requisition_number} has been received and confirmed by {$currentUser->name}.",
                'type' => 'requisition_update',
                'priority' => 'normal',
                'action_url' => route('supervisor.requisitions.details', $requisition->id),
                'metadata' => [
                    'requisition_number' => $requisition->requisition_number,
                    'requisition_status' => 'completed',
                    'confirmed_by' => $currentUser->name,
                    'confirmed_at' => Carbon::now()->toDateTimeString(),
                ],
                'created_at' => Carbon::now()
            ]);
        }
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Requisition Completed',
            'message' => "Your requisition {$requisition->requisition_number} has been successfully completed. Thank you for confirming receipt!",
            'type' => 'requisition_update',
            'priority' => 'normal',
            'action_url' => route('employee.requisitions.history'),
            'metadata' => [
                'requisition_number' => $requisition->requisition_number,
                'requisition_status' => 'completed',
                'completed_at' => Carbon::now()->toDateTimeString(),
            ],
            'created_at' => Carbon::now()
        ]);
        return back()->with('success', 'Receipt confirmed successfully.');
    }

    public function getItemsForRequisition(Request $request)
    {
        $search = $request->get('search');
        $items = Item::where('is_active', true)
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('name', 'ilike', "%{$search}%")
                          ->orWhere('item_code', 'ilike', "%{$search}%");
                }
            })
            ->with('unit')
            ->limit(10)
            ->get();
        return response()->json($items);
    }
}
