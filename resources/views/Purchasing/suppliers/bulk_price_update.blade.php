@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bulk Price Update</h1>
            <p class="text-sm text-gray-500 mt-1">Update supplier item prices in bulk.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.suppliers.prices') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Price List
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <form id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <select id="supplierFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Items</label>
                    <input type="text" id="itemSearch" class="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm" placeholder="Search items...">
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="loadSupplierItems()" class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition shadow-sm">
                        <i class="fas fa-search mr-2"></i> Load Items
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Price Update Table --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Supplier Items</h3>
            <p class="text-sm text-gray-500 mt-1">Select items to update their pricing information</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="priceUpdateTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAllUpdate" onchange="toggleSelectAll()" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Unit Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Order Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead Time (Days)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="itemsTableBody">
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <div>Load items to start updating prices</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Update Actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200" id="updateActions" style="display: none;">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span id="selectedCount">0</span> items selected for update
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="clearForm()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i> Clear
                    </button>
                    <button type="button" onclick="submitBulkUpdate()" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition shadow-sm">
                        <i class="fas fa-save mr-2"></i> Update Prices
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
.modal-backdrop {
    z-index: 1050;
}

.modal {
    z-index: 1060;
}
</style>
@endpush

@push('scripts')
<!-- Loading Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="loadingModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-80 shadow-lg rounded-md bg-white text-center">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="loadingTitle">Loading</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="loadingMessage">Please wait while we process your request...</p>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="confirmModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex space-x-3 justify-center">
                    <button id="modalCancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button id="modalConfirmBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="successModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="successTitle">Success</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="successMessage">Operation completed successfully.</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="successOkBtn" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="errorModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="errorTitle">Error</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="errorMessage">An error occurred while processing your request.</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="errorOkBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let supplierItemsData = [];
let selectedItems = new Set();
let pendingAction = null;

function showLoadingModal(title, message) {
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingModal').style.display = 'block';
}

function hideLoadingModal() {
    document.getElementById('loadingModal').style.display = 'none';
}

function showConfirmModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmModal').style.display = 'block';
    pendingAction = onConfirm;
}

function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingAction = null;
}

function showSuccessModal(title, message) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').style.display = 'block';
}

function hideSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}

function showErrorModal(title, message) {
    document.getElementById('errorTitle').textContent = title;
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').style.display = 'block';
}

function hideErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
}

function loadSupplierItems() {
    const supplierId = document.getElementById('supplierFilter').value;
    
    if (!supplierId) {
        showErrorModal('Validation Error', 'Please select a supplier first.');
        return;
    }

    // Show loading state
    showLoadingModal('Loading Items', 'Loading supplier items...');
    document.getElementById('itemsTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <div>Loading supplier items...</div>
            </td>
        </tr>
    `;

    // Fetch items
    fetch(`/purchasing/suppliers/items-for-edit?supplier_id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();
            supplierItemsData = data;
            renderItemsTable();
        })
        .catch(error => {
            console.error('Error loading items:', error);
            hideLoadingModal();
            document.getElementById('itemsTableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <div>Error loading items. Please try again.</div>
                    </td>
                </tr>
            `;
        });
}

function renderItemsTable() {
    const searchTerm = document.getElementById('itemSearch').value.toLowerCase();
    const filteredItems = supplierItemsData.filter(item => 
        item.item_name.toLowerCase().includes(searchTerm) ||
        item.item_code.toLowerCase().includes(searchTerm)
    );

    if (filteredItems.length === 0) {
        document.getElementById('itemsTableBody').innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-search text-2xl mb-2"></i>
                    <div>No items found matching your criteria</div>
                </td>
            </tr>
        `;
        document.getElementById('updateActions').style.display = 'none';
        return;
    }

    let html = '';
    filteredItems.forEach(item => {
        const isSelected = selectedItems.has(item.id);
        html += `
            <tr class="hover:bg-gray-50 ${isSelected ? 'bg-blue-50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="item-checkbox rounded border-gray-300 text-amber-600 focus:ring-amber-500" 
                           value="${item.id}" ${isSelected ? 'checked' : ''}
                           onchange="toggleItemSelection(this, ${item.id})">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                    <div class="text-sm text-gray-500">${item.item_code}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.supplier_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(item.current_unit_price).toFixed(2)}/${item.unit_symbol}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" step="0.01" min="0.01" value="${item.current_unit_price}" 
                           class="price-input block w-24 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-amber-500 focus:border-amber-500"
                           data-item-id="${item.id}" onchange="validatePrice(this)">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" step="0.001" min="0.001" value="${item.current_min_order_qty}" 
                           class="min-qty-input block w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-amber-500 focus:border-amber-500"
                           data-item-id="${item.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" min="0" value="${item.current_lead_time}" 
                           class="lead-time-input block w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-amber-500 focus:border-amber-500"
                           data-item-id="${item.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <input type="checkbox" ${item.is_preferred ? 'checked' : ''} 
                           class="preferred-checkbox rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                           data-item-id="${item.id}">
                </td>
            </tr>
        `;
    });

    document.getElementById('itemsTableBody').innerHTML = html;
    updateSelectedCount();
    document.getElementById('updateActions').style.display = 'block';
}

function toggleItemSelection(checkbox, itemId) {
    if (checkbox.checked) {
        selectedItems.add(itemId);
        checkbox.closest('tr').classList.add('bg-blue-50');
    } else {
        selectedItems.delete(itemId);
        checkbox.closest('tr').classList.remove('bg-blue-50');
    }
    updateSelectedCount();
    
    // Update select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllUpdate');
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    selectAllCheckbox.checked = allCheckboxes.length === selectedItems.size;
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllUpdate');
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        const itemId = parseInt(checkbox.value);
        if (selectAllCheckbox.checked) {
            selectedItems.add(itemId);
            checkbox.closest('tr').classList.add('bg-blue-50');
        } else {
            selectedItems.delete(itemId);
            checkbox.closest('tr').classList.remove('bg-blue-50');
        }
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = selectedItems.size;
}

function validatePrice(input) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) {
        input.value = '0.01';
    }
}

