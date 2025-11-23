@extends('Supervisor.layout.app')

@section('content')
{{-- Custom Styles --}}
<style>
    /* Brand Colors */
    .text-chocolate { color: #d2691e; }
    .bg-chocolate { background-color: #d2691e; }
    .hover\:bg-chocolate:hover { background-color: #d2691e; }
    .hover\:bg-chocolate-dark:hover { background-color: #8b4513; }
    .focus\:ring-chocolate:focus { --tw-ring-color: #d2691e; }
    .border-chocolate { border-color: #d2691e; }

    /* Table Input Styling */
    .table-input {
        transition: all 0.2s;
        border: 1px solid transparent;
        background-color: transparent;
    }
    .table-input:hover {
        background-color: #f9fafb;
        border-color: #e5e7eb;
    }
    .table-input:focus {
        background-color: #fff;
        border-color: #d2691e;
        box-shadow: 0 0 0 2px rgba(210, 105, 30, 0.1);
    }
    .table-input.changed {
        background-color: #fff7ed;
        border-color: #d2691e;
    }

    /* Smooth Animations */
    .fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="space-y-8 max-w-8xl mx-auto pb-10 px-4 sm:px-6 lg:px-8" id="stock-settings-app">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Minimum Stock Configuration</h1>
            <p class="text-sm text-gray-500 mt-2">
                Configure thresholds to automate low-stock alerts and reordering.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button id="saveAllChanges" class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-xl hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform active:scale-95">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- 2. METRICS OVERVIEW --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-24 hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-extrabold text-gray-900">{{ $metrics['total_items'] }}</div>
                <i class="fas fa-cubes text-gray-200 text-xl"></i>
            </div>
        </div>
        
        <!-- Healthy -->
        <div class="bg-green-50 p-4 rounded-xl border border-green-100 shadow-sm flex flex-col justify-between h-24">
            <div class="text-xs font-bold text-green-600 uppercase tracking-wider">Healthy</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-extrabold text-green-700">{{ $metrics['healthy_stock'] }}</div>
                <i class="fas fa-check-circle text-green-200 text-xl"></i>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 shadow-sm flex flex-col justify-between h-24">
            <div class="text-xs font-bold text-yellow-600 uppercase tracking-wider">Low Stock</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-extrabold text-yellow-700">{{ $metrics['low_stock'] }}</div>
                <i class="fas fa-exclamation-triangle text-yellow-200 text-xl"></i>
            </div>
        </div>

        <!-- Critical -->
        <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 shadow-sm flex flex-col justify-between h-24">
            <div class="text-xs font-bold text-orange-600 uppercase tracking-wider">Critical</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-extrabold text-orange-700">{{ $metrics['critical_stock'] }}</div>
                <i class="fas fa-radiation text-orange-200 text-xl"></i>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-red-50 p-4 rounded-xl border border-red-100 shadow-sm flex flex-col justify-between h-24">
            <div class="text-xs font-bold text-red-600 uppercase tracking-wider">Stockout</div>
            <div class="flex justify-between items-end">
                <div class="text-2xl font-extrabold text-red-700">{{ $metrics['out_of_stock'] }}</div>
                <i class="fas fa-times-circle text-red-200 text-xl"></i>
            </div>
        </div>
    </div>

    {{-- 3. SEASONAL BUFFER WIDGET --}}
    <div class="bg-gradient-to-br from-blue-50 via-white to-white border border-blue-100 rounded-2xl p-6 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 opacity-5 pointer-events-none">
            <i class="fas fa-snowflake text-9xl text-blue-900"></i>
        </div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                    <i class="fas fa-magic text-sm"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Seasonal Buffer Tool</h3>
            </div>
            
            <p class="text-sm text-gray-500 mb-5 max-w-3xl">
                Anticipating high demand? Automatically increase Minimum Levels and Reorder Points for a specific category by a percentage factor.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-white p-4 rounded-xl border border-blue-100 shadow-sm">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Category Target</label>
                    <select id="seasonalCategory" class="block w-full rounded-lg border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        <option value="">Select Category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Settings to Update</label>
                    <select id="adjustmentType" class="block w-full rounded-lg border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        <option value="both">Min Level & Reorder Point</option>
                        <option value="min_stock_level">Minimum Level Only</option>
                        <option value="reorder_point">Reorder Point Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Increase By (%)</label>
                    <div class="relative">
                        <input type="number" id="adjustmentPercentage" class="block w-full rounded-lg border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2.5 pl-3 pr-8" placeholder="20" min="1" max="500">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-bold">%</span>
                        </div>
                    </div>
                </div>
                <button id="applyAdjustment" class="w-full py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition shadow-sm">
                    Apply Changes
                </button>
            </div>
        </div>
    </div>

    {{-- 4. MAIN CONTENT --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Filters Toolbar -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" 
                       class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-xl text-sm focus:ring-chocolate focus:border-chocolate shadow-sm" 
                       placeholder="Search items by name or SKU..." value="{{ request('search') }}">
            </div>
            <div class="md:col-span-3">
                <select id="categoryFilter" class="block w-full py-2.5 px-3 border-gray-300 rounded-xl text-sm focus:ring-chocolate focus:border-chocolate shadow-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <select id="statusFilter" class="block w-full py-2.5 px-3 border-gray-300 rounded-xl text-sm focus:ring-chocolate focus:border-chocolate shadow-sm">
                    <option value="">All Status Levels</option>
                    <option value="healthy" {{ request('status') == 'healthy' ? 'selected' : '' }}>Healthy Stock</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="md:col-span-1 flex justify-end">
                <button onclick="applyFilters()" class="w-full h-full bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition shadow-sm flex items-center justify-center" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Item Details</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Current</th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32 text-red-600 bg-red-50/30 border-l border-r border-gray-200">
                            <i class="fas fa-arrow-down mr-1"></i> Min Level
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32 text-amber-600 bg-amber-50/30 border-r border-gray-200">
                            <i class="fas fa-sync mr-1"></i> Reorder
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32 text-green-600 bg-green-50/30 border-r border-gray-200">
                            <i class="fas fa-arrow-up mr-1"></i> Max Level
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Health</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stockItems as $item)
                        @php
                            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                            $reorderPoint = $item->reorder_point ?? 0;
                            $minStockLevel = $item->min_stock_level ?? 0;
                            $maxStockLevel = $item->max_stock_level ?? 0;

                            // Status Logic
                            $statusClass = 'bg-green-100 text-green-800 border-green-200';
                            $statusText = 'Healthy';
                            $barColor = 'bg-green-500';

                            if ($currentStock <= 0) {
                                $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                $statusText = 'Out of Stock';
                                $barColor = 'bg-red-500';
                            } elseif ($currentStock <= $minStockLevel) {
                                $statusClass = 'bg-red-50 text-red-600 border-red-100';
                                $statusText = 'Critical';
                                $barColor = 'bg-red-500';
                            } elseif ($currentStock <= $reorderPoint) {
                                $statusClass = 'bg-yellow-50 text-yellow-700 border-yellow-100';
                                $statusText = 'Reorder';
                                $barColor = 'bg-yellow-500';
                            }

                            $percentage = $maxStockLevel > 0 ? min(100, round(($currentStock / $maxStockLevel) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <!-- Item Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->item_code }} &bull; {{ $item->category->name ?? 'Uncategorized' }}</div>
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
                                <input type="number" step="0.01" min="0"
                                    class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-md py-1.5 text-sm"
                                    value="{{ $minStockLevel }}"
                                    data-item-id="{{ $item->id }}"
                                    data-field="min_stock_level">
                            </td>

                            <!-- Reorder Point Input -->
                            <td class="px-2 py-3 bg-amber-50/10 border-r border-gray-100">
                                <input type="number" step="0.01" min="0"
                                    class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-md py-1.5 text-sm"
                                    value="{{ $reorderPoint }}"
                                    data-item-id="{{ $item->id }}"
                                    data-field="reorder_point">
                            </td>

                            <!-- Max Level Input -->
                            <td class="px-2 py-3 bg-green-50/10 border-r border-gray-100">
                                <input type="number" step="0.01" min="0"
                                    class="stock-level-input table-input w-full text-center font-bold text-gray-700 rounded-md py-1.5 text-sm"
                                    value="{{ $maxStockLevel }}"
                                    data-item-id="{{ $item->id }}"
                                    data-field="max_stock_level">
                            </td>

                            <!-- Status Bar -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-medium {{ str_replace('bg-', 'text-', explode(' ', $statusClass)[0]) }} {{ explode(' ', $statusClass)[1] }}">
                                        {{ $statusText }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="p-3 rounded-full bg-gray-100 mb-3">
                                        <i class="fas fa-search text-gray-400 text-xl"></i>
                                    </div>
                                    <p>No items found matching your filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $stockItems->links() }}
        </div>
    </div>
</div>

<!-- Floating Success Toast -->
<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-gray-900 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-green-400"></i>
        <span id="toastMessage" class="font-medium text-sm">Changes Saved</span>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingModal" class="fixed inset-0 bg-gray-900 bg-opacity-40 hidden z-50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-gray-200 border-t-chocolate mb-3"></div>
        <span class="text-gray-700 font-bold text-sm">Applying seasonal adjustment...</span>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let pendingChanges = new Map();

    // --- Change Tracking ---
    $('.stock-level-input').on('input', function() {
        $(this).addClass('changed');
        
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
        let itemsData = [];
        
        pendingChanges.forEach((changes, itemId) => {
            itemsData.push({
                item_id: itemId,
                ...changes
            });
        });

        
        const promises = [];
        
        pendingChanges.forEach((changes, itemId) => {
            // Only include fields that were actually changed
            const updateData = {
                item_id: itemId,
                _token: '{{ csrf_token() }}'
            };
            
            // Add only the fields that were modified
            if (changes.hasOwnProperty('min_stock_level')) {
                updateData.min_stock_level = changes.min_stock_level;
            }
            if (changes.hasOwnProperty('reorder_point')) {
                updateData.reorder_point = changes.reorder_point;
            }
            if (changes.hasOwnProperty('max_stock_level')) {
                updateData.max_stock_level = changes.max_stock_level;
            }
            
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
                    showLoading(false);
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
                } else if (xhr.status === 422) {
                    errorMessage = 'Invalid input data. Please check your entries.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please try again later.';
                }
                showToast(errorMessage, 'error');
                showLoading(false);
                console.error('Seasonal adjustment error:', {xhr, status, error});
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