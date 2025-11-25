@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    {{-- DELIVERY RECEIVING HEADER --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-blue-500 rounded-lg p-3">
                    <i class="fas fa-truck-loading text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Receive Delivery
                    </h1>
                    <p class="text-gray-600 mt-1">Log received deliveries and update inventory</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm text-gray-500">Current Time</div>
                    <div class="text-xl font-semibold text-gray-900">{{ now()->format('M d, Y H:i') }}</div>
                </div>
                <div class="bg-gray-100 rounded-lg p-3">
                    <i class="fas fa-calendar-alt text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    {{-- META CSRF TOKEN --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- DELIVERY RECEIVING WORKFLOW --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- PO SELECTION SECTION --}}
        <div class="xl:col-span-2 bg-white rounded-xl shadow-lg border border-gray-200">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-search text-blue-500 mr-2"></i>
                    Select Purchase Order
                </h3>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label for="purchaseOrderSelect" class="block text-sm font-medium text-gray-700 mb-2">
                            Purchase Orders Ready for Delivery
                        </label>
                        <select id="purchaseOrderSelect" 
                                class="block w-full border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3"
                                onchange="loadPurchaseOrder(this.value)">
                            <option value="">Select a purchase order...</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}">
                                    {{ $po->po_number }} - {{ $po->supplier->name }} 
                                    (Expected: {{ \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-blue-900">Delivery Receiving Process</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Select a purchase order to view items, record received quantities, 
                                    and create batch records for inventory tracking.
                                </p>
                                <div class="mt-2 flex items-center text-xs text-green-700">
                                    <i class="fas fa-magic mr-1"></i>
                                    <span class="font-medium">Batch numbers are automatically generated for traceability</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DELIVERY SUMMARY DASHBOARD --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg text-white">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Today's Summary
                </h3>
                
                <div class="space-y-4">
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold" id="todayDeliveries">0</div>
                        <div class="text-sm text-blue-100">Deliveries Processed</div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold" id="itemsReceived">0</div>
                        <div class="text-sm text-blue-100">Items Received</div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-4">
                        <div class="text-2xl font-bold" id="batchesCreated">0</div>
                        <div class="text-sm text-blue-100">Batches Created</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DELIVERY DETAILS SECTION --}}
    <div id="deliveryDetailsSection" class="hidden">
        {{-- PO INFORMATION HEADER --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-invoice text-green-500 mr-2"></i>
                        Purchase Order Details
                    </h3>
                    <span id="poStatus" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Ready for Receipt
                    </span>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">PO Number</label>
                        <div class="text-lg font-semibold text-gray-900" id="poNumber">-</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Supplier</label>
                        <div class="text-lg font-semibold text-gray-900" id="poSupplier">-</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Order Date</label>
                        <div class="text-lg font-semibold text-gray-900" id="poOrderDate">-</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Expected Delivery</label>
                        <div class="text-lg font-semibold text-gray-900" id="poExpectedDate">-</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DELIVERY LOGGING FORM --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list text-purple-500 mr-2"></i>
                    Delivery Log - Received Items
                </h3>
            </div>
            
            <form id="receiveDeliveryForm" class="p-6">
                @csrf
                <input type="hidden" id="purchase_order_id" name="purchase_order_id">
                
                {{-- ITEMS TABLE --}}
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Batch Information
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Condition
                                </th>
                            </tr>
                        </thead>
                        <tbody id="deliveryItemsTable" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic content will be loaded here -->
                        </tbody>
                    </table>
                </div>

            

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-col sm:flex-row justify-end gap-4">
                    <button type="button" 
                            onclick="resetForm()" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-undo mr-2"></i>
                        Reset Form
                    </button>
                    <button type="button" 
                            onclick="showBatchTagsModal()" 
                            class="px-6 py-3 border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors">
                        <i class="fas fa-tags mr-2"></i>
                        🏷️ Batch Tags
                    </button>
                    <button type="button" 
                            onclick="previewDelivery()" 
                            class="px-6 py-3 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        Preview
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Process Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    <div id="emptyState" class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-truck-loading text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                Ready to Receive Deliveries
            </h3>
            <p class="text-gray-600 mb-6">
                Select a purchase order from above to begin logging received deliveries. 
                The system will help you track quantities, create batch records, and update inventory.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                <h4 class="font-medium text-blue-900 mb-2">What happens next?</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Select a purchase order to view ordered items</li>
                    <li>• Record received quantities for each item</li>
                    <li>• System creates batch records automatically</li>
                    <li>• Inventory levels are updated in real-time</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- PREVIEW MODAL --}}
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="bg-blue-600 p-6 text-white">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    Delivery Preview
                </h3>
                <button onclick="closeModal('previewModal')" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto" id="previewContent">
            <!-- Dynamic content -->
        </div>
        <div class="p-6 border-t border-gray-200 flex gap-3 justify-end">
            <button onclick="closeModal('previewModal')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Edit
            </button>
            <button onclick="confirmDelivery()" class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Confirm & Process
            </button>
        </div>
    </div>
</div>

{{-- SUCCESS MODAL --}}
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full">
        <div class="bg-green-600 p-6 text-white rounded-t-xl">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                Delivery Processed Successfully!
            </h3>
        </div>
        <div class="p-6">
            <div id="successContent">
                <!-- Dynamic content -->
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button onclick="closeModal('successModal')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Close
                </button>
                <button onclick="printDeliveryReceipt()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>
                    Print Receipt
                </button>
                <button onclick="resetToEmptyState()" class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    Process Another Delivery
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global variables
    let currentPoId = null;
    let currentPoData = null;
    let autoGeneratedBatches = {};

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // loadDeliveryStats(); // Commented out to avoid 404 error
    });

    // Load purchase order details
    async function loadPurchaseOrder(poId) {
        if (!poId) {
            hideDeliveryDetails();
            return;
        }

        try {
            showLoading();
            console.log('Loading PO ID:', poId);
            
            const response = await fetch(`/inventory/purchase-orders/${poId}/receive`);
            const data = await response.json();
            
            console.log('API Response:', data);
            
            if (data.success) {
                currentPoId = poId;
                currentPoData = data.data;
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

    // Display purchase order information
    function displayPurchaseOrder(poData) {
        // Update PO information
        document.getElementById('poNumber').textContent = poData.po_number;
        document.getElementById('poSupplier').textContent = poData.supplier_name;
        document.getElementById('poOrderDate').textContent = formatDate(poData.order_date);
        document.getElementById('poExpectedDate').textContent = formatDate(poData.expected_delivery_date);
        document.getElementById('purchase_order_id').value = poData.id;

        // Update status badge
        const statusBadge = document.getElementById('poStatus');
        const statusConfig = getStatusConfig(poData.status);
        statusBadge.className = `inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${statusConfig.class}`;
        statusBadge.textContent = statusConfig.text;

        // Build items table
        const itemsTable = document.getElementById('deliveryItemsTable');
        itemsTable.innerHTML = '';

        poData.items.forEach((item, index) => {
            const row = createDeliveryItemRow(item, index);
            itemsTable.appendChild(row);
        });

        // Show delivery details section
        document.getElementById('deliveryDetailsSection').classList.remove('hidden');
        document.getElementById('emptyState').classList.add('hidden');
        
        // Add condition handlers after DOM update
        setTimeout(() => {
            addConditionHandlers();
            // Auto-generate batch numbers for all items
            autoGenerateAllBatchNumbers();
        }, 100);
    }

    // Create delivery item row
    function createDeliveryItemRow(item, index) {
        const maxReceivable = item.quantity_remaining;
        
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="bg-gray-100 rounded-lg p-2 mr-3">
                        <i class="fas fa-box text-gray-500"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">${item.item_name}</div>
                        <div class="text-sm text-gray-500">SKU: ${item.sku} • ${item.unit_symbol}</div>
                        <div class="text-xs text-gray-400">Ordered: ${item.quantity_ordered} ${item.unit_symbol}</div>
                        ${item.quantity_received > 0 ? `
                            <div class="text-xs text-blue-600">Previously received: ${item.quantity_received} ${item.unit_symbol}</div>
                        ` : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 flex items-center">
                            <i class="fas fa-magic text-green-500 mr-1"></i>
                            Simple Batch Number
                            <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">AUTO</span>
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" 
                                   id="batch_${item.id}"
                                   name="items[${item.id}][batch_number]" 
                                   class="block flex-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-mono bg-green-50 text-center"
                                   placeholder="Generating..."
                                   readonly
                                   maxlength="50">
                            <button type="button" 
                                    onclick="regenerateBatchNumber(${item.id})"
                                    class="px-3 py-2 border border-blue-300 text-blue-700 rounded-md hover:bg-blue-50 transition-colors text-sm"
                                    title="Regenerate batch number">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Simple format: YYMMDD-XXXX (easy to type & remember)
                        </div>
                        <div id="qr_${item.id}" class="mt-2 hidden">
                            <!-- QR code will be displayed here after batch generation -->
                        </div>
                    </div>
                    ${item.is_perishable ? `
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Expiry Date</label>
                        <input type="date" 
                               id="expiry_${item.id}"
                               name="items[${item.id}][expiry_date]"
                               class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                               min="${new Date().toISOString().split('T')[0]}">
                    </div>
                    ` : `
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Non-perishable item
                    </div>
                    `}
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Received Quantity</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   id="quantity_${item.id}"
                                   name="items[${item.id}][quantity_received]"
                                   min="0" 
                                   max="${maxReceivable}"
                                   step="0.001"
                                   class="block w-24 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm text-right"
                                   placeholder="0.000"
                                   onchange="validateQuantity(this, ${maxReceivable})">
                            <span class="text-sm text-gray-500">${item.unit_symbol}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Remaining: ${maxReceivable} ${item.unit_symbol}
                        </div>
                    </div>
                    <input type="hidden" name="items[${item.id}][purchase_order_item_id]" value="${item.id}">
                </div>
            </td>
            <td class="px-6 py-4">
                <select name="items[${item.id}][condition]" 
                        class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="good">Good Condition</option>
                    <option value="damaged">Damaged</option>
                    <option value="wet_stained">Wet/Stained</option>
                    <option value="thawed">Thawed (Reject)</option>
                    <option value="leaking">Leaking</option>
                </select>
                <textarea name="items[${item.id}][receiving_notes]" 
                          class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs mt-2"
                          placeholder="Notes (optional)"
                          rows="2"
                          maxlength="500"></textarea>
                <div class="mt-2 hidden damage-details">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Damage Description</label>
                    <input type="text" 
                           name="items[${item.id}][damage_description]" 
                           class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-xs"
                           placeholder="Describe the damage..."
                           maxlength="500">
                </div>
            </td>
        `;
        
        return row;
    }

    // Form submission
    document.getElementById('receiveDeliveryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentPoId) {
            showAlert('error', 'No purchase order selected');
            return;
        }

        // Validate form before showing preview
        if (!validateDeliveryForm()) {
            return;
        }

        showPreviewModal();
    });

    // Validate delivery form
    function validateDeliveryForm() {
        const formData = new FormData(document.getElementById('receiveDeliveryForm'));
        let hasItems = false;
        let errors = [];

        currentPoData.items.forEach(item => {
            const quantity = parseFloat(formData.get(`items[${item.id}][quantity_received]`)) || 0;
            if (quantity > 0) {
                hasItems = true;
                
                // Validate quantity doesn't exceed remaining
                if (quantity > item.quantity_remaining) {
                    errors.push(`${item.item_name}: Quantity cannot exceed remaining amount (${item.quantity_remaining})`);
                }
                
                // Validate expiry date for perishable items
                if (item.is_perishable) {
                    const expiryDate = formData.get(`items[${item.id}][expiry_date]`);
                    if (!expiryDate) {
                        errors.push(`${item.item_name}: Expiry date is required for perishable items`);
                    } else {
                        const expiry = new Date(expiryDate);
                        const today = new Date();
                        if (expiry <= today) {
                            errors.push(`${item.item_name}: Expiry date must be in the future`);
                        }
                    }
                }
                
                // Validate condition
                const condition = formData.get(`items[${item.id}][condition]`) || 'good';
                if (condition !== 'good') {
                    const damageDescription = formData.get(`items[${item.id}][damage_description]`) || '';
                    if (!damageDescription.trim()) {
                        errors.push(`${item.item_name}: Damage description is required for condition "${condition}"`);
                    }
                }
            }
        });

        if (!hasItems) {
            errors.push('Please enter at least one item quantity to receive');
        }

        if (errors.length > 0) {
            showAlert('error', 'Please fix the following issues:\n• ' + errors.join('\n• '));
            return false;
        }

        return true;
    }

    // Add condition change handlers
    function addConditionHandlers() {
        currentPoData.items.forEach(item => {
            const conditionSelect = document.querySelector(`[name="items[${item.id}][condition]"]`);
            const damageDetails = document.querySelector(`#deliveryItemsTable tr:nth-child(${currentPoData.items.indexOf(item) + 1}) .damage-details`);
            
            if (conditionSelect && damageDetails) {
                conditionSelect.addEventListener('change', function() {
                    if (this.value !== 'good') {
                        damageDetails.classList.remove('hidden');
                    } else {
                        damageDetails.classList.add('hidden');
                    }
                });
            }
        });
    }

    // Preview delivery
    function showPreviewModal() {
        if (!currentPoData) return;
        
        const formData = new FormData(document.getElementById('receiveDeliveryForm'));
        const items = currentPoData.items;
        let totalItems = 0;
        let totalQuantity = 0;
        
        items.forEach(item => {
            const quantity = parseFloat(formData.get(`items[${item.id}][quantity_received]`)) || 0;
            if (quantity > 0) {
                totalItems++;
                totalQuantity += quantity;
            }
        });
        
        if (totalItems === 0) {
            showAlert('error', 'Please enter at least one item quantity');
            return;
        }
        
        let content = `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Delivery Summary</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">PO Number:</span>
                            <span class="font-medium ml-2">${currentPoData.po_number}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Supplier:</span>
                            <span class="font-medium ml-2">${currentPoData.supplier_name}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Items to receive:</span>
                            <span class="font-medium ml-2">${totalItems}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total quantity:</span>
                            <span class="font-medium ml-2">${totalQuantity.toFixed(3)}</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border-b border-gray-200 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="border-b border-gray-200 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                <th class="border-b border-gray-200 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="border-b border-gray-200 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        items.forEach(item => {
            const quantity = parseFloat(formData.get(`items[${item.id}][quantity_received]`)) || 0;
            const batchNumber = formData.get(`items[${item.id}][batch_number]`) || 'N/A';
            const condition = formData.get(`items[${item.id}][condition]`) || 'good';
            const notes = formData.get(`items[${item.id}][receiving_notes]`) || '';
            const damageDescription = formData.get(`items[${item.id}][damage_description]`) || '';
            
            if (quantity > 0) {
                let conditionDisplay = condition.replace('_', ' ');
                conditionDisplay = conditionDisplay.charAt(0).toUpperCase() + conditionDisplay.slice(1);
                
                let conditionDetail = '';
                if (condition !== 'good') {
                    conditionDetail = damageDescription ? ` - ${damageDescription}` : '';
                }
                
                content += `
                    <tr class="border-b border-gray-100">
                        <td class="px-4 py-3">
                            <div class="font-medium">${item.item_name}</div>
                            ${notes ? `<div class="text-xs text-gray-500 mt-1">${notes}</div>` : ''}
                        </td>
                        <td class="px-4 py-3 font-mono text-sm">${batchNumber}</td>
                        <td class="px-4 py-3">${quantity} ${item.unit_symbol}</td>
                        <td class="px-4 py-3">
                            <div class="${condition !== 'good' ? 'text-red-600 font-medium' : 'text-green-600'}">
                                ${conditionDisplay}${conditionDetail}
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
        
        content += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        document.getElementById('previewContent').innerHTML = content;
        document.getElementById('previewModal').classList.remove('hidden');
    }

    // Confirm delivery processing
    async function confirmDelivery() {
        try {
            closeModal('previewModal');
            showLoading();
            
            const formElement = document.getElementById('receiveDeliveryForm');
            const formDataToSubmit = new FormData(formElement);
            
            // DEBUGGING: Log currentPoData structure
            console.log('=== DEBUGGING DELIVERY FORM SUBMISSION ===');
            console.log('currentPoData:', currentPoData);
            console.log('autoGeneratedBatches:', autoGeneratedBatches);
            
            // DEBUGGING: Log all form data before processing
            console.log('--- FormData BEFORE processing ---');
            for (let [key, value] of formDataToSubmit.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Ensure auto-generated batch numbers are included in the form data
            Object.keys(autoGeneratedBatches).forEach(itemId => {
                formDataToSubmit.set(`items[${itemId}][batch_number]`, autoGeneratedBatches[itemId]);
            });
            
            // Ensure all items from currentPoData are included in form submission
            // This fixes the backend validation error for missing purchase_order_item_id fields
            if (currentPoData && currentPoData.items) {
                console.log(`--- Processing ${currentPoData.items.length} items from currentPoData ---`);
                currentPoData.items.forEach(item => {
                    console.log(`Processing item ${item.id}: ${item.item_name}`);
                    
                    // Ensure purchase_order_item_id exists (this is the key missing field!)
                    if (!formDataToSubmit.get(`items[${item.id}][purchase_order_item_id]`)) {
                        console.log(`Adding missing purchase_order_item_id for item ${item.id}: ${item.id}`);
                        formDataToSubmit.set(`items[${item.id}][purchase_order_item_id]`, item.id);
                    } else {
                        console.log(`purchase_order_item_id already exists for item ${item.id}:`, formDataToSubmit.get(`items[${item.id}][purchase_order_item_id]`));
                    }
                    
                    // Ensure quantity_received exists (set to 0 if missing)
                    if (!formDataToSubmit.get(`items[${item.id}][quantity_received]`)) {
                        console.log(`Adding missing quantity_received for item ${item.id}: 0`);
                        formDataToSubmit.set(`items[${item.id}][quantity_received]`, '0');
                    }
                    
                    // Ensure condition exists (default to 'good')
                    if (!formDataToSubmit.get(`items[${item.id}][condition]`)) {
                        console.log(`Adding missing condition for item ${item.id}: good`);
                        formDataToSubmit.set(`items[${item.id}][condition]`, 'good');
                    }
                    
                    // Ensure batch_number exists (can be empty)
                    if (!formDataToSubmit.get(`items[${item.id}][batch_number]`)) {
                        console.log(`Adding missing batch_number for item ${item.id}: (empty)`);
                        formDataToSubmit.set(`items[${item.id}][batch_number]`, '');
                    }
                });
            } else {
                console.error('ERROR: currentPoData or currentPoData.items is missing!');
            }
            
            // DEBUGGING: Log all form data after processing
            console.log('--- FormData AFTER processing ---');
            for (let [key, value] of formDataToSubmit.entries()) {
                console.log(`${key}: ${value}`);
            }
            console.log('=== END DEBUGGING ===');
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }
            
            const response = await fetch('/inventory/receive-delivery/process', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formDataToSubmit
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred');
            }
            
            if (data.success) {
                showSuccessModal(data);
                // Show any additional actions
                if (data.next_actions && data.next_actions.length > 0) {
                    setTimeout(() => {
                        data.next_actions.forEach(action => {
                            if (action.priority === 'high') {
                                showAlert('warning', `Action Required: ${action.title}`);
                            }
                        });
                    }, 2000);
                }
            } else {
                showAlert('error', data.message || 'Failed to process delivery');
            }
        } catch (error) {
            console.error('Submission error:', error);
            showAlert('error', `Failed to process delivery: ${error.message}`);
        } finally {
            hideLoading();
        }
    }

    // Show success modal
    function showSuccessModal(data) {
        let content = `
            <div class="space-y-4">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">Delivery Processed Successfully!</h4>
                    <p class="text-gray-600">${data.message || 'All items have been received and inventory updated'}</p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h5 class="font-medium text-green-900 mb-2">What's Been Completed:</h5>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>• Stock movements recorded</li>
                        <li>• Batch records created</li>
                        <li>• Purchase order updated</li>
                        <li>• Current stock levels adjusted</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h5 class="font-medium text-gray-900 mb-2">Delivery Details:</h5>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div>PO Number: ${currentPoData ? currentPoData.po_number : 'N/A'}</div>
                        <div>Supplier: ${currentPoData ? currentPoData.supplier_name : 'N/A'}</div>
                        <div>Processed: ${formatDate(new Date().toISOString().split('T')[0])}</div>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('successContent').innerHTML = content;
        document.getElementById('successModal').classList.remove('hidden');
    }

    // Utility functions
    function getStatusConfig(status) {
        const configs = {
            'sent': { class: 'bg-blue-100 text-blue-800', text: 'Ready to Receive' },
            'confirmed': { class: 'bg-green-100 text-green-800', text: 'Confirmed' },
            'partial': { class: 'bg-yellow-100 text-yellow-800', text: 'Partially Received' },
            'completed': { class: 'bg-gray-100 text-gray-800', text: 'Completed' }
        };
        
        return configs[status] || configs['sent'];
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function validateQuantity(input, maxQuantity) {
        const value = parseFloat(input.value) || 0;
        if (value > maxQuantity) {
            input.value = maxQuantity;
            showAlert('warning', `Quantity cannot exceed ${maxQuantity}. Adjusted to maximum.`);
        }
    }

    function hideDeliveryDetails() {
        document.getElementById('deliveryDetailsSection').classList.add('hidden');
        document.getElementById('emptyState').classList.remove('hidden');
    }

    function resetForm() {
        document.getElementById('receiveDeliveryForm').reset();
        document.getElementById('purchaseOrderSelect').value = '';
        hideDeliveryDetails();
    }

    function resetToEmptyState() {
        closeModal('successModal');
        resetForm();
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function previewDelivery() {
        showPreviewModal();
    }

    function printDeliveryReceipt() {
        window.print();
    }

    function showLoading() {
        document.body.style.cursor = 'wait';
    }

    function hideLoading() {
        document.body.style.cursor = 'default';
    }

    function showAlert(type, message) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-600' :
            type === 'error' ? 'bg-red-600' :
            type === 'warning' ? 'bg-yellow-600' :
            'bg-blue-600'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                } mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 100);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Delivery stats functions commented out to avoid 404 errors
    /*
    async function loadDeliveryStats() {
        try {
            const response = await fetch('/inventory/delivery-stats');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('todayDeliveries').textContent = data.data.today_deliveries || 0;
                document.getElementById('itemsReceived').textContent = data.data.items_received || 0;
                document.getElementById('batchesCreated').textContent = data.data.batches_created || 0;
            }
        } catch (error) {
            console.error('Error loading delivery stats:', error);
        }
    }

    function updateDeliveryStats() {
        loadDeliveryStats();
    }
    */

    // Auto-generate simple batch number format
    function generateBatchNumber(itemCode, supplierName) {
        const date = new Date();
        const year = date.getFullYear().toString().slice(-2);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const random = Math.random().toString(36).substring(2, 6).toUpperCase();
        
        // Simple format: YYMMDD-XXXX (e.g., 241125-AB12)
        return `${year}${month}${day}-${random}`;
    }

    // Auto-generate all batch numbers when PO is loaded
    function autoGenerateAllBatchNumbers() {
        if (!currentPoData) return;
        
        let generatedCount = 0;
        currentPoData.items.forEach(item => {
            const batchNumber = generateBatchNumber(item.sku || item.item_code, currentPoData.supplier_name);
            autoGeneratedBatches[item.id] = batchNumber;
            
            const batchInput = document.getElementById(`batch_${item.id}`);
            if (batchInput) {
                batchInput.value = batchNumber;
                // Add visual feedback
                batchInput.style.background = 'linear-gradient(45deg, #dbeafe, #e0f2fe)';
                batchInput.style.borderColor = '#3b82f6';
                batchInput.style.animation = 'pulse 1s ease-in-out';
                setTimeout(() => {
                    batchInput.style.background = '';
                    batchInput.style.borderColor = '';
                    batchInput.style.animation = '';
                }, 1000);
                generatedCount++;
                
                // Generate and display QR code
                generateAndDisplayQR(item, batchNumber);
            }
        });
        
        if (generatedCount > 0) {
            console.log(`✅ Auto-generated ${generatedCount} batch numbers for PO: ${currentPoData.po_number}`);
            showBatchGenerationNotice(generatedCount);
        }
    }

    // Generate and display QR code for an item
    function generateAndDisplayQR(item, batchNumber) {
        const qrContainer = document.getElementById(`qr_${item.id}`);
        if (!qrContainer) return;
        
        const qrData = generateQRCode(batchNumber, item.item_name, 'TBD', currentPoData.supplier_name);
        const qrElement = createQRElement(qrData, 60);
        
        // Clear previous QR code and add new one
        qrContainer.innerHTML = '';
        qrContainer.appendChild(qrElement);
        qrContainer.classList.remove('hidden');
        
        // Add QR info text
        const qrInfo = document.createElement('div');
        qrInfo.className = 'text-xs text-gray-500 mt-1 text-center';
        qrInfo.innerHTML = '<i class="fas fa-qrcode mr-1"></i>QR Code for tracking';
        qrContainer.appendChild(qrInfo);
    }

    // Enhanced batch generation notice with better visuals
    function showBatchGenerationNotice(count) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg bg-gradient-to-r from-green-600 to-green-700 text-white font-medium transform transition-all duration-300 border-l-4 border-green-800 max-w-sm';
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="bg-white/20 rounded-full p-2 mr-3 flex-shrink-0">
                    <i class="fas fa-magic text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="font-bold text-sm">✅ Auto-generated ${count} batch number${count > 1 ? 's' : ''}!</div>
                    <div class="text-xs text-green-100 mt-1">
                        <div>• Format: YYMMDD-XXXX (easy to remember)</div>
                        <div>• Click "🏷️ Batch Tags" to preview & print</div>
                        <div>• Batch numbers saved automatically</div>
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-200 hover:text-white ml-2">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);
    }

    // Regenerate single batch number
    function regenerateBatchNumber(itemId) {
        if (!currentPoData) return;
        
        const item = currentPoData.items.find(i => i.id === itemId);
        if (!item) return;
        
        const newBatchNumber = generateBatchNumber(item.sku || item.item_code, currentPoData.supplier_name);
        autoGeneratedBatches[itemId] = newBatchNumber;
        
        const batchInput = document.getElementById(`batch_${itemId}`);
        if (batchInput) {
            batchInput.value = newBatchNumber;
            
            // Add animation
            batchInput.classList.add('animate-pulse');
            setTimeout(() => batchInput.classList.remove('animate-pulse'), 500);
            
            // Regenerate QR code
            generateAndDisplayQR(item, newBatchNumber);
        }
        
        showBatchRegenerationNotice();
    }

    // Show batch regeneration notice
    function showBatchRegenerationNotice() {
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 z-50 p-3 rounded-lg shadow-lg bg-blue-600 text-white font-medium transform transition-all duration-300';
        toast.innerHTML = `
            <div class="flex items-center text-sm">
                <i class="fas fa-sync-alt mr-2"></i>
                Batch number regenerated successfully!
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 2 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 2000);
    }

    // Generate QR code for batch tracking
    function generateQRCode(batchNumber, itemName, quantity, supplierName) {
        const qrData = {
            batch: batchNumber,
            item: itemName,
            quantity: quantity,
            supplier: supplierName,
            date: new Date().toISOString(),
            type: 'delivery_batch'
        };
        
        // Create QR code data string (simplified format)
        const qrString = `WKZ|BATCH|${batchNumber}|${itemName}|${quantity}|${supplierName}|${new Date().toISOString()}`;
        return qrString;
    }

    // Create QR code element (enhanced version with better styling)
    function createQRElement(qrData, size = 80) {
        const qrDiv = document.createElement('div');
        qrDiv.className = 'qr-code bg-white border border-gray-400 rounded';
        qrDiv.style.width = size + 'px';
        qrDiv.style.height = size + 'px';
        qrDiv.style.display = 'flex';
        qrDiv.style.alignItems = 'center';
        qrDiv.style.justifyContent = 'center';
        
        // Create a more realistic QR code pattern using CSS
        const qrPattern = generateQRPattern(qrData, size);
        qrDiv.innerHTML = qrPattern;
        
        return qrDiv;
    }
    
    // Generate a realistic QR code pattern
    function generateQRPattern(data, size) {
        const cellSize = Math.floor(size / 15); // 15x15 grid
        const gridSize = Math.min(15, Math.floor(size / cellSize));
        let pattern = '<div style="display: grid; grid-template-columns: repeat(' + gridSize + ', ' + cellSize + 'px); gap: 1px; width: 100%; height: 100%;">';
        
        // Simple hash-based pattern generation
        let hash = 0;
        for (let i = 0; i < data.length; i++) {
            hash = ((hash << 5) - hash + data.charCodeAt(i)) & 0xffffffff;
        }
        
        for (let i = 0; i < gridSize * gridSize; i++) {
            const row = Math.floor(i / gridSize);
            const col = i % gridSize;
            const cellHash = (hash + row * gridSize + col) & 0xff;
            const isBlack = (cellHash % 3 === 0) || 
                           (row < 3 && col < 3) || 
                           (row >= gridSize - 3 && col < 3) ||
                           (row < 3 && col >= gridSize - 3);
            
            if (isBlack) {
                pattern += '<div style="background: #000; width: ' + cellSize + 'px; height: ' + cellSize + 'px;"></div>';
            } else {
                pattern += '<div style="background: #fff; width: ' + cellSize + 'px; height: ' + cellSize + 'px;"></div>';
            }
        }
        
        pattern += '</div>';
        return pattern;
    }

    // Show batch tags printing modal
    function showBatchTagsModal() {
        if (!currentPoData) {
            showAlert('error', 'No purchase order loaded');
            return;
        }
        
        const items = currentPoData.items;
        let tagsContent = '';
        
        items.forEach(item => {
            const quantityInput = document.getElementById(`quantity_${item.id}`);
            const quantity = parseFloat(quantityInput?.value) || 0;
            const batchNumber = autoGeneratedBatches[item.id] || document.getElementById(`batch_${item.id}`)?.value;
            
            if (quantity > 0 && batchNumber) {
                const qrData = generateQRCode(batchNumber, item.item_name, quantity, currentPoData.supplier_name);
                const qrElement = createQRElement(qrData, 60);
                
                tagsContent += `
                    <div class="batch-tag border-2 border-gray-800 rounded-lg p-3 bg-white shadow-sm mb-3 print:break-inside-avoid print:shadow-none">
                        <div class="batch-tag-header">
                            <div class="company-info">
                                <h4 class="text-xs font-bold text-gray-800">WellKenz BAKERY</h4>
                                <div class="text-xs text-gray-600">Inventory Batch Label</div>
                            </div>
                        </div>
                        
                        <div class="batch-tag-content">
                            <div class="item-info">
                                <div class="item-name font-bold text-gray-900 text-sm mb-1">${item.item_name}</div>
                                <div class="item-sku text-xs text-gray-700 mb-2">SKU: ${item.sku}</div>
                            </div>
                            
                            <div class="batch-info bg-blue-50 p-2 rounded border">
                                <div class="batch-number font-mono text-lg font-bold text-blue-800 mb-1">${batchNumber}</div>
                                <div class="text-xs text-blue-600">Batch Code</div>
                            </div>
                            
                            <div class="quantity-info bg-gray-50 p-2 rounded border">
                                <div class="text-lg font-bold text-gray-800">${quantity}</div>
                                <div class="text-xs text-gray-600">${item.unit_symbol}</div>
                            </div>
                            
                            <div class="details-grid">
                                <div class="detail-row">
                                    <span class="text-xs text-gray-600">Supplier:</span>
                                    <span class="text-xs font-medium text-gray-800">${currentPoData.supplier_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="text-xs text-gray-600">Date:</span>
                                    <span class="text-xs font-medium text-gray-800">${formatDate(new Date().toISOString().split('T')[0])}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="text-xs text-gray-600">PO:</span>
                                    <span class="text-xs font-medium text-gray-800">${currentPoData.po_number}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <div class="qr-code-container">
                                ${qrElement.outerHTML}
                            </div>
                            <div class="qr-text text-xs text-center text-gray-500 mt-1">Scan for details</div>
                        </div>
                        
                        <div class="batch-tag-footer">
                            <div class="footer-line"></div>
                            <div class="text-xs text-center text-gray-600">Inventory Management System</div>
                        </div>
                    </div>
                `;
            }
        });
        
        if (!tagsContent) {
            showAlert('warning', 'No items with quantities to create tags for');
            return;
        }
        
        // Calculate count of items with quantities before creating modal
        const readyToPrintCount = items.filter(item => {
            const quantityInput = document.getElementById(`quantity_${item.id}`);
            const quantity = parseFloat(quantityInput?.value) || 0;
            return quantity > 0;
        }).length;
        
        const modalHTML = `
            <div id="batchTagsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
                    <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold flex items-center">
                                <i class="fas fa-tags mr-2"></i>
                                🏷️ Batch Tags & QR Codes Preview
                            </h3>
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-green-100">
                                    Ready to Print: <span class="font-bold">${readyToPrintCount}</span> tags
                                </div>
                                <button onclick="document.getElementById('batchTagsModal').remove()" class="text-white hover:text-gray-200">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 max-h-[60vh] overflow-y-auto bg-gray-50" id="tagsContent">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            ${tagsContent}
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-200 bg-white">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                <span>Review tags above before printing. Labels are optimized for 75mm × 45mm adhesive tags.</span>
                            </div>
                            <div class="flex gap-3">
                                <button onclick="document.getElementById('batchTagsModal').remove()" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg">
                                    Close
                                </button>
                                <button onclick="printBatchTags()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center">
                                    <i class="fas fa-print mr-2"></i>
                                    Print Tags
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Print batch tags
    function printBatchTags() {
        const modal = document.getElementById('batchTagsModal');
        if (!modal) {
            console.error('Batch tags modal not found');
            showAlert('error', 'Modal not found');
            return;
        }
        
        // Get tags content before removing modal
        const tagsContent = modal.querySelector('#tagsContent');
        if (!tagsContent) {
            console.error('Tags content not found in modal');
            showAlert('error', 'No batch tags available for printing');
            modal.remove();
            return;
        }
        
        const tagsHtml = tagsContent.innerHTML;
        const totalItems = tagsContent.children.length;
        modal.remove();
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Batch Tags & QR Codes</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { 
                            font-family: 'Arial', sans-serif; 
                            margin: 0; 
                            padding: 15mm;
                            background: white;
                            color: #333;
                        }
                        
                        .print-header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #333;
                            padding-bottom: 15px;
                        }
                        
                        .print-header h2 {
                            font-size: 24px;
                            font-weight: bold;
                            margin-bottom: 5px;
                            color: #2563eb;
                        }
                        
                        .print-header p {
                            font-size: 14px;
                            color: #666;
                        }
                        
                        .batch-tags-container {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(85mm, 1fr));
                            gap: 10mm;
                            justify-items: center;
                        }
                        
                        .batch-tag { 
                            border: 2px solid #000; 
                            padding: 8mm;
                            margin: 0;
                            page-break-inside: avoid;
                            width: 80mm;
                            min-height: 50mm;
                            background: white;
                            position: relative;
                        }
                        
                        .batch-tag-header {
                            text-align: center;
                            margin-bottom: 8px;
                            padding-bottom: 6px;
                            border-bottom: 1px solid #ccc;
                        }
                        
                        .company-info h4 {
                            font-size: 14px;
                            font-weight: bold;
                            margin-bottom: 2px;
                        }
                        
                        .company-info div {
                            font-size: 10px;
                            color: #666;
                        }
                        
                        .batch-tag-content {
                            margin-bottom: 10px;
                        }
                        
                        .item-info {
                            margin-bottom: 10px;
                        }
                        
                        .item-name {
                            font-size: 13px;
                            font-weight: bold;
                            line-height: 1.2;
                            margin-bottom: 3px;
                        }
                        
                        .item-sku {
                            font-size: 11px;
                            color: #666;
                        }
                        
                        .batch-info {
                            background: #f0f8ff;
                            border: 1px solid #b3d9ff;
                            padding: 6px;
                            text-align: center;
                            margin-bottom: 8px;
                        }
                        
                        .batch-number {
                            font-size: 16px;
                            font-weight: bold;
                            color: #1e40af;
                            font-family: 'Courier New', monospace;
                            letter-spacing: 0.5px;
                        }
                        
                        .quantity-info {
                            background: #f8f9fa;
                            border: 1px solid #dee2e6;
                            padding: 6px;
                            text-align: center;
                            margin-bottom: 8px;
                        }
                        
                        .quantity-info .text-lg {
                            font-size: 18px;
                            font-weight: bold;
                            color: #333;
                        }
                        
                        .quantity-info .text-xs {
                            font-size: 10px;
                            color: #666;
                        }
                        
                        .details-grid {
                            margin-top: 8px;
                        }
                        
                        .detail-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 3px;
                            font-size: 10px;
                        }
                        
                        .detail-row span:first-child {
                            color: #666;
                        }
                        
                        .detail-row span:last-child {
                            font-weight: 600;
                            color: #333;
                        }
                        
                        .qr-section {
                            text-align: center;
                            margin-top: 10px;
                            padding-top: 8px;
                            border-top: 1px solid #eee;
                        }
                        
                        .qr-code-container {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            min-height: 40px;
                        }
                        
                        .qr-text {
                            font-size: 9px;
                            color: #666;
                            margin-top: 2px;
                        }
                        
                        .batch-tag-footer {
                            margin-top: 10px;
                        }
                        
                        .footer-line {
                            height: 1px;
                            background: #333;
                            margin-bottom: 4px;
                        }
                        
                        .batch-tag-footer .text-xs {
                            font-size: 9px;
                            text-align: center;
                            color: #666;
                        }
                        
                        /* Enhanced QR Code Styling */
                        .qr-code {
                            display: inline-block;
                            border: 1px solid #ccc;
                            background: white;
                            padding: 2px;
                        }
                        
                        .qr-placeholder {
                            width: 35px !important;
                            height: 35px !important;
                            background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa),
                                        linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa);
                            background-size: 4px 4px;
                            background-position: 0 0, 2px 2px;
                            border: 1px solid #ddd;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 7px;
                            color: #333;
                            text-align: center;
                            line-height: 1;
                        }
                        
                        /* Print Optimizations */
                        @media print {
                            body { 
                                margin: 5mm;
                                padding: 0;
                            }
                            
                            .batch-tag { 
                                width: 75mm;
                                min-height: 45mm;
                                padding: 6mm;
                                margin: 0;
                                page-break-inside: avoid;
                                box-shadow: none;
                                border: 2px solid #000;
                            }
                            
                            .print-header {
                                display: none;
                            }
                            
                            .batch-tag:nth-child(3n) {
                                page-break-before: always;
                            }
                        }
                        
                        @page {
                            size: A4;
                            margin: 10mm;
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h2>🏷️ WellKenz Bakery ERP - Batch Tags</h2>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                        <p>Total Items: ${totalItems}</p>
                    </div>
                    <div class="batch-tags-container">
                        ${tagsHtml}
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>

<style>
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .print-break {
            page-break-after: always;
        }
    }
    
    /* Custom scrollbar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Auto-batch generation animations */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .animate-pulse {
        animation: pulse 1s ease-in-out;
    }
    
    /* QR Code styling */
    .qr-code {
        display: inline-block;
        margin: 5px;
    }
    
    .qr-code img {
        max-width: 100%;
        height: auto;
    }
    
    /* Batch tags modal styling */
    .batch-tag {
        border: 2px solid #000;
        border-radius: 8px;
        padding: 15px;
        margin: 10px 0;
        background: white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        width: 300px;
        min-height: 200px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .batch-tag:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .batch-tag-header {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .company-info h4 {
        font-size: 16px;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }
    
    .company-info div:last-child {
        font-size: 10px;
        color: #6b7280;
        font-weight: 600;
    }
    
    .batch-tag-content {
        margin-bottom: 12px;
    }
    
    .item-info {
        margin-bottom: 12px;
        background: #f9fafb;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    
    .item-name {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
        margin-bottom: 4px;
    }
    
    .item-sku {
        font-size: 11px;
        color: #4b5563;
        font-family: 'Courier New', monospace;
        font-weight: 600;
    }
    
    .batch-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 2px solid #3b82f6;
        padding: 10px;
        text-align: center;
        margin-bottom: 10px;
        border-radius: 8px;
        position: relative;
    }
    
    .batch-info::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #3b82f6, #1d4ed8, #3b82f6);
        border-radius: 8px;
        z-index: -1;
    }
    
    .batch-number {
        font-size: 18px;
        font-weight: 900;
        color: #1e40af;
        font-family: 'Courier New', monospace;
        letter-spacing: 1px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .quantity-info {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid #6b7280;
        padding: 10px;
        text-align: center;
        margin-bottom: 12px;
        border-radius: 8px;
    }
    
    .quantity-info .text-lg {
        font-size: 20px;
        font-weight: 900;
        color: #111827;
        line-height: 1;
    }
    
    .quantity-info .text-xs {
        font-size: 10px;
        color: #4b5563;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .details-grid {
        margin-top: 12px;
        background: #f9fafb;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        font-size: 10px;
        padding: 2px 0;
    }
    
    .detail-row:last-child {
        margin-bottom: 0;
    }
    
    .detail-row span:first-child {
        color: #6b7280;
        font-weight: 600;
    }
    
    .detail-row span:last-child {
        font-weight: 700;
        color: #111827;
    }
    
    .qr-section {
        text-align: center;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 2px solid #e5e7eb;
    }
    
    .qr-code-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 50px;
        background: #ffffff;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        padding: 8px;
    }
    
    .qr-text {
        font-size: 9px;
        color: #6b7280;
        margin-top: 4px;
        font-weight: 600;
    }
    
    .batch-tag-footer {
        margin-top: 12px;
    }
    
    .footer-line {
        height: 2px;
        background: linear-gradient(90deg, #374151, #6b7280, #374151);
        margin-bottom: 6px;
        border-radius: 1px;
    }
    
    .batch-tag-footer .text-xs {
        font-size: 9px;
        text-align: center;
        color: #4b5563;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Enhanced QR Code styling */
    .qr-code {
        display: inline-block;
        border: 2px solid #374151;
        background: white;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Print styles for batch tags - enhanced */
    @media print {
        .batch-tag {
            border: 2px solid #000;
            width: 75mm;
            min-height: 45mm;
            margin: 3mm;
            padding: 4mm;
            page-break-inside: avoid;
            box-shadow: none;
            background: white;
        }
        
        .batch-tag:hover {
            transform: none;
            box-shadow: none;
        }
        
        .qr-code {
            border: 1px solid #333;
        }
        
        .company-info h4 {
            font-size: 12px;
        }
        
        .item-name {
            font-size: 11px;
        }
        
        .batch-number {
            font-size: 14px;
        }
        
        .quantity-info .text-lg {
            font-size: 16px;
        }
    }
    
    /* Modal specific styling */
    #batchTagsModal .batch-tag {
        width: auto;
        min-height: auto;
        max-width: 320px;
    }
    
    #tagsContent {
        background: #f8fafc;
    }
    
    /* Animation for new tags */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .batch-tag {
        animation: slideInUp 0.3s ease-out;
    }
</style>
@endsection