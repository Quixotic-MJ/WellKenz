<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'item_id',
        'movement_type',
        'reference_number',
        'quantity',
        'unit_cost',
        'total_cost',
        'batch_number',
        'expiry_date',
        'location',
        'notes',
        'user_id'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime'
    ];

    /**
     * Get the item for this stock movement.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user who created this stock movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the batch this stock movement belongs to.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_number', 'batch_number');
    }

    /**
     * Scope to filter by movement type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get only positive movements (additions).
     */
    public function scopePositive($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope to get only negative movements (reductions).
     */
    public function scopeNegative($query)
    {
        return $query->where('quantity', '<', 0);
    }
}