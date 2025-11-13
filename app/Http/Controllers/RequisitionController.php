<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Str;

class RequisitionController extends Controller
{
    /**
     * Show create requisition form
     */
    public function create()
    {
        $user = Auth::user();
        $userId = $user?->user_id;

        if (!$userId) {
            return view('Employee.Requisition.create_requisition', [
                'requisitions' => collect(),
                'items' => collect(),
                'reqTotal' => 0,
                'reqPending' => 0,
                'reqApproved' => 0,
                'reqRejected' => 0,
            ]);
        }

        $requisitions = Requisition::where('requested_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $reqTotal    = DB::table('requisitions')->where('requested_by', $userId)->count();
        $reqPending  = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'pending')->count();
        $reqApproved = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'approved')->count();
        $reqRejected = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'rejected')->count();

        $items = DB::table('items')
            ->where('is_active', true)
            ->orderBy('item_name')
            ->select('item_id', 'item_name', 'item_unit', 'item_stock')
            ->get();

        return view('Employee.Requisition.create_requisition', compact(
            'requisitions', 'items', 'reqTotal', 'reqPending', 'reqApproved', 'reqRejected'
        ));
    }

    public function myRequisitions()
    {
        $user = Auth::user();
        $userId = $user?->user_id;

        if (!$userId) {
            return view('Employee.Requisition.my_requisition', [
                'requisitions' => collect(),
                'totalCount' => 0,
                'pendingCount' => 0,
                'approvedCount' => 0,
                'rejectedCount' => 0,
            ]);
        }

        $requisitions = Requisition::with('items')
            ->where('requested_by', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalCount = DB::table('requisitions')->where('requested_by', $userId)->count();
        $pendingCount = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'pending')->count();
        $approvedCount = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'approved')->count();
        $rejectedCount = DB::table('requisitions')->where('requested_by', $userId)->where('req_status', 'rejected')->count();

        return view('Employee.Requisition.my_requisition', compact(
            'requisitions', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'
        ));
    }

    /**
     * Show edit requisition form
     */
    public function edit($id)
    {
        $user = Auth::user();
        $userId = $user?->user_id;

        if (!$userId) {
            return redirect()->route('Staff_Requisition_Record')->with('error', 'User not authenticated');
        }

        $requisition = Requisition::with('items.item')
            ->where('req_id', $id)
            ->where('requested_by', $userId)
            ->where('req_status', 'pending')
            ->first();

        if (!$requisition) {
            return redirect()->route('Staff_Requisition_Record')->with('error', 'Requisition not found or cannot be edited');
        }

        $items = DB::table('items')
            ->where('is_active', true)
            ->orderBy('item_name')
            ->select('item_id', 'item_name', 'item_unit', 'item_stock')
            ->get();

        return view('Employee.Requisition.edit', compact('requisition', 'items'));
    }

    /**
     * Update a requisition
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'req_purpose' => 'required|string|min:10',
            'req_priority' => 'required|in:low,medium,high',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1|max:10000'
        ], [
            'req_purpose.min' => 'Please provide a more detailed purpose (at least 10 characters).',
            'items.required' => 'Please add at least one item to the requisition.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 10,000.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            if (!$user) {
                return redirect()->back()->with('error', 'User not authenticated');
            }

            $userId = $user->user_id;

            $requisition = Requisition::where('req_id', $id)
                ->where('requested_by', $userId)
                ->where('req_status', 'pending')
                ->first();

            if (!$requisition) {
                return redirect()->route('Staff_Requisition_Record')->with('error', 'Requisition not found or cannot be updated');
            }

            // Update requisition
            $requisition->req_purpose = $request->req_purpose;
            $requisition->req_priority = $request->req_priority;
            $requisition->save();

            // Delete existing items
            RequisitionItem::where('req_id', $id)->delete();

            // Add updated items
            foreach ($request->items as $item) {
                $itemDetails = DB::table('items')
                    ->where('item_id', $item['item_id'])
                    ->select('item_unit')
                    ->first();

                $requisitionItem = new RequisitionItem();
                $requisitionItem->req_id = $id;
                $requisitionItem->item_id = $item['item_id'];
                $requisitionItem->req_item_quantity = $item['quantity'];
                $requisitionItem->req_item_status = 'pending';
                $requisitionItem->item_unit = $itemDetails->item_unit;
                $requisitionItem->save();
            }

            DB::commit();

            return redirect()->route('Staff_Requisition_Record')->with('success', 'Requisition updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating requisition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating requisition: ' . $e->getMessage());
        }
    }

    /**
     * Store a new requisition
     */
    public function store(Request $request)
    {
        Log::info('Requisition submission started', $request->all());

        $validator = Validator::make($request->all(), [
            'req_purpose' => 'required|string|min:10',
            'req_priority' => 'required|in:low,medium,high',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.quantity' => 'required|integer|min:1|max:10000'
        ], [
            'req_purpose.min' => 'Please provide a more detailed purpose (at least 10 characters).',
            'items.required' => 'Please add at least one item to the requisition.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 10,000.'
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

            // Generate sequential requisition reference for the day
            $today = now()->format('Y-m-d');
            $lastReq = DB::table('requisitions')
                ->whereDate('created_at', $today)
                ->orderBy('req_id', 'desc')
                ->first();

            $sequence = $lastReq ? intval(substr($lastReq->req_ref, -4)) + 1 : 1;
            $reqRef = 'REQ-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Create requisition using the model instead of DB::table
            $requisition = new Requisition();
            $requisition->req_ref = $reqRef;
            $requisition->req_purpose = $request->req_purpose;
            $requisition->req_priority = $request->req_priority;
            $requisition->req_status = 'pending';
            $requisition->req_date = now();
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

            // Create notifications to Supervisors about new requisition
            $supervisors = DB::table('users')->where('role', 'supervisor')->pluck('user_id');
            $notifPayloads = [];
            foreach ($supervisors as $supId) {
                $notifPayloads[] = [
                    'notif_title'   => 'New Requisition Submitted',
                    'notif_content' => 'Requisition '.$requisition->req_ref.' submitted by '.$user->name,
                    'related_id'    => (string)$requisitionId,
                    'related_type'  => 'Requisition',
                    'is_read'       => false,
                    'user_id'       => $supId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            if (!empty($notifPayloads)) {
                DB::table('notifications')->insert($notifPayloads);
            }

            DB::commit();

            Log::info('Requisition submitted successfully', ['requisition_id' => $requisitionId]);

            return response()->json([
                'success' => true,
                'message' => 'Requisition submitted successfully! Supervisors have been notified.',
                'requisition_id' => $requisitionId,
                'req_ref' => $requisition->req_ref,
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
            'remarks' => 'nullable|string|max:500',
            'req_reject_reason' => 'nullable|string|max:255' // Added reject reason validation
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
            
            if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
                return response()->json(['error' => 'Unauthorized access'], 403);
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
                
                // Store reject reason if status is rejected
                if ($request->req_status === 'rejected' && $request->req_reject_reason) {
                    $updateData['req_reject_reason'] = $request->req_reject_reason;
                } elseif ($request->req_status === 'rejected') {
                    // If rejected but no reason provided, use remarks or default
                    $updateData['req_reject_reason'] = $request->remarks ?: 'No reason provided';
                } else {
                    // Clear reject reason if not rejected
                    $updateData['req_reject_reason'] = null;
                }
            }

            $requisition->update($updateData);

            // Notify requester of decision
            $title = $request->req_status === 'approved' ? 'Requisition Approved' : ($request->req_status === 'rejected' ? 'Requisition Rejected' : 'Requisition Updated');
            $content = 'Your requisition '.$requisition->req_ref.' was '.$request->req_status.
                       ($request->req_status==='rejected' && ($updateData['req_reject_reason'] ?? null) ? 
                        (' with reason: '.$updateData['req_reject_reason']) : '');
            DB::table('notifications')->insert([
                'notif_title'   => $title,
                'notif_content' => $content,
                'related_id'    => (string)$requisition->req_id,
                'related_type'  => 'Requisition',
                'is_read'       => false,
                'user_id'       => $requisition->requested_by,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Remarks are stored in the requisition record itself or can be added to a separate table if needed
            // For now, we'll skip storing remarks in a separate table since it doesn't exist

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
     * Admin index page for requisitions
     */
    public function adminIndex()
    {
        try {
            $requisitions = Requisition::with(['requester', 'approver'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Counts for dynamic header cards
            $totalCount     = Requisition::count();
            $pendingCount   = Requisition::where('req_status', 'pending')->count();
            $approvedCount  = Requisition::where('req_status', 'approved')->count();
            $rejectedCount  = Requisition::where('req_status', 'rejected')->count();
            $completedCount = Requisition::where('req_status', 'completed')->count();

            return view('Admin.Requisition.requisition', compact(
                'requisitions',
                'totalCount',
                'pendingCount',
                'approvedCount',
                'rejectedCount',
                'completedCount'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error loading requisitions: ' . $e->getMessage());
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

    /**
     * Get requisitions for supervisor approval with filters
     */
    public function getRequisitionsForApproval(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is supervisor or admin
            if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
                Log::warning('Unauthorized access attempt to supervisor requisitions', [
                    'user_id' => $user ? $user->user_id : 'none',
                    'role' => $user ? $user->role : 'none'
                ]);
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            $query = Requisition::with(['requester', 'items.item'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('req_ref', 'LIKE', "%{$search}%")
                      ->orWhere('req_purpose', 'LIKE', "%{$search}%")
                      ->orWhereHas('requester', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%");
                      });
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('req_status', $request->status);
            }

            if ($request->has('priority') && $request->priority !== 'all') {
                $query->where('req_priority', $request->priority);
            }

            if ($request->has('date') && $request->date) {
                $query->whereDate('req_date', $request->date);
            }

            $requisitions = $query->paginate(10);

            return response()->json($requisitions);

        } catch (\Exception $e) {
            Log::error('Error in getRequisitionsForApproval: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load requisitions'], 500);
        }
    }

    /**
     * Get requisition statistics for supervisor
     */
    public function getRequisitionStats()
    {
        try {
            $user = Auth::user();
            
            // Check if user is supervisor or admin
            if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            $stats = [
                'pending' => Requisition::where('req_status', 'pending')->count(),
                'approved_today' => Requisition::where('req_status', 'approved')
                    ->whereDate('approved_date', today())
                    ->count(),
                'rejected_week' => Requisition::where('req_status', 'rejected')
                    ->whereBetween('approved_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'total_month' => Requisition::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error in getRequisitionStats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load statistics'], 500);
        }
    }

    /**
     * Get single requisition for supervisor review
     */
    public function getRequisitionForReview($id)
    {
        try {
            $user = Auth::user();

            // Check if user is supervisor or admin
            if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
                abort(403);
            }

            $requisition = Requisition::with([
                'requester',
                'items.item.category',
                'approver'
            ])->find($id);

            if (!$requisition) {
                abort(404);
            }

            return view('Supervisor.Requisition.review', compact('requisition'));

        } catch (\Exception $e) {
            Log::error('Error in getRequisitionForReview: ' . $e->getMessage());
            abort(500);
        }
    }

    /**
     * Update requisition status (approve/reject)
     */
    public function updateRequisitionStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'req_status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:500',
            'req_reject_reason' => 'nullable|string|max:255' // Added reject reason validation
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
            
            // Check if user is supervisor or admin
            if (!$user || !in_array($user->role, ['supervisor', 'admin'])) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            $requisition = Requisition::find($id);
            if (!$requisition) {
                return response()->json(['error' => 'Requisition not found'], 404);
            }

            if ($requisition->req_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Requisition has already been processed'
                ], 422);
            }

            $updateData = [
                'req_status' => $request->req_status,
                'approved_by' => $user->user_id,
                'approved_date' => now(),
            ];

            // Store reject reason if status is rejected
            if ($request->req_status === 'rejected') {
                $updateData['req_reject_reason'] = $request->req_reject_reason ?: ($request->remarks ?: 'No reason provided');
            } else {
                $updateData['req_reject_reason'] = null;
            }

            $requisition->update($updateData);

            // Notify requester of decision
            $title = $request->req_status === 'approved' ? 'Requisition Approved' : 'Requisition Rejected';
            $content = 'Your requisition ' . $requisition->req_ref . ' was ' . $request->req_status
                     . ($request->req_status === 'rejected' && ($updateData['req_reject_reason'] ?? null)
                        ? (' with reason: ' . $updateData['req_reject_reason'])
                        : '');
            DB::table('notifications')->insert([
                'notif_title'   => $title,
                'notif_content' => $content,
                'related_id'    => (string) $requisition->req_id,
                'related_type'  => 'Requisition',
                'is_read'       => false,
                'user_id'       => $requisition->requested_by,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::commit();

            $action = $request->req_status === 'approved' ? 'approved' : 'rejected';
            return response()->json([
                'success' => true,
                'message' => "Requisition {$action} successfully",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating requisition status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating requisition status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Printable requisition view (owner, admin, or supervisor)
     */
    public function print($id)
    {
        try {
            $user = Auth::user();
            $userId = $user?->user_id;

            $req = Requisition::with(['items.item', 'requester', 'approver'])
                ->where('req_id', $id)
                ->first();

            if (!$req) {
                return back()->with('error', 'Requisition not found');
            }

            if (!in_array($user?->role, ['admin', 'supervisor']) && $req->requested_by != $userId) {
                return back()->with('error', 'Unauthorized');
            }

            return view('Employee.Requisition.print', compact('req'));
        } catch (\Exception $e) {
            Log::error('Print requisition error: ' . $e->getMessage());
            return back()->with('error', 'Failed to render requisition print');
        }
    }
}