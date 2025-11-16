@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock-In Processing</h1>
            <p class="text-gray-600 mt-1">Process received goods and update inventory levels</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="showBulkProcessModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                Bulk Process
            </button>
        </div>
    </div>

    <!-- Process Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Memos</p>
                    <p class="text-2xl font-bold text-yellow-600" id="pending-memos">0</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clipboard-list text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Items to Process</p>
                    <p class="text-2xl font-bold text-blue-600" id="items-to-process">0</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-boxes text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Value</p>
                    <p class="text-2xl font-bold text-green-600" id="total-value">₱0</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-peso-sign text-green-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" id="search-memo" placeholder="Search by memo reference or PO..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
            </select>
            <input type="date" id="date-from" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <input type="date" id="date-to" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
    </div>

    <!-- Stock-In Processing Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Delivery Memos</h3>
                <span class="text-sm text-gray-500" id="memo-count">0 memos</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memo Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="stockin-table-body">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading stock-in data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock-In Processing Modal -->
<div id="stockInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Process Stock-In</h3>
                <button onclick="closeStockInModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200" id="items-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordered Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Items will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeStockInModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button onclick="processStockIn()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Process Stock-In
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentStockInData = null;
    let currentMemoId = null;

    // Load stock-in data when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadStockInData();
        setupEventListeners();
    });

    function setupEventListeners() {
        const searchInput = document.getElementById('search-memo');
        const statusFilter = document.getElementById('status-filter');

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadStockInData();
            }, 500);
        });

        statusFilter.addEventListener('change', function() {
            loadStockInData();
        });
    }

    function loadStockInData() {
        // This would typically load delivery memos that need processing
        // For now, showing placeholder data structure
        const sampleData = [
            {
                memo_ref: 'MEMO-20241116-001',
                po_ref: 'PO-2024-001',
                received_date: '2024-11-16',
                items_count: 5,
                status: 'pending',
                received_by: 'John Doe',
                po_id: 1,
                memo_id: 1
            }
        ];
        
        displayStockInData(sampleData);
        updateSummaryStats(sampleData);
    }

    function displayStockInData(memos) {
        const tbody = document.getElementById('stockin-table-body');
        
        if (memos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No pending stock-in processing</td></tr>';
            return;
        }

        tbody.innerHTML = memos.map(memo => {
            const statusClass = memo.status === 'completed' ? 'bg-green-100 text-green-800' : 
                               memo.status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-gray-100 text-gray-800';
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${memo.memo_ref}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${memo.po_ref}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(memo.received_date).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${memo.items_count} items</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                            ${memo.status.charAt(0).toUpperCase() + memo.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${memo.received_by}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 space-x-2">
                        ${memo.status === 'pending' ? 
                            `<button onclick="processMemo(${memo.memo_id})" class="hover:text-green-600">Process</button>` :
                            `<button onclick="viewMemo(${memo.memo_id})" class="hover:text-blue-600">View</button>`
                        }
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateSummaryStats(memos) {
        const pending = memos.filter(memo => memo.status === 'pending').length;
        const totalItems = memos.reduce((sum, memo) => sum + memo.items_count, 0);
        const totalValue = 125000; // Placeholder value

        document.getElementById('pending-memos').textContent = pending;
        document.getElementById('items-to-process').textContent = totalItems;
        document.getElementById('total-value').textContent = `₱${totalValue.toLocaleString()}`;
        document.getElementById('memo-count').textContent = `${memos.length} memos`;
    }

    function processMemo(memoId) {
        currentMemoId = memoId;
        
        // Load items for this memo
        const sampleItems = [
            {
                item_name: 'Office Paper A4',
                ordered_quantity: 100,
                received_quantity: 0,
                unit_price: 150.00
            },
            {
                item_name: 'Ballpen Blue',
                ordered_quantity: 50,
                received_quantity: 0,
                unit_price: 25.00
            }
        ];
        
        displayStockInItems(sampleItems);
        document.getElementById('stockInModal').classList.remove('hidden');
    }

    function displayStockInItems(items) {
        const tbody = document.getElementById('items-tbody');
        
        tbody.innerHTML = items.map((item, index) => `
            <tr>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${item.item_name}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">${item.ordered_quantity}</td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <input type="number" 
                           id="received-qty-${index}" 
                           value="${item.ordered_quantity}" 
                           min="0" 
                           max="${item.ordered_quantity}"
                           class="w-20 border border-gray-300 rounded px-2 py-1 text-sm"
                           onchange="updateSubtotal(${index}, ${item.unit_price})">
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">₱${item.unit_price.toFixed(2)}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium" id="subtotal-${index}">₱${(item.ordered_quantity * item.unit_price).toFixed(2)}</td>
            </tr>
        `).join('');
    }

    function updateSubtotal(index, unitPrice) {
        const qty = parseFloat(document.getElementById(`received-qty-${index}`).value) || 0;
        const subtotal = qty * unitPrice;
        document.getElementById(`subtotal-${index}`).textContent = `₱${subtotal.toFixed(2)}`;
    }

    function closeStockInModal() {
        document.getElementById('stockInModal').classList.add('hidden');
        currentMemoId = null;
        currentStockInData = null;
    }

    function processStockIn() {
        // Collect form data
        const items = [];
        const inputs = document.querySelectorAll('[id^="received-qty-"]');
        
        inputs.forEach((input, index) => {
            const qty = parseFloat(input.value) || 0;
            if (qty > 0) {
                items.push({
                    item_id: index + 1, // This would be the actual item ID
                    received_quantity: qty,
                    unit_price: 150.00 // This would come from the data
                });
            }
        });

        if (items.length === 0) {
            alert('Please enter at least one received quantity.');
            return;
        }

        const formData = {
            memo_ref: 'MEMO-20241116-001', // This would come from the selected memo
            po_id: currentMemoId,
            items: items
        };

        // Submit to server
        fetch('{{ route("inventory.stock-in.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Stock-in processed successfully!');
                closeStockInModal();
                loadStockInData();
            } else {
                alert('Error processing stock-in: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing stock-in. Please try again.');
        });
    }

    function showBulkProcessModal() {
        alert('Bulk process functionality will be implemented');
    }

    function viewMemo(memoId) {
        alert('View memo functionality will be implemented for memo ID: ' + memoId);
    }
</script>
@endpush
@endsection