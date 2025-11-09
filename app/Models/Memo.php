<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    protected $primaryKey = 'memo_id';
    protected $table = 'memos';

    protected $fillable = [
        'memo_ref',
        'memo_type',
        'memo_date',
        'po_id',
        'received_by',
        'remarks',
        'status'
    ];

    protected $casts = [
        'memo_date' => 'date',
    ];

    // Relationship with PurchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    // Relationship with User (who received the goods)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }

    // Scope for receiving memos
    public function scopeReceiving($query)
    {
        return $query->where('memo_type', 'receiving');
    }

    // Scope for completed memos
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}