@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Incoming Deliveries</h1>
            <p class="text-gray-600 mt-1">Manage and process purchase orders waiting for delivery confirmation</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="refreshDeliveries()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Refresh
            </button>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total POs</p>
                    <p class="text-2xl font-bold text-blue-600" id="total-pos">0</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-file-invoice text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">On Time</p>
                    <p class="text-2xl font-bold text-green-600" id="on-time-count">0</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Overdue</p>
                    <p class="text-2xl font-bold text-red-600" id="overdue-count">0</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Due Today</p>
                    <p class="text-2xl font-bold text-yellow-600" id="due-today-count">0</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-calendar-day text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" id="search-po" placeholder="Search PO reference or supplier..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="on-time">On Time</option>
                <option value="overdue">Overdue</option>
                <option value="due-today">Due Today</option>
            </select>
            <input type="date" id="date-from" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <input type="date" id="date-to" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Purchase Orders</h3>
                <span class="text-sm text-gray-500" id="po-count">0 orders</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="deliveries-table-body">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading deliveries...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentFilters = {
        search: '',
        status: '',
        dateFrom: '',
        dateTo: ''
    };

    // Load deliveries when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDeliveries();
        setupEventListeners();
    });

    function setupEventListeners() {
        const searchInput = document.getElementById('search-po');
        const statusFilter = document.getElementById('status-filter');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentFilters.search = this.value;
                loadDeliveries();
            }, 500);
        });

        statusFilter.addEventListener('change', function() {
            currentFilters.status = this.value;
            loadDeliveries();
        });

        dateFrom.addEventListener('change', function() {
            currentFilters.dateFrom = this.value;
            loadDeliveries();
        });

        dateTo.addEventListener('change', function() {
            currentFilters.dateTo = this.value;
            loadDeliveries();
        });
    }

    function loadDeliveries() {
        const params = new URLSearchParams(currentFilters);
        
        fetch(`{{ route('inventory.deliveries.incoming.api') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayDeliveries(data.deliveries);
                    updateSummaryStats(data.deliveries);
                }
            })
            .catch(error => {
                console.error('Error loading deliveries:', error);
                document.getElementById('deliveries-table-body').innerHTML = 
                    '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading deliveries</td></tr>';
            });
    }

    function displayDeliveries(deliveries) {
        const tbody = document.getElementById('deliveries-table-body');
        
        if (deliveries.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No incoming deliveries found</td></tr>';
            return;
        }

        tbody.innerHTML = deliveries.map(po => {
            const statusClass = po.status === 'on-time' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const overdueClass = po.days_overdue > 0 ? 'text-red-600 font-medium' : 'text-gray-500';
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${po.po_ref}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${po.supplier}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(po.expected_delivery_date).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(po.total_amount).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                            ${po.status === 'on-time' ? 'On Time' : 'Overdue'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm ${overdueClass}">
                        ${po.days_overdue > 0 ? po.days_overdue + ' days' : '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 space-x-2">
                        <button onclick="viewPODetails(${po.po_id})" class="hover:text-blue-800">View</button>
                        <button onclick="receiveDelivery(${po.po_id})" class="hover:text-green-600">Receive</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateSummaryStats(deliveries) {
        const total = deliveries.length;
        const onTime = deliveries.filter(po => po.status === 'on-time').length;
        const overdue = deliveries.filter(po => po.status === 'overdue').length;
        
        const today = new Date().toISOString().split('T')[0];
        const dueToday = deliveries.filter(po => po.expected_delivery_date === today).length;

        document.getElementById('total-pos').textContent = total;
        document.getElementById('on-time-count').textContent = onTime;
        document.getElementById('overdue-count').textContent = overdue;
        document.getElementById('due-today-count').textContent = dueToday;
        document.getElementById('po-count').textContent = `${total} orders`;
    }

    function viewPODetails(poId) {
        // Open PO details in a modal or new window
        window.open(`/admin/purchase-orders/view/${poId}`, '_blank');
    }

    function receiveDelivery(poId) {
        // Navigate to delivery receive page
        window.location.href = `{{ url('inventory/delivery/memo') }}/${poId}`;
    }

    function refreshDeliveries() {
        loadDeliveries();
    }

    function showNotification(message, type) {
        // Simple notification system
        const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${alertClass} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
@endsection