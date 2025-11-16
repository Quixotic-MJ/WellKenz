@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory Dashboard</h1>
            <p class="text-gray-600 mt-1">Real-time inventory overview and stock management</p>
        </div>
        <div class="text-right text-sm text-gray-500">
            <div id="current-time"></div>
            <div id="current-date"></div>
        </div>
    </div>

    <!-- Dashboard Widgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Critical Stock Count -->
        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-600 text-sm font-medium">Critical Stock</p>
                    <p class="text-2xl font-bold text-red-700" id="critical-stock-count">-</p>
                    <p class="text-xs text-red-500 mt-1">≤ min stock level</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Low Stock Count -->
        <div class="bg-white rounded-lg shadow-sm border border-yellow-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">Low Stock</p>
                    <p class="text-2xl font-bold text-yellow-700" id="low-stock-count">-</p>
                    <p class="text-xs text-yellow-500 mt-1">≤ reorder level</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Expiring Soon Items -->
        <div class="bg-white rounded-lg shadow-sm border border-orange-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-600 text-sm font-medium">Expiring Soon</p>
                    <p class="text-2xl font-bold text-orange-700" id="expiring-items-count">-</p>
                    <p class="text-xs text-orange-500 mt-1">Within 30 days</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Incoming Deliveries -->
        <div class="bg-white rounded-lg shadow-sm border border-green-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">Incoming Deliveries</p>
                    <p class="text-2xl font-bold text-green-700" id="incoming-deliveries-count">-</p>
                    <p class="text-xs text-green-500 mt-1">Pending POs</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Today's Stock-In Transactions -->
        <div class="bg-white rounded-lg shadow-sm border border-blue-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Today's Stock-In</h3>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-700" id="todays-stock-in">-</p>
                    <p class="text-sm text-gray-500">Items received today</p>
                </div>
            </div>
            <div class="space-y-2" id="todays-stock-in-items">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>

        <!-- Weekly Goods Received Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-purple-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Weekly Summary</h3>
                <div class="text-right">
                    <p class="text-2xl font-bold text-purple-700" id="weekly-total">-</p>
                    <p class="text-sm text-gray-500">Last 7 days</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4" id="weekly-summary">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Recent Inventory Transactions</h3>
                <a href="{{ route('inventory.transactions.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View All Transactions →
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="recent-transactions-body">
                    @forelse($recentTx as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->trans_ref }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->item_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction->trans_type === 'in' ? 'bg-green-100 text-green-800' : ($transaction->trans_type === 'out' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($transaction->trans_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($transaction->trans_quantity, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ auth()->user()->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent transactions</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Recent transactions are limited to 10 items, no pagination needed --}}
    </div>
</div>

@push('scripts')
<script>
    // Update current time and date
    function updateDateTime() {
        const now = new Date();
        document.getElementById('current-time').textContent = now.toLocaleTimeString();
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Load dashboard data
    function loadDashboardData() {
        // Load dashboard statistics
        fetch('{{ route("inventory.dashboard.stats") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('critical-stock-count').textContent = data.critical_stock || 0;
                    document.getElementById('low-stock-count').textContent = data.low_stock || 0;
                    document.getElementById('expiring-items-count').textContent = data.expiring_items || 0;
                    document.getElementById('incoming-deliveries-count').textContent = data.incoming_deliveries || 0;
                    document.getElementById('todays-stock-in').textContent = data.todays_stock_in ? parseFloat(data.todays_stock_in).toFixed(0) : '0';
                    document.getElementById('weekly-total').textContent = data.weekly_total ? parseFloat(data.weekly_total).toFixed(0) : '0';
                }
            })
            .catch(error => console.error('Error loading dashboard stats:', error));

        // Load expiry alerts details
        fetch('{{ route("inventory.dashboard.expiry-alerts") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Expiry alerts:', data.items);
                }
            })
            .catch(error => console.error('Error loading expiry alerts:', error));

        // Load incoming deliveries details
        fetch('{{ route("inventory.dashboard.incoming-deliveries") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Incoming deliveries:', data.deliveries);
                }
            })
            .catch(error => console.error('Error loading incoming deliveries:', error));

        // Load weekly summary details
        fetch('{{ route("inventory.dashboard.weekly-summary") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Weekly summary:', data.summary);
                }
            })
            .catch(error => console.error('Error loading weekly summary:', error));
    }

    // Load data when page loads
    document.addEventListener('DOMContentLoaded', loadDashboardData);

    // Refresh data every 5 minutes
    setInterval(loadDashboardData, 300000);
</script>
@endpush
@endsection