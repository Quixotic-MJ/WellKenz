<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';
    protected $primaryKey = 'req_id';
    public $timestamps = true;

    protected $fillable = [
        'req_ref',
        'user_id',
        'ar_ref',
        'issued_date',
        'req_status',
    ];

    protected $casts = [
        'issued_date' => 'datetime',
    ];
}
