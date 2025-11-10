<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'item_id';

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
        'is_active',
        'is_custom'        // ← NEW
    ];

    protected $casts = [
        'item_stock'        => 'decimal:3',
        'reorder_level'     => 'decimal:3',
        'min_stock_level'   => 'decimal:3',
        'max_stock_level'   => 'decimal:3',
        'item_expire_date'  => 'date',
        'is_active'         => 'boolean',
        'is_custom'         => 'boolean',   // ← NEW
        'last_updated'      => 'datetime'
    ];

    /* --------------------------------------------------------------------------
     *  RELATIONSHIPS
     * -------------------------------------------------------------------------- */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItem::class, 'item_id');
    }

    /* --------------------------------------------------------------------------
     *  ACCESSORS / MUTATORS
     * -------------------------------------------------------------------------- */
    public function getNameAttribute()
    {
        return $this->item_name;
    }

    public function getUnitAttribute()
    {
        return $this->item_unit;
    }

    public function getCodeAttribute()
    {
        return $this->item_code;
    }

    /* --------------------------------------------------------------------------
     *  SCOPES
     * -------------------------------------------------------------------------- */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithStock($query)
    {
        return $query->where('item_stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('item_stock <= reorder_level')
                    ->where('item_stock', '>', 0);
    }

    public function scopeCustom($query, bool $custom = true)   // ← NEW
    {
        return $query->where('is_custom', $custom);
    }

    /* --------------------------------------------------------------------------
     *  HELPERS
     * -------------------------------------------------------------------------- */
    public function hasSufficientStock($quantity)
    {
        return $this->item_stock >= $quantity;
    }

    public function isLowStock()
    {
        return $this->item_stock <= $this->reorder_level;
    }
}