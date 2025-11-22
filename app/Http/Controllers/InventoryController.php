<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display purchase requests for inventory staff
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get purchase requests (for now, show all)
        $purchaseRequests = PurchaseRequest::with([
            'requestedBy:id,name,email',
            'purchaseRequestItems.item.unit'
        ])
        ->latest()
        ->paginate(10);

        // Calculate statistics
        $pendingCount = PurchaseRequest::where('status', 'pending')->count();
        $approvedCount = PurchaseRequest::where('status', 'approved')->count();

        return view('Inventory.outbound.purchase_request', compact(
            'purchaseRequests',
            'pendingCount',
            'approvedCount'
        ));
    }

    /**
     * Show the form for creating a new purchase request
     */
    public function create()
    {
        return $this->index();
    }

    /**
     * Store a newly created purchase request
     */
    public function createPurchaseRequest(Request $request)
    {
        try {
            $request->validate([
                'department' => 'required|string|max:255',
                'priority' => 'required|in:low,normal,high,urgent',
                'request_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity_requested' => 'required|numeric|min:0.01',
                'items.*.unit_price_estimate' => 'required|numeric|min:0'
            ]);

            $user = Auth::user();

            DB::beginTransaction();

            // Generate PR number
            $prNumber = 'PR-' . date('Y') . '-' . str_pad(PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total
            $totalEstimatedCost = 0;
            foreach ($request->items as $item) {
                $totalEstimatedCost += ($item['quantity_requested'] * $item['unit_price_estimate']);
            }

            // Create purchase request
            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requested_by' => $user->id,
                'department' => $request->department,
                'priority' => $request->priority,
                'request_date' => $request->request_date,
                'notes' => $request->notes,
                'status' => 'pending',
                'total_estimated_cost' => $totalEstimatedCost
            ]);

            // Create purchase request items
            foreach ($request->items as $itemData) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity_requested'],
                    'unit_price_estimate' => $itemData['unit_price_estimate'],
                    'total_estimated_cost' => ($itemData['quantity_requested'] * $itemData['unit_price_estimate'])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Request created successfully',
                'pr_number' => $prNumber
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating purchase request: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase request
     */
    public function show($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::with([
                'requestedBy:id,name,email',
                'purchaseRequestItems.item.unit'
            ])->findOrFail($id);

            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase request not found'
                ], 404);
            }

            // Process purchase request items
            $items = $purchaseRequest->purchaseRequestItems->map(function($item) {
                return [
                    'item_name' => $item->item->name,
                    'quantity_requested' => number_format($item->quantity_requested, 2),
                    'unit_price_estimate' => number_format($item->unit_price_estimate, 2),
                    'total_estimated_cost' => number_format($item->total_estimated_cost, 2)
                ];
            });

            // Get requester info
            $requesterName = $purchaseRequest->requestedBy ? 
                ($purchaseRequest->requestedBy->name ?? 'Unknown User') : 'Unknown User';

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $purchaseRequest->id,
                    'pr_number' => $purchaseRequest->pr_number,
                    'requested_by' => $requesterName,
                    'department' => $purchaseRequest->department,
                    'priority' => $purchaseRequest->priority,
                    'status' => $purchaseRequest->status,
                    'notes' => $purchaseRequest->notes,
                    'request_date' => $purchaseRequest->request_date,
                    'created_at' => $purchaseRequest->created_at->toISOString(),
                    'items' => $items->values(),
                    'total_items' => $items->count(),
                    'total_estimated_cost' => $purchaseRequest->total_estimated_cost,
                    'formatted_total' => '₱' . number_format($purchaseRequest->total_estimated_cost, 2)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading purchase request details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase request details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase request
     */
    public function destroy($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            if (!$purchaseRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase request not found'
                ], 404);
            }

            if (!in_array($purchaseRequest->status, ['pending', 'draft'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a request that has already been processed'
                ], 400);
            }

            // Delete related items first
            PurchaseRequestItem::where('purchase_request_id', $purchaseRequest->id)->delete();
            
            // Delete the purchase request
            $purchaseRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error cancelling purchase request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel purchase request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for dropdown selection
     */
    public function getItems()
    {
        try {
            $items = Item::select('id', 'name', 'item_code', 'unit_id')
                ->with('unit:id,name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            \Log::error('Error fetching items: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}