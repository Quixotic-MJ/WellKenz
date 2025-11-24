@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if($selectedPurchaseRequest)
                    Converting Purchase Request {{ $selectedPurchaseRequest->pr_number }}
                @else
                    Step 1: Select Approved Purchase Request & Create PO
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if(!$selectedPurchaseRequest)
                <a href="{{ route('purchasing.po.drafts') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Drafts
                </a>
            @endif
            @if($selectedPurchaseRequest)
                <button type="button" onclick="resetForm()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-undo mr-2"></i> Start Over
                </button>
                <button type="button" onclick="saveDraft()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-save mr-2"></i> Save Draft
                </button>
                <button type="button" onclick="submitForApproval()" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i> Submit for Approval
                </button>
            @endif
        </div>
    </div>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Display success message --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-green-800">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    {{-- If no purchase request is selected, show the selection screen --}}
    @if(!$selectedPurchaseRequest)
        
        {{-- Purchase Request Selection Screen --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Select Approved Purchase Request</h2>
                <p class="text-sm text-gray-500 mt-1">Choose an approved purchase request to convert into a purchase order</p>
            </div>
            
            {{-- Filters --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                        <input type="text" id="prSearch" placeholder="PR Number or Department..." class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Department</label>
                        <select id="departmentFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                            <option value="">All Departments</option>
                            @foreach($purchaseRequests->pluck('department')->unique()->filter() as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Priority</label>
                        <select id="priorityFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                            <option value="">All Priorities</option>
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Purchase Requests Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="prTableBody">
                        @forelse($purchaseRequests as $pr)
                            <tr class="hover:bg-gray-50 pr-row" data-pr="{{ strtolower($pr->pr_number) }}" data-department="{{ strtolower($pr->department ?? '') }}" data-priority="{{ $pr->priority }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pr->pr_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pr->department ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pr->requestedBy->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $pr->purchaseRequestItems->count() }} items</div>
                                    <div class="text-sm text-gray-500">{{ $pr->purchaseRequestItems->sum('quantity_requested') }} total qty</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-medium text-gray-900">₱{{ number_format($pr->total_estimated_cost, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $pr->request_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $priorityColors = [
                                            'low' => 'text-blue-600 bg-blue-100',
                                            'normal' => 'text-green-600 bg-green-100',
                                            'high' => 'text-yellow-600 bg-yellow-100',
                                            'urgent' => 'text-red-600 bg-red-100'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$pr->priority] ?? 'text-gray-600 bg-gray-100' }}">
                                        {{ ucfirst($pr->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('purchasing.po.create', ['purchase_request_id' => $pr->id]) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-chocolate text-white text-xs font-medium rounded hover:bg-chocolate-dark transition">
                                        <i class="fas fa-plus mr-1"></i> Create PO
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-clipboard-check text-4xl mb-4 block"></i>
                                        <p class="text-lg font-medium">No approved purchase requests found</p>
                                        <p class="text-sm mt-1">Approved purchase requests will appear here for conversion to purchase orders</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else

        {{-- Purchase Order Creation Form --}}
        <form id="poForm" method="POST" action="{{ route('purchasing.po.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="purchase_request_id" value="{{ $selectedPurchaseRequest->id }}">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Purchase Request Info --}}
                <div class="lg:col-span-3">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-blue-800">Converting Purchase Request</h3>
                                <div class="mt-1 text-sm text-blue-700">
                                    <p><strong>PR Number:</strong> {{ $selectedPurchaseRequest->pr_number }}</p>
                                    <p><strong>Department:</strong> {{ $selectedPurchaseRequest->department ?? 'N/A' }}</p>
                                    <p><strong>Requested By:</strong> {{ $selectedPurchaseRequest->requestedBy->name ?? 'N/A' }}</p>
                                    <p><strong>Total Estimated Cost:</strong> ₱{{ number_format($selectedPurchaseRequest->total_estimated_cost, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. PO HEADER INFO (Left Col) --}}
                <div class="lg:col-span-2 space-y-6">
                    <!-- Vendor Selection Card -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-100 pb-2 mb-4">Vendor Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Select Supplier *</label>
                                <select id="supplier_id" name="supplier_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm py-2" required>
                                    <option value="" disabled selected>Choose vendor...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                data-payment-terms="{{ $supplier->payment_terms ?? 30 }}"
                                                data-contact="{{ $supplier->contact_person ?? '' }}"
                                                data-phone="{{ $supplier->phone ?? '' }}">
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Terms</label>
                                <input type="text" id="payment_terms_display" class="block w-full border-gray-200 bg-gray-50 rounded-md sm:text-sm text-gray-500 cursor-not-allowed" value="Net 30 Days (Auto-filled)" disabled>
                                <input type="hidden" name="payment_terms" id="payment_terms" value="{{ $defaultPaymentTerms }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Contact Information</label>
                                <div id="supplier_contact_info" class="text-sm text-gray-900 bg-gray-50 p-2 rounded border border-gray-200">
                                    Select a supplier to view contact details
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table Card -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-gray-800 uppercase">Order Items *</h3>
                            <button type="button" onclick="addNewRow()" class="text-xs text-blue-600 hover:underline font-bold">
                                <i class="fas fa-plus mr-1"></i> Add Row
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-4/12">Item</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Qty</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Unit Price</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Total</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-1/12"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-gray-200">
                                    <!-- Items will be populated here from purchase request -->
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-3 py-3 text-right text-sm font-bold text-gray-700">Grand Total</td>
                                        <td id="grandTotal" class="px-3 py-3 text-right text-base font-bold text-chocolate">₱ 0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        @error('items')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                        @error('items.*.item_id')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                        @error('items.*.quantity')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                        @error('items.*.unit_price')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 3. SIDEBAR INFO (Right Col) --}}
                <div class="lg:col-span-1 space-y-6">
                    <!-- Order Details -->
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-100 pb-2 mb-4">Order Logistics</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">PO Number</label>
                                <input type="text" class="block w-full border-gray-200 bg-gray-50 rounded-md sm:text-sm text-gray-500" value="{{ $nextPoNumber }} (Auto)" disabled>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Order Date *</label>
                                <input type="date" name="order_date" id="order_date" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" value="{{ date('Y-m-d') }}" required>
                                @error('order_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Expected Delivery</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                                @error('expected_delivery_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Notes to Vendor</label>
                                <textarea name="notes" rows="3" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="e.g. Deliver to rear entrance...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Workflow Guide -->
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div class="text-xs text-blue-800">
                                <p class="font-bold mb-1">Workflow:</p>
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Items are pre-populated from the approved PR</li>
                                    <li>You can modify quantities and unit prices</li>
                                    <li>Save as <strong>Draft</strong> if adjustments needed</li>
                                    <li><strong>Submit</strong> to send to Supervisor</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    @endif
</div>

@endsection

@push('scripts')
<script>
let purchaseRequestsData = @json($purchaseRequests);
let selectedPurchaseRequest = @json($selectedPurchaseRequest);
let prePopulatedItems = @json($prePopulatedItems);
let suppliersData = @json($suppliers);
let currentRowIndex = 0;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (selectedPurchaseRequest) {
        populateItemsFromPR();
        setupSupplierChange();
        setupDateValidation();
    } else {
        setupPRFilters();
    }
});

@if($selectedPurchaseRequest)
function populateItemsFromPR() {
    const tableBody = document.getElementById('itemsTableBody');
    tableBody.innerHTML = '';
    
    prePopulatedItems.forEach((item, index) => {
        addRowWithItem(item, index);
    });
    
    calculateGrandTotal();
}

function addRowWithItem(item, index = null) {
    const template = document.getElementById('newRowTemplate');
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    
    // Add unique identifiers for this row
    const rowId = 'row_' + (++currentRowIndex);
    row.id = rowId;
    
    // Update names to include row index
    const inputs = row.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.name.includes('items[]')) {
            input.name = input.name.replace('items[]', `items[${currentRowIndex}]`);
        }
    });
    
    // Pre-populate the row with item data
    const itemIdInput = row.querySelector('.item-id');
    const itemSearch = row.querySelector('.item-search');
    const itemDetails = row.querySelector('.item-details');
    const quantityInput = row.querySelector('.quantity');
    const priceInput = row.querySelector('.unit-price');
    
    if (item) {
        itemIdInput.value = item.id;
        itemSearch.value = `${item.name} (${item.item_code})`;
        itemSearch.disabled = true; // Lock the item selection
        itemDetails.innerHTML = `<strong>Code:</strong> ${item.item_code} • <strong>Unit:</strong> ${item.unit_symbol} • <strong>Stock:</strong> ${item.current_stock} ${item.unit_symbol}`;
        
        quantityInput.value = item.quantity_requested;
        quantityInput.min = item.quantity_requested * 0.1; // Allow 10% variance
        
        priceInput.value = (item.unit_price_estimate || 0).toFixed(2);
        
        calculateRowTotal(quantityInput);
        
        // Add a note about the source
        const noteDiv = document.createElement('div');
        noteDiv.className = 'text-xs text-blue-600 mt-1';
        noteDiv.innerHTML = '<i class="fas fa-info-circle mr-1"></i>From Purchase Request';
        itemDetails.appendChild(noteDiv);
    }
    
    document.getElementById('itemsTableBody').appendChild(clone);
}

function resetForm() {
    if (confirm('Are you sure you want to start over? All unsaved changes will be lost.')) {
        window.location.href = "{{ route('purchasing.po.create') }}";
    }
}
@else
function setupPRFilters() {
    const searchFilter = document.getElementById('prSearch');
    const departmentFilter = document.getElementById('departmentFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    
    [searchFilter, departmentFilter, priorityFilter].forEach(filter => {
        filter.addEventListener('input', applyPRFilters);
        filter.addEventListener('change', applyPRFilters);
    });
}

function applyPRFilters() {
    const searchTerm = document.getElementById('prSearch').value.toLowerCase();
    const departmentTerm = document.getElementById('departmentFilter').value.toLowerCase();
    const priorityTerm = document.getElementById('priorityFilter').value;
    
    const rows = document.querySelectorAll('.pr-row');
    
    rows.forEach(row => {
        const prText = row.dataset.pr;
        const departmentText = row.dataset.department;
        const priorityText = row.dataset.priority;
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !prText.includes(searchTerm) && !departmentText.includes(searchTerm)) {
            showRow = false;
        }
        
        // Department filter
        if (departmentTerm && !departmentText.includes(departmentTerm)) {
            showRow = false;
        }
        
        // Priority filter
        if (priorityTerm && priorityText !== priorityTerm) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}
@endif

@if($selectedPurchaseRequest)
function setupSupplierChange() {
    const supplierSelect = document.getElementById('supplier_id');
    supplierSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const paymentTerms = selectedOption.dataset.paymentTerms || '30';
        const contact = selectedOption.dataset.contact || '';
        const phone = selectedOption.dataset.phone || '';
        
        document.getElementById('payment_terms').value = paymentTerms;
        document.getElementById('payment_terms_display').value = `Net ${paymentTerms} Days (Auto-filled)`;
        
        let contactInfo = 'Select a supplier to view contact details';
        if (contact || phone) {
            contactInfo = `<strong>Contact:</strong> ${contact || 'N/A'}<br><strong>Phone:</strong> ${phone || 'N/A'}`;
        }
        document.getElementById('supplier_contact_info').innerHTML = contactInfo;
        
        // Update pricing for existing items based on supplier
        updatePricingForSupplier(this.value);
    });
}

function setupDateValidation() {
    const orderDate = document.getElementById('order_date');
    const expectedDelivery = document.getElementById('expected_delivery_date');
    
    if (orderDate && expectedDelivery) {
        expectedDelivery.min = orderDate.value;
        orderDate.addEventListener('change', function() {
            expectedDelivery.min = this.value;
        });
    }
}

function addNewRow() {
    const template = document.getElementById('newRowTemplate');
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    
    // Add unique identifiers for this row
    const rowId = 'row_' + (++currentRowIndex);
    row.id = rowId;
    
    // Update names to include row index
    const inputs = row.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.name.includes('items[]')) {
            input.name = input.name.replace('items[]', `items[${currentRowIndex}]`);
        }
    });
    
    document.getElementById('itemsTableBody').appendChild(clone);
}

function removeRow(button) {
    const row = button.closest('tr');
    const totalRows = document.querySelectorAll('#itemsTableBody .po-item-row').length;
    
    // Don't allow removing rows that came from purchase request
    if (row.querySelector('.item-search').disabled) {
        alert('Items from purchase requests cannot be removed. You can modify quantities instead.');
        return;
    }
    
    if (totalRows > 1) {
        row.remove();
        calculateGrandTotal();
    } else {
        alert('At least one item is required.');
    }
}

