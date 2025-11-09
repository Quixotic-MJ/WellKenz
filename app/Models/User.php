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
        'name',
        'position',
        'email',
        'contact',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'unread_notifications_count',
        'recent_notifications',
        'role_display',
        'role_badge',
        'is_admin',
        'is_supervisor',
        'can_approve_requisitions',
        'can_manage_inventory',
        'can_manage_purchases',
    ];

    // Add this accessor for compatibility
    public function getIdAttribute()
    {
        return $this->user_id;
    }

    // Relationship with Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    // Relationship with Requisitions (as requester)
    public function requisitions()
    {
        return $this->hasMany(Requisition::class, 'requested_by', 'user_id');
    }

    // Relationship with Requisitions (as approver)
    public function approvedRequisitions()
    {
        return $this->hasMany(Requisition::class, 'approved_by', 'user_id');
    }

    // Relationship with Item Requests
    public function itemRequests()
    {
        return $this->hasMany(ItemRequest::class, 'requested_by', 'user_id');
    }

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

    // Check if user is employee
    public function getIsEmployeeAttribute()
    {
        return $this->role === 'employee';
    }

    // Check if user is inventory staff
    public function getIsInventoryAttribute()
    {
        return $this->role === 'inventory';
    }

    // Check if user is purchasing staff
    public function getIsPurchasingAttribute()
    {
        return $this->role === 'purchasing';
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

    // Check if user can create requisitions
    public function getCanCreateRequisitionsAttribute()
    {
        return in_array($this->role, ['admin', 'employee', 'supervisor']);
    }

    // Check if user is active
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    // Get pending requisitions count (for approvers)
    public function getPendingRequisitionsCountAttribute()
    {
        if (!$this->can_approve_requisitions) {
            return 0;
        }

        return Requisition::where('req_status', 'pending')->count();
    }

    // Get pending item requests count (for approvers)
    public function getPendingItemRequestsCountAttribute()
    {
        if (!$this->can_approve_requisitions) {
            return 0;
        }

        return ItemRequest::where('status', 'pending')->count();
    }

    // Get user's recent requisitions
    public function getRecentRequisitions($limit = 5)
    {
        return $this->requisitions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // Get user's requisitions statistics
    public function getRequisitionStats()
    {
        return [
            'total' => $this->requisitions()->count(),
            'pending' => $this->requisitions()->where('req_status', 'pending')->count(),
            'approved' => $this->requisitions()->where('req_status', 'approved')->count(),
            'rejected' => $this->requisitions()->where('req_status', 'rejected')->count(),
            'completed' => $this->requisitions()->where('req_status', 'completed')->count(),
        ];
    }

    // Check if user can view all requisitions
    public function getCanViewAllRequisitionsAttribute()
    {
        return in_array($this->role, ['admin', 'supervisor', 'inventory', 'purchasing']);
    }

    // Check if user can delete requisitions
    public function getCanDeleteRequisitionsAttribute()
    {
        return in_array($this->role, ['admin']);
    }

    // Check if user can edit users
    public function getCanManageUsersAttribute()
    {
        return $this->role === 'admin';
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for inactive users
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Scope by role
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Scope for users who can approve requisitions
    public function scopeApprovers($query)
    {
        return $query->whereIn('role', ['admin', 'supervisor']);
    }

    // Scope for users who can create requisitions
    public function scopeRequesters($query)
    {
        return $query->whereIn('role', ['admin', 'employee', 'supervisor']);
    }

    // Get dashboard route based on role
    public function getDashboardRouteAttribute()
    {
        $routes = [
            'admin' => 'Admin_dashboard',
            'employee' => 'Staff_dashboard',
            'inventory' => 'Inventory_Dashboard',
            'purchasing' => 'Purchasing_dashboard',
            'supervisor' => 'Supervisor_Dashboard'
        ];

        return $routes[$this->role] ?? 'login';
    }

    // Get dashboard name based on role
    public function getDashboardNameAttribute()
    {
        $names = [
            'admin' => 'Admin Dashboard',
            'employee' => 'Staff Dashboard',
            'inventory' => 'Inventory Dashboard',
            'purchasing' => 'Purchasing Dashboard',
            'supervisor' => 'Supervisor Dashboard'
        ];

        return $names[$this->role] ?? 'Dashboard';
    }

    // Check if user has permission for a specific action
    public function hasPermission($permission)
    {
        $permissions = [
            'view_all_requisitions' => $this->can_view_all_requisitions,
            'create_requisitions' => $this->can_create_requisitions,
            'approve_requisitions' => $this->can_approve_requisitions,
            'manage_inventory' => $this->can_manage_inventory,
            'manage_purchases' => $this->can_manage_purchases,
            'manage_users' => $this->can_manage_users,
            'delete_requisitions' => $this->can_delete_requisitions,
        ];

        return $permissions[$permission] ?? false;
    }

    // Get user's initials for avatars
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials;
    }

    // Get user's avatar color based on ID (for consistent colors)
    public function getAvatarColorAttribute()
    {
        $colors = [
            'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
            'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
        ];
        
        $index = $this->user_id % count($colors);
        return $colors[$index];
    }
}