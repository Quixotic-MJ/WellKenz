@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventory Transactions</h1>
            <p class="text-gray-600 mt-1">View and manage all inventory transactions</p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">All Transactions</h3>
                <div class="flex space-x-2">
                    <button onclick="exportTransactions()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Export
                    </button>
                </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="transactions-body">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading transactions...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="pagination" class="px-6 py-3 border-t border-gray-200">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentPage = 1;
    
    function loadTransactions(page = 1) {
        currentPage = page;
        fetch(`{{ route('inventory.transactions.list') }}?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTransactions(data.transactions.data);
                    displayPagination(data.transactions);
                }
            })
            .catch(error => {
                console.error('Error loading transactions:', error);
                document.getElementById('transactions-body').innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading transactions</td></tr>';
            });
    }

    function displayTransactions(transactions) {
        const tbody = document.getElementById('transactions-body');
        
        if (transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No transactions found</td></tr>';
            return;
        }

        tbody.innerHTML = transactions.map(transaction => {
            const typeClass = transaction.trans_type === 'in' ? 'bg-green-100 text-green-800' : 
                             (transaction.trans_type === 'out' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
            
            return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${transaction.trans_ref}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.item ? transaction.item.item_name : 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${typeClass}">
                            ${transaction.trans_type.charAt(0).toUpperCase() + transaction.trans_type.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(transaction.trans_quantity).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(transaction.created_at).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${transaction.user ? transaction.user.name : 'System'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${transaction.trans_remarks || '-'}</td>
                </tr>
            `;
        }).join('');
    }

    function displayPagination(pagination) {
        const paginationDiv = document.getElementById('pagination');
        
        if (!pagination.has_pages) {
            paginationDiv.innerHTML = '';
            return;
        }

        let paginationHTML = '<div class="flex justify-between items-center">';
        paginationHTML += `<div class="text-sm text-gray-500">Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results</div>`;
        paginationHTML += '<div class="flex space-x-1">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHTML += `<button onclick="loadTransactions(${pagination.current_page - 1})" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = start; i <= end; i++) {
            const activeClass = i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
            paginationHTML += `<button onclick="loadTransactions(${i})" class="px-3 py-1 text-sm border border-gray-300 rounded ${activeClass}">${i}</button>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHTML += `<button onclick="loadTransactions(${pagination.current_page + 1})" class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">Next</button>`;
        }
        
        paginationHTML += '</div></div>';
        paginationDiv.innerHTML = paginationHTML;
    }

    function exportTransactions() {
        // Implement export functionality
        alert('Export functionality will be implemented');
    }

    // Load transactions when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadTransactions();
    });
</script>
@endpush
@endsection