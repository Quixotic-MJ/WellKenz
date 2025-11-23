@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Live Stock Levels</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time view of current warehouse inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.inventory.print-stock-report') }}" 
               target="_blank"
               class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Stock Sheet
            </a>
            <a href="{{ route('supervisor.inventory.export-stock-csv') }}" 
               class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- 2. METRICS SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total SKU -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($metrics['total_items']) }}</p>
            </div>
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <!-- Good Stock -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-green-600 uppercase tracking-wider">Healthy Stock</p>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($metrics['healthy_stock']) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white border-l-4 border-amber-400 border-y border-r border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Low Stock</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($metrics['low_stock']) }}</p>
            </div>
            <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center text-amber-600">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <!-- Critical / Out -->
        <div class="bg-white border-l-4 border-red-500 border-y border-r border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Critical / Out</p>
                <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($metrics['critical_stock']) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <form method="GET" action="{{ route('supervisor.inventory.stock-level') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                       placeholder="Search Item Name, SKU...">
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <select name="category" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Statuses</option>
                    <option value="good" {{ request('status') == 'good' ? 'selected' : '' }}>Good</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <select name="per_page" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="20" {{ request('per_page') == '20' ? 'selected' : '' }}>20 per page</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-chocolate text-white rounded-md hover:bg-chocolate-dark transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- 4. INVENTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Physical Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Movement</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stockItems as $item)
                        @php
                            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                            $reorderPoint = $item->reorder_point ?? 0;
                            $minStockLevel = $item->min_stock_level ?? 0;
                            $maxStockLevel = $item->max_stock_level ?? 0;
                            
                            // Determine stock status
                            $status = 'Good';
                            $statusClass = 'text-green-600';
                            $statusBgClass = 'bg-green-100 text-green-800';
                            $rowClass = '';
                            $barColor = 'bg-green-500';
                            
                            if ($currentStock <= 0 || $currentStock <= $reorderPoint * 0.5) {
                                $status = 'Critical';
                                $statusClass = 'text-red-600';
                                $statusBgClass = 'bg-red-100 text-red-800';
                                $rowClass = 'bg-red-50/30 border-l-4 border-l-red-400';
                                $barColor = 'bg-red-600';
                            } elseif ($currentStock <= $reorderPoint) {
                                $status = 'Low Stock';
                                $statusClass = 'text-amber-600';
                                $statusBgClass = 'bg-amber-100 text-amber-800';
                                $rowClass = 'bg-amber-50/30';
                                $barColor = 'bg-amber-500';
                            }
                            
                            // Calculate stock percentage
                            $percentage = $maxStockLevel > 0 ? round(($currentStock / $maxStockLevel) * 100, 1) : 0;
                            $barWidth = min(100, $percentage);
                            
                            // Get last movement
                            $lastMovement = $item->stockMovements->first();
                            $lastMovementText = 'No movement';
                            if ($lastMovement) {
                                $timeDiff = Carbon\Carbon::now()->diffForHumans($lastMovement->created_at, true);
                                $lastMovementText = $timeDiff . ' ago (' . ucfirst($lastMovement->movement_type) . ')';
                            }
                            
                            // Get category color
                            $categoryColors = [
                                'Flour & Grains' => 'bg-amber-100 text-amber-800',
                                'Dairy Products' => 'bg-blue-100 text-blue-800',
                                'Sweeteners' => 'bg-pink-100 text-pink-800',
                                'Fats & Oils' => 'bg-yellow-100 text-yellow-800',
                                'Finished Products' => 'bg-green-100 text-green-800',
                                'Packaging Materials' => 'bg-purple-100 text-purple-800',
                                'Cleaning Supplies' => 'bg-gray-100 text-gray-800'
                            ];
                            $categoryClass = $categoryColors[$item->category->name ?? ''] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        
                        <tr class="hover:bg-gray-50 transition-colors {{ $rowClass }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-600">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $item->item_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryClass }}">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold {{ $statusClass }}">{{ number_format($currentStock, 1) }} {{ $item->unit->symbol ?? '' }}</div>
                                <!-- Visual Health Bar -->
                                <div class="w-24 h-1.5 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <div class="text-[10px] {{ $statusClass }} mt-0.5">
                                    @if($status == 'Critical')
                                        <span class="font-bold">Below Reorder ({{ $reorderPoint }} {{ $item->unit->symbol ?? '' }})</span>
                                    @elseif($status == 'Low Stock')
                                        <span class="font-medium">Near Reorder ({{ $reorderPoint }} {{ $item->unit->symbol ?? '' }})</span>
                                    @else
                                        <span class="text-gray-400">Reorder at {{ $reorderPoint }} {{ $item->unit->symbol ?? '' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusBgClass }}">
                                    @if($status == 'Critical')
                                        <i class="fas fa-times-circle mr-1"></i>
                                    @elseif($status == 'Low Stock')
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                    @else
                                        <i class="fas fa-check-circle mr-1"></i>
                                    @endif
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $lastMovementText }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('supervisor.inventory.stock-card', $item) }}" 
                                   class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-border-soft px-3 py-1.5 rounded hover:bg-cream-bg transition">
                                    View Card
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No items found</p>
                                    <p class="text-sm">Try adjusting your search filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($stockItems->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $stockItems->firstItem() }}</span> 
                        to <span class="font-medium">{{ $stockItems->lastItem() }}</span> 
                        of <span class="font-medium">{{ $stockItems->total() }}</span> results
                    </p>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        {{ $stockItems->links() }}
                    </nav>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection