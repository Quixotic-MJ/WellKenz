<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\Requisition;
use App\Models\StockMovement;
use App\Models\CurrentStock;
use App\Models\User;
use App\Models\Notification;
use App\Models\Category;
use App\Models\Unit;
use App\Models\RtvTransaction;
use App\Models\RtvItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GeneralController extends Controller
{
    /**
     * Display inventory dashboard home with comprehensive statistics
     */
    public function home()
    {
        try {
            // Pending Purchase Orders
            $pendingPurchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->orderBy('expected_delivery_date', 'desc')
                ->limit(5)
                ->get();

            // Expiring batches with auto-notification check
            $expiringBatches = Batch::with(['item.unit', 'supplier'])
                ->whereIn('status', ['active', 'quarantine'])
                ->where(function($query) {
                    $query->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                        ->orWhereBetween('expiry_date', ['2024-01-01', '2024-12-31']);
                })
                ->orderBy('expiry_date')
                ->limit(5)
                ->get();

            // Auto-create expiry notifications for critical batches
            $this->createAutoExpiryNotifications($expiringBatches);

            // Two separate requisition widgets
            $pendingApprovalRequisitions = Requisition::with(['requestedBy', 'requisitionItems'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $readyForPickingRequisitions = Requisition::with(['requestedBy', 'requisitionItems'])
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            // Inventory calculations
            $inventoryValue = DB::table('current_stock')
                ->join('items', 'current_stock.item_id', '=', 'items.id')
                ->where('items.is_active', true)
                ->select(DB::raw('COALESCE(SUM(current_stock.current_quantity * current_stock.average_cost), 0) as total_value'))
                ->value('total_value') ?? 0;

            $lowStockItemsCount = DB::table('items')
                ->join('current_stock', 'items.id', '=', 'current_stock.item_id')
                ->where('items.is_active', true)
                ->whereRaw('current_stock.current_quantity <= items.min_stock_level')
                ->where('current_stock.current_quantity', '>', 0)
                ->count();

            $activeItemsCount = Item::where('is_active', true)->count();
            $todayMovementsCount = StockMovement::count();

            return view('Inventory.home', compact(
                'pendingPurchaseOrders',
                'expiringBatches',
                'pendingApprovalRequisitions',
                'readyForPickingRequisitions',
                'inventoryValue',
                'lowStockItemsCount',
                'activeItemsCount',
                'todayMovementsCount'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading inventory dashboard: ' . $e->getMessage());
            
            return view('Inventory.home', [
                'pendingPurchaseOrders' => collect(),
                'expiringBatches' => collect(),
                'pendingApprovalRequisitions' => collect(),
                'readyForPickingRequisitions' => collect(),
                'inventoryValue' => 0,
                'lowStockItemsCount' => 0,
                'activeItemsCount' => 0,
                'todayMovementsCount' => 0
            ]);
        }
    }

    /**
     * Display general notifications page
     */
    public function notifications(Request $request)
    {
        // Get filter from request (default to 'all')
        $filter = $request->get('filter', 'all');
        
        // Build the notifications query
        $query = \App\Models\Notification::forCurrentUser($filter);
        
        // Paginate results
        $notifications = $query->paginate(20);
        
        // Get notification statistics for the current user
        $stats = [
            'total' => \App\Models\Notification::forCurrentUser()->count(),
            'unread' => \App\Models\Notification::forCurrentUser('unread')->count(),
            'high_priority' => \App\Models\Notification::forCurrentUser('high')->count(),
            'urgent' => \App\Models\Notification::forCurrentUser('urgent')->count(),
        ];

        return view('Inventory.notification', compact('notifications', 'stats', 'filter'));
    }

    /**
     * Get notification statistics for AJAX updates
     */
    public function getNotificationStats()
    {
        $stats = [
            'total' => \App\Models\Notification::forCurrentUser()->count(),
            'unread' => \App\Models\Notification::forCurrentUser('unread')->count(),
            'high_priority' => \App\Models\Notification::forCurrentUser('high')->count(),
            'urgent' => \App\Models\Notification::forCurrentUser('urgent')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Mark specific notification as read
     */
    public function markNotificationAsRead(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark specific notification as unread
     */
    public function markNotificationAsUnread(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsUnread();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        try {
            \Log::info('Mark all read called for user: ' . auth()->id());
            
            $query = \App\Models\Notification::where('user_id', auth()->id())
                ->where('is_read', false);
            
            $count = $query->count();
            \Log::info("Found {$count} unread notifications to mark as read");
            
            $query->update(['is_read' => true]);
            
            \Log::info("Marked {$count} notifications as read");
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in markAllNotificationsAsRead: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get header notifications for the current user
     */
    public function getHeaderNotifications()
    {
        try {
            // Get only the 5 most recent unread notifications for header display
            $notifications = \App\Models\Notification::forCurrentUser()
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
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

            $unreadCount = \App\Models\Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting header notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications',
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Get unread notification count for current user
     */
    public function getUnreadNotificationCount()
    {
        try {
            $unreadCount = \App\Models\Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting unread notification count: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Delete specific notification
     */
    public function deleteNotification(\App\Models\Notification $notification)
    {
        // Ensure the notification belongs to the current user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Bulk operations on notifications
     */
    public function bulkNotificationOperations(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_unread,delete',
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id'
        ]);

        $notificationIds = $request->notification_ids;
        $action = $request->action;

        // Ensure all notifications belong to the current user
        $notifications = \App\Models\Notification::whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->get();

        if ($notifications->count() !== count($notificationIds)) {
            return response()->json(['success' => false, 'message' => 'Some notifications not found or unauthorized'], 403);
        }

        switch ($action) {
            case 'mark_read':
                \App\Models\Notification::markMultipleAsRead($notificationIds);
                $message = 'Notifications marked as read';
                break;
            case 'mark_unread':
                foreach ($notifications as $notification) {
                    $notification->markAsUnread();
                }
                $message = 'Notifications marked as unread';
                break;
            case 'delete':
                \App\Models\Notification::whereIn('id', $notificationIds)
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

    /**
     * Get border color class (alias for getPriorityColorClass for blade compatibility)
     */
    public function getBorderColorClass(): string
    {
        return $this->getPriorityColorClass();
    }

    /**
     * Get icon type for blade template compatibility
     */
    public function getIconType(): string
    {
        $iconClass = $this->getIconClass();
        $parts = explode(' ', $iconClass);
        // Return the icon classes without the background color
        return $parts[0] . ' ' . $parts[1] . ' ' . $parts[2];
    }

    /**
     * Get action button color class
     */
    public function getActionButtonClass(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-600 hover:bg-red-700',
            'high' => 'bg-amber-600 hover:bg-amber-700',
            'normal' => 'bg-blue-600 hover:bg-blue-700',
            'low' => 'bg-gray-600 hover:bg-gray-700',
            default => 'bg-chocolate hover:bg-chocolate-dark'
        };
    }

    /**
     * Get unread dot color
     */
    public function getUnreadDotColor(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-500',
            'high' => 'bg-amber-500',
            'normal' => 'bg-blue-500',
            'low' => 'bg-gray-400',
            default => 'bg-chocolate'
        };
    }

    /**
     * Get the icon background color class only
     */
    public function getIconBackgroundClass(): string
    {
        $iconClass = $this->getIconClass();
        $parts = explode(' ', $iconClass);
        return $parts[3] ?? 'bg-gray-100';
    }

    /**
     * Automatically create notifications for expiring batches
     */
    private function createAutoExpiryNotifications($expiringBatches)
    {
        try {
            foreach ($expiringBatches as $batch) {
                $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
                
                // Only create notifications for batches expiring within 3 days
                if ($daysUntilExpiry <= 3) {
                    $this->notifyExpiringBatch($batch, $daysUntilExpiry);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error creating auto expiry notifications: ' . $e->getMessage());
        }
    }

    /**
     * Notify relevant users about expiring batch
     */
    private function notifyExpiringBatch($batch, $daysUntilExpiry)
    {
        try {
            // Check if notification already exists for this batch today
            $existingNotification = Notification::where('metadata->batch_id', $batch->id)
                ->whereDate('created_at', today())
                ->where('type', 'batch_expiry')
                ->first();

            if ($existingNotification) {
                return; // Notification already sent today
            }

            $priority = $this->getExpiryPriority($daysUntilExpiry);
            $message = $this->getExpiryMessage($batch, $daysUntilExpiry, $priority);

            // Notify Production Staff (employees)
            $productionUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            // Notify Supervisors
            $supervisorUsers = User::where('role', 'supervisor')
                ->where('is_active', true)
                ->get();

            $allUsers = $productionUsers->merge($supervisorUsers);

            foreach ($allUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => "🚨 {$priority} Priority: Item Expiring Soon",
                    'message' => $message,
                    'type' => 'batch_expiry',
                    'priority' => $priority,
                    'action_url' => '/inventory/batches?highlight=' . $batch->id,
                    'metadata' => [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'quantity' => $batch->quantity,
                        'unit' => $batch->item->unit->symbol,
                        'priority_level' => $priority
                    ]
                ]);
            }

            \Log::info("Auto expiry notification sent for batch {$batch->batch_number}", [
                'batch_id' => $batch->id,
                'days_until_expiry' => $daysUntilExpiry,
                'notified_users' => $allUsers->count(),
                'priority' => $priority
            ]);

        } catch (\Exception $e) {
            \Log::error('Error notifying expiring batch: ' . $e->getMessage());
        }
    }

    /**
     * Determine priority level based on days until expiry
     */
    private function getExpiryPriority($daysUntilExpiry)
    {
        if ($daysUntilExpiry <= 1) return 'urgent';
        if ($daysUntilExpiry <= 2) return 'high';
        if ($daysUntilExpiry <= 3) return 'normal';
        return 'low';
    }

    /**
     * Generate appropriate expiry message
     */
    private function getExpiryMessage($batch, $daysUntilExpiry, $priority)
    {
        $urgencyText = [
            'urgent' => 'EXPIRES TODAY',
            'high' => 'Expires tomorrow',
            'normal' => "Expires in {$daysUntilExpiry} days",
            'low' => "Expires in {$daysUntilExpiry} days"
        ];

        return "🕒 {$urgencyText[$priority]}: {$batch->item->name} (Batch: {$batch->batch_number})\n\n" .
               "📦 Quantity: {$batch->quantity} {$batch->item->unit->symbol}\n" .
               "📅 Expiry Date: " . Carbon::parse($batch->expiry_date)->format('M d, Y') . "\n" .
               "📍 Location: " . ($batch->location ?? 'Main Storage') . "\n\n" .
               "💡 Action Required: Please prioritize usage of this batch in production.";
    }

    /**
     * Get unread notification count for sidebar badge (AJAX endpoint)
     */
    public function getNotificationCount()
    {
        try {
            $unreadCount = \App\Models\Notification::unreadCountForCurrentUser();

            return response()->json([
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting notification count: ' . $e->getMessage());
            
            return response()->json([
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Get pending requisitions count for sidebar badge (AJAX endpoint)
     */
    public function getPendingRequisitionsCount()
    {
        try {
            $pendingCount = \App\Models\Requisition::countPendingFulfillment();

            return response()->json([
                'pending_count' => $pendingCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting pending requisitions count: ' . $e->getMessage());
            
            return response()->json([
                'pending_count' => 0
            ]);
        }
    }
}