<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /* ----------  main overview page  ---------- */
    public function overview()
    {
        $total   = Item::count();
        $inStock = Item::where('item_stock', '>', 0)->count();
        $lowStock= Item::lowStockViaFunction()->count();
        $outStock= Item::where('item_stock', 0)->count();

        $items   = Item::allViaFunction();          // PostgreSQL function
        $lows    = Item::lowStockViaFunction()->take(5);

        return view('Inventory.inventory_list', compact(
            'total','inStock','lowStock','outStock','items','lows'
        ));
    }

    /* ----------  create new item (modal form)  ---------- */
    public function store(Request $request)
    {
        $request->validate([
            'item_code' => 'required|unique:items,item_code',
            'item_name' => 'required',
            'item_unit' => 'required',
            'cat_id'    => 'required|exists:categories,cat_id',
            'item_stock'=> 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'is_custom' => 'nullable|boolean',
        ]);

        $res = DB::select("SELECT create_item(
            ?::varchar, ?::varchar, ?::text, ?::varchar, ?::int,
            ?::numeric, ?::date, ?::numeric, ?::numeric, ?::numeric, ?::boolean
        ) AS result", [
            $request->item_code,
            $request->item_name,
            $request->item_description,
            $request->item_unit,
            $request->cat_id,
            $request->item_stock        ?? 0,
            $request->item_expire_date  ?? null,
            $request->reorder_level     ?? 0,
            $request->min_stock_level   ?? 0,
            $request->max_stock_level   ?? null,
            $request->boolean('is_custom')
        ])[0]->result;

        return response()->json($res);
    }

    /* ---------- API: inventory list with basic filters ---------- */
    public function apiList(Request $request)
    {
        $q = DB::table('items as i')
            ->leftJoin('categories as c','c.cat_id','=','i.cat_id')
            ->select('i.*','c.cat_name');

        if ($request->filled('category')) {
            $q->where('i.cat_id', $request->category);
        }
        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $q->where(function($w) use ($s){
                $w->where('i.item_code','ILIKE',$s)
                  ->orWhere('i.item_name','ILIKE',$s);
            });
        }
        if ($request->filled('low_stock') && $request->boolean('low_stock')) {
            $q->whereColumn('i.item_stock','<=','i.reorder_level');
        }

        return $q->orderBy('i.item_name')->paginate(20);
    }

    /* ---------- API: inventory transactions log ---------- */
    public function apiTransactions(Request $request)
    {
        $q = DB::table('inventory_transactions as t')
            ->leftJoin('items as i','i.item_id','=','t.item_id')
            ->leftJoin('purchase_orders as p','p.po_id','=','t.po_id')
            ->leftJoin('users as u','u.user_id','=','t.trans_by')
            ->select('t.*','i.item_code','i.item_name','i.item_unit','p.po_ref','u.name as trans_by_name');

        if ($request->filled('type')) $q->where('t.trans_type', $request->type);
        if ($request->filled('item_id')) $q->where('t.item_id', $request->item_id);
        if ($request->filled('po_id')) $q->where('t.po_id', $request->po_id);
        if ($request->filled('date_from')) $q->whereDate('t.trans_date','>=',$request->date_from);
        if ($request->filled('date_to')) $q->whereDate('t.trans_date','<=',$request->date_to);

        return $q->orderByDesc('t.trans_date')->paginate(20);
    }
}