<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    protected $table = 'notifications';

    // Disable automatic timestamp management since the table doesn't have updated_at column
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'priority',
        'is_read',
        'action_url',
        'metadata',
        'expires_at',
        'created_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    /**
     * Get the time ago string for when notification was created.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if notification is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if notification is for the current user.
     */
    public function isForCurrentUser(): bool
    {
        return $this->user_id === auth()->id();
    }

    /**
     * Get the priority color class for styling.
     */
    public function getPriorityColorClass(): string
    {
        return match($this->priority) {
            'urgent' => 'border-red-500',
            'high' => 'border-amber-500', 
            'normal' => 'border-blue-400',
            'low' => 'border-gray-300',
            default => 'border-gray-300'
        };
    }

    /**
     * Get the priority badge color for styling.
     */
    public function getPriorityBadgeColor(): string
    {
        return match($this->priority) {
            'urgent' => 'text-red-600 bg-red-50',
            'high' => 'text-amber-600 bg-amber-50',
            'normal' => 'text-blue-600 bg-blue-50', 
            'low' => 'text-gray-600 bg-gray-50',
            default => 'text-gray-600 bg-gray-50'
        };
    }

    /**
     * Get the icon class based on notification type.
     */
    public function getIconClass(): string
    {
        return match($this->type) {
            'stock_alert' => 'fas fa-exclamation-triangle text-red-600 bg-red-100',
            'approval_req' => 'fas fa-clipboard-check text-amber-600 bg-amber-100',
            'system_info' => 'fas fa-database text-blue-500 bg-blue-100',
            'delivery_update' => 'fas fa-truck text-green-600 bg-green-100',
            'production' => 'fas fa-cogs text-purple-600 bg-purple-100',
            'inventory' => 'fas fa-boxes text-indigo-600 bg-indigo-100',
            'purchasing' => 'fas fa-shopping-cart text-orange-600 bg-orange-100',
            'quality' => 'fas fa-check-circle text-emerald-600 bg-emerald-100',
            default => 'fas fa-bell text-gray-600 bg-gray-100'
        };
    }

    /**
     * Get notifications for the current user with optional filtering.
     */
    public static function forCurrentUser(string $filter = 'all')
    {
        $query = static::where('user_id', auth()->id())
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest();

        return match($filter) {
            'unread' => $query->where('is_read', false),
            'high' => $query->whereIn('priority', ['high', 'urgent']),
            'urgent' => $query->where('priority', 'urgent'),
            default => $query
        };
    }

    /**
     * Mark multiple notifications as read.
     */
    public static function markMultipleAsRead(array $notificationIds): void
    {
        static::whereIn('id', $notificationIds)
            ->where('user_id', auth()->id())
            ->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public static function markAllAsReadForCurrentUser(): void
    {
        static::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get unread count for current user.
     */
    public static function unreadCountForCurrentUser(): int
    {
        return static::where('user_id', auth()->id())
            ->where('is_read', false)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get only non-expired notifications.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}