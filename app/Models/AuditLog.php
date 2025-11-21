<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';
    
    // Audit logs should only have created_at, not updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'record_id' => 'integer',
    ];

    /**
     * Accessor for old_values to ensure proper array conversion
     */
    public function getOldValuesAttribute($value)
    {
        if (is_string($value)) {
            try {
                return json_decode($value, true);
            } catch (\Exception $e) {
                return [];
            }
        }
        return $value ?: [];
    }

    /**
     * Accessor for new_values to ensure proper array conversion
     */
    public function getNewValuesAttribute($value)
    {
        if (is_string($value)) {
            try {
                return json_decode($value, true);
            } catch (\Exception $e) {
                return [];
            }
        }
        return $value ?: [];
    }

    /**
     * Mutator for old_values to ensure proper JSON encoding
     */
    public function setOldValuesAttribute($value)
    {
        $this->attributes['old_values'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Mutator for new_values to ensure proper JSON encoding
     */
    public function setNewValuesAttribute($value)
    {
        $this->attributes['new_values'] = is_array($value) ? json_encode($value) : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}