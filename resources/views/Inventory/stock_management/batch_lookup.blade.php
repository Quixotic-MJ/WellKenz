@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    {{-- 1. HEADER & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Batch Locator</h1>
        <p class="text-sm text-gray-500 mb-6">Find exactly where specific items or batches are stored in the warehouse.</p>
        
        <div class="max-w-2xl mx-auto relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-lg"></i>
            </div>
            <input type="text" 
                   id="batchSearchInput" 
                   class="block w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-full shadow-sm focus:ring-chocolate focus:border-chocolate text-lg" 
                   placeholder="Scan Barcode or Type Item Name / Batch #..." 
                   autofocus
                   autocomplete="off">
            <button id="searchBtn" 
                    class="absolute inset-y-1 right-1 px-6 bg-chocolate text-white font-medium rounded-full hover:bg-chocolate-dark transition">
                Search
            </button>
        </div>

        {{-- Search Filters --}}
        <div class="mt-6 flex justify-center gap-4 flex-wrap">
            <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="quarantine">Quarantine</option>
                <option value="expired">Expired</option>
                <option value="consumed">Consumed</option>
            </select>
            
            <select id="expiryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                <option value="all">All Items</option>
                <option value="active">Not Expired</option>
                <option value="expiring_soon">Expiring Soon (≤7 days)</option>
                <option value="expired">Expired</option>
                <option value="no_expiry">No Expiry Date</option>
            </select>
        </div>
    </div>

    {{-- 2. SEARCH RESULTS CONTAINER --}}
    <div id="searchResultsContainer">
        {{-- Loading Spinner --}}
        <div id="loadingSpinner" class="hidden flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-chocolate"></div>
        </div>

        {{-- No Search State (Initial Load) --}}
        <div id="noSearchState" class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-search text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Search Batches</h3>
            <p class="text-gray-500">Enter a batch number, item name, or barcode to find specific inventory items.</p>
        </div>

        {{-- No Results State --}}
        <div id="noResultsState" class="hidden text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-4xl text-red-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Batches Found</h3>
            <p class="text-gray-500">No batches match your search criteria. Try different keywords or adjust your filters.</p>
        </div>

        {{-- Error State --}}
        <div id="errorState" class="hidden text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times-circle text-4xl text-red-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Search Error</h3>
            <p id="errorMessage" class="text-gray-500">An error occurred while searching. Please try again.</p>
        </div>

        {{-- Search Results Grid --}}
        <div id="searchResultsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6 hidden">
            {{-- Results will be populated here by JavaScript --}}
        </div>
    </div>

    {{-- 3. INITIAL RECENT BATCHES (if any) --}}
    @if(isset($recentBatches) && $recentBatches->count() > 0)
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Batches</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($recentBatches as $batch)
                @php
                    $now = \Carbon\Carbon::now();
                    $expiryDate = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date) : null;
                    $expiryDays = $expiryDate ? $now->diffInDays($expiryDate, false) : null;
                    $isExpired = $expiryDays !== null && $expiryDays < 0;
                    $isExpiringSoon = !$isExpired && $expiryDays !== null && $expiryDays <= 7;
                    
                    // Determine border color based on status and expiry
                    $borderColor = 'border-green-500';
                    if ($isExpired) {
                        $borderColor = 'border-red-500';
                    } elseif ($isExpiringSoon) {
                        $borderColor = 'border-yellow-500';
                    } elseif ($batch->status === 'quarantine') {
                        $borderColor = 'border-yellow-500';
                    }
                    
                    // Get icon based on item type
                    $itemType = $batch->item->item_type ?? 'supply';
                    $iconMap = [
                        'raw_material' => ['class' => 'fas fa-seedling', 'bg' => 'bg-green-100', 'color' => 'text-green-700'],
                        'finished_good' => ['class' => 'fas fa-birthday-cake', 'bg' => 'bg-purple-100', 'color' => 'text-purple-700'],
                        'semi_finished' => ['class' => 'fas fa-cookie-bite', 'bg' => 'bg-orange-100', 'color' => 'text-orange-700'],
                        'supply' => ['class' => 'fas fa-box', 'bg' => 'bg-blue-100', 'color' => 'text-blue-700'],
                    ];
                    $icon = $iconMap[$itemType] ?? $iconMap['supply'];
                    
                    // Status badge
                    $statusBadge = match($batch->status) {
                        'active' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Active'],
                        'quarantine' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Quarantine'],
                        'expired' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Expired'],
                        'consumed' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Consumed'],
                        default => ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($batch->status)]
                    };
                    
                    if ($isExpiringSoon && $batch->status === 'active') {
                        $statusBadge = ['class' => 'bg-red-100 text-red-800 animate-pulse', 'text' => 'Expiring Soon'];
                    }
                @endphp
                
                <div class="bg-white border-l-4 {{ $borderColor }} border-y border-r border-gray-200 rounded-lg shadow-sm hover:shadow-md transition p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 {{ $icon['bg'] }} rounded-lg flex items-center justify-center {{ $icon['color'] }} text-xl">
                                <i class="{{ $icon['class'] }}"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $batch->item->name }}</h3>
                                <p class="text-sm text-gray-500">SKU: {{ $batch->item->item_code }}</p>
                            </div>
                        </div>
                        <span class="{{ $statusBadge['class'] }} text-xs font-bold px-3 py-1 rounded-full">
                            {{ $statusBadge['text'] }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold">Batch Number</p>
                            <p class="font-mono text-gray-800 font-bold mt-1">{{ $batch->batch_number }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold">Expiry Date</p>
                            <p class="text-gray-800 font-medium mt-1">
                                @if($expiryDate)
                                    {{ $expiryDate->format('M d, Y') }}
                                    @if($isExpired)
                                        <span class="text-red-600">(Expired)</span>
                                    @elseif($isExpiringSoon)
                                        <span class="text-yellow-600">({{ $expiryDays }} days)</span>
                                    @endif
                                @else
                                    No Expiry
                                @endif
                            </p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded border border-blue-100 col-span-2 flex justify-between items-center">
                            <div>
                                <p class="text-xs text-blue-600 uppercase font-bold">Warehouse Location</p>
                                <p class="text-lg text-blue-900 font-bold mt-1">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    {{ $batch->location ?? 'Main Storage' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-blue-600 uppercase font-bold">Qty Here</p>
                                <p class="text-lg text-blue-900 font-bold">{{ number_format($batch->quantity, 2) }} {{ $batch->item->unit->symbol ?? 'pcs' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($batch->supplier)
                    <div class="mt-3 text-xs text-gray-500">
                        <i class="fas fa-truck mr-1"></i>
                        Supplier: {{ $batch->supplier->name }}
                    </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('batchSearchInput');
    const searchBtn = document.getElementById('searchBtn');
    const statusFilter = document.getElementById('statusFilter');
    const expiryFilter = document.getElementById('expiryFilter');
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const noSearchState = document.getElementById('noSearchState');
    const noResultsState = document.getElementById('noResultsState');
    const errorState = document.getElementById('errorState');
    const errorMessage = document.getElementById('errorMessage');
    const searchResultsGrid = document.getElementById('searchResultsGrid');
    
    let searchTimeout;
    
    // Search function
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Hide all states initially
        hideAllStates();
        
        if (searchTerm.length === 0) {
            showState('noSearch');
            return;
        }
        
        if (searchTerm.length < 2) {
            showState('error', 'Please enter at least 2 characters to search');
            return;
        }
        
        // Show loading
        showState('loading');
        
        // Prepare request data
        const requestData = {
            search: searchTerm,
            status: statusFilter.value,
            expiry_filter: expiryFilter.value
        };
        
        // Perform AJAX search
        fetch(`{{ route('inventory.stock.lookup.search') }}?${new URLSearchParams(requestData)}`)
            .then(response => response.json())
            .then(data => {
                hideAllStates();
                
                if (data.success) {
                    if (data.data && data.data.length > 0) {
                        displaySearchResults(data.data);
                        showState('results');
                    } else {
                        showState('noResults');
                    }
                } else {
                    showState('error', data.message || 'Search failed');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                hideAllStates();
                showState('error', 'Network error. Please check your connection and try again.');
            });
    }
    
    // Display search results
    function displaySearchResults(batches) {
        searchResultsGrid.innerHTML = '';
        
        batches.forEach(batch => {
            const batchElement = createBatchElement(batch);
            searchResultsGrid.appendChild(batchElement);
        });
    }
    
    // Create batch element
    function createBatchElement(batch) {
        const div = document.createElement('div');
        div.className = `bg-white border-l-4 ${batch.priority_color} border-y border-r border-gray-200 rounded-lg shadow-sm hover:shadow-md transition p-6`;
        
        // Format expiry date display
        let expiryDisplay = batch.expiry_date;
        let expiryClass = 'text-gray-800';
        
        if (batch.is_expired) {
            expiryClass = 'text-red-600';
            expiryDisplay += ' (Expired)';
        } else if (batch.is_expiring_soon) {
            expiryClass = 'text-yellow-600';
            expiryDisplay += ` (${batch.expiry_days} days)`;
        }
        
        if (!batch.expiry_date || batch.expiry_date === 'No Expiry') {
            expiryDisplay = 'No Expiry';
            expiryClass = 'text-gray-800';
        }
        
        div.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 ${batch.icon.bg} rounded-lg flex items-center justify-center ${batch.icon.color} text-xl">
                        <i class="${batch.icon.class}"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">${batch.item.name}</h3>
                        <p class="text-sm text-gray-500">SKU: ${batch.item.item_code}</p>
                    </div>
                </div>
                <span class="${batch.status_badge.class} text-xs font-bold px-3 py-1 rounded-full">
                    ${batch.status_badge.text}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold">Batch Number</p>
                    <p class="font-mono text-gray-800 font-bold mt-1">${batch.batch_number}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold">Expiry Date</p>
                    <p class="${expiryClass} font-medium mt-1">${expiryDisplay}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded border border-blue-100 col-span-2 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-blue-600 uppercase font-bold">Warehouse Location</p>
                        <p class="text-lg text-blue-900 font-bold mt-1">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            ${batch.location}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-600 uppercase font-bold">Qty Here</p>
                        <p class="text-lg text-blue-900 font-bold">${batch.quantity.toFixed(2)} ${batch.item.unit.symbol}</p>
                    </div>
                </div>
            </div>
            
            ${batch.supplier.name !== 'N/A' ? `
            <div class="mt-3 text-xs text-gray-500">
                <i class="fas fa-truck mr-1"></i>
                Supplier: ${batch.supplier.name}
            </div>
            ` : ''}
            
            <div class="mt-3 text-xs text-gray-400">
                <i class="fas fa-calendar mr-1"></i>
                Added: ${batch.created_at}
            </div>
        `;
        
        return div;
    }
    
    // State management functions
    function hideAllStates() {
        loadingSpinner.classList.add('hidden');
        noSearchState.classList.add('hidden');
        noResultsState.classList.add('hidden');
        errorState.classList.add('hidden');
        searchResultsGrid.classList.add('hidden');
    }
    
    function showState(state, message = null) {
        switch(state) {
            case 'loading':
                loadingSpinner.classList.remove('hidden');
                break;
            case 'noSearch':
                noSearchState.classList.remove('hidden');
                break;
            case 'noResults':
                noResultsState.classList.remove('hidden');
                break;
            case 'error':
                errorState.classList.remove('hidden');
                if (message) {
                    errorMessage.textContent = message;
                }
                break;
            case 'results':
                searchResultsGrid.classList.remove('hidden');
                break;
        }
    }
    
    // Event listeners
    searchBtn.addEventListener('click', performSearch);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
    
    // Real-time search with debouncing
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (this.value.trim().length >= 2) {
                performSearch();
            } else if (this.value.trim().length === 0) {
                hideAllStates();
                showState('noSearch');
            }
        }, 500); // 500ms debounce
    });
    
    // Filter change events
    [statusFilter, expiryFilter].forEach(filter => {
        filter.addEventListener('change', function() {
            if (searchInput.value.trim().length >= 2) {
                performSearch();
            }
        });
    });
    
    // Focus search input when clicking on search area
    document.querySelector('.max-w-2xl').addEventListener('click', function() {
        searchInput.focus();
    });
});
</script>
@endsection