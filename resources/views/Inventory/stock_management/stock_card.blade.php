@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600" id="stockCardApp">

    {{-- 1. HEADER & CONTEXT --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.stock.levels') }}" class="flex items-center justify-center w-10 h-10 bg-white border border-border-soft rounded-xl text-chocolate hover:bg-cream-bg transition-colors shadow-sm group">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="font-display text-3xl font-bold text-chocolate">{{ $item->name ?? 'Unknown Item' }}</h1>
                    @if($item->category)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-cream-bg text-caramel border border-border-soft">
                            {{ $item->category->name }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 font-mono flex items-center gap-3">
                    <span>SKU: <strong class="text-gray-700">{{ $item->item_code ?? 'N/A' }}</strong></span>
                    <span class="text-border-soft">|</span>
                    <span>Unit: <strong class="text-gray-700">{{ $item->unit->name ?? 'Unit' }} ({{ $item->unit->symbol ?? '' }})</strong></span>
                </p>
            </div>
        </div>
        
        <!-- Quick Item Switcher -->
        <div class="w-full md:w-72 relative group">
            <label class="block text-[10px] font-bold text-chocolate uppercase tracking-widest mb-1 ml-1">Switch Item</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <select class="block w-full pl-10 pr-10 py-2.5 border border-border-soft bg-white rounded-xl text-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer shadow-sm" 
                        onchange="switchItem(this.value)">
                    @foreach($allItems as $availableItem)
                        <option value="{{ $availableItem->id }}" {{ $availableItem->id == $item->id ? 'selected' : '' }}>
                            {{ $availableItem->name }} ({{ $availableItem->item_code }})
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SNAPSHOT METRICS GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Current Stock -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-8 -mt-8"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Current Balance</p>
                <div class="flex items-baseline gap-1mt-2">
                    <span class="font-display text-3xl font-bold text-gray-900">{{ number_format($metrics['current_balance'], 1) }}</span>
                    <span class="text-sm font-bold text-gray-400">{{ $item->unit->symbol ?? '' }}</span>
                </div>
            </div>
            @if($metrics['stock_status'] !== 'good')
                <div class="mt-3 pt-3 border-t border-gray-100">
                    @if($metrics['stock_status'] === 'critical')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100">
                            <i class="fas fa-exclamation-circle"></i> Critical
                        </span>
                    @elseif($metrics['stock_status'] === 'low')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded border border-amber-100">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock
                        </span>
                    @elseif($metrics['stock_status'] === 'out_of_stock')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    @endif
                </div>
            @else
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100">
                        <i class="fas fa-check-circle"></i> Healthy
                    </span>
                </div>
            @endif
        </div>

        <!-- Reorder Level -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -mr-8 -mt-8"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Reorder Point</p>
                <div class="flex items-baseline gap-1 mt-2">
                    <span class="font-display text-3xl font-bold text-amber-600">{{ number_format($metrics['reorder_level'], 1) }}</span>
                    <span class="text-sm font-bold text-gray-400">{{ $item->unit->symbol ?? '' }}</span>
                </div>
            </div>
            @if($metrics['current_balance'] <= $metrics['reorder_level'])
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs font-bold text-amber-600 flex items-center gap-1">
                        <i class="fas fa-shopping-cart"></i> Reorder Required
                    </span>
                </div>
            @else
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs font-bold text-gray-400 flex items-center gap-1">
                        Target: > {{ number_format($metrics['reorder_level'], 0) }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Usage Metrics -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-green-50 rounded-bl-full -mr-8 -mt-8"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">7-Day Avg. Usage</p>
                <div class="flex items-baseline gap-1 mt-2">
                    <span class="font-display text-3xl font-bold text-gray-900">{{ number_format($metrics['average_daily_usage'], 1) }}</span>
                    <span class="text-sm font-bold text-gray-400">/ Day</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                @if($metrics['days_of_supply'] > 0)
                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100">
                        ~{{ $metrics['days_of_supply'] }} Days Supply
                    </span>
                @else
                    <span class="text-xs font-bold text-gray-400">Not enough data</span>
                @endif
            </div>
        </div>

        <!-- Restock Info -->
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-purple-50 rounded-bl-full -mr-8 -mt-8"></div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Last Restock</p>
                <div class="mt-2">
                    @if($metrics['last_restock_date'])
                        <span class="font-display text-2xl font-bold text-gray-900">{{ $metrics['last_restock_date']->format('M j') }}</span>
                    @else
                        <span class="font-display text-2xl font-bold text-gray-400">--</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                @if($metrics['last_restock_date'])
                    <span class="text-xs font-bold text-purple-600 flex items-center gap-1">
                        <i class="fas fa-history"></i> {{ $metrics['last_restock_days_ago'] }}
                    </span>
                @else
                    <span class="text-xs text-gray-400">Never restocked</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. TRANSACTION HISTORY --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex flex-col lg:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full lg:w-auto no-scrollbar">
                <span class="text-xs font-bold text-chocolate uppercase tracking-wide mr-2">Filter:</span>
                <button onclick="filterTransactions('all')" class="transaction-filter-btn px-4 py-1.5 text-xs font-bold bg-chocolate text-white rounded-full shadow-md transition-all" data-filter="all">All</button>
                <button onclick="filterTransactions('in')" class="transaction-filter-btn px-4 py-1.5 text-xs font-bold bg-white text-gray-500 border border-border-soft hover:border-caramel hover:text-caramel rounded-full transition-all" data-filter="in">Stock In</button>
                <button onclick="filterTransactions('out')" class="transaction-filter-btn px-4 py-1.5 text-xs font-bold bg-white text-gray-500 border border-border-soft hover:border-caramel hover:text-caramel rounded-full transition-all" data-filter="out">Stock Out</button>
                <button onclick="filterTransactions('adjustment')" class="transaction-filter-btn px-4 py-1.5 text-xs font-bold bg-white text-gray-500 border border-border-soft hover:border-caramel hover:text-caramel rounded-full transition-all" data-filter="adjustment">Adjustments</button>
            </div>
            
            <form method="GET" class="flex items-center gap-2 w-full lg:w-auto">
                <div class="flex items-center bg-white border border-border-soft rounded-lg px-2 py-1">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-xs border-none focus:ring-0 p-1 text-gray-600 bg-transparent">
                    <span class="text-gray-300 mx-1">|</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-xs border-none focus:ring-0 p-1 text-gray-600 bg-transparent">
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-white border border-border-soft text-chocolate rounded-lg hover:bg-cream-bg transition-colors">
                    Apply
                </button>
                @if(request('date_from') || request('date_to'))
                    <a href="{{ route('inventory.stock-card', $item) }}" class="px-3 py-2 text-xs font-bold text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Reference</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Transaction Type</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Change</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Balance</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Actor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="transactionTableBody">
                    @php
                        // Pre-calculate running balances for all movements efficiently
                        $movementsWithBalance = [];
                        $cumulativeBalance = 0;
                        
                        // Get all movements for this item up to each point in time (for accurate running balance)
                        $allMovementsQuery = \App\Models\StockMovement::where('item_id', $item->id)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        
                        foreach ($movements as $movement) {
                            $isAdjustment = in_array($movement->movement_type, ['adjustment', 'waste', 'expired', 'return']);
                            $isStockIn = !$isAdjustment && $movement->quantity >= 0;
                            $isStockOut = !$isAdjustment && $movement->quantity < 0;
                            
                            $movementsWithBalance[] = [
                                'movement' => $movement,
                                'isAdjustment' => $isAdjustment,
                                'isStockIn' => $isStockIn,
                                'isStockOut' => $isStockOut
                            ];
                        }
                    @endphp
                    
                    @forelse($movements as $index => $movement)
                        @php
                            $movementData = $movementsWithBalance[$index];
                            $isAdjustment = $movementData['isAdjustment'];
                            $isStockIn = $movementData['isStockIn'];
                            $isStockOut = $movementData['isStockOut'];
                            
                            $rowClass = 'hover:bg-cream-bg/50 transition-colors group';
                            if ($isAdjustment) {
                                $rowClass = 'bg-red-50/30 hover:bg-red-50/60 transition-colors';
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

                            $badgeColor = match($movement->movement_type) {
                                'purchase' => 'bg-green-100 text-green-700 border-green-200',
                                'sale', 'production' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'waste', 'expired', 'return' => 'bg-red-100 text-red-700 border-red-200',
                                'adjustment' => 'bg-amber-100 text-amber-700 border-amber-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                            };

                            $quantityDisplay = ($isStockIn ? '+' : '') . number_format($movement->quantity, 2);
                            $quantityColor = $isStockIn ? 'text-green-600' : 'text-red-600';
                            
                            // Calculate running balance efficiently
                            // Sum all movements up to this point in time
                            $runningBalance = 0;
                            foreach ($allMovementsQuery as $movementItem) {
                                if ($movementItem->created_at->lte($movement->created_at)) {
                                    $runningBalance += $movementItem->quantity;
                                } else {
                                    break; // Since movements are ordered by created_at, we can break early
                                }
                            }
                        @endphp

                        <tr class="{{ $rowClass }} transaction-row" 
                            data-movement-type="{{ $isAdjustment ? 'adjustment' : ($isStockIn ? 'in' : 'out') }}"
                            data-is-adjustment="{{ $isAdjustment ? '1' : '0' }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $movement->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $movement->created_at->format('g:i A') }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movement->reference_number)
                                    <span class="font-mono text-xs font-bold px-2 py-1 rounded border bg-white text-gray-600 border-border-soft">
                                        #{{ $movement->reference_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">N/A</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $badgeColor }}">
                                        {{ $transactionType }}
                                    </span>
                                </div>
                                @if($movement->notes)
                                    <p class="text-xs text-gray-500 mt-1 italic pl-1 border-l-2 border-gray-200">
                                        {{ Str::limit($movement->notes, 60) }}
                                    </p>
                                @endif
                                @if($movement->batch_number)
                                    <p class="text-[10px] text-gray-400 font-mono mt-1">Batch: {{ $movement->batch_number }}</p>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $quantityColor }}">{{ $quantityDisplay }}</span>
                                <span class="text-xs text-gray-400 font-normal">{{ $item->unit->symbol ?? '' }}</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($runningBalance, 2) }}</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-chocolate/10 flex items-center justify-center text-[10px] font-bold text-chocolate">
                                        {{ substr($movement->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $movement->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-history text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate mb-1">No History</h3>
                                    <p class="text-sm text-gray-500">No stock movements found for this period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($movements->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-border-soft">
                {{ $movements->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function switchItem(itemId) {
    if (itemId && itemId != {{ $item->id }}) {
        window.location.href = '{{ route("inventory.stock-card", ":id") }}'.replace(':id', itemId);
    }
}

function filterTransactions(filterType) {
    const rows = document.querySelectorAll('.transaction-row');
    const buttons = document.querySelectorAll('.transaction-filter-btn');
    
    // Update button styles
    buttons.forEach(btn => {
        if(btn.dataset.filter === filterType) {
            btn.classList.remove('bg-white', 'text-gray-500', 'border-border-soft');
            btn.classList.add('bg-chocolate', 'text-white', 'shadow-md');
        } else {
            btn.classList.add('bg-white', 'text-gray-500', 'border-border-soft');
            btn.classList.remove('bg-chocolate', 'text-white', 'shadow-md');
        }
    });
    
    // Filter rows
    rows.forEach(row => {
        const movementType = row.dataset.movementType;
        const isAdjustment = row.dataset.isAdjustment === '1';
        let showRow = false;
        
        switch(filterType) {
            case 'all': showRow = true; break;
            case 'in': showRow = movementType === 'in'; break;
            case 'out': showRow = movementType === 'out'; break;
            case 'adjustment': showRow = movementType === 'adjustment'; break;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}
</script>
@endpush
@endsection