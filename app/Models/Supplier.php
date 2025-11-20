<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'supplier_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'province',
        'postal_code',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'rating',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'rating' => 'integer',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}