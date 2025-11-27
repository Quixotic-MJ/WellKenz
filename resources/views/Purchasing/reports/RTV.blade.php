@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6 font-sans text-gray-600">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate">Return to Vendor (RTV) Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Track returned items, manage disputes, and monitor credit note status.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('purchasing.reports.history') }}" 
               class="flex items-center justify-center px-4 py-2 bg-white border border-border-soft text-chocolate rounded-lg hover:bg-cream-bg transition-all shadow-sm text-sm font-medium group">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i> Back to Reports
            </a>
        </div>
    </div>

    {{-- FILTERS CARD --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6">
        <div class="mb-4 pb-2 border-b border-border-soft">
            <h2 class="font-display text-lg font-semibold text-chocolate flex items-center">
                <i class="fas fa-filter mr-2 text-caramel"></i> Filter & Search
            </h2>
        </div>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Search --}}
            <div class="space-y-1">
                <label for="search" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Search</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" 
                           id="search"
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="RTV #, Supplier, Item..."
                           class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel transition-colors">
                </div>
            </div>

            {{-- Supplier --}}
            <div class="space-y-1">
                <label for="supplier_id" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Supplier</label>
                <select id="supplier_id" 
                        name="supplier_id" 
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel transition-colors bg-white">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="space-y-1">
                <label for="status" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Status</label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel transition-colors bg-white">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Credit</option>
                    <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>In Process</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Credit Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Date Range</label>
                <div class="flex items-center space-x-2">
                    <input type="date" 
                           id="date_from"
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel text-gray-600">
                    <span class="text-gray-400 font-serif italic">to</span>
                    <input type="date" 
                           id="date_to"
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel text-gray-600">
                </div>
            </div>

            {{-- Actions --}}
            <div class="lg:col-span-4 flex justify-end space-x-3 pt-2 border-t border-dashed border-gray-200 mt-2">
                <a href="{{ route('purchasing.reports.rtv') }}" 
                   class="px-5 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center">
                    <i class="fas fa-undo mr-1.5 text-xs"></i> Reset
                </a>
                <button type="submit" 
                        class="px-5 py-2 bg-chocolate text-white rounded-lg hover:bg-[#2e1e11] shadow-md transition-all text-sm font-medium flex items-center">
                    <i class="fas fa-search mr-1.5 text-xs"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    {{-- RTV TABLE CARD --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
            <h3 class="font-display text-lg font-semibold text-chocolate">
                RTV Transactions
            </h3>
            <div class="flex items-center gap-2">
                @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-caramel/10 text-caramel">
                        Filtered Results
                    </span>
                @endif
                @if($rtvRecords->total() > 0)
                    <span class="text-xs text-gray-500 font-medium bg-white px-2 py-1 rounded border border-border-soft">
                        {{ number_format($rtvRecords->total()) }} records
                    </span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($rtvRecords->count() > 0)
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">RTV Number</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">Original PO</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">Items / Reason</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-chocolate uppercase tracking-wider">Value</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-chocolate uppercase tracking-wider">Return Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($rtvRecords as $rtv)
                            <tr class="hover:bg-cream-bg/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono text-sm font-bold text-chocolate bg-cream-bg px-2 py-1 rounded w-fit border border-border-soft">
                                        #{{ $rtv->rtv_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-caramel hover:text-chocolate hover:underline cursor-pointer transition-colors">
                                        {{ $rtv->formatted_po_number ?: 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-800">{{ $rtv->supplier->name ?? 'Unknown Supplier' }}</span>
                                        @if($rtv->supplier && $rtv->supplier->contact_person)
                                            <span class="text-xs text-gray-500 flex items-center mt-0.5">
                                                <i class="fas fa-user-circle mr-1 text-gray-400"></i> {{ $rtv->supplier->contact_person }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5">
                                        @foreach($rtv->rtvItems->take(2) as $item)
                                            <div class="flex flex-col border-l-2 border-border-soft pl-2">
                                                <span class="text-sm font-medium text-gray-800">{{ $item->item_display }}</span>
                                                @if($loop->first)
                                                    <span class="text-xs text-gray-500 italic">Reason: {{ $item->reason_display }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if($rtv->rtvItems->count() > 2)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ $rtv->rtvItems->count() - 2 }} more items
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-chocolate">
                                        {{ $rtv->formatted_total_value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full border shadow-sm {{ $rtv->status_badge['class'] }}">
                                        {{ $rtv->status_badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-alt mr-2 text-caramel"></i>
                                        {{ $rtv->return_date_formatted }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center bg-white">
                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-box-open text-2xl text-border-soft"></i>
                    </div>
                    <h3 class="font-display text-lg font-medium text-chocolate mb-1">No RTV records found</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">
                        @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                            No records match your filters. <a href="{{ route('purchasing.reports.rtv') }}" class="text-caramel hover:text-chocolate underline font-medium">Clear all filters</a> to see all data.
                        @else
                            No return to vendor transactions have been recorded in the system yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>

        {{-- PAGINATION --}}
        @if($rtvRecords->total() > 0)
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50/50">
                {{ $rtvRecords->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection