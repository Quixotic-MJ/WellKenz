<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Requisition extends Model
{
    use HasFactory;

    protected $table = 'requisitions';
    protected $primaryKey = 'req_id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'req_ref',
        'req_purpose',
        'req_priority',
        'req_status',
        'req_date',
        'approved_date',
        'req_reject_reason',
        'requested_by',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'req_date'      => 'date',
        'approved_date' => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * This automatically handles default values and the req_ref.
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * This 'creating' event fires *BEFORE* the database insert.
         * We set default values and a TEMPORARY, UNIQUE req_ref
         * to pass the 'not-null' database constraint.
         */
        static::creating(function ($model) {
            $model->req_status = $model->req_status ?? 'pending';
            $model->req_date = $model->req_date ?? now()->toDateString();
            $model->requested_by = $model->requested_by ?? Auth::id();

            // **** THIS IS THE FIX ****
            // Set a temporary, unique value to pass the NOT NULL constraint.
            if (empty($model->req_ref)) {
                $model->req_ref = 'TEMP-' . uniqid() . '-' . microtime(true); 
            }
        });

        /**
         * This 'created' event fires *AFTER* the database insert.
         * Now we have the model's 'req_id', so we can create the
         * *real* formatted req_ref (e.g., "RQ-00007").
         */
        static::created(function ($model) {
            // Check if the ref is our temporary one
            if (str_starts_with($model->req_ref, 'TEMP-')) {
                // Now we have the req_id, so we generate the final ref
                $model->req_ref = 'RQ-' . str_pad($model->req_id, 5, '0', STR_PAD_LEFT);
                
                // Save *without* firing events again, to prevent loops
                $model->saveQuietly(); 
            }
        });
    }

    // ===== RELATIONSHIPS =====

    /**
     * Get the user who requested this.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    /**
     * Get the user who approved this.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Get all items on this requisition.
     */
    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'req_id', 'req_id');
    }

    /**
     * Get the purchase order associated with this requisition.
     */
    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'req_id', 'req_id');
    }
}