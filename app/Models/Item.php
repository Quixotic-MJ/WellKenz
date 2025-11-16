<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    public $timestamps = true;

    protected $fillable = [
        'item_code',
        'item_name',
        'item_description',
        'item_unit',
        'cat_id',
        'item_stock',
        'item_expire_date',
        'reorder_level',
        'min_stock_level',
        'max_stock_level',
        'is_custom',
        'is_active',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
        'item_expire_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }
}
