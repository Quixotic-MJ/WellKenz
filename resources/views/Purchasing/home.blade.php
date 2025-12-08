@extends('Purchasing.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-8 font-sans text-gray-600">
    
    {{-- HEADER --}}
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

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- To Order Card --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-chocolate/10 text-chocolate rounded-lg">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-chocolate text-lg">To Order</h3>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Approved PRs</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-display font-bold text-chocolate">{{ $kpis['to_order'] }}</div>
                    <div class="text-xs text-gray-400">requests</div>
                </div>
            </div>
            <div class="bg-cream-bg rounded-lg p-3 border border-border-soft">
                <p class="text-xs text-gray-600 font-medium">
                    <i class="fas fa-clock text-chocolate mr-1"></i>
                    Ready for Purchase Order conversion
                </p>
            </div>
        </div>

        {{-- Open Orders Card --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 text-lg">Open Orders</h3>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Active POs</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-display font-bold text-blue-600">{{ $kpis['open_orders'] }}</div>
                    <div class="text-xs text-gray-400">orders</div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                <p class="text-xs text-blue-700 font-medium">
                    <i class="fas fa-shipping-fast text-blue-600 mr-1"></i>
                    In transit or confirmed
                </p>
            </div>
        </div>

        {{-- Overdue Card --}}
        <div class="bg-white border-l-4 border-l-red-500 border-y border-r border-border-soft rounded-r-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 text-lg">Overdue</h3>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Late Deliveries</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-display font-bold text-red-600">{{ $kpis['overdue'] }}</div>
                    <div class="text-xs text-gray-400">orders</div>
                </div>
            </div>
            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                <p class="text-xs text-red-700 font-medium">
                    <i class="fas fa-calendar-times text-red-600 mr-1"></i>
                    Past expected delivery date
                </p>
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT: Main Section + Sidebar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- MAIN SECTION: Procurement Queue --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-border-soft rounded-xl shadow-sm">
                
                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-chocolate/10 text-chocolate rounded-lg">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <h2 class="font-display font-bold text-chocolate text-xl">Procurement Queue</h2>
                                <p class="text-xs text-gray-500">Approved Purchase Requests ready for ordering</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1 rounded-full border">
                            {{ $requestsToOrder->count() }} Total
                        </span>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">The Right Date</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($requestsToOrder as $request)
                                <tr class="hover:bg-cream-bg transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-chocolate/10 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-file-alt text-chocolate text-xs"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-bold text-gray-900">{{ $request['pr_number'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $request['item_count'] }} items • ₱{{ number_format($request['total_estimated_cost'], 2) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $request['department'] }}</div>
                                        <div class="text-xs text-gray-500">by {{ $request['requested_by'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $request['request_date']->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">
                                            <span class="font-medium {{ $request['days_open'] > 7 ? 'text-red-600' : ($request['days_open'] > 3 ? 'text-amber-600' : 'text-gray-500') }}">
                                                Requested {{ $request['days_open'] }} days ago
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $request['priority'] === 'urgent' ? 'bg-red-100 text-red-800' : 
                                               ($request['priority'] === 'high' ? 'bg-orange-100 text-orange-800' : 
                                               ($request['priority'] === 'normal' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800')) }}">
                                            {{ ucfirst($request['priority']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-chocolate hover:bg-caramel transition-colors">
                                            <i class="fas fa-plus mr-1"></i> Create PO
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-check-circle text-3xl text-green-200 mb-3"></i>
                                            <h3 class="text-sm font-medium text-gray-900 mb-1">All caught up!</h3>
                                            <p class="text-sm text-gray-500">No pending purchase requests to process.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SIDEBAR: Vendor Monitor --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-border-soft rounded-xl shadow-sm h-fit">
                
                {{-- Sidebar Header --}}
                <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <h2 class="font-display font-bold text-gray-900 text-lg">Vendor Monitor</h2>
                            <p class="text-xs text-gray-500">Active Purchase Orders</p>
                        </div>
                    </div>
                </div>

                {{-- Orders List --}}
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @forelse($activeOrders as $order)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ $order['supplier_name'] }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $order['po_number'] }}</div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $order['status'] === 'sent' ? 'bg-blue-100 text-blue-800' : 
                                       ($order['status'] === 'confirmed' ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800') }}">
                                    {{ ucfirst($order['status']) }}
                                </span>
                            </div>
                            
                            <div class="text-xs text-gray-600 mb-2">
                                ₱{{ number_format($order['grand_total'], 2) }} • {{ $order['item_count'] }} items
                            </div>
                            
                            {{-- The Right Date with Overdue Highlighting --}}
                            <div class="text-xs font-medium
                                {{ $order['is_overdue'] ? 'text-red-600' : 'text-gray-700' }}">
                                <i class="fas fa-calendar-alt mr-1 {{ $order['is_overdue'] ? 'text-red-500' : 'text-gray-400' }}"></i>
                                Expected: {{ $order['expected_delivery_date']->format('M d, Y') }}
                                @if($order['is_overdue'])
                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                        OVERDUE
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <i class="fas fa-truck text-2xl text-gray-200 mb-2"></i>
                            <p class="text-sm text-gray-500">No active orders to display.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection