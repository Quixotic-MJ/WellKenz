@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
            <p class="text-gray-600 mt-1">Generate and view inventory reports</p>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Low Stock Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Low Stock Report</h3>
                <div class="bg-yellow-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-600 text-sm mb-4">Items that have reached or fallen below reorder level</p>
            <button onclick="generateLowStockReport()" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Generate Report
            </button>
        </div>

        <!-- Expiry Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Expiry Report</h3>
                <div class="bg-orange-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-600 text-sm mb-4">Items approaching expiration date</p>
            <button onclick="generateExpiryReport()" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Generate Report
            </button>
        </div>

        <!-- Stock Card Report -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Stock Card</h3>
                <div class="bg-blue-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-600 text-sm mb-4">Individual item stock movement history</p>
            <button onclick="showStockCardModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Generate Report
            </button>
        </div>

        <!-- Transaction Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Transaction Summary</h3>
                <div class="bg-green-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-600 text-sm mb-4">Summary of all inventory transactions</p>
            <button onclick="generateTransactionSummary()" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Generate Report
            </button>
        </div>
    </div>

    <!-- Report Results -->
    <div id="report-results" class="mt-8 hidden">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Report Results</h3>
                    <button onclick="exportReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Export to PDF
                    </button>
                </div>
            </div>
            <div class="p-6" id="report-content">
                <!-- Report content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateLowStockReport() {
        fetch('{{ route("inventory.reports.low-stock") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReport('Low Stock Report', data.items);
                }
            })
            .catch(error => console.error('Error generating report:', error));
    }

    function generateExpiryReport() {
        fetch('{{ route("inventory.reports.expiry") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReport('Expiry Report', data.items);
                }
            })
            .catch(error => console.error('Error generating report:', error));
    }

    function generateTransactionSummary() {
        // Implement transaction summary
        alert('Transaction summary report will be implemented');
    }

    function showStockCardModal() {
        // This would show a modal to select an item for stock card report
        const itemId = prompt('Enter Item ID for stock card report:');
        if (itemId) {
            window.location.href = `{{ url('inventory/reports/stock-card') }}/${itemId}`;
        }
    }

    function displayReport(title, items) {
        const resultsDiv = document.getElementById('report-results');
        const contentDiv = document.getElementById('report-content');
        
        let content = `<h4 class="text-lg font-semibold mb-4">${title}</h4>`;
        content += `<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">`;
        content += `<thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock</th><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th></tr></thead>`;
        content += `<tbody class="bg-white divide-y divide-gray-200">`;
        
        if (items.length === 0) {
            content += `<tr><td colspan="3" class="px-4 py-4 text-center text-gray-500">No items found</td></tr>`;
        } else {
            items.forEach(item => {
                const status = item.item_stock <= item.reorder_level ? 'Low Stock' : 'Normal';
                const statusClass = item.item_stock <= item.reorder_level ? 'text-red-600' : 'text-green-600';
                content += `<tr><td class="px-4 py-2">${item.item_name}</td><td class="px-4 py-2">${item.item_stock}</td><td class="px-4 py-2 ${statusClass}">${status}</td></tr>`;
            });
        }
        
        content += `</tbody></table></div>`;
        contentDiv.innerHTML = content;
        resultsDiv.classList.remove('hidden');
    }

    function exportReport() {
        alert('Export functionality will be implemented');
    }
</script>
@endpush
@endsection