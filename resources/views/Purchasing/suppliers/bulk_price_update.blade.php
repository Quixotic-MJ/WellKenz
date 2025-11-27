@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600 pb-24">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Bulk Price Update</h1>
            <p class="text-sm text-gray-500">Update supplier item prices, minimums, and lead times in bulk.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.suppliers.prices') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-arrow-left mr-2 opacity-70 group-hover:opacity-100"></i> Back to Price List
            </a>
        </div>
    </div>

    {{-- 2. FILTERS & LOADING CONTROL --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            
            {{-- Supplier Select --}}
            <div>
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">Select Supplier</label>
                <div class="relative">
                    <select id="supplierFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer transition-all">
                        <option value="">Choose a Supplier...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Search Items (Client-side) --}}
            <div>
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">Filter Items</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    </div>
                    <input type="text" id="itemSearch" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                        placeholder="Type to filter loaded items...">
                </div>
            </div>

            {{-- Load Button --}}
            <div>
                <button type="button" onclick="loadSupplierItems()" class="w-full inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt mr-2"></i> Load Items
                </button>
            </div>
        </form>
    </div>

    {{-- 3. PRICE UPDATE TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
            <h3 class="font-display text-lg font-bold text-chocolate">Supplier Items</h3>
            <div class="text-xs font-bold text-caramel bg-white border border-border-soft px-2.5 py-1 rounded-full shadow-sm" id="itemCountBadge" style="display:none;">
                0 Items Loaded
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft" id="priceUpdateTable">
                <thead class="bg-white">
                    <tr>
                        <th class="w-12 px-6 py-4 text-left bg-gray-50">
                            <input type="checkbox" id="selectAllUpdate" onchange="toggleSelectAll()" class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Item Details</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Supplier</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Current Price</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">New Unit Price</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Min Order</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Lead Time</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display bg-gray-50">Preferred</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-100">
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                    <i class="fas fa-cloud-download-alt text-3xl text-chocolate/30"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Select a supplier and click "Load Items" to start.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Update Actions Footer --}}
        <div id="updateActions" class="bg-white border-t-2 border-border-soft p-5 sticky bottom-0 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]" style="display: none;">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm font-medium text-chocolate flex items-center gap-2 bg-cream-bg px-3 py-1.5 rounded-lg border border-border-soft">
                    <i class="fas fa-check-circle"></i>
                    <span id="selectedCount">0</span> items selected for update
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="clearForm()" class="px-5 py-2.5 border border-gray-300 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel / Clear
                    </button>
                    <button type="button" onclick="submitBulkUpdate()" id="submitBtn" class="px-6 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-save mr-2"></i> Update Prices
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>



@push('styles')
<style>
    .modal-backdrop { z-index: 1050; }
    .modal { z-index: 1060; }
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endpush

