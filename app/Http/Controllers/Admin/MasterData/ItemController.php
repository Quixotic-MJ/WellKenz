<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display items masterlist with pagination, search and filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'unit', 'currentStockRecord'])
            ->where('is_active', true);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('item_code', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->category}%");
            });
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where(function($q) {
                    $q->whereHas('currentStockRecord', function($subQ) {
                        $subQ->where('current_quantity', '>', 0)
                             ->where('current_quantity', '<=', \DB::raw('items.reorder_point'));
                    })
                    ->orWhere(function($subQ2) {
                        // Items without stock records but with reorder_point > 0 (treat as low stock)
                        $subQ2->doesntHave('currentStockRecord')
                              ->where('reorder_point', '>', 0);
                    });
                });
            } elseif ($request->stock_status === 'out') {
                $query->where(function($q) {
                    $q->whereHas('currentStockRecord', function($subQ) {
                        $subQ->where('current_quantity', '<=', 0);
                    })
                    ->orWhereDoesntHave('currentStockRecord');
                });
            } elseif ($request->stock_status === 'good') {
                $query->whereHas('currentStockRecord', function($q) {
                    $q->where('current_quantity', '>', 0)
                      ->where('current_quantity', '>', \DB::raw('items.reorder_point'));
                });
            }
        }

        $perPage = $request->get('per_page', 10);
        $items = $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Get categories for filter dropdown
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('Admin.master_files.item_masterlist', compact('items', 'categories'));
    }

    /**
     * Store a newly created item in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'required|string|max:50|unique:items,item_code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:raw_material,finished_good,semi_finished,supply',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:0',
        ]);

        try {
            $item = Item::create([
                'name' => $request->name,
                'item_code' => $request->item_code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'item_type' => $request->item_type,
                'cost_price' => $request->cost_price ?? 0,
                'selling_price' => $request->selling_price ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'shelf_life_days' => $request->shelf_life_days,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        $item->load(['category', 'unit', 'currentStockRecord']);
        return response()->json($item);
    }

    /**
     * Update the specified item in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'required|string|max:50|unique:items,item_code,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:raw_material,finished_good,semi_finished,supply',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:0',
        ]);

        try {
            $item->update([
                'name' => $request->name,
                'item_code' => $request->item_code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'item_type' => $request->item_type,
                'cost_price' => $request->cost_price ?? 0,
                'selling_price' => $request->selling_price ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'shelf_life_days' => $request->shelf_life_days,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified item from storage.
     *
     * @param \App\Models\Item $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        try {
            $itemName = $item->name;
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item data for editing.
     *
     * @return \Illuminate\Http\Response
     */
    public function getItemData()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $units = Unit::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'symbol']);

        return response()->json([
            'categories' => $categories,
            'units' => $units
        ]);
    }

    /**
     * Search items for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $items = Item::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('item_code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($items);
    }
}