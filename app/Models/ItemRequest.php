<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_req_id';

    protected $fillable = [
        'item_req_name',
        'item_req_unit',
        'item_req_quantity',
        'item_req_description',
        'item_req_status',
        'requested_by',
        'approved_by',
        'item_req_reject_reason'
    ];

    protected $casts = [
        'item_req_quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who requested the item
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    /**
     * Get the user who approved the request
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('item_req_status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('item_req_status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('item_req_status', 'rejected');
    }

    /**
     * Scope for requests by specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }
}