@push('scripts')
{{-- LOADING MODAL --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="loadingModal">
    <div class="bg-white rounded-xl shadow-2xl p-8 text-center border border-border-soft max-w-xs w-full transform transition-all">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-[3px] border-border-soft border-t-chocolate mb-4"></div>
        <h3 class="text-lg font-display font-bold text-chocolate" id="loadingTitle">Loading</h3>
        <p class="text-xs text-gray-500 mt-2 font-medium uppercase tracking-wide" id="loadingMessage">Please wait...</p>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="confirmModal">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full border border-border-soft text-center transform transition-all scale-100">
        <div class="w-14 h-14 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-amber-100">
            <i class="fas fa-exclamation-triangle text-amber-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-chocolate mb-2" id="modalTitle">Confirm Action</h3>
        <p class="text-gray-600 text-sm mb-6 leading-relaxed" id="modalMessage">Are you sure?</p>
        <div class="flex justify-center gap-3">
            <button id="modalCancelBtn" class="px-5 py-2.5 border border-gray-300 text-gray-600 font-bold rounded-lg hover:bg-gray-50 transition-colors text-sm">Cancel</button>
            <button id="modalConfirmBtn" class="px-6 py-2.5 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 shadow-md transition-colors text-sm">Confirm</button>
        </div>
    </div>
</div>

{{-- SUCCESS MODAL --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="successModal">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full border border-border-soft text-center transform transition-all scale-100">
        <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100">
            <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-chocolate mb-2" id="successTitle">Success</h3>
        <p class="text-gray-600 text-sm mb-6 leading-relaxed" id="successMessage">Action completed.</p>
        <button id="successOkBtn" class="w-full px-5 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-md transition-colors text-sm">
            OK
        </button>
    </div>
</div>

{{-- ERROR MODAL --}}
<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center z-50 hidden" id="errorModal">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full border border-border-soft text-center transform transition-all scale-100">
        <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
            <i class="fas fa-times text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-red-600 mb-2" id="errorTitle">Error</h3>
        <p class="text-gray-600 text-sm mb-6 leading-relaxed" id="errorMessage">Something went wrong.</p>
        <button id="errorOkBtn" class="w-full px-5 py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 shadow-md transition-colors text-sm">
            Dismiss
        </button>
    </div>
</div>

<script>
// JavaScript logic is preserved 100% from original
let supplierItemsData = [];
let selectedItems = new Set();
let pendingAction = null;

function showLoadingModal(title, message) {
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingModal').classList.remove('hidden');
    document.getElementById('loadingModal').classList.add('flex');
}

function hideLoadingModal() {
    document.getElementById('loadingModal').classList.add('hidden');
    document.getElementById('loadingModal').classList.remove('flex');
}

function showConfirmModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmModal').classList.remove('hidden');
    document.getElementById('confirmModal').classList.add('flex');
    pendingAction = onConfirm;
}

function hideConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('flex');
    pendingAction = null;
}

function showSuccessModal(title, message) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
    document.getElementById('successModal').classList.add('flex');
}

function hideSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('successModal').classList.remove('flex');
}

function showErrorModal(title, message) {
    document.getElementById('errorTitle').textContent = title;
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
    document.getElementById('errorModal').classList.add('flex');
}

function hideErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
    document.getElementById('errorModal').classList.remove('flex');
}

function loadSupplierItems() {
    const supplierId = document.getElementById('supplierFilter').value;
    
    if (!supplierId) {
        showErrorModal('Validation Error', 'Please select a supplier first.');
        return;
    }

    showLoadingModal('Loading Items', 'Fetching supplier items...');
    
    // AJAX logic preserved
    fetch(`/purchasing/suppliers/items-for-edit?supplier_id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();
            supplierItemsData = data;
            renderItemsTable();
            
            // Update count badge
            const badge = document.getElementById('itemCountBadge');
            badge.textContent = `${data.length} Items Loaded`;
            badge.style.display = 'inline-block';
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
                    <i class="fas fa-search text-2xl mb-2 text-chocolate/30"></i>
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
            <tr class="group hover:bg-cream-bg/50 transition-colors ${isSelected ? 'bg-blue-50/50' : ''}">
                <td class="px-6 py-4 whitespace-nowrap border-l-4 ${isSelected ? 'border-l-caramel' : 'border-l-transparent'}">
                    <input type="checkbox" class="item-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer" 
                           value="${item.id}" ${isSelected ? 'checked' : ''}
                           onchange="toggleItemSelection(this, ${item.id})">
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-bold text-chocolate">${item.item_name}</div>
                    <div class="text-xs text-gray-500 font-mono mt-0.5">${item.item_code}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">${item.supplier_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-800">
                    ₱${parseFloat(item.current_unit_price).toFixed(2)}
                    <span class="text-xs text-gray-400 font-normal">/${item.unit_symbol}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" step="0.01" min="0.01" value="${item.current_unit_price}" 
                           class="price-input w-24 px-2 py-1.5 border border-gray-300 rounded-lg text-sm font-bold text-right focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all"
                           data-item-id="${item.id}" onchange="validatePrice(this)">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="number" step="0.001" min="0.001" value="${item.current_min_order_qty}" 
                           class="min-qty-input w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all"
                           data-item-id="${item.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="relative">
                        <input type="number" min="0" value="${item.current_lead_time}" 
                            class="lead-time-input w-20 px-2 py-1.5 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all"
                            data-item-id="${item.id}">
                        <span class="absolute right-8 top-2 text-xs text-gray-400 pointer-events-none">d</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex justify-center">
                        <input type="checkbox" ${item.is_preferred ? 'checked' : ''} 
                            class="preferred-checkbox w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"
                            data-item-id="${item.id}">
                    </div>
                </td>
            </tr>
        `;
    });

    document.getElementById('itemsTableBody').innerHTML = html;
    updateSelectedCount();
    document.getElementById('updateActions').style.display = 'block';
}

