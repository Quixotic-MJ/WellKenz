<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $table = 'production_orders';

    protected $fillable = [
        'production_number',
        'recipe_id',
        'planned_quantity',
        'actual_quantity',
        'unit_id',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'status',
        'total_material_cost',
        'total_labor_cost',
        'overhead_cost',
        'notes',
        'created_by',
        'supervisor_id'
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'datetime',
        'actual_end_date' => 'datetime',
        'total_material_cost' => 'decimal:2',
        'total_labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2'
    ];

    /**
     * Get the recipe for this production order.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the unit for this production order.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created this production order.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the supervisor assigned to this production order.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get all material consumption records for this production order.
     */
    public function consumptions()
    {
        return $this->hasMany(ProductionConsumption::class);
    }

    /**
     * Check if production is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if production is in progress.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if production is planned.
     */
    public function isPlanned()
    {
        return $this->status === 'planned';
    }

    /**
     * Get the total cost (material + labor + overhead).
     */
    public function getTotalCostAttribute()
    {
        return ($this->total_material_cost ?? 0) + 
               ($this->total_labor_cost ?? 0) + 
               ($this->overhead_cost ?? 0);
    }

    /**
     * Calculate the variance between planned and actual quantity.
     */
    public function getQuantityVarianceAttribute()
    {
        if (!$this->actual_quantity) {
            return 0;
        }
        
        return $this->actual_quantity - $this->planned_quantity;
    }

    /**
     * Calculate the percentage variance.
     */
    public function getQuantityVariancePercentageAttribute()
    {
        if (!$this->planned_quantity || !$this->actual_quantity) {
            return 0;
        }
        
        return (($this->actual_quantity - $this->planned_quantity) / $this->planned_quantity) * 100;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get only completed productions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get productions for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('planned_start_date', Carbon::today());
    }
}