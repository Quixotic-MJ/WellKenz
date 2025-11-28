<?php

namespace App\Http\Controllers\Purchasing\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = Notification::forCurrentUser($filter);
        $notifications = $query->paginate(20);
        $stats = [
            'total' => Notification::forCurrentUser()->count(),
            'unread' => Notification::forCurrentUser('unread')->count(),
            'high_priority' => Notification::forCurrentUser('high')->count(),
            'urgent' => Notification::forCurrentUser('urgent')->count(),
        ];
        return view('Purchasing.notification', compact('notifications', 'stats', 'filter'));
    }

    public function getNotificationStats()
    {
        $stats = [
            'total' => Notification::forCurrentUser()->count(),
            'unread' => Notification::forCurrentUser('unread')->count(),
            'high_priority' => Notification::forCurrentUser('high')->count(),
            'urgent' => Notification::forCurrentUser('urgent')->count(),
        ];
        return response()->json($stats);
    }

    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $notification->markAsRead();
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markNotificationAsUnread(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $notification->markAsUnread();
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    public function markAllAsRead()
    {
        try {
            $query = Notification::where('user_id', auth()->id())
                ->where('is_read', false);
            $count = $query->count();
            $query->update(['is_read' => true]);
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $notification->delete();
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    public function bulkOperations(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_unread,delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $notificationIds = $request->notification_ids;
        $action = $request->action;

        $notifications = Notification::whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->get();

        if ($notifications->count() !== count($notificationIds)) {
            return response()->json(['success' => false, 'message' => 'Some notifications not found or unauthorized'], 403);
        }

        switch ($action) {
            case 'mark_read':
                Notification::markMultipleAsRead($notificationIds);
                $message = 'Notifications marked as read';
                break;
            case 'mark_unread':
                foreach ($notifications as $notification) {
                    $notification->markAsUnread();
                }
                $message = 'Notifications marked as unread';
                break;
            case 'delete':
                Notification::whereIn('id', $notificationIds)
                    ->where('user_id', auth()->id())
                    ->delete();
                $message = 'Notifications deleted successfully';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function getHeaderNotifications()
    {
        try {
            $notifications = Notification::forCurrentUser()
                ->where('is_read', false)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'time_ago' => $notification->created_at->diffForHumans(),
                        'priority' => $notification->priority,
                        'type' => $notification->type,
                        'action_url' => $notification->action_url,
                        'read_at' => $notification->read_at,
                        'icon_class' => $this->getIconClass($notification->type, $notification->priority),
                    ];
                });
                
            $unreadCount = Notification::unreadCountForCurrentUser();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching header notifications: ' . $e->getMessage(),
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Get icon class for notification based on type and priority.
     */
    private function getIconClass(string $type, string $priority): string
    {
        $baseIcon = match($type) {
            'system' => 'fas fa-cog',
            'alert' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-circle',
            'error' => 'fas fa-times-circle',
            default => 'fas fa-bell'
        };

        $colorClass = match($priority) {
            'urgent' => 'text-red-500',
            'high' => 'text-orange-500',
            'medium' => 'text-yellow-500',
            'low' => 'text-green-500',
            default => 'text-caramel'
        };

        return "{$baseIcon} bg-cream-bg {$colorClass}";
    }
}
