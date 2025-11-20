<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'payment_terms',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isOrdered()
    {
        return in_array($this->status, ['sent', 'confirmed']);
    }

    public function isDelivered()
    {
        return $this->status === 'completed';
    }

    public function getAverageDeliveryTimeInDays()
    {
        if ($this->actual_delivery_date && $this->order_date) {
            return $this->order_date->diffInDays($this->actual_delivery_date);
        }
        return null;
    }
}