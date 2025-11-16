<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'sup_id';
    public $timestamps = true;

    protected $fillable = [
        'sup_name',
        'sup_contact',
        'sup_email',
        'sup_status',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'sup_id', 'sup_id');
    }
}
