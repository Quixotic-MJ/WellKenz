@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receive Delivery</h1>
            <p class="text-sm text-gray-500 mt-1">Inbound Logistics • <span class="text-chocolate font-bold">Blind Count Mode</span></p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white border border-gray-300 rounded-lg px-3 py-2 shadow-sm relative">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" 
                    id="poSearch" 
                    placeholder="Scan PO Barcode or Enter PO Number..." 
                    class="text-sm font-medium text-gray-700 focus:outline-none bg-transparent border-none p-0 w-64"
                    onkeypress="handlePoSearch(event)">
                <div id="searchLoading" class="hidden ml-2">
                    <i class="fas fa-spinner fa-spin text-chocolate"></i>
                </div>
            </div>
            <button onclick="manualSearch()" 
                    class="px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-brown-700 transition-colors">
                Search
            </button>
        </div>
    </div>

    {{-- 2. PO SELECTION DROPDOWN --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <label for="purchaseOrderSelect" class="block text-sm font-medium text-gray-700 mb-2">
            Select Purchase Order
        </label>
        <select id="purchaseOrderSelect" 
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate"
                onchange="loadPurchaseOrder(this.value)">
            <option value="">-- Choose a Purchase Order --</option>
            @foreach($purchaseOrders as $po)
                <option value="{{ $po->id }}" 
                        data-supplier="{{ $po->supplier->name }}"
                        data-status="{{ $po->status }}">
                    {{ $po->po_number }} - {{ $po->supplier->name }} 
                    (Expected: {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- 3. PO DETAILS & RECEIVING FORM --}}
    <div id="poDetailsSection" class="hidden">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                <div>
                    <h3 id="poNumber" class="text-lg font-bold text-gray-900"></h3>
                    <p id="poSupplier" class="text-sm text-gray-500">Supplier: <span class="font-medium text-gray-800"></span></p>
                </div>
                <div class="text-right">
                    <span id="poStatus" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Expected Today
                    </span>
                </div>
            </div>

            {{-- BLIND INPUT TABLE --}}
            <form id="receiveDeliveryForm">
                @csrf
                <input type="hidden" id="purchase_order_id" name="purchase_order_id">
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Item Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">Batch / Expiry</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/6">Condition</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase w-1/6">Physical Count</th>
                            </tr>
                        </thead>
                        <tbody id="poItemsTable" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic content will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-4 flex justify-end gap-3">
                    <button type="button" 
                            onclick="saveProgress()" 
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        Save Progress
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 shadow-sm flex items-center">
                        <i class="fas fa-check-double mr-2"></i> Submit Count
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. EMPTY STATE --}}
    <div id="emptyState" class="bg-white border border-gray-200 rounded-lg shadow-sm p-12 text-center">
        <i class="fas fa-truck-loading text-gray-300 text-4xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Purchase Order Selected</h3>
        <p class="text-gray-500 mb-4">Select a purchase order from the dropdown or scan a PO barcode to start receiving delivery.</p>
    </div>

</div>

<script>
// Global variables
let currentPoId = null;

// Handle PO search via barcode/input
function handlePoSearch(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        const searchTerm = event.target.value.trim();
        
        if (searchTerm) {
            searchPurchaseOrder(searchTerm);
        }
    }
}

// Search purchase order by number
async function searchPurchaseOrder(searchTerm) {
    try {
        console.log('Searching for:', searchTerm); // Debug log
        
        // Use the updated route
        const response = await fetch(`/inventory/purchase-orders-search?search=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();
        
        console.log('Search response:', data); // Debug log
        
        if (data.success) {
            // Clear the search input
            document.getElementById('poSearch').value = '';
            
            // Find and select the option in dropdown
            const select = document.getElementById('purchaseOrderSelect');
            let found = false;
            
            for (let option of select.options) {
                if (option.value == data.data.id) {
                    select.value = data.data.id;
                    loadPurchaseOrder(data.data.id);
                    found = true;
                    showAlert('success', `PO ${data.data.po_number} found and loaded!`);
                    break;
                }
            }
            
            if (!found) {
                showAlert('warning', 'PO found but not in current list. Please refresh the page to see all available POs.');
            }
            
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Search error:', error);
        showAlert('error', 'Error searching for purchase order. Check console for details.');
    }
}

// Load purchase order details
async function loadPurchaseOrder(poId) {
    if (!poId) {
        document.getElementById('poDetailsSection').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
        return;
    }

    try {
        showLoading();
        console.log('Loading PO ID:', poId);
        
        // Use the new route: /inventory/purchase-orders/{id}/receive
        const response = await fetch(`/inventory/purchase-orders/${poId}/receive`);
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success) {
            currentPoId = poId;
            displayPurchaseOrder(data.data);
        } else {
            showAlert('error', data.message || 'Failed to load purchase order');
            document.getElementById('purchaseOrderSelect').value = '';
        }
    } catch (error) {
        console.error('Error loading PO:', error);
        showAlert('error', 'Network error: Failed to load purchase order details');
        document.getElementById('purchaseOrderSelect').value = '';
    } finally {
        hideLoading();
    }
}

// Display purchase order details
function displayPurchaseOrder(poData) {
    // Update header info
    document.getElementById('poNumber').textContent = `PO ${poData.po_number}`;
    document.getElementById('poSupplier').querySelector('span').textContent = poData.supplier_name;
    document.getElementById('purchase_order_id').value = poData.id;
    
    // Update status badge
    const statusBadge = document.getElementById('poStatus');
    statusBadge.textContent = poData.status.charAt(0).toUpperCase() + poData.status.slice(1);
    statusBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
        poData.status === 'completed' ? 'bg-green-100 text-green-800' :
        poData.status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
        'bg-blue-100 text-blue-800'
    }`;

    // Build items table
    const itemsTable = document.getElementById('poItemsTable');
    itemsTable.innerHTML = '';

    poData.items.forEach(item => {
        const isPerishable = item.is_perishable;
        const maxReceivable = item.quantity_remaining;
        
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 transition-colors' + (isPerishable ? ' bg-amber-50/30' : '');
        row.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded ${isPerishable ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500'} flex items-center justify-center mr-3">
                        <i class="fas ${isPerishable ? 'fa-tint' : 'fa-box-open'}"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">${item.item_name}</p>
                        <p class="text-xs text-gray-500">SKU: ${item.sku}</p>
                        ${isPerishable ? '<span class="text-[10px] text-red-500 font-bold uppercase"><i class="fas fa-exclamation-circle"></i> Perishable</span>' : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-2">
                    <input type="text" 
                           name="items[${item.id}][batch_number]" 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs" 
                           placeholder="Batch # ${isPerishable ? '' : '(Optional)'}">
                    <input type="date" 
                           name="items[${item.id}][expiry_date]"
                           class="block w-full ${isPerishable ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 bg-gray-50 cursor-not-allowed'} rounded-md shadow-sm text-xs" 
                           ${isPerishable ? 'required' : 'disabled'}
                           title="${isPerishable ? 'Expiry date required' : 'Non-perishable'}">
                </div>
            </td>
            <td class="px-6 py-4 text-center">
                <select name="items[${item.id}][condition]" 
                        class="border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs">
                    ${getConditionOptions(item.category_name)}
                </select>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-end">
                    <input type="number" 
                           name="items[${item.id}][quantity_received]"
                           min="0" 
                           max="${maxReceivable}"
                           step="0.001"
                           class="block w-24 text-right border-2 border-blue-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg font-bold text-gray-900 p-2" 
                           placeholder="0"
                           onchange="validateQuantity(this, ${maxReceivable})">
                    <span class="ml-2 text-xs text-gray-500">${item.unit_symbol}</span>
                </div>
                <input type="hidden" name="items[${item.id}][purchase_order_item_id]" value="${item.id}">
                <div class="text-right mt-1">
                    <span class="text-xs text-gray-400">Ordered: ${item.quantity_ordered} ${item.unit_symbol}</span>
                    ${item.quantity_received > 0 ? `<br><span class="text-xs text-green-600">Previously received: ${item.quantity_received} ${item.unit_symbol}</span>` : ''}
                </div>
            </td>
        `;
        itemsTable.appendChild(row);
    });

    // Show the details section
    document.getElementById('poDetailsSection').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

// Get condition options based on item category
function getConditionOptions(category) {
    const baseOptions = '<option value="good">Good</option><option value="damaged">Damaged</option>';
    
    if (category.toLowerCase().includes('dairy') || category.toLowerCase().includes('frozen')) {
        return baseOptions + '<option value="thawed">Thawed (Reject)</option><option value="leaking">Leaking</option>';
    } else if (category.toLowerCase().includes('dry') || category.toLowerCase().includes('grain')) {
        return baseOptions + '<option value="wet_stained">Wet/Stained</option>';
    }
    
    return baseOptions + '<option value="wet_stained">Wet/Stained</option>';
}

// Validate quantity input
function validateQuantity(input, maxQuantity) {
    const value = parseFloat(input.value) || 0;
    if (value > maxQuantity) {
        input.value = maxQuantity;
        showAlert('warning', `Quantity cannot exceed ${maxQuantity}. Adjusted to maximum.`);
    }
}

// Save progress (local storage)
function saveProgress() {
    if (!currentPoId) return;
    
    const formData = new FormData(document.getElementById('receiveDeliveryForm'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem(`delivery_progress_${currentPoId}`, JSON.stringify(data));
    showAlert('success', 'Progress saved locally');
}

// Load saved progress
function loadSavedProgress() {
    if (!currentPoId) return;
    
    const saved = localStorage.getItem(`delivery_progress_${currentPoId}`);
    if (saved) {
        const data = JSON.parse(saved);
        // Implement form population logic here
        showAlert('info', 'Saved progress loaded');
    }
}

// Handle form submission
document.getElementById('receiveDeliveryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentPoId) {
        showAlert('error', 'No purchase order selected');
        return;
    }

    try {
        showLoading();
        const formData = new FormData(this);
        
        const response = await fetch('/inventory/receive-delivery/process', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            // Clear saved progress
            localStorage.removeItem(`delivery_progress_${currentPoId}`);
            // Reload the page after 2 seconds
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Submission error:', error);
        showAlert('error', 'Failed to submit delivery');
    } finally {
        hideLoading();
    }
});

// Utility functions
function showAlert(type, message) {
    // Implement your alert system here
    alert(`${type.toUpperCase()}: ${message}`);
}

function showLoading() {
    // Implement loading indicator
}

function hideLoading() {
    // Hide loading indicator
}
</script>

<style>
/* Add any custom styles here */
.hidden {
    display: none;
}
</style>
@endsection