function toggleItemSelection(checkbox, itemId) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        selectedItems.add(itemId);
        row.classList.add('bg-blue-50/50');
        row.querySelector('td:first-child').classList.replace('border-l-transparent', 'border-l-caramel');
    } else {
        selectedItems.delete(itemId);
        row.classList.remove('bg-blue-50/50');
        row.querySelector('td:first-child').classList.replace('border-l-caramel', 'border-l-transparent');
    }
    updateSelectedCount();
    
    const selectAllCheckbox = document.getElementById('selectAllUpdate');
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === selectedItems.size;
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllUpdate');
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        const itemId = parseInt(checkbox.value);
        const row = checkbox.closest('tr');
        
        if (selectAllCheckbox.checked) {
            selectedItems.add(itemId);
            row.classList.add('bg-blue-50/50');
            row.querySelector('td:first-child').classList.replace('border-l-transparent', 'border-l-caramel');
        } else {
            selectedItems.delete(itemId);
            row.classList.remove('bg-blue-50/50');
            row.querySelector('td:first-child').classList.replace('border-l-caramel', 'border-l-transparent');
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
        showConfirmModal('Clear Form', 'You have selected items that will be cleared. Continue?', () => {
            performClear();
        });
    } else {
        performClear();
    }
}

function performClear() {
    selectedItems.clear();
    document.getElementById('itemsTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="px-6 py-12 text-center text-gray-400 italic">
                Load items to start updating prices
            </td>
        </tr>
    `;
    document.getElementById('updateActions').style.display = 'none';
    document.getElementById('itemSearch').value = '';
    document.getElementById('supplierFilter').value = '';
    document.getElementById('selectAllUpdate').checked = false;
    document.getElementById('itemCountBadge').style.display = 'none';
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
        showErrorModal('Validation Error', 'No valid updates found.');
        return;
    }

    showConfirmModal('Confirm Bulk Update', `Update ${updates.length} item(s)? This cannot be undone.`, () => performBulkUpdate(updates));
}

function performBulkUpdate(updates) {
    showLoadingModal('Updating Prices', 'Updating supplier item prices...');
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

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
            setTimeout(() => performClear(), 2000);
        } else {
            showErrorModal('Update Failed', 'Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoadingModal();
        showErrorModal('Network Error', 'An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

document.getElementById('itemSearch').addEventListener('input', function() {
    if (supplierItemsData.length > 0) renderItemsTable();
});

document.addEventListener('DOMContentLoaded', function() {
    // Modal Event Listeners
    document.getElementById('modalCancelBtn').addEventListener('click', hideConfirmModal);
    document.getElementById('modalConfirmBtn').addEventListener('click', () => { if (pendingAction) { pendingAction(); hideConfirmModal(); } });
    document.getElementById('successOkBtn').addEventListener('click', hideSuccessModal);
    document.getElementById('errorOkBtn').addEventListener('click', hideErrorModal);
    
    // Close on backdrop click
    ['confirmModal', 'successModal', 'errorModal'].forEach(id => {
        document.getElementById(id).addEventListener('click', (e) => { if(e.target === e.currentTarget) document.getElementById(id).classList.add('hidden'); });
    });
});
</script>
@endpush
@endsection