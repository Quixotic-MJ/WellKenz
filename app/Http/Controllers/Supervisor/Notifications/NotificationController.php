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
        $filter = $request->get('filter', 'all');

        // Get notifications based on filter
        $query = \App\Models\Notification::forCurrentUser($filter);
        $notifications = $query->paginate(20)->withQueryString();

        // Calculate statistics for tabs
        $stats = $this->getNotificationStats();

        return view('Supervisor.notification', compact('notifications', 'filter', 'stats'));
    }

    public function getHeaderNotifications()
    {
        try {
            $notifications = \App\Models\Notification::where('user_id', auth()->id())
                ->notExpired()
                ->unread()
                ->latest()
                ->limit(5)
                ->get();

            $unreadCount = \App\Models\Notification::unreadCountForCurrentUser();

            $formattedNotifications = $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'time_ago' => $notification->getTimeAgoAttribute(),
                    'icon_class' => $notification->getIconClass(),
                    'action_url' => $notification->action_url,
                    'read_at' => $notification->read_at
                ];
            });

            return response()->json([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching header notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }

    public function getUnreadNotificationCount()
    {
        try {
            $count = \App\Models\Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0
            ]);
        }
    }

    public function markAllNotificationsAsRead()
    {
        try {
            \App\Models\Notification::markAllAsReadForCurrentUser();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    public function bulkNotificationOperations(Request $request)
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'exists:notifications,id',
                'operation' => 'required|in:mark_read,mark_unread,delete'
            ]);

            $notificationIds = $request->notification_ids;
            $operation = $request->operation;
            $userId = auth()->id();

            // Verify all notifications belong to the current user
            $validNotifications = \App\Models\Notification::whereIn('id', $notificationIds)
                ->where('user_id', $userId)
                ->get();

            if ($validNotifications->count() !== count($notificationIds)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Some notifications do not belong to you'
                ], 403);
            }

            $affectedCount = 0;

            switch ($operation) {
                case 'mark_read':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->update(['is_read' => true]);
                    break;

                case 'mark_unread':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->update(['is_read' => false]);
                    break;

                case 'delete':
                    $affectedCount = \App\Models\Notification::whereIn('id', $notificationIds)
                        ->where('user_id', $userId)
                        ->delete();
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$affectedCount} notification(s)",
                'affected_count' => $affectedCount
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }

            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in bulk notification operation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process bulk operation'
            ], 500);
        }
    }

    public function markNotificationAsRead(Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function markNotificationAsUnread(Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->markAsUnread();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as unread: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark notification as unread'
            ], 500);
        }
    }

    public function deleteNotification(Notification $notification)
    {
        try {
            // Verify the notification belongs to the current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete notification'
            ], 500);
        }
    }

    /**
     * Get notification statistics for the current user
     */
    private function getNotificationStats()
    {
        $userId = auth()->id();

        return [
            'total' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->count(),
            'unread' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->unread()
                ->count(),
            'high_priority' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->whereIn('priority', ['high', 'urgent'])
                ->count(),
            'urgent' => \App\Models\Notification::where('user_id', $userId)
                ->notExpired()
                ->where('priority', 'urgent')
                ->count(),
        ];
    }
}
