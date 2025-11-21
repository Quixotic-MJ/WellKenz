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
            <input type="text" id="searchInput" value="{{ request('search') }}" class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search Item Name, SKU...">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" id="searchBtn" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="categoryFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="stockFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">Stock Status</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                <option value="good" {{ request('stock_status') == 'good' ? 'selected' : '' }}>Good Stock</option>
            </select>
            <button id="clearFiltersBtn" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate transition-colors">
                <i class="fas fa-times mr-1"></i> Clear
            </button>
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
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-50 rounded flex items-center justify-center text-amber-600">
                                    <i class="fas fa-box text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ $item->item_code }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
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
                            <div class="text-sm font-bold text-gray-900">
                                {{ number_format($currentStock, 1) }} {{ $item->unit->symbol ?? '' }}
                            </div>
                            <div class="text-xs {{ $stockLevel === 'good' ? 'text-green-500' : 'text-red-500' }}">
                                Reorder at: {{ number_format($item->reorder_point, 1) }} {{ $item->unit->symbol ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Buy:</span> 
                                    <span class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-100">{{ $item->unit->symbol ?? 'unit' }}</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-600">
                                    <span class="w-16 font-semibold">Stock:</span> 
                                    <span class="bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-100">{{ $item->unit->symbol ?? 'unit' }}</span>
                                </div>
                                <div class="text-xs font-bold text-chocolate mt-1">
                                    1 {{ $item->unit->name ?? 'unit' }} = 1 {{ $item->unit->symbol ?? 'unit' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">₱{{ number_format($item->cost_price ?? 0, 2) }}</div>
                            <div class="text-xs text-gray-500">~ ₱{{ number_format(($item->cost_price ?? 0), 2) }} / {{ $item->unit->symbol ?? 'unit' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="editItem({{ $item->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteItem({{ $item->id }})" class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded hover:bg-red-100 transition ml-1">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-box text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Items Found</h3>
                                <p class="text-gray-500">Start by adding your first item to the masterlist.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} results</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    @if($items->previousPageUrl())
                        <a href="{{ $items->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @else
                        <button disabled class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @endif

                    @for($i = 1; $i <= $items->lastPage(); $i++)
                        @if($i == $items->currentPage())
                            <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-chocolate text-white text-sm font-medium">{{ $i }}</button>
                        @else
                            <a href="{{ $items->url($i) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($items->nextPageUrl())
                        <a href="{{ $items->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button disabled class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-300">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
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
                <button onclick="closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6">
                <form id="itemForm">
                    @csrf
                    <div class="space-y-8">

                        <!-- Section 1: Identity -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">1. Item Identity</h4>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <!-- Name -->
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="itemName" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Bread Flour" required>
                                </div>
                                
                                <!-- Category -->
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                    <select name="category_id" id="itemCategory" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- SKU -->
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Code <span class="text-red-500">*</span></label>
                                    <input type="text" name="item_code" id="itemCode" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. ING-001" required>
                                </div>

                                <!-- Item Type -->
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Type <span class="text-red-500">*</span></label>
                                    <select name="item_type" id="itemType" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" required>
                                        <option value="">Select Type</option>
                                        <option value="raw_material">Raw Material</option>
                                        <option value="finished_good">Finished Good</option>
                                        <option value="semi_finished">Semi Finished</option>
                                        <option value="supply">Supply</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Unit & Pricing -->
                        <div class="bg-blue-50/50 rounded-xl border border-blue-100 p-5 relative">
                            <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider flex items-center mb-4">
                                <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center mr-2 text-blue-600"><i class="fas fa-balance-scale"></i></span>
                                2. Unit & Pricing
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                                    <select name="unit_id" id="itemUnit" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" required>
                                        <option value="">Select Unit</option>
                                        <!-- Units will be loaded dynamically -->
                                    </select>
                                </div>

                                <!-- Cost Price -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" name="cost_price" id="costPrice" step="0.01" class="block w-full pl-7 border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Selling Price -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₱</span>
                                        </div>
                                        <input type="number" name="selling_price" id="sellingPrice" step="0.01" class="block w-full pl-7 border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Reorder Point -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                                    <input type="number" name="reorder_point" id="reorderLevel" step="0.01" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Additional Info -->
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">3. Additional Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="description" id="itemDescription" rows="3" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Item description..."></textarea>
                                </div>

                                <!-- Shelf Life -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Shelf Life (Days)</label>
                                    <input type="number" name="shelf_life_days" id="shelfLife" min="0" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0">
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" onclick="saveItem()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Save Master Item
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables
    let isEditing = false;
    let editingItemId = null;

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

        // Add clear filters functionality
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                document.getElementById('categoryFilter').value = '';
                document.getElementById('stockFilter').value = '';
                filterItems();
            });
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
                }
            })
            .catch(error => console.error('Error loading data:', error));
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
        
        const params = new URLSearchParams();
        if (search.trim()) params.append('search', search.trim());
        if (category.trim()) params.append('category', category.trim());
        if (stockStatus.trim()) params.append('stock_status', stockStatus.trim());
        
        // Redirect with query parameters to trigger server-side filtering
        window.location.href = `${window.location.pathname}?${params.toString()}`;
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
        const required = ['name', 'item_code', 'category_id', 'item_type', 'unit_id'];
        for (let field of required) {
            if (!formData.get(field)) {
                alert(`Please fill in the ${field.replace('_', ' ')} field.`);
                return;
            }
        }
        
        // Show loading state
        const saveButton = document.querySelector('button[onclick="saveItem()"]');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        saveButton.disabled = true;
        
        const url = isEditing ? `/admin/items/${editingItemId}` : '/admin/items';
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
                // Show success message
                const successMessage = document.createElement('div');
                successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                successMessage.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${data.message}`;
                document.body.appendChild(successMessage);
                
                // Remove success message after 3 seconds
                setTimeout(() => {
                    if (successMessage.parentNode) {
                        successMessage.remove();
                    }
                }, 3000);
                
                // Close modal
                closeModal();
                
                // Reload the page to refresh the data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Error saving item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
            
            alert('Error saving item. Please check the console for details.');
        });
    }

    // Edit item
    function editItem(itemId) {
        fetch(`/admin/items/${itemId}/edit`)
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
                alert('Error loading item data');
            });
    }

    // Delete item
    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch(`/admin/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error deleting item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting item');
            });
        }
    }

    // Reset form when modal is closed
    function closeModal() {
        document.getElementById('itemModal').classList.add('hidden');
        document.getElementById('itemForm').reset();
        document.getElementById('modal-title').textContent = 'Add New Master Item';
        isEditing = false;
        editingItemId = null;
    }
    
    // Add event listener for modal close button
    document.getElementById('itemModal').addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-500')) {
            closeModal();
        }
    });
</script>

@endsection