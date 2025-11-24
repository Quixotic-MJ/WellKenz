@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase History</h1>
            <p class="text-sm text-gray-500 mt-1">Archive of all completed and fully delivered orders.</p>
        </div>
        <div class="flex items-center space-x-3">
            <input type="text" id="searchInput" placeholder="Search history..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-chocolate focus:border-chocolate">
            <button class="bg-white border border-gray-300 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-50" onclick="applyFilters()">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <button class="bg-chocolate text-white px-3 py-2 rounded-md text-sm hover:bg-chocolate-dark" onclick="exportHistory()">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>

    {{-- HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Completed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="ordersTableBody">
                @forelse($completedOrders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->actual_delivery_date ? $order->actual_delivery_date->format('M d, Y') : $order->updated_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">
                        #{{ $order->po_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex flex-col">
                            <span>{{ $order->supplier->name ?? 'Unknown Supplier' }}</span>
                            @if($order->supplier && $order->supplier->contact_person)
                                <span class="text-xs text-gray-500">{{ $order->supplier->contact_person }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                        ₱ {{ number_format($order->grand_total ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-600">
                            Completed
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('purchasing.po.show', $order->id) }}" class="text-chocolate hover:text-chocolate-dark" title="View Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('purchasing.po.print', $order->id) }}" class="text-gray-600 hover:text-gray-800" title="Print PO">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No completed orders found</h3>
                            <p class="text-gray-500 text-sm">Completed purchase orders will appear here once they are fully delivered.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($completedOrders->hasPages())
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $completedOrders->firstItem() ?? 0 }} to {{ $completedOrders->lastItem() ?? 0 }} of {{ $completedOrders->total() }} results
            </div>
            <div class="flex items-center space-x-1">
                {{ $completedOrders->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
    @endif

    {{-- SUMMARY STATISTICS --}}
    @if($completedOrders->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-shopping-cart text-chocolate text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $completedOrders->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Value</p>
                    <p class="text-2xl font-semibold text-gray-900">₱ {{ number_format($completedOrders->sum('grand_total'), 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-truck text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $completedOrders->filter(function($order) { 
                            return $order->actual_delivery_date && $order->actual_delivery_date->isCurrentMonth(); 
                        })->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-users text-purple-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Suppliers</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $completedOrders->pluck('supplier_id')->unique()->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function applyFilters() {
    const searchQuery = document.getElementById('searchInput').value;
    // For now, just refresh the page with search parameter
    // In a real implementation, you'd use AJAX to filter results
    const url = new URL(window.location);
    if (searchQuery) {
        url.searchParams.set('search', searchQuery);
    } else {
        url.searchParams.delete('search');
    }
    window.location = url.toString();
}

function exportHistory() {
    // Implement export functionality
    const searchQuery = document.getElementById('searchInput').value;
    const url = new URL('{{ route("purchasing.po.history") }}');
    if (searchQuery) {
        url.searchParams.set('search', searchQuery);
    }
    url.searchParams.set('export', 'csv');
    window.location = url.toString();
}

// Search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endpush
@endsection