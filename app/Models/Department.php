<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'dept_id';

    protected $fillable = [
        'dept_name'
    ];

    // Relationship with Employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'dept_id', 'dept_id');
    }

    // Get department color for UI
    public function getColorAttribute()
    {
        $colors = [
            'Administration' => 'bg-blue-500',
            'Bakery' => 'bg-amber-500',
            'Purchasing' => 'bg-caramel',
            'Inventory' => 'bg-green-500',
            'Supervision' => 'bg-purple-500'
        ];

        return $colors[$this->dept_name] ?? 'bg-gray-500';
    }
}