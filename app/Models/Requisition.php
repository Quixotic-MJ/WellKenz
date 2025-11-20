<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $table = 'requisitions';

    protected $fillable = [
        'requisition_number',
        'request_date',
        'requested_by',
        'department',
        'purpose',
        'status',
        'total_estimated_value',
        'approved_by',
        'approved_at',
        'fulfilled_by',
        'fulfilled_at',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'total_estimated_value' => 'decimal:2',
        'status' => 'string',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}