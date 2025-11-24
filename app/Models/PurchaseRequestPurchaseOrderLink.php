<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestPurchaseOrderLink extends Model
{
    public $timestamps = false; // Disable automatic timestamps since table only has created_at and consolidated_at

    protected $table = 'purchase_request_purchase_order_link';
    
    protected $fillable = [
        'purchase_request_id',
        'purchase_order_id',
        'consolidated_by'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function consolidatedBy()
    {
        return $this->belongsTo(User::class, 'consolidated_by');
    }
}