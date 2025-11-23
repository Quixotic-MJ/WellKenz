<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentStock extends Model
{
    use HasFactory;

    protected $table = 'current_stock';

    // Disable automatic timestamps since the table doesn't have updated_at
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'current_quantity',
        'average_cost',
        'last_updated',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:3',
        'average_cost' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}