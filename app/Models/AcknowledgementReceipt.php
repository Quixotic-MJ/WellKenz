<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcknowledgementReceipt extends Model
{
    protected $table = 'acknowledge_receipts';
    protected $primaryKey = 'ar_id';
    public $timestamps = true;

    protected $fillable = [
        'ar_ref',
        'ar_status',
        'issued_date',
        'req_id',
        'issued_by',
        'issued_to',
        'ar_remarks',
    ];

    protected $casts = [
        'issued_date' => 'datetime',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'req_id', 'req_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'issued_to', 'id');
    }
}
