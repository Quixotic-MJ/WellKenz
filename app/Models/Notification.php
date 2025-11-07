<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notif_id';

    protected $fillable = [
        'notif_title',
        'notif_content',
        'related_id',
        'related_type',
        'is_read',
        'user_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Helper method to get notification type color
    public function getTypeColorAttribute()
    {
        $type = strtolower($this->related_type ?? 'general');
        
        $colors = [
            'requisition' => 'bg-chocolate',
            'purchase_order' => 'bg-caramel',
            'inventory' => 'bg-green-600',
            'item_request' => 'bg-blue-600',
            'acknowledge_receipt' => 'bg-purple-600',
            'general' => 'bg-caramel'
        ];

        return $colors[$type] ?? $colors['general'];
    }

    // Helper method to get notification icon
    public function getIconAttribute()
    {
        $type = strtolower($this->related_type ?? 'general');
        
        $icons = [
            'requisition' => 'fas fa-clipboard-list',
            'purchase_order' => 'fas fa-shopping-cart',
            'inventory' => 'fas fa-boxes',
            'item_request' => 'fas fa-plus-circle',
            'acknowledge_receipt' => 'fas fa-receipt',
            'general' => 'fas fa-bell'
        ];

        return $icons[$type] ?? $icons['general'];
    }

    // Format created_at to human readable time
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Check if notification is unread
    public function getIsUnreadAttribute()
    {
        return !$this->is_read;
    }

    // Scope for unread notifications
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope for read notifications
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Scope for recent notifications
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Scope by type
    public function scopeOfType($query, $type)
    {
        return $query->where('related_type', $type);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    // Mark as unread
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }
}