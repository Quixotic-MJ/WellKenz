@extends('Purchasing.layout.app')

@section('title', 'Unified Purchase Order Creation')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 font-sans text-gray-600 pb-24">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Create Purchase Orders</h1>
            <p class="text-sm text-gray-500">Select approved requests and create purchase orders individually or in bulk.</p>
        </div>
        <div class="flex items-center gap-3">
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

    {{-- 3. MODE SELECTOR --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
            <h3 class="font-display text-lg font-bold text-chocolate">Choose Creation Mode</h3>
            <p class="text-xs text-gray-500 mt-0.5">Select how you want to create purchase orders from the selected requests.</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mode-option cursor-pointer border-2 border-gray-200 rounded-xl p-6 hover:border-chocolate transition-all" data-mode="single">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-chocolate/10 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-file-invoice-dollar text-chocolate text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-chocolate text-lg">Single PO Creation</h4>
                            <p class="text-sm text-gray-500">Detailed configuration for one supplier</p>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Item-by-item quantity and price configuration</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Manual supplier selection</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Detailed order specifications</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Flexible item adjustment</li>
                    </ul>
                </div>
                
                <div class="mode-option cursor-pointer border-2 border-gray-200 rounded-xl p-6 hover:border-chocolate transition-all" data-mode="bulk">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-chocolate/10 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-layer-group text-chocolate text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-chocolate text-lg">Bulk PO Creation</h4>
                            <p class="text-sm text-gray-500">Automatic grouping for multiple suppliers</p>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Automatic supplier assignment</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Multiple PO creation in one process</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Quick delivery date and terms configuration</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i> Efficient bulk processing</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 1: PURCHASE REQUEST SELECTION --}}
    <div id="pr-selection-section" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        
        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">1. Select Purchase Requests</h3>
                <p class="text-xs text-gray-500 mt-0.5">Choose approved purchase requests to convert into purchase orders.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="selected-pr-count" class="font-bold text-chocolate text-sm">0</span> selected
                </div>
                <button type="button" 
                        id="proceed-analysis-btn"
                        disabled
                        onclick="purchaseOrderManager.proceedToAnalysis()"
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
                                    $orderedItems = 0;
                                    
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

    {{-- STEP 2: SUPPLIER GROUPING ANALYSIS --}}
    <div id="supplier-grouping-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        
        {{-- Header Bar --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">2. Supplier Analysis & Grouping</h3>
                <p class="text-xs text-gray-500 mt-0.5">Review automatic supplier assignments and prepare for configuration.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bucket-count" class="font-bold text-chocolate text-sm">0</span> suppliers
                </div>
                <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                    <span id="bucket-item-count" class="font-bold text-chocolate text-sm">0</span> items
                </div>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="px-6 py-3 border-b border-border-soft bg-gray-50 flex justify-between items-center">
            <button type="button" onclick="purchaseOrderManager.returnToPRSelection()" 
                    class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Selection
            </button>
            
            <div id="mode-specific-actions" class="flex gap-3">
                {{-- These buttons will be shown/hidden based on selected mode --}}
                <button type="button" id="single-mode-btn" onclick="purchaseOrderManager.enterSingleMode()" 
                        class="hidden px-6 py-2 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all flex items-center">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Select Supplier for Single PO
                </button>
                <button type="button" id="bulk-mode-btn" onclick="purchaseOrderManager.enterBulkMode()" 
                        class="hidden px-6 py-2 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all flex items-center">
                    <i class="fas fa-layer-group mr-2"></i> Configure Bulk POs
                </button>
            </div>
        </div>

        {{-- Bucket Cards --}}
        <div id="bucket-cards-container" class="p-6 space-y-6">
            <div class="text-center py-12 text-gray-400 italic">Run the analysis to view supplier groupings.</div>
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

    {{-- STEP 3: SINGLE PO CONFIGURATION --}}
    <div id="single-configuration-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">
        <form action="{{ route('purchasing.po.store') }}" method="POST" id="single-po-form">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="single-selected-pr-ids">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-chocolate">3. Single PO Configuration</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Configure items, quantities, and pricing for a single purchase order.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="purchaseOrderManager.returnToBucketReview()" 
                            class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <i class="fas fa-layer-group mr-2"></i> Back to Buckets
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-8">
                
                {{-- Form Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="single_supplier_id" class="block text-sm font-bold text-chocolate mb-2">Supplier *</label>
                        <div class="relative">
                            <select name="supplier_id" id="single_supplier_id" required onchange="purchaseOrderManager.handleSupplierChange(this.value)"
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
                        <label for="single_expected_delivery_date" class="block text-sm font-bold text-chocolate mb-2">Delivery Date *</label>
                        <input type="date" name="expected_delivery_date" required 
                               value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+7 days'))) }}"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>

                    <div>
                        <label for="single_payment_terms" class="block text-sm font-bold text-chocolate mb-2">Payment Terms</label>
                        <input type="number" name="payment_terms" value="30"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>
                </div>

                {{-- Items Table (Shopping Cart) --}}
                <div class="border border-border-soft rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-border-soft flex justify-between items-center">
                        <h4 class="font-bold text-gray-800 text-sm">Select Items to Order</h4>
                        <div class="text-xs text-gray-500">
                            <span id="single-selected-items-counter" class="font-bold text-chocolate">0</span> items selected
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
                            <tbody id="single-po-items-table" class="bg-white divide-y divide-gray-50">
                                {{-- JS will populate this --}}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-bold text-gray-600">Grand Total:</td>
                                    <td class="px-4 py-3 text-right text-sm font-bold text-chocolate" id="single-grand-total-display">₱0.00</td>
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
        </form>
    </div>

    {{-- STEP 3: BULK PO CONFIGURATION --}}
    <div id="bulk-configuration-section" class="hidden bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden transition-all duration-300">

        <form action="{{ route('purchasing.po.bulk-create') }}" method="POST" id="bulk-po-form">
            @csrf
            <input type="hidden" name="selected_pr_ids" id="bulk-selected-pr-ids">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="font-display text-lg font-bold text-chocolate">3. Bulk PO Configuration</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Configure delivery dates and payment terms for each supplier bucket.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                        <span id="bulk-config-count" class="font-bold text-chocolate text-sm">0</span> suppliers
                    </div>
                    <div class="text-xs font-medium text-gray-500 bg-white border border-border-soft px-3 py-1.5 rounded-lg shadow-sm">
                        <span id="bulk-total-pos" class="font-bold text-chocolate text-sm">0</span> POs
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="px-6 py-3 border-b border-border-soft bg-gray-50 flex justify-between items-center">
                <button type="button" onclick="purchaseOrderManager.returnToBucketReview()" 
                        class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Buckets
                </button>
            </div>

            {{-- Configuration Cards --}}
            <div id="bulk-config-cards-container" class="p-6 space-y-6">
                <div class="text-center py-12 text-gray-400 italic">Run the analysis to view supplier configurations.</div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-between items-center">
                <div></div>
                <button type="button" onclick="purchaseOrderManager.openBulkConfirmationModal()"
                        class="px-6 py-3 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Create All Purchase Orders
                </button>
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

{{-- SINGLE PO CONFIRMATION MODAL --}}
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

{{-- GENERAL ALERT MODAL --}}
<div id="alert-modal" class="hidden fixed inset-0 z-[70] bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-border-soft p-6 transform transition-all animate-fade-in-down">
        <div class="text-center mb-6">
            <div id="alert-modal-icon" class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500 text-2xl border border-red-200">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 id="alert-modal-title" class="font-display text-xl font-bold text-gray-900 mb-2">Alert</h3>
            <p id="alert-modal-message" class="text-sm text-gray-500">You have an important message.</p>
        </div>
        <div class="flex justify-center">
            <button type="button" onclick="closeAlertModal()" 
                    class="px-6 py-2 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition-all">
                OK
            </button>
        </div>
    </div>
</div>

<script>
const GROUP_PR_ITEMS_URL = "{{ url('/purchasing/api/group-pr-items') }}";
const GET_PR_ITEMS_URL = "{{ url('/purchasing/api/get-pr-items') }}";
const SUPPLIER_ITEMS_BASE_URL = "{{ url('/purchasing/api/suppliers') }}";

class UnifiedPurchaseOrderManager {
    constructor() {
        this.selectedPRs = [];
        this.currentMode = null; // 'single' or 'bulk'
        this.bucketData = [];
        this.currentBucket = null;
        this.lockedSupplierId = null;
        
        // Single mode properties
        this.items = [];
        this.baseItems = [];
        this.supplierAlertEl = document.getElementById('supplier-filter-alert');
        
        // Bulk mode properties
        this.bulkConfigData = [];
        
        this.bucketCardsContainer = document.getElementById('bucket-cards-container');
        this.bucketSection = document.getElementById('supplier-grouping-section');
        this.singleSection = document.getElementById('single-configuration-section');
        this.bulkSection = document.getElementById('bulk-configuration-section');
        this.unassignedContainer = document.getElementById('unassigned-items-container');
        this.unassignedList = document.getElementById('unassigned-items-list');
        
        this.bucketStats = {
            buckets: document.getElementById('bucket-count'),
            items: document.getElementById('bucket-item-count'),
            unassigned: document.getElementById('unassigned-count')
        };
        
        this.init();
    }

    init() {
        // Mode selector
        document.querySelectorAll('.mode-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const mode = option.dataset.mode;
                this.selectMode(mode);
            });
        });

        // PR Selection handlers
        document.getElementById('select-all-prs')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updatePRSelectionUI.bind(this));
        });

        // Search Filters
        document.getElementById('pr-search-filter')?.addEventListener('input', this.filterPRs.bind(this));
        document.getElementById('pr-department-filter')?.addEventListener('change', this.filterPRs.bind(this));
        document.getElementById('pr-priority-filter')?.addEventListener('change', this.filterPRs.bind(this));
    }

    selectMode(mode) {
        // Remove active state from all options
        document.querySelectorAll('.mode-option').forEach(option => {
            option.classList.remove('border-chocolate', 'bg-chocolate/5');
            option.classList.add('border-gray-200');
        });

        // Add active state to selected option
        const selectedOption = document.querySelector(`[data-mode="${mode}"]`);
        selectedOption.classList.remove('border-gray-200');
        selectedOption.classList.add('border-chocolate', 'bg-chocolate/5');

        this.currentMode = mode;
        console.log(`Mode selected: ${mode}`);
    }

    // --- PR Selection Logic ---
    
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
        document.getElementById('proceed-analysis-btn').disabled = count === 0;

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
        
        console.log(`PR Filter: Showing ${visibleCount} of ${document.querySelectorAll('.pr-row').length} PRs`);
    }

    // --- Analysis and Grouping ---

    proceedToAnalysis() {
        if (!this.currentMode) {
            showAlertModal('Mode Selection Required', 'Please select a creation mode first.', 'warning');
            return;
        }

        const checkboxes = document.querySelectorAll('.pr-checkbox:checked');
        this.selectedPRs = Array.from(checkboxes).map(cb => cb.value);

        if (this.selectedPRs.length === 0) {
            showAlertModal('Selection Required', 'Please select at least one purchase request.', 'warning');
            return;
        }

        document.getElementById('pr-selection-section').classList.add('hidden');
        this.bucketSection.classList.remove('hidden');
        this.singleSection.classList.add('hidden');
        this.bulkSection.classList.add('hidden');

        this.loadSupplierBuckets();
    }

    returnToPRSelection() {
        this.bucketSection.classList.add('hidden');
        this.singleSection.classList.add('hidden');
        this.bulkSection.classList.add('hidden');
        document.getElementById('pr-selection-section').classList.remove('hidden');

        // Reset all data
        this.resetAll();
    }

    returnToBucketReview() {
        this.singleSection.classList.add('hidden');
        this.bulkSection.classList.add('hidden');
        this.bucketSection.classList.remove('hidden');

        // Remove single mode instruction
        const instruction = document.getElementById('single-mode-instruction');
        if (instruction) {
            instruction.remove();
        }

        // Re-render buckets without selection functionality
        this.renderBuckets();

        // Reset mode-specific data
        this.resetModeData();
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

            // Show appropriate mode button
            this.showModeButtons();
        })
        .catch(error => {
            console.error(error);
            this.bucketCardsContainer.innerHTML = '<div class="text-center py-12 text-red-500">Failed to analyze suppliers. Please try again.</div>';
        });
    }

    showModeButtons() {
        // Hide all mode buttons first
        document.getElementById('single-mode-btn').classList.add('hidden');
        document.getElementById('bulk-mode-btn').classList.add('hidden');

        // Show button for selected mode
        if (this.currentMode === 'single') {
            document.getElementById('single-mode-btn').classList.remove('hidden');
        } else if (this.currentMode === 'bulk') {
            document.getElementById('bulk-mode-btn').classList.remove('hidden');
        }
    }

    enterSingleMode() {
        this.bucketSection.classList.remove('hidden');
        this.singleSection.classList.add('hidden');
        this.bulkSection.classList.add('hidden');
        
        // Re-render buckets with selection functionality
        this.renderBuckets();
        
        // Show instruction for single mode
        this.showSingleModeInstructions();
    }

    selectSingleBucket(bucketIndex) {
        const bucket = this.bucketData[bucketIndex];
        if (!bucket || bucket.items.length === 0) {
            showAlertModal('No Items Available', 'This supplier bucket has no items. Try selecting a different supplier.', 'info');
            return;
        }

        this.bucketSection.classList.add('hidden');
        this.singleSection.classList.remove('hidden');
        this.bulkSection.classList.add('hidden');
        
        // Load the selected bucket for single mode configuration
        this.loadSingleBucket(bucketIndex);
    }

    showSingleModeInstructions() {
        // Add instruction header if not already present
        const existingInstruction = document.getElementById('single-mode-instruction');
        if (existingInstruction) {
            existingInstruction.remove();
        }

        const instructionDiv = document.createElement('div');
        instructionDiv.id = 'single-mode-instruction';
        instructionDiv.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6';
        instructionDiv.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-blue-800 text-sm">Single PO Configuration Mode</h4>
                    <p class="text-xs text-blue-700 mt-0.5">Click on a supplier bucket below to configure a purchase order for that specific supplier.</p>
                </div>
            </div>
        `;

        // Insert instruction at the top of bucket cards container
        const container = document.getElementById('bucket-cards-container');
        container.insertBefore(instructionDiv, container.firstChild);
    }

    enterBulkMode() {
        this.bucketSection.classList.add('hidden');
        this.singleSection.classList.add('hidden');
        this.bulkSection.classList.remove('hidden');
        
        if (this.bulkConfigData.length === 0) {
            this.bulkConfigData = [...this.bucketData];
            this.renderBulkConfigCards();
            this.updateBulkStats();
        }
    }

    renderBuckets() {
        if (this.bucketData.length === 0) {
            this.bucketCardsContainer.innerHTML = '<div class="text-center py-12 text-gray-400 italic">No supplier buckets were generated.</div>';
            return;
        }

        this.bucketCardsContainer.innerHTML = '';

        this.bucketData.forEach((bucket, index) => {
            const card = document.createElement('div');
            card.className = 'border border-border-soft rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all cursor-pointer';
            card.dataset.bucketIndex = index;
            
            // Add selection functionality for single mode
            if (this.currentMode === 'single') {
                card.classList.add('hover:border-chocolate', 'hover:bg-chocolate/5');
                card.addEventListener('click', () => this.selectSingleBucket(index));
            }

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

            const selectionIndicator = this.currentMode === 'single' ? 
                '<div class="text-xs text-chocolate font-bold"><i class="fas fa-hand-pointer mr-1"></i>Click to Select</div>' : '';
                
            card.innerHTML = `
                <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500">Supplier</div>
                        <div class="text-lg font-display font-bold text-chocolate">${bucket.supplier.name}</div>
                        <div class="text-xs text-gray-500">${bucket.supplier.supplier_code || 'No code'} ${bucket.supplier.payment_terms ? `• ${bucket.supplier.payment_terms}-day terms` : ''}</div>
                        ${selectionIndicator}
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Estimated Total</div>
                        <div class="text-xl font-bold text-chocolate">₱${(bucket.totals.estimated_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                        <div class="text-xs text-gray-500">${bucket.totals.item_count || 0} item(s)</div>
                    </div>
                </div>
                ${itemsHtml || '<div class="p-4 text-center text-sm text-gray-400">No items assigned to this supplier.</div>'}
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

    // --- SINGLE MODE FUNCTIONALITY ---

    loadSingleBucket(index) {
        const bucket = this.bucketData[index];
        if (!bucket || bucket.items.length === 0) {
            showAlertModal('No Items Available', 'This supplier bucket has no items.', 'info');
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
        
        // Set form values
        document.getElementById('single-selected-pr-ids').value = this.selectedPRs.join(',');
        document.getElementById('single_supplier_id').value = bucket.supplier.id;
        // Don't disable the supplier field - just prevent user from changing it
        document.getElementById('single_supplier_id').setAttribute('readonly', 'readonly');
        document.getElementById('single_supplier_id').style.backgroundColor = '#f9f9f9';
    }

    renderItems() {
        const tbody = document.getElementById('single-po-items-table');
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
            inputs.forEach(input => {
                input.disabled = false;
                input.classList.remove('disabled:bg-gray-100', 'disabled:text-gray-400');
            });
            totalCell.classList.remove('text-gray-400');
            totalCell.classList.add('text-chocolate');
        } else {
            row.classList.remove('bg-orange-50');
            inputs.forEach(input => {
                input.disabled = true;
                input.classList.add('disabled:bg-gray-100', 'disabled:text-gray-400');
                // Don't clear values when unchecking, just disable them
            });
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
        document.querySelectorAll('#single-po-items-table tr').forEach(row => {
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
        document.getElementById('single-grand-total-display').textContent = '₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    }

    updateSelectedCounter() {
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        document.getElementById('single-selected-items-counter').textContent = count;
    }

    handleSupplierChange(supplierId) {
        // For single mode, supplier change logic would go here
        // This could reload items based on supplier selection
    }

    // --- BULK MODE FUNCTIONALITY ---

    renderBulkConfigCards() {
        if (this.bulkConfigData.length === 0) {
            document.getElementById('bulk-config-cards-container').innerHTML = '<div class="text-center py-12 text-gray-400 italic">No supplier buckets were generated.</div>';
            return;
        }

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
        document.getElementById('bulk-selected-pr-ids').value = this.selectedPRs.join(',');
    }

    openConfirmationModal(type) {
        const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (selectedCount === 0) {
            showAlertModal('Items Required', 'Please select at least one item to order.', 'warning');
            return;
        }

        const grandTotal = document.getElementById('single-grand-total-display').textContent;
        document.getElementById('modal-item-count').textContent = selectedCount;
        document.getElementById('modal-total-amount').textContent = grandTotal;
        
        document.getElementById('confirmation-modal').classList.remove('hidden');
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

    // --- UTILITY METHODS ---

    resetAll() {
        this.selectedPRs = [];
        this.currentMode = null;
        this.bucketData = [];
        this.currentBucket = null;
        this.lockedSupplierId = null;
        this.items = [];
        this.baseItems = [];
        this.bulkConfigData = [];

        // Reset checkboxes
        document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        this.updatePRSelectionUI();

        // Reset mode selector
        document.querySelectorAll('.mode-option').forEach(option => {
            option.classList.remove('border-chocolate', 'bg-chocolate/5');
            option.classList.add('border-gray-200');
        });
    }

    resetModeData() {
        if (this.currentMode === 'single') {
            this.items = [];
            this.baseItems = [];
            document.getElementById('single-po-items-table').innerHTML = '';
            document.getElementById('single_supplier_id').removeAttribute('readonly');
            document.getElementById('single_supplier_id').style.backgroundColor = '';
            document.getElementById('single_supplier_id').value = '';
            
            // Remove single mode instruction
            const instruction = document.getElementById('single-mode-instruction');
            if (instruction) {
                instruction.remove();
            }
        } else if (this.currentMode === 'bulk') {
            this.bulkConfigData = [];
            document.getElementById('bulk-config-cards-container').innerHTML = '';
        }
    }
}

// Simple Modal Logic for PR Details
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

// Global functions for inline onclicks
function viewPRDetails(id) { prDetailsModal.open(id); }
function closePRDetailsModal() { prDetailsModal.close(); }

function submitPurchaseOrderForm() {
    const form = document.getElementById('single-po-form');
    const selectedItems = document.querySelectorAll('.item-checkbox:checked');
    const selectedPRIds = document.getElementById('single-selected-pr-ids').value;
    const supplierId = document.getElementById('single_supplier_id').value;
    
    console.log('Submitting Single PO Form', {
        supplierId,
        selectedItemsCount: selectedItems.length,
        selectedPRIds,
        timestamp: new Date().toISOString()
    });
    
    if (!supplierId) {
        showAlertModal('Supplier Required', 'Please select a supplier.', 'warning');
        return false;
    }
    
    if (selectedItems.length === 0) {
        showAlertModal('Items Required', 'Please select at least one item to order.', 'warning');
        return false;
    }
    
    // Validate that selected items have valid quantity and price
    let hasInvalidItems = false;
    selectedItems.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        
        const quantity = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        
        if (quantity <= 0) {
            showAlertModal('Invalid Quantity', 'All selected items must have a quantity greater than 0.', 'warning');
            hasInvalidItems = true;
            return false;
        }
        
        if (price <= 0) {
            showAlertModal('Invalid Price', 'All selected items must have a price greater than 0.', 'warning');
            hasInvalidItems = true;
            return false;
        }
    });
    
    if (hasInvalidItems) {
        return false;
    }
    
    document.getElementById('confirmation-modal').classList.add('hidden');
    form.submit();
    return true;
}

function submitBulkPOForm() {
    const form = document.getElementById('bulk-po-form');
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
        showAlertModal('Delivery Dates Required', 'Please fill in all required delivery dates.', 'warning');
        return false;
    }

    document.getElementById('bulk-confirmation-modal').classList.add('hidden');
    form.submit();
    return true;
}

// --- ALERT MODAL FUNCTIONS ---

function showAlertModal(title, message, type = 'warning') {
    const modal = document.getElementById('alert-modal');
    const titleEl = document.getElementById('alert-modal-title');
    const messageEl = document.getElementById('alert-modal-message');
    const iconEl = document.getElementById('alert-modal-icon');
    
    // Set title and message
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    // Set icon and colors based on type
    iconEl.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl border';
    
    switch(type) {
        case 'error':
            iconEl.classList.add('bg-red-50', 'text-red-500', 'border-red-200');
            iconEl.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
            break;
        case 'warning':
            iconEl.classList.add('bg-yellow-50', 'text-yellow-500', 'border-yellow-200');
            iconEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        case 'success':
            iconEl.classList.add('bg-green-50', 'text-green-500', 'border-green-200');
            iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
            break;
        case 'info':
        default:
            iconEl.classList.add('bg-blue-50', 'text-blue-500', 'border-blue-200');
            iconEl.innerHTML = '<i class="fas fa-info-circle"></i>';
            break;
    }
    
    modal.classList.remove('hidden');
}

function closeAlertModal() {
    document.getElementById('alert-modal').classList.add('hidden');
}

// Initialize
let purchaseOrderManager;
let prDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    purchaseOrderManager = new UnifiedPurchaseOrderManager();
    prDetailsModal = new PRDetailsModal();
});

</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }

.mode-option.active {
    border-color: #c48d3f;
    background-color: rgba(196, 141, 63, 0.05);
}
</style>
@endsection