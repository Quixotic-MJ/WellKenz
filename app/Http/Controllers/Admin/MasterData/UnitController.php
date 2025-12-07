<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Unit;
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
                  ->orWhere('symbol', 'ilike', "%{$search}%");
            });
        }

        // Type filter (NEW - was missing)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        // Separate base units and packaging units for the view - APPLY SAME FILTERS
        $baseUnitsQuery = Unit::with('items')
            ->whereNull('base_unit_id')
            ->orderBy('name');

        $packagingUnitsQuery = Unit::with(['items', 'baseUnit'])
            ->whereNotNull('base_unit_id')
            ->orderBy('name');

        // Apply same filters to base units
        if ($request->filled('search')) {
            $search = $request->search;
            $baseUnitsQuery->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('symbol', 'ilike', "%{$search}%");
            });
            $packagingUnitsQuery->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('symbol', 'ilike', "%{$search}%");
            });
        }

        // Apply type filter to base units
        if ($request->filled('type')) {
            $baseUnitsQuery->where('type', $request->type);
            $packagingUnitsQuery->where('type', $request->type);
        }

        // Apply status filter to base units
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $baseUnitsQuery->where('is_active', true);
                $packagingUnitsQuery->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $baseUnitsQuery->where('is_active', false);
                $packagingUnitsQuery->where('is_active', false);
            }
        } else {
            // Default to active units only if no status filter
            $baseUnitsQuery->where('is_active', true);
            $packagingUnitsQuery->where('is_active', true);
        }

        $baseUnits = $baseUnitsQuery->get();
        $packagingUnits = $packagingUnitsQuery->get();

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
            'type' => 'required|in:weight,volume,piece,length',
            'description' => 'nullable|string|max:500',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0',
        ]);

        try {
            $unit = Unit::create([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'type' => $request->type,
                'description' => $request->description,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1,
                'is_active' => true
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
            'type' => 'required|in:weight,volume,piece,length',
            'description' => 'nullable|string|max:500',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0',
        ]);

        try {
            $oldData = $unit->toArray();
            
            $unit->update([
                'name' => $request->name,
                'symbol' => $request->symbol,
                'type' => $request->type,
                'description' => $request->description,
                'base_unit_id' => $request->base_unit_id,
                'conversion_factor' => $request->conversion_factor ?? 1,
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getBaseUnits(Request $request)
    {
        $query = Unit::where('is_active', true)
            ->whereNull('base_unit_id');

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $units = $query->orderBy('name')
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
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('symbol', 'ilike', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'symbol', 'description']);

        return response()->json($units);
    }
}