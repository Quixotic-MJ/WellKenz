<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Notification::where('notif_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

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

    /**
     * Employee notifications page: provide counts and list for the authenticated user.
     */
    public function employeeIndex()
    {
        $userId = Auth::id();
        if (!$userId) {
            return view('Employee.Notification.notification', [
                'notifications' => collect(),
                'totalCount' => 0,
                'unreadCount' => 0,
                'readCount' => 0,
                'thisWeekCount' => 0,
            ]);
        }

        $totalCount = Notification::where('user_id', $userId)->count();
        $unreadCount = Notification::where('user_id', $userId)->where('is_read', false)->count();
        $readCount = Notification::where('user_id', $userId)->where('is_read', true)->count();
        $thisWeekCount = Notification::where('user_id', $userId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Employee.Notification.notification', compact(
            'notifications', 'totalCount', 'unreadCount', 'readCount', 'thisWeekCount'
        ));
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

    public function compose(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,user_id',
            'notif_title' => 'required|string|max:255',
            'notif_content' => 'required|string',
            'related_type' => 'required|string|max:50'
        ]);

        if ($validated['user_id']) {
            // Send to specific user
            Notification::create([
                'notif_title' => $validated['notif_title'],
                'notif_content' => $validated['notif_content'],
                'related_type' => $validated['related_type'],
                'user_id' => $validated['user_id'],
                'is_read' => false
            ]);
        } else {
            // Send to all users
            $users = User::all();
            $notifications = [];
            foreach ($users as $user) {
                $notifications[] = [
                    'notif_title' => $validated['notif_title'],
                    'notif_content' => $validated['notif_content'],
                    'related_type' => $validated['related_type'],
                    'user_id' => $user->user_id,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            Notification::insert($notifications);
        }

        return redirect()->back()->with('success', 'Notification sent successfully!');
    }

    /**
     * Get notification details for modal view
     */
    public function getNotificationDetails($id)
    {
        try {
            \Log::info('=== NOTIFICATION DETAILS REQUEST ===');
            \Log::info('Notification ID: ' . $id);
            \Log::info('User ID: ' . Auth::id());
            \Log::info('User: ' . Auth::user()->name);

            // Check if notification exists and belongs to user
            $notification = Notification::where('notif_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                \Log::warning('Notification not found or access denied');
                \Log::warning('Available notifications for user: ' . 
                    Notification::where('user_id', Auth::id())->pluck('notif_id')->implode(', '));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found or you do not have permission to view it',
                    'debug' => [
                        'requested_id' => $id,
                        'user_id' => Auth::id(),
                        'available_notifications' => Notification::where('user_id', Auth::id())->pluck('notif_id')
                    ]
                ], 404);
            }

            \Log::info('Notification found: ' . $notification->notif_title);

            // Format the response data
            $formattedNotification = [
                'notification_id' => $notification->notif_id,
                'notif_title' => $notification->notif_title,
                'notif_content' => $notification->notif_content,
                'related_type' => $notification->related_type,
                'type_formatted' => ucfirst(str_replace('_', ' ', $notification->related_type)),
                'related_id' => $notification->related_id,
                'related_link' => $this->generateRelatedLink($notification),
                'is_read' => (bool) $notification->is_read,
                'formatted_date' => $notification->created_at->format('M d, Y \a\t H:i'),
                'created_at' => $notification->created_at->toISOString(),
                'updated_at' => $notification->updated_at->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'notification' => $formattedNotification,
                'debug' => [
                    'notification_found' => true,
                    'user_authenticated' => true
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getNotificationDetails: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate related link based on notification type
     */
    private function generateRelatedLink($notification)
    {
        // Adjust these routes based on your actual route names
        switch ($notification->related_type) {
            case 'requisition':
                return route('employee.requisitions.show', $notification->related_id);
            case 'item_request':
                return route('employee.item-requests.show', $notification->related_id);
            case 'acknowledgment':
                return route('employee.acknowledgments.show', $notification->related_id);
            case 'announcement':
                return route('employee.announcements.show', $notification->related_id);
            default:
                return null;
        }
    }
}