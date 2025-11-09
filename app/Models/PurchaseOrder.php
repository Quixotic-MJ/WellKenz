<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'po_id';
    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_ref',
        'po_status',
        'order_date',
        'delivery_address',
        'expected_delivery_date',
        'total_amount',
        'sup_id',
        'req_id'
    ];
}