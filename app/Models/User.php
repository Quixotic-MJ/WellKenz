<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    
    protected $fillable = [
        'username', 'password', 'role', 'name', 'position', 'email', 'contact', 'status',
    ];

    protected $hidden = ['password'];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

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
}