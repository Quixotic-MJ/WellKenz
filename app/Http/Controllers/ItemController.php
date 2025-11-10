<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\User; // Add this import

class ItemController extends Controller
{
    /**
     * Get items for requisition with stock information
     */
    public function getItemsForRequisition(Request $request)
    {
        try {
            Log::info('Getting items for requisition');
            
            $items = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.item_stock', '>', 0)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.cat_id',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->orderBy('i.item_name')
                ->get();

            Log::info('Items retrieved: ' . $items->count());

            return response()->json($items);

        } catch (\Exception $e) {
            Log::error('Error in getItemsForRequisition: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to load inventory items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific item details
     */
    public function getItemDetails($id)
    {
        try {
            $item = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.item_id', $id)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.item_expire_date',
                    'i.reorder_level',
                    'i.min_stock_level',
                    'i.max_stock_level',
                    'i.cat_id',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->first();

            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            return response()->json($item);

        } catch (\Exception $e) {
            Log::error('Error in getItemDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load item details'], 500);
        }
    }

    /**
     * Create new item
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|max:50|unique:items,item_code',
            'item_name' => 'required|max:255',
            'item_description' => 'nullable',
            'item_unit' => 'required|max:20',
            'cat_id' => 'required|exists:categories,cat_id',
            'item_stock' => 'nullable|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'reorder_level' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'is_custom' => 'nullable|boolean'        // ← NEW
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = new Item();
            $item->item_code = $request->item_code;
            $item->item_name = $request->item_name;
            $item->item_description = $request->item_description;
            $item->item_unit = $request->item_unit;
            $item->cat_id = $request->cat_id;
            $item->item_stock = $request->item_stock ?? 0;
            $item->item_expire_date = $request->item_expire_date;
            $item->reorder_level = $request->reorder_level ?? 0;
            $item->min_stock_level = $request->min_stock_level ?? 0;
            $item->max_stock_level = $request->max_stock_level;
            $item->is_active = true;
            $item->is_custom = $request->boolean('is_custom');        // ← NEW
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'item_id' => $item->item_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing item
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|max:50|unique:items,item_code,' . $id . ',item_id',
            'item_name' => 'required|max:255',
            'item_description' => 'nullable',
            'item_unit' => 'required|max:20',
            'cat_id' => 'required|exists:categories,cat_id',
            'item_stock' => 'required|numeric|min:0',
            'item_expire_date' => 'nullable|date',
            'reorder_level' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'is_custom' => 'nullable|boolean'        // ← NEW
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = Item::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $item->item_code = $request->item_code;
            $item->item_name = $request->item_name;
            $item->item_description = $request->item_description;
            $item->item_unit = $request->item_unit;
            $item->cat_id = $request->cat_id;
            $item->item_stock = $request->item_stock;
            $item->item_expire_date = $request->item_expire_date;
            $item->reorder_level = $request->reorder_level;
            $item->min_stock_level = $request->min_stock_level;
            $item->max_stock_level = $request->max_stock_level;
            $item->is_custom = $request->boolean('is_custom');        // ← NEW
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete item (soft delete)
     */
    public function destroy($id)
    {
        try {
            $item = Item::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $item->is_active = false;
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update item stock
     */
    public function updateStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity_change' => 'required|numeric',
            'transaction_type' => 'nullable|in:STOCK_IN,STOCK_OUT,ADJUSTMENT'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = Item::find($id);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $oldStock = $item->item_stock;
            $newStock = $oldStock + $request->quantity_change;

            if ($newStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock'
                ], 422);
            }

            $item->item_stock = $newStock;
            $item->last_updated = now();
            $item->save();

            // Log stock transaction
            $transType = match($request->transaction_type ?? 'ADJUSTMENT') {
                'STOCK_IN' => 'in',
                'STOCK_OUT' => 'out',
                'ADJUSTMENT' => 'adjustment',
                default => 'adjustment'
            };

            DB::table('inventory_transactions')->insert([
                'trans_ref' => 'STK-' . now()->format('YmdHis') . '-' . $id,
                'trans_type' => $transType,
                'trans_quantity' => $request->quantity_change,
                'trans_date' => now(),
                'trans_remarks' => 'Stock update via API',
                'po_id' => null,
                'trans_by' => Auth::id(),
                'item_id' => $id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'old_stock' => $oldStock,
                'new_stock' => $newStock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStock()
    {
        try {
            $lowStockItems = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.item_stock', '<=', DB::raw('i.reorder_level'))
                ->where('i.is_active', true)
                ->where('i.item_stock', '>', 0)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_unit',
                    'i.item_stock',
                    'i.reorder_level',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->orderBy('i.item_stock')
                ->get();

            return response()->json($lowStockItems);

        } catch (\Exception $e) {
            Log::error('Error in getLowStock: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load low stock items'], 500);
        }
    }

    /**
     * Get items by category
     */
    public function getItemsByCategory($categoryId)
    {
        try {
            $items = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.cat_id', $categoryId)
                ->where('i.item_stock', '>', 0)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.cat_id',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->orderBy('i.item_name')
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            Log::error('Error in getItemsByCategory: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load category items'], 500);
        }
    }

    /**
     * Search items
     */
    public function searchItems(Request $request)
    {
        try {
            $searchTerm = $request->get('q', '');

            $items = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where(function($query) use ($searchTerm) {
                    $query->where('i.item_name', 'ILIKE', "%{$searchTerm}%")
                          ->orWhere('i.item_code', 'ILIKE', "%{$searchTerm}%")
                          ->orWhere('c.cat_name', 'ILIKE', "%{$searchTerm}%");
                })
                ->where('i.item_stock', '>', 0)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.cat_id',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->orderBy('i.item_name')
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            Log::error('Error in searchItems: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search items'], 500);
        }
    }

    /**
     * Get expiry alerts
     */
    public function getExpiryAlerts(Request $request)
    {
        try {
            $daysThreshold = $request->get('days', 30);
            $currentDate = now()->format('Y-m-d');
            $thresholdDate = now()->addDays($daysThreshold)->format('Y-m-d');

            $expiryAlerts = DB::table('items')
                ->whereNotNull('item_expire_date')
                ->where('item_expire_date', '<=', $thresholdDate)
                ->where('item_expire_date', '>=', $currentDate)
                ->where('is_active', true)
                ->select(
                    'item_id',
                    'item_code',
                    'item_name',
                    'item_unit',
                    'item_stock',
                    'item_expire_date',
                    'is_custom'        // ← NEW
                )
                ->orderBy('item_expire_date')
                ->get();

            return response()->json($expiryAlerts);
        } catch (\Exception $e) {
            Log::error('Error in getExpiryAlerts: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load expiry alerts'], 500);
        }
    }

    /**
     * Show method - for displaying single item (if needed for web views)
     */
    public function show($id)
    {
        try {
            $item = DB::table('items as i')
                ->leftJoin('categories as c', 'i.cat_id', '=', 'c.cat_id')
                ->where('i.item_id', $id)
                ->where('i.is_active', true)
                ->select(
                    'i.item_id',
                    'i.item_code',
                    'i.item_name',
                    'i.item_description',
                    'i.item_unit',
                    'i.item_stock',
                    'i.item_expire_date',
                    'i.reorder_level',
                    'i.min_stock_level',
                    'i.max_stock_level',
                    'i.cat_id',
                    'c.cat_name',
                    'i.is_custom'        // ← NEW
                )
                ->first();

            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            return response()->json($item);

        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load item'], 500);
        }
    }
}