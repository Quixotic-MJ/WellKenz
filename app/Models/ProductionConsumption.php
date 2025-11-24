<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionConsumption extends Model
{
    use HasFactory;

    public $timestamps = false; // Disable automatic timestamps since table only has created_at

    protected $table = 'production_consumption';

    protected $fillable = [
        'production_order_id',
        'item_id',
        'quantity_consumed',
        'batch_number',
        'consumption_date',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'quantity_consumed' => 'decimal:3',
        'consumption_date' => 'date'
    ];

    /**
     * Get the production order this consumption belongs to.
     */
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Get the item that was consumed.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user who recorded this consumption.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the batch this consumption came from.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_number', 'batch_number');
    }

    /**
     * Calculate the total cost of this consumption.
     */
    public function getTotalCostAttribute()
    {
        return $this->quantity_consumed * ($this->item->cost_price ?? 0);
    }

    /**
     * Scope to filter by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('consumption_date', $date);
    }

    /**
     * Scope to filter by production order.
     */
    public function scopeByProductionOrder($query, $productionOrderId)
    {
        return $query->where('production_order_id', $productionOrderId);
    }
}