<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Requisition;

class NotificationComposer
{
    public function compose(View $view)
    {
        $unreadNotificationsCount = 0;
        $pendingRequisitionsCount = 0;
        $recentNotifications = collect([]);

        try {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && $user->exists) {
                    // Get actual unread notification count from the Notification model
                    $unreadNotificationsCount = Notification::unreadCountForCurrentUser();
                    
                    // Get pending requisitions count that need fulfillment
                    // Only count approved requisitions that can be fulfilled
                    $pendingRequisitionsCount = Requisition::countPendingFulfillment();
                    
                    // Get recent notifications (last 5 unread notifications)
                    $recentNotifications = Notification::forCurrentUser('unread')
                        ->take(5)
                        ->get();
                } else {
                    // If user doesn't exist (invalid session), logout
                    Auth::logout();
                    session()->invalidate();
                    session()->regenerateToken();
                }
            }
        } catch (\Exception $e) {
            // Handle any database/authentication errors
            if (Auth::check()) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }
            // Log error for debugging
            \Log::error('NotificationComposer error: ' . $e->getMessage());
        }

        $view->with('unreadNotificationsCount', $unreadNotificationsCount)
             ->with('pendingRequisitionsCount', $pendingRequisitionsCount)
             ->with('recentNotifications', $recentNotifications);
    }
}