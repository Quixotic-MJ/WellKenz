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
        // Get filter to show active, inactive, or all items
        $statusFilter = $request->get('status', 'all');
        
        $query = Item::with(['category', 'unit', 'currentStockRecord']);
        
        // Apply status filter
        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }
        // If statusFilter is 'all', don't apply any status filter

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
            'item_code' => 'nullable|string|max:50|unique:items,item_code',
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
            // Generate item code if not provided
            $itemCode = $request->item_code;
            if (empty($itemCode)) {
                $itemCode = $this->generateNextItemCode($request->category_id);
            }

            // Check for duplicate item_code
            $existingItem = Item::where('item_code', $itemCode)->first();
            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'message' => "Item code '{$itemCode}' already exists. Please choose a different code or select a different category.",
                    'error_type' => 'duplicate_code'
                ], 422);
            }

            $item = Item::create([
                'name' => $request->name,
                'item_code' => $itemCode,
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
        // Additional validation to prevent null binding issues
        if (!$item || !$item->exists || $item->id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item specified for editing.'
            ], 400);
        }
        
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
        // Additional validation to prevent null binding issues
        if (!$item || !$item->exists || $item->id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item specified for updating.'
            ], 400);
        }
        
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
        // Additional validation to prevent null binding issues
        if (!$item || !$item->exists || $item->id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item specified for deletion.'
            ], 400);
        }
        
        try {
            // Check for foreign key dependencies before deletion
            $dependencies = [];
            
            // Check if item is used in recipes as finished product
            $recipesAsFinishedProduct = \DB::table('recipes')
                ->where('finished_item_id', $item->id)
                ->count();
            if ($recipesAsFinishedProduct > 0) {
                $dependencies[] = "used in {$recipesAsFinishedProduct} recipe(s) as finished product";
            }
            
            // Check if item is used in recipe ingredients
            $recipeIngredients = \DB::table('recipe_ingredients')
                ->where('item_id', $item->id)
                ->count();
            if ($recipeIngredients > 0) {
                $dependencies[] = "used as ingredient in {$recipeIngredients} recipe(s)";
            }
            
            // Check if item has purchase order items
            $purchaseOrderItems = \DB::table('purchase_order_items')
                ->where('item_id', $item->id)
                ->count();
            if ($purchaseOrderItems > 0) {
                $dependencies[] = "referenced in {$purchaseOrderItems} purchase order item(s)";
            }
            
            // Check if item has requisition items
            $requisitionItems = \DB::table('requisition_items')
                ->where('item_id', $item->id)
                ->count();
            if ($requisitionItems > 0) {
                $dependencies[] = "referenced in {$requisitionItems} requisition item(s)";
            }
            
            // Check if item has stock movements
            $stockMovements = \DB::table('stock_movements')
                ->where('item_id', $item->id)
                ->count();
            if ($stockMovements > 0) {
                $dependencies[] = "has {$stockMovements} stock movement record(s)";
            }
            
            // If there are dependencies, prevent deletion
            if (!empty($dependencies)) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete item '{$item->name}' because it is: " . implode(', ', $dependencies) . ". Please remove these dependencies first or consider deactivating the item instead."
                ], 422);
            }
            
            // Safe to delete - no dependencies found
            $itemName = $item->name;
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            // Handle other potential database errors
            if (strpos($e->getMessage(), 'foreign key') !== false || 
                strpos($e->getMessage(), 'constraint') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete item '{$item->name}' because it is referenced by other records. Please check and remove these references first."
                ], 422);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Alternative: Soft delete by deactivating the item
     * This preserves referential integrity while allowing "deletion"
     */
    public function deactivate(Item $item)
    {
        // Additional validation to prevent null binding issues
        if (!$item || !$item->exists || $item->id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item specified for deactivation.'
            ], 400);
        }
        
        try {
            $itemName = $item->name;
            $item->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' deactivated successfully! (Use Delete only if item has no dependencies)"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deactivating item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a deactivated item
     */
    public function reactivate(Item $item)
    {
        // Additional validation to prevent null binding issues
        if (!$item || !$item->exists || $item->id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid item specified for reactivation.'
            ], 400);
        }
        
        try {
            $itemName = $item->name;
            $item->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' reactivated successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reactivating item: ' . $e->getMessage()
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

    /**
     * Generate next available item code for a category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateItemCode(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'Category ID is required'
            ], 400);
        }

        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Generate category code (first 3 letters of category name)
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3));
        
        // Get the highest sequence number for this category
        $lastItem = Item::whereHas('category', function($q) use ($categoryId) {
                $q->where('id', $categoryId);
            })
            ->where('item_code', 'like', $categoryCode . '%')
            ->orderBy('item_code', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastItem) {
            // Extract the numeric part from the last item code
            $lastCode = $lastItem->item_code;
            $pattern = '/^' . preg_quote($categoryCode, '/') . '-(\d+)$/';
            if (preg_match($pattern, $lastCode, $matches)) {
                $nextSequence = intval($matches[1]) + 1;
            }
        }

        // Generate the new item code
        $newItemCode = $categoryCode . '-' . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

        // Ensure uniqueness by checking if this code already exists
        $counter = 0;
        while (Item::where('item_code', $newItemCode)->exists()) {
            $nextSequence++;
            $newItemCode = $categoryCode . '-' . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            $counter++;
            
            // Safety check to prevent infinite loop
            if ($counter > 1000) {
                break;
            }
        }

        return response()->json([
            'success' => true,
            'item_code' => $newItemCode
        ]);
    }

    /**
     * Helper method to generate next available item code for a category.
     *
     * @param int $categoryId
     * @return string
     */
    private function generateNextItemCode($categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            throw new \Exception('Category not found');
        }

        // Generate category code (first 3 letters of category name)
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $category->name), 0, 3));
        
        // Get the highest sequence number for this category
        $lastItem = Item::whereHas('category', function($q) use ($categoryId) {
                $q->where('id', $categoryId);
            })
            ->where('item_code', 'like', $categoryCode . '%')
            ->orderBy('item_code', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastItem) {
            // Extract the numeric part from the last item code
            $lastCode = $lastItem->item_code;
            $pattern = '/^' . preg_quote($categoryCode, '/') . '-(\d+)$/';
            if (preg_match($pattern, $lastCode, $matches)) {
                $nextSequence = intval($matches[1]) + 1;
            }
        }

        // Generate the new item code
        $newItemCode = $categoryCode . '-' . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

        // Ensure uniqueness by checking if this code already exists
        $counter = 0;
        while (Item::where('item_code', $newItemCode)->exists()) {
            $nextSequence++;
            $newItemCode = $categoryCode . '-' . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
            $counter++;
            
            // Safety check to prevent infinite loop
            if ($counter > 1000) {
                break;
            }
        }

        return $newItemCode;
    }

    /**
     * Export items to CSV file
     */
    public function export(Request $request)
    {
        try {
            // Get the same filtered data as the index method
            $statusFilter = $request->get('status', 'all');
            
            $query = Item::with(['category', 'unit', 'currentStockRecord']);
            
            // Apply status filter
            if ($statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('is_active', false);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('item_code', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            // Apply category filter
            if ($request->filled('category')) {
                $query->whereHas('category', function($q) use ($request) {
                    $q->where('name', 'ilike', "%{$request->category}%");
                });
            }

            $items = $query->orderBy('name')->get();

            // Prepare CSV data
            $csvData = [];
            $csvData[] = [
                'Item Code',
                'Item Name', 
                'Description',
                'Category',
                'Unit',
                'Item Type',
                'Cost Price',
                'Selling Price',
                'Reorder Point',
                'Shelf Life (Days)',
                'Current Stock',
                'Status',
                'Created At'
            ];

            foreach ($items as $item) {
                $csvData[] = [
                    $item->item_code,
                    $item->name,
                    $item->description,
                    $item->category->name ?? '',
                    $item->unit->symbol ?? '',
                    ucwords(str_replace('_', ' ', $item->item_type)),
                    $item->cost_price,
                    $item->selling_price,
                    $item->reorder_point,
                    $item->shelf_life_days,
                    $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0,
                    $item->is_active ? 'Active' : 'Inactive',
                    $item->created_at->format('Y-m-d H:i:s')
                ];
            }

            // Create CSV content
            $filename = 'items_export_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/private/exports');
            
            // Ensure directory exists
            if (!file_exists($filepath)) {
                mkdir($filepath, 0755, true);
            }

            $fullPath = $filepath . '/' . $filename;
            
            $file = fopen($fullPath, 'w');
            
            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);

            // Return download response
            return response()->download($fullPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import items from CSV file
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $filepath = $file->getRealPath();

            // Read CSV file
            $csvData = array_map('str_getcsv', file($filepath));
            
            if (empty($csvData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The CSV file is empty.'
                ], 422);
            }

            // Get header row
            $header = $csvData[0];
            $dataRows = array_slice($csvData, 1);

            if (count($dataRows) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data rows found in the CSV file.'
                ], 422);
            }

            // Validate header
            $expectedHeaders = ['Item Code', 'Item Name', 'Description', 'Category', 'Unit', 'Item Type', 'Cost Price', 'Selling Price', 'Reorder Point', 'Shelf Life (Days)'];
            if (array_diff($expectedHeaders, $header)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid CSV format. Expected headers: ' . implode(', ', $expectedHeaders)
                ], 422);
            }

            $imported = 0;
            $errors = [];
            $skipped = 0;

            foreach ($dataRows as $index => $row) {
                try {
                    $rowNumber = $index + 2; // +2 because we skipped header and array is 0-indexed
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Map CSV data to fields
                    $itemCode = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $description = trim($row[2] ?? '');
                    $categoryName = trim($row[3] ?? '');
                    $unitSymbol = trim($row[4] ?? '');
                    $itemType = trim($row[5] ?? '');
                    $costPrice = trim($row[6] ?? '0');
                    $sellingPrice = trim($row[7] ?? '0');
                    $reorderPoint = trim($row[8] ?? '0');
                    $shelfLifeDays = trim($row[9] ?? '');

                    // Validate required fields
                    if (empty($itemCode) || empty($name) || empty($categoryName) || empty($unitSymbol)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields (Item Code, Item Name, Category, Unit)";
                        continue;
                    }

                    // Check if item code already exists
                    if (Item::where('item_code', $itemCode)->exists()) {
                        $errors[] = "Row {$rowNumber}: Item code '{$itemCode}' already exists";
                        continue;
                    }

                    // Find or create category
                    $category = Category::where('name', $categoryName)->first();
                    if (!$category) {
                        $errors[] = "Row {$rowNumber}: Category '{$categoryName}' not found";
                        continue;
                    }

                    // Find unit by symbol
                    $unit = Unit::where('symbol', $unitSymbol)->first();
                    if (!$unit) {
                        $errors[] = "Row {$rowNumber}: Unit '{$unitSymbol}' not found";
                        continue;
                    }

                    // Validate item type
                    $validItemTypes = ['raw_material', 'finished_good', 'semi_finished', 'supply'];
                    if (!empty($itemType) && !in_array(strtolower(str_replace(' ', '_', $itemType)), $validItemTypes)) {
                        $errors[] = "Row {$rowNumber}: Invalid item type '{$itemType}'";
                        continue;
                    }

                    // Create item
                    Item::create([
                        'item_code' => $itemCode,
                        'name' => $name,
                        'description' => $description,
                        'category_id' => $category->id,
                        'unit_id' => $unit->id,
                        'item_type' => !empty($itemType) ? strtolower(str_replace(' ', '_', $itemType)) : 'raw_material',
                        'cost_price' => is_numeric($costPrice) ? $costPrice : 0,
                        'selling_price' => is_numeric($sellingPrice) ? $sellingPrice : 0,
                        'reorder_point' => is_numeric($reorderPoint) ? $reorderPoint : 0,
                        'shelf_life_days' => is_numeric($shelfLifeDays) ? (int)$shelfLifeDays : null,
                        'is_active' => true
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": Error processing row - " . $e->getMessage();
                }
            }

            $message = "Import completed: {$imported} items imported successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} rows skipped.";
            }
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing items: ' . $e->getMessage()
            ], 500);
        }
    }
}