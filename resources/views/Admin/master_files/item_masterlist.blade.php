@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Item Masterlist</h1>
            <p class="text-sm text-gray-500">The central database for all inventory items, unit conversions, and pricing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="exportItems()" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-file-export mr-2 opacity-70 group-hover:opacity-100"></i> Export CSV
            </button>
            <button onclick="document.getElementById('csvFileInput').click()" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-file-import mr-2 opacity-70 group-hover:opacity-100"></i> Import CSV
            </button>
            <input type="file" id="csvFileInput" accept=".csv,.txt" style="display: none;" onchange="handleFileUpload(this)">
            <button onclick="document.getElementById('itemModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Add New Item
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" id="searchInput" value="{{ request('search') }}" 
                    class="block w-full pl-11 pr-12 py-2.5 border border-gray-200 rounded-lg bg-cream-bg placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                    placeholder="Search Item Name, SKU...">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" id="searchBtn" class="text-gray-400 hover:text-chocolate focus:outline-none transition-colors">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-48">
                    <select id="categoryFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                <div class="relative w-full md:w-40">
                    <select id="stockFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer">
                        <option value="">Stock Status</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="good" {{ request('stock_status') == 'good' ? 'selected' : '' }}>Good Stock</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                <div class="relative w-full md:w-32">
                    <select id="statusFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer">
                        <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Items</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                <button id="clearFiltersBtn" class="px-4 py-2.5 text-sm font-medium text-chocolate bg-white border border-border-soft rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm tooltip" title="Clear Filters">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- 3. ITEMS TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Item Details</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Stock Level</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Unit Config</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Costing</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-border-soft">
                    @forelse($items as $item)
                    <tr class="group hover:bg-cream-bg transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-chocolate to-caramel rounded-lg flex items-center justify-center text-white shadow-sm ring-2 ring-white">
                                    <i class="fas fa-box text-sm"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-chocolate">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">SKU: {{ $item->item_code }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-cream-bg text-caramel border border-border-soft mt-1">
                                        {{ $item->category->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                                $stockLevel = $currentStock > $item->reorder_point ? 'good' : ($currentStock > 0 ? 'low' : 'out');
                            @endphp
                            <div class="text-sm font-bold text-chocolate">
                                {{ number_format($currentStock, 1) }} <span class="text-xs text-gray-500 font-normal">{{ $item->unit->symbol ?? '' }}</span>
                            </div>
                            <div class="text-[10px] font-bold uppercase tracking-wider mt-1 {{ $stockLevel === 'good' ? 'text-green-600' : ($stockLevel === 'low' ? 'text-amber-600' : 'text-red-600') }}">
                                @if($stockLevel === 'good')
                                    <i class="fas fa-check-circle mr-1"></i> Healthy
                                @elseif($stockLevel === 'low')
                                    <i class="fas fa-exclamation-circle mr-1"></i> Low Stock
                                @else
                                    <i class="fas fa-times-circle mr-1"></i> Empty
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1.5">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-12 font-semibold text-chocolate/70">Buy:</span> 
                                    <span class="bg-white border border-border-soft px-1.5 py-0.5 rounded text-gray-700">{{ $item->unit->symbol ?? 'unit' }}</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-12 font-semibold text-chocolate/70">Stock:</span> 
                                    <span class="bg-white border border-border-soft px-1.5 py-0.5 rounded text-gray-700">{{ $item->unit->symbol ?? 'unit' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-chocolate">₱{{ number_format($item->cost_price ?? 0, 2) }}</div>
                            <div class="text-xs text-gray-400">per {{ $item->unit->symbol ?? 'unit' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold uppercase tracking-wide rounded-full {{ $item->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button onclick="editItem({{ $item->id }})" class="text-chocolate hover:text-white hover:bg-chocolate p-2 rounded-lg transition-all tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($item->is_active)
                                <button onclick="deactivateItem({{ $item->id }}, '{{ $item->name }}')" class="text-amber-600 hover:text-white hover:bg-amber-600 p-2 rounded-lg transition-all tooltip" title="Deactivate">
                                    <i class="fas fa-pause"></i>
                                </button>
                                @else
                                <button onclick="reactivateItem({{ $item->id }}, '{{ $item->name }}')" class="text-green-600 hover:text-white hover:bg-green-600 p-2 rounded-lg transition-all tooltip" title="Reactivate">
                                    <i class="fas fa-play"></i>
                                </button>
                                @endif
                                <button onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')" class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-lg transition-all tooltip" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                    <i class="fas fa-box text-chocolate/30 text-2xl"></i>
                                </div>
                                <h3 class="font-display text-lg font-bold text-chocolate">No Items Found</h3>
                                <p class="text-gray-500 text-sm mt-1">Start by adding your first item to the masterlist.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-border-soft">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-600">
                    Showing <span class="font-bold text-chocolate">{{ $items->firstItem() ?? 0 }}</span> 
                    to <span class="font-bold text-chocolate">{{ $items->lastItem() ?? 0 }}</span> 
                    of <span class="font-bold text-chocolate">{{ $items->total() }}</span> results
                </p>
                
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    @if($items->previousPageUrl())
                        <a href="{{ $items->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-200 bg-white text-sm font-medium text-gray-500 hover:bg-cream-bg transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @else
                        <button disabled class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @endif

                    @for($i = 1; $i <= $items->lastPage(); $i++)
                        @if($i == $items->currentPage())
                            <button class="relative inline-flex items-center px-4 py-2 border border-chocolate bg-chocolate text-white text-sm font-bold">{{ $i }}</button>
                        @else
                            <a href="{{ $items->url($i) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:bg-cream-bg transition-colors">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($items->nextPageUrl())
                        <a href="{{ $items->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-200 bg-white text-sm font-medium text-gray-500 hover:bg-cream-bg transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button disabled class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-200 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>

<div id="itemModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-border-soft">
            
            <div class="bg-chocolate px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-display font-bold text-white" id="modal-title">Add New Master Item</h3>
                    <p class="text-xs text-white/70 mt-0.5">Define basic info, conversion logic, and stock thresholds.</p>
                </div>
                <button onclick="closeModal()" class="text-white/60 hover:text-white transition-colors focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-8 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <form id="itemForm">
                    @csrf
                    <div class="space-y-8">

                        <div>
                            <h4 class="text-xs font-bold text-caramel uppercase tracking-widest mb-4 border-b border-border-soft pb-2">1. Item Identity</h4>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-bold text-chocolate mb-1">Item Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="itemName" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="e.g. Bread Flour" required>
                                </div>
                                
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-chocolate mb-1">Category <span class="text-red-500">*</span></label>
                                    <select name="category_id" id="itemCategory" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-chocolate mb-1">SKU / Code <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-barcode text-gray-400 text-xs"></i>
                                        </div>
                                        <input type="text" name="item_code" id="itemCode" class="block w-full pl-9 border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm font-mono" placeholder="Auto-generated based on category" required>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-1">Code will be auto-generated when you select a category. You can still edit it manually.</p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-chocolate mb-1">Item Type <span class="text-red-500">*</span></label>
                                    <select name="item_type" id="itemType" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" required>
                                        <option value="">Select Type</option>
                                        <option value="raw_material">Raw Material</option>
                                        <option value="finished_good">Finished Good</option>
                                        <option value="semi_finished">Semi Finished</option>
                                        <option value="supply">Supply</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-cream-bg rounded-xl border border-border-soft p-5 relative">
                            <h4 class="text-xs font-bold text-chocolate uppercase tracking-widest flex items-center mb-4">
                                <span class="w-6 h-6 rounded-full bg-white border border-border-soft flex items-center justify-center mr-2 text-caramel"><i class="fas fa-balance-scale text-[10px]"></i></span>
                                2. Unit & Pricing
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Unit <span class="text-red-500">*</span></label>
                                    <select name="unit_id" id="itemUnit" class="block w-full border-gray-200 bg-white rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" required>
                                        <option value="">Select Unit</option>
                                        </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Cost Price</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" name="cost_price" id="costPrice" step="0.01" class="block w-full pl-7 border-gray-200 bg-white rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="0.00">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Selling Price</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" name="selling_price" id="sellingPrice" step="0.01" class="block w-full pl-7 border-gray-200 bg-white rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="0.00">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Reorder Level</label>
                                    <input type="number" name="reorder_point" id="reorderLevel" step="0.01" class="block w-full border-gray-200 bg-white rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="0.00">
                                    <p class="text-[10px] text-gray-500 mt-1">Alert when stock drops below this.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-bold text-caramel uppercase tracking-widest mb-4 border-b border-border-soft pb-2">3. Additional Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-chocolate mb-1">Description</label>
                                    <textarea name="description" id="itemDescription" rows="3" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="Item description..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Shelf Life (Days)</label>
                                    <input type="number" name="shelf_life_days" id="shelfLife" min="0" class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm" placeholder="0">
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" onclick="saveItem()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Save Master Item
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation Modal for Delete Operations --}}
<div id="confirmDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-50 border border-red-200 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confirmDeleteTitle">Confirm Delete</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmDeleteMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Delete
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Notification Modal for Success/Error Messages --}}
<div id="notificationModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeNotificationModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="notifIcon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="notifTitle">Notification</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="notifMessage">Message goes here...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" onclick="closeNotificationModal()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables
    let isEditing = false;
    let editingItemId = null;
    let pendingDeleteId = null;

    // --- Modal Functions ---
    function showNotificationModal(title, message, isError = false) {
        const modal = document.getElementById('notificationModal');
        document.getElementById('notifTitle').innerText = title;
        document.getElementById('notifMessage').innerText = message;
        
        const iconContainer = document.getElementById('notifIcon');
        if (isError) {
            iconContainer.innerHTML = '<i class="fas fa-times-circle text-red-500 text-3xl"></i>';
        } else {
            iconContainer.innerHTML = '<i class="fas fa-check-circle text-green-500 text-3xl"></i>';
        }

        modal.classList.remove('hidden');
    }

    function showDeactivationOption(errorMessage, itemId) {
        // Update notification modal to offer deactivation option
        const modal = document.getElementById('notificationModal');
        document.getElementById('notifTitle').innerText = 'Cannot Delete Item';
        document.getElementById('notifMessage').innerHTML = `
            ${errorMessage}<br><br>
            <strong>Would you like to deactivate this item instead?</strong><br>
            Deactivating will hide it from active lists while preserving data integrity.
        `;
        
        const iconContainer = document.getElementById('notifIcon');
        iconContainer.innerHTML = '<i class="fas fa-exclamation-triangle text-amber-500 text-3xl"></i>';
        
        // Change close button to offer deactivation
        const closeButton = modal.querySelector('button[onclick="closeNotificationModal()"]');
        closeButton.innerHTML = 'Cancel';
        closeButton.onclick = () => {
            closeNotificationModal();
            closeButton.innerHTML = 'Close';
            closeButton.onclick = closeNotificationModal;
        };
        
        // Add deactivation button if it doesn't exist
        if (!modal.querySelector('#deactivateButton')) {
            const deactivationButton = document.createElement('button');
            deactivationButton.id = 'deactivateButton';
            deactivationButton.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-amber-600 text-base font-bold text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all mt-3';
            deactivationButton.innerHTML = 'Deactivate Item';
            deactivationButton.onclick = () => executeDeactivation(itemId);
            
            const buttonContainer = modal.querySelector('.bg-gray-50');
            buttonContainer.insertBefore(deactivationButton, buttonContainer.firstChild);
        }
        
        modal.classList.remove('hidden');
    }

    function executeDeactivation(itemId) {
        fetch(`/admin/items/${itemId}/deactivate`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            closeNotificationModal();
            if (data.success) {
                showNotificationModal('Success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotificationModal('Error', data.message || 'Error deactivating item', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeNotificationModal();
            showNotificationModal('Error', 'Error deactivating item', true);
        });
    }

    function closeNotificationModal() {
        document.getElementById('notificationModal').classList.add('hidden');
    }

    function showConfirmDeleteModal(itemId, itemName) {
        pendingDeleteId = itemId;
        document.getElementById('confirmDeleteTitle').innerText = 'Delete Item';
        document.getElementById('confirmDeleteMessage').innerText = `Are you sure you want to delete "${itemName}"? This action cannot be undone.`;
        document.getElementById('confirmDeleteBtn').onclick = executeDelete;
        document.getElementById('confirmDeleteModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmDeleteModal').classList.add('hidden');
        // Properly reset to null value, not string "null"
        pendingDeleteId = null;
    }

    function executeDelete() {
        // Enhanced validation to prevent string "null" from being sent
        if (!pendingDeleteId || pendingDeleteId === 'null' || isNaN(parseInt(pendingDeleteId))) {
            console.error('Invalid item ID for deletion:', pendingDeleteId);
            showNotificationModal('Error', 'Invalid item selected for deletion', true);
            return;
        }
        
        // Convert to integer for safety
        const itemId = parseInt(pendingDeleteId);
        if (itemId <= 0) {
            console.error('Invalid item ID for deletion:', pendingDeleteId);
            showNotificationModal('Error', 'Invalid item ID', true);
            return;
        }
        
        closeConfirmModal();

        fetch(`/admin/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotificationModal('Success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Check if it's a foreign key constraint error
                if (data.message && data.message.includes('referenced') || data.message.includes('constraint')) {
                    // Offer deactivation as alternative
                    showDeactivationOption(data.message, pendingDeleteId);
                } else {
                    showNotificationModal('Error', data.message || 'Error deleting item', true);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('Error', 'Error deleting item', true);
        });
    }

    // Load units when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadUnits();
        
        // Setup search functionality with debouncing
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const debouncedFilter = debounce(filterItems, 2000); // Increased to 2000ms (2 seconds)
        
        searchInput.addEventListener('input', function() {
            debouncedFilter();
        });

        // Add search button click handler
        searchBtn.addEventListener('click', function() {
            clearTimeout(debouncedFilter);
            filterItems();
        });

        // Add Enter key support for immediate search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(debouncedFilter);
                filterItems();
            }
        });

        // Setup filter functionality
        document.getElementById('categoryFilter').addEventListener('change', immediateFilter);
        document.getElementById('stockFilter').addEventListener('change', immediateFilter);
        document.getElementById('statusFilter').addEventListener('change', immediateFilter);

        // Add clear filters functionality
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                document.getElementById('categoryFilter').value = '';
                document.getElementById('stockFilter').value = '';
                document.getElementById('statusFilter').value = 'all';
                filterItems();
            });
        }

        // Add real-time duplicate checking for item code field
        const itemCodeInput = document.getElementById('itemCode');
        if (itemCodeInput) {
            itemCodeInput.addEventListener('input', debounce(checkItemCodeDuplicate, 1000));
        }
    });

    // Load units and categories for dropdowns
    function loadUnits() {
        fetch('/admin/items/data')
            .then(response => response.json())
            .then(data => {
                // Load units
                const unitSelect = document.getElementById('itemUnit');
                unitSelect.innerHTML = '<option value="">Select Unit</option>';
                
                data.units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.name} (${unit.symbol})`;
                    unitSelect.appendChild(option);
                });
                
                // Also refresh categories dropdown in modal
                const categorySelect = document.getElementById('itemCategory');
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });

                    // Add event listener for auto-generating item code
                    categorySelect.addEventListener('change', function() {
                        if (this.value && !isEditing) {
                            generateItemCode(this.value);
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading data:', error));
    }

    // Generate item code based on selected category
    function generateItemCode(categoryId) {
        if (!categoryId) {
            return;
        }

        const itemCodeInput = document.getElementById('itemCode');
        if (itemCodeInput && !isEditing) {
            // Show loading state
            itemCodeInput.value = 'Generating...';
            itemCodeInput.style.opacity = '0.6';

            fetch('/admin/items/generate-code', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ category_id: categoryId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    itemCodeInput.value = data.item_code;
                } else {
                    itemCodeInput.value = '';
                    console.error('Error generating item code:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                itemCodeInput.value = '';
            })
            .finally(() => {
                // Reset loading state
                itemCodeInput.style.opacity = '1';
            });
        }
    }

    // Check for duplicate item codes in real-time
    function checkItemCodeDuplicate() {
        const itemCodeInput = document.getElementById('itemCode');
        if (!itemCodeInput || !itemCodeInput.value || itemCodeInput.value === 'Generating...') {
            return;
        }

        const itemCode = itemCodeInput.value.trim();
        if (!itemCode || itemCode.length < 3) {
            // Clear any previous duplicate warnings
            clearItemCodeWarning();
            return;
        }

        // Don't check if we're editing and the code belongs to the current item
        if (isEditing && itemCode === document.getElementById('itemCode').getAttribute('data-original-code')) {
            clearItemCodeWarning();
            return;
        }

        // Check for duplicates by making a search request
        fetch(`/admin/items/search?q=${encodeURIComponent(itemCode)}`)
            .then(response => response.json())
            .then(items => {
                if (items.length > 0) {
                    showItemCodeWarning(itemCode, items[0].name);
                } else {
                    clearItemCodeWarning();
                }
            })
            .catch(error => {
                console.error('Error checking duplicate item code:', error);
                // Don't show error to user for this background check
            });
    }

    // Show warning for duplicate item code
    function showItemCodeWarning(itemCode, existingItemName) {
        const itemCodeInput = document.getElementById('itemCode');
        
        // Remove existing warning
        clearItemCodeWarning();
        
        // Add warning styling and message
        itemCodeInput.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
        itemCodeInput.classList.remove('border-gray-200', 'focus:border-caramel', 'focus:ring-caramel/20');
        
        // Add warning text below the input
        const warningDiv = document.createElement('div');
        warningDiv.id = 'itemCodeWarning';
        warningDiv.className = 'text-[10px] text-red-500 mt-1 flex items-center';
        warningDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Code "${itemCode}" already exists for item "${existingItemName}"
        `;
        
        itemCodeInput.parentNode.appendChild(warningDiv);
    }

    // Clear item code warning
    function clearItemCodeWarning() {
        const itemCodeInput = document.getElementById('itemCode');
        const warningDiv = document.getElementById('itemCodeWarning');
        
        if (itemCodeInput) {
            itemCodeInput.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
            itemCodeInput.classList.add('border-gray-200', 'focus:border-caramel', 'focus:ring-caramel/20');
        }
        
        if (warningDiv) {
            warningDiv.remove();
        }
    }
    
    // Refresh categories specifically (called after category creation)
    function refreshCategories() {
        fetch('/admin/items/data')
            .then(response => response.json())
            .then(data => {
                // Refresh categories in filter dropdown
                const categoryFilter = document.getElementById('categoryFilter');
                if (categoryFilter) {
                    const currentValue = categoryFilter.value;
                    categoryFilter.innerHTML = '<option value="">All Categories</option>';
                    
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.name;
                        option.textContent = category.name;
                        categoryFilter.appendChild(option);
                    });
                    
                    // Restore previous selection if it still exists
                    if (currentValue && [...categoryFilter.options].some(opt => opt.value === currentValue)) {
                        categoryFilter.value = currentValue;
                    }
                }
                
                // Refresh categories in item modal dropdown
                const categorySelect = document.getElementById('itemCategory');
                if (categorySelect) {
                    const currentModalValue = categorySelect.value;
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                    
                    // Restore previous selection if it still exists
                    if (currentModalValue && [...categorySelect.options].some(opt => opt.value == currentModalValue)) {
                        categorySelect.value = currentModalValue;
                    }
                }
            })
            .catch(error => console.error('Error refreshing categories:', error));
    }

    // Filter items based on search and filters
    function filterItems() {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const stockStatus = document.getElementById('stockFilter').value;
        const status = document.getElementById('statusFilter').value;
        
        const params = new URLSearchParams();
        if (search.trim()) params.append('search', search.trim());
        if (category.trim()) params.append('category', category.trim());
        if (stockStatus.trim()) params.append('stock_status', stockStatus.trim());
        if (status && status !== 'all') params.append('status', status);
        
        // Redirect with query parameters to trigger server-side filtering
        // Ensure we're on the correct path for admin items
        let currentPath = window.location.pathname;
        if (!currentPath.includes('/admin/items')) {
            // If somehow on wrong path, redirect to correct admin items path
            currentPath = '/admin/items';
        }
        window.location.href = `${currentPath}?${params.toString()}`;
    }

    // Debounced search function for better performance
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Enhanced filter function with immediate feedback
    function immediateFilter() {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const stockStatus = document.getElementById('stockFilter').value;
        const status = document.getElementById('statusFilter').value;
        
        // Show loading state
        const tableBody = document.querySelector('tbody');
        if (tableBody) {
            tableBody.style.opacity = '0.6';
        }
        
        // Apply filters immediately
        filterItems();
    }

    // Save item (create or update)
    function saveItem() {
        const form = document.getElementById('itemForm');
        const formData = new FormData(form);
        
        // Validate required fields
        const required = ['name', 'category_id', 'item_type', 'unit_id'];
        const missingFields = [];
        for (let field of required) {
            if (!formData.get(field)) {
                missingFields.push(field.replace('_', ' '));
            }
        }
        
        // Special validation for item_code - either auto-generated or manually entered
        const itemCode = formData.get('item_code');
        if (!itemCode || itemCode.trim() === '' || itemCode === 'Generating...') {
            if (isEditing) {
                // For editing, item_code should already be filled
                missingFields.push('SKU / Code');
            } else {
                // For new items, suggest waiting for auto-generation
                missingFields.push('SKU / Code (please select a category for auto-generation)');
            }
        }
        
        if (missingFields.length > 0) {
            showNotificationModal('Validation Error', `Please fill in the following required fields: ${missingFields.join(', ')}`, true);
            return;
        }
        
        // Show loading state
        const saveButton = document.querySelector('button[onclick="saveItem()"]');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        saveButton.disabled = true;
        
        const url = isEditing ? 
            (editingItemId && editingItemId !== 'null' && !isNaN(parseInt(editingItemId)) ? 
                `/admin/items/${parseInt(editingItemId)}` : 
                (showNotificationModal('Error', 'Invalid item ID for editing', true), null)) : 
            '/admin/items';
        
        if (isEditing && !url) return; // Exit if invalid URL for editing
        const method = isEditing ? 'POST' : 'POST'; // Always use POST
        
        // Add CSRF token and method override for editing
        if (isEditing) {
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('_method', 'PUT');
        } else {
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        }
        
        fetch(url, {
            method: method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            if (data.success) {
                // Close modal first
                closeModal();
                
                // Show success message via modal
                showNotificationModal('Success', data.message);
                
                // Reload the page to refresh the data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Handle duplicate code error specifically
                if (data.error_type === 'duplicate_code') {
                    showNotificationModal('Duplicate Item Code', data.message + ' Click "Generate New Code" to create a unique code.', true);
                    
                    // Add a "Generate New Code" button to the notification
                    setTimeout(() => {
                        const modal = document.getElementById('notificationModal');
                        const buttonContainer = modal.querySelector('.bg-gray-50');
                        
                        // Remove existing generate button if any
                        const existingBtn = modal.querySelector('#generateNewCodeBtn');
                        if (existingBtn) {
                            existingBtn.remove();
                        }
                        
                        // Add generate new code button
                        const generateBtn = document.createElement('button');
                        generateBtn.id = 'generateNewCodeBtn';
                        generateBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all mt-3';
                        generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>Generate New Code';
                        generateBtn.onclick = () => {
                            const categorySelect = document.getElementById('itemCategory');
                            if (categorySelect && categorySelect.value) {
                                generateItemCode(categorySelect.value);
                                closeNotificationModal();
                            }
                        };
                        
                        buttonContainer.insertBefore(generateBtn, buttonContainer.firstChild);
                    }, 100);
                } else {
                    showNotificationModal('Error', data.message || 'Error saving item', true);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            showNotificationModal('Error', 'Error saving item. Please check the console for details.', true);
        });
    }

    // Edit item
    function editItem(itemId) {
        // Enhanced validation to prevent string "null" from being used
        if (!itemId || itemId === 'null' || isNaN(parseInt(itemId))) {
            console.error('Invalid item ID for editing:', itemId);
            showNotificationModal('Error', 'Invalid item selected for editing', true);
            return;
        }
        
        // Convert to integer for safety
        const validItemId = parseInt(itemId);
        if (validItemId <= 0) {
            console.error('Invalid item ID for editing:', itemId);
            showNotificationModal('Error', 'Invalid item ID', true);
            return;
        }
        
        fetch(`/admin/items/${validItemId}/edit`)
            .then(response => response.json())
            .then(item => {
                isEditing = true;
                editingItemId = itemId;
                
                document.getElementById('modal-title').textContent = 'Edit Master Item';
                document.getElementById('itemName').value = item.name;
                document.getElementById('itemCode').value = item.item_code;
                document.getElementById('itemCategory').value = item.category_id;
                document.getElementById('itemType').value = item.item_type;
                document.getElementById('itemUnit').value = item.unit_id;
                document.getElementById('costPrice').value = item.cost_price || 0;
                document.getElementById('sellingPrice').value = item.selling_price || 0;
                document.getElementById('reorderLevel').value = item.reorder_point || 0;
                document.getElementById('itemDescription').value = item.description || '';
                document.getElementById('shelfLife').value = item.shelf_life_days || '';
                
                document.getElementById('itemModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotificationModal('Error', 'Error loading item data', true);
            });
    }

    // Delete item - now uses confirmation modal
    function deleteItem(itemId, itemName) {
        showConfirmDeleteModal(itemId, itemName);
    }

    // Deactivate item - show confirmation modal
    function deactivateItem(itemId, itemName) {
        // Update confirmation modal for deactivation
        document.getElementById('confirmDeleteTitle').innerText = 'Deactivate Item';
        document.getElementById('confirmDeleteMessage').innerText = `Are you sure you want to deactivate "${itemName}"? The item will be hidden from active lists but all data will be preserved.`;
        
        // Update confirm button for deactivation
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.innerHTML = '<i class="fas fa-pause mr-2"></i>Deactivate';
        confirmBtn.className = confirmBtn.className.replace('bg-red-600 hover:bg-red-700', 'bg-amber-600 hover:bg-amber-700');
        confirmBtn.onclick = () => executeDeactivation(itemId);
        
        pendingDeleteId = itemId;
        document.getElementById('confirmDeleteModal').classList.remove('hidden');
    }

    // Reactivate item
    function reactivateItem(itemId, itemName) {
        fetch(`/admin/items/${itemId}/reactivate`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotificationModal('Success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotificationModal('Error', data.message || 'Error reactivating item', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('Error', 'Error reactivating item', true);
        });
    }

    // Export items to CSV
    function exportItems() {
        // Show loading state
        const exportBtn = document.querySelector('button[onclick="exportItems()"]');
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
        exportBtn.disabled = true;

        // Get current filter parameters
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const stockStatus = document.getElementById('stockFilter').value;
        const status = document.getElementById('statusFilter').value;

        // Build URL with current filters
        const params = new URLSearchParams();
        if (search.trim()) params.append('search', search.trim());
        if (category.trim()) params.append('category', category.trim());
        if (stockStatus.trim()) params.append('stock_status', stockStatus.trim());
        if (status && status !== 'all') params.append('status', status);

        const url = `/admin/items/export?${params.toString()}`;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Reset button state
        setTimeout(() => {
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
        }, 2000);

        showNotificationModal('Export Started', 'Your CSV file is being generated and will download automatically.');
    }

    // Handle CSV file upload
    function handleFileUpload(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file type
        const validTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        const validExtensions = ['.csv', '.txt'];
        
        const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
        if (!validExtensions.includes(fileExtension)) {
            showNotificationModal('Invalid File Type', 'Please select a CSV or TXT file.', true);
            input.value = '';
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showNotificationModal('File Too Large', 'Please select a file smaller than 10MB.', true);
            input.value = '';
            return;
        }

        // Show confirmation modal
        const modal = document.getElementById('notificationModal');
        document.getElementById('notifTitle').innerText = 'Import Items';
        document.getElementById('notifMessage').innerHTML = `
            <p>You are about to import items from:</p>
            <p><strong>${file.name}</strong></p>
            <p class="text-sm text-gray-600 mt-2">This will add new items to your masterlist. Existing items with the same code will be skipped.</p>
        `;
        
        const iconContainer = document.getElementById('notifIcon');
        iconContainer.innerHTML = '<i class="fas fa-upload text-blue-500 text-3xl"></i>';
        
        // Update button for import
        const closeButton = modal.querySelector('button[onclick="closeNotificationModal()"]');
        closeButton.innerHTML = 'Cancel';
        closeButton.onclick = () => {
            closeNotificationModal();
            closeButton.innerHTML = 'Close';
            closeButton.onclick = closeNotificationModal;
            input.value = ''; // Clear file input
        };
        
        // Add import button if it doesn't exist
        if (!modal.querySelector('#importConfirmBtn')) {
            const importButton = document.createElement('button');
            importButton.id = 'importConfirmBtn';
            importButton.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all mt-3';
            importButton.innerHTML = '<i class="fas fa-upload mr-2"></i>Import Items';
            importButton.onclick = () => executeImport(file);
            
            const buttonContainer = modal.querySelector('.bg-gray-50');
            buttonContainer.insertBefore(importButton, buttonContainer.firstChild);
        }
        
        modal.classList.remove('hidden');
    }

    // Execute CSV import
    function executeImport(file) {
        closeNotificationModal();

        // Show loading state
        const importBtn = document.querySelector('#importConfirmBtn');
        if (importBtn) {
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing...';
            importBtn.disabled = true;
        }

        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('/admin/items/import', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = data.message;
                if (data.errors && data.errors.length > 0) {
                    message += '<br><br><strong>Errors:</strong><br>' + data.errors.slice(0, 5).join('<br>');
                    if (data.errors.length > 5) {
                        message += `<br>... and ${data.errors.length - 5} more errors`;
                    }
                }
                
                showNotificationModal('Import Complete', message, data.errors && data.errors.length > 0);
                
                if (data.imported > 0) {
                    // Reload page to show new items
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }
            } else {
                showNotificationModal('Import Failed', data.message, true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotificationModal('Import Error', 'Error importing items. Please check the console for details.', true);
        })
        .finally(() => {
            // Clear file input
            document.getElementById('csvFileInput').value = '';
        });
    }

    // Reset form when modal is closed
    function closeModal() {
        document.getElementById('itemModal').classList.add('hidden');
        document.getElementById('itemForm').reset();
        document.getElementById('modal-title').textContent = 'Add New Master Item';
        isEditing = false;
        editingItemId = null;
    }
    
    // Add event listeners for modal close buttons
    document.getElementById('itemModal').addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-900')) {
            closeModal();
        }
    });

    // Add event listeners for outside clicks to close modals
    document.getElementById('confirmDeleteModal').addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-900')) {
            closeConfirmModal();
        }
    });

    document.getElementById('notificationModal').addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-900')) {
            closeNotificationModal();
        }
    });
</script>

@endsection