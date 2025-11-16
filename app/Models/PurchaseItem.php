<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';
    protected $primaryKey = 'pi_id';
    public $timestamps = true;

    protected $fillable = [
        'po_id',
        'item_id',
        'ordered_quantity',
        'received_quantity',
        'pi_unit_price',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
