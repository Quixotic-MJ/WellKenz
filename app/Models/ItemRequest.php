<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    use HasFactory;

    protected $table = 'item_requests';
    protected $primaryKey = 'item_req_id';
    public $timestamps = true;

    protected $fillable = [
        'item_req_name', 'item_req_unit', 'item_req_quantity', 'item_req_description',
        'item_req_status', 'requested_by', 'approved_by', 'item_req_reject_reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Default status to pending
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->item_req_status = $model->item_req_status ?? 'pending';
            $model->requested_by = $model->requested_by ?? Auth::id();
        });
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }
}