@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Procurement Home</h1>
            <p class="text-sm text-gray-500">Purchasing overview for {{ date('F d, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('purchasing.po.create') }}" class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus-circle mr-2"></i> Create Purchase Order
            </a>
        </div>
    </div>

    {{-- 2. TOP WIDGETS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- WIDGET 1: LOW STOCK ALERTS (Triggers Buying) --}}
        <div class="bg-white border-t-4 border-red-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Low Stock Alerts</h3>
                    <p class="text-[10px] text-red-500 font-bold mt-0.5">
                        <i class="fas fa-arrow-down mr-1"></i> Below Reorder Level
                    </p>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">{{ $lowStockItems->count() }} Items</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                <ul class="space-y-2">
                    @forelse($lowStockItems as $item)
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div>
                            <span class="text-xs font-bold text-gray-700 block">{{ $item['name'] }}</span>
                            <span class="text-[10px] text-gray-500">Stock: <span class="font-bold text-red-600">{{ number_format($item['current_stock'], 0) }}</span> / Min: {{ number_format($item['min_stock'], 0) }}</span>
                        </div>
                    </li>
                    @empty
                    <li class="p-3 text-center text-gray-500 text-xs">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        All items are above reorder level
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- WIDGET 2: OPEN PO VALUE (Financial Commitment) --}}
        <div class="bg-white border-t-4 border-blue-500 rounded-lg shadow-sm p-5 flex flex-col justify-between h-full">
            <div>
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Open PO Value</h3>
                <p class="text-xs text-gray-500 mt-1">Total outgoing cash commitment</p>
            </div>
            <div class="text-center py-4">
                <span class="text-4xl font-black text-gray-800 tracking-tight">₱ {{ number_format($openPoValue, 2) }}</span>
                <div class="flex justify-center items-center gap-2 mt-2">
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">{{ $openPoCount }} Active POs</span>
                </div>
            </div>
            <div class="mt-2 text-center">
                <p class="text-[10px] text-gray-400">Based on Approved & Sent orders awaiting delivery.</p>
            </div>
        </div>

        {{-- WIDGET 3: OVERDUE DELIVERIES (The Shame List) --}}
        <div class="bg-white border-t-4 border-amber-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Overdue Deliveries</h3>
                    <p class="text-[10px] text-amber-600 font-bold mt-0.5">
                        <i class="fas fa-clock mr-1"></i> Late Suppliers
                    </p>
                </div>
                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full">{{ $overdueDeliveries->count() }} Late</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                <ul class="space-y-2">
                    @forelse($overdueDeliveries as $delivery)
                    <li class="p-3 bg-amber-50 rounded border-l-4 border-amber-400 relative group">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-800 block">{{ $delivery['supplier_name'] }}</span>
                                <span class="text-[10px] text-gray-500 font-mono">PO #{{ $delivery['po_number'] }}</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-white px-1.5 rounded border border-red-100">
                                +{{ max(0, $delivery['days_overdue']) }} Day{{ max(0, $delivery['days_overdue']) > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="text-[10px] text-gray-600">
                                <i class="fas fa-user mr-1"></i>{{ $delivery['supplier_contact_person'] ?? 'N/A' }}
                            </div>
                            @if($delivery['supplier_phone'])
                            <div class="text-[10px] text-blue-600">
                                <i class="fas fa-phone mr-1"></i>{{ $delivery['supplier_phone'] }}
                            </div>
                            @endif
                            @if($delivery['supplier_email'])
                            <div class="text-[10px] text-green-600">
                                <i class="fas fa-envelope mr-1"></i>{{ $delivery['supplier_email'] }}
                            </div>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="p-3 text-center text-gray-500 text-xs">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        No overdue deliveries
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>

    {{-- 3. RECENT ACTIVITY / QUICK ACTIONS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Recent POs List --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-800 uppercase">Recent Purchase Orders</h3>
                <a href="{{ route('purchasing.po.history') }}" class="text-xs text-blue-600 hover:underline font-medium">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentPurchaseOrders as $order)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                            PO
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">#{{ $order['po_number'] }} <span class="text-gray-400 font-normal mx-1">•</span> {{ $order['supplier_name'] }}</p>
                            <p class="text-xs text-gray-500">Items: {{ $order['item_count'] }} • Total: ₱ {{ number_format($order['total_amount'], 2) }}</p>
                        </div>
                    </div>
                    @php
                        $statusClass = match($order['status']) {
                            'draft' => 'bg-gray-100 text-gray-600',
                            'sent', 'confirmed' => 'bg-yellow-100 text-yellow-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-blue-100 text-blue-800'
                        };
                        
                        $statusText = match($order['status']) {
                            'draft' => 'Draft',
                            'sent' => 'Sent',
                            'confirmed' => 'Confirmed', 
                            'partial' => 'Partial Delivery',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            default => ucfirst($order['status'])
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500 text-sm">
                    <i class="fas fa-inbox text-gray-300 text-2xl mb-2"></i>
                    <p>No recent purchase orders</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Vendor Search --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Vendor Lookup</h3>
                <div class="relative">
                    <input type="text" id="supplier-search" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Find supplier...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                </div>
                
                <script>
                document.getElementById('supplier-search').addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const supplierItems = document.querySelectorAll('[data-supplier-name]');
                    
                    supplierItems.forEach(function(item) {
                        const supplierName = item.getAttribute('data-supplier-name').toLowerCase();
                        if (supplierName.includes(searchTerm) || searchTerm === '') {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
                </script>
                <div class="mt-4 space-y-2">
                    <p class="text-[10px] text-gray-400 uppercase font-bold">Frequent Contacts</p>
                    @forelse($frequentSuppliers as $supplier)
                    <a href="#" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded transition group" data-supplier-name="{{ $supplier['name'] }}">
                        <span class="text-sm text-gray-700 group-hover:text-chocolate">{{ $supplier['name'] }}</span>
                        <i class="fas fa-phone text-xs text-gray-300 group-hover:text-chocolate"></i>
                    </a>
                    @empty
                    <p class="text-sm text-gray-500">No frequent suppliers found</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection