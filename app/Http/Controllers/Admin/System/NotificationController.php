<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the admin notifications page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get current filter (default to 'all')
        $filter = $request->get('filter', 'all');
        
        // Validate filter value
        $validFilters = ['all', 'unread', 'high', 'urgent'];
        if (!in_array($filter, $validFilters)) {
            $filter = 'all';
        }
        
        // Get filtered notifications for current user
        $notifications = Notification::forCurrentUser($filter)
            ->paginate(15)
            ->withQueryString();
            
        // Get notification statistics
        $stats = $this->getNotificationStats();
        
        return view('Admin.notification', compact('notifications', 'stats', 'filter'));
    }
    
    /**
     * Get notification statistics for the current user.
     *
     * @return array
     */
    private function getNotificationStats(): array
    {
        $baseQuery = Notification::where('user_id', auth()->id())
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
            
        return [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('is_read', false)->count(),
            'high_priority' => (clone $baseQuery)->whereIn('priority', ['high', 'urgent'])->count(),
            'urgent' => (clone $baseQuery)->where('priority', 'urgent')->count(),
        ];
    }
    
    /**
     * Mark all notifications as read for the current user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            Notification::markAllAsReadForCurrentUser();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark a specific notification as read.
     *
     * @param \App\Models\Notification $notification
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Notification $notification, Request $request): JsonResponse
    {
        try {
            // Ensure notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }
            
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark a specific notification as unread.
     *
     * @param \App\Models\Notification $notification
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsUnread(Notification $notification, Request $request): JsonResponse
    {
        try {
            // Ensure notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }
            
            $notification->markAsUnread();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as unread: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a specific notification.
     *
     * @param \App\Models\Notification $notification
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Notification $notification, Request $request): JsonResponse
    {
        try {
            // Ensure notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }
            
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle bulk operations on notifications.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkOperations(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'integer|exists:notifications,id',
            'operation' => 'required|string|in:mark_read,mark_unread,delete'
        ]);
        
        try {
            $notificationIds = $request->notification_ids;
            $operation = $request->operation;
            
            // Get only notifications that belong to current user
            $userNotifications = Notification::whereIn('id', $notificationIds)
                ->where('user_id', auth()->id())
                ->get();
                
            if ($userNotifications->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid notifications found'
                ], 400);
            }
            
            $count = $userNotifications->count();
            
            switch ($operation) {
                case 'mark_read':
                    Notification::markMultipleAsRead($userNotifications->pluck('id')->toArray());
                    $message = $count . ' notifications marked as read';
                    break;
                    
                case 'mark_unread':
                    foreach ($userNotifications as $notification) {
                        $notification->markAsUnread();
                    }
                    $message = $count . ' notifications marked as unread';
                    break;
                    
                case 'delete':
                    $userNotifications->each->delete();
                    $message = $count . ' notifications deleted';
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid operation'
                    ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get header notifications for the current user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHeaderNotifications(Request $request): JsonResponse
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
                'message' => 'Error fetching header notifications: ' . $e->getMessage()
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
    
    /**
     * Get unread notification count for the current user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotificationCount(Request $request): JsonResponse
    {
        try {
            $count = Notification::unreadCountForCurrentUser();
            
            return response()->json([
                'success' => true,
                'data' => ['count' => $count]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching unread count: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get details of a specific notification.
     *
     * @param \App\Models\Notification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationDetails(Notification $notification): JsonResponse
    {
        try {
            // Ensure notification belongs to current user
            if ($notification->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to notification'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'is_read' => $notification->is_read,
                    'action_url' => $notification->action_url,
                    'metadata' => $notification->metadata,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => $notification->expires_at?->format('Y-m-d H:i:s'),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a new notification (admin function).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:' . implode(',', array_keys(Notification::getTypes())),
            'priority' => 'required|string|in:' . implode(',', array_keys(Notification::getPriorities())),
            'action_url' => 'nullable|url',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        try {
            $notification = Notification::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'priority' => $request->priority,
                'is_read' => false,
                'action_url' => $request->action_url,
                'metadata' => $request->metadata,
                'expires_at' => $request->expires_at,
                'created_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => ['id' => $notification->id]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating notification: ' . $e->getMessage()
            ], 500);
        }
    }
}