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
        'memo_remarks',
        'received_date',
        'received_by',
        'po_ref',
        'attachment',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    // Relationship with PurchaseOrder by po_ref (string key)
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_ref', 'po_ref');
    }

    // Relationship with User (who received the goods)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }
}