<?php

namespace App\Http\Controllers\Employee\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $totalNotifications = Notification::forCurrentUser()->count();
        $unreadNotifications = Notification::forCurrentUser('unread')->count();
        $highPriorityNotifications = Notification::forCurrentUser('high')->count();
        $urgentNotifications = Notification::forCurrentUser('urgent')->count();

        $allUserNotifications = Notification::forCurrentUser()->get();
        $approvalNotifications = $allUserNotifications->filter(function($notification) {
            return $notification->isApproval();
        })->count();
        $fulfillmentNotifications = $allUserNotifications->filter(function($notification) {
            return $notification->isFulfillment();
        })->count();

        $query = Notification::forCurrentUser();
        switch($filter) {
            case 'approvals':
                $query = $query->where(function($q) {
                    $q->where('type', Notification::TYPE_APPROVAL_REQUEST)
                      ->orWhere('type', Notification::TYPE_REQUISITION_UPDATE)
                      ->orWhere('type', Notification::TYPE_PURCHASING);
                });
                break;
            case 'fulfillments':
                $query = $query->where(function($q) {
                    $q->where('type', Notification::TYPE_INVENTORY)
                      ->orWhere(function($subq) {
                          $subq->where('type', Notification::TYPE_REQUISITION_UPDATE)
                               ->whereJsonContains('metadata->requisition_status', 'fulfilled')
                               ->orWhereJsonContains('metadata->requisition_status', 'completed');
                      });
                });
                break;
            default:
                if (in_array($filter, ['unread', 'high', 'urgent'])) {
                    $query = Notification::forCurrentUser($filter);
                }
                break;
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        $stats = [
            'total' => $totalNotifications,
            'unread' => $unreadNotifications,
            'high_priority' => $highPriorityNotifications,
            'urgent' => $urgentNotifications,
            'approvals' => $approvalNotifications,
            'fulfillments' => $fulfillmentNotifications
        ];

        return view('Employee.notification', compact('notifications', 'stats', 'filter'));
    }

    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::markAllAsReadForCurrentUser();
        return response()->json(['success' => true]);
    }

    public function markNotificationAsUnread(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        $notification->markAsUnread();
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        try {
            $unreadCount = Notification::unreadCountForHeaderDisplay();
            return response()->json(['success' => true, 'unread_count' => $unreadCount]);
        } catch (\Exception $e) {
            Log::error('Error getting unread notification count: ' . $e->getMessage());
            return response()->json(['success' => false, 'unread_count' => 0], 500);
        }
    }

    public function getHeaderNotifications()
    {
        try {
            $notifications = Notification::forHeaderDisplay()
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'time_ago' => $notification->getTimeAgoAttribute(),
                        'action_url' => $notification->action_url,
                        'icon_class' => $notification->getIconClass(),
                        'read_at' => $notification->is_read ? now() : null,
                        'priority' => $notification->priority,
                        'type' => $notification->type,
                        'created_at' => $notification->created_at
                    ];
                });

            $unreadCount = Notification::unreadCountForHeaderDisplay();
            return response()->json(['success' => true, 'notifications' => $notifications, 'unread_count' => $unreadCount]);
        } catch (\Exception $e) {
            Log::error('Error getting header notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load notifications', 'notifications' => [], 'unread_count' => 0], 500);
        }
    }
}
