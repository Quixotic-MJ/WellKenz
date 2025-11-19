@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Item Masterlist</h1>
            <p class="text-sm text-gray-500 mt-1">The central database for all inventory items, unit conversions, and pricing.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-import mr-2"></i> Import CSV
            </button>
            <button onclick="document.getElementById('itemModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Item
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search Item Name, SKU...">
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                <option value="dry">Dry Goods</option>
                <option value="dairy">Dairy & Cold</option>
                <option value="packaging">Packaging</option>
                <option value="wip">Work in Progress</option>
            </select>
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">Stock Status</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
                <option value="good">Good Stock</option>
            </select>
        </div>
    </div>

    {{-- 3. ITEMS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Level</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Config (Factor)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costing</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Item 1: Bread Flour --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-50 rounded flex items-center justify-center text-amber-600">
                                    <i class="fas fa-box text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Bread Flour</div>
                                    <div class="text-xs text-gray-500">SKU: ING-001</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                        Dry Goods
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">150.5 kg</div>
                            <div class="text-xs text-red-500">Reorder at: 50 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Buy:</span> 
                                    <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-100">Sack</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Stock:</span> 
                                    <span class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100">kg</span>
                                </div>
                                <div class="text-xs font-bold text-chocolate mt-1">
                                    1 Sack = 25.00 kg
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">₱950.00 / Sack</div>
                            <div class="text-xs text-gray-500">~ ₱38.00 / kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Item 2: White Sugar --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-50 rounded flex items-center justify-center text-amber-600">
                                    <i class="fas fa-cube text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">White Sugar</div>
                                    <div class="text-xs text-gray-500">SKU: RM-SGR-002</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                        Dry Goods
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">45.0 kg</div>
                            <div class="text-xs text-gray-500">Reorder at: 20 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Buy:</span> 
                                    <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-100">Sack</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Stock:</span> 
                                    <span class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100">kg</span>
                                </div>
                                <div class="text-xs font-bold text-chocolate mt-1">
                                    1 Sack = 50.00 kg
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">₱2,800.00 / Sack</div>
                            <div class="text-xs text-gray-500">~ ₱56.00 / kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">2</span> of <span class="font-medium">1450</span> results</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-chocolate text-white text-sm font-medium">1</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">2</button>
                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- ADD ITEM MODAL (Redesigned) -->
<div id="itemModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('itemModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            
            <!-- Modal Header -->
            <div class="bg-chocolate px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-bold text-white" id="modal-title">Add New Master Item</h3>
                    <p class="text-xs text-white/80 mt-1">Define basic info, conversion logic, and stock thresholds.</p>
                </div>
                <button onclick="document.getElementById('itemModal').classList.add('hidden')" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6">
                <form>
                    <div class="space-y-8">

                        <!-- Section 1: Identity -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">1. Item Identity</h4>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <!-- Name -->
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                                    <input type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Bread Flour">
                                </div>
                                
                                <!-- Category -->
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                    <select class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option>Dry Goods</option>
                                        <option>Dairy & Cold</option>
                                        <option>Packaging</option>
                                    </select>
                                </div>

                                <!-- SKU -->
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Code</label>
                                    <input type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. ING-001">
                                </div>

                                <!-- Perishable Toggle -->
                                <div class="md:col-span-6">
                                    <div class="flex items-center justify-between h-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div>
                                            <span class="text-sm font-bold text-gray-900">Is Perishable?</span>
                                            <p class="text-xs text-gray-500">Forces expiry date entry.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-chocolate"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: ERP Logic (The clean part) -->
                        <div class="bg-blue-50/50 rounded-xl border border-blue-100 p-5 relative">
                            <!-- Section Label -->
                            <div class="flex items-center justify-between mb-5">
                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider flex items-center">
                                    <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center mr-2 text-blue-600"><i class="fas fa-balance-scale"></i></span>
                                    2. Unit Conversion Logic
                                </h4>
                                <span class="text-[10px] px-2 py-1 bg-amber-100 text-amber-800 rounded border border-amber-200 font-semibold flex items-center">
                                    <i class="fas fa-lock mr-1 text-xs"></i> Admin Only
                                </span>
                            </div>
                            
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                                <!-- Buy -->
                                <div class="flex-1 w-full">
                                    <label class="block text-xs font-bold text-blue-700 mb-1 uppercase">Purchase Unit (Buy)</label>
                                    <select class="block w-full border-blue-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                        <option selected>Sack</option>
                                        <option>Box</option>
                                        <option>Tin</option>
                                        <option>Bottle</option>
                                        <option>Piece</option>
                                    </select>
                                </div>

                                <!-- Visual Connector -->
                                <div class="text-blue-300 pt-6 flex justify-center w-full md:w-auto">
                                    <i class="fas fa-arrow-right hidden md:block text-xl"></i>
                                    <i class="fas fa-arrow-down block md:hidden text-xl"></i>
                                </div>

                                <!-- Conversion Input -->
                                <div class="flex-1 w-full">
                                     <label class="block text-xs font-bold text-chocolate mb-1 text-center uppercase">Conversion Factor</label>
                                     <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-400 sm:text-sm font-bold">1 =</span>
                                        </div>
                                        <input type="number" step="0.01" class="block w-full pl-10 pr-3 text-center border-2 border-chocolate/30 rounded-lg focus:ring-chocolate focus:border-chocolate sm:text-lg font-bold text-gray-900" placeholder="25.00" value="25.00">
                                     </div>
                                </div>

                                <!-- Visual Connector -->
                                <div class="text-green-300 pt-6 flex justify-center w-full md:w-auto">
                                    <i class="fas fa-arrow-right hidden md:block text-xl"></i>
                                    <i class="fas fa-arrow-down block md:hidden text-xl"></i>
                                </div>

                                <!-- Stock -->
                                <div class="flex-1 w-full">
                                    <label class="block text-xs font-bold text-green-700 mb-1 uppercase">Stock Unit (Count)</label>
                                    <select class="block w-full border-green-200 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm bg-white">
                                        <option selected>Kilograms (kg)</option>
                                        <option>Grams (g)</option>
                                        <option>Liters (L)</option>
                                        <option>Milliliters (mL)</option>
                                        <option>Pieces (pc)</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-center text-xs text-blue-400 mt-4 font-medium">
                                Logic: <span class="text-gray-600">1 Purchase Unit</span> contains exactly <span class="text-gray-600">25.00 Stock Units</span>
                            </p>
                        </div>

                        <!-- Section 3: Inventory -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">3. Inventory & Costing</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" class="block w-full pl-7 border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0.00">
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-1">Per Purchase Unit</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                                    <div class="flex rounded-md shadow-sm">
                                        <input type="number" class="flex-1 min-w-0 block w-full border-gray-300 rounded-l-lg focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="10">
                                        <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Units</span>
                                    </div>
                                     <p class="text-[10px] text-gray-500 mt-1">Based on Stock Unit</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Stock</label>
                                    <input type="number" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0">
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Save Master Item
                </button>
                <button type="button" onclick="document.getElementById('itemModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection