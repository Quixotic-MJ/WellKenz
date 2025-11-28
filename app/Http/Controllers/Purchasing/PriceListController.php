<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierItem;

class PriceListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function supplierPriceList(Request $request)
    {
        $query = SupplierItem::with([
            'supplier',
            'item.unit',
            'item.category'
        ])->whereHas('supplier', function($q) {
            $q->where('is_active', true);
        })->whereHas('item', function($q) {
            $q->where('is_active', true);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('name', 'ilike', "%{$search}%")
                              ->orWhere('item_code', 'ilike', "%{$search}%");
                });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $supplierItems = $query->orderBy('supplier_id')
            ->orderBy('item_id')
            ->paginate(20);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $stats = [
            'total_supplier_items' => SupplierItem::whereHas('supplier', function($q) {
                $q->where('is_active', true);
            })->whereHas('item', function($q) {
                $q->where('is_active', true);
            })->count(),
            'total_active_suppliers' => Supplier::where('is_active', true)->count(),
            'preferred_supplier_items' => SupplierItem::where('is_preferred', true)->whereHas('supplier', function($q) {
                $q->where('is_active', true);
            })->whereHas('item', function($q) {
                $q->where('is_active', true);
            })->count(),
        ];

        return view('Purchasing.suppliers.pricelist', compact('supplierItems', 'suppliers', 'stats'));
    }

    public function showPriceUpdate(Request $request, SupplierItem $supplierItem = null)
    {
        if ($supplierItem) {
            $supplierItem->load(['supplier', 'item.unit']);
            return view('Purchasing.suppliers.update_price', compact('supplierItem'));
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = \App\Models\Item::where('is_active', true)->orderBy('name')->get();

        return view('Purchasing.suppliers.bulk_price_update', compact('suppliers', 'items'));
    }

    public function updateSupplierItemPrice(Request $request, SupplierItem $supplierItem)
    {
        $request->validate([
            'unit_price' => 'required|numeric|min:0.01',
            'minimum_order_quantity' => 'required|numeric|min:0.001',
            'lead_time_days' => 'required|integer|min:0',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplierItem->update([
            'unit_price' => $request->unit_price,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'lead_time_days' => $request->lead_time_days,
            'is_preferred' => $request->is_preferred ?? false,
        ]);

        if (class_exists('App\\Models\\AuditLog')) {
            \App\Models\AuditLog::create([
                'table_name' => 'supplier_items',
                'record_id' => $supplierItem->id,
                'action' => 'UPDATE',
                'old_values' => $supplierItem->getOriginal(),
                'new_values' => $supplierItem->fresh()->toArray(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully!',
            'data' => [
                'unit_price' => $supplierItem->unit_price,
                'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                'lead_time_days' => $supplierItem->lead_time_days,
                'is_preferred' => $supplierItem->is_preferred,
                'updated_at' => $supplierItem->updated_at->format('M d, Y H:i')
            ]
        ]);
    }

    public function bulkUpdateSupplierPrices(Request $request)
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
                    $oldData = $supplierItem->toArray();
                    $supplierItem->update([
                        'unit_price' => $update['unit_price'],
                        'minimum_order_quantity' => $update['minimum_order_quantity'],
                        'lead_time_days' => $update['lead_time_days'],
                        'is_preferred' => $update['is_preferred'] ?? false,
                    ]);
                    $updatedCount++;
                    if (class_exists('App\\Models\\AuditLog')) {
                        \App\Models\AuditLog::create([
                            'table_name' => 'supplier_items',
                            'record_id' => $supplierItem->id,
                            'action' => 'UPDATE',
                            'old_values' => $oldData,
                            'new_values' => $supplierItem->toArray(),
                            'user_id' => auth()->id(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                    }
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

    public function getSupplierItemsForEdit(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $itemIds = $request->get('item_ids', []);

        $query = SupplierItem::with(['supplier', 'item.unit', 'item.category'])
            ->whereHas('supplier', function($q) use ($supplierId) {
                if ($supplierId) {
                    $q->where('id', $supplierId);
                }
                $q->where('is_active', true);
            })
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });

        if (!empty($itemIds)) {
            $query->whereIn('item_id', $itemIds);
        }

        $supplierItems = $query->get();

        return response()->json($supplierItems->map(function($item) {
            return [
                'id' => $item->id,
                'supplier_id' => $item->supplier_id,
                'item_id' => $item->item_id,
                'supplier_name' => $item->supplier->name,
                'item_name' => $item->item->name,
                'item_code' => $item->item->item_code,
                'unit_symbol' => $item->item->unit->symbol ?? '',
                'current_unit_price' => $item->unit_price,
                'current_min_order_qty' => $item->minimum_order_quantity,
                'current_lead_time' => $item->lead_time_days,
                'is_preferred' => $item->is_preferred,
            ];
        }));
    }

    public function exportSupplierPriceList(Request $request)
    {
        $query = SupplierItem::with([
            'supplier',
            'item.unit',
            'item.category'
        ])->whereHas('supplier', function($q) {
            $q->where('is_active', true);
        })->whereHas('item', function($q) {
            $q->where('is_active', true);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('name', 'ilike', "%{$search}%")
                              ->orWhere('item_code', 'ilike', "%{$search}%");
                });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $supplierItems = $query->orderBy('supplier_id')
            ->orderBy('item_id')
            ->get();

        $filename = 'supplier_pricelist_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($supplierItems) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Item Code', 'Item Name', 'Category', 'Supplier Code', 'Supplier Name', 'Unit Price', 'Unit', 'Minimum Order Qty', 'Lead Time (Days)', 'Preferred Status', 'Last Purchase Price', 'Last Purchase Date', 'Updated At'
            ]);
            foreach ($supplierItems as $supplierItem) {
                fputcsv($file, [
                    $supplierItem->item->item_code ?? '',
                    $supplierItem->item->name ?? '',
                    $supplierItem->item->category->name ?? '',
                    $supplierItem->supplier->supplier_code ?? '',
                    $supplierItem->supplier->name ?? '',
                    $supplierItem->unit_price,
                    $supplierItem->item->unit->symbol ?? '',
                    $supplierItem->minimum_order_quantity,
                    $supplierItem->lead_time_days,
                    $supplierItem->is_preferred ? 'Preferred' : 'Alternate',
                    $supplierItem->last_purchase_price ?? '',
                    $supplierItem->last_purchase_date ? $supplierItem->last_purchase_date->format('Y-m-d') : '',
                    $supplierItem->updated_at ? $supplierItem->updated_at->format('Y-m-d H:i:s') : ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getSupplierItems(Supplier $supplier)
    {
        $supplierItems = SupplierItem::where('supplier_id', $supplier->id)
            ->with('item.unit')
            ->get()
            ->map(function ($supplierItem) {
                return [
                    'item_id' => $supplierItem->item_id,
                    'item_name' => $supplierItem->item->name,
                    'item_code' => $supplierItem->item->item_code,
                    'unit_symbol' => $supplierItem->item->unit ? $supplierItem->item->unit->symbol : '',
                    'supplier_item_code' => $supplierItem->supplier_item_code,
                    'unit_price' => $supplierItem->unit_price,
                    'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                    'lead_time_days' => $supplierItem->lead_time_days,
                    'is_preferred' => $supplierItem->is_preferred
                ];
            });

        return response()->json($supplierItems);
    }
}
