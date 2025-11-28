<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;

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
            ->paginate(15);

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

        return view('Purchasing.suppliers.supplier_masterlist', compact('suppliers', 'paymentTerms', 'stats'));
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
}
