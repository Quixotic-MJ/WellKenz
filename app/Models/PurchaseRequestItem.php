<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'quantity_requested',
        'unit_price_estimate',
        'total_estimated_cost',
        'notes'
    ];

    public $timestamps = false;

    protected $casts = [
        'quantity_requested' => 'decimal:3',
        'unit_price_estimate' => 'decimal:2',
        'total_estimated_cost' => 'decimal:2',
    ];

    // Relationships
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Accessors
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity_requested, 3);
    }

    public function getFormattedUnitPriceAttribute()
    {
        return '₱' . number_format($this->unit_price_estimate, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total_estimated_cost, 2);
    }

    public function getTotalCostAttribute()
    {
        return $this->quantity_requested * $this->unit_price_estimate;
    }

    // Auto-calculate total when saving
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($purchaseRequestItem) {
            if ($purchaseRequestItem->quantity_requested && $purchaseRequestItem->unit_price_estimate) {
                $purchaseRequestItem->total_estimated_cost = 
                    $purchaseRequestItem->quantity_requested * $purchaseRequestItem->unit_price_estimate;
            }
        });

        static::saved(function ($purchaseRequestItem) {
            // Update the parent purchase request total
            $purchaseRequest = $purchaseRequestItem->purchaseRequest;
            if ($purchaseRequest) {
                $total = $purchaseRequest->purchaseRequestItems()->sum('total_estimated_cost');
                $purchaseRequest->update(['total_estimated_cost' => $total]);
            }
        });

        static::deleted(function ($purchaseRequestItem) {
            // Update the parent purchase request total
            $purchaseRequest = $purchaseRequestItem->purchaseRequest;
            if ($purchaseRequest) {
                $total = $purchaseRequest->purchaseRequestItems()->sum('total_estimated_cost');
                $purchaseRequest->update(['total_estimated_cost' => $total]);
            }
        });
    }
}