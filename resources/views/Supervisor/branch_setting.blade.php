@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600 pb-24" id="stock-settings-app">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Minimum Stock Configuration</h1>
            <p class="text-sm text-gray-500">Configure thresholds to automate low-stock alerts and reordering.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="saveAllChanges" class="inline-flex items-center justify-center px-6 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:scale-95">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- 2. METRICS OVERVIEW --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-white p-4 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between group hover:border-chocolate/30 transition-all">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Total Items</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-display font-bold text-gray-900">{{ number_format($metrics['total_items']) }}</div>
                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                    <i class="fas fa-cubes text-sm"></i>
                </div>
            </div>
        </div>
        
        <!-- Healthy -->
        <div class="bg-white p-4 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between group hover:border-green-200 transition-all">
            <div class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-2">Healthy</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-display font-bold text-green-700">{{ number_format($metrics['healthy_stock']) }}</div>
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                    <i class="fas fa-check-circle text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white p-4 rounded-xl border border-border-soft border-b-4 border-b-amber-400 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-2">Low Stock</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-display font-bold text-amber-700">{{ number_format($metrics['low_stock']) }}</div>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-exclamation-triangle text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Critical -->
        <div class="bg-white p-4 rounded-xl border border-border-soft border-b-4 border-b-orange-500 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="text-[10px] font-bold text-orange-600 uppercase tracking-widest mb-2">Critical</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-display font-bold text-orange-700">{{ number_format($metrics['critical_stock']) }}</div>
                <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                    <i class="fas fa-radiation text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white p-4 rounded-xl border border-border-soft border-b-4 border-b-red-600 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="text-[10px] font-bold text-red-600 uppercase tracking-widest mb-2">Stockout</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-display font-bold text-red-700">{{ number_format($metrics['out_of_stock']) }}</div>
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
                    <i class="fas fa-times-circle text-sm"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SEASONAL BUFFER WIDGET --}}
    <div class="bg-white border border-border-soft rounded-2xl p-6 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50/50 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg inline-flex w-fit">
                    <i class="fas fa-magic text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-display font-bold text-chocolate">Seasonal Buffer Tool</h3>
                    <p class="text-xs text-gray-500">Automatically increase thresholds for high-demand periods.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-cream-bg/50 p-5 rounded-xl border border-border-soft">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">Category Target</label>
                    <div class="relative">
                        <select id="seasonalCategory" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="">Select Category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">Settings to Update</label>
                    <div class="relative">
                        <select id="adjustmentType" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                            <option value="both">Min Level & Reorder Point</option>
                            <option value="min_stock_level">Minimum Level Only</option>
                            <option value="reorder_point">Reorder Point Only</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">Increase By (%)</label>
                    <div class="relative">
                        <input type="number" id="adjustmentPercentage" class="block w-full pl-3 pr-8 py-2.5 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" placeholder="20" min="1" max="500">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-xs font-bold">%</span>
                        </div>
                    </div>
                </div>
                
                <div class="md:col-span-3">
                    <button id="applyAdjustment" class="w-full py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-bolt text-xs"></i> Apply Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MAIN CONTENT --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        <!-- Filters Toolbar -->
        <div class="p-5 border-b border-border-soft bg-white grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
            <div class="md:col-span-5 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" id="searchInput" 
                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                       placeholder="Search items by name or SKU..." value="{{ request('search') }}">
            </div>
            
            <div class="md:col-span-3 relative">
                <select id="categoryFilter" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all appearance-none cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            <div class="md:col-span-3 relative">
                <select id="statusFilter" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all appearance-none cursor-pointer">
                    <option value="">All Status Levels</option>
                    <option value="healthy" {{ request('status') == 'healthy' ? 'selected' : '' }}>Healthy Stock</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            <div class="md:col-span-1 flex justify-end">
                <button onclick="applyFilters()" class="w-full h-full bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-chocolate transition-colors shadow-sm flex items-center justify-center" title="Refresh Results">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display w-1/3">Item Details</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Current</th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-red-600 bg-red-50/50 uppercase tracking-wider w-32 border-l border-r border-red-100">
                            <i class="fas fa-arrow-down mr-1"></i> Min Level
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-amber-600 bg-amber-50/50 uppercase tracking-wider w-32 border-r border-amber-100">
                            <i class="fas fa-sync mr-1"></i> Reorder
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-green-600 bg-green-50/50 uppercase tracking-wider w-32 border-r border-green-100">
                            <i class="fas fa-arrow-up mr-1"></i> Max Level
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Health</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($stockItems as $item)
                        @php
                            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                            $reorderPoint = $item->reorder_point ?? 0;
                            $minStockLevel = $item->min_stock_level ?? 0;
                            $maxStockLevel = $item->max_stock_level ?? 0;

                            // Status Logic
                            $statusText = 'Healthy';
                            $statusClass = 'text-green-700 bg-green-50 border-green-100';
                            $barColor = 'bg-green-500';
                            $icon = 'fa-check-circle';

                            if ($currentStock <= 0) {
                                $statusText = 'Out of Stock';
                                $statusClass = 'text-red-700 bg-red-50 border-red-100';
                                $barColor = 'bg-red-600';
                                $icon = 'fa-times-circle';
                            } elseif ($currentStock <= $minStockLevel) {
                                $statusText = 'Critical';
                                $statusClass = 'text-red-700 bg-red-50 border-red-100';
                                $barColor = 'bg-red-500';
                                $icon = 'fa-radiation';
                            } elseif ($currentStock <= $reorderPoint) {
                                $statusText = 'Reorder';
                                $statusClass = 'text-amber-700 bg-amber-50 border-amber-100';
                                $barColor = 'bg-amber-500';
                                $icon = 'fa-exclamation-triangle';
                            }

                            $percentage = $maxStockLevel > 0 ? min(100, round(($currentStock / $maxStockLevel) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-cream-bg/30 transition-colors group">
                            <!-- Item Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-white border border-border-soft flex items-center justify-center text-chocolate shadow-sm">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-chocolate transition-colors">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $item->item_code }}</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 mt-1">
                                            {{ $item->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Current Stock -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($currentStock, 1) }}</span>
                                <span class="text-xs text-gray-500 block">{{ $item->unit->symbol ?? 'units' }}</span>
                            </td>

                            <!-- Min Level Input -->
                            <td class="px-2 py-3 bg-red-50/10 border-l border-r border-gray-100">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-lg py-2 text-sm focus:ring-2 focus:ring-chocolate focus:border-chocolate bg-transparent hover:bg-white transition-all"
                                        value="{{ $minStockLevel }}"
                                        data-item-id="{{ $item->id }}"
                                        data-field="min_stock_level">
                                </div>
                            </td>

                            <!-- Reorder Point Input -->
                            <td class="px-2 py-3 bg-amber-50/10 border-r border-gray-100">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-lg py-2 text-sm focus:ring-2 focus:ring-chocolate focus:border-chocolate bg-transparent hover:bg-white transition-all"
                                        value="{{ $reorderPoint }}"
                                        data-item-id="{{ $item->id }}"
                                        data-field="reorder_point">
                                </div>
                            </td>

                            <!-- Max Level Input -->
                            <td class="px-2 py-3 bg-green-50/10 border-r border-gray-100">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0"
                                        class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-lg py-2 text-sm focus:ring-2 focus:ring-chocolate focus:border-chocolate bg-transparent hover:bg-white transition-all"
                                        value="{{ $maxStockLevel }}"
                                        data-item-id="{{ $item->id }}"
                                        data-field="max_stock_level">
                                </div>
                            </td>

                            <!-- Health Status -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $statusClass }}">
                                        <i class="fas {{ $icon }}"></i> {{ $statusText }}
                                    </span>
                                    <span class="text-xs font-mono text-gray-400">{{ $percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                                        <i class="fas fa-search text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate">No Items Found</h3>
                                    <p class="text-sm text-gray-500 mt-1">Try adjusting your filters to see more results.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($stockItems->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                {{ $stockItems->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Floating Success Toast -->
<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-gray-900 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-white/10 backdrop-blur-md">
        <i class="fas fa-check-circle text-green-400"></i>
        <span id="toastMessage" class="font-medium text-sm">Changes Saved</span>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingModal" class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm hidden z-50 flex items-center justify-center transition-opacity duration-300">
    <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center border border-border-soft transform scale-100">
        <div class="animate-spin rounded-full h-10 w-10 border-[3px] border-border-soft border-t-chocolate mb-4"></div>
        <span class="text-chocolate font-bold text-sm uppercase tracking-widest">Processing...</span>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let pendingChanges = new Map();

    // --- Change Tracking ---
    $('.stock-level-input').on('input', function() {
        $(this).addClass('bg-amber-50 border-amber-300 text-amber-800');
        
        const itemId = $(this).data('item-id');
        const field = $(this).data('field');
        const value = parseFloat($(this).val()) || 0;
        
        if (!pendingChanges.has(itemId)) {
            pendingChanges.set(itemId, {});
        }
        pendingChanges.get(itemId)[field] = value;
    });

    // --- Save Action ---
    $('#saveAllChanges').on('click', function() {
        if (pendingChanges.size === 0) {
            showToast('No changes to save.', 'warning');
            return;
        }
        updateAllItems();
    });

    // --- Filters ---
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
    $('#categoryFilter, #statusFilter').on('change', applyFilters);

    // --- Seasonal Tool ---
    $('#applyAdjustment').on('click', applySeasonalAdjustment);

    // --- Functions ---

    function updateAllItems() {
        showLoading(true);
        
        const promises = [];
        
        pendingChanges.forEach((changes, itemId) => {
            // Create base data object
            const updateData = {
                item_id: itemId,
                _token: '{{ csrf_token() }}'
            };
            
            // Explicitly check and add each field if it exists in changes
            if (changes.hasOwnProperty('min_stock_level')) {
                updateData.min_stock_level = changes.min_stock_level;
            }
            if (changes.hasOwnProperty('reorder_point')) {
                updateData.reorder_point = changes.reorder_point;
            }
            if (changes.hasOwnProperty('max_stock_level')) {
                updateData.max_stock_level = changes.max_stock_level;
            }
            
            // Push the AJAX request to the promises array
            promises.push(
                $.ajax({
                    url: '{{ route("supervisor.settings.stock-levels.update") }}',
                    method: 'POST',
                    data: updateData
                })
            );
        });

        Promise.all(promises)
            .then(() => {
                showToast('All stock levels updated successfully!');
                setTimeout(() => location.reload(), 800);
            })
            .catch(() => {
                showToast('Some updates failed. Please check inputs.', 'error');
                showLoading(false);
            });
    }

    function applySeasonalAdjustment() {
        const categoryId = $('#seasonalCategory').val();
        const adjustmentPercentage = parseFloat($('#adjustmentPercentage').val());
        const adjustmentType = $('#adjustmentType').val();

        if (!categoryId) { showToast('Select a category first.', 'warning'); return; }
        if (!adjustmentPercentage || adjustmentPercentage <= 0) { 
            showToast('Enter a valid percentage (1-500).', 'warning'); 
            $('#adjustmentPercentage').focus();
            return; 
        }
        if (adjustmentPercentage > 500) {
            showToast('Percentage cannot exceed 500%.', 'warning');
            $('#adjustmentPercentage').focus();
            return;
        }

        showLoading(true);
        $.ajax({
            url: '{{ route("supervisor.settings.stock-levels.seasonal-adjustment") }}',
            method: 'POST',
            data: {
                category_id: categoryId,
                adjustment_percentage: adjustmentPercentage,
                adjustment_type: adjustmentType,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if(res.success) {
                    const message = res.data ? 
                        `Seasonal adjustment applied to ${res.data.updated_count} items in ${res.data.category_name}!` : 
                        'Seasonal adjustment applied!';
                    showToast(message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.error || 'Failed to apply adjustment', 'error');
                    showLoading(false);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to apply adjustment.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showToast(errorMessage, 'error');
                showLoading(false);
            }
        });
    }

    function applyFilters() {
        const search = $('#searchInput').val();
        const category = $('#categoryFilter').val();
        const status = $('#statusFilter').val();
        const params = new URLSearchParams();
        if(search) params.set('search', search);
        if(category) params.set('category', category);
        if(status) params.set('status', status);
        window.location.href = `{{ route('supervisor.settings.stock-levels') }}?${params.toString()}`;
    }

    function showLoading(show) {
        const el = $('#loadingModal');
        show ? el.removeClass('hidden').addClass('flex') : el.addClass('hidden').removeClass('flex');
    }

    function showToast(msg, type = 'success') {
        const toast = $('#toast');
        const icon = toast.find('i');
        
        $('#toastMessage').text(msg);
        
        icon.attr('class', ''); // reset
        if(type === 'success') icon.addClass('fas fa-check-circle text-green-400');
        else if(type === 'error') icon.addClass('fas fa-times-circle text-red-400');
        else icon.addClass('fas fa-exclamation-circle text-yellow-400');

        toast.removeClass('hidden translate-y-full opacity-0');
        setTimeout(() => {
            toast.addClass('translate-y-full opacity-0');
            setTimeout(() => toast.addClass('hidden'), 300);
        }, 3000);
    }
});
</script>
@endsection