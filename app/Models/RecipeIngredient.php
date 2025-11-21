<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $table = 'recipe_ingredients';

    protected $fillable = [
        'recipe_id',
        'item_id',
        'quantity_required',
        'unit_id',
        'is_optional',
        'notes'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
        'is_optional' => 'boolean'
    ];

    /**
     * Get the recipe this ingredient belongs to.
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the item (ingredient).
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the unit for this ingredient.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Calculate the total cost for this ingredient quantity.
     */
    public function getTotalCostAttribute()
    {
        return $this->quantity_required * ($this->item->cost_price ?? 0);
    }
}