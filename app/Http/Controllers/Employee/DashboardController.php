<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Requisition;
use App\Models\Notification;
use App\Models\Recipe;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $user = Auth::user();

        // Fetch data for workstation interface
        $my_pending_reqs = $this->getMyPendingRequisitionsCount();
        $my_approved_reqs = $this->getMyApprovedRequisitionsCount();
        $recent_requisitions = $this->getRecentRequisitions();
        $notifications = $this->getUnreadNotifications();

        return view('Employee.home', compact(
            'user', 'my_pending_reqs', 'my_approved_reqs', 'recent_requisitions', 'notifications'
        ));
    }

    /**
     * Count of pending requisitions for current user
     */
    private function getMyPendingRequisitionsCount()
    {
        return Requisition::where('requested_by', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    /**
     * Count of approved requisitions (ready to collect) for current user
     */
    private function getMyApprovedRequisitionsCount()
    {
        return Requisition::where('requested_by', Auth::id())
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Fetch the last 5 requisitions created by current user
     */
    private function getRecentRequisitions()
    {
        return Requisition::where('requested_by', Auth::id())
            ->with(['requisitionItems' => function($query) {
                $query->with('item');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Fetch unread notifications for current user
     */
    private function getUnreadNotifications($limit = 5)
    {
        return Notification::forCurrentUser()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
