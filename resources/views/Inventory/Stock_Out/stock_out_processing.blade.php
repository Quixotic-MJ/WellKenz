@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock-Out Processing</h1>
            <p class="text-gray-600 mt-1">Process approved requisitions and issue items</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="refreshRequisitions()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Refresh
            </button>
        </div>
    </div>

    <!-- Process Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Requisitions</p>
                    <p class="text-2xl font-bold text-yellow-600" id="pending-requisitions">0</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clipboard-list text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Items to Issue</p>
                    <p class="text-2xl font-bold text-blue-600" id="items-to-issue">0</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-box-open text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed Today</p>
                    <p class="text-2xl font-bold text-green-600" id="completed-today">0</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Urgent Requests</p>
                    <p class="text-2xl font-bold text-red-600" id="urgent-requests">0</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" id="search-requisition" placeholder="Search by requisition reference or requester..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <select id="priority-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Priority</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
            </select>
            <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="partially_fulfilled">Partially Fulfilled</option>
            </select>
            <input type="date" id="date-from" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <input type="date" id="date-to" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Approved Requisitions</h3>
                <span class="text-sm text-gray-500" id="requisition-count">0 requisitions</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requisition Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="requisitions-table-body">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Loading requisitions...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock-Out Processing Modal -->
<div id="stockOutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="stockout-modal-title">Issue Items</h3>
                <button onclick="closeStockOutModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <form id="ar-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">AR Reference</label>
                            <input type="text" id="ar-ref" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remarks</label>
                            <textarea id="ar-remarks" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200" id="requisition-items-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody id="requisition-items-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Items will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeStockOutModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button onclick="processStockOut()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Issue Items & Create AR
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentRequisitionData = null;
    let currentRequisitionId = null;

    // Load requisitions when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadRequisitions();
        setupEventListeners();
        generateARRef();
    });

    function setupEventListeners() {
        const searchInput = document.getElementById('search-requisition');
        const priorityFilter = document.getElementById('priority-filter');
        const statusFilter = document.getElementById('status-filter');

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadRequisitions();
            }, 500);
        });

        priorityFilter.addEventListener('change', function() {
            loadRequisitions();
        });

        statusFilter.addEventListener('change', function() {
            loadRequisitions();
        });
    }

    function loadRequisitions() {
        fetch('{{ route("inventory.stock-out.requisitions") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRequisitions(data.requisitions);
                    updateSummaryStats(data.requisitions);
                }
            })
            .catch(error => {
                console.error('Error loading requisitions:', error);
                // Use sample data for demonstration
                const sampleData = [
                    {
                        req_id: 1,
                        req_ref: 'REQ-2024-001',
                        req_purpose: 'Office Supplies for Department',
                        req_date: '2024-11-15',
                        req_priority: 'normal',
                        requester_name: 'Jane Smith',
                        total_items: 3,
                        pending_items: 2,
                        ar_ref: null
                    },
                    {
                        req_id: 2,
                        req_ref: 'REQ-2024-002',
                        req_purpose: 'Urgent: Printer Maintenance',
                        req_date: '2024-11-16',
                        req_priority: 'urgent',
                        requester_name: 'John Doe',
                        total_items: 1,
                        pending_items: 1,
                        ar_ref: null
                    }
                ];
                displayRequisitions(sampleData);
                updateSummaryStats(sampleData);
            });
    }

    function displayRequisitions(requisitions) {
        const tbody = document.getElementById('requisitions-table-body');
        
        if (requisitions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No pending requisitions found</td></tr>';
            return;
        }

        tbody.innerHTML = requisitions.map(req => {
            const priorityClass = req.req_priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                 req.req_priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                 req.req_priority === 'normal' ? 'bg-blue-100 text-blue-800' :
                                 'bg-gray-100 text-gray-800';
            
            const canProcess = req.pending_items > 0 && !req.ar_ref;
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${req.req_ref}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${req.requester_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${req.req_purpose}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${priorityClass}">
                            ${req.req_priority.charAt(0).toUpperCase() + req.req_priority.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${req.total_items - req.pending_items}/${req.total_items} fulfilled
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(req.req_date).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 space-x-2">
                        ${canProcess ? 
                            `<button onclick="processRequisition(${req.req_id})" class="hover:text-green-600">Process</button>` :
                            `<button onclick="viewRequisition(${req.req_id})" class="hover:text-blue-600">View</button>`
                        }
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateSummaryStats(requisitions) {
        const pending = requisitions.filter(req => req.pending_items > 0).length;
        const totalItems = requisitions.reduce((sum, req) => sum + req.pending_items, 0);
        const completed = requisitions.filter(req => req.ar_ref).length;
        const urgent = requisitions.filter(req => req.req_priority === 'urgent').length;

        document.getElementById('pending-requisitions').textContent = pending;
        document.getElementById('items-to-issue').textContent = totalItems;
        document.getElementById('completed-today').textContent = completed;
        document.getElementById('urgent-requests').textContent = urgent;
        document.getElementById('requisition-count').textContent = `${requisitions.length} requisitions`;
    }

    function processRequisition(reqId) {
        currentRequisitionId = reqId;
        
        // Load requisition items
        fetch(`{{ url('inventory/stock-out/requisition-items') }}/${reqId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRequisitionItems(data.items);
                }
            })
            .catch(error => {
                console.error('Error loading requisition items:', error);
                // Use sample data
                const sampleItems = [
                    {
                        item_id: 1,
                        item_code: 'PAPER-A4',
                        requested_quantity: 5,
                        item_stock: 100,
                        reorder_level: 20,
                        req_item_status: 'pending'
                    },
                    {
                        item_id: 2,
                        item_code: 'PEN-BLUE',
                        requested_quantity: 10,
                        item_stock: 5,
                        reorder_level: 10,
                        req_item_status: 'pending'
                    }
                ];
                displayRequisitionItems(sampleItems);
            });
            
        document.getElementById('stockOutModal').classList.remove('hidden');
        generateARRef();
    }

    function displayRequisitionItems(items) {
        const tbody = document.getElementById('requisition-items-tbody');
        
        tbody.innerHTML = items.map(item => {
            const stockStatus = item.item_stock <= item.reorder_level ? 'Low Stock' : 'Available';
            const stockClass = item.item_stock <= item.reorder_level ? 'text-red-600' : 'text-green-600';
            const canIssue = item.requested_quantity <= item.item_stock;
            
            return `
                <tr>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${item.item_code}</div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm ${stockClass}">${item.item_stock}</div>
                        <div class="text-xs text-gray-500">${stockStatus}</div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">${item.requested_quantity}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="number" 
                               id="issue-qty-${item.item_id}" 
                               value="${canIssue ? item.requested_quantity : 0}" 
                               min="0" 
                               max="${Math.min(item.requested_quantity, item.item_stock)}"
                               ${!canIssue ? 'disabled' : ''}
                               class="w-20 border border-gray-300 rounded px-2 py-1 text-sm">
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                        ${canIssue ? 
                            `<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Can Issue</span>` :
                            `<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Insufficient Stock</span>`
                        }
                    </td>
                </tr>
            `;
        }).join('');
    }

    function generateARRef() {
        const now = new Date();
        const dateStr = now.getFullYear().toString() + 
                       (now.getMonth() + 1).toString().padStart(2, '0') + 
                       now.getDate().toString().padStart(2, '0');
        const timeStr = now.getTime().toString().slice(-6);
        const arRef = `AR-${dateStr}-${timeStr}`;
        document.getElementById('ar-ref').value = arRef;
    }

    function closeStockOutModal() {
        document.getElementById('stockOutModal').classList.add('hidden');
        currentRequisitionId = null;
        currentRequisitionData = null;
        document.getElementById('ar-form').reset();
        generateARRef();
    }

    function processStockOut() {
        const arRef = document.getElementById('ar-ref').value.trim();
        const arRemarks = document.getElementById('ar-remarks').value.trim();
        
        if (!arRef) {
            alert('Please enter AR reference.');
            return;
        }

        // Collect items to issue
        const items = [];
        const inputs = document.querySelectorAll('[id^="issue-qty-"]');
        
        inputs.forEach(input => {
            const qty = parseFloat(input.value) || 0;
            if (qty > 0 && !input.disabled) {
                const itemId = parseInt(input.id.replace('issue-qty-', ''));
                items.push({
                    item_id: itemId,
                    quantity: qty
                });
            }
        });

        if (items.length === 0) {
            alert('Please enter at least one quantity to issue.');
            return;
        }

        const formData = {
            ar_ref: arRef,
            ar_remarks: arRemarks,
            req_id: currentRequisitionId,
            items: items
        };

        // Submit to server
        fetch('{{ route("inventory.stock-out.process") }}', {
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
                alert('Items issued successfully! AR: ' + data.ar_ref);
                closeStockOutModal();
                loadRequisitions();
            } else {
                alert('Error processing stock-out: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing stock-out. Please try again.');
        });
    }

    function refreshRequisitions() {
        loadRequisitions();
    }

    function viewRequisition(reqId) {
        alert('View requisition functionality will be implemented for requisition ID: ' + reqId);
    }
</script>
@endpush
@endsection