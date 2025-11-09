<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $primaryKey = 'req_id';

    protected $fillable = [
        'req_ref',
        'req_purpose',
        'req_priority',
        'req_status',
        'req_date',
        'approved_date',
        'requested_by',
        'approved_by'
    ];

    protected $casts = [
        'req_date' => 'date',
        'approved_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship with requester
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    // Relationship with approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    // Relationship with requisition items
    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'req_id', 'req_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('req_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('req_status', 'approved');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('req_priority', 'high');
    }
}