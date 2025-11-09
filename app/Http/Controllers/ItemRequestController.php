<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\ItemRequest;
use App\Models\Item;

class ItemRequestController extends Controller
{
    /**
     * Show create item request form
     */
    public function create()
    {
        return view('Employee.item_request');
    }

    /**
     * Store a new item request
     */
    public function store(Request $request)
    {
        Log::info('Item request submission started', $request->all());

        $validator = Validator::make($request->all(), [
            'item_req_name' => 'required|string|max:255',
            'item_req_unit' => 'required|string|max:20',
            'item_req_quantity' => 'required|integer|min:1|max:100000',
            'item_req_description' => 'required|string|min:10|max:1000'
        ], [
            'item_req_name.required' => 'Item name is required',
            'item_req_unit.required' => 'Unit is required',
            'item_req_quantity.required' => 'Quantity is required',
            'item_req_quantity.min' => 'Quantity must be at least 1',
            'item_req_description.required' => 'Description is required',
            'item_req_description.min' => 'Description must be at least 10 characters'
        ]);

        if ($validator->fails()) {
            Log::error('Item request validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Please fix the validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            if (!$user) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Create item request
            $itemRequest = new ItemRequest();
            $itemRequest->item_req_name = $request->item_req_name;
            $itemRequest->item_req_unit = $request->item_req_unit;
            $itemRequest->item_req_quantity = $request->item_req_quantity;
            $itemRequest->item_req_description = $request->item_req_description;
            $itemRequest->item_req_status = 'pending';
            $itemRequest->requested_by = $user->user_id;
            $itemRequest->save();

            DB::commit();

            Log::info('Item request submitted successfully', [
                'item_req_id' => $itemRequest->item_req_id,
                'requested_by' => $user->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item request submitted successfully! It will be reviewed by supervisors.',
                'item_req_id' => $itemRequest->item_req_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating item request: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating item request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's item requests
     */
    public function getMyRequests()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $requests = ItemRequest::with(['requester', 'approver'])
                ->where('requested_by', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests);

        } catch (\Exception $e) {
            Log::error('Error in getMyRequests: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load item requests',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific item request details
     */
    public function show($id)
    {
        try {
            $request = ItemRequest::with(['requester', 'approver'])->find($id);

            if (!$request) {
                return response()->json(['error' => 'Item request not found'], 404);
            }

            // Check if user is authorized to view this request
            $user = Auth::user();
            if ($request->requested_by !== $user->user_id && !$user->hasPermission('approve_requisitions')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($request);

        } catch (\Exception $e) {
            Log::error('Error in show item request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load item request details'], 500);
        }
    }

    /**
     * Get pending item requests (for supervisors/admins)
     */
    public function getPendingRequests()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasPermission('approve_requisitions')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $requests = ItemRequest::with(['requester', 'approver'])
                ->where('item_req_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests);

        } catch (\Exception $e) {
            Log::error('Error in getPendingRequests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load pending requests'], 500);
        }
    }

    /**
     * Approve item request
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            if (!$user->hasPermission('approve_requisitions')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $itemRequest = ItemRequest::with('requester')->find($id);
            if (!$itemRequest) {
                return response()->json(['error' => 'Item request not found'], 404);
            }

            if ($itemRequest->item_req_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item request is not pending approval'
                ], 422);
            }

            // Update item request status
            $itemRequest->item_req_status = 'approved';
            $itemRequest->approved_by = $user->user_id;
            $itemRequest->save();

            // Create the item in inventory
            $this->createItemFromRequest($itemRequest);

            DB::commit();

            Log::info('Item request approved', [
                'item_req_id' => $itemRequest->item_req_id,
                'approved_by' => $user->user_id,
                'requester' => $itemRequest->requester ? $itemRequest->requester->name : 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item request approved and added to inventory successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving item request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving item request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject item request
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            if (!$user->hasPermission('approve_requisitions')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $itemRequest = ItemRequest::with('requester')->find($id);
            if (!$itemRequest) {
                return response()->json(['error' => 'Item request not found'], 404);
            }

            if ($itemRequest->item_req_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Item request is not pending approval'
                ], 422);
            }

            // Update item request status
            $itemRequest->item_req_status = 'rejected';
            $itemRequest->approved_by = $user->user_id;
            // Store rejection reason if provided
            if ($request->rejection_reason) {
                // You might want to add a rejection_reason field to your table
                // $itemRequest->rejection_reason = $request->rejection_reason;
            }
            $itemRequest->save();

            DB::commit();

            Log::info('Item request rejected', [
                'item_req_id' => $itemRequest->item_req_id,
                'approved_by' => $user->user_id,
                'requester' => $itemRequest->requester ? $itemRequest->requester->name : 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item request rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting item request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting item request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create item in inventory from approved request
     */
    private function createItemFromRequest(ItemRequest $itemRequest)
    {
        try {
            // Check if item already exists with same name and unit
            $existingItem = Item::where('item_name', $itemRequest->item_req_name)
                ->where('item_unit', $itemRequest->item_req_unit)
                ->first();

            if ($existingItem) {
                Log::info('Item already exists in inventory', [
                    'item_id' => $existingItem->item_id,
                    'item_req_id' => $itemRequest->item_req_id
                ]);
                return; // Item already exists, no need to create
            }

            // Generate item code
            $itemCode = 'ITM-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $itemRequest->item_req_name), 0, 3)) . '-' . time();

            // Create new item
            $item = new Item();
            $item->item_code = $itemCode;
            $item->item_name = $itemRequest->item_req_name;
            $item->item_description = $itemRequest->item_req_description;
            $item->item_unit = $itemRequest->item_req_unit;
            $item->item_stock = 0; // Start with 0 stock
            $item->cat_id = 1; // Default category, you might want to make this configurable
            $item->reorder_level = 10; // Default reorder level
            $item->min_stock_level = 5; // Default min stock level
            $item->is_active = true;
            $item->save();

            Log::info('Item created from request', [
                'item_req_id' => $itemRequest->item_req_id,
                'item_id' => $item->item_id,
                'item_code' => $itemCode
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating item from request: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get item request statistics for dashboard
     */
    public function getStats()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $stats = [
                'total' => ItemRequest::where('requested_by', $user->user_id)->count(),
                'pending' => ItemRequest::where('requested_by', $user->user_id)->where('item_req_status', 'pending')->count(),
                'approved' => ItemRequest::where('requested_by', $user->user_id)->where('item_req_status', 'approved')->count(),
                'rejected' => ItemRequest::where('requested_by', $user->user_id)->where('item_req_status', 'rejected')->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error in getStats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load statistics'], 500);
        }
    }
}