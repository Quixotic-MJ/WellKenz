@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
            <p class="text-sm text-gray-500">Create purchase orders from approved purchase requests with supplier-specific items</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('purchasing.po.drafts') }}" class="flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shadow-sm">
                <i class="fas fa-list mr-2"></i> View Drafts
            </a>
            <a href="{{ route('purchasing.dashboard') }}" class="flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
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

    {{-- APPROVED PURCHASE REQUESTS SECTION --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Approved Purchase Requests</h3>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-500">
                        <span id="selectedPRCount">0</span> selected
                    </span>
                    <button type="button" onclick="openSupplierSelection()" 
                            disabled
                            id="proceedToSupplierBtn"
                            class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-arrow-right mr-2"></i> Select Supplier
                    </button>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="prSearchFilter" placeholder="PR Number, Department..." 
                               class="block w-full pl-10 border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Department</label>
                    <select id="prDepartmentFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Priority</label>
                    <select id="prPriorityFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
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
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAllPRs" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estimated Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="prTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($approvedRequests ?? [] as $request)
                        <tr class="hover:bg-gray-50 pr-row transition-colors duration-150"
                            data-pr="{{ strtolower($request->pr_number ?? '') }}"
                            data-department="{{ strtolower($request->department ?? '') }}"
                            data-priority="{{ $request->priority ?? '' }}"
                            data-date="{{ $request->request_date?->format('Y-m-d') ?? '' }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       name="selected_prs[]" 
                                       value="{{ $request->id }}"
                                       data-pr-id="{{ $request->id }}"
                                       data-items-count="{{ $request->purchaseRequestItems->count() ?? 0 }}"
                                       data-total-cost="{{ $request->total_estimated_cost ?? 0 }}"
                                       data-date="{{ $request->request_date?->format('Y-m-d') ?? '' }}"
                                       class="pr-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate">
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($request->priority === 'urgent')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Urgent
                                        </span>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $request->pr_number ?? 'N/A' }}</div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->department ?? 'N/A' }}</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->requestedBy->name ?? 'N/A' }}</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($request->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($request->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($request->priority === 'normal') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    <i class="fas fa-circle mr-1 text-xs"></i>
                                    {{ ucfirst($request->priority ?? 'normal') }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->purchaseRequestItems->count() ?? 0 }} items</div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($request->purchaseRequestItems->sum('quantity_requested') ?? 0, 1) }} total qty
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ₱{{ number_format($request->total_estimated_cost ?? 0, 2) }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $request->request_date?->format('M d, Y') ?? 'N/A' }}
                                @if($request->request_date)
                                    <div class="text-xs text-gray-500">
                                        {{ $request->request_date->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button type="button" 
                                        onclick="viewPRDetails({{ $request->id }})"
                                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-md text-chocolate bg-chocolate-100 hover:bg-chocolate-200 focus:outline-none focus:ring-2 focus:ring-chocolate transition">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </button>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Approved
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 block"></i>
                                    <p class="text-lg font-medium">No approved purchase requests found</p>
                                    <p class="text-sm mt-1">Approved requests will appear here for PO creation</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SUPPLIER SELECTION AND ITEMS SECTION (Hidden initially) --}}
    <div id="supplierSection" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Supplier Selection & Items</h3>
                <button type="button" onclick="resetToPRSelection()" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back to PR Selection
                </button>
            </div>
        </div>

        <form action="{{ route('purchasing.po.store') }}" method="POST" id="purchaseOrderForm">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="selectedPRIds">
            
            <div class="px-6 py-4 space-y-6">
                {{-- Selected PRs Summary --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Selected Purchase Requests</h4>
                    <div id="selectedPRsSummary" class="text-sm text-blue-700"></div>
                </div>

                {{-- Basic Information --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Supplier Selection --}}
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                        <select name="supplier_id" id="supplier_id" required 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->supplier_code }})</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expected Delivery Date --}}
                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-2">Expected Delivery Date *</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" required 
                               value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        @error('expected_delivery_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payment Terms --}}
                    <div>
                        <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-2">Payment Terms (Days)</label>
                        <input type="number" name="payment_terms" id="payment_terms" 
                               value="{{ old('payment_terms', 30) }}" min="1" max="365"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        @error('payment_terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- REQUIRED ITEMS SECTION --}}
                <div id="requiredItemsSection">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-md font-medium text-gray-800">Required Items (From Selected PRs)</h4>
                            <p class="text-sm text-gray-500">Items from approved purchase requests that this supplier provides</p>
                        </div>
                        <span class="text-sm text-gray-500" id="requiredItemsCount">0 items</span>
                    </div>
                    
                    {{-- Items Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Source PR</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Requested Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Order Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody id="requiredItemsTable" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <p class="text-lg font-medium">Please select a supplier to view available items</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm"
                              placeholder="Additional notes or special instructions">{{ old('notes') }}</textarea>
                </div>

                {{-- Save Options --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Save Options *</label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="save_as_draft" name="save_option" type="radio" value="draft" 
                                   class="h-4 w-4 text-chocolate focus:ring-chocolate border-gray-300" checked>
                            <label for="save_as_draft" class="ml-3 block text-sm text-gray-900">
                                <span class="font-medium">Save as Draft</span>
                                <span class="text-gray-500 block">Save the purchase order in draft status for later editing</span>
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="create_po" name="save_option" type="radio" value="create" 
                                   class="h-4 w-4 text-chocolate focus:ring-chocolate border-gray-300">
                            <label for="create_po" class="ml-3 block text-sm text-gray-900">
                                <span class="font-medium">Create Purchase Order</span>
                                <span class="text-gray-500 block">Save and submit the purchase order for processing</span>
                            </label>
                        </div>
                    </div>
                    @error('save_option')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button type="submit" id="submitBtn"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                    <i class="fas fa-save mr-2"></i> Save as Draft
                </button>
                <button type="button" onclick="resetToPRSelection()" 
                        class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- PURCHASE REQUEST DETAILS MODAL --}}
    <div id="prDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Purchase Request Details</h3>
                <button onclick="closePRDetailsModal()" 
                        class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="prDetailsContent" class="space-y-4">
                {{-- Loading state --}}
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-chocolate mb-2"></i>
                    <p class="text-gray-600">Loading purchase request details...</p>
                </div>
            </div>
            
            <div class="flex justify-end mt-6 pt-4 border-t">
                <button type="button" 
                        onclick="closePRDetailsModal()"
                        class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>

<script>
// Global variables
let selectedPRs = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupPRSelection();
    setupSupplierSelection();
    setupSaveOptionHandlers();
    updateSubmitButton();
});

function setupPRSelection() {
    const selectAll = document.getElementById('selectAllPRs');
    const checkboxes = document.querySelectorAll('.pr-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updatePRSelectionUI();
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePRSelectionUI);
    });
}

function updatePRSelectionUI() {
    const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
    const selectedCount = checkboxes.length;
    const selectAll = document.getElementById('selectAllPRs');
    const selectedCountElement = document.getElementById('selectedPRCount');
    const proceedBtn = document.getElementById('proceedToSupplierBtn');
    
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedCount;
    }
    
    if (proceedBtn) {
        proceedBtn.disabled = selectedCount === 0;
    }
    
    const allCheckboxes = document.querySelectorAll('.pr-checkbox');
    const allChecked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
    const noneChecked = selectedCount === 0;
    
    if (selectAll) {
        selectAll.checked = allChecked;
        selectAll.indeterminate = !allChecked && !noneChecked;
    }
}

function openSupplierSelection() {
    const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one purchase request.');
        return;
    }
    
    // Get selected PR data
    selectedPRs = [];
    checkboxes.forEach(checkbox => {
        const prId = parseInt(checkbox.value);
        const row = checkbox.closest('tr');
        
        selectedPRs.push({
            id: prId,
            pr_number: row.querySelector('td:nth-child(2) .text-sm').textContent.trim(),
            department: row.querySelector('td:nth-child(3) .text-sm').textContent.trim(),
            priority: row.dataset.priority,
            total_estimated_cost: parseFloat(checkbox.dataset.totalCost),
            request_date: row.dataset.date,
            items_count: parseInt(checkbox.dataset.itemsCount)
        });
    });
    
    // Show supplier section
    showSupplierSection();
}

function showSupplierSection() {
    document.querySelector('.bg-white.border.border-gray-200').classList.add('hidden');
    document.getElementById('supplierSection').classList.remove('hidden');
    
    showSelectedPRsSummary();
    document.getElementById('selectedPRIds').value = selectedPRs.map(pr => pr.id).join(',');
}

