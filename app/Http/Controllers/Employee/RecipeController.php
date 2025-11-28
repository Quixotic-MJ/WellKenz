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
use App\Models\RecipeIngredient;
use App\Models\Notification;

class RecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function recipes()
    {
        $recipes = Recipe::where('is_active', true)
            ->with('finishedItem.category', 'ingredients.item.unit', 'yieldUnit')
            ->orderBy('name')
            ->get();

        return view('Employee.production.recipe', compact('recipes'));
    }

    public function getRecipeDetails(Recipe $recipe)
    {
        $recipe->load([
            'finishedItem.category',
            'ingredients.item.unit',
            'yieldUnit'
        ]);

        $recipeData = [
            'id' => $recipe->id,
            'name' => $recipe->name,
            'recipe_code' => $recipe->recipe_code,
            'description' => $recipe->description,
            'preparation_time' => $recipe->preparation_time,
            'cooking_time' => $recipe->cooking_time,
            'yield_quantity' => $recipe->yield_quantity,
            'serving_size' => $recipe->serving_size,
            'instructions' => $recipe->instructions,
            'notes' => $recipe->notes,
            'ingredients' => $recipe->ingredients->map(function($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'quantity_required' => $ingredient->quantity_required,
                    'notes' => $ingredient->notes,
                    'item' => [
                        'id' => $ingredient->item->id,
                        'name' => $ingredient->item->name,
                        'unit' => [
                            'symbol' => $ingredient->item->unit->symbol,
                            'name' => $ingredient->item->unit->name
                        ]
                    ]
                ];
            }),
            'yield_unit' => $recipe->yieldUnit ? [
                'symbol' => $recipe->yieldUnit->symbol,
                'name' => $recipe->yieldUnit->name
            ] : null,
            'finished_item' => $recipe->finishedItem ? [
                'category' => [ 'name' => $recipe->finishedItem->category->name ]
            ] : null
        ];

        return response()->json([
            'success' => true,
            'recipe' => $recipeData
        ]);
    }

    public function createRecipe(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'recipe_code' => 'nullable|string|max:50|unique:recipes,recipe_code',
                'description' => 'nullable|string',
                'finished_item_id' => 'required|exists:items,id',
                'yield_quantity' => 'required|numeric|min:0.001',
                'yield_unit_id' => 'required|exists:units,id',
                'preparation_time' => 'nullable|integer|min:0',
                'cooking_time' => 'nullable|integer|min:0',
                'serving_size' => 'nullable|string|max:255',
                'instructions' => 'nullable|string',
                'notes' => 'nullable|string',
                'ingredients' => 'required|array|min:1',
                'ingredients.*.item_id' => 'required|exists:items,id',
                'ingredients.*.quantity_required' => 'required|numeric|min:0.001',
                'ingredients.*.unit_id' => 'required|exists:units,id',
                'ingredients.*.notes' => 'nullable|string'
            ]);

            if (empty($validated['recipe_code'])) {
                $validated['recipe_code'] = 'REC-' . date('Ymd') . '-' . str_pad((Recipe::count() + 1), 4, '0', STR_PAD_LEFT);
            }

            $recipe = Recipe::create([
                'recipe_code' => $validated['recipe_code'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'finished_item_id' => $validated['finished_item_id'],
                'yield_quantity' => $validated['yield_quantity'],
                'yield_unit_id' => $validated['yield_unit_id'],
                'preparation_time' => $validated['preparation_time'] ?? 0,
                'cooking_time' => $validated['cooking_time'] ?? 0,
                'serving_size' => $validated['serving_size'],
                'instructions' => $validated['instructions'],
                'notes' => $validated['notes'],
                'is_active' => true,
                'created_by' => Auth::id()
            ]);

            foreach ($validated['ingredients'] as $ingredientData) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'item_id' => $ingredientData['item_id'],
                    'quantity_required' => $ingredientData['quantity_required'],
                    'unit_id' => $ingredientData['unit_id'],
                    'is_optional' => false,
                    'notes' => $ingredientData['notes']
                ]);
            }

            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'New Recipe Created',
                    'message' => Auth::user()->name . " has created a new recipe: {$validated['name']} ({$validated['recipe_code']}).",
                    'type' => 'recipe',
                    'priority' => 'normal',
                    'created_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recipe created successfully.',
                'recipe' => [ 'id' => $recipe->id, 'name' => $recipe->name, 'recipe_code' => $recipe->recipe_code ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Recipe creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the recipe. Please try again.'
            ], 500);
        }
    }

    public function updateRecipe(Request $request, Recipe $recipe)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'recipe_code' => 'nullable|string|max:50|unique:recipes,recipe_code,' . $recipe->id,
                'description' => 'nullable|string',
                'finished_item_id' => 'required|exists:items,id',
                'yield_quantity' => 'required|numeric|min:0.001',
                'yield_unit_id' => 'required|exists:units,id',
                'preparation_time' => 'nullable|integer|min:0',
                'cooking_time' => 'nullable|integer|min:0',
                'serving_size' => 'nullable|string|max:255',
                'instructions' => 'nullable|string',
                'notes' => 'nullable|string',
                'ingredients' => 'required|array|min:1',
                'ingredients.*.item_id' => 'required|exists:items,id',
                'ingredients.*.quantity_required' => 'required|numeric|min:0.001',
                'ingredients.*.unit_id' => 'required|exists:units,id',
                'ingredients.*.notes' => 'nullable|string'
            ]);

            $recipe->update([
                'recipe_code' => $validated['recipe_code'] ?? $recipe->recipe_code,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'finished_item_id' => $validated['finished_item_id'],
                'yield_quantity' => $validated['yield_quantity'],
                'yield_unit_id' => $validated['yield_unit_id'],
                'preparation_time' => $validated['preparation_time'] ?? 0,
                'cooking_time' => $validated['cooking_time'] ?? 0,
                'serving_size' => $validated['serving_size'],
                'instructions' => $validated['instructions'],
                'notes' => $validated['notes']
            ]);

            RecipeIngredient::where('recipe_id', $recipe->id)->delete();
            foreach ($validated['ingredients'] as $ingredientData) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'item_id' => $ingredientData['item_id'],
                    'quantity_required' => $ingredientData['quantity_required'],
                    'unit_id' => $ingredientData['unit_id'],
                    'is_optional' => false,
                    'notes' => $ingredientData['notes']
                ]);
            }

            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'Recipe Updated',
                    'message' => Auth::user()->name . " has updated the recipe: {$validated['name']} ({$recipe->recipe_code}).",
                    'type' => 'recipe',
                    'priority' => 'normal',
                    'created_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully.',
                'recipe' => [ 'id' => $recipe->id, 'name' => $recipe->name, 'recipe_code' => $recipe->recipe_code ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Recipe update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the recipe. Please try again.'
            ], 500);
        }
    }

    public function deleteRecipe(Recipe $recipe)
    {
        try {
            if (!$recipe->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe not found or already deleted.'
                ], 404);
            }

            $recipeName = $recipe->name;
            $recipeCode = $recipe->recipe_code;
            $recipe->delete();

            $supervisors = User::where('role', 'supervisor')->get();
            foreach ($supervisors as $supervisor) {
                Notification::create([
                    'user_id' => $supervisor->id,
                    'title' => 'Recipe Deleted',
                    'message' => Auth::user()->name . " has deleted the recipe: {$recipeName} ({$recipeCode}).",
                    'type' => 'recipe',
                    'priority' => 'normal',
                    'created_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Recipe deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the recipe. Please try again.'
            ], 500);
        }
    }
}
