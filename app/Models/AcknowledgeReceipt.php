<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcknowledgeReceipt extends Model
{
    use HasFactory;

    protected $table = 'acknowledge_receipts';
    protected $primaryKey = 'ar_id';
    public $timestamps = true;

    protected $fillable = [
        'ar_ref', 'ar_remarks', 'ar_status', 'issued_date', 'req_id', 'issued_by', 'issued_to',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by', 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'issued_to', 'user_id');
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'req_id', 'req_id');
    }
}