<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'item_id';
    protected $table      = 'items';

    protected $fillable = [
        'item_code',
        'item_name',
        'item_description',
        'item_unit',
        'cat_id',
        'item_stock',
        'item_expire_date',
        'reorder_level',
        'min_stock_level',
        'max_stock_level',
        'is_active',
        'is_custom',
    ];

    protected $casts = [
        'item_stock'       => 'decimal:3',
        'reorder_level'    => 'decimal:3',
        'min_stock_level'  => 'decimal:3',
        'max_stock_level'  => 'decimal:3',
        'item_expire_date' => 'date',
        'is_active'        => 'boolean',
        'is_custom'        => 'boolean',
    ];

    /* --------------------------------------------------
     *  Use PostgreSQL function instead of Eloquent
     * -------------------------------------------------- */
    public static function allViaFunction()
    {
        return collect(DB::select('SELECT * FROM get_all_items()'));
    }

    public static function lowStockViaFunction()
    {
        return collect(DB::select('SELECT * FROM get_low_stock_items()'));
    }

    /* ---------------------------------------
     *  Relationships
     * --------------------------------------- */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }
}