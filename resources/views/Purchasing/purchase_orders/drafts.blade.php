@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Draft Purchase Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and process draft purchase orders</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Create New PO
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-green-800">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-red-800">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                <input type="text" id="searchFilter" placeholder="PO Number or Supplier..." class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Supplier</label>
                <select id="supplierFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                    <option value="">All Suppliers</option>
                    @foreach($draftOrders->pluck('supplier.name')->unique()->filter() as $supplierName)
                        <option value="{{ $supplierName }}">{{ $supplierName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Date Range</label>
                <select id="dateFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Draft Orders Table --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source PR</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="ordersTableBody">
                    @forelse($draftOrders as $order)
                        <tr class="hover:bg-gray-50 po-row" data-po="{{ strtolower($order->po_number) }}" data-supplier="{{ strtolower($order->supplier->name ?? '') }}" data-date="{{ $order->order_date->format('Y-m-d') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_orders[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->po_number }}</div>
                                <div class="text-sm text-gray-500">Draft</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->supplier->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $order->supplier->contact_person ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->purchaseOrderItems->count() }} items</div>
                                <div class="text-sm text-gray-500">
                                    {{ $order->purchaseOrderItems->sum('quantity_ordered') }} total qty
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">₱{{ number_format($order->grand_total, 2) }}</div>
                                <div class="text-sm text-gray-500">{{ $order->purchaseOrderItems->count() }} items</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->purchase_request_id)
                                    <div class="text-sm text-gray-900">{{ $order->purchaseRequest->pr_number ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->purchaseRequest->department ?? '' }}</div>
                                @else
                                    <span class="text-sm text-gray-400">Direct Creation</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $order->order_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($order->expected_delivery_date)
                                    {{ $order->expected_delivery_date->format('M d, Y') }}
                                    @if($order->expected_delivery_date->isPast())
                                        <span class="text-red-600 text-xs">(Overdue)</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="editOrder({{ $order->id }})" class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="submitOrder({{ $order->id }})" class="text-green-600 hover:text-green-900" title="Submit for Approval">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    <button onclick="viewOrder({{ $order->id }})" class="text-gray-600 hover:text-gray-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="deleteOrder({{ $order->id }})" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 block"></i>
                                    <p class="text-lg font-medium">No draft purchase orders found</p>
                                    <p class="text-sm mt-1">Create a new purchase order to get started</p>
                                    <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center justify-center px-4 py-2 mt-4 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition">
                                        <i class="fas fa-plus mr-2"></i> Create New PO
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($draftOrders->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $draftOrders->links() }}
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-sm text-gray-700">
                <span id="selectedCount">0</span> order(s) selected
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="bulkSubmit()" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition" disabled id="bulkSubmitBtn">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Selected
                </button>
                <button onclick="bulkDelete()" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition" disabled id="bulkDeleteBtn">
                    <i class="fas fa-trash mr-2"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Filter functionality
function setupFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const supplierFilter = document.getElementById('supplierFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    [searchFilter, supplierFilter, dateFilter].forEach(filter => {
        filter.addEventListener('input', applyFilters);
        filter.addEventListener('change', applyFilters);
    });
}

function applyFilters() {
    const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
    const supplierTerm = document.getElementById('supplierFilter').value.toLowerCase();
    const dateTerm = document.getElementById('dateFilter').value;
    
    const rows = document.querySelectorAll('.po-row');
    
    rows.forEach(row => {
        const poText = row.dataset.po;
        const supplierText = row.dataset.supplier;
        const dateText = row.dataset.date;
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !poText.includes(searchTerm) && !supplierText.includes(searchTerm)) {
            showRow = false;
        }
        
        // Supplier filter
        if (supplierTerm && !supplierText.includes(supplierTerm)) {
            showRow = false;
        }
        
        // Date filter
        if (dateTerm) {
            const today = new Date();
            const rowDate = new Date(dateText);
            
            switch(dateTerm) {
                case 'today':
                    showRow = rowDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    showRow = rowDate >= weekAgo;
                    break;
                case 'month':
                    const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                    showRow = rowDate >= monthAgo;
                    break;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchFilter').value = '';
    document.getElementById('supplierFilter').value = '';
    document.getElementById('dateFilter').value = '';
    applyFilters();
}

// Selection functionality
function setupSelection() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectionUI();
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });
}

function updateSelectionUI() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const selectedCount = document.querySelectorAll('.order-checkbox:checked').length;
    const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectAll = document.getElementById('selectAll');
    
    document.getElementById('selectedCount').textContent = selectedCount;
    
    const allChecked = selectedCount === checkboxes.length && checkboxes.length > 0;
    const noneChecked = selectedCount === 0;
    
    selectAll.checked = allChecked;
    selectAll.indeterminate = !allChecked && !noneChecked;
    
    bulkSubmitBtn.disabled = noneChecked;
    bulkDeleteBtn.disabled = noneChecked;
}

// Action functions
function editOrder(orderId) {
    // This would typically open an edit modal or redirect to edit page
    alert('Edit functionality would be implemented here for order ID: ' + orderId);
}

function submitOrder(orderId) {
    if (confirm('Are you sure you want to submit this purchase order for approval?')) {
        // Submit via form or AJAX
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/purchasing/po/${orderId}/submit`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = csrfToken;
        
        form.appendChild(methodField);
        form.appendChild(csrfField);
        document.body.appendChild(form);
        form.submit();
    }
}

function viewOrder(orderId) {
    // This would open a view modal or redirect to view page
    alert('View functionality would be implemented here for order ID: ' + orderId);
}

function deleteOrder(orderId) {
    if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
        // Submit delete via form or AJAX
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/purchasing/po/${orderId}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = csrfToken;
        
        form.appendChild(methodField);
        form.appendChild(csrfField);
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkSubmit() {
    const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one order to submit.');
        return;
    }
    
    if (confirm(`Are you sure you want to submit ${selectedIds.length} purchase order(s) for approval?`)) {
        // Submit selected orders
        console.log('Submitting orders:', selectedIds);
        // Implementation would depend on backend support for bulk operations
    }
}

function bulkDelete() {
    const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one order to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedIds.length} purchase order(s)? This action cannot be undone.`)) {
        // Delete selected orders
        console.log('Deleting orders:', selectedIds);
        // Implementation would depend on backend support for bulk operations
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    setupSelection();
    updateSelectionUI();
});
</script>
@endpush