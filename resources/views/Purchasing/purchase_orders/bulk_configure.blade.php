@extends('Purchasing.layout.app')

@section('title', 'Bulk Configure Purchase Orders')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 font-sans text-gray-600 pb-24">

    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Bulk Configure Purchase Orders</h1>
            <p class="text-sm text-gray-500">Select multiple purchase requests and create purchase orders for multiple suppliers at once.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.po.create') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
               <i class="fas fa-arrow-left mr-2 opacity-70 group-hover:opacity-100"></i> Single PO
            </a>
            <a href="{{ route('purchasing.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
               <i class="fas fa-home mr-2 opacity-70 group-hover:opacity-100"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- 2. ALERTS --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="text-sm font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            <span class="text-sm font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            <span class="text-sm font-bold text-yellow-800">{{ session('warning') }}</span>
        </div>
    @endif

    {{-- STEP 1: PURCHASE REQUEST SELECTION --}}
    <div id="bulk-pr-selection-section" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">

        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">1. Select Requests</h3>
                <p class="text-xs text-gray-500 mt-0.5">Choose approved purchase requests to consolidate into bulk purchase orders.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bulk-selected-pr-count" class="font-bold text-chocolate text-sm">0</span> selected
                </div>
                <button type="button"
                        id="bulk-proceed-btn"
                        disabled
                        onclick="bulkConfigureManager.proceedToBulkGrouping()"
                        class="inline-flex items-center px-5 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Analyze & Group <i class="fas fa-layer-group ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Filters Toolbar --}}
        <div class="px-6 py-4 bg-white border-b border-border-soft grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative group">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                <input type="text" id="bulk-pr-search-filter" placeholder="Search PRs..."
                       class="w-full pl-10 pr-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
            </div>
            <select id="bulk-pr-department-filter" class="px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
            <select id="bulk-pr-priority-filter" class="px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all">
                <option value="">All Priorities</option>
                <option value="low">Low Priority</option>
                <option value="normal">Normal Priority</option>
                <option value="high">High Priority</option>
                <option value="urgent">Urgent Priority</option>
            </select>
        </div>

        {{-- PR Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-12 px-6 py-3 text-left">
                            <input type="checkbox" id="bulk-select-all-prs" class="rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">PR Number</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Items</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Est. Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Date</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display w-20">Action</th>
                    </tr>
                </thead>
                <tbody id="bulk-pr-table-body" class="bg-white divide-y divide-gray-100">
                    @forelse($approvedRequests ?? [] as $request)
                        <tr class="bulk-pr-row hover:bg-cream-bg/50 transition-colors group cursor-pointer"
                            onclick="if(event.target.type !== 'checkbox' && event.target.tagName !== 'BUTTON' && event.target.tagName !== 'I') document.getElementById('bulk-chk-{{ $request->id }}').click()"
                            data-pr="{{ strtolower($request->pr_number ?? '') }}"
                            data-department="{{ strtolower($request->department ?? '') }}"
                            data-priority="{{ $request->priority ?? '' }}">

                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       id="bulk-chk-{{ $request->id }}"
                                       name="bulk_selected_prs[]"
                                       value="{{ $request->id }}"
                                       data-total-cost="{{ $request->total_estimated_cost ?? 0 }}"
                                       data-items-count="{{ $request->purchaseRequestItems->count() ?? 0 }}"
                                       class="bulk-pr-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-chocolate group-hover:text-caramel transition-colors">
                                #{{ $request->pr_number ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $request->department ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-chocolate/10 flex items-center justify-center text-xs font-bold text-chocolate">
                                        {{ substr($request->requestedBy->name ?? 'U', 0, 1) }}
                                    </div>
                                    {{ $request->requestedBy->name ?? 'N/A' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $pClass = match($request->priority) {
                                        'urgent' => 'bg-red-50 text-red-700 border-red-100',
                                        'high' => 'bg-orange-50 text-orange-700 border-orange-100',
                                        default => 'bg-blue-50 text-blue-700 border-blue-100'
                                    };
                                    $pIcon = match($request->priority) {
                                        'urgent' => 'fa-exclamation-circle',
                                        'high' => 'fa-arrow-up',
                                        default => 'fa-minus'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border {{ $pClass }}">
                                    <i class="fas {{ $pIcon }} mr-1.5 text-[10px]"></i> {{ ucfirst($request->priority ?? 'normal') }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center font-mono">
                                {{ $request->purchaseRequestItems->count() ?? 0 }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $totalItems = $request->purchaseRequestItems->count() ?? 0;
                                    $orderedItems = 0; // This would need to be calculated from backend
                                    // For now, show as "Available" since we can't calculate ordered items easily in view
                                    // In a real implementation, you'd pass this data from controller
                                    $statusClass = 'bg-green-50 text-green-700 border-green-100';
                                    $statusText = 'Available';
                                    $statusIcon = 'fa-check-circle';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusClass }}">
                                    <i class="fas {{ $statusIcon }} mr-1.5 text-[10px]"></i> {{ $statusText }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-chocolate">
                                ₱{{ number_format($request->total_estimated_cost ?? 0, 2) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->request_date?->format('M d, Y') ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button type="button"
                                        onclick="viewPRDetails({{ $request->id }})"
                                        class="text-gray-400 hover:text-caramel hover:bg-cream-bg p-2 rounded-lg transition-all tooltip"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-inbox text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <p class="font-display text-lg font-bold text-chocolate">No Approved Requests</p>
                                    <p class="text-sm text-gray-500 mt-1">There are currently no approved purchase requests to process.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- STEP 2: BULK SUPPLIER CONFIGURATION --}}
    <div id="bulk-configuration-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">

        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">2. Bulk Supplier Configuration</h3>
                <p class="text-xs text-gray-500 mt-0.5">Configure delivery dates and payment terms for each supplier bucket.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bulk-config-count" class="font-bold text-chocolate text-sm">0</span> suppliers configured
                </div>
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bulk-total-pos" class="font-bold text-chocolate text-sm">0</span> POs to create
                </div>
            </div>
        </div>

        <form action="{{ route('purchasing.po.bulk-create') }}" method="POST" id="bulk-po-form">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="bulk-selected-pr-ids">

            {{-- Configuration Cards --}}
            <div id="bulk-config-cards-container" class="p-6 space-y-6">
                <div class="text-center py-12 text-gray-400 italic">Run the analysis to view supplier configurations.</div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-between items-center">
                <button type="button" onclick="bulkConfigureManager.returnToPRSelection()"
                        class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to PR Selection
                </button>
                <button type="button" onclick="bulkConfigureManager.openBulkConfirmationModal()"
                        class="px-6 py-3 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Create All Purchase Orders
                </button>
            </div>
        </form>
    </div>

</div>

{{-- PR DETAILS MODAL --}}
<div id="bulk-pr-details-modal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl border border-border-soft overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
            <h3 class="font-display text-lg font-bold text-chocolate">Request Details</h3>
            <button onclick="closePRDetailsModal()" class="text-gray-400 hover:text-chocolate transition-colors p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="bulk-pr-details-content" class="p-6 overflow-y-auto max-h-[60vh] custom-scrollbar bg-white"></div>
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end">
            <button onclick="closePRDetailsModal()" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition-colors text-sm shadow-sm">Close</button>
        </div>
    </div>
</div>

{{-- BULK CONFIRMATION MODAL --}}
<div id="bulk-confirmation-modal" class="hidden fixed inset-0 z-[60] bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg border border-border-soft p-6 transform transition-all animate-fade-in-down">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-chocolate/10 rounded-full flex items-center justify-center mx-auto mb-4 text-chocolate text-2xl border border-chocolate/20">
                <i class="fas fa-layer-group"></i>
            </div>
            <h3 class="font-display text-xl font-bold text-chocolate mb-2">Confirm Bulk Purchase Orders</h3>
            <p class="text-sm text-gray-500">You are about to create <span id="bulk-modal-po-count" class="font-bold text-chocolate">0</span> purchase orders for <span id="bulk-modal-supplier-count" class="font-bold text-chocolate">0</span> suppliers.</p>
            <div class="mt-4 p-4 bg-cream-bg rounded-lg">
                <p class="text-sm text-gray-600">Total Items: <span id="bulk-modal-total-items" class="font-bold text-chocolate">0</span></p>
                <p class="text-sm text-gray-600">Total Value: <span id="bulk-modal-total-value" class="font-bold text-chocolate">₱0.00</span></p>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="button" onclick="document.getElementById('bulk-confirmation-modal').classList.add('hidden')"
                    class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" onclick="submitBulkPOForm()"
                    class="flex-1 px-4 py-2 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md">
                Create All POs
            </button>
        </div>
    </div>
</div>

<script>
const BULK_GROUP_PR_ITEMS_URL = "{{ url('/purchasing/api/group-pr-items') }}";

class BulkConfigureManager {
    constructor() {
        this.selectedPRs = [];
        this.bulkConfigData = [];
        this.init();
    }

    init() {
        document.getElementById('bulk-select-all-prs')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.bulk-pr-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updatePRSelectionUI.bind(this));
        });

        // Search Filters
        document.getElementById('bulk-pr-search-filter')?.addEventListener('input', this.filterPRs.bind(this));
        document.getElementById('bulk-pr-department-filter')?.addEventListener('change', this.filterPRs.bind(this));
        document.getElementById('bulk-pr-priority-filter')?.addEventListener('change', this.filterPRs.bind(this));
    }

    handleSelectAll(e) {
       document.querySelectorAll('.bulk-pr-checkbox:not(:disabled)').forEach(checkbox => {
           checkbox.checked = e.target.checked;
       });
       this.updatePRSelectionUI();
    }

    updatePRSelectionUI() {
        const checkboxes = document.querySelectorAll('.bulk-pr-checkbox:checked');
        const count = checkboxes.length;

        document.getElementById('bulk-selected-pr-count').textContent = count;
        document.getElementById('bulk-proceed-btn').disabled = count === 0;

        // Update Master Checkbox state
        const selectAll = document.getElementById('bulk-select-all-prs');
        const allCheckboxes = document.querySelectorAll('.bulk-pr-checkbox');
        if(selectAll && allCheckboxes.length > 0) {
            selectAll.checked = count === allCheckboxes.length;
            selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
        }
    }

    filterPRs() {
        const searchTerm = document.getElementById('bulk-pr-search-filter')?.value.toLowerCase() || '';
        const departmentFilter = document.getElementById('bulk-pr-department-filter')?.value.toLowerCase() || '';
        const priorityFilter = document.getElementById('bulk-pr-priority-filter')?.value.toLowerCase() || '';

        let visibleCount = 0;
        document.querySelectorAll('.bulk-pr-row').forEach(row => {
            const prText = row.dataset.pr || '';
            const departmentText = row.dataset.department || '';
            const priorityText = row.dataset.priority || '';

            const matchesSearch = !searchTerm || prText.includes(searchTerm) || departmentText.includes(searchTerm);
            const matchesDepartment = !departmentFilter || departmentText.includes(departmentFilter);
            const matchesPriority = !priorityFilter || priorityText.includes(priorityFilter);

            const shouldShow = matchesSearch && matchesDepartment && matchesPriority;
            row.style.display = shouldShow ? '' : 'none';

            if (shouldShow) {
                visibleCount++;
            }
        });

        console.log(`Bulk PR Filter: Showing ${visibleCount} of ${document.querySelectorAll('.bulk-pr-row').length} PRs`);
    }

    proceedToBulkGrouping() {
        const checkboxes = document.querySelectorAll('.bulk-pr-checkbox:checked');
        this.selectedPRs = Array.from(checkboxes).map(cb => cb.value);
        document.getElementById('bulk-selected-pr-ids').value = this.selectedPRs.join(',');

        if (this.selectedPRs.length === 0) {
            alert('Please select at least one purchase request.');
            return;
        }

        document.getElementById('bulk-pr-selection-section').classList.add('hidden');
        this.loadBulkSupplierBuckets();
    }

    returnToPRSelection() {
        document.getElementById('bulk-configuration-section').classList.add('hidden');
        document.getElementById('bulk-pr-selection-section').classList.remove('hidden');

        // Reset selections
        this.selectedPRs = [];
        this.bulkConfigData = [];
        document.getElementById('bulk-selected-pr-ids').value = '';
        this.updatePRSelectionUI();
    }

    loadBulkSupplierBuckets() {
        const container = document.getElementById('bulk-config-cards-container');
        container.innerHTML = '<div class="text-center py-12"><i class="fas fa-spinner fa-spin text-chocolate text-2xl"></i><div class="mt-2 text-gray-500">Analyzing supplier buckets...</div></div>';

        fetch(BULK_GROUP_PR_ITEMS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ pr_ids: this.selectedPRs })
        })
        .then(res => res.json())
        .then(data => {
            this.bulkConfigData = data.buckets || [];
            this.renderBulkConfigCards();
            this.updateBulkStats();
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = '<div class="text-center py-12 text-red-500">Failed to analyze suppliers. Please try again.</div>';
        });
    }

    renderBulkConfigCards() {
        if (this.bulkConfigData.length === 0) {
            document.getElementById('bulk-config-cards-container').innerHTML = '<div class="text-center py-12 text-gray-400 italic">No supplier buckets were generated.</div>';
            return;
        }

        document.getElementById('bulk-configuration-section').classList.remove('hidden');

        const container = document.getElementById('bulk-config-cards-container');
        container.innerHTML = '';

        this.bulkConfigData.forEach((bucket, index) => {
            const card = document.createElement('div');
            card.className = 'border border-border-soft rounded-xl shadow-sm overflow-hidden';
            card.dataset.bucketIndex = index;

            const itemsList = bucket.items.map(item => `
                <div class="flex justify-between items-center py-2 px-4 bg-gray-50 border-b border-gray-100 last:border-0">
                    <div>
                        <span class="font-semibold text-gray-800 text-sm">${item.item_name}</span>
                        <span class="text-xs text-gray-500 ml-2">${item.item_code}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-chocolate">Qty: ${parseFloat(item.qty_remaining).toFixed(2)}</span>
                        <span class="text-xs text-gray-500 block">₱${parseFloat(item.suggested_price).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');

            card.innerHTML = `
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500">Supplier</div>
                        <div class="text-lg font-display font-bold text-chocolate">${bucket.supplier.name}</div>
                        <div class="text-xs text-gray-500">${bucket.supplier.supplier_code || 'No code'}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Total Items</div>
                        <div class="text-xl font-bold text-chocolate">${bucket.totals.item_count || 0}</div>
                        <div class="text-xs text-gray-500">₱${(bucket.totals.estimated_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-2">Expected Delivery Date *</label>
                            <input type="date" name="bulk_config[${index}][expected_delivery_date]"
                                   value="${new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}"
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-2">Payment Terms (days)</label>
                            <input type="number" name="bulk_config[${index}][payment_terms]"
                                   value="30"
                                   min="0"
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all text-sm shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-chocolate mb-2">Notes</label>
                        <textarea name="bulk_config[${index}][notes]" rows="2"
                                  placeholder="Internal notes for this supplier..."
                                  class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel resize-none text-sm transition-all placeholder-gray-400 shadow-sm"></textarea>
                    </div>

                    <input type="hidden" name="bulk_config[${index}][supplier_id]" value="${bucket.supplier.id}">

                    <div class="border-t border-gray-100 pt-4">
                        <h5 class="font-bold text-chocolate text-sm mb-3">Items for this Supplier</h5>
                        <div class="max-h-40 overflow-y-auto space-y-1">
                            ${itemsList}
                        </div>
                    </div>
                </div>
            `;

            container.appendChild(card);
        });
    }

    updateBulkStats() {
        const supplierCount = this.bulkConfigData.length;
        const totalItems = this.bulkConfigData.reduce((sum, bucket) => sum + (bucket.totals?.item_count || 0), 0);

        document.getElementById('bulk-config-count').textContent = supplierCount;
        document.getElementById('bulk-total-pos').textContent = supplierCount;
    }

    openBulkConfirmationModal() {
        const supplierCount = this.bulkConfigData.length;
        const totalItems = this.bulkConfigData.reduce((sum, bucket) => sum + (bucket.totals?.item_count || 0), 0);
        const totalValue = this.bulkConfigData.reduce((sum, bucket) => sum + (bucket.totals?.estimated_amount || 0), 0);

        document.getElementById('bulk-modal-po-count').textContent = supplierCount;
        document.getElementById('bulk-modal-supplier-count').textContent = supplierCount;
        document.getElementById('bulk-modal-total-items').textContent = totalItems;
        document.getElementById('bulk-modal-total-value').textContent = '₱' + totalValue.toLocaleString('en-PH', {minimumFractionDigits: 2});

        document.getElementById('bulk-confirmation-modal').classList.remove('hidden');
    }
}

// Simple Modal Logic for PR Details (Preserved from original)
class BulkPRDetailsModal {
    constructor() {
        this.modal = document.getElementById('bulk-pr-details-modal');
        this.content = document.getElementById('bulk-pr-details-content');
    }

    open(prId) {
        this.modal.classList.remove('hidden');
        this.content.innerHTML = '<div class="p-12 text-center"><i class="fas fa-spinner fa-spin text-chocolate text-2xl"></i></div>';

        fetch(`/purchasing/api/purchase-requests/${prId}`)
            .then(response => response.json())
            .then(data => this.displayPRDetails(data.purchaseRequest));
    }

    close() { this.modal.classList.add('hidden'); }

    displayPRDetails(prData) {
        const items = prData.purchaseRequestItems || [];

        const itemsHtml = items.map(i => {
            const itemName = i.item_name || i.item?.name || 'Unknown Item';
            const itemCode = i.item_code || i.item?.item_code || 'N/A';
            const categoryName = i.category_name || i.item?.category?.name || 'No Category';
            const unitSymbol = i.unit_symbol || i.item?.unit?.symbol || '';
            const quantityRequested = i.quantity_requested || 0;
            const unitPriceEstimate = i.unit_price_estimate || 0;
            const remainingQuantity = i.remaining_quantity !== undefined ? i.remaining_quantity : quantityRequested;
            const isFullyOrdered = i.is_fully_ordered || false;

            return `
                <div class="border-b border-gray-200 py-3">
                    <div class="flex justify-between mb-1">
                        <span class="font-semibold text-gray-800">${itemName}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-chocolate font-bold">${quantityRequested} ${unitSymbol}</span>
                            ${isFullyOrdered ? '<span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Fully Ordered</span>' : ''}
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        <span class="mr-2">Code: ${itemCode}</span>
                        <span class="mr-2">Category: ${categoryName}</span>
                        <span>Est. Price: ₱${unitPriceEstimate.toFixed(2)}</span>
                        ${remainingQuantity !== quantityRequested ? `<span class="ml-2 text-orange-600">Remaining: ${remainingQuantity} ${unitSymbol}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        const prInfoHtml = `
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg mb-6">
                <div>
                    <div class="text-sm text-gray-500">PR Number</div>
                    <div class="font-bold text-chocolate text-lg">#${prData.pr_number || 'N/A'}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Department</div>
                    <div class="font-semibold text-gray-800">${prData.department || 'N/A'}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Priority</div>
                    <div class="font-semibold ${prData.priority === 'urgent' ? 'text-red-600' : prData.priority === 'high' ? 'text-orange-600' : 'text-blue-600'}">${(prData.priority || 'normal').toUpperCase()}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Requested By</div>
                    <div class="font-semibold text-gray-800">${prData.requestedBy?.name || 'N/A'}</div>
                </div>
            </div>
        `;

        this.content.innerHTML = `
            <div class="space-y-4">
                ${prInfoHtml}
                <div class="mt-4">
                    <h4 class="font-bold text-chocolate mb-3">Items Requested</h4>
                    ${itemsHtml || '<div class="text-gray-500 italic">No items found in this request.</div>'}
                </div>
                <div class="mt-4 flex justify-end">
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Total Items:</span> ${items.length}
                    </div>
                </div>
            </div>
        `;
    }
}

// Global functions
function viewPRDetails(id) { bulkPRDetailsModal.open(id); }
function closePRDetailsModal() { bulkPRDetailsModal.close(); }

function submitBulkPOForm() {
    const form = document.getElementById('bulk-po-form');

    // Basic validation
    const configCards = document.querySelectorAll('[data-bucket-index]');
    let hasValidConfig = true;

    configCards.forEach(card => {
        const deliveryDate = card.querySelector('input[name*="[expected_delivery_date]"]');
        if (deliveryDate && !deliveryDate.value) {
            hasValidConfig = false;
            deliveryDate.focus();
        }
    });

    if (!hasValidConfig) {
        alert('Please fill in all required delivery dates.');
        return false;
    }

    // Hide modal and submit form
    document.getElementById('bulk-confirmation-modal').classList.add('hidden');
    form.submit();

    return true;
}

// Initialize
let bulkConfigureManager;
let bulkPRDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    bulkConfigureManager = new BulkConfigureManager();
    bulkPRDetailsModal = new BulkPRDetailsModal();
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endsection