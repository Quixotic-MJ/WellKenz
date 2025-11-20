@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & SEARCH --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recipe Book</h1>
            <p class="text-sm text-gray-500 mt-1">Standard Operating Procedures (SOP) for production.</p>
        </div>
        <div class="relative w-full md:w-72">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input type="text" placeholder="Find a recipe..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-chocolate focus:border-chocolate">
        </div>
    </div>

    {{-- 2. RECIPE GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        {{-- Recipe Card 1 --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group cursor-pointer" onclick="openRecipeModal('Egg Pie (Classic)')">
            <div class="h-40 bg-amber-100 flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-chart-pie text-6xl text-amber-300 group-hover:scale-110 transition-transform duration-500"></i>
                <span class="absolute bottom-2 right-2 bg-white/90 backdrop-blur text-gray-800 text-xs font-bold px-2 py-1 rounded">
                    Yield: 8 Pies
                </span>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-chocolate transition">Egg Pie (Classic)</h3>
                </div>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">Rich custard filling with a flaky butter crust. Top seller.</p>
                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i> 90 mins</span>
                    <span class="text-xs font-bold text-chocolate">View Details &rarr;</span>
                </div>
            </div>
        </div>

        {{-- Recipe Card 2 --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group cursor-pointer" onclick="openRecipeModal('Soft Roll Dough')">
            <div class="h-40 bg-yellow-50 flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-bread-slice text-6xl text-yellow-200 group-hover:scale-110 transition-transform duration-500"></i>
                <span class="absolute bottom-2 right-2 bg-white/90 backdrop-blur text-gray-800 text-xs font-bold px-2 py-1 rounded">
                    Yield: 5kg Dough
                </span>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-chocolate transition">Soft Roll Dough</h3>
                </div>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">Base dough for dinner rolls, ensaymada, and cheese bread.</p>
                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i> 120 mins</span>
                    <span class="text-xs font-bold text-chocolate">View Details &rarr;</span>
                </div>
            </div>
        </div>

        {{-- Recipe Card 3 --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition group cursor-pointer" onclick="openRecipeModal('Chocolate Moist Cake')">
            <div class="h-40 bg-gray-800 flex items-center justify-center relative overflow-hidden">
                <i class="fas fa-birthday-cake text-6xl text-gray-600 group-hover:scale-110 transition-transform duration-500"></i>
                <span class="absolute bottom-2 right-2 bg-white/90 backdrop-blur text-gray-800 text-xs font-bold px-2 py-1 rounded">
                    Yield: 4 Cakes
                </span>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-chocolate transition">Chocolate Moist Cake</h3>
                </div>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">Dark chocolate cake base. Requires overnight chilling.</p>
                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i> 60 mins</span>
                    <span class="text-xs font-bold text-chocolate">View Details &rarr;</span>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- RECIPE MODAL -->
<div id="recipeModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeRecipeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-white relative">
                <!-- Close Button -->
                <button onclick="closeRecipeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>

                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-6 border-b border-gray-100 pb-6">
                        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 text-2xl">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900" id="modalRecipeTitle">Egg Pie (Classic)</h2>
                            <p class="text-sm text-gray-500">Standard Batch</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Ingredients -->
                        <div>
                            <h4 class="text-sm font-bold text-chocolate uppercase tracking-wider mb-3">Ingredients</h4>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>All Purpose Flour</span>
                                    <span class="font-bold">1.0 kg</span>
                                </li>
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>Butter (Cold)</span>
                                    <span class="font-bold">500 g</span>
                                </li>
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>White Sugar</span>
                                    <span class="font-bold">200 g</span>
                                </li>
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>Eggs (Large)</span>
                                    <span class="font-bold">12 pcs</span>
                                </li>
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>Evaporated Milk</span>
                                    <span class="font-bold">2 Cans</span>
                                </li>
                                <li class="flex justify-between border-b border-dashed border-gray-200 pb-1">
                                    <span>Vanilla Extract</span>
                                    <span class="font-bold">10 ml</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Procedure -->
                        <div>
                            <h4 class="text-sm font-bold text-chocolate uppercase tracking-wider mb-3">Procedure</h4>
                            <ol class="space-y-3 text-sm text-gray-700 list-decimal list-inside">
                                <li>Prepare crust by cutting cold butter into flour and sugar mixture until crumbly.</li>
                                <li>Press dough into tart pans and chill for 30 mins.</li>
                                <li>Whisk eggs, milk, sugar, and vanilla in a separate bowl. strain to remove lumps.</li>
                                <li>Pour custard into chilled shells.</li>
                                <li>Bake at 170°C for 40-45 minutes or until set.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeRecipeModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openRecipeModal(title) {
        document.getElementById('modalRecipeTitle').innerText = title;
        document.getElementById('recipeModal').classList.remove('hidden');
    }

    function closeRecipeModal() {
        document.getElementById('recipeModal').classList.add('hidden');
    }
</script>

@endsection