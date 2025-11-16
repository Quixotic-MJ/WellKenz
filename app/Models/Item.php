<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'item_id';
    public $timestamps = true;

    protected $fillable = [
        'item_code', 'item_name', 'item_description', 'item_unit', 'cat_id',
        'item_stock', 'item_expire_date', 'reorder_level', 'min_stock_level',
        'max_stock_level', 'is_active', 'is_custom',
    ];

    protected $casts = [
        'item_expire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_updated' => 'datetime',
    ];
}