@extends('Employee.layout.app')

@section('content')
<style>
    /* Custom Scrollbar for Modal */
    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }
    .modal-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .modal-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }
    .modal-scroll::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
    
    /* Animation Classes */
    .modal-backdrop {
        transition: opacity 0.3s ease-out;
        opacity: 0;
        pointer-events: none;
    }
    .modal-backdrop.active {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-panel {
        transition: all 0.3s ease-out;
        transform: scale(0.95) translateY(10px);
        opacity: 0;
    }
    .modal-panel.active {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    /* Ensure Alerts sit on top of other modals */
    .z-60 {
        z-index: 60;
    }
    .z-70 {
        z-index: 70;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- 1. HEADER & SEARCH --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Production Recipes</h1>
            <p class="text-sm text-gray-500 mt-1">Standard Operating Procedures & Formulation</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="recipeSearch" placeholder="Search by name, code, or ingredient..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent transition-all shadow-sm text-sm">
            </div>
            <button onclick="RecipeManager.createRecipe()" 
                    class="px-4 py-2.5 bg-chocolate text-white rounded-xl hover:bg-chocolate-dark transition-colors flex items-center gap-2 whitespace-nowrap shadow-sm font-medium">
                <i class="fas fa-plus"></i>
                Add Recipe
            </button>
        </div>
    </div>

    {{-- 2. RECIPE GRID --}}
    <div id="recipeGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        @forelse($recipes as $recipe)
            @php
                // Determine Icon and Color based on category or type
                $category = $recipe->finishedItem->category->name ?? 'General';
                $iconClass = 'fa-book';
                $bgClass = 'bg-amber-50 text-amber-600';
                
                switch($category) {
                    case 'Flour & Grains':
                    case 'Breads':
                        $iconClass = 'fa-bread-slice';
                        $bgClass = 'bg-orange-50 text-orange-600';
                        break;
                    case 'Dairy Products':
                    case 'Cakes':
                        $iconClass = 'fa-birthday-cake';
                        $bgClass = 'bg-pink-50 text-pink-600';
                        break;
                    case 'Frosting':
                        $iconClass = 'fa-ice-cream';
                        $bgClass = 'bg-blue-50 text-blue-600';
                        break;
                }
            @endphp

            <div class="group bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col h-full recipe-card"
                 onclick="RecipeManager.open({{ $recipe->id }})"
                 data-name="{{ strtolower($recipe->name) }}"
                 data-code="{{ strtolower($recipe->recipe_code ?? '') }}">
                
                {{-- Card Header --}}
                <div class="h-32 {{ $bgClass }} flex items-center justify-center relative rounded-t-2xl overflow-hidden">
                    <i class="fas {{ $iconClass }} text-5xl opacity-90 group-hover:scale-110 transition-transform duration-500"></i>
                    
                    @if($recipe->recipe_code)
                        <div class="absolute top-3 right-3 bg-white/90 backdrop-blur px-2 py-1 rounded-md text-xs font-bold text-gray-700 shadow-sm">
                            {{ $recipe->recipe_code }}
                        </div>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col">
                    <div class="mb-auto">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-bold text-gray-900 text-lg leading-tight group-hover:text-chocolate transition-colors">
                                {{ $recipe->name }}
                            </h3>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-4">
                            {{ $recipe->description ?: 'Standard formulation details.' }}
                        </p>
                    </div>

                    {{-- Metadata --}}
                    <div class="border-t border-gray-100 pt-4 grid grid-cols-2 gap-2 text-xs text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-clock w-4 text-center mr-1.5 text-gray-400"></i>
                            {{ ($recipe->preparation_time + $recipe->cooking_time) }} mins
                        </div>
                        <div class="flex items-center justify-end">
                            <i class="fas fa-chart-pie w-4 text-center mr-1.5 text-gray-400"></i>
                            {{ number_format($recipe->yield_quantity, 0) }} {{ $recipe->yieldUnit->symbol ?? 'units' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                <div class="bg-gray-50 rounded-full p-6 mb-4">
                    <i class="fas fa-book-open text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No Recipes Found</h3>
                <p class="text-gray-500 mt-1">Contact the administrator to add new SOPs.</p>
            </div>
        @endforelse

    </div>

    {{-- NO SEARCH RESULTS STATE (Hidden by default) --}}
    <div id="noResults" class="hidden flex-col items-center justify-center py-20 text-center">
        <div class="bg-gray-50 rounded-full p-6 mb-4">
            <i class="fas fa-search text-4xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900">No matches found</h3>
        <p class="text-gray-500 mt-1">Try adjusting your search terms.</p>
    </div>
</div>

{{-- 3. VIEW DETAILS MODAL --}}
<div id="recipeModalBackdrop" class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RecipeManager.close()"></div>

    <div id="recipeModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col modal-panel">
        
        <div class="flex items-start justify-between p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
            <div class="flex items-center gap-4">
                <div id="modalIconContainer" class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-900">Recipe Name</h2>
                    <div class="flex items-center gap-3 mt-1 text-sm">
                        <span id="modalCode" class="font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">CODE</span>
                        <span class="text-gray-300">|</span>
                        <span id="modalYield" class="text-gray-600 font-medium">Yield: 0 pcs</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="RecipeManager.print()" class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-print"></i> Print
                </button>
                <button onclick="RecipeManager.editRecipe()" class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="RecipeManager.confirmDelete()" class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button onclick="RecipeManager.close()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 modal-scroll">
            <div id="modalLoading" class="hidden py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-chocolate"></div>
                <p class="mt-3 text-gray-500 text-sm">Retrieving details...</p>
            </div>

            <div id="modalContent" class="space-y-8">
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 text-center">
                        <p class="text-xs text-blue-500 font-bold uppercase tracking-wide">Prep Time</p>
                        <p id="modalPrepTime" class="text-lg font-bold text-gray-800">0 m</p>
                    </div>
                    <div class="bg-orange-50/50 p-3 rounded-xl border border-orange-100 text-center">
                        <p class="text-xs text-orange-500 font-bold uppercase tracking-wide">Cook Time</p>
                        <p id="modalCookTime" class="text-lg font-bold text-gray-800">0 m</p>
                    </div>
                    <div class="bg-green-50/50 p-3 rounded-xl border border-green-100 text-center">
                        <p class="text-xs text-green-500 font-bold uppercase tracking-wide">Total Time</p>
                        <p id="modalTotalTime" class="text-lg font-bold text-gray-800">0 m</p>
                    </div>
                    <div class="bg-purple-50/50 p-3 rounded-xl border border-purple-100 text-center">
                        <p class="text-xs text-purple-500 font-bold uppercase tracking-wide">Category</p>
                        <p id="modalCategory" class="text-sm font-bold text-gray-800 truncate">General</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 bg-white">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-carrot text-chocolate"></i> Ingredients
                        </h3>
                        <ul id="modalIngredients" class="space-y-3 text-sm">
                        </ul>
                    </div>

                    <div class="lg:col-span-2">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                            <i class="fas fa-list-ol text-chocolate"></i> Procedure
                        </h3>
                        
                        <div id="modalNotesContainer" class="mb-6 hidden">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-lightbulb text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700 font-medium">Chef's Notes</p>
                                        <p id="modalNotes" class="text-sm text-yellow-800 mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="modalInstructions" class="space-y-6">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
            <button type="button" onclick="RecipeManager.close()" class="px-5 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 shadow-sm transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

{{-- 4. CREATE RECIPE MODAL --}}
<div id="createRecipeModalBackdrop" class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RecipeManager.closeCreateRecipeModal()"></div>

    <div id="createRecipeModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col modal-panel">
        
        <div class="flex items-start justify-between p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
            <div class="flex items-center gap-4">
                <div id="createModalIcon" class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h2 id="createModalTitle" class="text-2xl font-bold text-gray-900">Create New Recipe</h2>
                    <p id="createModalSubtitle" class="text-sm text-gray-500 mt-1">Add a new production recipe with ingredients and procedures</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="RecipeManager.closeCreateRecipeModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="createRecipeForm" class="flex-1 overflow-y-auto p-6 modal-scroll">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                {{-- Basic Information --}}
                <div class="lg:col-span-2">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-info-circle text-chocolate"></i> Basic Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="recipeName" class="block text-sm font-medium text-gray-700 mb-2">Recipe Name *</label>
                            <input type="text" id="recipeName" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="recipeCode" class="block text-sm font-medium text-gray-700 mb-2">Recipe Code</label>
                            <input type="text" id="recipeCode" name="recipe_code"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="recipeDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="recipeDescription" name="description" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent"></textarea>
                        </div>
                        
                        <div>
                            <label for="finishedItemId" class="block text-sm font-medium text-gray-700 mb-2">Finished Product *</label>
                            <select id="finishedItemId" name="finished_item_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                                <option value="">Select finished product...</option>
                                @foreach(\App\Models\Item::where('item_type', 'finished_good')->where('is_active', true)->orderBy('name')->get() as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->item_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="yieldQuantity" class="block text-sm font-medium text-gray-700 mb-2">Yield Quantity *</label>
                            <input type="number" id="yieldQuantity" name="yield_quantity" step="0.01" min="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="yieldUnitId" class="block text-sm font-medium text-gray-700 mb-2">Yield Unit *</label>
                            <select id="yieldUnitId" name="yield_unit_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                                <option value="">Select unit...</option>
                                @foreach(\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="preparationTime" class="block text-sm font-medium text-gray-700 mb-2">Preparation Time (minutes)</label>
                            <input type="number" id="preparationTime" name="preparation_time" min="0" value="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="cookingTime" class="block text-sm font-medium text-gray-700 mb-2">Cooking Time (minutes)</label>
                            <input type="number" id="cookingTime" name="cooking_time" min="0" value="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="servingSize" class="block text-sm font-medium text-gray-700 mb-2">Serving Size</label>
                            <input type="text" id="servingSize" name="serving_size"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Ingredients Section --}}
                <div class="lg:col-span-2">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-carrot text-chocolate"></i> Ingredients
                    </h3>
                    
                    <div id="ingredientsList" class="space-y-3 mb-4">
                        {{-- Dynamic ingredient rows will be added here --}}
                    </div>
                    
                    <button type="button" onclick="RecipeManager.addIngredientRow()" 
                            class="px-4 py-2 text-sm font-medium text-chocolate bg-chocolate/10 hover:bg-chocolate/20 rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Add Ingredient
                    </button>
                </div>

                {{-- Instructions Section --}}
                <div class="lg:col-span-2">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-list-ol text-chocolate"></i> Procedure
                    </h3>
                    
                    <textarea id="instructions" name="instructions" rows="6"
                              placeholder="Enter the step-by-step procedure for this recipe. Each line will be treated as a separate step."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent"></textarea>
                </div>

                {{-- Notes Section --}}
                <div class="lg:col-span-2">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-lightbulb text-chocolate"></i> Notes
                    </h3>
                    
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Add any additional notes, tips, or special instructions."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent"></textarea>
                </div>
            </div>
        </form>

        <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-between">
            <button type="button" onclick="RecipeManager.closeCreateRecipeModal()" 
                    class="px-5 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 shadow-sm transition-colors">
                Cancel
            </button>
            <div class="flex gap-3">
                <button type="button" id="saveRecipeBtn" onclick="RecipeManager.saveRecipe()" 
                        class="px-6 py-2 bg-chocolate text-white rounded-lg font-medium hover:bg-chocolate-dark shadow-sm transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span id="saveRecipeBtnText">Save Recipe</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 5. CONFIRMATION MODAL (Replaces window.confirm) --}}
<div id="confirmationModalBackdrop" class="fixed inset-0 z-70 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RecipeManager.closeConfirm()"></div>
    
    <div id="confirmationModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col modal-panel overflow-hidden">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="confirmTitle">Delete Recipe?</h3>
            <p class="text-sm text-gray-500" id="confirmMessage">This action cannot be undone.</p>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
            <button type="button" id="confirmBtn"
                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                Confirm
            </button>
            <button type="button" onclick="RecipeManager.closeConfirm()"
                    class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:w-auto sm:text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

{{-- 6. NOTIFICATION/ALERT MODAL (Replaces window.alert) --}}
<div id="notificationModalBackdrop" class="fixed inset-0 z-70 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RecipeManager.closeNotification()"></div>
    
    <div id="notificationModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col modal-panel overflow-hidden">
        <div class="p-6 text-center">
            <div id="notifIconBg" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-5">
                <i id="notifIcon" class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" id="notifTitle">Success</h3>
            <div class="text-sm text-gray-500 whitespace-pre-line" id="notifMessage">Operation successful.</div>
        </div>
        <div class="bg-gray-50 px-6 py-3">
            <button type="button" id="notifBtn" onclick="RecipeManager.closeNotification()"
                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:text-sm">
                Okay, got it
            </button>
        </div>
    </div>
</div>

<script>
/**
 * Recipe Manager Class
 * Handles UI interactions and Data Management
 */
const RecipeManager = {
    data: {},
    pendingAction: null, // Stores the callback for confirmation modal
    editingRecipeId: null, // Track if we're editing a recipe
    
    // DOM Elements
    elements: {
        backdrop: document.getElementById('recipeModalBackdrop'),
        panel: document.getElementById('recipeModalPanel'),
        content: document.getElementById('modalContent'),
        loading: document.getElementById('modalLoading'),
        search: document.getElementById('recipeSearch'),
        grid: document.getElementById('recipeGrid'),
        noResults: document.getElementById('noResults'),
        modalTitle: document.getElementById('modalTitle'),
        createModal: {
            backdrop: document.getElementById('createRecipeModalBackdrop'),
            panel: document.getElementById('createRecipeModalPanel'),
            form: document.getElementById('createRecipeForm'),
            ingredientsList: document.getElementById('ingredientsList')
        },
        // Notification Modal Elements
        notification: {
            backdrop: document.getElementById('notificationModalBackdrop'),
            panel: document.getElementById('notificationModalPanel'),
            title: document.getElementById('notifTitle'),
            message: document.getElementById('notifMessage'),
            icon: document.getElementById('notifIcon'),
            iconBg: document.getElementById('notifIconBg'),
            btn: document.getElementById('notifBtn')
        },
        // Confirmation Modal Elements
        confirmation: {
            backdrop: document.getElementById('confirmationModalBackdrop'),
            panel: document.getElementById('confirmationModalPanel'),
            title: document.getElementById('confirmTitle'),
            message: document.getElementById('confirmMessage'),
            btn: document.getElementById('confirmBtn')
        }
    },

    init(recipes) {
        // Populate internal data store
        recipes.forEach(recipe => {
            this.data[recipe.id] = recipe;
        });

        // Initialize Search Listener
        if (this.elements.search) {
            this.elements.search.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }
    },

    // ========================================
    // CUSTOM MODAL UTILITIES (Replacing Alert/Confirm)
    // ========================================

    showNotification(type, title, message, callback = null) {
        const el = this.elements.notification;
        
        // Set Content
        el.title.textContent = title;
        el.message.textContent = message;

        // Reset Styles
        el.iconBg.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-5 transition-colors';
        el.icon.className = 'text-2xl transition-colors fas';

        // Apply Styles based on Type
        if (type === 'success') {
            el.iconBg.classList.add('bg-green-100');
            el.icon.classList.add('fa-check', 'text-green-600');
        } else if (type === 'error') {
            el.iconBg.classList.add('bg-red-100');
            el.icon.classList.add('fa-times', 'text-red-600');
        } else if (type === 'warning') {
            el.iconBg.classList.add('bg-yellow-100');
            el.icon.classList.add('fa-exclamation', 'text-yellow-600');
        }

        // Handle Callback on Button Click
        el.btn.onclick = () => {
            this.closeNotification();
            if (callback) callback();
        };

        // Show
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });
    },

    closeNotification() {
        const el = this.elements.notification;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
        }, 300);
    },

    showConfirm(title, message, onConfirm) {
        const el = this.elements.confirmation;
        
        el.title.textContent = title;
        el.message.textContent = message;
        
        // Unbind previous events and Bind new click
        const newBtn = el.btn.cloneNode(true);
        el.btn.parentNode.replaceChild(newBtn, el.btn);
        this.elements.confirmation.btn = newBtn;

        newBtn.addEventListener('click', () => {
            this.closeConfirm();
            if (onConfirm) onConfirm();
        });

        // Show
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });
    },

    closeConfirm() {
        const el = this.elements.confirmation;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
        }, 300);
    },

    // ========================================
    // EXISTING LOGIC
    // ========================================

    open(id) {
        const recipe = this.data[id];
        if (!recipe) return;

        this.elements.panel.querySelector('.modal-scroll').scrollTop = 0;
        this.populateModal(recipe);

        this.elements.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            this.elements.backdrop.classList.add('active');
            this.elements.panel.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    },

    close() {
        this.elements.backdrop.classList.remove('active');
        this.elements.panel.classList.remove('active');
        
        setTimeout(() => {
            this.elements.backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    },

    populateModal(recipe) {
        // Basic Info
        document.getElementById('modalTitle').textContent = recipe.name;
        document.getElementById('modalCode').textContent = recipe.recipe_code || 'N/A';
        document.getElementById('modalYield').textContent = `Yield: ${recipe.yield_quantity} ${recipe.yield_unit?.symbol || 'pcs'}`;
        
        // Times
        document.getElementById('modalPrepTime').textContent = (recipe.preparation_time || 0) + ' m';
        document.getElementById('modalCookTime').textContent = (recipe.cooking_time || 0) + ' m';
        document.getElementById('modalTotalTime').textContent = ((recipe.preparation_time || 0) + (recipe.cooking_time || 0)) + ' m';
        document.getElementById('modalCategory').textContent = recipe.finished_item?.category?.name || 'Standard';

        // Ingredients List
        const ingContainer = document.getElementById('modalIngredients');
        if (recipe.ingredients && recipe.ingredients.length > 0) {
            ingContainer.innerHTML = recipe.ingredients.map(ing => `
                <li class="flex items-start justify-between p-2 rounded hover:bg-gray-50 border-b border-gray-100 border-dashed last:border-0">
                    <div class="flex items-start gap-2">
                        <div class="mt-1 w-1.5 h-1.5 rounded-full bg-chocolate/60"></div>
                        <span class="text-gray-700 font-medium">${ing.item.name}
                            ${ing.notes ? `<span class="text-xs text-gray-500 block font-normal italic">${ing.notes}</span>` : ''}
                        </span>
                    </div>
                    <span class="font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded text-xs whitespace-nowrap">
                        ${parseFloat(ing.quantity_required)} ${ing.item.unit.symbol}
                    </span>
                </li>
            `).join('');
        } else {
            ingContainer.innerHTML = '<li class="text-gray-400 italic p-2">No ingredients listed.</li>';
        }

        // Instructions
        const instContainer = document.getElementById('modalInstructions');
        if (recipe.instructions) {
            const steps = recipe.instructions.split('\n').filter(line => line.trim() !== '');
            instContainer.innerHTML = steps.map((step, index) => `
                <div class="flex gap-4 group">
                    <div class="flex-shrink-0 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-chocolate text-white flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-110 transition-transform">
                            ${index + 1}
                        </div>
                        ${index !== steps.length - 1 ? '<div class="w-0.5 h-full bg-gray-200 my-1"></div>' : ''}
                    </div>
                    <div class="pb-6 pt-1">
                        <p class="text-gray-700 leading-relaxed">${step}</p>
                    </div>
                </div>
            `).join('');
        } else {
            instContainer.innerHTML = '<p class="text-gray-400 italic">No instructions provided.</p>';
        }

        // Notes
        const notesContainer = document.getElementById('modalNotesContainer');
        if (recipe.notes) {
            document.getElementById('modalNotes').textContent = recipe.notes;
            notesContainer.classList.remove('hidden');
        } else {
            notesContainer.classList.add('hidden');
        }
    },

    handleSearch(term) {
        term = term.toLowerCase().trim();
        const cards = document.querySelectorAll('.recipe-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name;
            const code = card.dataset.code;
            
            if (name.includes(term) || code.includes(term)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0 && term !== '') {
            this.elements.noResults.classList.remove('hidden');
            this.elements.noResults.classList.add('flex');
        } else {
            this.elements.noResults.classList.add('hidden');
            this.elements.noResults.classList.remove('flex');
        }
    },

    print() {
        const printContent = document.getElementById('modalContent').innerHTML;
        const title = document.getElementById('modalTitle').innerText;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>' + title + '</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body class="p-8">');
        printWindow.document.write('<h1 class="text-3xl font-bold mb-4">' + title + '</h1>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    },

    confirmDelete() {
        const recipeName = this.elements.modalTitle.textContent;
        
        this.showConfirm(
            `Delete "${recipeName}"?`,
            `Are you sure you want to delete this recipe? This action cannot be undone and will remove the recipe and all its ingredients from the system.`,
            () => {
                this.deleteRecipe();
            }
        );
    },

    async deleteRecipe() {
        const recipeId = this.getCurrentRecipeId();
        if (!recipeId) {
            this.showNotification('error', 'Error', 'No recipe selected for deletion.');
            return;
        }

        try {
            // Show loading state on delete button inside modal
            const deleteButton = document.querySelector('button[onclick="RecipeManager.confirmDelete()"]');
            if (deleteButton) {
                const originalHtml = deleteButton.innerHTML;
                deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deleteButton.disabled = true;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found.');
            }

            const response = await fetch(`/employee/recipes/${recipeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.close(); // Close main modal
                this.showNotification('success', 'Deleted!', 'Recipe deleted successfully.', () => {
                    location.reload();
                });
            } else {
                throw new Error(result.message || 'Failed to delete recipe');
            }

        } catch (error) {
            console.error('Error deleting recipe:', error);
            this.showNotification('error', 'Failed', 'Error deleting recipe: ' + error.message);
        } finally {
            // Reset button state
            const deleteButton = document.querySelector('button[onclick="RecipeManager.confirmDelete()"]');
            if (deleteButton) {
                deleteButton.innerHTML = '<i class="fas fa-trash"></i> Delete';
                deleteButton.disabled = false;
            }
        }
    },

    getCurrentRecipeId() {
        const recipeName = this.elements.modalTitle.textContent;
        for (const [id, recipe] of Object.entries(this.data)) {
            if (recipe.name === recipeName) {
                return parseInt(id);
            }
        }
        return null;
    },

    createRecipe() {
        this.editingRecipeId = null; // Reset editing mode
        this.resetCreateForm();
        this.addIngredientRow();
        
        // Update modal title and button
        document.getElementById('createModalTitle').textContent = 'Create New Recipe';
        document.getElementById('createModalSubtitle').textContent = 'Add a new production recipe with ingredients and procedures';
        document.getElementById('createModalIcon').className = 'w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xl shadow-sm';
        document.getElementById('createModalIcon').innerHTML = '<i class="fas fa-plus"></i>';
        document.getElementById('saveRecipeBtnText').textContent = 'Save Recipe';
        
        this.elements.createModal.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            this.elements.createModal.backdrop.classList.add('active');
            this.elements.createModal.panel.classList.add('active');
        });
    },

    editRecipe() {
        const recipeId = this.getCurrentRecipeId();
        if (!recipeId) {
            this.showNotification('error', 'Error', 'No recipe selected for editing.');
            return;
        }

        const recipe = this.data[recipeId];
        if (!recipe) {
            this.showNotification('error', 'Error', 'Recipe data not found.');
            return;
        }

        this.editingRecipeId = recipeId;
        this.resetCreateForm();
        
        // Update modal title and button for editing
        document.getElementById('createModalTitle').textContent = 'Edit Recipe';
        document.getElementById('createModalSubtitle').textContent = 'Update recipe information and ingredients';
        document.getElementById('createModalIcon').className = 'w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow-sm';
        document.getElementById('createModalIcon').innerHTML = '<i class="fas fa-edit"></i>';
        document.getElementById('saveRecipeBtnText').textContent = 'Update Recipe';
        
        // Populate form with existing data
        document.getElementById('recipeName').value = recipe.name || '';
        document.getElementById('recipeCode').value = recipe.recipe_code || '';
        document.getElementById('recipeDescription').value = recipe.description || '';
        document.getElementById('finishedItemId').value = recipe.finished_item_id || '';
        document.getElementById('yieldQuantity').value = recipe.yield_quantity || '';
        document.getElementById('yieldUnitId').value = recipe.yield_unit_id || '';
        document.getElementById('preparationTime').value = recipe.preparation_time || 0;
        document.getElementById('cookingTime').value = recipe.cooking_time || 0;
        document.getElementById('servingSize').value = recipe.serving_size || '';
        document.getElementById('instructions').value = recipe.instructions || '';
        document.getElementById('notes').value = recipe.notes || '';
        
        // Populate ingredients
        if (recipe.ingredients && recipe.ingredients.length > 0) {
            recipe.ingredients.forEach(ingredient => {
                this.addIngredientRow();
                const rows = this.elements.createModal.ingredientsList.querySelectorAll('.ingredient-row');
                const lastRow = rows[rows.length - 1];
                
                const itemSelect = lastRow.querySelector('.ingredient-item');
                const quantityInput = lastRow.querySelector('.ingredient-quantity');
                const unitSelect = lastRow.querySelector('.ingredient-unit');
                const notesInput = lastRow.querySelector('.ingredient-notes');
                
                if (itemSelect) itemSelect.value = ingredient.item_id || '';
                if (quantityInput) quantityInput.value = ingredient.quantity_required || '';
                if (unitSelect) unitSelect.value = ingredient.unit_id || '';
                if (notesInput) notesInput.value = ingredient.notes || '';
            });
        } else {
            this.addIngredientRow();
        }
        
        // Close the view modal and open edit modal
        this.close();
        
        this.elements.createModal.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            this.elements.createModal.backdrop.classList.add('active');
            this.elements.createModal.panel.classList.add('active');
        });
    },

    closeCreateRecipeModal() {
        this.elements.createModal.backdrop.classList.remove('active');
        this.elements.createModal.panel.classList.remove('active');
        
        setTimeout(() => {
            this.elements.createModal.backdrop.classList.add('hidden');
        }, 300);
    },

    resetCreateForm() {
        this.elements.createModal.form.reset();
        this.elements.createModal.ingredientsList.innerHTML = '<div class="text-center text-gray-500 py-8"><i class="fas fa-carrot text-2xl mb-2"></i><p>No ingredients added yet</p></div>';
    },

    addIngredientRow() {
        const emptyState = this.elements.createModal.ingredientsList.querySelector('.text-center.text-gray-500');
        if (emptyState) {
            emptyState.remove();
        }

        const ingredientRowId = 'ingredient-' + Date.now();
        const row = document.createElement('div');
        row.className = 'ingredient-row bg-gray-50 p-4 rounded-lg border border-gray-200';
        row.id = ingredientRowId;
        
        row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ingredient Item</label>
                    <select name="ingredients[0][item_id]" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent ingredient-item">
                        <option value="">Select item...</option>
                        @foreach(\App\Models\Item::where('is_active', true)->where('item_type', 'raw_material')->orderBy('name')->get() as $item)
                            <option value="{{ $item->id }}" data-unit="{{ $item->unit->symbol ?? 'pcs' }}">{{ $item->name }} ({{ $item->item_code }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity Required</label>
                    <input type="number" name="ingredients[0][quantity_required]" step="0.001" min="0" placeholder="0.000"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent ingredient-quantity">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit</label>
                    <select name="ingredients[0][unit_id]" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent ingredient-unit">
                        <option value="">Select unit...</option>
                        @foreach(\App\Models\Unit::where('is_active', true)->orderBy('name')->get() as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="button" onclick="RecipeManager.removeIngredientRow('${ingredientRowId}')" 
                            class="w-full px-3 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 border border-red-200 rounded-lg transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-trash"></i>
                        Remove
                    </button>
                </div>
            </div>
            
            <div class="mt-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Notes (Optional)</label>
                <input type="text" name="ingredients[0][notes]" placeholder="e.g., room temperature, sifted, etc."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent ingredient-notes">
            </div>
        `;
        
        this.elements.createModal.ingredientsList.appendChild(row);
        this.updateIngredientIndices();
    },

    removeIngredientRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            if (this.elements.createModal.ingredientsList.querySelectorAll('.ingredient-row').length === 0) {
                this.elements.createModal.ingredientsList.innerHTML = '<div class="text-center text-gray-500 py-8"><i class="fas fa-carrot text-2xl mb-2"></i><p>No ingredients added yet</p></div>';
            } else {
                this.updateIngredientIndices();
            }
        }
    },

    updateIngredientIndices() {
        const rows = this.elements.createModal.ingredientsList.querySelectorAll('.ingredient-row');
        rows.forEach((row, index) => {
            const itemSelect = row.querySelector('.ingredient-item');
            const quantityInput = row.querySelector('.ingredient-quantity');
            const unitSelect = row.querySelector('.ingredient-unit');
            const notesInput = row.querySelector('.ingredient-notes');
            
            if (itemSelect) itemSelect.name = `ingredients[${index}][item_id]`;
            if (quantityInput) quantityInput.name = `ingredients[${index}][quantity_required]`;
            if (unitSelect) unitSelect.name = `ingredients[${index}][unit_id]`;
            if (notesInput) notesInput.name = `ingredients[${index}][notes]`;
        });
    },

    async saveRecipe() {
        const form = this.elements.createModal.form;
        const formData = new FormData(form);
        
        // Validate required fields
        const requiredFields = [
            { id: 'recipeName', name: 'Recipe Name' },
            { id: 'finishedItemId', name: 'Finished Product' },
            { id: 'yieldQuantity', name: 'Yield Quantity' },
            { id: 'yieldUnitId', name: 'Yield Unit' }
        ];
        let hasErrors = false;
        const errorMessages = [];
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field.id);
            if (input) {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    hasErrors = true;
                    errorMessages.push(`${field.name} is required`);
                } else {
                    input.classList.remove('border-red-500');
                }
            }
        });
        
        if (hasErrors) {
            this.showNotification('warning', 'Validation Error', 'Please fix the following errors:\n\n' + errorMessages.join('\n'));
            return;
        }
        
        // Validate ingredients
        const ingredientRows = this.elements.createModal.ingredientsList.querySelectorAll('.ingredient-row');
        if (ingredientRows.length === 0) {
            this.showNotification('warning', 'Missing Ingredients', 'Please add at least one ingredient.');
            return;
        }
        
        for (let row of ingredientRows) {
            const itemSelect = row.querySelector('.ingredient-item');
            const quantityInput = row.querySelector('.ingredient-quantity');
            const unitSelect = row.querySelector('.ingredient-unit');
            
            if (itemSelect && !itemSelect.value) {
                itemSelect.classList.add('border-red-500');
                hasErrors = true;
            } else if (itemSelect) {
                itemSelect.classList.remove('border-red-500');
            }
            
            if (quantityInput) {
                const quantity = parseFloat(quantityInput.value);
                if (!quantityInput.value || quantity <= 0) {
                    quantityInput.classList.add('border-red-500');
                    hasErrors = true;
                } else {
                    quantityInput.classList.remove('border-red-500');
                }
            }
            
            if (unitSelect && !unitSelect.value) {
                unitSelect.classList.add('border-red-500');
                hasErrors = true;
            } else if (unitSelect) {
                unitSelect.classList.remove('border-red-500');
            }
        }
        
        if (hasErrors) {
            this.showNotification('warning', 'Invalid Ingredients', 'Please ensure all ingredients have an Item, Quantity, and Unit selected.');
            return;
        }
        
        try {
            const saveButton = document.getElementById('saveRecipeBtn');
            if (saveButton) {
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>' + (this.editingRecipeId ? 'Updating...' : 'Saving...') + '</span>';
                saveButton.disabled = true;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found.');
            }
            
            const url = this.editingRecipeId 
                ? `/employee/recipes/${this.editingRecipeId}` 
                : '/employee/recipes';
            const method = this.editingRecipeId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.formDataToObject(formData))
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.closeCreateRecipeModal();
                const message = this.editingRecipeId ? 'Recipe updated successfully.' : 'Recipe created successfully.';
                this.showNotification('success', 'Success!', message, () => {
                    location.reload();
                });
            } else {
                throw new Error(result.message || 'Failed to save recipe');
            }
            
        } catch (error) {
            console.error('Error saving recipe:', error);
            this.showNotification('error', 'Error', 'Error saving recipe: ' + error.message);
        } finally {
            const saveButton = document.getElementById('saveRecipeBtn');
            if (saveButton) {
                saveButton.innerHTML = '<i class="fas fa-save"></i> <span id="saveRecipeBtnText">' + (this.editingRecipeId ? 'Update Recipe' : 'Save Recipe') + '</span>';
                saveButton.disabled = false;
            }
        }
    },

    formDataToObject(formData) {
        const data = {};
        
        data.name = formData.get('name') || '';
        data.recipe_code = formData.get('recipe_code') || null;
        data.description = formData.get('description') || null;
        data.finished_item_id = parseInt(formData.get('finished_item_id'));
        data.yield_quantity = parseFloat(formData.get('yield_quantity'));
        data.yield_unit_id = parseInt(formData.get('yield_unit_id'));
        data.preparation_time = parseInt(formData.get('preparation_time') || 0);
        data.cooking_time = parseInt(formData.get('cooking_time') || 0);
        data.serving_size = formData.get('serving_size') || null;
        data.instructions = formData.get('instructions') || null;
        data.notes = formData.get('notes') || null;
        
        data.ingredients = [];
        const ingredientKeys = Array.from(formData.keys()).filter(key => key.startsWith('ingredients['));
        const ingredientIndices = [...new Set(ingredientKeys.map(key => {
            const match = key.match(/ingredients\[(\d+)\]/);
            return match ? match[1] : null;
        }).filter(Boolean))];
        
        ingredientIndices.forEach(index => {
            const itemId = formData.get(`ingredients[${index}][item_id]`);
            const quantityValue = formData.get(`ingredients[${index}][quantity_required]`);
            const unitId = formData.get(`ingredients[${index}][unit_id]`);
            const notes = formData.get(`ingredients[${index}][notes]`);
            
            if (itemId && quantityValue && unitId) {
                const quantityRequired = parseFloat(quantityValue);
                if (quantityRequired > 0 && itemId && unitId) {
                    data.ingredients.push({
                        item_id: parseInt(itemId),
                        quantity_required: quantityRequired,
                        unit_id: parseInt(unitId),
                        notes: notes || null
                    });
                }
            }
        });
        
        return data;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const recipesData = [
        @foreach($recipes as $recipe)
        {
            id: {{ $recipe->id }},
            name: {!! json_encode($recipe->name) !!},
            recipe_code: {!! json_encode($recipe->recipe_code) !!},
            description: {!! json_encode($recipe->description) !!},
            finished_item_id: {{ $recipe->finished_item_id }},
            yield_quantity: {{ $recipe->yield_quantity }},
            yield_unit_id: {{ $recipe->yield_unit_id }},
            preparation_time: {{ $recipe->preparation_time ?? 0 }},
            cooking_time: {{ $recipe->cooking_time ?? 0 }},
            serving_size: {!! json_encode($recipe->serving_size) !!},
            yield_unit: {
                symbol: "{{ $recipe->yieldUnit->symbol ?? 'pcs' }}"
            },
            finished_item: {
                category: {
                    name: "{{ $recipe->finishedItem->category->name ?? 'General' }}"
                }
            },
            ingredients: [
                @foreach($recipe->ingredients as $ingredient)
                {
                    item_id: {{ $ingredient->item_id }},
                    quantity_required: {{ $ingredient->quantity_required }},
                    unit_id: {{ $ingredient->item->unit_id }},
                    notes: {!! json_encode($ingredient->notes) !!},
                    item: {
                        name: {!! json_encode($ingredient->item->name) !!},
                        unit: { symbol: "{{ $ingredient->item->unit->symbol }}" }
                    }
                },
                @endforeach
            ],
            instructions: {!! json_encode($recipe->instructions) !!},
            notes: {!! json_encode($recipe->notes) !!}
        },
        @endforeach
    ];

    RecipeManager.init(recipesData);
});
</script>
@endsection