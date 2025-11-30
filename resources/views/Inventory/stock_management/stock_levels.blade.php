@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Live Stock Levels</h1>
            <p class="text-sm text-gray-500">Real-time view of current warehouse inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.stock.print-stock-report') }}"
               target="_blank"
               class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-print mr-2 opacity-70 group-hover:opacity-100"></i> Print Report
            </a>
            <a href="{{ route('inventory.stock.export-stock-csv') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-file-csv mr-2"></i> Export CSV
            </a>
            <a href="{{ route('inventory.stock.export-stock-pdf') . '?' . http_build_query(request()->query()) }}"
               class="inline-flex items-center justify-center px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- 2. METRICS SUMMARY --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Items -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between group hover:border-chocolate/30 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-chocolate/10 rounded-lg text-chocolate group-hover:bg-chocolate group-hover:text-white transition-colors">
                    <i class="fas fa-boxes text-lg"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl font-display font-bold text-gray-900">{{ number_format($metrics['total_items']) }}</p>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-1">Total Items</p>
            </div>
        </div>

        <!-- Healthy Stock -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between group hover:border-green-200 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-100 transition-colors">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl font-display font-bold text-green-700">{{ number_format($metrics['healthy_stock']) }}</p>
                <p class="text-xs font-bold text-green-600/70 uppercase tracking-wider mt-1">Healthy Stock</p>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white p-5 rounded-xl border border-border-soft border-l-4 border-l-amber-400 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600 group-hover:bg-amber-100 transition-colors">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl font-display font-bold text-amber-700">{{ number_format($metrics['low_stock']) }}</p>
                <p class="text-xs font-bold text-amber-600/70 uppercase tracking-wider mt-1">Low Stock</p>
            </div>
        </div>

        <!-- Critical / Out -->
        <div class="bg-white p-5 rounded-xl border border-border-soft border-l-4 border-l-red-500 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 bg-red-50 rounded-lg text-red-600 group-hover:bg-red-100 transition-colors">
                    <i class="fas fa-times-circle text-lg"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl font-display font-bold text-red-700">{{ number_format($metrics['critical_stock']) }}</p>
                <p class="text-xs font-bold text-red-600/70 uppercase tracking-wider mt-1">Critical / Out</p>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form id="stock-filter-form" method="GET" action="{{ route('inventory.stock.levels') }}" class="flex flex-col lg:flex-row items-center gap-4 w-full">
            <div class="relative w-full lg:flex-1 group">
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                           placeholder="Search Item Name, SKU...">
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Category</label>
                <div class="relative">
                    <select name="category" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer min-w-[160px]">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Status</label>
                <div class="relative">
                    <select name="status" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer min-w-[140px]">
                        <option value="">All Statuses</option>
                        <option value="good" {{ request('status') == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Per Page</label>
                <div class="relative">
                    <select name="per_page" class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer">
                        <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>
        </form>
    </div>

    {{-- 4. INVENTORY TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Item Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Category</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Physical Stock</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Last Movement</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($stockItems as $item)
                        @php
                            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                            $reorderPoint = $item->reorder_point ?? 0;
                            $minStockLevel = $item->min_stock_level ?? 0;
                            $maxStockLevel = $item->max_stock_level ?? 0;
                            
                            // Determine stock status styles
                            $status = 'Good';
                            $statusClass = 'text-green-700';
                            $statusBgClass = 'bg-green-100 border-green-200';
                            $rowClass = '';
                            $barColor = 'bg-green-500';
                            
                            if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                                $status = 'Critical';
                                $statusClass = 'text-red-700';
                                $statusBgClass = 'bg-red-100 border-red-200';
                                $rowClass = 'bg-red-50/30';
                                $barColor = 'bg-red-600';
                            } elseif ($currentStock <= $reorderPoint) {
                                $status = 'Low Stock';
                                $statusClass = 'text-amber-700';
                                $statusBgClass = 'bg-amber-100 border-amber-200';
                                $rowClass = 'bg-amber-50/30';
                                $barColor = 'bg-amber-500';
                            }
                            
                            // Calculate stock percentage
                            $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;
                            $barWidth = min(100, $percentage);
                            
                            // Get last movement
                            $lastMovement = $item->stockMovements->first();
                            $lastMovementText = 'No movement';
                            $lastMovementSub = '';
                            if ($lastMovement) {
                                $lastMovementText = $lastMovement->created_at->diffForHumans();
                                $lastMovementSub = ucfirst($lastMovement->movement_type);
                            }
                            
                            // Category Styling
                            $catName = $item->category->name ?? 'Uncategorized';
                        @endphp
                        
                        <tr class="hover:bg-cream-bg/50 transition-colors group {{ $rowClass }}">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white border border-border-soft flex items-center justify-center text-gray-400 flex-shrink-0 shadow-sm">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-chocolate">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $item->item_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $catName }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-bold {{ $statusClass }}">{{ number_format($currentStock, 1) }} {{ $item->unit->symbol ?? '' }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $percentage }}%</span>
                                </div>
                                <!-- Visual Health Bar -->
                                <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1">Max: {{ number_format($maxStockLevel) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $statusBgClass }}">
                                    @if($status == 'Critical')
                                        <i class="fas fa-times-circle mr-1.5"></i>
                                    @elseif($status == 'Low Stock')
                                        <i class="fas fa-exclamation-circle mr-1.5"></i>
                                    @else
                                        <i class="fas fa-check-circle mr-1.5"></i>
                                    @endif
                                    {{ $status }}
                                </span>
                                @if($status != 'Good')
                                    <div class="text-[10px] text-gray-500 mt-1 pl-1">Reorder: {{ $reorderPoint }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $lastMovementText }}</div>
                                <div class="text-xs text-gray-400">{{ $lastMovementSub }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex flex-col gap-1">
                                    <button onclick="openAdjustmentModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->item_code) }}', {{ $currentStock }}, '{{ $item->unit->symbol ?? '' }}')"
                                       class="text-blue-600 hover:text-white hover:bg-blue-600 border border-border-soft px-3 py-1.5 rounded-lg transition-all inline-flex items-center text-xs font-bold"
                                       data-item-id="{{ $item->id }}"
                                       data-item-name="{{ addslashes($item->name) }}"
                                       data-item-code="{{ addslashes($item->item_code) }}"
                                       data-current-stock="{{ $currentStock }}"
                                       data-unit-symbol="{{ $item->unit->symbol ?? '' }}">
                                        <i class="fas fa-edit mr-1.5"></i> Adjust Stock
                                    </button>
                                    <a href="{{ route('inventory.stock-card', $item) }}"
                                       class="text-chocolate hover:text-white hover:bg-chocolate border border-border-soft px-3 py-1.5 rounded-lg transition-all inline-flex items-center text-xs font-bold">
                                        <i class="fas fa-file-alt mr-1.5"></i> View Card
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-box-open text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate">No Items Found</h3>
                                    <p class="text-sm text-gray-500 mt-1">Try adjusting your filters to find what you're looking for.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($stockItems->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Showing <span class="font-bold text-chocolate">{{ $stockItems->firstItem() }}</span> to <span class="font-bold text-chocolate">{{ $stockItems->lastItem() }}</span> of <span class="font-bold text-chocolate">{{ $stockItems->total() }}</span> results
                    </p>
                    {{ $stockItems->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- ADJUSTMENTS MODAL --}}
<div id="adjustment-modal" class="fixed inset-0 z-50 hidden overflow-y-auto backdrop-blur-sm transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-chocolate/20 transition-opacity" aria-hidden="true" onclick="closeAdjustmentModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-border-soft max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-chocolate px-6 py-4 border-b border-chocolate-dark flex items-center justify-between">
                <h3 class="text-lg font-display font-bold text-white flex items-center gap-2">
                    <i class="fas fa-edit text-caramel"></i> New Adjustment
                </h3>
                <button onclick="closeAdjustmentModal()" class="text-white/70 hover:text-white focus:outline-none transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="p-6" id="adjustment-modal-content">
                <form id="adjustment-form" enctype="multipart/form-data" class="space-y-6">
                    @csrf 
                    
                    <!-- ACTION TYPE -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">1. Action Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Deduction -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="adjustment_type" value="remove" class="peer sr-only" checked>
                                <div class="h-full p-3 rounded-xl border border-border-soft bg-cream-bg/50 hover:bg-red-50/50 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:ring-1 peer-checked:ring-red-500 transition-all duration-200 text-center flex flex-col items-center justify-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-white border border-border-soft text-red-500 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <i class="fas fa-minus"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-gray-900">Remove</span>
                                        <span class="block text-[10px] text-gray-500">Loss / Damage</span>
                                    </div>
                                </div>
                                <div class="absolute top-2 right-2 text-red-600 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fas fa-check-circle"></i></div>
                            </label>
                            
                            <!-- Addition -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="adjustment_type" value="add" class="peer sr-only">
                                <div class="h-full p-3 rounded-xl border border-border-soft bg-cream-bg/50 hover:bg-green-50/50 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:ring-1 peer-checked:ring-green-500 transition-all duration-200 text-center flex flex-col items-center justify-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-white border border-border-soft text-green-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-gray-900">Add Stock</span>
                                        <span class="block text-[10px] text-gray-500">Return / Found</span>
                                    </div>
                                </div>
                                <div class="absolute top-2 right-2 text-green-600 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fas fa-check-circle"></i></div>
                            </label>
                        </div>
                    </div>

                    <!-- SELECTED ITEM INFO -->
                    <div class="p-4 bg-cream-bg/30 rounded-xl border border-border-soft space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-3">2. Selected Item</label>
                            
                            <!-- Item Info Display -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="text-sm font-bold text-chocolate" id="modal-item-name">Loading...</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5" id="modal-item-code">--</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500 font-bold uppercase">Current Stock</div>
                                        <div class="font-mono font-bold text-chocolate text-sm" id="modal-current-stock-display">--</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden input for item_id -->
                            <input type="hidden" id="modal-item-id" name="item_id" value="">
                        </div>

                        <!-- QUANTITY & REASON -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">3. Quantity <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.001" min="0.001" 
                                           class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm py-2.5 pl-3 pr-12 font-bold" 
                                           id="modal-quantity" name="quantity" placeholder="0.00" required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <input type="text" class="text-[10px] font-bold text-gray-400 bg-transparent border-none text-right w-12 p-0 uppercase" id="modal-unit-display" value="UNIT" disabled>
                                    </div>
                                </div>
                                <p class="text-[10px] text-red-600 mt-1 hidden font-bold flex items-center gap-1" id="modal-qty-error">
                                    <i class="fas fa-exclamation-circle"></i> Invalid quantity
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">4. Reason <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm py-2.5 pl-3 pr-8 cursor-pointer bg-white" id="modal-reason-code" name="reason_code" required>
                                        <option value="" disabled selected>Select reason...</option>
                                        <optgroup label="Inventory Loss">
                                            <option value="Spoilage / Expired">Spoilage / Expired</option>
                                            <option value="Damaged / Broken">Damaged / Broken</option>
                                            <option value="Spillage (Production)">Spillage</option>
                                            <option value="Theft / Missing">Theft / Missing</option>
                                        </optgroup>
                                        <optgroup label="Inventory Correction">
                                            <option value="Audit Variance Correction">Audit Variance</option>
                                            <option value="Found Item">Found Item</option>
                                        </optgroup>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- REMARKS -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">5. Remarks <span class="text-red-500">*</span></label>
                        <textarea rows="2" class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm p-3 resize-none" id="modal-remarks" name="remarks" placeholder="Briefly describe what happened..." required></textarea>
                    </div>

                    <!-- UPLOAD -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Attach Proof (Optional)</label>
                        <div class="relative group cursor-pointer" id="modal-photo-upload-area">
                            <div class="flex items-center justify-center w-full px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-cream-bg hover:border-caramel transition-all duration-200 bg-gray-50/50">
                                <input type="file" id="modal-photo" name="photo" class="sr-only" accept="image/*">
                                <div class="space-y-1 text-center">
                                    <div class="w-10 h-10 mx-auto bg-white rounded-full flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-lg group-hover:text-caramel transition-colors" id="modal-photo-icon"></i>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-bold text-chocolate hover:underline" id="modal-photo-upload-text">Click to upload</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 uppercase">PNG, JPG (Max 5MB)</p>
                                    <p class="text-xs text-green-600 font-bold bg-green-50 py-1 px-2 rounded-md mt-2 border border-green-100 shadow-sm" id="modal-photo-info" style="display: none;"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-chocolate hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-caramel transition-all duration-300 transform active:scale-95 items-center gap-2" id="modal-submit-btn">
                            <i class="fas fa-save"></i> <span id="modal-submit-text">Submit Adjustment</span>
                            <span id="modal-submit-loading" style="display: none;" class="flex items-center gap-2">
                                <i class="fas fa-circle-notch fa-spin"></i> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stock-filter-form');
    const searchInput = document.querySelector('input[name="search"]');
    let debounceTimer;

    // Function to debounce search input
    function debounceSubmit() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            form.submit();
        }, 800); // Wait 800ms after user stops typing
    }

    // Auto-submit on select changes
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            form.submit();
        });
    });

    // Auto-submit on search input with debounce
    if (searchInput) {
        searchInput.addEventListener('input', debounceSubmit);
    }

    // Initialize adjustment modal functionality
    initializeAdjustmentModal();
});

function initializeAdjustmentModal() {
    setupModalEventListeners();
    setupModalPhotoUpload();
}

function setupModalEventListeners() {
    const modalForm = document.getElementById('adjustment-form');
    if (modalForm) {
        modalForm.addEventListener('submit', handleModalFormSubmit);
    }
    
    const modalQuantity = document.getElementById('modal-quantity');
    if (modalQuantity) {
        modalQuantity.addEventListener('input', validateModalQuantity);
    }
    
    const modalRadios = document.querySelectorAll('input[name="adjustment_type"]');
    modalRadios.forEach(radio => {
        radio.addEventListener('change', validateModalQuantity);
    });
}

function setupModalPhotoUpload() {
    const uploadArea = document.getElementById('modal-photo-upload-area');
    const photoInput = document.getElementById('modal-photo');
    const photoText = document.getElementById('modal-photo-upload-text');
    const photoInfo = document.getElementById('modal-photo-info');
    const photoIcon = document.getElementById('modal-photo-icon');
    
    if (!uploadArea || !photoInput) return;
    
    uploadArea.addEventListener('click', () => photoInput.click());
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                showNotification('File size must be less than 5MB', 'error');
                photoInput.value = '';
                return;
            }
            if (!file.type.match('image.*')) {
                showNotification('Please select an image file', 'error');
                photoInput.value = '';
                return;
            }
            
            photoText.textContent = "Change Photo";
            photoInfo.textContent = file.name;
            photoInfo.style.display = 'inline-block';
            
            uploadArea.classList.add('border-green-400', 'bg-green-50');
            uploadArea.classList.remove('border-border-soft', 'bg-cream-bg/50');
            photoIcon.classList.remove('text-gray-400');
            photoIcon.classList.add('text-green-500');
        }
    });
}

