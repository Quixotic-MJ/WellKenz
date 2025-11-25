@extends('Purchasing.layout.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between bg-white border-b border-gray-200 px-6 py-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Create Purchase Order</h1>
            <p class="text-sm text-gray-500">Select approved requests and create purchase orders</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('purchasing.po.drafts') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors">
                <i class="fas fa-list mr-1.5"></i>Drafts
            </a>
            <a href="{{ route('purchasing.dashboard') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors">
                <i class="fas fa-arrow-left mr-1.5"></i>Dashboard
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-3 mx-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-2"></i>
                <span class="text-sm text-green-700">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-3 mx-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                <span class="text-sm text-red-700">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Step 1: Purchase Requests --}}
    <div id="pr-selection-section" class="mx-6 bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-900">1. Select Purchase Requests</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Choose approved requests to include</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-500">
                        <span id="selected-pr-count" class="font-medium text-chocolate">0</span> selected
                    </span>
                    <button type="button" 
                            id="proceed-supplier-btn"
                            disabled
                            onclick="proceedToSupplierSelection()"
                            class="px-3 py-1.5 bg-chocolate text-white text-xs font-medium rounded-md hover:bg-chocolate-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-arrow-right mr-1"></i>Next
                    </button>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-3 gap-3">
                <div class="relative">
                    <input type="text" 
                           id="pr-search-filter" 
                           placeholder="Search PRs..."
                           class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                    <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                </div>
                <select id="pr-department-filter" class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
                <select id="pr-priority-filter" class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-8 px-3 py-2">
                            <input type="checkbox" id="select-all-prs" class="text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">PR Number</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="w-16 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="pr-table-body" class="divide-y divide-gray-200">
                    @forelse($approvedRequests ?? [] as $request)
                        <tr class="pr-row hover:bg-gray-50"
                            data-pr="{{ strtolower($request->pr_number ?? '') }}"
                            data-department="{{ strtolower($request->department ?? '') }}"
                            data-priority="{{ $request->priority ?? '' }}">
                            
                            <td class="px-3 py-2">
                                <input type="checkbox" 
                                       name="selected_prs[]" 
                                       value="{{ $request->id }}"
                                       data-total-cost="{{ $request->total_estimated_cost ?? 0 }}"
                                       data-items-count="{{ $request->purchaseRequestItems->count() ?? 0 }}"
                                       class="pr-checkbox text-chocolate focus:ring-chocolate">
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="text-sm font-medium text-gray-900">{{ $request->pr_number ?? 'N/A' }}</div>
                                @if($request->priority === 'urgent')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Urgent
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="text-sm text-gray-900">{{ $request->department ?? 'N/A' }}</div>
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="text-sm text-gray-900">{{ $request->requestedBy->name ?? 'N/A' }}</div>
                            </td>
                            
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                    @if($request->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($request->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($request->priority === 'normal') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($request->priority ?? 'normal') }}
                                </span>
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="text-sm text-gray-900">{{ $request->purchaseRequestItems->count() ?? 0 }}</div>
                            </td>
                            
                            <td class="px-3 py-2 text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ₱{{ number_format($request->total_estimated_cost ?? 0, 0) }}
                                </div>
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="text-sm text-gray-900">{{ $request->request_date?->format('M d') ?? 'N/A' }}</div>
                            </td>
                            
                            <td class="px-3 py-2 text-center">
                                <button type="button" 
                                        onclick="viewPRDetails({{ $request->id }})"
                                        class="text-chocolate hover:text-chocolate-dark text-xs">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                <p class="text-sm">No approved purchase requests found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Step 2: Supplier Selection --}}
    <div id="supplier-section" class="hidden mx-6">
        <form action="{{ route('purchasing.po.store') }}" method="POST" id="purchase-order-form">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="selected-pr-ids">
            
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">2. Configure Purchase Order</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Select supplier and configure items</p>
                        </div>
                        <button type="button" 
                                onclick="resetToPRSelection()" 
                                class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i>Back
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Selected PRs Summary --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                        <h4 class="text-xs font-medium text-blue-800 mb-1">Selected Requests</h4>
                        <div id="selected-prs-summary" class="text-xs text-blue-700"></div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="supplier_id" class="block text-xs font-medium text-gray-700 mb-1">Supplier *</label>
                            <select name="supplier_id" 
                                    id="supplier_id" 
                                    required 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expected_delivery_date" class="block text-xs font-medium text-gray-700 mb-1">Delivery Date *</label>
                            <input type="date" 
                                   name="expected_delivery_date" 
                                   id="expected_delivery_date" 
                                   required 
                                   value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                            @error('expected_delivery_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_terms" class="block text-xs font-medium text-gray-700 mb-1">Payment Terms</label>
                            <input type="number" 
                                   name="payment_terms" 
                                   id="payment_terms" 
                                   value="{{ old('payment_terms', 30) }}" 
                                   min="1" 
                                   max="365"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                            @error('payment_terms')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-gray-900">Items from Selected PRs</h4>
                            <span class="text-xs text-gray-500" id="required-items-count">0 items</span>
                        </div>
                        
                        <div class="border border-gray-200 rounded-md">
                            <table class="min-w-full">
                                <thead class="bg-orange-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Item</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">PR Source</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Qty</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Price</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Order Qty</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="required-items-table" class="divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="6" class="px-3 py-8 text-center text-gray-500 text-sm">
                                            Select a supplier to view items
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="2"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chomotor"
                                  placeholder="Additional notes">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Save Options --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-2">Save Option *</label>
                        <div class="space-y-2">
                            <label class="flex items-center p-2 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                                <input id="save-as-draft" 
                                       name="save_option" 
                                       type="radio" 
                                       value="draft" 
                                       class="text-chocolate focus:ring-chocolate">
                                <div class="ml-2">
                                    <div class="text-xs font-medium text-gray-900">Save as Draft</div>
                                    <div class="text-xs text-gray-500">Save for later editing</div>
                                </div>
                            </label>
                            <label class="flex items-center p-2 border border-gray-200 rounded-md hover:bg-gray-50 cursor-pointer">
                                <input id="create-po" 
                                       name="save_option" 
                                       type="radio" 
                                       value="create" 
                                       class="text-chocolate focus:ring-chocolate">
                                <div class="ml-2">
                                    <div class="text-xs font-medium text-gray-900">Create Purchase Order</div>
                                    <div class="text-xs text-gray-500">Submit for processing</div>
                                </div>
                            </label>
                        </div>
                        @error('save_option')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex justify-end space-x-2">
                        <button type="button" 
                                onclick="resetToPRSelection()" 
                                class="px-4 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                id="submit-btn"
                                class="px-4 py-1.5 bg-chocolate text-white text-sm font-medium rounded-md hover:bg-chocolate-dark transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            <span id="submit-btn-text">Save Draft</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- PR Details Modal --}}
<div id="pr-details-modal" 
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-4 border w-11/12 md:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-medium text-gray-900">Request Details</h3>
            <button onclick="closePRDetailsModal()" 
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="pr-details-content">
            <div class="text-center py-6">
                <i class="fas fa-spinner fa-spin text-xl text-chocolate mb-2"></i>
                <p class="text-gray-600">Loading...</p>
            </div>
        </div>
        
        <div class="flex justify-end mt-4 pt-3 border-t">
            <button type="button" 
                    onclick="closePRDetailsModal()"
                    class="px-4 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class PurchaseOrderManager {
    constructor() {
        this.selectedPRs = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updatePRSelectionUI();
    }

    setupEventListeners() {
        document.getElementById('select-all-prs')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updatePRSelectionUI.bind(this));
        });

        document.getElementById('pr-search-filter')?.addEventListener('input', this.filterPRs.bind(this));
        document.getElementById('pr-department-filter')?.addEventListener('change', this.filterPRs.bind(this));
        document.getElementById('pr-priority-filter')?.addEventListener('change', this.filterPRs.bind(this));
        document.getElementById('supplier_id')?.addEventListener('change', this.handleSupplierChange.bind(this));

        this.setupSaveOptionHandlers();
    }

    handleSelectAll(e) {
        document.querySelectorAll('.pr-checkbox:not(:disabled)').forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        this.updatePRSelectionUI();
    }

    updatePRSelectionUI() {
        const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
        const selectedCount = checkboxes.length;
        const selectedCountElement = document.getElementById('selected-pr-count');
        const proceedBtn = document.getElementById('proceed-supplier-btn');
        
        if (selectedCountElement) selectedCountElement.textContent = selectedCount;
        if (proceedBtn) proceedBtn.disabled = selectedCount === 0;
        
        const selectAll = document.getElementById('select-all-prs');
        if (selectAll) {
            const allCheckboxes = document.querySelectorAll('.pr-checkbox:not(:disabled)');
            const allChecked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && selectedCount > 0;
        }
    }

    filterPRs() {
        const searchTerm = document.getElementById('pr-search-filter')?.value.toLowerCase() || '';
        const departmentFilter = document.getElementById('pr-department-filter')?.value.toLowerCase() || '';
        const priorityFilter = document.getElementById('pr-priority-filter')?.value.toLowerCase() || '';

        document.querySelectorAll('.pr-row').forEach(row => {
            const prText = row.dataset.pr || '';
            const departmentText = row.dataset.department || '';
            const priorityText = row.dataset.priority || '';
            
            const matchesSearch = !searchTerm || prText.includes(searchTerm) || departmentText.includes(searchTerm);
            const matchesDepartment = !departmentFilter || departmentText.includes(departmentFilter);
            const matchesPriority = !priorityFilter || priorityText.includes(priorityFilter);
            
            row.style.display = matchesSearch && matchesDepartment && matchesPriority ? '' : 'none';
        });
    }

    proceedToSupplierSelection() {
        const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
        if (checkboxes.length === 0) {
            this.showAlert('Please select at least one purchase request.', 'warning');
            return;
        }
        
        this.selectedPRs = [];
        checkboxes.forEach(checkbox => {
            const prId = parseInt(checkbox.value);
            const row = checkbox.closest('tr');
            
            this.selectedPRs.push({
                id: prId,
                pr_number: row.querySelector('td:nth-child(2) .text-sm').textContent.trim(),
                department: row.querySelector('td:nth-child(3) .text-sm').textContent.trim(),
                total_estimated_cost: parseFloat(checkbox.dataset.totalCost) || 0,
                items_count: parseInt(checkbox.dataset.itemsCount) || 0
            });
        });
        
        this.showSupplierSection();
    }

    showSupplierSection() {
        document.getElementById('pr-selection-section').classList.add('hidden');
        document.getElementById('supplier-section').classList.remove('hidden');
        this.showSelectedPRsSummary();
        document.getElementById('selected-pr-ids').value = this.selectedPRs.map(pr => pr.id).join(',');
        document.getElementById('supplier-section').scrollIntoView({ behavior: 'smooth' });
    }

    showSelectedPRsSummary() {
        const summaryDiv = document.getElementById('selected-prs-summary');
        if (this.selectedPRs.length === 0) return;
        
        let html = '';
        let totalCost = 0;
        
        this.selectedPRs.forEach(pr => {
            html += `<div>${pr.pr_number} (${pr.department || 'N/A'}) - ₱${pr.total_estimated_cost.toLocaleString()}</div>`;
            totalCost += pr.total_estimated_cost;
        });
        
        html += `<div class="font-medium border-t pt-1 mt-1">Total: ₱${totalCost.toLocaleString()}</div>`;
        summaryDiv.innerHTML = html;
    }

    handleSupplierChange(e) {
        const supplierId = e.target.value;
        const table = document.getElementById('required-items-table');
        const counter = document.getElementById('required-items-count');
        
        if (supplierId) {
            this.loadSupplierItems(supplierId);
        } else {
            table.innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-500 text-sm">Select a supplier to view items</td></tr>';
            counter.textContent = '0 items';
        }
    }

    loadSupplierItems(supplierId) {
        const table = document.getElementById('required-items-table');
        
        table.innerHTML = `
            <tr>
                <td colspan="6" class="px-3 py-8 text-center">
                    <i class="fas fa-spinner fa-spin text-chocolate mb-2"></i>
                    <p class="text-sm text-gray-600">Loading items...</p>
                </td>
            </tr>
        `;
        
        const selectedPRIds = this.selectedPRs.map(pr => pr.id);
        
        if (selectedPRIds.length === 0) {
            table.innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-500 text-sm">No requests selected</td></tr>';
            return;
        }
        
        fetch(`/purchasing/api/suppliers/${supplierId}/items-for-prs`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ pr_ids: selectedPRIds.join(',') })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            this.renderSupplierItems(data.items || { available: [], unavailable: [] });
        })
        .catch(error => {
            console.error('Error loading items:', error);
            table.innerHTML = `<tr><td colspan="6" class="px-3 py-8 text-center text-red-500 text-sm">Error: ${error.message}</td></tr>`;
        });
    }

    renderSupplierItems(itemsData) {
        const availableItems = itemsData.available || [];
        const table = document.getElementById('required-items-table');
        const counter = document.getElementById('required-items-count');
        
        if (availableItems.length === 0) {
            table.innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-500 text-sm">No items available from this supplier</td></tr>';
            counter.textContent = '0 items';
            return;
        }
        
        let html = '';
        let itemIndex = 0;
        
        availableItems.forEach(item => {
            const total = (parseFloat(item.total_requested_quantity) || 0) * (parseFloat(item.unit_price) || 0);
            html += `
                <tr>
                    <td class="px-3 py-2">
                        <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                        <div class="text-xs text-gray-500">${item.item_code}</div>
                    </td>
                    <td class="px-3 py-2 text-sm text-gray-900">${item.source_prs.map(pr => pr.pr_number).join(', ')}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">${parseFloat(item.total_requested_quantity).toFixed(2)}</td>
                    <td class="px-3 py-2 text-sm text-gray-900">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="px-3 py-2">
                        <input type="number" 
                               name="items[${itemIndex}][quantity_ordered]" 
                               value="${parseFloat(item.total_requested_quantity)}" 
                               min="0.001" 
                               step="0.001"
                               class="w-20 px-2 py-1 text-xs border border-gray-300 rounded"
                               onchange="purchaseOrderManager.calculateItemTotal(this)">
                    </td>
                    <td class="px-3 py-2 text-sm font-medium text-gray-900 item-total">₱${total.toFixed(2)}</td>
                </tr>
                <input type="hidden" name="items[${itemIndex}][item_id]" value="${item.item_id}">
                <input type="hidden" name="items[${itemIndex}][unit_price]" value="${item.unit_price}">
                <input type="hidden" name="items[${itemIndex}][source_pr_id]" value="${this.selectedPRs[0].id}">
            `;
            itemIndex++;
        });
        
        table.innerHTML = html;
        counter.textContent = `${availableItems.length} items`;
    }

    calculateItemTotal(input) {
        const quantity = parseFloat(input.value) || 0;
        const row = input.closest('tr');
        const unitPriceInput = row.querySelector('input[name$="[unit_price]"]');
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const total = quantity * unitPrice;
        const totalCell = row.querySelector('.item-total');
        totalCell.textContent = `₱${total.toFixed(2)}`;
    }

    setupSaveOptionHandlers() {
        const draftRadio = document.getElementById('save-as-draft');
        const createRadio = document.getElementById('create-po');
        const submitBtn = document.getElementById('submit-btn-text');
        
        if (draftRadio && createRadio) {
            draftRadio.addEventListener('change', this.updateSubmitButton.bind(this));
            createRadio.addEventListener('change', this.updateSubmitButton.bind(this));
        }
    }

    updateSubmitButton() {
        const draftRadio = document.getElementById('save-as-draft');
        const submitBtnText = document.getElementById('submit-btn-text');
        
        if (draftRadio && submitBtnText) {
            submitBtnText.textContent = draftRadio.checked ? 'Save Draft' : 'Create PO';
        }
    }

    resetToPRSelection() {
        document.getElementById('pr-selection-section').classList.remove('hidden');
        document.getElementById('supplier-section').classList.add('hidden');
        
        this.selectedPRs = [];
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => checkbox.checked = false);
        this.updatePRSelectionUI();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    showAlert(message, type = 'info') {
        const alertClass = type === 'warning' ? 'bg-yellow-50 border-yellow-400 text-yellow-700' : 
                          type === 'error' ? 'bg-red-50 border-red-400 text-red-700' : 
                          'bg-blue-50 border-blue-400 text-blue-700';
        const iconClass = type === 'warning' ? 'fas fa-exclamation-triangle' : 
                         type === 'error' ? 'fas fa-exclamation-circle' : 
                         'fas fa-info-circle';
        
        const alertHtml = `
            <div class="fixed top-4 right-4 ${alertClass} border p-3 rounded-md shadow-lg z-50">
                <div class="flex items-center">
                    <i class="${iconClass} mr-2"></i>
                    <span class="text-sm">${message}</span>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const alert = document.querySelector('.fixed.top-4.right-4');
            if (alert) alert.remove();
        }, 3000);
    }
}

class PRDetailsModal {
    constructor() {
        this.modal = document.getElementById('pr-details-modal');
        this.content = document.getElementById('pr-details-content');
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.close();
            }
        });
    }

    open(prId) {
        this.modal.classList.remove('hidden');
        this.content.innerHTML = `
            <div class="text-center py-6">
                <i class="fas fa-spinner fa-spin text-xl text-chocolate mb-2"></i>
                <p class="text-gray-600">Loading...</p>
            </div>
        `;
        
        fetch(`/purchasing/api/purchase-requests/${prId}`)
            .then(response => response.json())
            .then(data => this.displayPRDetails(data.purchaseRequest))
            .catch(error => {
                this.content.innerHTML = `
                    <div class="text-center py-6 text-red-600">
                        <i class="fas fa-exclamation-triangle text-xl mb-2"></i>
                        <p>Error loading details: ${error.message}</p>
                    </div>
                `;
            });
    }

    close() {
        this.modal.classList.add('hidden');
    }

    displayPRDetails(prData) {
        const items = prData.purchase_request_items || [];
        const itemsHtml = items.length > 0 ? items.map(item => `
            <tr>
                <td class="px-3 py-2">
                    <div class="text-sm font-medium">${item.item?.name || 'Unknown'}</div>
                    <div class="text-xs text-gray-500">${item.item?.item_code || 'N/A'}</div>
                </td>
                <td class="px-3 py-2 text-sm">${parseFloat(item.quantity_requested) || 0}</td>
                <td class="px-3 py-2 text-sm">₱${(parseFloat(item.unit_price_estimate) || 0).toFixed(2)}</td>
                <td class="px-3 py-2 text-sm font-medium">₱${((parseFloat(item.quantity_requested) || 0) * (parseFloat(item.unit_price_estimate) || 0)).toFixed(2)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">No items</td></tr>';

        this.content.innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-xs font-medium text-gray-500">PR Number</label>
                        <p class="font-medium">${prData.pr_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Department</label>
                        <p class="font-medium">${prData.department || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Requester</label>
                        <p class="font-medium">${prData.requested_by?.name || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Total Cost</label>
                        <p class="font-medium">₱${(parseFloat(prData.total_estimated_cost) || 0).toLocaleString()}</p>
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-2">Items</label>
                    <div class="border rounded-md">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs">Item</th>
                                    <th class="px-3 py-2 text-left text-xs">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs">Price</th>
                                    <th class="px-3 py-2 text-left text-xs">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Notes</label>
                    <p class="text-sm text-gray-700">${prData.notes || 'No notes provided'}</p>
                </div>
            </div>
        `;
    }
}

// Initialize
let purchaseOrderManager, prDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    purchaseOrderManager = new PurchaseOrderManager();
    prDetailsModal = new PRDetailsModal();
});

// Global functions
function viewPRDetails(prId) { prDetailsModal.open(prId); }
function closePRDetailsModal() { prDetailsModal.close(); }
function proceedToSupplierSelection() { purchaseOrderManager.proceedToSupplierSelection(); }
function resetToPRSelection() { purchaseOrderManager.resetToPRSelection(); }
</script>
@endpush