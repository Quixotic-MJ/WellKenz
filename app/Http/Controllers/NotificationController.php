<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Notification::where('notif_id', $id)->firstOrFail();
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('Admin.Notification.notification', compact('notifications'));
    }

    public function getUnreadCount()
    {
        $count = Auth::user()->getUnreadNotificationsCount();

        return response()->json(['count' => $count]);
    }

    public function adminIndex()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(20);
        $users = User::all();

        return view('Admin.Notification.notification', compact('notifications', 'users'));
    }
}