@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Return to Vendor (RTV) Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Track returned items and credit note status.</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('purchasing.reports.history') }}" 
               class="flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shadow-sm text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reports
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       id="search"
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="RTV #, Supplier, Item, PO #..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="supplier_id" class="block text-xs font-medium text-gray-700 mb-1">Supplier</label>
                <select id="supplier_id" 
                        name="supplier_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Credit</option>
                    <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>In Process</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Credit Received</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" 
                       id="date_from"
                       name="date_from" 
                       value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" 
                       id="date_to"
                       name="date_to" 
                       value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-3 flex space-x-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm font-medium">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('purchasing.reports.rtv') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition text-sm font-medium">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>
    </div>

    {{-- RTV TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-900">
                    RTV Transactions 
                    @if($rtvRecords->total() > 0)
                        <span class="text-xs text-gray-500">({{ number_format($rtvRecords->total()) }} total)</span>
                    @endif
                </h3>
                @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                    <span class="text-xs text-blue-600">Filtered Results</span>
                @endif
            </div>
        </div>

        @if($rtvRecords->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RTV Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Original PO</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items / Reason</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($rtvRecords as $rtv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900 font-bold">
                                #{{ $rtv->rtv_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-blue-600">
                                {{ $rtv->formatted_po_number ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>
                                    <div class="font-medium">{{ $rtv->supplier->name ?? 'Unknown Supplier' }}</div>
                                    @if($rtv->supplier && $rtv->supplier->contact_person)
                                        <div class="text-xs text-gray-500">{{ $rtv->supplier->contact_person }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @foreach($rtv->rtvItems->take(2) as $item)
                                        <div class="text-sm font-medium text-gray-900">{{ $item->item_display }}</div>
                                        @if($loop->first)
                                            <div class="text-xs text-gray-500 italic">{{ $item->reason_display }}</div>
                                        @endif
                                    @endforeach
                                    @if($rtv->rtvItems->count() > 2)
                                        <div class="text-xs text-blue-600">+{{ $rtv->rtvItems->count() - 2 }} more items</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                {{ $rtv->formatted_total_value }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $rtv->status_badge['class'] }}">
                                    {{ $rtv->status_badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $rtv->return_date_formatted }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $rtvRecords->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-500 mb-2">
                    <i class="fas fa-inbox text-4xl"></i>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No RTV records found</h3>
                <p class="text-xs text-gray-500">
                    @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                        No records match your current filters.
                        <a href="{{ route('purchasing.reports.rtv') }}" class="text-blue-600 hover:text-blue-500">Clear filters</a>
                    @else
                        No return to vendor transactions have been recorded yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection