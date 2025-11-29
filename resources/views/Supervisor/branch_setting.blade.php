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



    {{-- 4. MAIN CONTENT --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        <!-- Filters Toolbar -->
        <form id="filtersForm" method="GET" action="{{ route('supervisor.settings.stock-levels') }}" class="p-5 border-b border-border-soft bg-white grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
            <div class="md:col-span-5 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" id="searchInput" name="search"
                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                       placeholder="Search items by name or SKU..." value="{{ request('search') }}">
            </div>
            
            <div class="md:col-span-3 relative">
                <select id="categoryFilter" name="category" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all appearance-none cursor-pointer">
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
                <select id="statusFilter" name="status" class="block w-full pl-3 pr-10 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all appearance-none cursor-pointer">
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
                <button type="submit" class="w-full h-full bg-white border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 hover:text-chocolate transition-colors shadow-sm flex items-center justify-center" title="Refresh Results">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </form>

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

@push('scripts')
<script>
$(document).ready(function() {
    console.log('📄 Document ready - Branch Settings App Starting...');
    
    // Debug jQuery and DOM
    console.log('🔍 jQuery loaded:', typeof $ !== 'undefined');
    console.log('🔍 Document loaded:', $(document).length);
    console.log('🔍 Save button exists:', $('#saveAllChanges').length);
    console.log('🔍 Toast element exists:', $('#toast').length);
    console.log('🔍 Loading modal exists:', $('#loadingModal').length);
    
    let pendingChanges = new Map();

    // --- Initialize original values ---
    const inputCount = $('.stock-level-input').length;
    console.log(`🔍 Found ${inputCount} stock level input fields`);
    
    if (inputCount === 0) {
        console.error('❌ No stock level input fields found! This might be a DOM loading issue.');
        return;
    }
    
    $('.stock-level-input').each(function() {
        const $input = $(this);
        const originalValue = parseFloat($input.attr('value')) || 0;
        $input.attr('data-original-value', originalValue);
        console.log('✅ Initialized item', $input.data('item-id'), 'field', $input.data('field'), 'with value', originalValue);
    });

    // --- Change Tracking ---
    $('.stock-level-input').on('input change', function() {
        const $input = $(this);
        const itemId = $input.data('item-id');
        const field = $input.data('field');
        const value = parseFloat($input.val()) || 0;
        const originalValue = parseFloat($input.data('original-value')) || $input.attr('data-original-value') || 0;
        
        // Update the original value attribute for future reference
        if (!$input.data('original-value')) {
            $input.attr('data-original-value', originalValue);
        }
        
        console.log('Input changed:', { itemId, field, value, originalValue });
        
        if (!pendingChanges.has(itemId)) {
            pendingChanges.set(itemId, {});
        }
        
        // Only track changes if the value is actually different from original
        if (Math.abs(value - originalValue) > 0.001) {
            pendingChanges.get(itemId)[field] = value;
            $input.addClass('bg-amber-50 border-amber-300 text-amber-800');
            console.log('Change tracked for item', itemId, field, ':', value);
        } else {
            // Remove the field from pending changes if it matches original
            if (pendingChanges.get(itemId)[field] !== undefined) {
                delete pendingChanges.get(itemId)[field];
                $input.removeClass('bg-amber-50 border-amber-300 text-amber-800');
                console.log('Change removed for item', itemId, field);
            }
        }
        
        // Clean up empty items from pendingChanges
        const itemChanges = pendingChanges.get(itemId);
        if (Object.keys(itemChanges).length === 0) {
            pendingChanges.delete(itemId);
            console.log('Removed item', itemId, 'from pending changes (no changes)');
        }
        
        console.log('Total pending changes:', pendingChanges.size);
    });

    // --- Save Action ---
    $('#saveAllChanges').on('click', function(e) {
        e.preventDefault();
        console.log('🔘 Save button clicked. Pending changes:', pendingChanges.size);
        
        if (pendingChanges.size === 0) {
            console.log('⚠️ No changes to save');
            showToast('No changes to save.', 'warning');
            return;
        }
        
        // Debug: Log all pending changes
        console.log('📋 Pending changes:', Object.fromEntries(pendingChanges));
        pendingChanges.forEach((changes, itemId) => {
            console.log(`📝 Item ${itemId}:`, changes);
        });
        
        // Disable the button to prevent double-clicks
        const $button = $(this);
        console.log('🔒 Disabling button...');
        $button.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        
        console.log('🚀 Starting updateAllItems()...');
        updateAllItems().finally(() => {
            console.log('✅ Update completed, re-enabling button...');
            // Re-enable the button after update completes
            $button.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        });
    });

    // --- Filters ---
    const $filtersForm = $('#filtersForm');
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(submitFilters, 500);
    });
    $('#searchInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitFilters();
        }
    });
    $('#categoryFilter, #statusFilter').on('change', submitFilters);



    // --- Functions ---

   // Make the function async to allow 'await'
    async function updateAllItems() {
        console.log('🚀 Starting sequential update for', pendingChanges.size, 'items');
        console.log('🔄 Converting Map to array for iteration...');
        
        showLoading(true);
        
        // Disable button visually
        const $btn = $('#saveAllChanges');
        console.log('🔒 Disabling save button...');
        $btn.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);

        let successCount = 0;
        let failCount = 0;
        
        console.log('🔄 Starting iteration through pending changes...');
        // Convert Map to array to iterate cleanly
        // We use a restricted loop to send requests ONE BY ONE
        for (const [itemId, changes] of pendingChanges) {
            console.log(`🔄 Processing item ${itemId}:`, changes);
            
            try {
                // 1. Prepare Data
                const updateData = {
                    item_id: itemId,
                    _token: '{{ csrf_token() }}',
                    ...changes // Spread the changes (min_stock_level, etc.)
                };
                
                console.log(`📤 Sending AJAX request for item ${itemId}...`, updateData);

                // 2. Send Request and AWAIT the result (Pauses loop until done)
                const response = await $.ajax({
                    url: '{{ route("supervisor.settings.stock-levels.update") }}',
                    method: 'POST',
                    data: updateData
                });
                
                console.log(`✅ Item ${itemId} updated successfully! Response:`, response);

                // 3. On Success: Update UI
                successCount++;
                console.log(`🎉 Item ${itemId} updated successfully`);
                
                // Update the "original value" on the inputs so they don't look "changed" anymore
                Object.keys(changes).forEach(field => {
                    const $input = $(`.stock-level-input[data-item-id="${itemId}"][data-field="${field}"]`);
                    console.log(`🎨 Updating input visual state for item ${itemId}, field ${field}...`);
                    // Update both .data and .attr to be safe
                    $input.data('original-value', changes[field]); 
                    $input.attr('data-original-value', changes[field]);
                    $input.removeClass('bg-amber-50 border-amber-300 text-amber-800');
                });
                
                // Remove from pending map
                pendingChanges.delete(itemId);
                console.log(`🗑️ Removed item ${itemId} from pending changes`);

            } catch (error) {
                failCount++;
                console.error(`❌ Failed to update item ${itemId}:`, error);
                // We leave it in 'pendingChanges' and keep the amber color 
                // so the user knows it failed and can try again.
            }
        }

        // 4. Final Cleanup
        console.log('🧹 Final cleanup...');
        showLoading(false);
        $btn.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);

        console.log('📊 Final results:', { successCount, failCount });
        
        if (failCount === 0 && successCount > 0) {
            console.log('🎊 Showing success toast...');
            showToast('All items updated successfully!');
            // Optional: Reload to ensure fresh data, or just stay on page
            // setTimeout(() => location.reload(), 1000); 
        } else if (successCount > 0 && failCount > 0) {
            console.log('⚠️ Showing partial success toast...');
            showToast(`${successCount} saved, but ${failCount} failed. Check console.`, 'warning');
        } else if (failCount > 0) {
            console.log('❌ Showing error toast...');
            showToast('Update failed. Please check values and try again.', 'error');
        } else {
            console.log('ℹ️ Showing no changes toast...');
            showToast('No changes were saved.');
        }
        
        console.log('🏁 updateAllItems() completed!');
    }



    function submitFilters() {
        if ($filtersForm.length) {
            $filtersForm.submit();
        } else {
            window.location.href = "{{ route('supervisor.settings.stock-levels') }}";
        }
    }

    function showLoading(show) {
        console.log('🎭 showLoading called with:', show);
        const el = $('#loadingModal');
        if (el.length === 0) {
            console.error('❌ Loading modal element not found!');
            return;
        }
        
        if (show) {
            console.log('📱 Showing loading modal...');
            el.removeClass('hidden').addClass('flex');
        } else {
            console.log('📱 Hiding loading modal...');
            el.addClass('hidden').removeClass('flex');
        }
    }

    function showToast(msg, type = 'success') {
        console.log('🍞 showToast called with:', { msg, type });
        const toast = $('#toast');
        const icon = toast.find('i');
        
        if (toast.length === 0) {
            console.error('❌ Toast element not found!');
            return;
        }
        
        if (icon.length === 0) {
            console.error('❌ Toast icon element not found!');
            return;
        }
        
        console.log('📝 Setting toast message:', msg);
        $('#toastMessage').text(msg);
        
        icon.attr('class', ''); // reset
        if(type === 'success') icon.addClass('fas fa-check-circle text-green-400');
        else if(type === 'error') icon.addClass('fas fa-times-circle text-red-400');
        else icon.addClass('fas fa-exclamation-circle text-yellow-400');

        console.log('🍞 Showing toast...');
        toast.removeClass('hidden translate-y-full opacity-0');
        setTimeout(() => {
            console.log('🍞 Hiding toast...');
            toast.addClass('translate-y-full opacity-0');
            setTimeout(() => toast.addClass('hidden'), 300);
        }, 3000);
    }
    
    // --- Debug Functions (accessible from console) ---
    window.debugStockSettings = function() {
        console.log('=== Stock Settings Debug Info ===');
        console.log('Pending changes:', pendingChanges.size);
        pendingChanges.forEach((changes, itemId) => {
            console.log(`Item ${itemId}:`, changes);
        });
        
        console.log('Input fields with data attributes:');
        $('.stock-level-input').each(function() {
            const $input = $(this);
            console.log({
                itemId: $input.data('item-id'),
                field: $input.data('field'),
                value: $input.val(),
                originalValue: $input.data('original-value') || $input.attr('data-original-value'),
                hasChanges: $input.hasClass('bg-amber-50')
            });
        });
        
        console.log('CSRF Token:', '{{ csrf_token() }}');
        console.log('Update route:', '{{ route("supervisor.settings.stock-levels.update") }}');
        
        return {
            pendingChanges: Object.fromEntries(pendingChanges),
            inputCount: $('.stock-level-input').length,
            csrfToken: '{{ csrf_token() }}',
            updateRoute: '{{ route("supervisor.settings.stock-levels.update") }}'
        };
    };
    
    // Clear all pending changes (for testing)
    window.clearPendingChanges = function() {
        pendingChanges.clear();
        $('.stock-level-input').each(function() {
            const $input = $(this);
            const originalValue = parseFloat($input.attr('value')) || 0;
            $input.attr('data-original-value', originalValue);
            $input.removeClass('bg-amber-50 border-amber-300 text-amber-800');
        });
        console.log('All pending changes cleared');
    };
});
</script>
@endpush