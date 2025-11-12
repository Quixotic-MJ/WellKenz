<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /* ----------- AJAX CRUD used by the purchasing dashboard ----------- */
    public function index()
    {
        $suppliers = Supplier::orderBy('sup_name')->get();
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('sup_status', 'active')->count();
        $inactiveSuppliers = Supplier::where('sup_status', 'inactive')->count();
        $thisMonthSuppliers = Supplier::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('Admin.Purchasing.Supplier.supplier', compact(
            'suppliers', 'totalSuppliers', 'activeSuppliers', 'inactiveSuppliers', 'thisMonthSuppliers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sup_name'        => 'required|string|max:255',
            'sup_email'       => 'nullable|email',
            'sup_address'     => 'nullable|string',
            'contact_person'  => 'nullable|string|max:255',
            'contact_number'  => 'nullable|string|max:25',
        ]);

        $supplier = Supplier::create(array_merge(
            $request->only(['sup_name','sup_email','sup_address','contact_person','contact_number']),
            ['sup_status' => 'active']
        ));

        return response()->json(['success' => true, 'supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'sup_name'        => 'required|string|max:255',
            'sup_email'       => 'nullable|email',
            'sup_address'     => 'nullable|string',
            'contact_person'  => 'nullable|string|max:255',
            'contact_number'  => 'nullable|string|max:25',
            'sup_status'      => 'nullable|in:active,inactive',
        ]);

        $payload = $request->only(['sup_name','sup_email','sup_address','contact_person','contact_number','sup_status']);
        $supplier->update($payload);

        return response()->json(['success' => true]);
    }

    public function destroy(Supplier $supplier)
    {
        // Prevent delete if supplier used in any purchase orders
        $inUse = DB::table('purchase_orders')->where('sup_id', $supplier->sup_id)->exists();
        if ($inUse) {
            $supplier->update(['sup_status' => 'inactive']);
            return response()->json(['success' => true, 'message' => 'Supplier set to inactive because it is referenced by purchase orders.']);
        }
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted.']);
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function toggle(Supplier $supplier)
    {
        $supplier->sup_status = $supplier->sup_status === 'active' ? 'inactive' : 'active';
        $supplier->save();
        return response()->json(['success' => true, 'status' => $supplier->sup_status]);
    }
}