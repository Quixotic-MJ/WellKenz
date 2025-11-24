@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
            <p class="text-sm text-gray-500">Select approved purchase requests to create purchase orders</p>
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

    {{-- 2. APPROVED PURCHASE REQUESTS SECTION --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Approved Purchase Requests</h3>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-500">
                        <span id="selectedCount">0</span> selected
                    </span>
                    <button type="button" onclick="openCreatePOModal()" 
                            disabled
                            id="createPOBtn"
                            class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-plus mr-2"></i> Create PO from Selected
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
                        <input type="text" id="searchFilter" placeholder="PR Number, Department..." 
                               class="block w-full pl-10 border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Department</label>
                    <select id="departmentFilter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="prTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($approvedRequests as $request)
                        <tr class="hover:bg-gray-50 pr-row transition-colors duration-150"
                            data-pr="{{ strtolower($request->pr_number) }}"
                            data-department="{{ strtolower($request->department ?? '') }}"
                            data-priority="{{ $request->priority }}"
                            data-date="{{ $request->request_date?->format('Y-m-d') ?? '' }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       name="selected_prs[]" 
                                       value="{{ $request->id }}"
                                       data-items-count="{{ $request->purchaseRequestItems->count() ?? 0 }}"
                                       data-total-cost="{{ $request->total_estimated_cost }}"
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
                                    <div class="text-sm font-medium text-gray-900">{{ $request->pr_number }}</div>
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
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->purchaseRequestItems->count() }} items</div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($request->purchaseRequestItems->sum('quantity_requested'), 1) }} total qty
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ₱{{ number_format($request->total_estimated_cost, 2) }}
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
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Approved
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
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

        {{-- Pagination --}}
        @if($approvedRequests->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Showing {{ $approvedRequests->firstItem() ?? 0 }} to {{ $approvedRequests->lastItem() ?? 0 }} of {{ $approvedRequests->total() }} results</span>
                    </div>
                    <div>
                        {{ $approvedRequests->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- CREATE PO MODAL --}}
<div id="createPOModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCreatePOModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form action="{{ route('purchasing.po.store') }}" method="POST" id="purchaseOrderForm">
                @csrf
                <input type="hidden" name="selected_pr_ids" id="selectedPRIds">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Create Purchase Order
                            </h3>
                            
                            {{-- Selected PRs Summary --}}
                            <div id="selectedPRsSummary" class="mb-6 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">Selected Purchase Requests</h4>
                                    <div id="selectedPRsList" class="text-sm text-blue-700"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Supplier Selection --}}
                                <div>
                                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                                    <select name="supplier_id" id="supplier_id" required 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
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
                            </div>

                            {{-- Notes --}}
                            <div class="mt-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm"
                                          placeholder="Additional notes or special instructions">{{ old('notes') }}</textarea>
                            </div>

                            {{-- Items from Selected PRs --}}
                            <div class="mt-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Items from Selected PRs</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source PR</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Unit Price</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalItemsTable" class="bg-white divide-y divide-gray-200">
                                            {{-- Items will be populated by JavaScript --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Order Summary --}}
                            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-md font-medium text-gray-800">Order Summary</h4>
                                    <div class="text-right">
                                        <div class="text-2xl font-black text-gray-800" id="modal-grand-total">₱ 0.00</div>
                                        <p class="text-sm text-gray-500">Grand Total</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-save mr-2"></i> Create Purchase Order
                    </button>
                    <button type="button" 
                            onclick="closeCreatePOModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables
let selectedPRs = [];
const approvedRequests = @json($approvedRequestsForJS ?? $approvedRequests);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Ensure approvedRequests is an array
    if (!Array.isArray(approvedRequests)) {
        console.warn('approvedRequests is not an array:', approvedRequests);
    }
    
    setupFilters();
    setupSelection();
    updateSelectionUI();
});

function setupFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const departmentFilter = document.getElementById('departmentFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    
    if (searchFilter) searchFilter.addEventListener('input', debounce(applyFilters, 300));
    if (departmentFilter) departmentFilter.addEventListener('change', applyFilters);
    if (priorityFilter) priorityFilter.addEventListener('change', applyFilters);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function applyFilters() {
    const searchTerm = (document.getElementById('searchFilter')?.value || '').toLowerCase();
    const departmentTerm = document.getElementById('departmentFilter')?.value || '';
    const priorityTerm = document.getElementById('priorityFilter')?.value || '';
    
    const rows = document.querySelectorAll('.pr-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const prText = row.dataset.pr || '';
        const departmentText = row.dataset.department || '';
        const priorityText = row.dataset.priority || '';
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !prText.includes(searchTerm) && !departmentText.includes(searchTerm)) {
            showRow = false;
        }
        
        // Department filter
        if (departmentTerm && departmentText !== departmentTerm.toLowerCase()) {
            showRow = false;
        }
        
        // Priority filter
        if (priorityTerm && priorityText !== priorityTerm) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
    });
}

