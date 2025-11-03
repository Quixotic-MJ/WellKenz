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

        if (Auth::check()) {
            $user = Auth::user();
            $unreadNotificationsCount = $user->getUnreadNotificationsCount();
            $recentNotifications = $user->getRecentNotifications(5);
        }

        $view->with('unreadNotificationsCount', $unreadNotificationsCount)
             ->with('recentNotifications', $recentNotifications);
    }
}