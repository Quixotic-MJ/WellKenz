<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batches';

    protected $fillable = [
        'batch_number',
        'item_id',
        'quantity',
        'unit_cost',
        'manufacturing_date',
        'expiry_date',
        'supplier_id',
        'location',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            // Validate batch number format to prevent problematic patterns
            if ($batch->batch_number) {
                $batchNumber = trim($batch->batch_number);
                
                // Block problematic patterns
                if (preg_match('/^(N\/A|NA)-/', $batchNumber)) {
                    throw new \InvalidArgumentException('Batch number cannot start with "N/A" or "NA-". Please use a valid batch number format.');
                }
                
                // Ensure batch number is not too generic
                if (strlen($batchNumber) < 8) {
                    throw new \InvalidArgumentException('Batch number must be at least 8 characters long.');
                }
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isExpiringSoon($days = 7)
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date <= now()->addDays($days);
    }
}