<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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

    // Relationships
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function currentStockRecord(): HasOneThrough
    {
        return $this->hasOneThrough(
            CurrentStock::class,  // Final model
            Item::class,          // Intermediate model  
            'id',                 // Foreign key on intermediate table (items.id)
            'item_id',            // Foreign key on final table (current_stock.item_id)
            'item_id',            // Local key on this table (requisition_items.item_id)
            'id'                  // Local key on intermediate table (items.id)
        );
    }

    // Helper methods
    public function getFormattedQuantityRequestedAttribute(): string
    {
        return number_format($this->quantity_requested, 3);
    }

    public function getFormattedQuantityIssuedAttribute(): string
    {
        return number_format($this->quantity_issued ?? 0, 3);
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return '₱' . number_format($this->unit_cost_estimate, 2);
    }

    public function getFormattedTotalValueAttribute(): string
    {
        return '₱' . number_format($this->total_estimated_value, 2);
    }

    public function getCurrentStockAttribute(): float
    {
        return $this->currentStockRecord?->current_quantity ?? 0;
    }

    public function getStockPercentageAttribute(): float
    {
        $currentStock = $this->currentStock;
        return $currentStock > 0 ? round(($this->quantity_requested / $currentStock) * 100, 1) : 0;
    }

    public function getCanFulfillAttribute(): bool
    {
        return $this->currentStock >= $this->quantity_requested && $this->currentStock > 0;
    }

    public function getIsInsufficientStockAttribute(): bool
    {
        return $this->currentStock < $this->quantity_requested && $this->currentStock > 0;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->currentStock <= 0;
    }

    public function getIsHighUsageAttribute(): bool
    {
        return $this->stock_percentage > 80;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        } elseif ($this->is_insufficient_stock) {
            return 'insufficient';
        } elseif ($this->is_high_usage) {
            return 'high_usage';
        } else {
            return 'sufficient';
        }
    }

    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'text-red-600 bg-red-50',
            'insufficient' => 'text-amber-600 bg-amber-50',
            'high_usage' => 'text-orange-600 bg-orange-50',
            default => 'text-green-600 bg-green-50'
        };
    }

    public function getRemainingQuantityAttribute(): float
    {
        return max(0, $this->quantity_requested - ($this->quantity_issued ?? 0));
    }

    public function getFulfillmentPercentageAttribute(): float
    {
        if ($this->quantity_requested <= 0) {
            return 0;
        }
        return round((($this->quantity_issued ?? 0) / $this->quantity_requested) * 100, 1);
    }

    public function isFullyFulfilled(): bool
    {
        return $this->quantity_issued >= $this->quantity_requested;
    }

    public function isPartiallyFulfilled(): bool
    {
        return ($this->quantity_issued ?? 0) > 0 && !$this->isFullyFulfilled();
    }

    public function isNotFulfilled(): bool
    {
        return ($this->quantity_issued ?? 0) <= 0;
    }

    // Stock validation methods
    public function canModifyQuantity(float $newQuantity): bool
    {
        if ($newQuantity <= 0) {
            return false;
        }

        // Check if requisition is still pending
        if (!$this->requisition->isPending()) {
            return false;
        }

        // Allow modifications up to current stock level
        return $newQuantity <= $this->currentStock;
    }

    public function getMaxAllowedQuantity(): float
    {
        return max($this->currentStock, $this->quantity_requested);
    }

    public function getShortageAmount(): float
    {
        return max(0, $this->quantity_requested - $this->currentStock);
    }

    // Scope methods
    public function scopeInsufficientStock($query)
    {
        return $query->whereHas('currentStockRecord', function($q) {
            $q->whereColumn('current_quantity', '<', 'requisition_items.quantity_requested');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('currentStockRecord', function($q) {
            $q->where('current_quantity', '<=', 0);
        })->orWhereDoesntHave('currentStockRecord');
    }

    public function scopeHighUsage($query)
    {
        return $query->whereHas('currentStockRecord', function($q) {
            $q->whereRaw('(requisition_items.quantity_requested / current_quantity) * 100 > 80');
        });
    }

    public function scopeByItem($query, int $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeFullyFulfilled($query)
    {
        return $query->whereColumn('quantity_issued', '>=', 'quantity_requested');
    }

    public function scopePartiallyFulfilled($query)
    {
        return $query->where('quantity_issued', '>', 0)
                    ->whereColumn('quantity_issued', '<', 'quantity_requested');
    }

    public function scopeNotFulfilled($query)
    {
        return $query->where('quantity_issued', '<=', 0);
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($requisitionItem) {
            // Auto-calculate total estimated value
            if ($requisitionItem->quantity_requested && $requisitionItem->unit_cost_estimate) {
                $requisitionItem->total_estimated_value = 
                    $requisitionItem->quantity_requested * $requisitionItem->unit_cost_estimate;
            }
        });

        static::updating(function ($requisitionItem) {
            // Recalculate total estimated value when quantity or unit cost changes
            if ($requisitionItem->isDirty(['quantity_requested', 'unit_cost_estimate'])) {
                $requisitionItem->total_estimated_value = 
                    $requisitionItem->quantity_requested * $requisitionItem->unit_cost_estimate;
            }
        });
    }

    // Static helper methods
    public static function generateRequisitionNumber(): string
    {
        $prefix = 'REQ';
        $year = date('Y');
        $month = date('m');
        
        // Get the last requisition number for this month
        $lastRequisition = self::where('requisition_number', 'like', "{$prefix}-{$year}{$month}-%")
                               ->orderBy('requisition_number', 'desc')
                               ->first();
        
        if ($lastRequisition) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastRequisition->requisition_number, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }
        
        return "{$prefix}-{$year}{$month}-{$newSequence}";
    }
}