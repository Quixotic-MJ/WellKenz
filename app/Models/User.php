<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

// DEBUGGING: Temporarily remove HasUuids to test if this is the issue
// use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];



    /**
     * Get the password for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Check if user has specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the formatted role name.
     *
     * @return string
     */
    public function getFormattedRoleAttribute()
    {
        $roleMap = [
            'admin' => 'Administrator',
            'supervisor' => 'Supervisor', 
            'purchasing' => 'Purchasing Officer',
            'inventory' => 'Inventory Manager',
            'employee' => 'Staff'
        ];
        
        return $roleMap[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Get the user's initials for avatar.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $name = $this->name ?? '';
        if (empty($name)) {
            return 'U';
        }
        
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get the last login time formatted for display.
     *
     * @return string
     */
    public function getFormattedLastLoginAttribute()
    {
        if (!$this->last_login_at) {
            return 'Never';
        }
        
        return $this->last_login_at->diffForHumans();
    }

    /**
     * Get the role color class for UI.
     *
     * @return string
     */
    public function getRoleColorClassAttribute()
    {
        $colorMap = [
            'admin' => 'bg-purple-100 text-purple-800',
            'supervisor' => 'bg-blue-100 text-blue-800',
            'purchasing' => 'bg-green-100 text-green-800',
            'inventory' => 'bg-orange-100 text-orange-800',
            'employee' => 'bg-gray-100 text-gray-800'
        ];
        
        return $colorMap[$this->role] ?? 'bg-gray-100 text-gray-800';
    }
}