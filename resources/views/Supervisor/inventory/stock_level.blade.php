@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Live Stock Levels</h1>
            <p class="text-sm text-gray-500">Real-time view of current warehouse inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.inventory.print-stock-report') }}" 
               target="_blank"
               class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-print mr-2 opacity-70 group-hover:opacity-100"></i> Print Report
            </a>
            <a href="{{ route('supervisor.inventory.export-stock-csv') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-file-csv mr-2"></i> Export CSV
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
        <form method="GET" action="{{ route('supervisor.inventory.stock-level') }}" class="flex flex-col lg:flex-row items-center gap-4 w-full">
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
            
            <div class="flex gap-2 self-end">
                <button type="submit" class="px-6 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-sm">
                    Filter
                </button>
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
                                <a href="{{ route('supervisor.inventory.stock-card', $item) }}" 
                                   class="text-chocolate hover:text-white hover:bg-chocolate border border-border-soft px-3 py-1.5 rounded-lg transition-all inline-flex items-center text-xs font-bold">
                                    <i class="fas fa-file-alt mr-1.5"></i> View Card
                                </a>
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
@endsection