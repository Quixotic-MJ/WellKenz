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
        'req_reject_reason', // Added to fillable
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

    // Accessor for reject reason (if empty, return default message)
    public function getRejectReasonDisplayAttribute()
    {
        return $this->req_reject_reason ?: 'No reason provided';
    }

    // Check if requisition is rejected
    public function getIsRejectedAttribute()
    {
        return $this->req_status === 'rejected';
    }

    // Check if requisition is approved
    public function getIsApprovedAttribute()
    {
        return $this->req_status === 'approved';
    }

    // Check if requisition is pending
    public function getIsPendingAttribute()
    {
        return $this->req_status === 'pending';
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

    public function scopeRejected($query)
    {
        return $query->where('req_status', 'rejected');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('req_priority', 'high');
    }

    // Get status with color for display
    public function getStatusWithColorAttribute()
    {
        $statusColors = [
            'pending' => 'amber',
            'approved' => 'green',
            'rejected' => 'red',
            'completed' => 'blue'
        ];

        return [
            'status' => $this->req_status,
            'color' => $statusColors[$this->req_status] ?? 'gray',
            'display' => ucfirst($this->req_status)
        ];
    }
}