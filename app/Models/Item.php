<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Item extends Model
{
    use HasFactory, Auditable;

    protected $table = 'items';

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category_id',
        'unit_id',
        'item_type',
        'barcode',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'cost_price',
        'selling_price',
        'shelf_life_days',
        'storage_requirements',
        'is_active',
    ];

    protected $casts = [
        'min_stock_level' => 'decimal:3',
        'max_stock_level' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'shelf_life_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function currentStockRecord()
    {
        return $this->hasOne(CurrentStock::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function getCurrentStockAttribute()
    {
        return $this->currentStockRecord ? $this->currentStockRecord->current_quantity : 0;
    }

    public function isLowStock()
    {
        $currentStock = $this->currentStockRecord ? $this->currentStockRecord->current_quantity : 0;
        return $currentStock <= $this->reorder_point;
    }

    public function isOutOfStock()
    {
        $currentStock = $this->currentStockRecord ? $this->currentStockRecord->current_quantity : 0;
        return $currentStock <= 0;
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function rtvItems()
    {
        return $this->hasMany(\App\Models\RtvItem::class);
    }

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class);
    }
}