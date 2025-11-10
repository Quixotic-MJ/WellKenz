<?php

namespace App\Http\Controllers;

use App\Models\ItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ItemRequestController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Employee.item_request');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_req_name' => 'required|string|max:255',
                'item_req_unit' => 'required|string|max:50',
                'item_req_quantity' => 'required|integer|min:1',
                'item_req_description' => 'required|string'
            ]);

            $itemRequest = ItemRequest::create([
                'item_req_name' => $validated['item_req_name'],
                'item_req_unit' => $validated['item_req_unit'],
                'item_req_quantity' => $validated['item_req_quantity'],
                'item_req_description' => $validated['item_req_description'],
                'requested_by' => Auth::id(),
                'item_req_status' => 'pending'
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item request submitted successfully!',
                    'data' => $itemRequest
                ]);
            }

            return redirect()->route('Staff_Item_Request')
                ->with('success', 'Item request submitted successfully!');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error submitting request: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error submitting request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $request = ItemRequest::with(['requester', 'approver'])->findOrFail($id);

            // Check if user is authorized to view this request
            if (Auth::id() !== $request->requested_by && !in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'error' => 'Unauthorized to view this request'
                    ], 403);
                }
                abort(403);
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($request);
            }

            // For non-AJAX requests, redirect back
            return redirect()->back();

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'Request not found: ' . $e->getMessage()
                ], 404);
            }
            abort(404);
        }
    }

    /**
     * Get current user's item requests
     */
    public function getMyRequests()
    {
        try {
            $requests = ItemRequest::with(['requester', 'approver'])
                ->where('requested_by', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error loading requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending item requests for supervisors/admins
     */
    public function getPendingRequests()
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $requests = ItemRequest::with(['requester'])
                ->where('item_req_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error loading pending requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all item requests for supervisors/admins with filtering and pagination
     */
    public function getAllRequests(Request $request)
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $query = ItemRequest::with(['requester', 'approver']);

            // Apply filters
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('item_req_name', 'like', "%{$search}%")
                      ->orWhere('item_req_description', 'like', "%{$search}%")
                      ->orWhereHas('requester', function($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('item_req_status', $request->status);
            }

            if ($request->has('date') && !empty($request->date)) {
                $query->whereDate('created_at', $request->date);
            }

            // Order by creation date
            $query->orderBy('created_at', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $requests = $query->paginate($perPage);

            return response()->json($requests);

        } catch (\Exception $e) {
            Log::error('Error loading requests: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item request statistics for supervisors/admins
     */
    public function getRequestStats()
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                return response()->json([
                    'error' => 'Unauthorized access'
                ], 403);
            }

            $stats = [
                'pending' => ItemRequest::where('item_req_status', 'pending')->count(),
                'approved_today' => ItemRequest::where('item_req_status', 'approved')
                    ->whereDate('updated_at', today())->count(),
                'rejected_week' => ItemRequest::where('item_req_status', 'rejected')
                    ->where('updated_at', '>=', now()->startOfWeek())->count(),
                'total_month' => ItemRequest::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error loading stats: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update item request status (for supervisors/admins)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validated = $request->validate([
                'item_req_status' => 'required|in:approved,rejected',
                'remarks' => 'nullable|string|max:500'
            ]);

            $itemRequest = ItemRequest::findOrFail($id);

            // Check if already processed
            if ($itemRequest->item_req_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed'
                ], 400);
            }

            $updateData = [
                'item_req_status' => $validated['item_req_status'],
                'approved_by' => Auth::id()
            ];

            // Store rejection reason if provided
            if ($validated['item_req_status'] === 'rejected' && !empty($validated['remarks'])) {
                $updateData['item_req_reject_reason'] = $validated['remarks'];
            }

            $itemRequest->update($updateData);

            $message = $validated['item_req_status'] === 'approved'
                ? 'Item request approved successfully!'
                : 'Item request rejected!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $itemRequest->load(['requester', 'approver'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating request status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve item request (for admin/supervisors) - legacy method
     */
    public function approve($id)
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
                abort(403);
            }

            $itemRequest = ItemRequest::findOrFail($id);
            
            $itemRequest->update([
                'item_req_status' => 'approved',
                'approved_by' => Auth::id()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item request approved successfully!',
                    'data' => $itemRequest
                ]);
            }

            return back()->with('success', 'Item request approved successfully!');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error approving request: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    /**
     * Reject item request (for admin/supervisors) - legacy method
     */
    public function reject($id)
    {
        try {
            if (!in_array(Auth::user()->role, ['admin', 'supervisor'])) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
                abort(403);
            }

            $itemRequest = ItemRequest::findOrFail($id);

            $itemRequest->update([
                'item_req_status' => 'rejected',
                'approved_by' => Auth::id()
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item request rejected!',
                    'data' => $itemRequest
                ]);
            }

            return back()->with('success', 'Item request rejected!');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error rejecting request: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error rejecting request: ' . $e->getMessage());
        }
    }
}