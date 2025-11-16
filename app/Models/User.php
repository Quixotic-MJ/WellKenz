<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB; // <-- Make sure this is imported

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'username', 'password', 'role', 'name', 'position', 'email', 'contact', 'status',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'created_at' => 'datetime', 
        'updated_at' => 'datetime',
        'password' => 'hashed', // Ensures password is always hashed when set
    ];

    // Requisitions this user requested
    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'requested_by', 'user_id');
    }

    // Item Requests this user requested
    public function itemRequests()
    {
        return $this->hasMany(ItemRequest::class, 'requested_by', 'user_id');
    }

    // Notifications for this user
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // ARs this user issued
    public function issuedReceipts()
    {
        return $this->hasMany(AcknowledgeReceipt::class, 'issued_by', 'user_id');
    }

    // ARs this user must receive
    public function receivedReceipts()
    {
        return $this->hasMany(AcknowledgeReceipt::class, 'issued_to', 'user_id');
    }

    // **** FIX: ADDED YOUR CUSTOM NOTIFICATION FUNCTIONS BACK ****

    /**
     * Get the count of unread notifications for the user.
     *
     * @return int
     */
    public function getUnreadNotificationsCount()
    {
        // Use the Eloquent relationship for consistency
        // Also check for global notifications (where user_id is null)
        $userId = $this->getKey();
        return Notification::where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get recent notifications for the user.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecentNotifications($limit = 5)
    {
        // Use the Eloquent relationship for consistency
        // Also check for global notifications (where user_id is null)
        $userId = $this->getKey();
        return Notification::where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                // Add type_color and icon based on notif_type or something
                $notification->type_color = 'bg-blue-100'; // default
                $notification->icon = 'fa-info-circle'; // default
                if (isset($notification->notif_type)) {
                    switch ($notification->notif_type) {
                        case 'warning':
                            $notification->type_color = 'bg-yellow-100';
                            $notification->icon = 'fa-exclamation-triangle';
                            break;
                        case 'error':
                            $notification->type_color = 'bg-red-100';
                            $notification->icon = 'fa-times-circle';
                            break;
                        case 'success':
                            $notification->type_color = 'bg-green-100';
                            $notification->icon = 'fa-check-circle';
                            break;
                    }
                }
                return $notification;
            });
    }
}