@extends('Purchasing.layout.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 font-sans text-gray-600 pb-24">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Create Purchase Order</h1>
            <p class="text-sm text-gray-500">Select approved requests and convert them into official purchase orders.</p>
        </div>
        <div class="flex items-center gap-3">

            <a href="{{ route('purchasing.dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-arrow-left mr-2 opacity-70 group-hover:opacity-100"></i> Dashboard
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

    {{-- STEP 1: PURCHASE REQUEST SELECTION --}}
    <div id="pr-selection-section" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        
        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">1. Select Requests</h3>
                <p class="text-xs text-gray-500 mt-0.5">Choose approved purchase requests to consolidate.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="selected-pr-count" class="font-bold text-chocolate text-sm">0</span> selected
                </div>
                <button type="button" 
                        id="proceed-supplier-btn"
                        disabled
                        onclick="purchaseOrderManager.proceedToGrouping()"
                        class="inline-flex items-center px-5 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Analyze & Group <i class="fas fa-sitemap ml-2"></i>
                </button>
            </div>
        </div>

        {{-- Filters Toolbar --}}
        <div class="px-6 py-4 bg-white border-b border-border-soft grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative group">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                <input type="text" id="pr-search-filter" placeholder="Search PRs..."
                       class="w-full pl-10 pr-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
            </div>
            <select id="pr-department-filter" class="px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
            <select id="pr-priority-filter" class="px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all">
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
                            <input type="checkbox" id="select-all-prs" class="rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
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
                <tbody id="pr-table-body" class="bg-white divide-y divide-gray-100">
                    @forelse($approvedRequests ?? [] as $request)
                        <tr class="pr-row hover:bg-cream-bg/50 transition-colors group cursor-pointer"
                            onclick="if(event.target.type !== 'checkbox' && event.target.tagName !== 'BUTTON' && event.target.tagName !== 'I') document.getElementById('chk-{{ $request->id }}').click()"
                            data-pr="{{ strtolower($request->pr_number ?? '') }}"
                            data-department="{{ strtolower($request->department ?? '') }}"
                            data-priority="{{ $request->priority ?? '' }}">
                            
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       id="chk-{{ $request->id }}"
                                       name="selected_prs[]" 
                                       value="{{ $request->id }}"
                                       data-total-cost="{{ $request->total_estimated_cost ?? 0 }}"
                                       data-items-count="{{ $request->purchaseRequestItems->count() ?? 0 }}"
                                       class="pr-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
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

    {{-- STEP 2A: SUPPLIER BUCKET REVIEW --}}
    <div id="supplier-grouping-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        
        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">2. Automatic Supplier Buckets</h3>
                <p class="text-xs text-gray-500 mt-0.5">Review how items were auto-assigned per supplier. Move items if needed, then load a bucket to configure pricing.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bucket-count" class="font-bold text-chocolate text-sm">0</span> buckets
                </div>
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bucket-item-count" class="font-bold text-chocolate text-sm">0</span> items
                </div>
            </div>
        </div>

        {{-- Bucket Cards --}}
        <div id="bucket-cards-container" class="p-6 space-y-6">
            <div class="text-center py-12 text-gray-400 italic">Run the analysis to view supplier buckets.</div>
        </div>

        {{-- Unassigned Items --}}
        <div id="unassigned-items-container" class="hidden border-t border-border-soft bg-gray-50 px-6 py-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h4 class="font-bold text-chocolate text-sm">Unassigned Items</h4>
                    <p class="text-xs text-gray-500">These items have no supplier mapping yet. Update supplier records to include them.</p>
                </div>
                <span id="unassigned-count" class="text-xs font-semibold text-red-600">0 item(s)</span>
            </div>
            <div id="unassigned-items-list" class="grid gap-3"></div>
        </div>
    </div>

    {{-- STEP 2: CONFIGURE ORDER (Shopping Cart Style) --}}
    <div id="supplier-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        <form action="{{ route('purchasing.po.store') }}" method="POST" id="purchase-order-form">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="selected-pr-ids">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-chocolate">2. Configure Order</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Select a supplier and check the items you wish to order from them.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="purchaseOrderManager.returnToBucketReview()" 
                            class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <i class="fas fa-layer-group mr-2"></i> Buckets
                    </button>
                    <button type="button" onclick="purchaseOrderManager.resetToPRSelection()" 
                            class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Select PRs
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-8">
                
                {{-- Form Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="supplier_id" class="block text-sm font-bold text-chocolate mb-2">Supplier *</label>
                        <div class="relative">
                            {{-- Added onchange handler to load suggested prices --}}
                            <select name="supplier_id" id="supplier_id" required onchange="purchaseOrderManager.handleSupplierChange(this.value)"
                                    class="w-full pl-4 pr-10 py-3 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm appearance-none cursor-pointer shadow-sm">
                                <option value="">Select Supplier...</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-bold text-chocolate mb-2">Delivery Date *</label>
                        <input type="date" name="expected_delivery_date" required 
                               value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>

                    <div>
                        <label for="payment_terms" class="block text-sm font-bold text-chocolate mb-2">Payment Terms</label>
                        <input type="number" name="payment_terms" value="30"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>
                </div>

                {{-- Items Table (Shopping Cart) --}}
                <div class="border border-border-soft rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-border-soft flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-sm">Select Items to Order</h4>
                        <div class="text-xs text-gray-500">
                            <span id="selected-items-counter" class="font-bold text-chocolate">0</span> items selected for this PO
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-white">
                                <tr>
                                    <th class="w-10 px-4 py-3 text-center">
                                        <input type="checkbox" id="select-all-items" onclick="purchaseOrderManager.toggleAllItems(this)" class="rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">PR Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">Remaining</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 140px;">Order Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider" style="width: 140px;">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody id="po-items-table" class="bg-white divide-y divide-gray-50">
                                {{-- JS will populate this --}}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-bold text-gray-600">Grand Total:</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold text-chocolate" id="grand-total-display">₱0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="supplier-filter-alert" class="hidden px-4 py-2 text-xs font-semibold text-yellow-800 bg-yellow-50 border-t border-yellow-100">
                        
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <div class="flex-1">
                         <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Notes</label>
                         <input type="text" name="notes" class="w-full text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-chocolate" placeholder="Internal notes...">
                    </div>
                    <div class="flex items-end gap-3">

                        <button type="button" onclick="purchaseOrderManager.openConfirmationModal('create')" 
                                class="px-6 py-2.5 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all">
                            Create & Send PO
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Confirmation Modal --}}
            <div id="confirmation-modal" class="hidden fixed inset-0 z-[60] bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-border-soft p-6 transform transition-all animate-fade-in-down">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mx-auto mb-4 text-chocolate text-2xl border border-chocolate/20">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h3 class="font-display text-xl font-bold text-gray-900 mb-2">Confirm Purchase Order</h3>
                        <p class="text-sm text-gray-500">You are about to create a PO for <span id="modal-item-count" class="font-bold text-chocolate">0</span> items.</p>
                        <p class="text-sm text-gray-500 mt-1">Total Amount: <span id="modal-total-amount" class="font-bold text-chocolate">₱0.00</span></p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('confirmation-modal').classList.add('hidden')" 
                                class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" onclick="submitPurchaseOrderForm()" 
                                class="flex-1 px-4 py-2 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md">
                            Confirm & Process
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

</div>

{{-- PR DETAILS MODAL --}}
<div id="pr-details-modal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl border border-border-soft overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
            <h3 class="font-display text-lg font-bold text-chocolate">Request Details</h3>
            <button onclick="closePRDetailsModal()" class="text-gray-400 hover:text-chocolate transition-colors p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="pr-details-content" class="p-6 overflow-y-auto max-h-[60vh] custom-scrollbar bg-white"></div>
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end">
            <button onclick="closePRDetailsModal()" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-100 transition-colors text-sm shadow-sm">Close</button>
        </div>
    </div>
</div>

<script>
const GROUP_PR_ITEMS_URL = "{{ url('/purchasing/api/group-pr-items') }}";
const GET_PR_ITEMS_URL = "{{ url('/purchasing/api/get-pr-items') }}";
const SUPPLIER_ITEMS_BASE_URL = "{{ url('/purchasing/api/suppliers') }}";

class PurchaseOrderManager {
    constructor() {
        this.selectedPRs = [];
        this.items = []; // Store items currently rendered (legacy table)
        this.baseItems = []; // Store unfiltered items fetched from PRs
        this.supplierAlertEl = document.getElementById('supplier-filter-alert');
        this.bucketCardsContainer = document.getElementById('bucket-cards-container');
        this.bucketSection = document.getElementById('supplier-grouping-section');
        this.configurationSection = document.getElementById('supplier-section');
        this.unassignedContainer = document.getElementById('unassigned-items-container');
        this.unassignedList = document.getElementById('unassigned-items-list');
        this.bucketStats = {
            buckets: document.getElementById('bucket-count'),
            items: document.getElementById('bucket-item-count'),
            unassigned: document.getElementById('unassigned-count')
        };
        this.bucketData = [];
        this.currentBucket = null;
        this.lockedSupplierId = null;
        this.init();
    }

    init() {
        document.getElementById('select-all-prs')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updatePRSelectionUI.bind(this));
        });

        // Search Filters
        document.getElementById('pr-search-filter')?.addEventListener('input', this.filterPRs.bind(this));
        document.getElementById('pr-department-filter')?.addEventListener('change', this.filterPRs.bind(this));
        document.getElementById('pr-priority-filter')?.addEventListener('change', this.filterPRs.bind(this));
    }

    // --- STEP 1: PR Selection Logic ---
    
    handleSelectAll(e) {
       document.querySelectorAll('.pr-checkbox:not(:disabled)').forEach(checkbox => {
           checkbox.checked = e.target.checked;
       });
       this.updatePRSelectionUI();
    }

    updatePRSelectionUI() {
        const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
        const count = checkboxes.length;
        
        document.getElementById('selected-pr-count').textContent = count;
        document.getElementById('proceed-supplier-btn').disabled = count === 0;

        // Update Master Checkbox state
        const selectAll = document.getElementById('select-all-prs');
        const allCheckboxes = document.querySelectorAll('.pr-checkbox');
        if(selectAll && allCheckboxes.length > 0) {
            selectAll.checked = count === allCheckboxes.length;
            selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
        }
    }

    filterPRs() {
        const searchTerm = document.getElementById('pr-search-filter')?.value.toLowerCase() || '';
        const departmentFilter = document.getElementById('pr-department-filter')?.value.toLowerCase() || '';
        const priorityFilter = document.getElementById('pr-priority-filter')?.value.toLowerCase() || '';

        let visibleCount = 0;
        document.querySelectorAll('.pr-row').forEach(row => {
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
        
        // Debug logging to help track PR visibility
        console.log(`PR Filter: Showing ${visibleCount} of ${document.querySelectorAll('.pr-row').length} PRs`);
    }

    // --- STEP 2: Automatic Grouping + Configuration ---

    proceedToGrouping() {
        const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
        this.selectedPRs = Array.from(checkboxes).map(cb => cb.value);
        document.getElementById('selected-pr-ids').value = this.selectedPRs.join(',');

        if (this.selectedPRs.length === 0) {
            alert('Please select at least one purchase request.');
            return;
        }

        document.getElementById('pr-selection-section').classList.add('hidden');
        this.bucketSection.classList.remove('hidden');
        this.configurationSection.classList.add('hidden');

        this.loadSupplierBuckets();
    }

    resetToPRSelection() {
        document.getElementById('supplier-section').classList.add('hidden');
        document.getElementById('supplier-grouping-section').classList.add('hidden');
        document.getElementById('pr-selection-section').classList.remove('hidden');

        // Reset selections
        this.items = [];
        this.selectedPRs = [];
        this.baseItems = [];
        document.getElementById('po-items-table').innerHTML = '';
        
        // Clear PR checkboxes and reset UI
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        this.updatePRSelectionUI();
        
        // Refresh the page to get updated PR data (including new partial order statuses)
        // This ensures PRs with remaining items after partial PO creation are properly shown
        window.location.reload();
        this.unlockSupplierSelect();
    }

    returnToBucketReview() {
        this.configurationSection.classList.add('hidden');
        this.bucketSection.classList.remove('hidden');
        this.currentBucket = null;
        this.items = [];
        document.getElementById('po-items-table').innerHTML = '';
        this.updateSupplierAlert();
        this.unlockSupplierSelect();
    }

    loadSupplierBuckets() {
        this.bucketCardsContainer.innerHTML = '<div class="text-center py-12"><i class="fas fa-spinner fa-spin text-chocolate text-2xl"></i><div class="mt-2 text-gray-500">Analyzing supplier match...</div></div>';

        fetch(GROUP_PR_ITEMS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ pr_ids: this.selectedPRs })
        })
        .then(res => res.json())
        .then(data => {
            this.bucketData = data.buckets || [];
            this.renderBuckets();
            this.renderUnassigned(data.unassignedItems || []);
            this.bucketStats.buckets.textContent = this.bucketData.length;
            const totalItems = this.bucketData.reduce((sum, bucket) => sum + (bucket.totals?.item_count || 0), 0);
            this.bucketStats.items.textContent = totalItems;
        })
        .catch(error => {
            console.error(error);
            this.bucketCardsContainer.innerHTML = '<div class="text-center py-12 text-red-500">Failed to analyze suppliers. Please try again.</div>';
        });
    }

    renderBuckets() {
        if (this.bucketData.length === 0) {
            this.bucketCardsContainer.innerHTML = '<div class="text-center py-12 text-gray-400 italic">No supplier buckets were generated.</div>';
            return;
        }

        this.bucketCardsContainer.innerHTML = '';

        this.bucketData.forEach((bucket, index) => {
            const card = document.createElement('div');
            card.className = 'border border-border-soft rounded-xl shadow-sm overflow-hidden';
            card.dataset.bucketIndex = index;

            const itemsHtml = bucket.items.map((item, itemIndex) => {
                const sourceLabels = item.source_prs.map(pr => `<span class="inline-flex items-center text-[10px] font-mono text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded">#${pr.pr_number} (${parseFloat(pr.qty_remaining).toFixed(2)})</span>`).join(' ');
                const moveOptions = item.alternate_suppliers.map(option => {
                    const diff = option.price_difference;
                    const diffLabel = diff === 0 ? 'same price' : (diff > 0 ? `+₱${diff.toFixed(2)}` : `-₱${Math.abs(diff).toFixed(2)}`);
                    return `<button type="button" class="text-xs px-2 py-1 border rounded-lg hover:bg-gray-50" onclick="purchaseOrderManager.moveItemToSupplier(${index}, ${itemIndex}, ${option.supplier_id})">${option.supplier_name} (${diffLabel})</button>`;
                }).join(' ');

                return `
                    <div class="p-4 border-t border-gray-100">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-semibold text-gray-800">${item.item_name}</div>
                                <div class="text-xs text-gray-500 font-mono">${item.item_code}</div>
                                <div class="mt-2 space-x-1">${sourceLabels}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Qty Remaining</div>
                                <div class="font-bold text-green-600">${parseFloat(item.qty_remaining).toFixed(3)} ${item.unit_symbol || ''}</div>
                                <div class="text-xs text-gray-500 mt-2">Suggested Price</div>
                                <div class="font-bold text-chocolate">₱${parseFloat(item.suggested_price).toFixed(2)}</div>
                            </div>
                        </div>
                        ${item.alternate_suppliers.length ? `<div class="mt-3 text-xs text-gray-500">Move to: ${moveOptions}</div>` : ''}
                    </div>
                `;
            }).join('');

            const buttonDisabled = bucket.items.length === 0 ? 'disabled opacity-50 cursor-not-allowed' : '';

            card.innerHTML = `
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500">Supplier</div>
                        <div class="text-lg font-display font-bold text-chocolate">${bucket.supplier.name}</div>
                        <div class="text-xs text-gray-500">${bucket.supplier.supplier_code || 'No code'} ${bucket.supplier.payment_terms ? `• ${bucket.supplier.payment_terms}-day terms` : ''}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Estimated Total</div>
                        <div class="text-xl font-bold text-chocolate">₱${(bucket.totals.estimated_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                        <div class="text-xs text-gray-500">${bucket.totals.item_count || 0} item(s)</div>
                    </div>
                </div>
                ${itemsHtml || '<div class="p-4 text-center text-sm text-gray-400">No items assigned to this supplier.</div>'}
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3 border-t border-border-soft">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-white ${buttonDisabled}" ${buttonDisabled ? 'disabled' : ''} onclick="purchaseOrderManager.loadBucket(${index})">Configure & Create PO</button>
                </div>
            `;

            this.bucketCardsContainer.appendChild(card);
        });
    }

    renderUnassigned(items) {
        if (!items.length) {
            this.unassignedContainer.classList.add('hidden');
            this.unassignedList.innerHTML = '';
            this.bucketStats.unassigned.textContent = '0 item(s)';
            return;
        }

        this.unassignedContainer.classList.remove('hidden');
        this.bucketStats.unassigned.textContent = `${items.length} item(s)`;
        this.unassignedList.innerHTML = items.map(item => `
            <div class="p-3 bg-white border border-red-100 rounded-lg">
                <div class="font-semibold text-gray-800">${item.item_name}</div>
                <div class="text-xs text-gray-500 font-mono">${item.item_code}</div>
                <div class="text-xs text-gray-500 mt-1">Qty Remaining: <span class="font-bold text-red-600">${parseFloat(item.qty_remaining || item.qty_remaining || 0).toFixed(3)}</span></div>
            </div>
        `).join('');
    }

    moveItemToSupplier(bucketIndex, itemIndex, newSupplierId) {
        const bucket = this.bucketData[bucketIndex];
        if (!bucket) return;

        const item = bucket.items.splice(itemIndex, 1)[0];
        if (!item) return;

        const targetBucketIndex = this.bucketData.findIndex(b => b.supplier.id === newSupplierId);
        let targetBucket = this.bucketData[targetBucketIndex];

        if (!targetBucket) {
            const alternateSupplier = item.alternate_suppliers.find(a => a.supplier_id === newSupplierId);
            targetBucket = {
                supplier: {
                    id: newSupplierId,
                    name: alternateSupplier?.supplier_name || 'Supplier',
                    supplier_code: null,
                    payment_terms: null,
                },
                items: [],
                totals: {
                    estimated_amount: 0,
                    item_count: 0,
                },
            };
            this.bucketData.push(targetBucket);
        }

        targetBucket.items.push({
            ...item,
            current_supplier_id: newSupplierId,
            suggested_price: this.resolveAlternatePrice(item, newSupplierId) || item.suggested_price,
        });

        this.recalculateBucketTotals(bucket);
        this.recalculateBucketTotals(targetBucket);
        this.renderBuckets();
    }

    resolveAlternatePrice(item, supplierId) {
        const alternate = item.alternate_suppliers.find(a => a.supplier_id === supplierId);
        return alternate ? alternate.unit_price : item.suggested_price;
    }

    recalculateBucketTotals(bucket) {
        const total = bucket.items.reduce((sum, item) => {
            return sum + (item.qty_remaining * (item.suggested_price || 0));
        }, 0);
        bucket.totals = bucket.totals || {};
        bucket.totals.estimated_amount = total;
        bucket.totals.item_count = bucket.items.length;
    }

    loadBucket(index) {
        const bucket = this.bucketData[index];
        if (!bucket || bucket.items.length === 0) {
            alert('This supplier bucket has no items.');
            return;
        }

        this.currentBucket = bucket;
        this.baseItems = bucket.items.map(item => ({
            item_id: item.item_id,
            item_name: item.item_name,
            item_code: item.item_code,
            qty_requested: item.qty_remaining,
            qty_remaining: item.qty_remaining,
            pr_label: item.source_prs.map(pr => `#${pr.pr_number}`).join(', '),
            suggested_price: item.suggested_price,
        }));

        this.items = [...this.baseItems];
        this.renderItems();
        this.lockSupplierSelect(bucket.supplier.id);
        this.updateSupplierAlert('Supplier is fixed based on bucket selection. To change, go back to the bucket view.', 'info');
        this.bucketSection.classList.add('hidden');
        this.configurationSection.classList.remove('hidden');
    }

    renderItems() {
        const tbody = document.getElementById('po-items-table');
        tbody.innerHTML = '';

        if (this.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-gray-400 italic">No items are currently available for the selected requests. Try choosing different PRs or add new items before proceeding.</td></tr>';
            return;
        }

        this.items.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors';
            const prBadgeLabel = item.pr_label || (item.pr_number ? `#${item.pr_number}` : 'Multiple PRs');
            const prId = item.pr_id ?? (item.source_prs?.[0]?.pr_id ?? '');
            
            // Generate row HTML
            tr.innerHTML = `
                <td class="px-4 py-3 text-center">
                    <input type="checkbox" name="items[${index}][selected]" value="1" checked
                           class="item-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer"
                           onchange="purchaseOrderManager.toggleRowState(this)">
                    <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                    <input type="hidden" name="items[${index}][pr_id]" value="${prId}">
                </td>
                <td class="px-4 py-3">
                    <div class="font-bold text-gray-800 text-sm">${item.item_name}</div>
                    <div class="text-xs text-gray-500 font-mono">${item.item_code}</div>
                    <div class="text-[10px] text-blue-600 bg-blue-50 inline-block px-1.5 py-0.5 rounded mt-1 border border-blue-100">${prBadgeLabel}</div>
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">${item.qty_requested}</td>
                <td class="px-4 py-3 text-center text-sm font-bold text-green-600">${item.qty_remaining}</td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${index}][quantity]" value="${item.qty_remaining}" max="${item.qty_remaining}" min="0.1" step="0.01"
                           class="qty-input w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-chocolate focus:ring-1 focus:ring-chocolate disabled:bg-gray-100 disabled:text-gray-400 text-right font-medium transition-colors"
                           disabled onchange="purchaseOrderManager.calculateTotals()">
                </td>
                <td class="px-4 py-3">
                    <div class="relative">
                        <span class="absolute left-3 top-1.5 text-gray-500 text-xs font-bold">₱</span>
                        <input type="number" name="items[${index}][price]" value="${item.suggested_price || 0}" min="0" step="0.01"
                               class="price-input w-full pl-7 pr-3 py-1.5 text-sm border border-gray-300 rounded focus:border-chocolate focus:ring-1 focus:ring-chocolate disabled:bg-gray-100 disabled:text-gray-400 text-right transition-colors"
                               disabled onchange="purchaseOrderManager.calculateTotals()">
                    </div>
                </td>
                <td class="px-4 py-3 text-right text-sm font-bold text-gray-400 row-total">₱0.00</td>
            `;
            tbody.appendChild(tr);
        });
        
        // Auto-enable inputs and calculate totals for all items since they're checked by default
        setTimeout(() => {
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    this.toggleRowState(checkbox);
                }
            });
        }, 100);
    }

    toggleRowState(checkbox) {
        const row = checkbox.closest('tr');
        const inputs = row.querySelectorAll('input[type="number"]');
        const totalCell = row.querySelector('.row-total');
        
        if (checkbox.checked) {
            row.classList.add('bg-orange-50');
            inputs.forEach(input => input.disabled = false);
            totalCell.classList.remove('text-gray-400');
            totalCell.classList.add('text-chocolate');
        } else {
            row.classList.remove('bg-orange-50');
            inputs.forEach(input => input.disabled = true);
            totalCell.classList.add('text-gray-400');
            totalCell.classList.remove('text-chocolate');
        }
        this.calculateTotals();
        this.updateSelectedCounter();
    }

    toggleAllItems(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
            this.toggleRowState(cb);
        });
    }

    calculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#po-items-table tr').forEach(row => {
            const checkbox = row.querySelector('.item-checkbox');
            if (checkbox && checkbox.checked) {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const total = qty * price;
                
                row.querySelector('.row-total').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2});
                grandTotal += total;
            } else {
                if(row.querySelector('.row-total')) row.querySelector('.row-total').textContent = '₱0.00';
            }
        });
        document.getElementById('grand-total-display').textContent = '₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    }

    updateSelectedCounter() {
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        document.getElementById('selected-items-counter').textContent = count;
    }

    handleSupplierChange(supplierId) {
        const supplierSelect = document.getElementById('supplier_id');

        if (this.lockedSupplierId) {
            if (supplierId !== this.lockedSupplierId && supplierSelect) {
                supplierSelect.value = this.lockedSupplierId;
            }
            this.updateSupplierAlert('Supplier is fixed based on bucket selection. To change, go back to the bucket view.', 'info');
            return;
        }

        if (!supplierId) {
            this.items = [...this.baseItems];
            this.updateSupplierAlert();
            this.renderItems();
            return;
        }

        if (this.selectedPRs.length === 0) {
            alert('Please select at least one purchase request first.');
            if (supplierSelect) {
                supplierSelect.value = '';
            }
            return;
        }

        this.updateSupplierAlert('', 'info');
    }

    normalizeSupplierItems(items) {
        return items.map(item => {
            const ordered = parseFloat(item.quantity_ordered_so_far ?? 0);
            const remaining = parseFloat(item.remaining_quantity ?? 0);
            const totalRequested = ordered + remaining;
            const prLabel = Array.isArray(item.source_prs) && item.source_prs.length
                ? item.source_prs.join(', ')
                : 'Multiple PRs';

            return {
                item_id: item.item_id,
                item_name: item.item_name,
                item_code: item.item_code,
                qty_requested: parseFloat(totalRequested.toFixed(3)),
                qty_remaining: parseFloat(remaining.toFixed(3)),
                suggested_price: parseFloat(item.unit_price ?? 0),
                pr_label: prLabel
            };
        });
    }

    updateSupplierAlert(message = '', type = 'info') {
        if (!this.supplierAlertEl) {
            return;
        }

        if (!message) {
            this.supplierAlertEl.classList.add('hidden');
            this.supplierAlertEl.textContent = '';
            this.supplierAlertEl.classList.remove('text-red-800');
            this.supplierAlertEl.classList.add('text-yellow-800');
            return;
        }

        this.supplierAlertEl.textContent = message;
        this.supplierAlertEl.classList.remove('hidden');
        if (type === 'error') {
            this.supplierAlertEl.classList.add('text-red-800');
            this.supplierAlertEl.classList.remove('text-yellow-800');
        } else {
            this.supplierAlertEl.classList.remove('text-red-800');
            this.supplierAlertEl.classList.add('text-yellow-800');
        }
    }

    lockSupplierSelect(supplierId) {
        const supplierSelect = document.getElementById('supplier_id');
        if (!supplierSelect) {
            return;
        }

        this.lockedSupplierId = supplierId ? supplierId.toString() : null;
        supplierSelect.value = this.lockedSupplierId || '';
        if (this.lockedSupplierId) {
            supplierSelect.dataset.locked = 'true';
            supplierSelect.classList.add('cursor-not-allowed', 'bg-gray-100');
            supplierSelect.title = 'Supplier is locked to this bucket. Go back to change it.';
        }
    }

    unlockSupplierSelect() {
        const supplierSelect = document.getElementById('supplier_id');
        this.lockedSupplierId = null;
        if (!supplierSelect) {
            return;
        }

        supplierSelect.removeAttribute('data-locked');
        supplierSelect.classList.remove('cursor-not-allowed', 'bg-gray-100');
        supplierSelect.removeAttribute('title');
        supplierSelect.disabled = false;
        supplierSelect.value = '';
    }

    openConfirmationModal(type) {
        const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (selectedCount === 0) {
            alert("Please select at least one item to order.");
            return;
        }

        const grandTotal = document.getElementById('grand-total-display').textContent;
        document.getElementById('modal-item-count').textContent = selectedCount;
        document.getElementById('modal-total-amount').textContent = grandTotal;
        document.getElementById('final-save-option').value = type;
        
        document.getElementById('confirmation-modal').classList.remove('hidden');
    }
    
    /**
     * Enhanced method to return to PR selection with fresh data
     * This ensures that PRs with remaining items after partial PO creation are properly displayed
     */
    returnToPRSelectionWithRefresh() {
        // Show loading state
        const prSelectionSection = document.getElementById('pr-selection-section');
        const originalContent = prSelectionSection.innerHTML;
        prSelectionSection.innerHTML = `
            <div class="p-12 text-center">
                <i class="fas fa-spinner fa-spin text-chocolate text-2xl"></i>
                <div class="mt-2 text-gray-500">Refreshing purchase requests...</div>
            </div>
        `;
        
        // Reset all sections
        document.getElementById('supplier-section').classList.add('hidden');
        document.getElementById('supplier-grouping-section').classList.add('hidden');
        
        // Refresh the page to get updated PR data
        // This ensures that PRs with remaining items after partial PO creation are properly shown
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Simple Modal Logic for PR Details (Preserved from original)
class PRDetailsModal {
    constructor() {
        this.modal = document.getElementById('pr-details-modal');
        this.content = document.getElementById('pr-details-content');
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
        // Improve PR details display
        const items = prData.purchaseRequestItems || [];
        
        // Format items list with detailed information
        const itemsHtml = items.map(i => {
            // Handle both legacy and new data structures
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
        
        // Format PR header information
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
        
        // Construct the complete HTML
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

// Initializers
let purchaseOrderManager;
let prDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    purchaseOrderManager = new PurchaseOrderManager();
    prDetailsModal = new PRDetailsModal();
    
    // Check if we need to refresh the page due to recent PO creation
    checkForRefreshNeeded();
});

/**
 * Check if the page needs to be refreshed due to recent PO creation
 * This ensures that PRs with consumed items are properly hidden
 */
function checkForRefreshNeeded() {
    // Check for the refresh_needed flag in the session
    const urlParams = new URLSearchParams(window.location.search);
    const refreshNeeded = urlParams.get('refresh_needed') || sessionStorage.getItem('po_creation_refresh');
    
    if (refreshNeeded === 'true') {
        // Show a brief loading indicator
        const loadingHtml = `
            <div class="fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-2xl p-6 transform transition-all">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-chocolate text-2xl mb-4"></i>
                        <p class="text-gray-700 font-semibold">Refreshing Purchase Requests...</p>
                        <p class="text-sm text-gray-500 mt-1">Updating status to show current availability</p>
                    </div>
                </div>
            </div>
        `;
        
        // Add loading overlay
        document.body.insertAdjacentHTML('beforeend', loadingHtml);
        
        // Clear the refresh flag
        sessionStorage.removeItem('po_creation_refresh');
        urlParams.delete('refresh_needed');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
        
        // Refresh after a brief delay to show the loading state
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

// Global functions for inline onclicks
function viewPRDetails(id) { prDetailsModal.open(id); }
function closePRDetailsModal() { prDetailsModal.close(); }

// Form submission handler with debugging
function submitPurchaseOrderForm() {
    console.log('=== PO FORM SUBMISSION DEBUG ===');
    
    const form = document.getElementById('purchase-order-form');
    const selectedItems = document.querySelectorAll('.item-checkbox:checked');
    const selectedPRIds = document.getElementById('selected-pr-ids').value;
    const supplierId = document.getElementById('supplier_id').value;
    const saveOption = document.getElementById('final-save-option').value;
    
    console.log('Form element:', form);
    console.log('Selected items count:', selectedItems.length);
    console.log('Selected PR IDs:', selectedPRIds);
    console.log('Supplier ID:', supplierId);
    console.log('Save option:', saveOption);
    
    // Debug: Check each selected item's data
    selectedItems.forEach((checkbox, index) => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const itemId = row.querySelector('input[name*="[item_id]"]').value;
        
        console.log(`Item ${index + 1}:`, {
            itemId: itemId,
            quantity: qtyInput ? qtyInput.value : 'N/A',
            price: priceInput ? priceInput.value : 'N/A',
            selected: checkbox.checked
        });
    });
    
    // Validation checks
    if (!supplierId) {
        alert('Please select a supplier.');
        return false;
    }
    
    if (selectedItems.length === 0) {
        alert('Please select at least one item to order.');
        return false;
    }
    
    // Check if any selected items have invalid quantities or prices
    let hasInvalidItems = false;
    selectedItems.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        
        if (qtyInput && parseFloat(qtyInput.value) <= 0) {
            alert('Selected items must have valid quantities greater than 0.');
            hasInvalidItems = true;
        }
        
        if (priceInput && parseFloat(priceInput.value) <= 0) {
            alert('Selected items must have valid prices greater than 0.');
            hasInvalidItems = true;
        }
    });
    
    if (hasInvalidItems) {
        return false;
    }
    
    console.log('Form submission passed all validation checks');
    
    // Hide modal and submit form
    document.getElementById('confirmation-modal').classList.add('hidden');
    
    console.log('Submitting form...');
    form.submit();
    
    return true;
}

</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endsection