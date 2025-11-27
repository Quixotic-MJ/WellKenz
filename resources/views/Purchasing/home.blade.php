@extends('Purchasing.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-24 font-sans text-gray-600 h-[calc(100vh-6rem)] flex flex-col">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 shrink-0">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Procurement Dashboard</h1>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span class="flex items-center gap-1.5">
                    <i class="far fa-calendar-alt text-caramel"></i>
                    {{ date('F d, Y') }}
                </span>
                <span class="text-border-soft">|</span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-sync-alt text-caramel"></i>
                    Data synced: {{ now()->format('H:i') }}
                </span>
            </div>
        </div>
        <div>
            <button onclick="location.reload()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-redo-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i> Refresh Data
            </button>
        </div>
    </div>

    {{-- 2. KPI / METRICS GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1 min-h-0">
        
        {{-- WIDGET 1: INVENTORY ALERTS (Low Stock) --}}
        <div class="bg-white border border-border-soft rounded-2xl shadow-sm flex flex-col overflow-hidden h-full">
            <div class="px-5 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-red-100 text-red-600 rounded-lg">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h3 class="font-display font-bold text-chocolate text-lg">Inventory Alerts</h3>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full border border-red-200">
                    {{ $lowStockItems->count() }} Critical
                </span>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                <table class="min-w-full divide-y divide-border-soft">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Item</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Level</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($lowStockItems as $item)
                            @php
                                $percentage = min(100, ($item['current_stock'] / max(1, $item['min_stock'])) * 100);
                                $statusColor = $percentage < 25 ? 'bg-red-500' : 'bg-amber-500';
                                $textColor = $percentage < 25 ? 'text-red-600' : 'text-amber-600';
                            @endphp
                            <tr class="hover:bg-cream-bg transition-colors group">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900 truncate max-w-[150px]" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-right align-middle">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-bold {{ $textColor }}">
                                            {{ $item['current_stock'] }} <span class="text-gray-400 font-normal">/ {{ $item['min_stock'] }}</span>
                                        </span>
                                        <div class="w-20 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full rounded-full {{ $statusColor }} transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-chocolate hover:text-caramel text-xs font-bold uppercase tracking-wide hover:underline decoration-caramel/30">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-check-circle text-3xl text-green-200 mb-2"></i>
                                    <p class="text-sm">Inventory levels are healthy.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- WIDGET 2: FINANCIAL COMMITMENTS --}}
        <div class="bg-white border border-border-soft rounded-2xl shadow-sm p-0 flex flex-col h-full overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-chocolate/5 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>
            
            <div class="p-6 flex-1 flex flex-col">
                <h3 class="font-display font-bold text-chocolate text-lg mb-6">Open Commitments</h3>
                
                <div class="mb-8">
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-2xl font-medium text-caramel">₱</span>
                        <span class="text-5xl font-display font-bold text-gray-900 tracking-tighter">{{ number_format($openPoValue, 2) }}</span>
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Active Value</p>
                </div>

                <div class="bg-cream-bg rounded-xl p-4 border border-border-soft mb-6">
                    <div class="flex items-center gap-3 text-chocolate font-medium">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm text-caramel">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span>{{ $openPoCount }} Active Purchase Orders</span>
                    </div>
                </div>

                <div class="space-y-4 mt-auto">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 font-medium">Pending Approval</span>
                        <div class="text-right">
                            <span class="font-mono font-bold text-gray-900">₱{{ number_format($openPoValue * 0.2, 2) }}</span>
                            <div class="w-24 h-1 bg-gray-100 rounded-full mt-1 ml-auto overflow-hidden">
                                <div class="h-full bg-amber-400 w-[20%]"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500 font-medium">Awaiting Goods</span>
                        <div class="text-right">
                            <span class="font-mono font-bold text-gray-900">₱{{ number_format($openPoValue * 0.8, 2) }}</span>
                            <div class="w-24 h-1 bg-gray-100 rounded-full mt-1 ml-auto overflow-hidden">
                                <div class="h-full bg-blue-500 w-[80%]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 border-t border-border-soft text-center">
                <a href="#" class="text-xs font-bold text-chocolate hover:text-caramel uppercase tracking-widest transition-colors">
                    View Financial Report &rarr;
                </a>
            </div>
        </div>

        {{-- WIDGET 3: DELIVERY EXCEPTION MONITOR --}}
        <div class="bg-white border-l-4 border-l-red-500 border-y border-r border-border-soft rounded-r-2xl shadow-sm flex flex-col h-full overflow-hidden">
            <div class="px-5 py-4 border-b border-border-soft bg-white flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-red-50 text-red-600 rounded-lg">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 text-lg">Overdue</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Exception Monitor</p>
                    </div>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">
                    {{ $overdueDeliveries->count() }} Late
                </span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                <div class="divide-y divide-gray-100">
                    @forelse($overdueDeliveries as $delivery)
                        <div class="p-4 hover:bg-red-50/30 transition-colors group">
                            <div class="flex justify-between items-start mb-1">
                                <div class="flex-1 min-w-0 pr-2">
                                    <h4 class="text-sm font-bold text-gray-900 truncate">{{ $delivery['supplier_name'] }}</h4>
                                    <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                        <span class="text-gray-400">Ref:</span> 
                                        <a href="#" class="font-mono text-chocolate hover:underline">{{ $delivery['po_number'] }}</a>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 whitespace-nowrap">
                                    +{{ max(0, $delivery['days_overdue']) }} Days
                                </span>
                            </div>
                            
                            <div class="mt-3 flex gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                @if($delivery['supplier_phone'])
                                    <a href="tel:{{ $delivery['supplier_phone'] }}" class="flex-1 inline-flex justify-center items-center px-2 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-600 bg-white hover:bg-gray-50 hover:text-chocolate transition-colors">
                                        <i class="fas fa-phone mr-1.5"></i> Call
                                    </a>
                                @endif
                                <a href="mailto:{{ $delivery['supplier_email'] }}" class="flex-1 inline-flex justify-center items-center px-2 py-1.5 border border-gray-200 shadow-sm text-xs font-bold rounded-lg text-gray-600 bg-white hover:bg-gray-50 hover:text-chocolate transition-colors">
                                    <i class="fas fa-envelope mr-1.5"></i> Email
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-10 text-gray-400">
                            <i class="fas fa-check-circle text-3xl mb-2 text-green-200"></i>
                            <p class="text-sm">All deliveries are on schedule.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e8dfd4; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #c48d3f; }
</style>
@endsection