function setupSelection() {
    const selectAll = document.getElementById('selectAllPRs');
    const checkboxes = document.querySelectorAll('.pr-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                if (this.checked) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            });
            updateSelectionUI();
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });
}

function updateSelectionUI() {
    const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
    const selectedCount = checkboxes.length;
    const selectAll = document.getElementById('selectAllPRs');
    const selectedCountElement = document.getElementById('selectedCount');
    const createPOBtn = document.getElementById('createPOBtn');
    
    // Update count display
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedCount;
    }
    
    // Update button state
    if (createPOBtn) {
        createPOBtn.disabled = selectedCount === 0;
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.pr-checkbox');
    const allChecked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
    const noneChecked = selectedCount === 0;
    
    if (selectAll) {
        selectAll.checked = allChecked;
        selectAll.indeterminate = !allChecked && !noneChecked;
    }
}

function openCreatePOModal() {
    const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one purchase request.');
        return;
    }
    
    // Get selected PR data from checkbox attributes and table rows
    selectedPRs = [];
    checkboxes.forEach(checkbox => {
        const prId = parseInt(checkbox.value);
        const row = checkbox.closest('tr');
        
        // Extract data from table row
        const prData = {
            id: prId,
            pr_number: row.querySelector('td:nth-child(2) .text-sm').textContent.trim(),
            department: row.querySelector('td:nth-child(3) .text-sm').textContent.trim(),
            priority: row.dataset.priority,
            total_estimated_cost: parseFloat(checkbox.dataset.totalCost),
            request_date: row.dataset.date,
            // Load detailed items from approvedRequests if available
            purchaseRequestItems: []
        };
        
        // Try to find detailed data in approvedRequests
        if (Array.isArray(approvedRequests) && approvedRequests.length > 0) {
            const fullPRData = approvedRequests.find(pr => pr.id === prId);
            if (fullPRData && fullPRData.purchaseRequestItems) {
                prData.purchaseRequestItems = fullPRData.purchaseRequestItems;
            }
        }
        
        selectedPRs.push(prData);
    });
    
    // Show basic summary
    showSelectedPRsSummary();
    
    // Populate modal with detailed items if available, otherwise use basic approach
    if (selectedPRs.length > 0 && selectedPRs.some(pr => pr.purchaseRequestItems.length > 0)) {
        populateModalItemsTableWithDetailedData();
    } else {
        populateModalItemsTableFromBasicData();
    }
    
    // Show modal
    document.getElementById('createPOModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreatePOModal() {
    document.getElementById('createPOModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('purchaseOrderForm').reset();
    selectedPRs = [];
    
    // Hide summary
    document.getElementById('selectedPRsSummary').classList.add('hidden');
}

function showSelectedPRsSummary() {
    const summaryDiv = document.getElementById('selectedPRsSummary');
    const listDiv = document.getElementById('selectedPRsList');
    
    if (selectedPRs.length === 0) return;
    
    let html = `<div class="space-y-1">`;
    let totalCost = 0;
    let totalItems = 0;
    
    selectedPRs.forEach(pr => {
        const itemsCount = parseInt(document.querySelector(`input[value="${pr.id}"]`).dataset.itemsCount) || 0;
        html += `<div class="flex justify-between">
                    <span>${pr.pr_number} (${pr.department || 'N/A'})</span>
                    <span>₱${pr.total_estimated_cost.toLocaleString()}</span>
                </div>`;
        totalCost += pr.total_estimated_cost;
        totalItems += itemsCount;
    });
    
    html += `<div class="border-t pt-2 mt-2 font-medium">
                <div class="flex justify-between">
                    <span>Total (${selectedPRs.length} PRs, ${totalItems} items)</span>
                    <span>₱${totalCost.toLocaleString()}</span>
                </div>
             </div>`;
    html += `</div>`;
    
    listDiv.innerHTML = html;
    summaryDiv.classList.remove('hidden');
}

function populateModalItemsTable() {
    const tbody = document.getElementById('modalItemsTable');
    let html = '';
    let grandTotal = 0;
    
    // Since we don't have detailed item data, show a message
    html = `
        <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                <i class="fas fa-info-circle text-2xl mb-2 block"></i>
                <p class="text-lg font-medium">Items will be loaded from selected PRs</p>
                <p class="text-sm mt-1">Please confirm the purchase order details. Items from the selected purchase requests will be included automatically.</p>
            </td>
        </tr>
    `;
    
    tbody.innerHTML = html;
    
    // Set selected PR IDs
    document.getElementById('selectedPRIds').value = selectedPRs.map(pr => pr.id).join(',');
    
    // Calculate total based on PR totals
    selectedPRs.forEach(pr => {
        grandTotal += pr.total_estimated_cost;
    });
    
    document.getElementById('modal-grand-total').textContent = `₱${grandTotal.toFixed(2)}`;
}

function populateModalItemsTableFromBasicData() {
    // This is a fallback function - using the main populateModalItemsTable for now
    populateModalItemsTable();
}

function populateModalItemsTableWithDetailedData() {
    const tbody = document.getElementById('modalItemsTable');
    let html = '';
    let grandTotal = 0;
    
    selectedPRs.forEach(pr => {
        if (pr.purchaseRequestItems && pr.purchaseRequestItems.length > 0) {
            pr.purchaseRequestItems.forEach(item => {
                const itemTotal = item.total_estimated_cost || (item.quantity_requested * item.unit_price_estimate);
                grandTotal += itemTotal;
                
                html += `
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">${item.item?.name || 'Unknown Item'}</div>
                            <div class="text-sm text-gray-500">${item.item?.item_code || ''}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">${pr.pr_number}</td>
                        <td class="px-4 py-3">
                            <input type="number" name="items[${item.id}][quantity_ordered]" 
                                   value="${item.quantity_requested}" 
                                   min="0.001" step="0.001"
                                   class="block w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                                   onchange="calculateModalTotal()">
                            <div class="text-xs text-gray-500 mt-1">${item.quantity_requested} requested</div>
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" name="items[${item.id}][unit_price]" 
                                   value="${item.unit_price_estimate || 0}" 
                                   min="0.01" step="0.01"
                                   class="block w-24 px-2 py-1 border border-gray-300 rounded text-sm"
                                   onchange="calculateModalTotal()">
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 item-modal-total">
                            ₱${itemTotal.toFixed(2)}
                        </td>
                    </tr>
                    <input type="hidden" name="items[${item.id}][item_id]" value="${item.item_id}">
                    <input type="hidden" name="items[${item.id}][source_pr_id]" value="${pr.id}">
                `;
            });
        }
    });
    
    if (html === '') {
        html = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-info-circle text-2xl mb-2 block"></i>
                    <p class="text-lg font-medium">Items will be loaded from selected PRs</p>
                    <p class="text-sm mt-1">Please confirm the purchase order details. Items from the selected purchase requests will be included automatically.</p>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
    
    // Set selected PR IDs
    document.getElementById('selectedPRIds').value = selectedPRs.map(pr => pr.id).join(',');
    
    document.getElementById('modal-grand-total').textContent = `₱${grandTotal.toFixed(2)}`;
}

function calculateModalTotal() {
    let grandTotal = 0;
    
    // Calculate based on input values
    const rows = document.querySelectorAll('#modalItemsTable tr');
    rows.forEach(row => {
        const quantityInput = row.querySelector('input[name*="[quantity_ordered]"]');
        const priceInput = row.querySelector('input[name*="[unit_price]"]');
        const totalSpan = row.querySelector('.item-modal-total');
        
        if (quantityInput && priceInput && totalSpan) {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            
            totalSpan.textContent = `₱${total.toFixed(2)}`;
            grandTotal += total;
        }
    });
    
    document.getElementById('modal-grand-total').textContent = `₱${grandTotal.toFixed(2)}`;
}

// Form validation
document.getElementById('purchaseOrderForm').addEventListener('submit', function(e) {
    if (selectedPRs.length === 0) {
        e.preventDefault();
        alert('Please select at least one purchase request.');
        return;
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreatePOModal();
    }
});

// Close modal when clicking outside
document.getElementById('createPOModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreatePOModal();
    }
});
</script>
@endsection