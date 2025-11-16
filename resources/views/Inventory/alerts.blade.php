@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory Alerts</h1>
            <p class="text-gray-600 mt-1">Monitor critical inventory situations requiring attention</p>
        </div>
        <button onclick="refreshAlerts()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Refresh Alerts
        </button>
    </div>

    <!-- Alert Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Low Stock Alert -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-yellow-800">Low Stock Items</h3>
                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium">
                    {{ $lowStockItems->count() }} items
                </span>
            </div>
            @forelse($lowStockItems->take(5) as $item)
            <div class="flex justify-between items-center py-2 border-b border-yellow-200 last:border-b-0">
                <span class="text-sm text-yellow-700">{{ $item->item_name }}</span>
                <span class="text-sm font-medium text-yellow-800">{{ $item->item_stock }} {{ $item->item_unit }}</span>
            </div>
            @empty
            <p class="text-sm text-yellow-600">No low stock items</p>
            @endforelse
            @if($lowStockItems->count() > 5)
            <div class="mt-3 text-center">
                <a href="#" class="text-sm text-yellow-700 hover:text-yellow-800 font-medium">View all low stock items</a>
            </div>
            @endif
        </div>

        <!-- Critical Stock Alert -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-800">Critical Stock Items</h3>
                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-medium">
                    {{ $criticalStockItems->count() }} items
                </span>
            </div>
            @forelse($criticalStockItems->take(5) as $item)
            <div class="flex justify-between items-center py-2 border-b border-red-200 last:border-b-0">
                <span class="text-sm text-red-700">{{ $item->item_name }}</span>
                <span class="text-sm font-medium text-red-800">{{ $item->item_stock }} {{ $item->item_unit }}</span>
            </div>
            @empty
            <p class="text-sm text-red-600">No critical stock items</p>
            @endforelse
            @if($criticalStockItems->count() > 5)
            <div class="mt-3 text-center">
                <a href="#" class="text-sm text-red-700 hover:text-red-800 font-medium">View all critical stock items</a>
            </div>
            @endif
        </div>

        <!-- Expiring Items Alert -->
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-orange-800">Expiring Soon</h3>
                <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-sm font-medium">
                    {{ $expiringItems->count() }} items
                </span>
            </div>
            @forelse($expiringItems->take(5) as $item)
            <div class="flex justify-between items-center py-2 border-b border-orange-200 last:border-b-0">
                <span class="text-sm text-orange-700">{{ $item->item_name }}</span>
                <span class="text-sm font-medium text-orange-800">{{ $item->item_expire_date->format('M d, Y') }}</span>
            </div>
            @empty
            <p class="text-sm text-orange-600">No items expiring soon</p>
            @endforelse
            @if($expiringItems->count() > 5)
            <div class="mt-3 text-center">
                <a href="#" class="text-sm text-orange-700 hover:text-orange-800 font-medium">View all expiring items</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Detailed Alerts Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">All Alerts</h3>
                <div class="flex space-x-2">
                    <button onclick="exportAlerts()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Export Alerts
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alert Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Threshold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Low Stock Items -->
                    @forelse($lowStockItems as $item)
                    <tr class="bg-yellow-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-700 font-medium">{{ $item->item_stock }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->reorder_level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->item_expire_date ? $item->item_expire_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                            <button onclick="createReorderRequest({{ $item->item_id }})" class="hover:text-blue-800">Reorder</button>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    <!-- Critical Stock Items -->
                    @forelse($criticalStockItems as $item)
                    <tr class="bg-red-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700 font-medium">{{ $item->item_stock }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Critical Stock</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->min_stock_level }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->item_expire_date ? $item->item_expire_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            <button onclick="createUrgentReorderRequest({{ $item->item_id }})" class="hover:text-red-800">Urgent Reorder</button>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    <!-- Expiring Items -->
                    @forelse($expiringItems as $item)
                    <tr class="bg-orange-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->item_stock }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">Expiring Soon</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-700 font-medium">{{ $item->item_expire_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                            <button onclick="viewItem({{ $item->item_id }})" class="hover:text-orange-800">View</button>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($lowStockItems->isEmpty() && $criticalStockItems->isEmpty() && $expiringItems->isEmpty())
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No alerts at this time</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function refreshAlerts() {
        location.reload();
    }

    function createReorderRequest(itemId) {
        alert('Reorder request functionality will be implemented for item ID: ' + itemId);
    }

    function createUrgentReorderRequest(itemId) {
        alert('Urgent reorder request functionality will be implemented for item ID: ' + itemId);
    }

    function viewItem(itemId) {
        window.location.href = `{{ url('inventory/items') }}/${itemId}`;
    }

    function exportAlerts() {
        alert('Export alerts functionality will be implemented');
    }
</script>
@endpush
@endsection