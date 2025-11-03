<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'emp_id';

    protected $fillable = [
        'emp_name',
        'emp_position',
        'emp_email',
        'emp_contact',
        'emp_status',
        'dept_id'
    ];

    protected $casts = [
        'emp_status' => 'string',
    ];

    // Relationship with User
    public function user()
    {
        return $this->hasOne(User::class, 'emp_id', 'emp_id');
    }

    // Relationship with Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    // Get initials for avatar
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->emp_name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    // Check if employee is a baker
    public function getIsBakerAttribute()
    {
        return stripos($this->emp_position, 'baker') !== false;
    }

    // Get status badge color
    public function getStatusBadgeAttribute()
    {
        return $this->emp_status === 'active' 
            ? 'bg-green-100 text-green-800 border-green-200' 
            : 'bg-red-100 text-red-800 border-red-200';
    }

    // Get status text
    public function getStatusTextAttribute()
    {
        return $this->emp_status === 'active' ? 'Active' : 'Inactive';
    }
}