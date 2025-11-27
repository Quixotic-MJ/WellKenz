<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::whereHas('supplierItems', function($q) {
                // No need to filter supplier_items by is_active as it doesn't exist
                // Just load the supplierItems relationship
            })
            ->with(['supplierItems' => function($q) {
                // No filtering needed on supplier_items table itself
            }])
            ->orderBy('name');

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $perPage = $request->get('per_page', 10);
        $suppliers = $query->paginate($perPage)
            ->withQueryString()
            ->through(function ($supplier) {
                $supplier->items_count = $supplier->supplierItems->count();
                $supplier->formatted_status = $supplier->is_active ? 'Active' : 'Inactive';
                return $supplier;
            });

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('Admin.supplier.supplier_list', compact('suppliers', 'stats'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $supplier = Supplier::create([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->is_active ?? true,
                'created_by' => Auth::id(),
            ]);

            // Log the action
            $this->logAction('create_supplier', $supplier->id, 'suppliers', [
                'supplier_name' => $supplier->name,
                'created_by' => Auth::user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully!',
                'supplier' => $supplier
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        $supplier->load('supplierItems.item');
        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $oldData = $supplier->toArray();

            $supplier->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->is_active ?? true,
                'updated_by' => Auth::id(),
            ]);

            // Log the action
            $this->logAction('update_supplier', $supplier->id, 'suppliers', [
                'supplier_name' => $supplier->name,
                'old_data' => $oldData,
                'new_data' => $supplier->toArray(),
                'updated_by' => Auth::user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully!',
                'supplier' => $supplier
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the status of the specified supplier.
     */
    public function toggleStatus(Supplier $supplier)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $supplier->is_active;
            $supplier->is_active = !$supplier->is_active;
            $supplier->updated_by = Auth::id();
            $supplier->save();

            // Log the action
            $this->logAction('toggle_supplier_status', $supplier->id, 'suppliers', [
                'supplier_name' => $supplier->name,
                'old_status' => $oldStatus ? 'Active' : 'Inactive',
                'new_status' => $supplier->is_active ? 'Active' : 'Inactive',
                'updated_by' => Auth::user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier status updated successfully!',
                'is_active' => $supplier->is_active
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            DB::beginTransaction();

            // Check if supplier has associated items
            $itemsCount = SupplierItem::where('supplier_id', $supplier->id)->count();
            
            if ($itemsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete supplier with associated items. Please remove or reassign items first.'
                ], 422);
            }

            $supplierName = $supplier->name;
            $supplier->delete();

            // Log the action
            $this->logAction('delete_supplier', $supplier->id, 'suppliers', [
                'supplier_name' => $supplierName,
                'deleted_by' => Auth::user()->name,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search suppliers for AJAX requests.
     */
    public function search(Request $request)
    {
        $query = Supplier::where('is_active', true)
            ->select('id', 'name', 'contact_person', 'email');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Get supplier details with items.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['supplierItems.item' => function($q) {
            // No need to filter supplier_items by is_active as it doesn't exist
            // Filter by item's is_active status instead
            $q->where('is_active', true);
        }]);

        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
    }

    /**
     * Alias for index method.
     */
    public function supplierList(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Alias for store method.
     */
    public function storeSupplier(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Alias for edit method.
     */
    public function editSupplier(Supplier $supplier)
    {
        return $this->edit($supplier);
    }

    /**
     * Alias for update method.
     */
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        return $this->update($request, $supplier);
    }

    /**
     * Alias for toggleStatus method.
     */
    public function toggleSupplierStatus(Supplier $supplier)
    {
        return $this->toggleStatus($supplier);
    }

    /**
     * Alias for destroy method.
     */
    public function deleteSupplier(Supplier $supplier)
    {
        return $this->destroy($supplier);
    }



    /**
     * Log audit actions.
     */
    private function logAction($action, $recordId, $tableName, $data = [])
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => isset($data['old_data']) ? json_encode($data['old_data']) : null,
                'new_values' => isset($data['new_data']) ? json_encode($data['new_data']) : json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silent fail for logging
        }
    }
}