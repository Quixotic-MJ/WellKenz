<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $table = 'recipes';

    protected $fillable = [
        'recipe_code',
        'name',
        'description',
        'finished_item_id',
        'yield_quantity',
        'yield_unit_id',
        'preparation_time',
        'cooking_time',
        'serving_size',
        'instructions',
        'notes',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'yield_quantity' => 'decimal:3',
        'serving_size' => 'decimal:3',
        'preparation_time' => 'integer',
        'cooking_time' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Get the finished item that this recipe produces.
     */
    public function finishedItem()
    {
        return $this->belongsTo(Item::class, 'finished_item_id');
    }

    /**
     * Get the yield unit.
     */
    public function yieldUnit()
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    /**
     * Get the user who created this recipe.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all ingredients for this recipe.
     */
    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }



    /**
     * Check if recipe is active.
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get the total estimated cost of all ingredients.
     */
    public function getTotalIngredientCostAttribute()
    {
        return $this->ingredients->sum(function ($ingredient) {
            return $ingredient->quantity_required * $ingredient->item->cost_price;
        });
    }

    /**
     * Scope to get only active recipes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}