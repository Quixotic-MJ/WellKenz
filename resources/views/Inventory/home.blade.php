@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6 font-sans text-gray-600">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Warehouse Operations</h1>
            <p class="text-sm text-gray-500 flex items-center gap-2">
                <span>Daily operations overview for {{ date('F d, Y') }}</span>
                <span class="text-border-soft">|</span>
                <span class="text-caramel font-bold bg-cream-bg px-2 py-0.5 rounded-md border border-border-soft">Operational Focus</span>
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.inbound.receive') }}" class="flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg">
                <i class="fas fa-truck-loading mr-2"></i> Receive Delivery
            </a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">To Pack</p>
                    <p class="text-3xl font-bold text-chocolate mt-2">{{ $to_pack }}</p>
                    <p class="text-xs text-gray-500 mt-1">Approved requisitions ready for picking</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box-open text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Incoming</p>
                    <p class="text-3xl font-bold text-chocolate mt-2">{{ $incoming_pos }}</p>
                    <p class="text-xs text-gray-500 mt-1">Purchase orders confirmed/partial</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-truck text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Expiring Soon</p>
                    <p class="text-3xl font-bold text-chocolate mt-2">{{ $expiring_soon }}</p>
                    <p class="text-xs text-gray-500 mt-1">Batches expiring within 30 days</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT: MAIN (2/3) + SIDEBAR (1/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- MAIN SECTION: READY FOR PICKING --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="font-display text-xl font-bold text-chocolate">Ready for Picking</h2>
                    <p class="text-sm text-gray-600 mt-1">FIFO - First In, First Out order</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pickList as $requisition)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $requisition->requisition_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $requisition->requestedBy->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $requisition->department }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $requisition->requisitionItems->count() }} items
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $requisition->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('inventory.outbound.fulfill') }}?requisition={{ $requisition->id }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-chocolate hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate transition-all">
                                            <i class="fas fa-hand-paper mr-1"></i> Pick
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-check-circle text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium text-gray-900 mb-1">All caught up!</p>
                                            <p class="text-sm text-gray-500">No requisitions ready for picking at the moment.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SIDEBAR: INCOMING DELIVERIES --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="font-display text-xl font-bold text-chocolate">Incoming Deliveries</h2>
                    <p class="text-sm text-gray-600 mt-1">Expected deliveries</p>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @forelse($incomingOrders as $order)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $order->supplier->name ?? 'Unknown Supplier' }}</h3>
                                    <p class="text-xs text-gray-600 font-mono">PO: {{ $order->po_number }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    @if($order->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($order->status === 'partial') bg-amber-100 text-amber-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $order->expected_delivery_date ? \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d, Y') : 'TBD' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $order->purchaseOrderItems ? $order->purchaseOrderItems->count() : 0 }} items
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-truck text-3xl text-gray-300 mb-3"></i>
                                <p class="text-sm text-gray-500">No incoming deliveries</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                
                @if($incomingOrders->count() > 0)
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('purchasing.purchase-orders.index') }}" class="text-sm font-medium text-chocolate hover:text-chocolate-dark">
                        View all purchase orders →
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection