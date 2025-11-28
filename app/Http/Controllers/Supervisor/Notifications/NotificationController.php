<?php

namespace App\Http\Controllers\Supervisor\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->notifications($request);
    }

    public function getHeaderNotifications()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getHeaderNotifications();
    }

    public function getUnreadNotificationCount()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getUnreadNotificationCount();
    }

    public function markAllNotificationsAsRead()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->markAllNotificationsAsRead();
    }

    public function bulkNotificationOperations(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->bulkNotificationOperations($request);
    }

    public function markNotificationAsRead(Notification $notification)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->markNotificationAsRead($notification);
    }

    public function markNotificationAsUnread(Notification $notification)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->markNotificationAsUnread($notification);
    }

    public function deleteNotification(Notification $notification)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->deleteNotification($notification);
    }
}
