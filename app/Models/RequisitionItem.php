<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'req_item_id';

    protected $fillable = [
        'req_id',
        'item_id',
        'req_item_quantity',
        'req_item_status',
        'item_unit'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship with requisition
    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'req_id', 'req_id');
    }

    // Relationship with item
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}