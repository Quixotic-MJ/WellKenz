<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtvItem extends Model
{
    use HasFactory;

    protected $table = 'rtv_items';

    protected $fillable = [
        'rtv_id',
        'item_id',
        'quantity_returned',
        'unit_cost',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Override to handle missing updated_at in database
    public function getUpdatedAtColumn()
    {
        return null; // Don't use updated_at column
    }

    // Relationships
    public function rtvTransaction(): BelongsTo
    {
        return $this->belongsTo(RtvTransaction::class, 'rtv_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Helper methods
    public function getTotalCostAttribute(): float
    {
        return $this->quantity_returned * $this->unit_cost;
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '₱' . number_format($this->total_cost, 2);
    }

    public function getFormattedQuantityAttribute(): string
    {
        $unit = $this->item->unit ?? null;
        $unitSymbol = $unit ? $unit->symbol : 'pcs';
        return number_format($this->quantity_returned, 3) . ' ' . $unitSymbol;
    }

    public function getFormattedUnitCostAttribute(): string
    {
        $unit = $this->item->unit ?? null;
        $unitSymbol = $unit ? $unit->symbol : 'pcs';
        return '₱' . number_format($this->unit_cost, 2) . '/' . $unitSymbol;
    }

    public function getItemDisplayAttribute(): string
    {
        $itemName = $this->item ? $this->item->name : 'Unknown Item';
        $unit = $this->item->unit ?? null;
        $unitSymbol = $unit ? $unit->symbol : 'pcs';
        
        return $itemName . ' (' . number_format($this->quantity_returned, 0) . ' ' . $unitSymbol . ')';
    }

    public function getReasonDisplayAttribute(): string
    {
        return '"' . $this->reason . '"';
    }

    // Scopes
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeWithReason($query, $reason)
    {
        return $query->where('reason', 'like', "%{$reason}%");
    }
}