function clearForm() {
    if (selectedItems.size > 0) {
        showConfirmModal(
            'Clear Form',
            'You have selected items that will be cleared. Do you want to continue?',
            () => {
                selectedItems.clear();
                document.getElementById('itemsTableBody').innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-info-circle text-2xl mb-2"></i>
                            <div>Load items to start updating prices</div>
                        </td>
                    </tr>
                `;
                document.getElementById('updateActions').style.display = 'none';
                document.getElementById('itemSearch').value = '';
                document.getElementById('supplierFilter').value = '';
            }
        );
    } else {
        selectedItems.clear();
        document.getElementById('itemsTableBody').innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-info-circle text-2xl mb-2"></i>
                    <div>Load items to start updating prices</div>
                </td>
            </tr>
        `;
        document.getElementById('updateActions').style.display = 'none';
        document.getElementById('itemSearch').value = '';
        document.getElementById('supplierFilter').value = '';
    }
}

function submitBulkUpdate() {
    if (selectedItems.size === 0) {
        showErrorModal('Validation Error', 'Please select at least one item to update.');
        return;
    }

    const updates = [];
    selectedItems.forEach(itemId => {
        const priceInput = document.querySelector(`input[data-item-id="${itemId}"].price-input`);
        const minQtyInput = document.querySelector(`input[data-item-id="${itemId}"].min-qty-input`);
        const leadTimeInput = document.querySelector(`input[data-item-id="${itemId}"].lead-time-input`);
        const preferredCheckbox = document.querySelector(`input[data-item-id="${itemId}"].preferred-checkbox`);

        if (priceInput && minQtyInput && leadTimeInput) {
            updates.push({
                supplier_item_id: itemId,
                unit_price: parseFloat(priceInput.value),
                minimum_order_quantity: parseFloat(minQtyInput.value),
                lead_time_days: parseInt(leadTimeInput.value),
                is_preferred: preferredCheckbox.checked
            });
        }
    });

    if (updates.length === 0) {
        showErrorModal('Validation Error', 'No valid updates to submit.');
        return;
    }

    // Show confirmation modal
    showConfirmModal(
        'Confirm Bulk Update',
        `Are you sure you want to update ${updates.length} item(s)? This action cannot be undone.`,
        () => performBulkUpdate(updates)
    );
}

function performBulkUpdate(updates) {
    // Show loading state
    showLoadingModal('Updating Prices', 'Updating supplier item prices...');
    
    const submitBtn = document.querySelector('button[onclick="submitBulkUpdate()"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

    // Submit updates
    fetch('/purchasing/suppliers/prices/bulk-update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ updates })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showSuccessModal('Update Successful', data.message);
            // Reset form after successful update
            setTimeout(() => {
                clearForm();
            }, 2000);
        } else {
            showErrorModal('Update Failed', 'Error: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoadingModal();
        showErrorModal('Network Error', 'An error occurred while updating prices. Please check your internet connection and try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Search functionality
document.getElementById('itemSearch').addEventListener('input', function() {
    if (supplierItemsData.length > 0) {
        renderItemsTable();
    }
});

// Modal event handlers
document.addEventListener('DOMContentLoaded', function() {
    // Confirmation modal handlers
    document.getElementById('modalCancelBtn').addEventListener('click', hideConfirmModal);
    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        if (pendingAction) {
            pendingAction();
            hideConfirmModal();
        }
    });
    
    // Success modal handlers
    document.getElementById('successOkBtn').addEventListener('click', hideSuccessModal);
    
    // Error modal handlers
    document.getElementById('errorOkBtn').addEventListener('click', hideErrorModal);
    
    // Close modals when clicking outside
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) hideConfirmModal();
    });
    
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) hideSuccessModal();
    });
    
    document.getElementById('errorModal').addEventListener('click', function(e) {
        if (e.target === this) hideErrorModal();
    });
    
    document.getElementById('loadingModal').addEventListener('click', function(e) {
        if (e.target === this) hideLoadingModal();
    });

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideConfirmModal();
            hideSuccessModal();
            hideErrorModal();
            hideLoadingModal();
        }
    });
});
</script>
@endpush
@endsection