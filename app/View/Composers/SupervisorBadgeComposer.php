<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Requisition;
use App\Models\Notification;

class SupervisorBadgeComposer
{
    public function compose(View $view)
    {
        $badgeCounts = [
            'pending_requisitions' => 0,
            'unread_notifications' => 0
        ];

        try {
            if (Auth::check()) {
                $user = Auth::user();
                
                if ($user && $user->exists) {
                    // Get pending requisitions count for supervisor
                    $badgeCounts['pending_requisitions'] = Requisition::where('status', 'pending')->count();
                    
                    // Get unread notifications count for current user
                    $badgeCounts['unread_notifications'] = Notification::unreadCountForCurrentUser();
                }
            }
        } catch (\Exception $e) {
            // Log error for debugging but don't fail the view
            \Log::error('SupervisorBadgeComposer error: ' . $e->getMessage());
        }

        $view->with('badgeCounts', $badgeCounts);
    }
}