<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'role',
        'emp_id',
    ];

    protected $hidden = [
        'password',
    ];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }

    // Relationship with Requisitions (requested by)
    // public function requisitions()
    // {
    //     return $this->hasMany(Requisition::class, 'requested_by', 'user_id');
    // }

    // // Relationship with Approved Requisitions
    // public function approvedRequisitions()
    // {
    //     return $this->hasMany(Requisition::class, 'approved_by', 'user_id');
    // }

    // Relationship with Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // // Relationship with Inventory Transactions
    // public function inventoryTransactions()
    // {
    //     return $this->hasMany(InventoryTransaction::class, 'trans_by', 'user_id');
    // }

    // // Relationship with Acknowledge Receipts (issued by)
    // public function issuedAcknowledgeReceipts()
    // {
    //     return $this->hasMany(AcknowledgeReceipt::class, 'issued_by', 'user_id');
    // }

    // // Relationship with Acknowledge Receipts (issued to)
    // public function receivedAcknowledgeReceipts()
    // {
    //     return $this->hasMany(AcknowledgeReceipt::class, 'issued_to', 'user_id');
    // }

    // // Relationship with Memos
    // public function memos()
    // {
    //     return $this->hasMany(Memo::class, 'received_by', 'user_id');
    // }

    // Get unread notifications count
    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    // Get recent notifications
    public function getRecentNotifications($limit = 5)
    {
        return $this->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // Accessor for unread notifications count (for blade templates)
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->getUnreadNotificationsCount();
    }

    // Accessor for recent notifications (for blade templates)
    public function getRecentNotificationsAttribute()
    {
        return $this->getRecentNotifications(5);
    }

    // Accessor for role display name
    public function getRoleDisplayAttribute()
    {
        $roleNames = [
            'admin' => 'Admin',
            'employee' => 'Employee',
            'inventory' => 'Inventory Staff',
            'purchasing' => 'Purchase Staff',
            'supervisor' => 'Supervisor'
        ];

        return $roleNames[$this->role] ?? ucfirst($this->role);
    }

    // Accessor for role badge color
    public function getRoleBadgeAttribute()
    {
        $roleColors = [
            'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
            'employee' => 'bg-caramel text-white border-caramel-dark',
            'inventory' => 'bg-blue-100 text-blue-700 border-blue-200',
            'purchasing' => 'bg-green-100 text-green-700 border-green-200',
            'supervisor' => 'bg-yellow-100 text-yellow-700 border-yellow-200'
        ];

        return $roleColors[$this->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    }

    // Check if user is admin
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    // Check if user is supervisor
    public function getIsSupervisorAttribute()
    {
        return $this->role === 'supervisor';
    }

    // Check if user can approve requisitions
    public function getCanApproveRequisitionsAttribute()
    {
        return in_array($this->role, ['admin', 'supervisor']);
    }

    // Check if user can manage inventory
    public function getCanManageInventoryAttribute()
    {
        return in_array($this->role, ['admin', 'inventory']);
    }

    // Check if user can manage purchases
    public function getCanManagePurchasesAttribute()
    {
        return in_array($this->role, ['admin', 'purchasing']);
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->whereHas('employee', function($q) {
            $q->where('emp_status', 'active');
        });
    }

    // Scope for inactive users
    public function scopeInactive($query)
    {
        return $query->whereHas('employee', function($q) {
            $q->where('emp_status', 'inactive');
        });
    }

    // Scope by role
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}