<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $primaryKey = 'inv_id';

    protected $fillable = [
        'item_id',
        'inv_unit',
        'inv_stock_quantity',
        'inv_expire_date',
        'reorder_level'
    ];

    protected $casts = [
        'inv_stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'inv_expire_date' => 'datetime',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Accessor for quantity (to match code expectations)
    public function getQuantityAttribute()
    {
        return $this->inv_stock_quantity;
    }

    // Helpers
    public function decreaseStock($quantity)
    {
        $this->decrement('inv_stock_quantity', $quantity);
    }

    public function increaseStock($quantity)
    {
        $this->increment('inv_stock_quantity', $quantity);
    }

    public function isLowStock()
    {
        return $this->inv_stock_quantity <= $this->reorder_level;
    }
}