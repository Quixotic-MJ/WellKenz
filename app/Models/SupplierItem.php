<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierItem extends Model
{
    use HasFactory;

    protected $table = 'supplier_items';

    protected $fillable = [
        'supplier_id',
        'item_id',
        'supplier_item_code',
        'unit_price',
        'minimum_order_quantity',
        'lead_time_days',
        'last_purchase_price',
        'last_purchase_date',
        'is_preferred',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:3',
        'last_purchase_price' => 'decimal:2',
        'last_purchase_date' => 'date',
        'is_preferred' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}