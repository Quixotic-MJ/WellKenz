<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtvTransaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'rtv_transactions';

    protected $fillable = [
        'rtv_number',
        'purchase_order_id',
        'supplier_id',
        'return_date',
        'status',
        'total_value',
        'notes',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Override to handle missing updated_at in database
    public function getUpdatedAtColumn()
    {
        return null; // Don't use updated_at column
    }

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rtvItems(): HasMany
    {
        return $this->hasMany(RtvItem::class, 'rtv_id');
    }

    // Helper methods
    public function getFormattedRtvNumberAttribute(): string
    {
        return '#' . $this->rtv_number;
    }

    public function getFormattedPoNumberAttribute(): string
    {
        return $this->purchaseOrder ? '#' . $this->purchaseOrder->po_number : '';
    }

    public function getStatusBadgeAttribute(): array
    {
        $statusConfig = [
            'pending' => ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Pending Credit'],
            'processed' => ['class' => 'bg-blue-100 text-blue-800', 'label' => 'In Process'],
            'completed' => ['class' => 'bg-green-100 text-green-800', 'label' => 'Credit Received'],
            'cancelled' => ['class' => 'bg-red-100 text-red-800', 'label' => 'Cancelled'],
        ];

        $config = $statusConfig[$this->status] ?? $statusConfig['pending'];
        
        return [
            'class' => 'px-2 py-1 text-xs font-medium rounded-full ' . $config['class'],
            'label' => $config['label']
        ];
    }

    public function getFormattedTotalValueAttribute(): string
    {
        return '₱' . number_format($this->total_value, 2);
    }

    public function getReturnDateFormattedAttribute(): string
    {
        return $this->return_date->format('M d, Y');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('return_date', date('Y'));
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }
}