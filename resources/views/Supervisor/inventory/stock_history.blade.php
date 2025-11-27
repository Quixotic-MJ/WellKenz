@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Stock History</h1>
            <p class="text-sm text-gray-500">Complete inventory movement history and current status overview.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-file-download mr-2 opacity-70 group-hover:opacity-100"></i> Export History
            </button>
        </div>
    </div>

    {{-- 2. INVENTORY TABLE CARD --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        {{-- Table Header / Toolbar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
            <h3 class="font-display text-lg font-bold text-chocolate flex items-center gap-2">
                <i class="fas fa-boxes text-caramel"></i> Inventory Items
            </h3>
            
            {{-- Optional: Search (Visual placeholder to match design system, functional if backend supports it) --}}
            <div class="relative group hidden sm:block">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 group-focus-within:text-caramel"></i>
                <input type="text" placeholder="Quick find..." class="pl-9 pr-4 py-2 text-xs border border-gray-300 rounded-lg focus:border-caramel focus:ring-1 focus:ring-caramel/20 w-48 transition-all">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-white">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Item Details</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Category</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Current Stock</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Reorder Point</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($items as $item)
                        @php
                            // Logic for visual status
                            $currentQty = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                            $reorderPoint = $item->reorder_point ?? 0;
                            
                            $isLow = $currentQty <= $reorderPoint;
                            $isCritical = $currentQty <= ($reorderPoint * 0.5);
                            
                            $rowClass = '';
                            if ($isCritical) $rowClass = 'bg-red-50/30';
                            elseif ($isLow) $rowClass = 'bg-amber-50/30';
                        @endphp

                        <tr class="hover:bg-cream-bg/50 transition-colors group {{ $rowClass }}">
                            
                            {{-- Item Name & Code --}}
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white border border-border-soft flex items-center justify-center text-gray-400 shadow-sm flex-shrink-0">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-chocolate">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $item->item_code }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            {{-- Current Stock --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->currentStockRecord)
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-lg font-bold {{ $isLow ? ($isCritical ? 'text-red-600' : 'text-amber-600') : 'text-gray-900' }}">
                                            {{ number_format($item->currentStockRecord->current_quantity, 2) }}
                                        </span>
                                        <span class="text-xs text-gray-500 font-medium">{{ $item->unit->symbol ?? 'units' }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">No Records</span>
                                @endif
                            </td>

                            {{-- Reorder Point --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ number_format($item->reorder_point, 2) }}</span>
                                    <span class="text-xs text-gray-400">{{ $item->unit->symbol ?? '' }}</span>
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($isCritical)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 border border-red-200">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Critical
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700 border border-amber-200">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Low
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 border border-green-200">
                                        <i class="fas fa-check-circle mr-1"></i> Good
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('supervisor.inventory.stock-card', $item->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-white border border-border-soft text-chocolate text-xs font-bold rounded-lg hover:bg-chocolate hover:text-white transition-all shadow-sm group/btn">
                                    <i class="fas fa-history mr-1.5 opacity-70 group-hover/btn:opacity-100"></i> History
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                                        <i class="fas fa-box-open text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate mb-1">No Inventory Items</h3>
                                    <p class="text-sm text-gray-500">Your inventory list is currently empty.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 3. PAGINATION --}}
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-border-soft flex justify-center">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection