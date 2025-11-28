<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::query()
                ->select([
                    'id', 'supplier_code', 'name', 'contact_person', 'email', 
                    'phone', 'mobile', 'address', 'city', 'province', 'postal_code',
                    'tax_id', 'payment_terms', 'credit_limit', 'rating', 'is_active', 
                    'notes', 'created_at', 'updated_at'
                ])
                ->orderBy('name');

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = trim(strtolower($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(supplier_code) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(contact_person) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(mobile) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(tax_id) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(city) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(province) LIKE ?', ["%{$search}%"]);
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('is_active', $request->status === 'active');
            }

            $perPage = $request->get('per_page', 15);
            $suppliers = $query->paginate($perPage)->withQueryString();

            $stats = [
                'total' => Supplier::count(),
                'active' => Supplier::where('is_active', true)->count(),
                'inactive' => Supplier::where('is_active', false)->count(),
            ];

            Log::info('Supplier list retrieved', [
                'user_id' => Auth::id(),
                'total_suppliers' => $suppliers->total(),
                'search_term' => $request->search,
                'status_filter' => $request->status
            ]);

            return view('Admin.supplier.supplier_list', compact('suppliers', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error retrieving supplier list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return view('Admin.supplier.supplier_list', [
                'suppliers' => collect([]),
                'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0]
            ])->with('error', 'Failed to load suppliers. Please try again.');
        }
    }

    /**
     * Generate a unique supplier code.
     */
    private function generateSupplierCode(): string
    {
        $prefix = 'SUP';
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        
        if ($lastSupplier && $lastSupplier->supplier_code) {
            $lastCode = $lastSupplier->supplier_code;
            $lastNumber = (int) substr($lastCode, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $newCode = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness
        while (Supplier::where('supplier_code', $newCode)->exists()) {
            $newNumber++;
            $newCode = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return $newCode;
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $supplier = Supplier::create([
                'supplier_code' => $this->generateSupplierCode(),
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'tax_id' => $request->tax_id,
                'payment_terms' => $request->payment_terms ?? 30,
                'credit_limit' => $request->credit_limit ?? 0,
                'rating' => $request->rating,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => Auth::id(),
            ]);

            // Log the action
            $this->logAction('CREATE', $supplier->id, 'suppliers', [
                'supplier_code' => $supplier->supplier_code,
                'supplier_name' => $supplier->name,
                'action' => 'Supplier created',
                'created_by' => Auth::user()->name ?? 'Unknown'
            ]);

            DB::commit();

            Log::info('Supplier created successfully', [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code,
                'supplier_name' => $supplier->name,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully!',
                'supplier' => [
                    'id' => $supplier->id,
                    'supplier_code' => $supplier->supplier_code,
                    'name' => $supplier->name,
                    'is_active' => $supplier->is_active
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollback();
            Log::error('Supplier creation validation error', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating supplier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): JsonResponse
    {
        try {
            // Ensure the supplier exists and belongs to the current tenant/company if applicable
            $supplier->load('supplierItems.item');

            Log::info('Supplier edit data requested', [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'supplier' => [
                    'id' => $supplier->id,
                    'supplier_code' => $supplier->supplier_code,
                    'name' => $supplier->name,
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
                    'supplier_items_count' => $supplier->supplierItems->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving supplier for edit', [
                'supplier_id' => $supplier->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading supplier data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Store old data for logging
            $oldData = $supplier->toArray();

            // Update the supplier
            $supplier->update([
                'name' => $request->name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'tax_id' => $request->tax_id,
                'payment_terms' => $request->payment_terms,
                'credit_limit' => $request->credit_limit,
                'rating' => $request->rating,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active', true),
                'updated_by' => Auth::id(),
            ]);

            // Reload the supplier to get updated data
            $supplier->refresh();

            // Log the action
            $this->logAction('UPDATE', $supplier->id, 'suppliers', [
                'supplier_code' => $supplier->supplier_code,
                'supplier_name' => $supplier->name,
                'action' => 'Supplier updated',
                'updated_by' => Auth::user()->name ?? 'Unknown',
                'changes' => $this->getChangedFields($oldData, $supplier->toArray())
            ]);

            DB::commit();

            Log::info('Supplier updated successfully', [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code,
                'supplier_name' => $supplier->name,
                'changed_fields' => $this->getChangedFields($oldData, $supplier->toArray()),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully!',
                'supplier' => [
                    'id' => $supplier->id,
                    'supplier_code' => $supplier->supplier_code,
                    'name' => $supplier->name,
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
                    'notes' => $supplier->notes
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            Log::error('Supplier update validation error', [
                'supplier_id' => $supplier->id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating supplier', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the status of the specified supplier.
     */
    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        try {
            DB::beginTransaction();

            $oldStatus = $supplier->is_active;
            $action = $oldStatus ? 'deactivate' : 'activate';
            
            // Update the supplier status
            $supplier->is_active = !$oldStatus;
            $supplier->updated_by = Auth::id();
            $supplier->save();

            DB::commit();

            // Log the action
            $this->logAction('UPDATE', $supplier->id, 'suppliers', [
                'supplier_code' => $supplier->supplier_code,
                'supplier_name' => $supplier->name,
                'action' => "Supplier {$action}d",
                'old_status' => $oldStatus ? 'Active' : 'Inactive',
                'new_status' => $supplier->is_active ? 'Active' : 'Inactive',
                'updated_by' => Auth::user()->name ?? 'Unknown'
            ]);

            Log::info("Supplier {$action}d successfully", [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplier->supplier_code,
                'old_status' => $oldStatus,
                'new_status' => $supplier->is_active,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Supplier {$action}d successfully!",
                'is_active' => $supplier->is_active
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error("Error toggling supplier status", [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if supplier has associated items
            $itemsCount = SupplierItem::where('supplier_id', $supplier->id)->count();
            
            if ($itemsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete supplier '{$supplier->name}' because it has {$itemsCount} associated item(s). Please remove or reassign items first."
                ], 422);
            }

            $supplierCode = $supplier->supplier_code;
            $supplierName = $supplier->name;
            $supplier->delete();

            // Log the action
            $this->logAction('DELETE', $supplier->id, 'suppliers', [
                'supplier_code' => $supplierCode,
                'supplier_name' => $supplierName,
                'action' => 'Supplier deleted',
                'deleted_by' => Auth::user()->name ?? 'Unknown'
            ]);

            DB::commit();

            Log::info('Supplier deleted successfully', [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplierCode,
                'supplier_name' => $supplierName,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error deleting supplier', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search suppliers for AJAX requests.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Supplier::where('is_active', true)
                ->select('id', 'supplier_code', 'name', 'contact_person', 'email');

            if ($request->has('search') && !empty($request->search)) {
                $search = trim(strtolower($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(supplier_code) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(contact_person) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
                });
            }

            $suppliers = $query->limit(10)->get();

            return response()->json([
                'success' => true,
                'suppliers' => $suppliers
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching suppliers', [
                'search_term' => $request->search,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error searching suppliers',
                'suppliers' => []
            ], 500);
        }
    }

    /**
     * Get supplier details with items.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        try {
            $supplier->load(['supplierItems.item' => function($q) {
                $q->where('is_active', true);
            }]);

            return response()->json([
                'success' => true,
                'supplier' => $supplier
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing supplier details', [
                'supplier_id' => $supplier->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading supplier details'
            ], 500);
        }
    }

    /**
     * Log audit actions.
     */
    private function logAction(string $action, int $recordId, string $tableName, array $data = []): void
    {
        try {
            AuditLog::create([
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
            Log::warning('Failed to log audit action', [
                'action' => $action,
                'table' => $tableName,
                'record_id' => $recordId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get changed fields between old and new data.
     */
    private function getChangedFields(array $oldData, array $newData): array
    {
        $changed = [];
        $ignoreFields = ['created_at', 'updated_at', 'created_by', 'updated_by'];
        
        foreach ($newData as $key => $newValue) {
            if (in_array($key, $ignoreFields)) {
                continue;
            }
            
            $oldValue = $oldData[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changed;
    }

    /**
     * Export suppliers to CSV format.
     */
    public function exportCsv(Request $request)
    {
        try {
            $query = Supplier::query()
                ->select([
                    'supplier_code', 'name', 'contact_person', 'email', 
                    'phone', 'mobile', 'address', 'city', 'province', 'postal_code',
                    'tax_id', 'payment_terms', 'credit_limit', 'rating', 'is_active', 
                    'notes', 'created_at', 'updated_at'
                ])
                ->orderBy('name');

            // Apply search filter (same logic as index)
            if ($request->has('search') && !empty($request->search)) {
                $search = trim(strtolower($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(supplier_code) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(contact_person) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(mobile) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(tax_id) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(city) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(province) LIKE ?', ["%{$search}%"]);
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('is_active', $request->status === 'active');
            }

            $suppliers = $query->get();

            $filename = 'suppliers_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($suppliers) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Supplier Code',
                    'Company Name',
                    'Contact Person',
                    'Email',
                    'Phone',
                    'Mobile',
                    'Address',
                    'City',
                    'Province',
                    'Postal Code',
                    'Tax ID (TIN)',
                    'Payment Terms (Days)',
                    'Credit Limit',
                    'Rating',
                    'Status',
                    'Notes',
                    'Created Date',
                    'Updated Date'
                ]);

                // CSV data
                foreach ($suppliers as $supplier) {
                    fputcsv($file, [
                        $supplier->supplier_code,
                        $supplier->name,
                        $supplier->contact_person,
                        $supplier->email,
                        $supplier->phone,
                        $supplier->mobile,
                        $supplier->address,
                        $supplier->city,
                        $supplier->province,
                        $supplier->postal_code,
                        $supplier->tax_id,
                        $supplier->payment_terms,
                        $supplier->credit_limit,
                        $supplier->rating,
                        $supplier->is_active ? 'Active' : 'Inactive',
                        $supplier->notes,
                        $supplier->created_at->format('Y-m-d H:i:s'),
                        $supplier->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            Log::info('Supplier CSV export generated', [
                'user_id' => Auth::id(),
                'total_records' => $suppliers->count(),
                'search_term' => $request->search,
                'status_filter' => $request->status
            ]);

            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting suppliers to CSV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export suppliers to PDF format.
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Supplier::query()
                ->select([
                    'supplier_code', 'name', 'contact_person', 'email', 
                    'phone', 'mobile', 'address', 'city', 'province', 'postal_code',
                    'tax_id', 'payment_terms', 'credit_limit', 'rating', 'is_active', 
                    'notes', 'created_at', 'updated_at'
                ])
                ->orderBy('name');

            // Apply search filter (same logic as index)
            if ($request->has('search') && !empty($request->search)) {
                $search = trim(strtolower($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(supplier_code) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(contact_person) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(mobile) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(tax_id) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(city) LIKE ?', ["%{$search}%"])
                      ->orWhereRaw('LOWER(province) LIKE ?', ["%{$search}%"]);
                });
            }

            // Apply status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('is_active', $request->status === 'active');
            }

            $suppliers = $query->get();
            
            $stats = [
                'total' => $suppliers->count(),
                'active' => $suppliers->where('is_active', true)->count(),
                'inactive' => $suppliers->where('is_active', false)->count(),
            ];

            $filename = 'suppliers_' . date('Y-m-d_H-i-s') . '.pdf';

            Log::info('Supplier PDF export generated', [
                'user_id' => Auth::id(),
                'total_records' => $suppliers->count(),
                'search_term' => $request->search,
                'status_filter' => $request->status
            ]);

            $pdf = Pdf::loadView('Admin.supplier.supplier_pdf', compact('suppliers', 'stats'));
            $pdf->setPaper('a4', 'portrait');
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting suppliers to PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF export: ' . $e->getMessage()
            ], 500);
        }
    }
}