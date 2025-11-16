<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    protected $table = 'memos';
    protected $primaryKey = 'memo_id';
    public $timestamps = true;

    protected $fillable = [
        'memo_ref',
        'memo_remarks',
        'received_date',
        'received_by',
        'po_ref',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];
}
