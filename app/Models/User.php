<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * The primary key associated with the table.
     *
     * This must match your schema's primary key column (user_id) so that
     * authentication can correctly persist and retrieve the user from session.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';
    
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * Note: 'password' is often handled separately (e.g., in a registration controller)
     * but is included here for completeness based on your controller's logic.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email', // Assuming you also have an email field
        'password',
        'role',
        'status',
        'position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ensures password is always hashed when set
    ];

    /**
     * Get the count of unread notifications for the user.
     *
     * @return int
     */
    public function getUnreadNotificationsCount()
    {
        return DB::table('notifications')
            ->where('user_id', $this->getKey())
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
        return DB::table('notifications')
            ->where('user_id', $this->getKey())
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