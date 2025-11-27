<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Unit::with(['items' => function($q) {
                $q->where('is_active', true);
            }, 'baseUnit'])
            ->orderBy('name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('symbol', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $perPage = $request->get('per_page', 10);
        $units = $query->paginate($perPage)
            ->withQueryString()
            ->through(function ($unit) {
                $unit->linked_items_count = $unit->items->count();
                return $unit;
            });

        // Separate base units and packaging units for the view
        $baseUnits = Unit::with('items')
            ->whereNull('base_unit_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $packagingUnits = Unit::with(['items', 'baseUnit'])
            ->whereNotNull('base_unit_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('Admin.master_files.unit_config', compact('units', 'baseUnits', 'packagingUnits'));
    }

    /**
     * Store a newly created unit in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'required|string|max:10|unique:units,symbol',
            'description' => 'nullable|string|max:500',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0',
        ]);

        try {
            $unit = Unit::create([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'description' => $request->description,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1,
                'is_active' => true
            ]);

            // Create audit log
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'CREATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => json_encode($unit->toArray())
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified unit.
     *
     * @param \App\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        return response()->json($unit);
    }

    /**
     * Update the specified unit in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'symbol' => 'required|string|max:10|unique:units,symbol,' . $unit->id,
            'description' => 'nullable|string|max:500',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0',
        ]);

        try {
            $oldData = $unit->toArray();
            
            $unit->update([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'description' => $request->description,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1,
            ]);

            // Create audit log
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'UPDATE',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => json_encode($oldData),
                'new_values' => json_encode($unit->toArray())
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified unit from storage.
     *
     * @param \App\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Unit $unit)
    {
        try {
            // Check if unit has items
            if ($unit->items()->where('is_active', true)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unit with active items. Please reassign or delete associated items first.'
                ], 422);
            }

            // Check if unit is being used as base unit
            if (Unit::where('base_unit_id', $unit->id)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unit that is used as base unit for other units.'
                ], 422);
            }

            $unitName = $unit->name;
            $unit->delete();

            // Create audit log
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'DELETE',
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => json_encode(['name' => $unitName])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Unit '{$unitName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle unit active/inactive status.
     *
     * @param \App\Models\Unit $unit
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Unit $unit)
    {
        try {
            $oldStatus = $unit->is_active;
            $unit->is_active = !$unit->is_active;
            $unit->save();

            $status = $unit->is_active ? 'activated' : 'deactivated';

            // Create audit log
            AuditLog::create([
                'table_name' => 'units',
                'record_id' => $unit->id,
                'action' => 'UPDATE',
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => json_encode(['is_active' => $oldStatus]),
                'new_values' => json_encode(['is_active' => $unit->is_active])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Unit {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating unit status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get base units for dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBaseUnits()
    {
        $units = Unit::where('is_active', true)
            ->whereNull('base_unit_id')
            ->orderBy('name')
            ->get(['id', 'name', 'symbol']);

        return response()->json($units);
    }

    /**
     * Check if unit has dependencies.
     */
    public function hasUnitDependencies(Unit $unit)
    {
        $dependencies = [
            'items' => $unit->items()->count(),
            'packaging_units' => Unit::where('base_unit_id', $unit->id)->count()
        ];

        $hasDependencies = ($dependencies['items'] > 0 || $dependencies['packaging_units'] > 0);

        return response()->json([
            'has_dependencies' => $hasDependencies,
            'dependencies' => $dependencies
        ]);
    }

    /**
     * Search units for AJAX requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $units = Unit::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('symbol', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'symbol', 'description']);

        return response()->json($units);
    }
}