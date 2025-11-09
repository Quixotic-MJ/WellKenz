<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'trans_id';
    protected $table = 'inventory_transactions';

    protected $fillable = [
        'trans_ref',
        'trans_type',
        'trans_quantity',
        'trans_date',
        'trans_remarks',
        'po_id',
        'trans_by',
        'item_id'
    ];

    protected $casts = [
        'trans_date' => 'date',
    ];

    // Relationship with Item
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    // Relationship with User (who performed the transaction)
    public function user()
    {
        return $this->belongsTo(User::class, 'trans_by', 'user_id');
    }

    // Relationship with PurchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    // Scope for incoming transactions
    public function scopeIncoming($query)
    {
        return $query->where('trans_type', 'in');
    }

    // Scope for outgoing transactions
    public function scopeOutgoing($query)
    {
        return $query->where('trans_type', 'out');
    }

    // Scope for adjustment transactions
    public function scopeAdjustment($query)
    {
        return $query->where('trans_type', 'adjustment');
    }

    // Get transaction type badge color
    public function getTypeBadgeAttribute()
    {
        $colors = [
            'in' => 'bg-green-100 text-green-800',
            'out' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-blue-100 text-blue-800',
        ];
        return $colors[$this->trans_type] ?? 'bg-gray-100 text-gray-800';
    }

    // Get transaction type display name
    public function getTypeDisplayAttribute()
    {
        $types = [
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
        ];
        return $types[$this->trans_type] ?? ucfirst($this->trans_type);
    }
}