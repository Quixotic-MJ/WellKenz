<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'requisition_items';

    public $timestamps = false;

    protected $fillable = [
        'requisition_id',
        'item_id',
        'quantity_requested',
        'quantity_issued',
        'unit_cost_estimate',
        'total_estimated_value',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:3',
        'quantity_issued' => 'decimal:3',
        'unit_cost_estimate' => 'decimal:2',
        'total_estimated_value' => 'decimal:2',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function currentStockRecord()
    {
        // Get current stock through the item relationship
        return $this->hasOneThrough(
            CurrentStock::class,  // Final model
            Item::class,          // Intermediate model  
            'id',                 // Foreign key on intermediate table (items.id)
            'item_id',            // Foreign key on final table (current_stock.item_id)
            'item_id',            // Local key on this table (requisition_items.item_id)
            'id'                  // Local key on intermediate table (items.id)
        );
    }
}