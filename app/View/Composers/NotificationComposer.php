<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    public function compose(View $view)
    {
        $unreadNotificationsCount = 0;
        $recentNotifications = collect([]);

        try {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user && $user->exists) {
                    // Set default notification data - implement proper notification system later
                    $unreadNotificationsCount = 0;
                    $recentNotifications = collect([]);
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
             ->with('recentNotifications', $recentNotifications);
    }
}