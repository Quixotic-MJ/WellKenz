<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Notification;
use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display the employee dashboard.
     */
    public function home()
    {
        $user = Auth::user();
        
        // Get user's profile data
        $profile = $user->profile;
        
        // Get active requisitions for current user
        $activeRequisitions = $this->getActiveRequisitions();
        
        // Get incoming deliveries (requisitions that are approved and pending fulfillment)
        $incomingDeliveries = $this->getIncomingDeliveries();
        
        // Get notifications for the current user
        $notifications = $this->getNotifications();
        
        // Get recipe of the day
        $recipeOfTheDay = $this->getRecipeOfTheDay();

        return view('Employee.home', compact(
            'user',
            'profile',
            'activeRequisitions',
            'incomingDeliveries', 
            'notifications',
            'recipeOfTheDay'
        ));
    }

    /**
     * Get active requisitions for the current user.
     */
    public function getActiveRequisitions()
    {
        return Requisition::where('requested_by', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->with(['requisitionItems' => function($query) {
                $query->with('item');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get incoming deliveries (approved requisitions pending fulfillment).
     */
    public function getIncomingDeliveries()
    {
        return Requisition::where('requested_by', Auth::id())
            ->where('status', 'approved')
            ->with(['requisitionItems' => function($query) {
                $query->with('item');
            }])
            ->orderBy('approved_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Get notifications for the current user.
     */
    public function getNotifications($limit = 5)
    {
        return Notification::forCurrentUser()
            ->where('priority', '!=', 'low')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recipe of the day (most recent active recipe).
     */
    public function getRecipeOfTheDay()
    {
        return Recipe::where('is_active', true)
            ->with('finishedItem', 'ingredients.item')
            ->orderBy('created_at', 'desc')
            ->first();
    }



    /**
     * Create a new requisition.
     */
    public function createRequisition(Request $request)
    {
        try {
            $validated = $request->validate([
                'cart_items' => 'required|json',
                'purpose' => 'required|string|max:255',
                'department' => 'required|string|max:100'
            ]);

            // Parse cart items from JSON
            $cartItems = json_decode($validated['cart_items'], true);
            
            if (!$cartItems || count($cartItems) === 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['cart_items' => 'Please add at least one item to your requisition.']);
            }

        // Validate each cart item
        foreach ($cartItems as $itemData) {
            if (!isset($itemData['id']) || !isset($itemData['quantity'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['cart_items' => 'Invalid cart data. Please try again.']);
            }
            
            // Check if item exists and is active
            $item = Item::where('id', $itemData['id'])
                ->where('is_active', true)
                ->first();
                
            if (!$item) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['cart_items' => "Item with ID {$itemData['id']} is not available for requisition."]);
            }
            
            if ($itemData['quantity'] <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['cart_items' => "Invalid quantity for item: {$item->name}."]);
            }
        }

        // Generate requisition number
        $requisitionNumber = 'REQ-' . date('Y') . '-' . str_pad((Requisition::count() + 1), 4, '0', STR_PAD_LEFT);

        // Create requisition
        $requisition = Requisition::create([
            'requisition_number' => $requisitionNumber,
            'request_date' => Carbon::now(),
            'requested_by' => Auth::id(),
            'department' => $validated['department'],
            'purpose' => $validated['purpose'],
            'status' => 'pending'
        ]);

        $totalEstimatedValue = 0;

        // Create requisition items
        foreach ($cartItems as $itemData) {
            $item = Item::find($itemData['id']);
            $quantity = $itemData['quantity'];
            $estimatedCost = $item->cost_price * $quantity;
            $totalEstimatedValue += $estimatedCost;

            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'item_id' => $itemData['id'],
                'quantity_requested' => $quantity,
                'unit_cost_estimate' => $item->cost_price,
                'total_estimated_value' => $estimatedCost
            ]);
        }

        // Update total estimated value in requisition
        $requisition->update(['total_estimated_value' => $totalEstimatedValue]);

        // Create notification for supervisor about new requisition
        $supervisors = User::where('role', 'supervisor')->get();
        foreach ($supervisors as $supervisor) {
            Notification::create([
                'user_id' => $supervisor->id,
                'title' => 'New Requisition Submitted',
                'message' => "New requisition {$requisitionNumber} has been submitted by " . Auth::user()->name . " for approval.",
                'type' => 'requisition',
                'priority' => 'normal',
                'created_at' => Carbon::now()
            ]);
        }

        return redirect()->route('employee.requisitions.history')
            ->with('success', "Requisition {$requisitionNumber} created successfully and sent for approval.")
            ->with('new_requisition', true);

        } catch (\Exception $e) {
            \Log::error('Requisition creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['cart_items' => 'An error occurred while creating your requisition. Please try again.']);
        }
    }

    /**
     * Show requisition creation form.
     */
    public function showCreateRequisition()
    {
        $user = Auth::user();
        
        // Get items with stock levels and categories
        $items = Item::where('is_active', true)
            ->with(['unit', 'category', 'currentStockRecord'])
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                // Calculate stock status
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                
                if ($currentStock <= 0) {
                    $stockStatus = 'Out of Stock';
                    $stockClass = 'text-red-600';
                } elseif ($currentStock <= $item->reorder_point) {
                    $stockStatus = 'Low';
                    $stockClass = 'text-amber-500';
                } elseif ($currentStock <= ($item->min_stock_level * 1.5)) {
                    $stockStatus = 'OK';
                    $stockClass = 'text-blue-600';
                } else {
                    $stockStatus = 'High';
                    $stockClass = 'text-green-600';
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'description' => $item->description,
                    'category' => $item->category ? $item->category->name : 'Uncategorized',
                    'unit' => $item->unit ? $item->unit->symbol : 'pcs',
                    'unit_name' => $item->unit ? $item->unit->name : 'piece',
                    'current_stock' => $currentStock,
                    'stock_status' => $stockStatus,
                    'stock_class' => $stockClass,
                    'cost_price' => $item->cost_price ?? 0,
                    'reorder_point' => $item->reorder_point ?? 0,
                    'min_stock_level' => $item->min_stock_level ?? 0,
                    'max_stock_level' => $item->max_stock_level ?? 0
                ];
            });

        // Get categories for filtering (include all item types)
        $categories = Category::where('is_active', true)
            ->withCount(['items' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        // Get user's department from profile
        $department = $user->profile->department ?? 'Production';

        return view('Employee.requisition.create', compact('items', 'categories', 'department'));
    }

    /**
     * Show requisition history.
     */
    public function requisitionHistory(Request $request)
    {
        // Check if this is from a new requisition creation
        $isNewRequisition = session('new_requisition', false);
        
        $query = Requisition::where('requested_by', Auth::id())
            ->with(['requisitionItems' => function($query) {
                $query->with('item.unit');
            }, 'approvedBy']);

        // Apply search filter - only if explicitly provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('requisition_number', 'ilike', "%{$search}%")
                  ->orWhereHas('requisitionItems', function($itemQuery) use ($search) {
                      $itemQuery->whereHas('item', function($itemQ) use ($search) {
                          $itemQ->where('name', 'ilike', "%{$search}%")
                                ->orWhere('item_code', 'ilike', "%{$search}%");
                      });
                  });
            });
        }

        // Apply status filter - only if explicitly provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // If this is a new requisition, show more results and reset filters
        if ($isNewRequisition && !$request->filled('search') && !$request->filled('status')) {
            $requisitions = $query->orderBy('created_at', 'desc')->limit(20)->get();
            // Clear the flag since we're now showing it
            $request->session()->forget('new_requisition');
        } else {
            // Ensure we reset page to 1 if filters are applied to a different page
            if ($request->has('page') && ($request->filled('search') || $request->filled('status'))) {
                $request->merge(['page' => 1]);
            }
            $requisitions = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('Employee.requisition.history', compact('requisitions', 'isNewRequisition'));
    }

    /**
     * Show production log.
     */
    public function productionLog(Request $request)
    {
        // Get all finished goods (not just those with recipes)
        $finishedGoods = Item::where('is_active', true)
            ->where('item_type', 'finished_good')
            ->with('unit')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                // Check if there's an active recipe for this item
                $recipe = Recipe::where('finished_item_id', $item->id)
                    ->where('is_active', true)
                    ->first();
                
                return [
                    'id' => $item->id, // Use item ID
                    'recipe_id' => $recipe ? $recipe->id : null, // Recipe ID if available
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit' => $item->unit ? $item->unit->symbol : 'pcs',
                    'yield_quantity' => $recipe ? $recipe->yield_quantity : 1,
                    'yield_unit' => $recipe ? ($recipe->yieldUnit ? $recipe->yieldUnit->symbol : 'pcs') : ($item->unit ? $item->unit->symbol : 'pcs'),
                    'has_recipe' => $recipe ? true : false
                ];
            });

        return view('Employee.production.log', compact(
            'finishedGoods'
        ));
    }

    /**
     * Store new production entry.
     */
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

            // Find the item
            $item = Item::findOrFail($validated['item_id']);
            
            // Check if there's a recipe for this item
            $recipe = Recipe::where('finished_item_id', $item->id)
                ->where('is_active', true)
                ->first();
            
            $finishedItem = $item;
            $unit = $recipe ? $recipe->yieldUnit : $item->unit;

            // Generate manual production number
            $productionNumber = 'MAN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create batch record for the finished product
            $batch = Batch::create([
                'batch_number' => $validated['batch_number'],
                'item_id' => $finishedItem->id,
                'quantity' => $validated['good_output'],
                'unit_cost' => $finishedItem->cost_price ?? 0,
                'manufacturing_date' => Carbon::now()->toDateString(),
                'expiry_date' => Carbon::now()->addDays($finishedItem->shelf_life_days ?? 30)->toDateString(),
                'status' => 'active'
            ]);

            // Record stock movement for the finished goods
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

            // Create notification for supervisor
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

            return redirect()->route('employee.production.log')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \Log::error('Production logging failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'An error occurred while logging production. Please try again.']);
        }
    }

    /**
     * Show recipes.
     */
    public function recipes()
    {
        $recipes = Recipe::where('is_active', true)
            ->with('finishedItem.category', 'ingredients.item.unit', 'yieldUnit')
            ->orderBy('name')
            ->get();

        return view('Employee.production.recipe', compact('recipes'));
    }

    /**
     * Get recipe details (AJAX).
     */
    public function getRecipeDetails(Recipe $recipe)
    {
        // Load relationships
        $recipe->load([
            'finishedItem.category',
            'ingredients.item.unit',
            'yieldUnit'
        ]);

        // Format recipe data for frontend
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
                'category' => [
                    'name' => $recipe->finishedItem->category->name
                ]
            ] : null
        ];

        return response()->json([
            'success' => true,
            'recipe' => $recipeData
        ]);
    }

    /**
     * Create a new recipe.
     */
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

            // Generate recipe code if not provided
            if (empty($validated['recipe_code'])) {
                $validated['recipe_code'] = 'REC-' . date('Ymd') . '-' . str_pad((Recipe::count() + 1), 4, '0', STR_PAD_LEFT);
            }

            // Create recipe
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

            // Create recipe ingredients
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

            // Create notification for supervisors about new recipe
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
                'recipe' => [
                    'id' => $recipe->id,
                    'name' => $recipe->name,
                    'recipe_code' => $recipe->recipe_code
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Recipe creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the recipe. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an existing recipe.
     */
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

            // Update recipe
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

            // Delete existing ingredients
            RecipeIngredient::where('recipe_id', $recipe->id)->delete();

            // Create new recipe ingredients
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

            // Create notification for supervisors about updated recipe
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
                'recipe' => [
                    'id' => $recipe->id,
                    'name' => $recipe->name,
                    'recipe_code' => $recipe->recipe_code
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Recipe update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the recipe. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a recipe.
     */
    public function deleteRecipe(Recipe $recipe)
    {
        try {
            // Check if recipe exists and is active
            if (!$recipe->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe not found or already deleted.'
                ], 404);
            }



            $recipeName = $recipe->name;
            $recipeCode = $recipe->recipe_code;

            // Delete the recipe (this will cascade delete ingredients due to foreign key constraints)
            $recipe->delete();

            // Create notification for supervisors about deleted recipe
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
            \Log::error('Recipe deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the recipe. Please try again.'
            ], 500);
        }
    }

    /**
     * Confirm receipt of incoming delivery.
     */
    public function confirmReceipt(Request $request, Requisition $requisition)
    {
        // Verify the requisition belongs to current user
        if ($requisition->requested_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if requisition is fulfilled and ready for pickup confirmation
        if ($requisition->status !== 'fulfilled') {
            return back()->with('error', 'Requisition must be fulfilled by inventory before confirming receipt.');
        }

        // Update requisition status to completed (final confirmation)
        $requisition->update([
            'status' => 'completed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => Carbon::now()
        ]);

        // Get the current user name for the notification message
        $currentUser = Auth::user();
        
        // Create notification for supervisor about receipt confirmation
        if ($requisition->approved_by) {
            Notification::create([
                'user_id' => $requisition->approved_by,
                'title' => 'Requisition Completed',
                'message' => "Requisition {$requisition->requisition_number} has been received and confirmed by {$currentUser->name}.",
                'type' => 'requisition_update',
                'priority' => 'normal',
                'action_url' => route('supervisor.requisitions.details', $requisition->id),
                'metadata' => [
                    'requisition_number' => $requisition->requisition_number,
                    'requisition_status' => 'completed',
                    'confirmed_by' => $currentUser->name,
                    'confirmed_at' => Carbon::now()->toDateTimeString(),
                ],
                'created_at' => Carbon::now()
            ]);
        }

        // Create confirmation notification for the employee
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Requisition Completed',
            'message' => "Your requisition {$requisition->requisition_number} has been successfully completed. Thank you for confirming receipt!",
            'type' => 'requisition_update',
            'priority' => 'normal',
            'action_url' => route('employee.requisitions.history'),
            'metadata' => [
                'requisition_number' => $requisition->requisition_number,
                'requisition_status' => 'completed',
                'completed_at' => Carbon::now()->toDateTimeString(),
            ],
            'created_at' => Carbon::now()
        ]);

        return back()->with('success', 'Receipt confirmed successfully.');
    }

    /**
     * Show notifications.
     */
    public function notifications(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        // Calculate stats for the current user
        $totalNotifications = Notification::forCurrentUser()->count();
        $unreadNotifications = Notification::forCurrentUser('unread')->count();
        $highPriorityNotifications = Notification::forCurrentUser('high')->count();
        $urgentNotifications = Notification::forCurrentUser('urgent')->count();

        // Calculate approval and fulfillment notifications
        $allUserNotifications = Notification::forCurrentUser()->get();
        $approvalNotifications = $allUserNotifications->filter(function($notification) {
            return $notification->isApproval();
        })->count();
        
        $fulfillmentNotifications = $allUserNotifications->filter(function($notification) {
            return $notification->isFulfillment();
        })->count();

        // Apply the filter and get notifications
        $query = Notification::forCurrentUser();
        
        // Apply specific filters
        switch($filter) {
            case 'approvals':
                $query = $query->where(function($q) {
                    $q->where('type', Notification::TYPE_APPROVAL_REQUEST)
                      ->orWhere('type', Notification::TYPE_REQUISITION_UPDATE)
                      ->orWhere('type', Notification::TYPE_PURCHASING);
                });
                break;
            case 'fulfillments':
                $query = $query->where(function($q) {
                    $q->where('type', Notification::TYPE_INVENTORY)
                      ->orWhere(function($subq) {
                          $subq->where('type', Notification::TYPE_REQUISITION_UPDATE)
                               ->whereJsonContains('metadata->requisition_status', 'fulfilled')
                               ->orWhereJsonContains('metadata->requisition_status', 'completed');
                      });
                });
                break;
            default:
                // For 'all', 'unread', 'high', 'urgent' filters, use existing logic
                if (in_array($filter, ['unread', 'high', 'urgent'])) {
                    $query = Notification::forCurrentUser($filter);
                }
                break;
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => $totalNotifications,
            'unread' => $unreadNotifications,
            'high_priority' => $highPriorityNotifications,
            'urgent' => $urgentNotifications,
            'approvals' => $approvalNotifications,
            'fulfillments' => $fulfillmentNotifications
        ];

        return view('Employee.notification', compact('notifications', 'stats', 'filter'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsAsRead()
    {
        Notification::markAllAsReadForCurrentUser();

        return response()->json(['success' => true]);
    }

    /**
     * Mark notification as unread.
     */
    public function markNotificationAsUnread(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsUnread();

        return response()->json(['success' => true]);
    }

    /**
     * Get header notifications for the current user.
     */
    public function getHeaderNotifications()
    {
        try {
            // Get only the 5 most recent unread notifications for header display
            $notifications = Notification::forCurrentUser()
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'time_ago' => $notification->getTimeAgoAttribute(),
                        'action_url' => $notification->action_url,
                        'icon_class' => $notification->getIconClass(),
                        'read_at' => $notification->is_read ? now() : null,
                        'priority' => $notification->priority,
                        'type' => $notification->type,
                        'created_at' => $notification->created_at
                    ];
                });

            $unreadCount = Notification::unreadCountForCurrentUser();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting header notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications',
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Get items for requisition (AJAX).
     */
    public function getItemsForRequisition(Request $request)
    {
        $search = $request->get('search');
        
        $items = Item::where('is_active', true)
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('name', 'ilike', "%{$search}%")
                          ->orWhere('item_code', 'ilike', "%{$search}%");
                }
            })
            ->with('unit')
            ->limit(10)
            ->get();

        return response()->json($items);
    }

    /**
     * Get requisition details (AJAX).
     */
    public function getRequisitionDetails(Requisition $requisition)
    {
        // Verify the requisition belongs to current user
        if ($requisition->requested_by !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $requisition->load(['requisitionItems.item.unit']);

        return response()->json([
            'success' => true,
            'requisition' => [
                'id' => $requisition->id,
                'requisition_number' => $requisition->requisition_number,
                'request_date' => $requisition->request_date,
                'department' => $requisition->department,
                'purpose' => $requisition->purpose,
                'status' => $requisition->status,
                'total_estimated_value' => $requisition->total_estimated_value,
                'requisition_items' => $requisition->requisitionItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'quantity_requested' => $item->quantity_requested,
                        'unit_cost_estimate' => $item->unit_cost_estimate,
                        'total_estimated_value' => $item->total_estimated_value,
                        'item' => [
                            'id' => $item->item->id,
                            'name' => $item->item->name,
                            'item_code' => $item->item->item_code,
                            'unit' => [
                                'symbol' => $item->item->unit->symbol,
                                'name' => $item->item->unit->name
                            ]
                        ]
                    ];
                })
            ]
        ]);
    }


}