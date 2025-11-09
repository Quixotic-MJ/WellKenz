<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'cat_id';
    protected $table = 'categories';

    protected $fillable = [
        'cat_name'
    ];

    // Relationship with Items
    public function items()
    {
        return $this->hasMany(Item::class, 'cat_id', 'cat_id');
    }

    // Accessor for name (to match code expectations)
    public function getNameAttribute()
    {
        return $this->cat_name;
    }

    // Accessor for items count
    public function getItemsCountAttribute()
    {
        return $this->items->count();
    }
}