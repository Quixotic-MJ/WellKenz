<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';
    protected $primaryKey = 'trans_id';
    public $timestamps = true;

    protected $fillable = [
        'trans_ref',
        'trans_type',
        'trans_quantity',
        'trans_date',
        'trans_remarks',
        'item_id',
        'po_id',
        'trans_by',
    ];

    protected $casts = [
        'trans_date' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'trans_by', 'user_id');
    }
}
