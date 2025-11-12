<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InventoryController extends Controller
{
    /* ----------  main overview page  ---------- */
    public function overview()
    {
        $total   = Item::count();
        $inStock = Item::where('item_stock', '>', 0)->count();
        $lowStock = Item::lowStockViaFunction()->count();
        $outStock = Item::where('item_stock', 0)->count();

        $items   = Item::allViaFunction();          // PostgreSQL function
        $lows    = Item::lowStockViaFunction()->take(5);

        return view('Inventory.inventory_list', compact(
            'total',
            'inStock',
            'lowStock',
            'outStock',
            'items',
            'lows'
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
            'item_stock' => 'nullable|numeric|min:0',
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
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.*', 'c.cat_name');

        if ($request->filled('category')) {
            $q->where('i.cat_id', $request->category);
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $q->where(function ($w) use ($s) {
                $w->where('i.item_code', 'ILIKE', $s)
                    ->orWhere('i.item_name', 'ILIKE', $s);
            });
        }
        if ($request->filled('low_stock') && $request->boolean('low_stock')) {
            $q->whereColumn('i.item_stock', '<=', 'i.reorder_level');
        }

        return $q->orderBy('i.item_name')->paginate(20);
    }

    /* ---------- API: inventory transactions log ---------- */
    public function apiTransactions(Request $request)
    {
        $q = DB::table('inventory_transactions as t')
            ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
            ->leftJoin('purchase_orders as p', 'p.po_id', '=', 't.po_id')
            ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
            ->select('t.*', 'i.item_code', 'i.item_name', 'i.item_unit', 'p.po_ref', 'u.name as trans_by_name');

        if ($request->filled('type')) $q->where('t.trans_type', $request->type);
        if ($request->filled('item_id')) $q->where('t.item_id', $request->item_id);
        if ($request->filled('po_id')) $q->where('t.po_id', $request->po_id);
        if ($request->filled('date_from')) $q->whereDate('t.trans_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('t.trans_date', '<=', $request->date_to);

        return $q->orderByDesc('t.trans_date')->paginate(20);
    }

    /**
     * API: single transaction details
     */
    public function apiTransactionShow($id)
    {
        $row = DB::table('inventory_transactions as t')
            ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
            ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
            ->leftJoin('purchase_orders as p', 'p.po_id', '=', 't.po_id')
            ->select(
                't.*',
                'i.item_code', 'i.item_name', 'i.item_unit',
                'u.name as user_name',
                'p.po_ref'
            )
            ->where(function($q) use ($id){
                $q->where('t.inventory_transaction_id', $id)
                  ->orWhere('t.trans_id', $id)
                  ->orWhere('t.id', $id);
            })
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $quantity = $row->trans_quantity ?? ($row->trans_qty ?? ($row->quantity ?? 0));
        $date     = $row->trans_date ?? ($row->created_at ?? null);
        $dateStr  = null;
        if ($date instanceof \DateTimeInterface) {
            $dateStr = $date->format('Y-m-d H:i:s');
        } elseif (!is_null($date)) {
            $dateStr = (string) $date;
        }

        return response()->json([
            'id'        => $row->inventory_transaction_id ?? ($row->trans_id ?? ($row->id ?? null)),
            'type'      => $row->trans_type,
            'quantity'  => (float) $quantity,
            'remarks'   => $row->trans_remarks ?? ($row->remarks ?? null),
            'date'      => $dateStr,
            'item_code' => $row->item_code,
            'item_name' => $row->item_name,
            'item_unit' => $row->item_unit,
            'user'      => $row->user_name,
            'po_ref'    => $row->po_ref,
        ]);
    }

    /* ---------- Admin: Inventory Transactions page ---------- */
    public function adminTransactions()
    {
        // counts
        $totalCount   = DB::table('inventory_transactions')->count();
        $inCount      = DB::table('inventory_transactions')->where('trans_type', 'in')->count();
        $outCount     = DB::table('inventory_transactions')->where('trans_type', 'out')->count();
        $adjCount     = DB::table('inventory_transactions')->where('trans_type', 'adjustment')->count();
        $negStockCount= DB::table('items')->where('item_stock', '<', 0)->count();
        $ackCount     = Schema::hasTable('acknowledge_receipts')
            ? DB::table('acknowledge_receipts')->count()
            : 0;

        // users for filter
        $users = DB::table('users')->select('user_id','name')->orderBy('name')->get();

        // transactions list
        $transactions = DB::table('inventory_transactions as t')
            ->leftJoin('items as i', 'i.item_id', '=', 't.item_id')
            ->leftJoin('users as u', 'u.user_id', '=', 't.trans_by')
            ->select(
                't.*',
                'i.item_name',
                'i.item_stock',
                'u.name as user_name'
            )
            ->orderByDesc('t.created_at')
            ->paginate(20);

        // transform to objects with expected fields used in blade
        $transactions->getCollection()->transform(function ($row) {
            $row->item = (object) ['item_name' => $row->item_name, 'current_stock' => $row->item_stock];
            $row->user = (object) ['name' => $row->user_name];
            // normalize names used in blade
            $row->quantity = $row->trans_quantity ?? ($row->trans_qty ?? ($row->quantity ?? 0));
            // id alias for blade
            $row->inventory_transaction_id = $row->trans_id ?? null;
            return $row;
        });

        return view('Admin.Inventory.inventory_transaction', compact(
            'totalCount', 'inCount', 'outCount', 'adjCount', 'negStockCount', 'ackCount',
            'users', 'transactions'
        ));
    }

    /* ---------- Admin: Category & Item Management page ---------- */
    public function adminItemManagement()
    {
        $categories = \App\Models\Category::all();

        $categoriesCount = DB::table('categories')->count();
        $totalItems      = DB::table('items')->count();
        $lowStockCount   = DB::table('items')->whereColumn('item_stock', '<=', 'reorder_level')->count();
        $expiringCount   = DB::table('items')
            ->whereNotNull('item_expire_date')
            ->whereBetween('item_expire_date', [DB::raw('CURRENT_DATE'), DB::raw("CURRENT_DATE + INTERVAL '30 days'")])
            ->count();

        $items = DB::table('items as i')
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.item_id', 'i.item_code', 'i.item_name', 'i.item_unit', 'i.item_stock', 'i.reorder_level', 'i.item_expire_date', 'c.cat_name')
            ->orderBy('i.item_name')
            ->limit(200)
            ->get();

        return view('Admin.Inventory.item_management', compact(
            'categories',
            'categoriesCount',
            'totalItems',
            'lowStockCount',
            'expiringCount',
            'items'
        ));
    }


    /* ---------- Category CRUD ---------- */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,cat_name',
        ]);

        DB::table('categories')->insert([
            'cat_name' => $request->category_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Category created successfully!']);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,cat_name,' . $id . ',cat_id',
        ]);

        DB::table('categories')->where('cat_id', $id)->update([
            'cat_name' => $request->category_name,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Category updated successfully!']);
    }

    public function destroyCategory($id)
    {
        // Check if category is used
        $inUse = DB::table('items')->where('cat_id', $id)->exists();
        if ($inUse) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with associated items.']);
        }

        DB::table('categories')->where('cat_id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully!']);
    }

    /* ---------- Item CRUD ---------- */
    public function showItem($id)
    {
        $item = DB::table('items as i')
            ->leftJoin('categories as c', 'c.cat_id', '=', 'i.cat_id')
            ->select('i.*', 'c.cat_name')
            ->where('i.item_id', $id)
            ->first();

        return response()->json($item);
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required',
            'item_unit' => 'required',
            'cat_id' => 'required|exists:categories,cat_id',
            'item_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'item_expire_date' => 'nullable|date',
        ]);

        DB::table('items')->where('item_id', $id)->update([
            'item_name' => $request->item_name,
            'item_unit' => $request->item_unit,
            'cat_id' => $request->cat_id,
            'item_stock' => $request->item_stock ?? 0,
            'reorder_level' => $request->reorder_level ?? 0,
            'item_expire_date' => $request->item_expire_date,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Item updated successfully!']);
    }

    public function destroyItem($id)
    {
        // Check if item has transactions
        $inUse = DB::table('inventory_transactions')->where('item_id', $id)->exists();
        if ($inUse) {
            return response()->json(['success' => false, 'message' => 'Cannot delete item with transaction history.']);
        }

        DB::table('items')->where('item_id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Item deleted successfully!']);
    }

    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'adjustment' => 'nullable|integer',
            'current_stock' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = DB::table('items')->where('item_id', $id)->first();
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
        }

        // Determine adjustment
        if ($request->filled('current_stock')) {
            $newStock = (int) $request->current_stock;
            $adjustment = $newStock - (int) $item->item_stock;
        } elseif ($request->filled('adjustment')) {
            $adjustment = (int) $request->adjustment;
            $newStock = (int) $item->item_stock + $adjustment;
        } else {
            return response()->json(['success' => false, 'message' => 'No stock value provided.'], 422);
        }

        if ($newStock < 0) {
            return response()->json(['success' => false, 'message' => 'Stock cannot be negative.'], 422);
        }

        DB::table('items')->where('item_id', $id)->update([
            'item_stock' => $newStock,
            'updated_at' => now(),
        ]);

        // Log transaction only when there is a change
        if ($adjustment !== 0) {
            DB::table('inventory_transactions')->insert([
                'item_id' => $id,
                'trans_type' => $adjustment > 0 ? 'stock_in' : 'stock_out',
                'trans_qty' => abs($adjustment),
                'trans_date' => now(),
                'trans_by' => Auth::id(),
                'trans_notes' => $request->reason ?? 'Manual adjustment',
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully!',
            'new_stock' => $newStock,
        ]);
    }
}
