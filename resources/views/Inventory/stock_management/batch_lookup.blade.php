@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & SEARCH --}}
    <div class="bg-white border border-border-soft rounded-2xl shadow-sm p-10 text-center relative overflow-hidden">
        {{-- Decorative Elements --}}
        <div class="absolute top-0 left-0 w-32 h-32 bg-cream-bg rounded-br-full -ml-10 -mt-10 z-0"></div>
        <div class="absolute bottom-0 right-0 w-24 h-24 bg-caramel/10 rounded-tl-full -mr-5 -mb-5 z-0"></div>

        <div class="relative z-10 max-w-3xl mx-auto">
            <h1 class="font-display text-4xl font-bold text-chocolate mb-3">Batch Locator</h1>
            <p class="text-gray-500 mb-8 max-w-lg mx-auto">Locate inventory batches instantly. Search by batch number, item name, or scan a barcode.</p>
            
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <i class="fas fa-search text-caramel text-xl group-focus-within:text-chocolate transition-colors"></i>
                </div>
                <input type="text" 
                       id="batchSearchInput" 
                       class="block w-full pl-14 pr-36 py-5 border-2 border-border-soft bg-cream-bg/50 rounded-full shadow-inner focus:outline-none focus:ring-0 focus:border-caramel/50 focus:bg-white transition-all text-lg placeholder-gray-400 font-medium" 
                       placeholder="Scan Barcode or Type Item Name / Batch #..." 
                       autofocus 
                       autocomplete="off">
                <button id="searchBtn" 
                        class="absolute inset-y-2 right-2 px-8 bg-chocolate text-white font-bold rounded-full hover:bg-chocolate-dark shadow-md transition-all transform hover:scale-105 active:scale-95">
                    Search
                </button>
            </div>

            {{-- Search Filters --}}
            <div class="mt-6 flex justify-center gap-4 flex-wrap">
                <div class="relative">
                    <select id="statusFilter" class="appearance-none pl-4 pr-10 py-2.5 bg-white border border-border-soft rounded-xl text-sm font-medium text-gray-600 focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel cursor-pointer shadow-sm">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="quarantine">Quarantine</option>
                        <option value="expired">Expired</option>
                        <option value="consumed">Consumed</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                
                <div class="relative">
                    <select id="expiryFilter" class="appearance-none pl-4 pr-10 py-2.5 bg-white border border-border-soft rounded-xl text-sm font-medium text-gray-600 focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel cursor-pointer shadow-sm">
                        <option value="all">All Items</option>
                        <option value="active">Not Expired</option>
                        <option value="expiring_soon">Expiring Soon (≤7 days)</option>
                        <option value="expired">Expired</option>
                        <option value="no_expiry">No Expiry Date</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SEARCH RESULTS CONTAINER --}}
    <div id="searchResultsContainer" class="min-h-[200px]">
        {{-- Loading Spinner --}}
        <div id="loadingSpinner" class="hidden flex flex-col items-center justify-center py-16">
            <div class="animate-spin rounded-full h-12 w-12 border-[3px] border-border-soft border-t-chocolate mb-4"></div>
            <p class="text-sm font-bold text-chocolate uppercase tracking-widest">Searching Warehouse...</p>
        </div>

        {{-- No Search State (Initial Load) --}}
        <div id="noSearchState" class="text-center py-16 border-2 border-dashed border-border-soft rounded-2xl bg-gray-50/50">
            <div class="w-20 h-20 mx-auto mb-6 bg-white rounded-full flex items-center justify-center shadow-sm border border-border-soft">
                <i class="fas fa-search text-3xl text-caramel/50"></i>
            </div>
            <h3 class="font-display text-xl font-bold text-gray-900 mb-2">Ready to Search</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto">Enter a keyword above to locate specific inventory batches across all warehouse locations.</p>
        </div>

        {{-- No Results State --}}
        <div id="noResultsState" class="hidden text-center py-16 border-2 border-dashed border-border-soft rounded-2xl bg-white">
            <div class="w-20 h-20 mx-auto mb-6 bg-cream-bg rounded-full flex items-center justify-center shadow-inner">
                <i class="fas fa-box-open text-3xl text-chocolate/40"></i>
            </div>
            <h3 class="font-display text-xl font-bold text-chocolate mb-2">No Batches Found</h3>
            <p class="text-gray-500 text-sm">We couldn't find any batches matching your criteria. Try adjusting your filters.</p>
        </div>

        {{-- Error State --}}
        <div id="errorState" class="hidden text-center py-16 border-2 border-red-100 rounded-2xl bg-red-50/30">
            <div class="w-20 h-20 mx-auto mb-6 bg-red-50 rounded-full flex items-center justify-center border border-red-100">
                <i class="fas fa-exclamation-triangle text-3xl text-red-400"></i>
            </div>
            <h3 class="font-display text-xl font-bold text-red-800 mb-2">Search Error</h3>
            <p id="errorMessage" class="text-red-600 text-sm">An error occurred while searching. Please try again.</p>
        </div>

        {{-- Search Results Grid --}}
        <div id="searchResultsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6 hidden">
            {{-- Results will be populated here by JavaScript --}}
        </div>
    </div>

    {{-- 3. INITIAL RECENT BATCHES (if any) --}}
    @if(isset($recentBatches) && $recentBatches->count() > 0)
    <div class="pt-8 border-t border-border-soft">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-8 h-8 bg-chocolate rounded-lg flex items-center justify-center text-white shadow-sm">
                <i class="fas fa-history text-sm"></i>
            </div>
            <h2 class="font-display text-xl font-bold text-chocolate">Recently Added Batches</h2>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($recentBatches as $batch)
                @php
                    $now = \Carbon\Carbon::now();
                    $expiryDate = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date) : null;
                    $expiryDays = $expiryDate ? $now->diffInDays($expiryDate, false) : null;
                    $isExpired = $expiryDays !== null && $expiryDays < 0;
                    $isExpiringSoon = !$isExpired && $expiryDays !== null && $expiryDays <= 7;
                    
                    // Determine border color based on status and expiry
                    $borderColor = 'border-l-green-500';
                    if ($isExpired) {
                        $borderColor = 'border-l-red-500';
                    } elseif ($isExpiringSoon) {
                        $borderColor = 'border-l-amber-500';
                    } elseif ($batch->status === 'quarantine') {
                        $borderColor = 'border-l-amber-500';
                    }
                    
                    // Get icon based on item type
                    $itemType = $batch->item->item_type ?? 'supply';
                    $iconMap = [
                        'raw_material' => ['class' => 'fas fa-wheat', 'bg' => 'bg-amber-50', 'color' => 'text-amber-700'],
                        'finished_good' => ['class' => 'fas fa-bread-slice', 'bg' => 'bg-orange-50', 'color' => 'text-orange-700'],
                        'semi_finished' => ['class' => 'fas fa-cookie-bite', 'bg' => 'bg-chocolate/10', 'color' => 'text-chocolate'],
                        'supply' => ['class' => 'fas fa-box', 'bg' => 'bg-blue-50', 'color' => 'text-blue-700'],
                    ];
                    $icon = $iconMap[$itemType] ?? $iconMap['supply'];
                    
                    // Status badge
                    $statusBadge = match($batch->status) {
                        'active' => ['class' => 'bg-green-100 text-green-800 border-green-200', 'text' => 'Active'],
                        'quarantine' => ['class' => 'bg-amber-100 text-amber-800 border-amber-200', 'text' => 'Quarantine'],
                        'expired' => ['class' => 'bg-red-100 text-red-800 border-red-200', 'text' => 'Expired'],
                        'consumed' => ['class' => 'bg-gray-100 text-gray-800 border-gray-200', 'text' => 'Consumed'],
                        default => ['class' => 'bg-gray-100 text-gray-800 border-gray-200', 'text' => ucfirst($batch->status)]
                    };
                    
                    if ($isExpiringSoon && $batch->status === 'active') {
                        $statusBadge = ['class' => 'bg-red-50 text-red-700 border-red-200 animate-pulse', 'text' => 'Expiring Soon'];
                    }
                    
                    // Expiry Display
                    $expiryTextClass = 'text-gray-800';
                    $expiryLabel = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') : 'No Expiry';
                    
                    if ($isExpired) {
                        $expiryTextClass = 'text-red-600 font-bold';
                        $expiryLabel .= ' (Expired)';
                    } elseif ($isExpiringSoon) {
                        $expiryTextClass = 'text-amber-600 font-bold';
                        $expiryLabel .= " ({$expiryDays} days)";
                    }
                @endphp
                
                <div class="bg-white border-l-4 {{ $borderColor }} border-y border-r border-border-soft rounded-xl shadow-sm hover:shadow-md transition-all p-6 group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 {{ $icon['bg'] }} rounded-xl flex items-center justify-center {{ $icon['color'] }} text-lg shadow-sm border border-white">
                                <i class="{{ $icon['class'] }}"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-chocolate group-hover:text-caramel transition-colors">{{ $batch->item->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">SKU: {{ $batch->item->item_code }}</p>
                            </div>
                        </div>
                        <span class="{{ $statusBadge['class'] }} text-[10px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide">
                            {{ $statusBadge['text'] }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] text-caramel uppercase font-bold tracking-widest">Batch Number</p>
                            <p class="font-mono text-gray-800 font-bold mt-1">{{ $batch->batch_number }}</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Expiry Date</p>
                            <p class="{{ $expiryTextClass }} mt-1 text-xs">{{ $expiryLabel }}</p>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] text-blue-600 uppercase font-bold tracking-widest">Location</p>
                            <p class="text-sm text-blue-900 font-bold mt-0.5 flex items-center gap-1">
                                <i class="fas fa-map-marker-alt"></i> {{ $batch->location ?? 'Main Storage' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-blue-600 uppercase font-bold tracking-widest">Qty Available</p>
                            <p class="text-lg text-blue-900 font-bold mt-0.5">{{ number_format($batch->quantity, 2) }} <span class="text-xs font-normal">{{ $batch->item->unit->symbol ?? 'pcs' }}</span></p>
                        </div>
                    </div>
                    
                    @if($batch->supplier)
                    <div class="mt-4 pt-3 border-t border-border-soft flex justify-between items-center">
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-truck mr-1.5 text-gray-400"></i> {{ $batch->supplier->name }}
                        </div>
                        <div class="text-[10px] text-gray-400">
                            Added {{ $batch->created_at->diffForHumans() }}
                        </div>
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
    
    // Create batch element (Updated with New Design System classes)
    function createBatchElement(batch) {
        const div = document.createElement('div');
        div.className = `bg-white border-l-4 ${batch.priority_color} border-y border-r border-border-soft rounded-xl shadow-sm hover:shadow-md transition p-6 group`;
        
        // Format expiry date display
        let expiryDisplay = batch.expiry_date;
        let expiryClass = 'text-gray-800';
        
        if (batch.is_expired) {
            expiryClass = 'text-red-600 font-bold';
            expiryDisplay += ' (Expired)';
        } else if (batch.is_expiring_soon) {
            expiryClass = 'text-amber-600 font-bold';
            expiryDisplay += ` (${batch.expiry_days} days)`;
        }
        
        if (!batch.expiry_date || batch.expiry_date === 'No Expiry') {
            expiryDisplay = 'No Expiry';
            expiryClass = 'text-gray-500 italic';
        }
        
        // Use JS template literal for dynamic HTML structure matching the blade design
        div.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 ${batch.icon.bg} rounded-xl flex items-center justify-center ${batch.icon.color} text-lg shadow-sm border border-white">
                        <i class="${batch.icon.class}"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-chocolate group-hover:text-caramel transition-colors">${batch.item.name}</h3>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">SKU: ${batch.item.item_code}</p>
                    </div>
                </div>
                <span class="${batch.status_badge.class} text-[10px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide">
                    ${batch.status_badge.text}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                    <p class="text-[10px] text-caramel uppercase font-bold tracking-widest">Batch Number</p>
                    <p class="font-mono text-gray-800 font-bold mt-1">${batch.batch_number}</p>
                </div>
                <div class="bg-white p-3 rounded-lg border border-border-soft">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Expiry Date</p>
                    <p class="${expiryClass} text-xs mt-1">${expiryDisplay}</p>
                </div>
            </div>
            
            <div class="bg-blue-50/50 p-4 rounded-lg border border-blue-100 flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-blue-600 uppercase font-bold tracking-widest">Location</p>
                    <p class="text-sm text-blue-900 font-bold mt-0.5 flex items-center gap-1">
                        <i class="fas fa-map-marker-alt"></i>
                        ${batch.location}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-blue-600 uppercase font-bold tracking-widest">Qty Here</p>
                    <p class="text-lg text-blue-900 font-bold mt-0.5">${batch.quantity.toFixed(2)} <span class="text-xs font-normal">${batch.item.unit.symbol}</span></p>
                </div>
            </div>
            
            ${batch.supplier.name !== 'N/A' ? `
            <div class="mt-4 pt-3 border-t border-border-soft flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <i class="fas fa-truck mr-1.5 text-gray-400"></i> ${batch.supplier.name}
                </div>
                <div class="text-[10px] text-gray-400">
                    Added: ${batch.created_at}
                </div>
            </div>
            ` : ''}
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
    
    // Focus search input when clicking on search area (Visual enhancement)
    document.querySelector('.max-w-3xl').addEventListener('click', function(e) {
        // Only focus if not clicking on the select inputs
        if(e.target.tagName !== 'SELECT' && e.target.tagName !== 'OPTION') {
             searchInput.focus();
        }
    });
});
</script>
@endsection