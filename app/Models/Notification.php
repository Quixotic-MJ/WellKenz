<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'notif_id';
    public $timestamps = true;

    protected $fillable = [
        'notif_title', 'notif_content', 'related_id', 'related_type', 'is_read', 'user_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scope to get notifications for a specific user OR global (user_id IS NULL)
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->orWhereNull('user_id');
    }
}