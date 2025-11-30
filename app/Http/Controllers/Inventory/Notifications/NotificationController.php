<?php

namespace App\Http\Controllers\Inventory\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display notifications interface for inventory staff
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get notifications with filtering
            $query = Notification::where('user_id', $user->id);

            // Apply filters (using 'filter' parameter to match the view tabs)
            if ($request->has('filter') && $request->filter !== 'all') {
                switch ($request->filter) {
                    case 'unread':
                        $query->where('is_read', false);
                        break;
                    case 'high':
                        $query->whereIn('priority', ['high', 'urgent']);
                        break;
                    case 'urgent':
                        $query->where('priority', 'urgent');
                        break;
                }
            }

            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            if ($request->has('priority') && $request->priority !== 'all') {
                $query->where('priority', $request->priority);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('message', 'like', '%' . $search . '%');
                });
            }

            // Date filters
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $notifications = $query->orderByRaw('CASE WHEN is_read = false THEN 0 ELSE 1 END')
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();

            // Get statistics for all notifications (not filtered)
            $stats = [
                'total' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::where('user_id', $user->id)
                    ->where('is_read', false)->count(),
                'read' => Notification::where('user_id', $user->id)
                    ->where('is_read', true)->count(),
                'today' => Notification::where('user_id', $user->id)
                    ->whereDate('created_at', today())->count(),
                'this_week' => Notification::where('user_id', $user->id)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'high_priority' => Notification::where('user_id', $user->id)
                    ->whereIn('priority', ['high', 'urgent'])->count(),
                'urgent' => Notification::where('user_id', $user->id)
                    ->where('priority', 'urgent')->count(),
            ];

            // Get notification types for filter dropdown
            $notificationTypes = Notification::where('user_id', $user->id)
                ->distinct()
                ->pluck('type')
                ->sort()
                ->values();

            // Get current filter from request, default to 'all'
            $filter = $request->get('filter', 'all');
            
            // Debug logging to help troubleshoot filter issues
            \Log::info('Notification filter debug', [
                'user_id' => Auth::id(),
                'filter_param' => $request->get('filter'),
                'final_filter' => $filter,
                'query_params' => $request->query()
            ]);

            return view('Inventory.notification', compact('notifications', 'stats', 'notificationTypes', 'filter'));

        } catch (\Exception $e) {
            \Log::error('Error loading notifications: ' . $e->getMessage());
            $filter = 'all';
            return view('Inventory.notification', [
                'notifications' => collect(),
                'stats' => ['total' => 0, 'unread' => 0, 'read' => 0, 'today' => 0, 'this_week' => 0, 'high_priority' => 0, 'urgent' => 0],
                'notificationTypes' => collect(),
                'filter' => $filter
            ]);
        }
    }

    /**
     * Get notification statistics for dashboard
     */
    public function getNotificationStats()
    {
        try {
            $user = Auth::user();
            
            $stats = [
                'total' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::where('user_id', $user->id)
                    ->where('is_read', false)->count(),
                'read' => Notification::where('user_id', $user->id)
                    ->where('is_read', true)->count(),
                'today' => Notification::where('user_id', $user->id)
                    ->whereDate('created_at', today())->count(),
                'this_week' => Notification::where('user_id', $user->id)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'high_priority' => Notification::where('user_id', $user->id)
                    ->where('priority', 'high')->count(),
                'urgent' => Notification::where('user_id', $user->id)
                    ->where('priority', 'urgent')->count(),
                'today_unread' => Notification::where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->where('is_read', false)->count(),
                'critical_issues' => Notification::where('user_id', $user->id)
                    ->where('priority', 'urgent')
                    ->where('is_read', false)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting notification stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification statistics',
                'data' => [
                    'total' => 0,
                    'unread' => 0,
                    'read' => 0,
                    'today' => 0,
                    'this_week' => 0,
                    'high_priority' => 0,
                    'urgent' => 0,
                    'today_unread' => 0,
                    'critical_issues' => 0
                ]
            ], 500);
        }
    }

    /**
     * Mark specific notification as read
     */
    public function markAsRead(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark specific notification as unread
     */
    public function markAsUnread(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $notification->update(['is_read' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking notification as unread: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as unread'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            $updatedCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} notifications marked as read",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Delete specific notification
     */
    public function destroy(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }

    /**
     * Get unread notification count for header
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            
            $count = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get header notifications (latest 5 unread)
     */
    public function getHeaderNotifications()
    {
        try {
            $user = Auth::user();
            
            $notifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'priority' => $notification->priority,
                        'action_url' => $notification->action_url,
                        'time_ago' => $notification->created_at->diffForHumans(),
                        'read_at' => $notification->is_read ? $notification->created_at : null,
                        'icon_class' => $this->getIconClass($notification->type, $notification->priority),
                        'metadata' => $notification->metadata
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting header notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
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
            'normal' => 'text-amber-500',
            'low' => 'text-green-500',
            default => 'text-caramel'
        };

        return "{$baseIcon} bg-cream-bg {$colorClass}";
    }

    /**
     * Bulk notification operations
     */
    public function bulkOperations(Request $request)
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'exists:notifications,id',
                'action' => 'required|in:mark_read,mark_unread,delete'
            ]);

            $user = Auth::user();
            $notificationIds = $request->notification_ids;
            $action = $request->action;

            // Ensure all notifications belong to the current user
            $ownedNotifications = Notification::where('user_id', $user->id)
                ->whereIn('id', $notificationIds)
                ->count();

            if ($ownedNotifications !== count($notificationIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some notifications do not belong to you'
                ], 403);
            }

            $processedCount = 0;

            switch ($action) {
                case 'mark_read':
                    $processedCount = Notification::where('user_id', $user->id)
                        ->whereIn('id', $notificationIds)
                        ->where('is_read', false)
                        ->update(['is_read' => true]);
                    break;

                case 'mark_unread':
                    $processedCount = Notification::where('user_id', $user->id)
                        ->whereIn('id', $notificationIds)
                        ->update(['is_read' => false]);
                    break;

                case 'delete':
                    $processedCount = Notification::where('user_id', $user->id)
                        ->whereIn('id', $notificationIds)
                        ->delete();
                    break;
            }

            $actionMessages = [
                'mark_read' => 'marked as read',
                'mark_unread' => 'marked as unread',
                'delete' => 'deleted'
            ];

            return response()->json([
                'success' => true,
                'message' => "{$processedCount} notifications {$actionMessages[$action]}",
                'processed_count' => $processedCount,
                'action' => $action
            ]);

        } catch (\Exception $e) {
            \Log::error('Error with bulk notification operations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk operations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification by ID with details
     */
    public function show(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Mark as read if not already read
            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'action_url' => $notification->action_url,
                    'metadata' => $notification->metadata,
                    'created_at' => $notification->created_at->toISOString(),
                    'read_at' => $notification->is_read ? $notification->created_at->toISOString() : null,
                    'is_read' => $notification->is_read
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error showing notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to show notification'
            ], 500);
        }
    }

    /**
     * Filter notifications by type or priority
     */
    public function filterNotifications(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Notification::where('user_id', $user->id);

            if ($request->has('type') && !empty($request->type)) {
                $query->where('type', $request->type);
            }

            if ($request->has('priority') && !empty($request->priority)) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'read') {
                    $query->where('is_read', true);
                } elseif ($request->status === 'unread') {
                    $query->where('is_read', false);
                }
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'priority' => $notification->priority,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'action_url' => $notification->action_url
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'total' => $notifications->total()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error filtering notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to filter notifications',
                'data' => [],
                'total' => 0
            ], 500);
        }
    }
}