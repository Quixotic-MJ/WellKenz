<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}