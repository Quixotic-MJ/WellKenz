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
        'username',
        'password',
        'role',
        'emp_id'
    ];

    protected $hidden = [
        'password',
    ];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }

    // Relationship with Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // Get unread notifications count
    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }

    // Get recent notifications
    public function getRecentNotifications($limit = 5)
    {
        return $this->notifications()->recent($limit)->get();
    }

    // Role-based scopes and helper methods remain the same...
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeInventory($query)
    {
        return $query->where('role', 'inventory');
    }

    public function scopePurchasing($query)
    {
        return $query->where('role', 'purchasing');
    }

    public function scopeSupervisors($query)
    {
        return $query->where('role', 'supervisor');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function isInventory()
    {
        return $this->role === 'inventory';
    }

    public function isPurchasing()
    {
        return $this->role === 'purchasing';
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }
}