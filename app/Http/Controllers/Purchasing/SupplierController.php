<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
            
    public function suppliers(Request $request)
    {   
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('supplier_code', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('tax_id', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('payment_terms')) {
            $query->where('payment_terms', $request->payment_terms);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $suppliers = $query->withCount(['purchaseOrders'])
            ->with(['batches' => function($query) {
                $query->selectRaw('supplier_id, COUNT(*) as batch_count')->groupBy('supplier_id');
            }, 'supplierItems' => function($query) {
                $query->selectRaw('supplier_id, COUNT(*) as supplier_item_count')->groupBy('supplier_id');
            }])
            ->orderBy('name')
            ->get();

        $paymentTerms = Supplier::distinct()
            ->whereNotNull('payment_terms')
            ->orderBy('payment_terms')
            ->pluck('payment_terms')
            ->values();

        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'inactive_suppliers' => Supplier::where('is_active', false)->count(),
            'avg_rating' => Supplier::whereNotNull('rating')->avg('rating') ?: 0,
            'suppliers_with_po' => Supplier::has('purchaseOrders')->count(),
        ];

        return view('Purchasing.suppliers.supplier_management', compact('suppliers', 'paymentTerms', 'stats'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($request->all());

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroySupplier(Supplier $supplier)
    {
        $conflicts = [];
        if ($supplier->purchaseOrders()->count() > 0) {
            $conflicts[] = 'purchase orders';
        }
        if (\App\Models\Batch::where('supplier_id', $supplier->id)->count() > 0) {
            $conflicts[] = 'inventory batches';
        }
        if (\App\Models\SupplierItem::where('supplier_id', $supplier->id)->count() > 0) {
            $conflicts[] = 'supplier item records';
        }
        if (!empty($conflicts)) {
            $conflictList = implode(', ', $conflicts);
            return redirect()->back()
                ->with('error', "Cannot delete supplier '{$supplier->name}' because it has existing {$conflictList}. Please remove all related records first or deactivate the supplier instead.");
        }

        $supplier->delete();

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    public function toggleSupplierStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        $status = $supplier->is_active ? 'activated' : 'deactivated';
        return redirect()->route('purchasing.suppliers.index')
            ->with('success', "Supplier {$status} successfully!");
    }

    // API
    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q', '');
        $suppliers = Supplier::where('is_active', true)
            ->where(function($q) use ($query) {
                if ($query) {
                    $q->where('name', 'ilike', "%{$query}%")
                      ->orWhere('supplier_code', 'ilike', "%{$query}%")
                      ->orWhere('contact_person', 'ilike', "%{$query}%");
                }
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'supplier_code', 'contact_person', 'phone', 'email']);
        return response()->json($suppliers);
    }

    public function getSupplierDetails(Supplier $supplier)
    {
        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'supplier_code' => $supplier->supplier_code,
            'contact_person' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'mobile' => $supplier->mobile,
            'email' => $supplier->email,
            'address' => $supplier->address,
            'city' => $supplier->city,
            'province' => $supplier->province,
            'postal_code' => $supplier->postal_code,
            'tax_id' => $supplier->tax_id,
            'payment_terms' => $supplier->payment_terms,
            'credit_limit' => $supplier->credit_limit,
            'rating' => $supplier->rating,
            'is_active' => $supplier->is_active,
            'notes' => $supplier->notes,
            'created_at' => $supplier->created_at,
            'updated_at' => $supplier->updated_at
        ]);
    }

    /**
     * Get supplier items with pricing information
     */
    public function getSupplierItems(Supplier $supplier)
    {
        $supplierItems = SupplierItem::with(['item.unit', 'item.category'])
            ->where('supplier_id', $supplier->id)
            ->orderBy('item_id')
            ->get();

        return response()->json($supplierItems->map(function($item) {
            return [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'item_name' => $item->item->name,
                'item_code' => $item->item->item_code,
                'category_name' => $item->item->category->name ?? '',
                'unit_name' => $item->item->unit->name ?? 'Unit',
                'unit_symbol' => $item->item->unit->symbol ?? '',
                'unit_price' => $item->unit_price,
                'minimum_order_quantity' => $item->minimum_order_quantity,
                'lead_time_days' => $item->lead_time_days,
                'is_preferred' => $item->is_preferred,
                'last_purchase_price' => $item->last_purchase_price,
                'last_purchase_date' => $item->last_purchase_date,
                'updated_at' => $item->updated_at,
            ];
        }));
    }

    /**
     * Show single supplier item details for editing
     */
    public function showSupplierItem(SupplierItem $supplierItem)
    {
        $supplierItem->load(['item.unit', 'item.category', 'supplier']);

        return response()->json([
            'id' => $supplierItem->id,
            'supplier_id' => $supplierItem->supplier_id,
            'item_id' => $supplierItem->item_id,
            'item_name' => $supplierItem->item->name ?? '',
            'item_code' => $supplierItem->item->item_code ?? '',
            'unit_symbol' => $supplierItem->item->unit->symbol ?? '',
            'unit_price' => $supplierItem->unit_price,
            'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
            'lead_time_days' => $supplierItem->lead_time_days,
            'is_preferred' => $supplierItem->is_preferred,
            'supplier_name' => $supplierItem->supplier->name ?? '',
            'updated_at' => $supplierItem->updated_at,
        ]);
    }

    /**
     * Add items to supplier
     */
    public function addSupplierItems(Request $request, Supplier $supplier)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.minimum_order_quantity' => 'required|numeric|min:0.001',
            'items.*.lead_time_days' => 'required|integer|min:0',
            'items.*.is_preferred' => 'boolean',
        ]);

        $created = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                // Check if item already exists for this supplier
                $existing = SupplierItem::where('supplier_id', $supplier->id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                if ($existing) {
                    $errors[] = "Item ID {$itemData['item_id']} already exists for this supplier";
                    continue;
                }

                $supplierItem = SupplierItem::create([
                    'supplier_id' => $supplier->id,
                    'item_id' => $itemData['item_id'],
                    'unit_price' => $itemData['unit_price'],
                    'minimum_order_quantity' => $itemData['minimum_order_quantity'],
                    'lead_time_days' => $itemData['lead_time_days'],
                    'is_preferred' => $itemData['is_preferred'] ?? false,
                ]);

                $created[] = $supplierItem;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully added ' . count($created) . ' item(s) to supplier.',
                'created_count' => count($created),
                'errors' => $errors,
                'created_items' => $created
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error adding items: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update supplier item price
     */
    public function updateSupplierItem(Request $request, SupplierItem $supplierItem)
    {
        $request->validate([
            'unit_price' => 'required|numeric|min:0.01',
            'minimum_order_quantity' => 'required|numeric|min:0.001',
            'lead_time_days' => 'required|integer|min:0',
            'is_preferred' => 'boolean',
        ]);

        $supplierItem->update([
            'unit_price' => $request->unit_price,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'lead_time_days' => $request->lead_time_days,
            'is_preferred' => $request->is_preferred ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully!',
            'data' => [
                'id' => $supplierItem->id,
                'unit_price' => $supplierItem->unit_price,
                'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                'lead_time_days' => $supplierItem->lead_time_days,
                'is_preferred' => $supplierItem->is_preferred,
                'updated_at' => $supplierItem->updated_at->format('M d, Y H:i')
            ]
        ]);
    }

    /**
     * Remove item from supplier
     */
    public function removeSupplierItem(SupplierItem $supplierItem)
    {
        $supplierItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from supplier successfully!'
        ]);
    }

    /**
     * Bulk update supplier item prices
     */
    public function bulkUpdateSupplierItemPrices(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.supplier_item_id' => 'required|exists:supplier_items,id',
            'updates.*.unit_price' => 'required|numeric|min:0.01',
            'updates.*.minimum_order_quantity' => 'required|numeric|min:0.001',
            'updates.*.lead_time_days' => 'required|integer|min:0',
            'updates.*.is_preferred' => 'boolean',
        ]);

        $updatedCount = 0;
        $errors = [];

        foreach ($request->updates as $update) {
            try {
                $supplierItem = SupplierItem::find($update['supplier_item_id']);
                if ($supplierItem) {
                    $supplierItem->update([
                        'unit_price' => $update['unit_price'],
                        'minimum_order_quantity' => $update['minimum_order_quantity'],
                        'lead_time_days' => $update['lead_time_days'],
                        'is_preferred' => $update['is_preferred'] ?? false,
                    ]);
                    $updatedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to update item ID {$update['supplier_item_id']}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updatedCount} price records.",
            'updated_count' => $updatedCount,
            'errors' => $errors
        ]);
    }

    /**
     * Get available items to add to supplier
     */
    public function getAvailableItems(Request $request, Supplier $supplier)
    {
        $search = $request->get('search', '');
        
        $query = Item::where('is_active', true)
            ->whereDoesntHave('supplierItems', function($q) use ($supplier) {
                $q->where('supplier_id', $supplier->id);
            })
            ->with(['unit', 'category']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('item_code', 'ilike', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')
            ->limit(50)
            ->get(['id', 'item_code', 'name', 'category_id', 'unit_id']);

        return response()->json($items->map(function($item) {
            return [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'category_name' => $item->category->name ?? '',
                'unit_name' => $item->unit->name ?? 'Unit',
                'unit_symbol' => $item->unit->symbol ?? '',
            ];
        }));
    }
}
