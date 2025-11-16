<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_id';
    public $timestamps = true;

    protected $fillable = [
        'po_ref',
        'po_status',
        'expected_delivery_date',
        'total_amount',
        'sup_id',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'sup_id', 'sup_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'po_id', 'po_id');
    }
}
