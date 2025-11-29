@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. WELCOME HEADER --}}
    <div class="bg-white border border-border-soft rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <!-- Decorative Background -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-chocolate/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
            <div>
                <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Purchase Request Approvals</h1>
                <p class="text-sm text-gray-500 max-w-md">Review and approve procurement plans from the Purchasing Officer.</p>
            </div>
            <div class="flex flex-col items-end gap-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                        {{ $pendingCount ?? 0 }} Pending
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ $approvedToday ?? 0 }} Approved Today
                    </span>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900">{{ now()->format('M d, Y') }}</p>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold">{{ now()->format('l') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. FILTERS & SEARCH --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-lg font-bold text-chocolate">Filter Requests</h3>
            <button onclick="clearFilters()" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider hover:underline decoration-caramel/30 underline-offset-2 transition-colors">
                Reset Filters
            </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Search -->
            <div class="lg:col-span-2 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" id="searchInput" 
                       class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                       placeholder="Search PR number, requester, department..." 
                       value="{{ request('search') }}">
            </div>

            <!-- Status Filter -->
            <div class="relative">
                <select id="statusFilter" class="block w-full py-2.5 px-4 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer shadow-sm">
                    <option value="pending" {{ (!request('status') || request('status') == 'pending') ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All History</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>

            <!-- Priority Filter -->
            <div class="relative">
                <select id="priorityFilter" class="block w-full py-2.5 px-4 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer shadow-sm">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between mt-6 pt-4 border-t border-border-soft gap-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" id="highValueFilter" {{ request('high_value') ? 'checked' : '' }} class="rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 transition-all cursor-pointer">
                    <span class="text-sm text-gray-600 font-medium group-hover:text-chocolate transition-colors">High Value (≥ ₱10,000)</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 transition-all cursor-pointer">
                    <span class="text-sm text-gray-600 font-medium group-hover:text-chocolate transition-colors">Select All Pending</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button" id="bulkApproveBtn" onclick="PurchaseRequestManager.bulkApproveSelected()" class="px-5 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg shadow-md transition-all text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-check-double"></i> Bulk Approve
                </button>

                <button onclick="refreshData()" class="w-full sm:w-auto px-5 py-2 bg-chocolate text-white hover:bg-chocolate-dark rounded-lg shadow-md transition-all text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 transform active:scale-95">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    {{-- 3. PURCHASE REQUESTS LIST --}}
    <div class="space-y-6" id="purchaseRequestsContainer">
        @if(isset($purchaseRequests) && $purchaseRequests->count() > 0)
            @foreach($purchaseRequests as $pr)
                @php
                    $isHighValue = $pr->total_estimated_cost >= 10000;
                    $isUrgent = $pr->priority === 'urgent';
                    $isOverdue = $pr->created_at->lt(now()->subDays(7));
                    $requesterName = $pr->requestedBy ? ($pr->requestedBy->name ?? 'Unknown User') : 'Unknown User';
                    
                    // Initials logic
                    $nameParts = explode(' ', $requesterName);
                    $initials = '';
                    foreach($nameParts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    $initials = substr($initials, 0, 2);
                @endphp

                {{-- PR CARD --}}
                <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-lg transition-all relative group {{ $isHighValue ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-transparent' }}">
                    
                    {{-- Status Badges --}}
                    <div class="absolute top-4 right-4 flex gap-2">
                        @if($pr->status === 'pending')
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" class="pr-checkbox rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 cursor-pointer"
                                       value="{{ $pr->id }}"
                                       data-pr-id="{{ $pr->id }}">
                                <span class="text-xs text-gray-600 group-hover:text-chocolate font-medium transition-colors">Select</span>
                            </label>
                        @endif
                        @if($isHighValue)
                            <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-bold rounded-full border border-red-100 uppercase tracking-wide">High Value</span>
                        @endif
                        @if($isUrgent)
                            <span class="px-2.5 py-1 bg-orange-50 text-orange-700 text-[10px] font-bold rounded-full border border-orange-100 uppercase tracking-wide">Urgent</span>
                        @endif
                        @if($isOverdue)
                            <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-[10px] font-bold rounded-full border border-purple-100 uppercase tracking-wide">Overdue</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <!-- Left: Request Info -->
                        <div class="lg:col-span-4 space-y-5 border-r border-border-soft/50 pr-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-chocolate text-white flex items-center justify-center font-bold text-sm shadow-md border-2 border-white ring-1 ring-chocolate/20">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $pr->pr_number }}</h3>
                                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mt-0.5">{{ $requesterName }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between border-b border-border-soft/50 pb-2">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Department</span>
                                    <span class="font-medium text-chocolate">{{ $pr->department ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-border-soft/50 pb-2">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Priority</span>
                                    @php
                                        $prioConfig = match($pr->priority) {
                                            'urgent' => 'text-red-600 bg-red-50 border-red-100',
                                            'high' => 'text-orange-600 bg-orange-50 border-orange-100',
                                            'low' => 'text-gray-600 bg-gray-50 border-gray-100',
                                            default => 'text-blue-600 bg-blue-50 border-blue-100'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border {{ $prioConfig }}">
                                        {{ ucfirst($pr->priority) }}
                                    </span>
                                </div>
                                <div class="flex justify-between border-b border-border-soft/50 pb-2">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Status</span>
                                    @php
                                        $statusConfig = match($pr->status) {
                                            'approved' => ['class' => 'text-green-600 bg-green-50 border-green-100', 'icon' => 'fa-check-circle'],
                                            'rejected' => ['class' => 'text-red-600 bg-red-50 border-red-100', 'icon' => 'fa-times-circle'],
                                            'converted' => ['class' => 'text-blue-600 bg-blue-50 border-blue-100', 'icon' => 'fa-share'],
                                            default => ['class' => 'text-gray-600 bg-gray-50 border-gray-100', 'icon' => 'fa-circle']
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 {{ $statusConfig['class'] }} px-2 py-0.5 rounded text-[10px] font-bold uppercase border">
                                        <i class="fas {{ $statusConfig['icon'] }}"></i> {{ ucfirst($pr->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between pt-1">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Created</span>
                                    <span class="font-medium text-gray-700">{{ $pr->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Middle: Items Summary -->
                        <div class="lg:col-span-5 flex flex-col justify-center">
                            <div class="bg-cream-bg rounded-xl p-5 border border-border-soft min-h-[200px]">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-bold text-chocolate flex items-center">
                                        <i class="fas fa-box-open mr-2"></i> Items Requested
                                        <span class="ml-2 text-xs font-normal text-gray-500">({{ $pr->purchaseRequestItems->count() }} items)</span>
                                    </h4>
                                    <span class="text-sm font-bold text-gray-900 bg-white px-2 py-1 rounded border border-border-soft">
                                        ₱ {{ number_format($pr->total_estimated_cost, 2) }}
                                    </span>
                                </div>
                                
                                <div class="space-y-4">
                                    @foreach($pr->purchaseRequestItems->take(3) as $item)
                                        <div class="flex justify-between items-start pb-3 border-b border-border-soft/50 last:border-0 last:pb-0 gap-3">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 leading-tight">{{ $item->item->name ?? 'Unknown Item' }}</p>
                                                <p class="text-xs text-gray-500 mt-1 leading-tight">
                                                    {{ number_format($item->quantity_requested, 1) }} {{ $item->item->unit->symbol ?? '' }} × ₱{{ number_format($item->unit_price_estimate, 2) }}
                                                </p>
                                            </div>
                                            <div class="text-right flex-shrink-0 ml-3">
                                                <p class="text-sm font-bold text-chocolate whitespace-nowrap">₱ {{ number_format($item->total_estimated_cost, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($pr->purchaseRequestItems->count() > 3)
                                        <div class="text-center pt-3">
                                            <button onclick="showAllItems({{ $pr->id }})" class="text-xs font-bold text-caramel hover:text-chocolate transition-colors hover:underline decoration-caramel/30 underline-offset-2">
                                                View {{ $pr->purchaseRequestItems->count() - 3 }} more items
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Contextual Notes --}}
                            @if($pr->status === 'rejected' && $pr->reject_reason)
                                <div class="mt-4 px-4 py-3 bg-red-50 rounded-lg border border-red-100 text-xs">
                                    <span class="font-bold text-red-800 uppercase tracking-wide">Rejection Reason:</span>
                                    <p class="text-red-600 mt-1">{{ $pr->reject_reason }}</p>
                                </div>
                            @elseif($pr->notes)
                                <div class="mt-4 px-4 py-3 bg-amber-50 rounded-lg border border-amber-100 text-xs">
                                    <span class="font-bold text-amber-800 uppercase tracking-wide">Note:</span>
                                    <p class="text-amber-700 mt-1 italic">"{{ $pr->notes }}"</p>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Actions -->
                        <div class="lg:col-span-3 flex flex-col justify-center space-y-3 border-l border-border-soft/50 pl-4">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center mb-2">Actions</p>
                            
                            @if($pr->status === 'pending')
                                <button onclick="approvePR({{ $pr->id }}, '{{ $pr->pr_number }}')" 
                                        class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all shadow-sm hover:shadow-md font-bold text-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Approve Request
                                </button>
                                <button onclick="viewPRDetails({{ $pr->id }})" 
                                        class="w-full py-2.5 bg-white border border-border-soft text-gray-700 rounded-lg hover:bg-cream-bg hover:border-caramel hover:text-chocolate transition-all shadow-sm font-bold text-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button onclick="rejectPR({{ $pr->id }}, '{{ $pr->pr_number }}')" 
                                        class="w-full py-2.5 bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition-all shadow-sm font-bold text-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i> Reject
                                </button>
                            @else
                                <div class="text-center py-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <p class="text-xs font-medium text-gray-500 mb-2">Action completed</p>
                                    <button onclick="viewPRDetails({{ $pr->id }})" class="text-xs font-bold text-chocolate hover:underline decoration-chocolate/30">
                                        View Details
                                    </button>
                                    @if($pr->approved_at)
                                        <p class="text-[10px] text-gray-400 mt-2">Approved: {{ $pr->approved_at->format('M d, H:i') }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white border border-border-soft rounded-xl p-16 text-center shadow-sm">
                <div class="w-20 h-20 bg-cream-bg rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <i class="fas fa-clipboard-list text-chocolate/30 text-4xl"></i>
                </div>
                <h3 class="font-display text-xl font-bold text-chocolate mb-1">No Requests Found</h3>
                <p class="text-sm text-gray-500">There are no purchase requests matching your current filters.</p>
                <button onclick="clearFilters()" class="mt-6 text-caramel font-bold hover:text-chocolate hover:underline transition-colors">Reset Filters</button>
            </div>
        @endif
    </div>

    {{-- 4. PAGINATION --}}
    @if(isset($purchaseRequests) && $purchaseRequests->hasPages())
        <div class="flex justify-center pt-6 border-t border-border-soft">
            {{ $purchaseRequests->links() }}
        </div>
    @endif

</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300">
    <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[85vh] overflow-hidden flex flex-col border border-border-soft transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center shrink-0">
            <h3 class="font-display text-lg font-bold text-chocolate">Purchase Request Details</h3>
            <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-chocolate transition-colors p-1 rounded-lg hover:bg-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="detailsContent" class="p-8 overflow-y-auto custom-scrollbar bg-white flex-1"></div>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center border border-border-soft transform transition-all scale-100">
        <div id="confirmIconContainer" class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-blue-100">
            <i id="confirmIcon" class="fas fa-question text-blue-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-chocolate mb-2" id="confirmTitle">Confirm Action</h3>
        <p class="text-gray-600 mb-6 text-sm leading-relaxed" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="flex gap-3">
            <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-colors text-sm">
                Cancel
            </button>
            <button id="confirmBtn" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-colors shadow-md text-sm">
                Confirm
            </button>
        </div>
    </div>
</div>

{{-- REJECT REASON MODAL --}}
<div id="rejectReasonModal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 border border-border-soft transform transition-all scale-100">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
                <i class="fas fa-times text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-display font-bold text-chocolate">Reject Purchase Request</h3>
            <p class="text-xs text-gray-500 mt-1" id="rejectReasonTitle">Please provide a reason</p>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Reason <span class="text-red-500">*</span></label>
                <select id="rejectReasonSelect" class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all">
                    <option value="">Select a reason...</option>
                    <option value="Insufficient Budget">Insufficient Budget</option>
                    <option value="Duplicate Request">Duplicate Request</option>
                    <option value="Items Not Available">Items Not Available</option>
                    <option value="Incorrect Specifications">Incorrect Specifications</option>
                    <option value="Policy Violation">Policy Violation</option>
                    <option value="Quality Concerns">Quality Concerns</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Comments</label>
                <textarea id="rejectComments" rows="3" class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all resize-none" placeholder="Additional details..."></textarea>
            </div>
        </div>
        
        <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
            <button onclick="closeRejectReasonModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-colors text-sm">
                Cancel
            </button>
            <button id="confirmRejectBtn" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition-colors shadow-md text-sm">
                Reject
            </button>
        </div>
    </div>
</div>

{{-- TOAST NOTIFICATION --}}
<div id="toast" class="fixed top-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-[-20px] opacity-0">
    <div class="bg-white/95 backdrop-blur-md border border-border-soft rounded-xl shadow-2xl p-4 flex items-center gap-4 min-w-[320px]">
        <div id="toastIconContainer" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
            <i id="toastIcon" class="fas fa-check"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-chocolate" id="toastTitle">Success</h4>
            <p class="text-xs text-gray-600 mt-0.5" id="toastMessage">Action completed successfully.</p>
        </div>
    </div>
</div>

{{-- Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endpush

@push('scripts')
<script>
// PurchaseRequestManager - Handles all purchase request approval interactions
window.PurchaseRequestManager = {
    // Store current purchase request data for modal
    currentPurchaseRequest: null,

    // API endpoints
    getDetailsUrl(prId) {
        return `/supervisor/purchase-requests/${prId}/details`;
    },

    getApproveUrl(prId) {
        return `/supervisor/purchase-requests/${prId}/approve`;
    },

    getRejectUrl(prId) {
        return `/supervisor/purchase-requests/${prId}/reject`;
    },

    // Initialize event listeners and DOM elements
    init() {
        // Setup modal event listeners
        this.setupModalListeners();

        // Setup filter functionality
        this.setupFilters();

        // Setup bulk selection functionality
        this.setupBulkSelection();

        // Setup CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    },

    // Open detailed purchase request modal
    viewPRDetails(prId) {
        // Show loading state
        this.showLoadingState();

        // Fetch purchase request details
        fetch(this.getDetailsUrl(prId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentPurchaseRequest = data.data;
                this.populateDetailsModal(data.data);
                this.showDetailsModal();
            } else {
                this.showToast('Error', data.error || 'Failed to load purchase request details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading purchase request details:', error);
            this.showToast('Error', 'Failed to load purchase request details', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },

    // Approve purchase request
    approvePR(prId, prNumber) {
        this.showConfirmModal(
            'Confirm Approval',
            `Are you sure you want to approve Purchase Request #${prNumber}?`,
            () => {
                this.showLoadingState();

                fetch(this.getApproveUrl(prId), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Success', 'Purchase request approved successfully', 'success');
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showToast('Error', data.message || 'Failed to approve purchase request', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error approving purchase request:', error);
                    this.showToast('Error', 'Failed to approve purchase request', 'error');
                })
                .finally(() => {
                    this.hideLoadingState();
                });
            },
            'fa-check-circle'
        );
    },

    // Reject purchase request
    rejectPR(prId, prNumber) {
        // Show rejection reason modal
        this.currentPurchaseRequestId = prId;
        document.getElementById('rejectReasonTitle').textContent = `Reject Purchase Request #${prNumber}`;
        document.getElementById('rejectReasonModal').classList.remove('hidden');
    },

    // Confirm rejection with reason
    confirmRejection() {
        const reason = document.getElementById('rejectReasonSelect').value;
        const comments = document.getElementById('rejectComments').value;

        if (!reason) {
            this.showToast('Error', 'Please select a rejection reason', 'error');
            return;
        }

        this.showLoadingState();

        fetch(this.getRejectUrl(this.currentPurchaseRequestId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                reason: reason,
                comments: comments
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Success', 'Purchase request rejected successfully', 'success');
                this.closeRejectReasonModal();

                // Refresh the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast('Error', data.message || 'Failed to reject purchase request', 'error');
            }
        })
        .catch(error => {
            console.error('Error rejecting purchase request:', error);
            this.showToast('Error', 'Failed to reject purchase request', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },

    // Show all items in a modal (for items beyond the first 3)
    showAllItems(prId) {
        // For now, just open the details modal which shows all items
        this.viewPRDetails(prId);
    },

    // Populate details modal with purchase request data
   // Inside window.PurchaseRequestManager object...

populateDetailsModal(purchaseRequest) {
    const content = document.getElementById('detailsContent');

    // 1. Status & Timeline Logic
    const steps = [
        { label: 'Created', active: true, completed: true },
        { label: 'Supervisor Review', active: purchaseRequest.status === 'pending', completed: ['approved', 'converted'].includes(purchaseRequest.status) },
        { label: 'PO Generation', active: purchaseRequest.status === 'approved', completed: purchaseRequest.status === 'converted' },
        { label: 'Completed', active: purchaseRequest.status === 'converted', completed: false }
    ];
    
    // Handle Rejected State specifically
    if (purchaseRequest.status === 'rejected') {
        steps[1] = { label: 'Rejected', active: false, completed: false, error: true };
    }

    // 2. Timeline HTML Generator
    const timelineHtml = `
        <div class="relative flex items-center justify-between w-full mb-8 px-4">
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
            ${steps.map((step, index) => {
                let circleClass = 'bg-white border-2 border-gray-300 text-gray-400';
                let textClass = 'text-gray-400';
                let icon = `<span class="text-xs font-bold">${index + 1}</span>`;

                if (step.error) {
                    circleClass = 'bg-red-500 border-red-500 text-white';
                    textClass = 'text-red-600 font-bold';
                    icon = '<i class="fas fa-times"></i>';
                } else if (step.completed) {
                    circleClass = 'bg-green-500 border-green-500 text-white';
                    textClass = 'text-green-600 font-bold';
                    icon = '<i class="fas fa-check"></i>';
                } else if (step.active) {
                    circleClass = 'bg-chocolate border-chocolate text-white ring-4 ring-chocolate/20';
                    textClass = 'text-chocolate font-bold';
                }

                return `
                    <div class="flex flex-col items-center bg-white px-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all ${circleClass}">
                            ${icon}
                        </div>
                        <span class="text-[10px] uppercase tracking-wider mt-2 ${textClass}">${step.label}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;

    // 3. Format Currency
    const formatCurrency = (amount) => {
        return '₱ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    // 4. Generate Items Table Rows
    let itemsRows = '';
    if (purchaseRequest.items && purchaseRequest.items.length > 0) {
        itemsRows = purchaseRequest.items.map((item, index) => `
            <tr class="hover:bg-cream-bg/50 transition-colors border-b border-gray-100 last:border-0">
                <td class="py-3 px-4 text-sm text-gray-500">${index + 1}</td>
                <td class="py-3 px-4">
                    <p class="text-sm font-bold text-gray-800">${item.item_name || 'Unknown Item'}</p>
                    <p class="text-xs text-gray-400 font-mono">${item.item_code || ''}</p>
                </td>
                <td class="py-3 px-4 text-center">
                    <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs font-bold text-gray-600">
                        ${item.quantity_requested} ${item.unit_symbol || 'units'}
                    </span>
                </td>
                <td class="py-3 px-4 text-right text-sm text-gray-600">
                    ${formatCurrency(item.unit_price_estimate || 0)}
                </td>
                <td class="py-3 px-4 text-right">
                    <span class="text-sm font-bold text-chocolate">
                        ${formatCurrency(item.total_estimated_cost || 0)}
                    </span>
                </td>
            </tr>
        `).join('');
    } else {
        itemsRows = '<tr><td colspan="5" class="py-8 text-center text-gray-500 italic">No items found in this request.</td></tr>';
    }

    // 5. Build the Full Modal Content
    content.innerHTML = `
        <div class="flex flex-col h-full">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-2xl font-display font-bold text-gray-900">PR #${purchaseRequest.pr_number}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border ${
                            purchaseRequest.status === 'approved' ? 'bg-green-100 text-green-700 border-green-200' : 
                            purchaseRequest.status === 'rejected' ? 'bg-red-100 text-red-700 border-red-200' : 
                            'bg-amber-100 text-amber-700 border-amber-200'
                        }">${purchaseRequest.status.toUpperCase()}</span>
                    </div>
                    <p class="text-sm text-gray-500">Created on ${new Date(purchaseRequest.created_at).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-100">
                ${timelineHtml}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center text-chocolate">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Requester</p>
                        <p class="font-bold text-gray-900">${purchaseRequest.requested_by || 'Unknown'}</p>
                        <p class="text-xs text-chocolate">${purchaseRequest.department || 'No Dept'}</p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center text-chocolate">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Priority</p>
                        <p class="font-bold text-gray-900 capitalize">${purchaseRequest.priority || 'Normal'}</p>
                        <span class="text-[10px] text-gray-500">Target: Within 7 days</span>
                    </div>
                </div>

                <div class="bg-chocolate text-white p-4 rounded-xl shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-white/80 uppercase tracking-wide">Total Estimated</p>
                        <p class="text-xl font-bold font-mono">${formatCurrency(purchaseRequest.total_estimated_cost)}</p>
                    </div>
                    <div class="text-3xl opacity-20">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>

            ${purchaseRequest.notes ? `
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-sticky-note text-amber-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-800 italic">"${purchaseRequest.notes}"</p>
                            <p class="text-xs text-amber-600 font-bold mt-1">— User Note</p>
                        </div>
                    </div>
                </div>
            ` : ''}
            
            ${purchaseRequest.reject_reason ? `
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800">Rejection Reason</h3>
                            <p class="text-sm text-red-700 mt-1">${purchaseRequest.reject_reason}</p>
                        </div>
                    </div>
                </div>
            ` : ''}

            <div class="flex-1">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-shopping-basket text-caramel mr-2"></i> Request Items
                </h3>
                <div class="bg-white border border-border-soft rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-12">#</th>
                                <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Item Details</th>
                                <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Qty</th>
                                <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Unit Est.</th>
                                <th class="py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            ${itemsRows}
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="4" class="py-4 px-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wide">Grand Total Estimate</td>
                                <td class="py-4 px-4 text-right text-lg font-bold text-chocolate">
                                    ${formatCurrency(purchaseRequest.total_estimated_cost)}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            ${purchaseRequest.status === 'pending' ? `
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3 no-print">
                    <button onclick="closeDetailsModal()" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button onclick="rejectPR(${purchaseRequest.id}, '${purchaseRequest.pr_number}')" class="px-5 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-lg font-bold text-sm hover:bg-red-100 transition-colors flex items-center gap-2">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <button onclick="approvePR(${purchaseRequest.id}, '${purchaseRequest.pr_number}')" class="px-5 py-2.5 bg-green-600 text-white rounded-lg font-bold text-sm hover:bg-green-700 shadow-md transition-colors flex items-center gap-2">
                        <i class="fas fa-check"></i> Approve Request
                    </button>
                </div>
            ` : `
                 <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3 no-print">
                    <button onclick="closeDetailsModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold text-sm hover:bg-gray-200 transition-colors">
                        Close
                    </button>
                 </div>
            `}
        </div>
    `;
},

    // Show/hide modal functions
    showDetailsModal() {
        document.getElementById('detailsModalBackdrop').classList.remove('hidden');
    },

    closeDetailsModal() {
        document.getElementById('detailsModalBackdrop').classList.add('hidden');
        this.currentPurchaseRequest = null;
    },

    closeRejectReasonModal() {
        document.getElementById('rejectReasonModal').classList.add('hidden');
        document.getElementById('rejectReasonSelect').value = '';
        document.getElementById('rejectComments').value = '';
        this.currentPurchaseRequestId = null;
    },

    // Show confirmation modal
    showConfirmModal(title, message, onConfirm, icon = 'fa-question') {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmIcon').className = `fas ${icon}`;
        document.getElementById('confirmIconContainer').className = 'w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-blue-100';

        // Store the callback
        document.getElementById('confirmBtn').onclick = () => {
            this.closeConfirmModal();
            onConfirm();
        };

        document.getElementById('confirmModal').classList.remove('hidden');
    },

    // Close confirmation modal
    closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    },

    // Loading state management
    showLoadingState() {
        const loadingElements = document.querySelectorAll('.loading-state');
        loadingElements.forEach(el => {
            el.classList.remove('hidden');
            el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        });
    },

    hideLoadingState() {
        const loadingElements = document.querySelectorAll('.loading-state');
        loadingElements.forEach(el => {
            el.classList.add('hidden');
        });
    },

    // Toast notification system
    showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        const iconContainer = document.getElementById('toastIconContainer');
        const titleEl = document.getElementById('toastTitle');
        const messageEl = document.getElementById('toastMessage');

        // Configure icon based on type
        if (type === 'success') {
            icon.className = 'fas fa-check-circle text-green-600';
            iconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-green-100';
        } else {
            icon.className = 'fas fa-exclamation-circle text-red-600';
            iconContainer.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-100';
        }

        titleEl.textContent = title;
        messageEl.textContent = message;

        // Show toast
        toast.classList.remove('hidden');
        toast.classList.remove('translate-y-[-20px]', 'opacity-0');

        // Auto hide after 4 seconds
        setTimeout(() => {
            toast.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 4000);
    },

    // Setup modal event listeners
    setupModalListeners() {
        // Details modal close on backdrop click
        document.getElementById('detailsModalBackdrop').addEventListener('click', (e) => {
            if (e.target.id === 'detailsModalBackdrop') {
                this.closeDetailsModal();
            }
        });

        // Rejection modal confirm button
        document.getElementById('confirmRejectBtn').addEventListener('click', () => {
            this.confirmRejection();
        });

        // Escape key to close modals
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDetailsModal();
                this.closeRejectReasonModal();
            }
        });
    },

    // Filter functionality
    setupFilters() {
        // Status filter change - submit form immediately
        document.getElementById('statusFilter').addEventListener('change', function() {
            // For now, just reload the page. In a full implementation, this would use AJAX
            this.form.submit();
        });

        // Search input with debounce - submit form after delay
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 800);
        });

        // Priority filter - submit form immediately
        document.getElementById('priorityFilter').addEventListener('change', function() {
            this.form.submit();
        });

        // High value filter - submit form immediately
        document.getElementById('highValueFilter').addEventListener('change', function() {
            this.form.submit();
        });
    },

    // Setup bulk selection functionality
    setupBulkSelection() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');

        // Select All checkbox event listener
        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            // Only select checkboxes for pending PRs
            const pendingCheckboxes = document.querySelectorAll('.pr-checkbox');

            pendingCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            this.updateBulkApproveButton();
        });

        // Individual checkbox event listeners (using event delegation)
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('pr-checkbox')) {
                this.updateSelectAllCheckbox();
                this.updateBulkApproveButton();
            }
        });

        // Initial state
        this.updateBulkApproveButton();
    },

    // Update Select All checkbox state
    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const pendingCheckboxes = document.querySelectorAll('.pr-checkbox');
        const checkedBoxes = document.querySelectorAll('.pr-checkbox:checked');

        if (pendingCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }

        if (checkedBoxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length === pendingCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    },

    // Update Bulk Approve button state
    updateBulkApproveButton() {
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        const checkedBoxes = document.querySelectorAll('.pr-checkbox:checked');

        bulkApproveBtn.disabled = checkedBoxes.length === 0;
    },

    // Get selected PR IDs
    getSelectedPrIds() {
        const checkedBoxes = document.querySelectorAll('.pr-checkbox:checked');
        return Array.from(checkedBoxes).map(checkbox => parseInt(checkbox.value));
    },

    // Bulk approve selected PRs
    bulkApproveSelected() {
        const selectedIds = this.getSelectedPrIds();

        if (selectedIds.length === 0) {
            this.showToast('Warning', 'Please select at least one purchase request to approve', 'error');
            return;
        }

        this.showConfirmModal(
            'Confirm Bulk Approval',
            `Are you sure you want to approve ${selectedIds.length} purchase request${selectedIds.length > 1 ? 's' : ''}?`,
            () => this.performBulkApprove(selectedIds),
            'fa-check-double'
        );
    },

    // Perform bulk approve API call
    performBulkApprove(prIds) {
        this.showLoadingState();

        fetch('/supervisor/purchase-requests/bulk-approve', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                requisition_ids: prIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Success', data.message || 'Purchase requests approved successfully', 'success');

                // Clear selections
                document.querySelectorAll('.pr-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('selectAllCheckbox').checked = false;
                this.updateBulkApproveButton();

                // Refresh the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast('Error', data.error || 'Failed to bulk approve purchase requests', 'error');
            }
        })
        .catch(error => {
            console.error('Error bulk approving purchase requests:', error);
            this.showToast('Error', 'Failed to bulk approve purchase requests', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    PurchaseRequestManager.init();
});

// Global functions for onclick handlers
function closeDetailsModal() {
    PurchaseRequestManager.closeDetailsModal();
}

function closeConfirmModal() {
    PurchaseRequestManager.closeConfirmModal();
}

function closeRejectReasonModal() {
    PurchaseRequestManager.closeRejectReasonModal();
}

// Clear all filters and reset the form
function clearFilters() {
    // Reset form fields
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'pending';
    document.getElementById('priorityFilter').value = '';
    document.getElementById('highValueFilter').checked = false;

    // Submit form to reload with default filters
    document.getElementById('searchInput').form.submit();
}

// Refresh data using AJAX to maintain current filters
function refreshData() {
    // For now, just reload the page
    location.reload();
}

// Global functions for onclick handlers (mapped to PurchaseRequestManager methods)
function viewPRDetails(id) {
    PurchaseRequestManager.viewPRDetails(id);
}

function approvePR(id, prNumber) {
    PurchaseRequestManager.approvePR(id, prNumber);
}

function rejectPR(id, prNumber) {
    PurchaseRequestManager.rejectPR(id, prNumber);
}

function showAllItems(id) {
    PurchaseRequestManager.showAllItems(id);
}
</script>
@endpush