function updateModalItemDisplay(itemData = null) {
    const currentStockDisplay = document.getElementById('modal-current-stock-display');
    const unitDisplay = document.getElementById('modal-unit-display');
    const itemNameDisplay = document.getElementById('modal-item-name');
    const itemCodeDisplay = document.getElementById('modal-item-code');
    const itemIdInput = document.getElementById('modal-item-id');
    
    if (!currentStockDisplay || !unitDisplay || !itemNameDisplay || !itemCodeDisplay || !itemIdInput) return;
    
    if (itemData) {
        currentStockDisplay.textContent = parseFloat(itemData.currentStock).toFixed(3) + ' ' + itemData.unitSymbol;
        unitDisplay.value = itemData.unitSymbol;
        itemNameDisplay.textContent = itemData.itemName;
        itemCodeDisplay.textContent = itemData.itemCode;
        itemIdInput.value = itemData.itemId;
        
        validateModalQuantity();
    } else {
        currentStockDisplay.textContent = '--';
        unitDisplay.value = 'UNIT';
        itemNameDisplay.textContent = 'Loading...';
        itemCodeDisplay.textContent = '--';
        itemIdInput.value = '';
    }
}

function validateModalQuantity() {
    const quantityInput = document.getElementById('modal-quantity');
    const qtyError = document.getElementById('modal-qty-error');
    const adjustmentType = document.querySelector('input[name="adjustment_type"]:checked')?.value;
    const itemIdInput = document.getElementById('modal-item-id');
    
    if (!quantityInput || !qtyError || !adjustmentType || !itemIdInput) return;
    
    quantityInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500', 'bg-red-50');
    qtyError.style.display = 'none';
    
    if (itemIdInput.value && adjustmentType === 'remove') {
        // Get current stock from the displayed value
        const currentStockText = document.getElementById('modal-current-stock-display').textContent;
        const currentStock = parseFloat(currentStockText) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        
        if (quantity > currentStock) {
            quantityInput.setCustomValidity('Cannot exceed stock');
            quantityInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500', 'bg-red-50');
            qtyError.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Exceeds current stock (${currentStock})`;
            qtyError.style.display = 'flex';
            return false;
        }
    }
    quantityInput.setCustomValidity('');
    return true;
}

function openAdjustmentModal(itemId, itemName, itemCode, currentStock, unitSymbol) {
    const modal = document.getElementById('adjustment-modal');
    if (!modal) return;
    
    // Reset form
    const form = document.getElementById('adjustment-form');
    if (form) {
        form.reset();
    }
    
    // Clear any previous errors
    const qtyError = document.getElementById('modal-qty-error');
    if (qtyError) {
        qtyError.style.display = 'none';
    }
    
    // Clear photo upload state
    const uploadArea = document.getElementById('modal-photo-upload-area');
    const photoInput = document.getElementById('modal-photo');
    const photoText = document.getElementById('modal-photo-upload-text');
    const photoInfo = document.getElementById('modal-photo-info');
    const photoIcon = document.getElementById('modal-photo-icon');
    
    if (uploadArea && photoInput && photoText && photoInfo && photoIcon) {
        photoInput.value = '';
        photoText.textContent = 'Click to upload';
        photoInfo.style.display = 'none';
        uploadArea.classList.remove('border-green-400', 'bg-green-50');
        uploadArea.classList.add('border-border-soft', 'bg-cream-bg/50');
        photoIcon.classList.remove('text-green-500');
        photoIcon.classList.add('text-gray-400');
    }
    
    // Populate item information
    const itemData = {
        itemId: itemId,
        itemName: itemName,
        itemCode: itemCode,
        currentStock: currentStock,
        unitSymbol: unitSymbol
    };
    
    updateModalItemDisplay(itemData);
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeAdjustmentModal() {
    const modal = document.getElementById('adjustment-modal');
    if (!modal) return;
    
    // Reset item display
    updateModalItemDisplay();
    
    modal.classList.add('hidden');
    document.body.style.overflow = ''; // Restore scrolling
}

async function handleModalFormSubmit(e) {
    e.preventDefault();
    
    if(!validateModalQuantity()) {
        showNotification('Please fix errors before submitting.', 'error');
        const quantityInput = document.getElementById('modal-quantity');
        if (quantityInput) {
            quantityInput.classList.add('animate-pulse');
            setTimeout(() => quantityInput.classList.remove('animate-pulse'), 500);
        }
        return;
    }
    
    const submitBtn = document.getElementById('modal-submit-btn');
    const submitText = document.getElementById('modal-submit-text');
    const submitLoading = document.getElementById('modal-submit-loading');
    
    if (!submitBtn || !submitText || !submitLoading) return;
    
    submitBtn.disabled = true;
    submitText.style.display = 'none';
    submitLoading.style.display = 'flex';
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch("{{ route('inventory.adjustments.store') }}", { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showNotification('Adjustment recorded successfully!', 'success');
            closeAdjustmentModal();
            
            // Refresh the page after a short delay to show updated stock levels
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            let msg = result.message || 'Failed to create adjustment';
            if(result.errors) {
                msg = Object.values(result.errors).flat().join('\n');
            }
            showNotification(msg, 'error');
            submitBtn.disabled = false;
            submitText.style.display = 'inline';
            submitLoading.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error occurred. Please check your connection.', 'error');
        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoading.style.display = 'none';
    }
}

function showNotification(message, type = 'info') {
    const existing = document.getElementById('toast-notification');
    if(existing) existing.remove();

    const notification = document.createElement('div');
    notification.id = 'toast-notification';
    notification.className = `fixed top-5 right-5 z-[60] px-6 py-4 rounded-xl shadow-2xl text-white font-medium slide-in flex items-center gap-4 border border-white/10 backdrop-blur-md ${
        type === 'success' ? 'bg-gray-900' : 
        type === 'error' ? 'bg-red-600' : 'bg-blue-600'
    }`;
    
    const icon = type === 'success' ? '<i class="fas fa-check-circle text-green-400 text-xl"></i>' : 
                 type === 'error' ? '<i class="fas fa-times-circle text-white text-xl"></i>' : 
                 '<i class="fas fa-info-circle text-xl"></i>';
                  
    notification.innerHTML = `${icon} <div><p class="font-bold text-sm uppercase tracking-wider mb-0.5">${type}</p><p class="text-sm opacity-90">${message}</p></div>`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        notification.style.transition = 'all 0.5s ease-in-out';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}
</script>
@endsection