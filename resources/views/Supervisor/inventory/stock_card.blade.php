@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6" id="stockCardApp">

    {{-- 1. HEADER & ITEM CONTEXT --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('supervisor.inventory.stock-level') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-chocolate hover:border-chocolate transition shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">Stock Card: {{ $item->name ?? 'Unknown Item' }}</h1>
                    @if($item->category)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">{{ $item->category->name }}</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    SKU: <span class="font-mono text-gray-700">{{ $item->item_code ?? 'N/A' }}</span> 
                    • Unit: <span class="font-mono text-gray-700">{{ $item->unit->symbol ?? 'N/A' }}</span>
                    @if($item->unit->name) ({{ $item->unit->name }}) @endif
                </p>
            </div>
        </div>
        
        <!-- Item Selector (Quick Switch) -->
        <div class="relative w-full md:w-64">
            <select class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-chocolate focus:border-chocolate rounded-md shadow-sm bg-white" 
                    onchange="switchItem(this.value)">
                @foreach($allItems as $availableItem)
                    <option value="{{ $availableItem->id }}" 
                            {{ $availableItem->id == $item->id ? 'selected' : '' }}>
                        {{ $availableItem->name }} ({{ $availableItem->item_code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- 2. SNAPSHOT METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Current Stock -->
        <div class="bg-white border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Balance</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-gray-900">{{ number_format($metrics['current_balance'], 1) }}</span>
                <span class="text-sm text-gray-500">{{ $item->unit->symbol ?? '' }}</span>
            </div>
            @if($metrics['stock_status'] !== 'good')
                <div class="mt-1">
                    @if($metrics['stock_status'] === 'critical')
                        <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">Critical</span>
                    @elseif($metrics['stock_status'] === 'low')
                        <span class="text-xs px-2 py-1 bg-amber-100 text-amber-800 rounded-full">Low Stock</span>
                    @elseif($metrics['stock_status'] === 'out_of_stock')
                        <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">Out of Stock</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Reorder Level -->
        <div class="bg-white border-l-4 border-amber-400 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reorder Level</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-amber-600">{{ number_format($metrics['reorder_level'], 1) }}</span>
                <span class="text-sm text-gray-500">{{ $item->unit->symbol ?? '' }}</span>
            </div>
            @if($metrics['current_balance'] <= $metrics['reorder_level'])
                <div class="mt-1">
                    <span class="text-xs px-2 py-1 bg-amber-100 text-amber-800 rounded-full">Reorder Now</span>
                </div>
            @endif
        </div>

        <!-- Average Daily Usage -->
        <div class="bg-white border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg. Daily Usage (7 days)</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-gray-900">{{ number_format($metrics['average_daily_usage'], 1) }}</span>
                <span class="text-sm text-gray-500">{{ $item->unit->symbol ?? '' }}/Day</span>
            </div>
            @if($metrics['days_of_supply'] > 0)
                <div class="mt-1">
                    <span class="text-xs text-gray-500">{{ $metrics['days_of_supply'] }} days supply</span>
                </div>
            @endif
        </div>

        <!-- Last Restock -->
        <div class="bg-white border-l-4 border-purple-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Last Restock</p>
            <div class="mt-1">
                @if($metrics['last_restock_date'])
                    <span class="text-lg font-bold text-gray-900">{{ $metrics['last_restock_date']->format('M j') }}</span>
                    <span class="text-xs text-gray-500 block">{{ $metrics['last_restock_days_ago'] }}</span>
                @else
                    <span class="text-lg font-bold text-gray-500">No Record</span>
                    <span class="text-xs text-gray-400 block">Never restocked</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. TRANSACTION HISTORY --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Filter:</span>
                <button onclick="filterTransactions('all')" class="transaction-filter-btn px-3 py-1 text-xs font-medium bg-chocolate text-white rounded-full shadow-sm" data-filter="all">All</button>
                <button onclick="filterTransactions('in')" class="transaction-filter-btn px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full" data-filter="in">Stock In</button>
                <button onclick="filterTransactions('out')" class="transaction-filter-btn px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full" data-filter="out">Stock Out</button>
                <button onclick="filterTransactions('adjustment')" class="transaction-filter-btn px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full" data-filter="adjustment">Adjustments</button>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate">
                <span class="text-gray-400">-</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate">
                <button type="submit" class="px-3 py-1 text-xs font-medium bg-blue-500 text-white rounded-md hover:bg-blue-600">Apply</button>
                @if(request('date_from') || request('date_to'))
                    <a href="{{ route('supervisor.inventory.stock-card', $item) }}" class="px-3 py-1 text-xs font-medium bg-gray-500 text-white rounded-md hover:bg-gray-600">Clear</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Type</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Change</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Running Balance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User / Actor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="transactionTableBody">
                    @forelse($movements as $index => $movement)
                        @php
                            $isStockIn = $movement->quantity > 0;
                            $isAdjustment = in_array($movement->movement_type, ['adjustment', 'waste', 'expired', 'return']);
                            $rowClass = 'hover:bg-gray-50 transition-colors';
                            
                            if ($isAdjustment) {
                                $rowClass = 'bg-red-50/30 hover:bg-red-50 transition-colors border-l-4 border-l-red-400';
                            }
                            
                            $transactionType = match($movement->movement_type) {
                                'purchase' => 'Purchase Receive',
                                'sale' => 'Sale',
                                'production' => 'Production Use',
                                'adjustment' => 'Stock Adjustment',
                                'transfer' => 'Stock Transfer',
                                'waste' => 'Spoilage / Damage',
                                'return' => 'Return/Write-off',
                                default => ucfirst(str_replace('_', ' ', $movement->movement_type))
                            };
                            
                            $colorClass = match($movement->movement_type) {
                                'purchase' => 'bg-green-500',
                                'sale', 'production' => 'bg-blue-500',
                                'waste', 'expired', 'return' => 'bg-red-500',
                                'adjustment' => 'bg-orange-500',
                                default => 'bg-gray-500'
                            };
                            
                            $quantityDisplay = ($movement->quantity >= 0 ? '+' : '') . number_format($movement->quantity, 1);
                            $quantityColor = $movement->quantity >= 0 ? 'text-green-600' : 'text-red-600';
                            
                            // Calculate running balance (simplified - showing current balance for all for now)
                            $runningBalance = $metrics['current_balance'];
                        @endphp
                        
                        <tr class="{{ $rowClass }} transaction-row" 
                            data-movement-type="{{ $movement->quantity >= 0 ? 'in' : 'out' }}"
                            data-is-adjustment="{{ $isAdjustment ? '1' : '0' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $movement->created_at->format('M j, g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movement->reference_number)
                                    <span class="font-mono text-xs 
                                        @if($isStockIn) text-green-600 bg-green-50 
                                        @elseif($isAdjustment) text-red-600 bg-red-100 
                                        @else text-blue-600 bg-blue-50 
                                        @endif 
                                        px-2 py-1 rounded">
                                        #{{ $movement->reference_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full {{ $colorClass }} mr-2"></div>
                                    <span class="text-sm font-medium text-gray-900">{{ $transactionType }}</span>
                                </div>
                                @if($movement->notes)
                                    <p class="text-xs text-gray-500 ml-4">{{ Str::limit($movement->notes, 50) }}</p>
                                @endif
                                @if($movement->batch_number)
                                    <p class="text-xs text-gray-400 ml-4">Batch: {{ $movement->batch_number }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $quantityColor }}">{{ $quantityDisplay }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($runningBalance, 1) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $movement->user->name ?? 'System' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-clipboard-list text-2xl text-gray-300 mb-2"></i>
                                    <p>No stock movements found for this item.</p>
                                    <p class="text-sm text-gray-400 mt-1">Stock movements will appear here as transactions occur.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $movements->firstItem() ?? 0 }}</span> 
                    to <span class="font-medium">{{ $movements->lastItem() ?? 0 }}</span> 
                    of <span class="font-medium">{{ $movements->total() }}</span> transactions
                </p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    @if($movements->previousPageUrl())
                        <a href="{{ $movements->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>
                    @else
                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">Previous</span>
                    @endif
                    
                    @if($movements->nextPageUrl())
                        <a href="{{ $movements->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">Next</span>
                    @endif
                </nav>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function switchItem(itemId) {
    if (itemId && itemId != {{ $item->id }}) {
        window.location.href = '{{ route("supervisor.inventory.stock-card", ":id") }}'.replace(':id', itemId);
    }
}

function filterTransactions(filterType) {
    const rows = document.querySelectorAll('.transaction-row');
    const buttons = document.querySelectorAll('.transaction-filter-btn');
    
    // Update button styles
    buttons.forEach(btn => {
        btn.classList.remove('bg-chocolate', 'text-white');
        btn.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-300', 'hover:bg-gray-100');
    });
    
    // Highlight active button
    const activeBtn = document.querySelector(`[data-filter="${filterType}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-300', 'hover:bg-gray-100');
        activeBtn.classList.add('bg-chocolate', 'text-white');
    }
    
    // Filter rows
    rows.forEach(row => {
        const movementType = row.dataset.movementType;
        const isAdjustment = row.dataset.isAdjustment === '1';
        
        let showRow = false;
        
        switch(filterType) {
            case 'all':
                showRow = true;
                break;
            case 'in':
                showRow = movementType === 'in';
                break;
            case 'out':
                showRow = movementType === 'out';
                break;
            case 'adjustment':
                showRow = isAdjustment;
                break;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

// Set initial filter to 'all' when page loads
document.addEventListener('DOMContentLoaded', function() {
    filterTransactions('all');
});
</script>
@endpush

@endsection