function updatePricingForSupplier(supplierId) {
    if (!supplierId) return;
    
    // This would typically make an AJAX call to get supplier-specific pricing
    console.log('Updating pricing for supplier:', supplierId);
}

function saveDraft() {
    const form = document.getElementById('poForm');
    const formData = new FormData(form);
    
    // Add draft flag
    const draftInput = document.createElement('input');
    draftInput.type = 'hidden';
    draftInput.name = 'save_as_draft';
    draftInput.value = '1';
    form.appendChild(draftInput);
    
    form.submit();
}

function submitForApproval() {
    // Validate required fields
    const supplierId = document.getElementById('supplier_id').value;
    
    if (!supplierId) {
        alert('Please select a supplier.');
        return;
    }
    
    // Submit the form
    document.getElementById('poForm').submit();
}
@endif
</script>

{{-- Hidden template for new rows --}}
@if($selectedPurchaseRequest)
<template id="newRowTemplate">
    <tr class="po-item-row">
        <td class="px-3 py-2">
            <input type="hidden" name="items[][item_id]" class="item-id">
            <input type="text" class="item-search block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="Search item...">
            <div class="item-details text-xs text-gray-500 mt-1"></div>
        </td>
        <td class="px-3 py-2">
            <input type="number" name="items[][quantity]" class="quantity block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" min="0.001" step="0.001" onchange="calculateRowTotal(this)">
        </td>
        <td class="px-3 py-2">
            <input type="number" name="items[][unit_price]" class="unit-price block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" min="0.01" step="0.01" onchange="calculateRowTotal(this)">
        </td>
        <td class="px-3 py-2 text-right font-medium text-gray-900 row-total">0.00</td>
        <td class="px-3 py-2 text-center">
            <button type="button" class="remove-row text-red-400 hover:text-red-600" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
function calculateRowTotal(element) {
    const row = element.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
    const total = quantity * unitPrice;
    
    row.querySelector('.row-total').textContent = '₱' + total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.po-item-row').forEach(row => {
        const rowTotal = parseFloat(row.querySelector('.row-total').textContent.replace('₱', '')) || 0;
        grandTotal += rowTotal;
    });
    
    document.getElementById('grandTotal').textContent = '₱' + grandTotal.toFixed(2);
}

// Handle form submission
document.getElementById('poForm')?.addEventListener('submit', function(e) {
    const items = Array.from(document.querySelectorAll('.po-item-row')).map(row => {
        return {
            item_id: row.querySelector('.item-id').value,
            quantity: parseFloat(row.querySelector('.quantity').value) || 0,
            unit_price: parseFloat(row.querySelector('.unit-price').value) || 0,
        };
    }).filter(item => item.item_id && item.quantity > 0 && item.unit_price > 0);
    
    if (items.length === 0) {
        e.preventDefault();
        alert('Please ensure all items have valid quantities and unit prices.');
        return false;
    }
});
</script>
@endif
@endpush