function resetToPRSelection() {
    document.querySelector('.bg-white.border.border-gray-200').classList.remove('hidden');
    document.getElementById('supplierSection').classList.add('hidden');
    
    selectedPRs = [];
    const checkboxes = document.querySelectorAll('.pr-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    updatePRSelectionUI();
}

function showSelectedPRsSummary() {
    const summaryDiv = document.getElementById('selectedPRsSummary');
    
    if (selectedPRs.length === 0) return;
    
    let html = `<div class="space-y-1">`;
    let totalCost = 0;
    let totalItems = 0;
    
    selectedPRs.forEach(pr => {
        html += `<div class="flex justify-between">
                    <span>${pr.pr_number} (${pr.department || 'N/A'})</span>
                    <span>₱${pr.total_estimated_cost.toLocaleString()}</span>
                </div>`;
        totalCost += pr.total_estimated_cost;
        totalItems += pr.items_count;
    });
    
    html += `<div class="border-t pt-2 mt-2 font-medium">
                <div class="flex justify-between">
                    <span>Total (${selectedPRs.length} PRs, ${totalItems} items)</span>
                    <span>₱${totalCost.toLocaleString()}</span>
                </div>
             </div>`;
    html += `</div>`;
    
    summaryDiv.innerHTML = html;
}

function setupSupplierSelection() {
    const supplierSelect = document.getElementById('supplier_id');
    
    if (supplierSelect) {
        supplierSelect.addEventListener('change', function() {
            const supplierId = this.value;
            
            if (supplierId) {
                loadRequiredItems(supplierId);
            } else {
                // Clear items
                document.getElementById('requiredItemsTable').innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <p class="text-lg font-medium">Please select a supplier to view available items</p>
                        </td>
                    </tr>
                `;
                document.getElementById('requiredItemsCount').textContent = '0 items';
            }
        });
    }
}

function loadRequiredItems(supplierId) {
    const requiredItemsTable = document.getElementById('requiredItemsTable');
    const itemsCountSpan = document.getElementById('requiredItemsCount');
    
    // Show loading state
    requiredItemsTable.innerHTML = `
        <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                <p class="text-lg font-medium">Loading items available from selected supplier...</p>
            </td>
        </tr>
    `;
    
    // Get selected PR IDs
    const selectedPRIds = selectedPRs.map(pr => pr.id);
    
    if (selectedPRIds.length === 0) {
        requiredItemsTable.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    <p class="text-lg font-medium">Please select purchase requests first</p>
                </td>
            </tr>
        `;
        itemsCountSpan.textContent = '0 items';
        return;
    }
    
    // Make API call to get supplier items for selected PRs
    fetch(`/purchasing/api/suppliers/${supplierId}/items-for-prs`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            pr_ids: selectedPRIds.join(',')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error || data.message) {
            throw new Error(data.error || data.message);
        }
        
        const availableItems = data.items?.available || [];
        const unavailableItems = data.items?.unavailable || [];
        
        if (availableItems.length === 0 && unavailableItems.length === 0) {
            requiredItemsTable.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <p class="text-lg font-medium">No items from selected purchase requests are available from this supplier</p>
                        <p class="text-sm mt-2">Items may need to be sourced from other suppliers</p>
                    </td>
                </tr>
            `;
            itemsCountSpan.textContent = '0 items';
            return;
        }
        
        let html = '';
        let itemIndex = 0;
        
        // Show available items first
        availableItems.forEach((item) => {
            const total = (parseFloat(item.total_requested_quantity) || 0) * (parseFloat(item.unit_price) || 0);
            const sourcePRNumbers = item.source_prs.map(pr => pr.pr_number).join(', ');
            const unitPrice = parseFloat(item.unit_price) || 0;
            const totalRequestedQty = parseFloat(item.total_requested_quantity) || 0;
            const minimumOrderQty = parseFloat(item.minimum_order_quantity) || 0;
            
            html += `
                <tr class="border-l-4 border-green-400">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                        <div class="text-sm text-gray-500">${item.item_code} ${item.category !== 'No Category' ? `• ${item.category}` : ''}</div>
                        ${item.is_preferred ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1"><i class="fas fa-star mr-1"></i>Preferred</span>' : ''}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${sourcePRNumbers}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${totalRequestedQty.toFixed(3)} ${item.unit_symbol || ''}</td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">₱${unitPrice.toFixed(2)}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        <input type="number" 
                               name="items[${itemIndex}][quantity_ordered]" 
                               value="${totalRequestedQty}" 
                               min="${minimumOrderQty || totalRequestedQty}" 
                               step="0.001"
                               class="block w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                               onchange="calculateTotal(this, ${itemIndex})">
                        ${minimumOrderQty && minimumOrderQty > totalRequestedQty ? 
                            `<div class="text-xs text-orange-600 mt-1">Min: ${minimumOrderQty.toFixed(3)} ${item.unit_symbol || ''}</div>` : ''}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 item-total">
                        ₱${total.toFixed(2)}
                    </td>
                </tr>
                <input type="hidden" name="items[${itemIndex}][item_id]" value="${item.item_id}">
                <input type="hidden" name="items[${itemIndex}][unit_price]" value="${unitPrice}">
                <input type="hidden" name="items[${itemIndex}][source_pr_id]" value="${selectedPRIds[0]}">
            `;
            itemIndex++;
        });
        
        // Show unavailable items with warning
        if (unavailableItems.length > 0) {
            html += `
                <tr class="bg-red-50">
                    <td colspan="6" class="px-4 py-3">
                        <h4 class="text-sm font-medium text-red-800 mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Items Not Available from This Supplier (${unavailableItems.length})
                        </h4>
                        <p class="text-sm text-red-700 mb-3">These items were requested in your selected PRs but are not available from this supplier:</p>
                        <div class="space-y-2">
            `;
            
            unavailableItems.forEach((item) => {
                const sourcePRNumbers = item.source_prs.map(pr => pr.pr_number).join(', ');
                const totalRequestedQty = parseFloat(item.total_requested_quantity) || 0;
                html += `
                    <div class="flex justify-between items-center text-sm">
                        <div>
                            <span class="font-medium text-red-900">${item.item_name}</span>
                            <span class="text-red-600 ml-2">(${item.item_code})</span>
                            <span class="text-red-700 ml-2">• ${sourcePRNumbers}</span>
                        </div>
                        <div class="text-red-800">${totalRequestedQty.toFixed(3)} ${item.unit_symbol || ''}</div>
                    </div>
                `;
            });
            
            html += `
                        </div>
                    </td>
                </tr>
            `;
        }
        
        requiredItemsTable.innerHTML = html;
        
        // Update counter
        const totalItems = availableItems.length;
        itemsCountSpan.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} available`;
        
        // Show summary message
        if (data.summary) {
            const summary = data.summary;
            let message = `${summary.items_available_from_supplier} of ${summary.total_items_requested} requested items available`;
            if (summary.items_not_available > 0) {
                message += ` • ${summary.items_not_available} items not available from this supplier`;
            }
            
            // Create or update summary alert
            let summaryAlert = document.getElementById('supplierItemsSummary');
            if (!summaryAlert) {
                summaryAlert = document.createElement('div');
                summaryAlert.id = 'supplierItemsSummary';
                summaryAlert.className = 'bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4';
                requiredItemsTable.parentNode.insertBefore(summaryAlert, requiredItemsTable);
            }
            
            summaryAlert.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        ${message}
                    </div>
                    <div class="text-sm font-medium text-blue-900">
                        Est. Total: ₱${(parseFloat(summary.total_estimated_cost) || 0).toLocaleString()}
                    </div>
                </div>
            `;
        }
        
    })
    .catch(error => {
        console.error('Error loading supplier items:', error);
        requiredItemsTable.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i>
                    <p class="text-lg font-medium">Error loading items</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </td>
            </tr>
        `;
        itemsCountSpan.textContent = '0 items';
    });
}

function calculateTotal(input, index) {
    const quantity = parseFloat(input.value) || 0;
    
    // Get unit price from hidden input in the same row
    const row = input.closest('tr');
    const unitPriceInput = row.querySelector('input[name$="[unit_price]"]');
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    
    const total = quantity * unitPrice;
    const totalCell = row.querySelector('.item-total');
    totalCell.textContent = `₱${total.toFixed(2)}`;
}

function setupSaveOptionHandlers() {
    const draftRadio = document.getElementById('save_as_draft');
    const createRadio = document.getElementById('create_po');
    const submitBtn = document.getElementById('submitBtn');
    
    if (draftRadio && createRadio && submitBtn) {
        draftRadio.addEventListener('change', updateSubmitButton);
        createRadio.addEventListener('change', updateSubmitButton);
    }
}

function updateSubmitButton() {
    const draftRadio = document.getElementById('save_as_draft');
    const submitBtn = document.getElementById('submitBtn');
    
    if (draftRadio && submitBtn) {
        if (draftRadio.checked) {
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save as Draft';
            submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            submitBtn.classList.add('bg-chocolate', 'hover:bg-chocolate-dark');
        } else {
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Create Purchase Order';
            submitBtn.classList.remove('bg-chocolate', 'hover:bg-chocolate-dark');
            submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        }
    }
}

// Purchase Request Details Modal Functions
function viewPRDetails(prId) {
    const modal = document.getElementById('prDetailsModal');
    const content = document.getElementById('prDetailsContent');
    
    // Show modal and loading state
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-chocolate mb-2"></i>
            <p class="text-gray-600">Loading purchase request details...</p>
        </div>
    `;
    
    // Fetch purchase request details from API
    fetch(`/purchasing/api/purchase-requests/${prId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch purchase request details');
            }
            return response.json();
        })
        .then(data => {
            displayPRDetails(data.purchaseRequest);
        })
        .catch(error => {
            console.error('Error fetching PR details:', error);
            content.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-500 mb-2"></i>
                    <p class="text-red-600">Failed to load purchase request details.</p>
                    <button onclick="viewPRDetails(${prId})" 
                            class="mt-2 text-chocolate hover:text-chocolate-dark underline">
                        Try Again
                    </button>
                </div>
            `;
        });
}

function displayPRDetails(prData) {
    const content = document.getElementById('prDetailsContent');
    
    // Basic information
    const prNumber = prData.pr_number || 'N/A';
    const department = prData.department || 'N/A';
    const requester = prData.requested_by?.name || 'N/A';
    const priority = prData.priority || 'normal';
    const itemsCount = prData.purchase_request_items?.length || 0;
    const totalCost = prData.total_estimated_cost || 0;
    const requestDate = prData.request_date ? new Date(prData.request_date) : null;
    
    // Format date
    let formattedDate = 'N/A';
    if (requestDate) {
        formattedDate = requestDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    // Generate priority badge HTML
    const priorityClass = getPriorityClass(priority);
    const priorityIcon = priority === 'urgent' ? '<i class="fas fa-exclamation-triangle mr-1"></i>' : '<i class="fas fa-circle mr-1 text-xs"></i>';
    
    // Build items HTML
    let itemsHtml = '';
    if (prData.purchase_request_items && prData.purchase_request_items.length > 0) {
        itemsHtml = prData.purchase_request_items.map(item => {
            const itemName = item.item?.name || 'Unknown Item';
            const itemCode = item.item?.item_code || 'N/A';
            const quantity = parseFloat(item.quantity_requested) || 0;
            const unitPrice = parseFloat(item.unit_price_estimate) || 0;
            const total = quantity * unitPrice;
            const unit = item.item?.unit?.symbol || item.item?.unit?.name || '';
            
            return `
                <tr class="border-b border-gray-200">
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">${itemName}</div>
                        <div class="text-sm text-gray-500">${itemCode}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">${quantity} ${unit}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">₱${unitPrice.toFixed(2)}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">₱${total.toFixed(2)}</td>
                </tr>
            `;
        }).join('');
    } else {
        itemsHtml = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No items found</td></tr>';
    }
    
    // Notes section
    const notes = prData.notes || 'No additional notes provided for this purchase request.';
    
    // Approval info
    const approvalInfo = prData.approved_at ? 
        `Approved by ${prData.approved_by?.name || 'N/A'} on ${new Date(prData.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}` :
        'Approved';
    
    content.innerHTML = `
        <div class="space-y-6">
            {{-- Basic Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">PR Number</label>
                        <p class="text-sm font-medium text-gray-900 mt-1">${prNumber}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">Department</label>
                        <p class="text-sm font-medium text-gray-900 mt-1">${department}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">Requester</label>
                        <p class="text-sm font-medium text-gray-900 mt-1">${requester}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">Priority</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${priorityClass}">
                                ${priorityIcon} ${priority.charAt(0).toUpperCase() + priority.slice(1)}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">Request Date</label>
                        <p class="text-sm font-medium text-gray-900 mt-1">${formattedDate}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide">Total Estimated Cost</label>
                        <p class="text-sm font-medium text-gray-900 mt-1">₱${(parseFloat(totalCost) || 0).toLocaleString()}</p>
                    </div>
                </div>
            </div>
            
            {{-- Status Information --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm font-medium text-green-800">Approved</span>
                    <span class="text-sm text-green-600 ml-2">• ${approvalInfo}</span>
                </div>
            </div>
            
            {{-- Items Section --}}
            <div>
                <h4 class="text-md font-medium text-gray-800 mb-3">Requested Items (${itemsCount} ${itemsCount === 1 ? 'item' : 'items'})</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${itemsHtml}
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Total Estimated Cost:</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900">₱${(parseFloat(totalCost) || 0).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            {{-- Notes Section --}}
            <div>
                <label class="block text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Additional Notes</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <p class="text-sm text-gray-700">${notes}</p>
                </div>
            </div>
        </div>
    `;
}

function closePRDetailsModal() {
    const modal = document.getElementById('prDetailsModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside or pressing Escape
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('prDetailsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePRDetailsModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closePRDetailsModal();
            }
        });
    }
});

function getPriorityClass(priority) {
    switch (priority) {
        case 'urgent':
            return 'bg-red-100 text-red-800';
        case 'high':
            return 'bg-orange-100 text-orange-800';
        case 'normal':
            return 'bg-blue-100 text-blue-800';
        case 'low':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
</script>
@endsection