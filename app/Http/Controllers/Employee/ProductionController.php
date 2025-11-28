<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Item;
use App\Models\Recipe;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\Notification;

class ProductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function productionLog(Request $request)
    {
        $finishedGoods = Item::where('is_active', true)
            ->where('item_type', 'finished_good')
            ->with('unit')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $recipe = Recipe::where('finished_item_id', $item->id)
                    ->where('is_active', true)
                    ->first();
                return [
                    'id' => $item->id,
                    'recipe_id' => $recipe ? $recipe->id : null,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit' => $item->unit ? $item->unit->symbol : 'pcs',
                    'yield_quantity' => $recipe ? $recipe->yield_quantity : 1,
                    'yield_unit' => $recipe ? ($recipe->yieldUnit ? $recipe->yieldUnit->symbol : 'pcs') : ($item->unit ? $item->unit->symbol : 'pcs'),
                    'has_recipe' => $recipe ? true : false
                ];
            });

        return view('Employee.production.log', compact('finishedGoods'));
    }

    public function storeProduction(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|exists:items,id',
                'batch_number' => 'required|string|max:255',
                'good_output' => 'required|numeric|min:0',
                'rejects' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000'
            ]);

            $item = Item::findOrFail($validated['item_id']);
            $recipe = Recipe::where('finished_item_id', $item->id)->where('is_active', true)->first();
            $finishedItem = $item;
            $unit = $recipe ? $recipe->yieldUnit : $item->unit;

            $productionNumber = 'MAN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $batch = Batch::create([
                'batch_number' => $validated['batch_number'],
                'item_id' => $finishedItem->id,
                'quantity' => $validated['good_output'],
                'unit_cost' => $finishedItem->cost_price ?? 0,
                'manufacturing_date' => Carbon::now()->toDateString(),
                'expiry_date' => Carbon::now()->addDays($finishedItem->shelf_life_days ?? 30)->toDateString(),
                'status' => 'active'
            ]);

            StockMovement::create([
                'item_id' => $finishedItem->id,
                'movement_type' => 'production',
                'reference_number' => $productionNumber,
                'quantity' => $validated['good_output'],
                'unit_cost' => $finishedItem->cost_price ?? 0,
                'total_cost' => ($validated['good_output'] * ($finishedItem->cost_price ?? 0)),
                'batch_number' => $validated['batch_number'],
                'location' => 'Production',
                'notes' => ($recipe ? 'Recipe-based production' : 'Manual production') . ' - ' . ($validated['notes'] ?? 'No notes'),
                'user_id' => Auth::id()
            ]);

            $productionType = 'Manual production';
            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'New Production Entry',
                    'message' => Auth::user()->name . " has logged {$productionType} of {$validated['good_output']} {$unit->symbol} {$finishedItem->name}.",
                    'type' => 'production',
                    'priority' => 'normal',
                    'created_at' => Carbon::now()
                ]);
            }

            $successMessage = "Production entry logged successfully. Batch: {$validated['batch_number']} (Manual production)";
            return redirect()->route('employee.production.log')->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Production logging failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['general' => 'An error occurred while logging production. Please try again.']);
        }
    }
}
