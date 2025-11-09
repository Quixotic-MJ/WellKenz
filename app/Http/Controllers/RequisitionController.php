<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Requisition; // Add this import
use App\Models\RequisitionItem; // Add this import

class RequisitionController extends Controller
{
    /**
     * Show create requisition form
     */
    public function create()
    {
        return view('Employee.Requisition.create_requisition');
    }

    /**
     * Store a new requisition
     */
    public function store(Request $request)
    {
        Log::info('Requisition submission started', $request->all());

        $validator = Validator::make($request->all(), [
            'req_ref' => 'required|string|max:255|unique:requisitions,req_ref',
            'req_purpose' => 'required|string|min:10',
            'req_priority' => 'required|in:low,medium,high',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1|max:10000'
        ], [
            'req_purpose.min' => 'Please provide a more detailed purpose (at least 10 characters).',
            'items.required' => 'Please add at least one item to the requisition.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 10,000.',
            'req_ref.unique' => 'This requisition reference already exists. Please try again.'
        ]);

        if ($validator->fails()) {
            Log::error('Requisition validation failed', $validator->errors()->toArray());
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
                Log::error('User not authenticated for requisition submission');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated. Please log in again.'
                ], 401);
            }

            $userId = $user->user_id;
            
            if (!$userId) {
                DB::rollBack();
                Log::error('Could not determine user ID', ['user' => $user->getAttributes()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine user identity.'
                ], 401);
            }

            Log::info('Creating requisition for user', [
                'user_id' => $userId, 
                'user_name' => $user->name
            ]);

            // Create requisition using the model instead of DB::table
            $requisition = new Requisition();
            $requisition->req_ref = $request->req_ref;
            $requisition->req_purpose = $request->req_purpose;
            $requisition->req_priority = $request->req_priority;
            $requisition->req_status = 'pending';
            $requisition->req_date = now()->format('Y-m-d');
            $requisition->requested_by = $userId;
            $requisition->save();

            $requisitionId = $requisition->req_id;

            Log::info('Requisition created', ['requisition_id' => $requisitionId]);

            // Add requisition items
            foreach ($request->items as $item) {
                Log::info('Adding requisition item', [
                    'requisition_id' => $requisitionId,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity']
                ]);

                // Get item details for unit
                $itemDetails = DB::table('items')
                    ->where('item_id', $item['item_id'])
                    ->select('item_unit')
                    ->first();

                // Use the model to create requisition item
                $requisitionItem = new RequisitionItem();
                $requisitionItem->req_id = $requisitionId;
                $requisitionItem->item_id = $item['item_id'];
                $requisitionItem->req_item_quantity = $item['quantity'];
                $requisitionItem->req_item_status = 'pending';
                $requisitionItem->item_unit = $itemDetails->item_unit;
                $requisitionItem->save();
            }

            DB::commit();

            Log::info('Requisition submitted successfully', ['requisition_id' => $requisitionId]);

            return response()->json([
                'success' => true,
                'message' => 'Requisition submitted successfully! Purchasing officers have been notified.',
                'requisition_id' => $requisitionId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating requisition: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating requisition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's requisitions
     */
    public function getMyRequisitions()
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id;
            
            Log::info("Fetching requisitions for user ID: {$userId}");

            // Use the model with relationships
            $requisitions = Requisition::with(['items.item'])
                ->where('requested_by', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info("Found " . $requisitions->count() . " requisitions");

            return response()->json($requisitions);

        } catch (\Exception $e) {
            Log::error('Error in getMyRequisitions: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load requisitions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get requisition details
     */
    public function getRequisitionDetails($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id;

            // Get requisition with relationships
            $requisition = Requisition::with(['items.item.category', 'requester', 'approver'])
                ->where('req_id', $id)
                ->first();

            if (!$requisition) {
                return response()->json(['error' => 'Requisition not found'], 404);
            }

            // Verify ownership (unless user is admin/supervisor)
            if (!$user->hasPermission('view_all_requisitions') && $requisition->requested_by != $userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($requisition);

        } catch (\Exception $e) {
            Log::error('Error in getRequisitionDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load requisition details'], 500);
        }
    }

    /**
     * Update requisition status (for supervisors/admins)
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'req_status' => 'required|in:pending,approved,rejected,completed',
            'remarks' => 'nullable|string|max:500'
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

            $requisition = Requisition::find($id);
            if (!$requisition) {
                return response()->json(['error' => 'Requisition not found'], 404);
            }

            $updateData = [
                'req_status' => $request->req_status,
            ];

            if ($request->req_status === 'approved' || $request->req_status === 'rejected') {
                $updateData['approved_by'] = $user->user_id;
                $updateData['approved_date'] = now();
            }

            $requisition->update($updateData);

            if ($request->remarks) {
                // Store remarks in a separate table or add a remarks field to requisitions
                DB::table('requisition_remarks')->insert([
                    'req_id' => $id,
                    'remarks' => $request->remarks,
                    'created_by' => $user->user_id,
                    'created_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Requisition status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating requisition status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating requisition status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete requisition
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $userId = $user->user_id;

            $requisition = Requisition::find($id);
            if (!$requisition) {
                return response()->json(['error' => 'Requisition not found'], 404);
            }

            // Verify ownership (unless user is admin)
            if (!$user->hasPermission('delete_requisitions') && $requisition->requested_by != $userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Only allow deletion of pending requisitions
            if ($requisition->req_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete requisition that is not pending'
                ], 422);
            }

            DB::beginTransaction();

            // Delete requisition items first
            RequisitionItem::where('req_id', $id)->delete();

            // Delete requisition
            $requisition->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Requisition deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting requisition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting requisition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all requisitions (for admin/supervisor)
     */
    public function getAllRequisitions()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasPermission('view_all_requisitions')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $requisitions = Requisition::with(['requester', 'approver', 'items'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requisitions);

        } catch (\Exception $e) {
            Log::error('Error in getAllRequisitions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load requisitions'], 500);
        }
    }

    /**
     * Get inventory items for requisition
     */
    public function getInventoryItems()
    {
        try {
            $items = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.item_stock', '>', 0)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.cat_id',
                    'c.cat_name'
                )
                ->orderBy('i.item_name')
                ->get();

            return response()->json($items);

        } catch (\Exception $e) {
            Log::error('Error in getInventoryItems: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load inventory items'], 500);
        }
    }
}