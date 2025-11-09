<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_req_id';
    protected $table = 'item_requests';

    protected $fillable = [
        'item_req_name',
        'item_req_unit',
        'item_req_quantity',
        'item_req_description',
        'item_req_status',
        'requested_by',
        'approved_by'
    ];

    protected $casts = [
        'item_req_quantity' => 'integer',
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

    // Scopes
    public function scopePending($query)
    {
        return $query->where('item_req_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('item_req_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('item_req_status', 'rejected');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    // Check if request is pending
    public function getIsPendingAttribute()
    {
        return $this->item_req_status === 'pending';
    }

    // Check if request is approved
    public function getIsApprovedAttribute()
    {
        return $this->item_req_status === 'approved';
    }

    // Check if request is rejected
    public function getIsRejectedAttribute()
    {
        return $this->item_req_status === 'rejected';
    }

    // Get status badge color
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'approved' => 'bg-green-100 text-green-800 border-green-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200'
        ];

        return $colors[$this->item_req_status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    // Get priority badge (if you add priority field later)
    public function getPriorityBadgeAttribute()
    {
        $priorities = [
            'low' => 'bg-green-100 text-green-800 border-green-200',
            'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'high' => 'bg-red-100 text-red-800 border-red-200'
        ];

        return $priorities[$this->priority] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }
}