@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. WELCOME HEADER --}}
    <div class="bg-white border border-border-soft rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-chocolate/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
            <div>
                <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Requisition Approvals</h1>
                <p class="text-sm text-gray-500 max-w-md">Review and process stock requests from the production team to ensure smooth operations.</p>
            </div>
            <div class="flex flex-col items-end gap-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span>
                        {{ $statistics['pending'] ?? 0 }} Pending
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ $statistics['approved_today'] ?? 0 }} Approved Today
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                        <i class="fas fa-calendar-week mr-2"></i>
                        {{ $statistics['total_approved_this_week'] ?? 0 }} This Week
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                        <i class="fas fa-times-circle mr-2"></i>
                        {{ $statistics['total_rejected_this_week'] ?? 0 }} Rejected
                    </span>
                    @if(($statistics['high_stock_usage'] ?? 0) > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ $statistics['high_stock_usage'] ?? 0 }} High Usage
                        </span>
                    @endif
                    @if(($statistics['critical_items'] ?? 0) > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200 animate-pulse">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ $statistics['critical_items'] ?? 0 }} Critical
                        </span>
                    @endif
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
            <h3 class="font-display text-lg font-bold text-chocolate">Filter Requisitions</h3>
            <button onclick="clearFilters()" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider hover:underline decoration-caramel/30 underline-offset-2 transition-colors">
                Reset Filters
            </button>
        </div>
        
        <form id="filterForm" method="GET" action="{{ route('supervisor.requisitions.index') }}">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    </div>
                    <input type="text" id="searchInput" name="search"
                           class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                           placeholder="Search by requester or item..." 
                           value="{{ request('search') }}">
                </div>

                <div class="relative">
                    <select id="statusFilter" name="status" class="block w-full py-2.5 px-4 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer shadow-sm">
                        <option value="pending" {{ (!request('status') || request('status') == 'pending') ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All History</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between mt-6 pt-4 border-t border-border-soft gap-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" id="highStockFilter" name="high_stock" value="1"
                               class="rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 transition-all cursor-pointer"
                               {{ request('high_stock') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600 font-medium group-hover:text-chocolate transition-colors">Show High Stock Usage Only (>80%)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" id="selectAllCheckbox"
                               class="rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 transition-all cursor-pointer">
                        <span class="text-sm text-gray-600 font-medium group-hover:text-chocolate transition-colors">Select All Pending</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="bulkApproveBtn" onclick="RequisitionManager.bulkApproveSelected()"
                            class="px-5 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg shadow-md transition-all text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        <i class="fas fa-check-double"></i> Bulk Approve
                    </button>

                    <button type="button" onclick="refreshData()" class="w-full sm:w-auto px-5 py-2 bg-chocolate text-white hover:bg-chocolate-dark rounded-lg shadow-md transition-all text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 transform active:scale-95">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. REQUISITIONS LIST --}}
    <div class="space-y-6" id="requisitionsContainer">
        @if(isset($requisitions) && $requisitions->count() > 0)
            @foreach($requisitions as $requisition)
                @php
                    $mainItem = $requisition->requisitionItems->first();
                    $stockRecord = $mainItem ? $mainItem->currentStockRecord : null;
                    $currentStock = $stockRecord ? $stockRecord->current_quantity : 0;
                    $requestedQty = $mainItem ? $mainItem->quantity_requested : 0;
                    $stockPercentage = $currentStock > 0 ? round(($requestedQty / $currentStock) * 100, 1) : 0;
                    $hasSufficientStock = $currentStock >= $requestedQty && $currentStock > 0;
                    $hasHighRequest = $stockPercentage > 80;
                    
                    // Initials logic preserved
                    $nameParts = explode(' ', $requisition->requestedBy->name ?? 'Unknown User');
                    $initials = '';
                    foreach($nameParts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    $initials = substr($initials, 0, 2);
                @endphp

                {{-- REQUISITION CARD --}}
                <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-lg transition-all relative group {{ $hasHighRequest && !$hasSufficientStock ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-transparent' }}">
                    
                    {{-- Status Badges --}}
                    <div class="absolute top-4 right-4 flex gap-2">
                        @if($requisition->status === 'pending')
                            <label class="flex items-center gap-2 {{ !$hasSufficientStock ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer group' }}">
                                <input type="checkbox" class="requisition-checkbox rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 {{ !$hasSufficientStock ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                                       value="{{ $requisition->id }}"
                                       data-requisition-id="{{ $requisition->id }}"
                                       {{ !$hasSufficientStock ? 'disabled' : '' }}>
                                <span class="text-xs {{ !$hasSufficientStock ? 'text-gray-400' : 'text-gray-600 group-hover:text-chocolate' }} font-medium transition-colors">Select</span>
                            </label>
                        @endif
                        @if($hasHighRequest && !$hasSufficientStock)
                            <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-bold rounded-full border border-red-100 uppercase tracking-wide">High Usage</span>
                        @endif
                        @if(!$hasSufficientStock && $currentStock > 0)
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-full border border-amber-100 uppercase tracking-wide">Low Stock</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <div class="lg:col-span-4 space-y-5 border-r border-border-soft/50 pr-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-chocolate text-white flex items-center justify-center font-bold text-sm shadow-md border-2 border-white ring-1 ring-chocolate/20">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight">#{{ $requisition->requisition_number }}</h3>
                                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mt-0.5">{{ $requisition->requestedBy->name ?? 'Unknown User' }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between border-b border-border-soft/50 pb-2">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Department</span>
                                    <span class="font-medium text-chocolate">{{ $requisition->department ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-border-soft/50 pb-2">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Status</span>
                                    @php
                                        $statusConfig = match($requisition->status) {
                                            'approved' => ['class' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'fa-check-circle'],
                                            'rejected' => ['class' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'fa-times-circle'],
                                            'pending' => ['class' => 'bg-amber-100 text-amber-800 border-amber-200', 'icon' => 'fa-clock'],
                                            default => ['class' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'fa-circle']
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $statusConfig['class'] }}">
                                        <i class="fas {{ $statusConfig['icon'] }} mr-1.5"></i>{{ ucfirst($requisition->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between pt-1">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Created</span>
                                    <span class="font-medium text-gray-700">{{ $requisition->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-5 flex flex-col justify-center">
                            <div class="bg-cream-bg rounded-xl p-5 border border-border-soft">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-bold text-chocolate flex items-center">
                                        <i class="fas fa-box-open mr-2"></i> Items Requested
                                        <span class="ml-2 text-xs font-normal text-gray-500">({{ $requisition->requisitionItems->count() }} items)</span>
                                    </h4>
                                    
                                    @if($mainItem && $currentStock > 0)
                                        <div class="text-right">
                                            <span class="text-xs font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-green-600' }} flex items-center justify-end gap-1">
                                                @if($hasSufficientStock)
                                                    <i class="fas fa-check-circle"></i> Sufficient Stock
                                                @else
                                                    <i class="fas fa-exclamation-triangle"></i> Insufficient
                                                @endif
                                            </span>
                                            <div class="w-24 h-1.5 bg-gray-200 rounded-full mt-1 ml-auto overflow-hidden">
                                                <div class="h-full {{ $hasHighRequest ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ min(100, $stockPercentage) }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="space-y-3">
                                    @if($mainItem)
                                        <div class="flex justify-between items-center pb-3 border-b border-border-soft/50 last:border-0 last:pb-0">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 truncate max-w-[180px]" title="{{ $mainItem->item->name ?? '' }}">
                                                    {{ $mainItem->item->name ?? 'Unknown Item' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    Req: <span class="font-bold text-chocolate">{{ number_format($requestedQty, 1) }} {{ $mainItem->item->unit->symbol ?? '' }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wide">Available</p>
                                                <p class="text-sm font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-gray-800' }}">
                                                    {{ number_format($currentStock, 1) }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($requisition->requisitionItems->count() > 1)
                                        <div class="text-center pt-2">
                                            <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" class="text-xs font-bold text-caramel hover:text-chocolate transition-colors hover:underline decoration-caramel/30 underline-offset-2">
                                                View {{ $requisition->requisitionItems->count() - 1 }} more items
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Contextual Notes/Reasons --}}
                            @if($requisition->status === 'rejected' && $requisition->reject_reason)
                                <div class="mt-4 px-4 py-3 bg-red-50 rounded-lg border border-red-100 text-xs">
                                    <span class="font-bold text-red-800 uppercase tracking-wide">Rejection Reason:</span>
                                    <p class="text-red-600 mt-1">{{ $requisition->reject_reason }}</p>
                                </div>
                            @elseif($requisition->notes)
                                <div class="mt-4 px-4 py-3 bg-amber-50 rounded-lg border border-amber-100 text-xs">
                                    <span class="font-bold text-amber-800 uppercase tracking-wide">Note:</span>
                                    <p class="text-amber-700 mt-1 italic">"{{ $requisition->notes }}"</p>
                                </div>
                            @elseif($requisition->purpose)
                                <div class="mt-4 px-4 py-3 bg-blue-50 rounded-lg border border-blue-100 text-xs">
                                    <span class="font-bold text-blue-800 uppercase tracking-wide">Purpose:</span>
                                    <p class="text-blue-700 mt-1 italic">"{{ $requisition->purpose }}"</p>
                                </div>
                            @endif
                        </div>

                        <div class="lg:col-span-3 flex flex-col justify-center space-y-3 border-l border-border-soft/50 pl-4">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center mb-2">Actions</p>
                            
                            @if($requisition->status === 'pending')
                                <button onclick="RequisitionManager.approve({{ $requisition->id }})" 
                                        class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all shadow-sm hover:shadow-md font-bold text-xs flex items-center justify-center gap-2 {{ !$hasSufficientStock ? 'opacity-60 cursor-not-allowed' : '' }}"
                                        {{ !$hasSufficientStock ? 'disabled' : '' }}>
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button onclick="RequisitionManager.openModifyModal({{ $requisition->id }})" 
                                        class="w-full py-2.5 bg-white border border-border-soft text-gray-700 rounded-lg hover:bg-cream-bg hover:border-caramel hover:text-chocolate transition-all shadow-sm font-bold text-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-edit"></i> Modify
                                </button>
                                <button onclick="RequisitionManager.reject({{ $requisition->id }})" 
                                        class="w-full py-2.5 bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition-all shadow-sm font-bold text-xs flex items-center justify-center gap-2">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @else
                                <div class="text-center py-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <p class="text-xs font-medium text-gray-500 mb-2">Action completed</p>
                                    <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" class="text-xs font-bold text-chocolate hover:underline decoration-chocolate/30">
                                        View Details
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white border border-border-soft rounded-xl p-16 text-center shadow-sm">
                <div class="w-20 h-20 bg-cream-bg rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <i class="fas fa-inbox text-chocolate/30 text-4xl"></i>
                </div>
                <h3 class="font-display text-xl font-bold text-chocolate mb-1">No Pending Requisitions</h3>
                <p class="text-sm text-gray-500">You have processed all incoming requests. Great job!</p>
            </div>
        @endif
    </div>

    {{-- 4. PAGINATION --}}
    @if(isset($requisitions) && $requisitions->hasPages())
        <div class="flex justify-center pt-6 border-t border-border-soft">
            {{ $requisitions->links() }}
        </div>
    @endif

</div>

{{-- INCLUDE MODALS (Unchanged logic, new styles applied via CSS classes in JS templates in previous turn) --}}
{{-- NOTE: The modal HTML structure is dynamically generated by the JS provided in the previous turn. --}}
{{-- Ensure the JS from the previous turn is included in this file's @push('scripts') section. --}}

{{-- DETAILS MODAL CONTAINER --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300">
    <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[85vh] overflow-hidden flex flex-col border border-border-soft transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center shrink-0">
            <h3 class="font-display text-lg font-bold text-chocolate">Requisition Details</h3>
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

{{-- MODIFY REQUISITION MODAL --}}
<div id="modifyModal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col border border-border-soft transform transition-all scale-100">
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center shrink-0">
            <h3 class="font-display text-lg font-bold text-chocolate">Modify Requisition</h3>
            <button onclick="RequisitionManager.closeModifyModal()" class="text-gray-400 hover:text-chocolate transition-colors p-1 rounded-lg hover:bg-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modifyContent" class="p-6 overflow-y-auto custom-scrollbar bg-white flex-1">
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-caramel/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-edit text-caramel text-2xl"></i>
                </div>
                <h4 class="text-lg font-bold text-chocolate mb-2">Loading Modification Interface</h4>
                <p class="text-gray-500">Please wait while we prepare the modification form...</p>
            </div>
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
            <h3 class="text-xl font-display font-bold text-chocolate">Reject Requisition</h3>
            <p class="text-xs text-gray-500 mt-1" id="rejectReasonTitle">Please provide a reason</p>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Reason <span class="text-red-500">*</span></label>
                <select id="rejectReasonSelect" class="block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all">
                    <option value="">Select a reason...</option>
                    <option value="Insufficient Stock">Insufficient Stock</option>
                    <option value="Invalid Request">Invalid Request</option>
                    <option value="Duplicate Request">Duplicate Request</option>
                    <option value="Policy Violation">Policy Violation</option>
                    <option value="Quality Issues">Quality Issues</option>
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
// RequisitionManager - Handles all requisition approval interactions
window.RequisitionManager = {
    // Store current requisition data for modal
    currentRequisition: null,
    
    // API endpoints
    getDetailsUrl(requisitionId) {
        return `/supervisor/requisitions/${requisitionId}/details`;
    },
    
    getApproveUrl(requisitionId) {
        return `/supervisor/requisitions/${requisitionId}/approve`;
    },
    
    getRejectUrl(requisitionId) {
        return `/supervisor/requisitions/${requisitionId}/reject`;
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

    // API endpoints for modification
    getModifyUrl(requisitionId) {
        return `/supervisor/requisitions/${requisitionId}/modify`;
    },

    getModifyMultiUrl(requisitionId) {
        return `/supervisor/requisitions/${requisitionId}/modify-multi`;
    },

    // Open modification modal
    openModifyModal(requisitionId) {
        // Show loading state
        this.showLoadingState();
        
        // Fetch requisition details for modification
        fetch(this.getDetailsUrl(requisitionId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentRequisition = data.data;
                this.populateModifyModal(data.data);
                this.showModifyModal();
            } else {
                this.showToast('Error', data.error || 'Failed to load requisition for modification', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading requisition for modification:', error);
            this.showToast('Error', 'Failed to load requisition for modification', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },

    // Populate modification modal with requisition data
    populateModifyModal(requisition) {
        const content = document.getElementById('modifyContent');
        
        // Generate items HTML with editable quantities
        let itemsHtml = '';
        if (requisition.items && requisition.items.length > 0) {
            itemsHtml = requisition.items.map((item, index) => {
                const currentStock = item.current_stock || 0;
                const requestedQty = item.quantity_requested;
                const stockPercentage = currentStock > 0 ? Math.round((requestedQty / currentStock) * 100) : 0;
                
                return `
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-4" data-item-id="${item.item_id}">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900">${item.item_name}</h4>
                                        <p class="text-sm text-gray-500">${item.item_code || ''}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <i class="fas fa-boxes mr-1"></i>
                                            Stock: <span class="font-bold ${currentStock < requestedQty ? 'text-red-600' : 'text-green-600'}">${currentStock} ${item.unit_symbol || ''}</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold ${stockPercentage > 80 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                            ${stockPercentage}% usage
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Current Requested</label>
                                        <div class="text-lg font-bold text-chocolate">${requestedQty} ${item.unit_symbol || ''}</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Available Stock</label>
                                        <div class="text-lg font-bold ${currentStock < requestedQty ? 'text-red-600' : 'text-green-600'}">${currentStock} ${item.unit_symbol || ''}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="lg:w-64">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">New Quantity <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           class="quantity-input flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel" 
                                           value="${requestedQty}" 
                                           min="0.001" 
                                           max="${currentStock}" 
                                           step="0.001"
                                           data-original="${requestedQty}"
                                           data-max="${currentStock}"
                                           data-unit="${item.unit_symbol || ''}">
                                    <span class="text-sm font-medium text-gray-500 min-w-[40px]">${item.unit_symbol || ''}</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    Max: ${currentStock} ${item.unit_symbol || ''}
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Difference:</span>
                                <span class="quantity-difference font-bold ${requestedQty <= currentStock ? 'text-green-600' : 'text-red-600'}">0 ${item.unit_symbol || ''}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            itemsHtml = '<p class="text-gray-500 text-center py-4">No items found</p>';
        }
        
        content.innerHTML = `
            <form id="modifyForm" onsubmit="RequisitionManager.submitModification(event)">
                <div class="space-y-6">
                    <div class="bg-cream-bg p-4 rounded-lg border border-border-soft">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-bold text-caramel uppercase tracking-wide">Requisition ID</p>
                                <p class="text-lg font-mono font-bold text-chocolate mt-1">#${requisition.requisition_number}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-caramel uppercase tracking-wide">Requested By</p>
                                <p class="text-lg font-bold text-chocolate mt-1">${requisition.requested_by || 'Unknown'}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-caramel uppercase tracking-wide">Department</p>
                                <p class="text-lg font-bold text-chocolate mt-1">${requisition.department || 'General'}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-bold text-chocolate mb-4 flex items-center">
                            <i class="fas fa-edit mr-2"></i> Modify Item Quantities
                        </h4>
                        <div id="modifyItemsContainer">
                            ${itemsHtml}
                        </div>
                    </div>
                    
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h5 class="font-bold text-amber-800 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i> Modification Guidelines
                        </h5>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li>• Quantities cannot exceed available stock levels</li>
                            <li>• Minimum quantity is 0.001 units</li>
                            <li>• All changes will be logged for audit purposes</li>
                            <li>• Provide a reason for the modification</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reason for Modification <span class="text-red-500">*</span></label>
                        <textarea id="modifyReason" name="reason" rows="3" 
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all resize-none" 
                                  placeholder="Please explain why you are modifying these quantities..."
                                  required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Required for audit trail and approval tracking</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-border-soft">
                        <button type="button" onclick="RequisitionManager.closeModifyModal()" 
                                class="flex-1 px-6 py-3 border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-6 py-3 bg-chocolate text-white rounded-lg font-bold hover:bg-chocolate-dark transition-colors shadow-md">
                            <i class="fas fa-save mr-2"></i> Save Modifications
                        </button>
                    </div>
                </div>
            </form>
        `;
        
        // Setup quantity input listeners for real-time validation
        this.setupQuantityListeners();
    },

    // Setup quantity input listeners for real-time validation
    setupQuantityListeners() {
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.validateQuantity(e.target);
                this.updateQuantityDifference(e.target);
            });
            
            input.addEventListener('blur', (e) => {
                this.validateQuantity(e.target);
                this.updateQuantityDifference(e.target);
            });
        });
    },

    // Validate individual quantity input
    validateQuantity(input) {
        const value = parseFloat(input.value) || 0;
        const max = parseFloat(input.dataset.max) || 0;
        const original = parseFloat(input.dataset.original) || 0;
        
        // Remove previous validation classes
        input.classList.remove('border-red-500', 'border-green-500', 'border-amber-500');
        
        if (value <= 0) {
            input.classList.add('border-red-500');
            return false;
        } else if (value > max) {
            input.classList.add('border-red-500');
            return false;
        } else if (value > original && value <= max) {
            input.classList.add('border-amber-500'); // Increasing quantity but within limits
            return true;
        } else if (value <= original && value > 0) {
            input.classList.add('border-green-500'); // Decreasing quantity
            return true;
        } else {
            input.classList.add('border-green-500'); // Same quantity
            return true;
        }
    },

    // Update quantity difference display
    updateQuantityDifference(input) {
        const value = parseFloat(input.value) || 0;
        const original = parseFloat(input.dataset.original) || 0;
        const unit = input.dataset.unit || '';
        const difference = value - original;
        const container = input.closest('[data-item-id]');
        const differenceElement = container.querySelector('.quantity-difference');
        
        if (differenceElement) {
            const sign = difference > 0 ? '+' : '';
            differenceElement.textContent = `${sign}${difference.toFixed(3)} ${unit}`;
            
            // Update color based on difference
            differenceElement.className = `quantity-difference font-bold ${difference > 0 ? 'text-amber-600' : difference < 0 ? 'text-green-600' : 'text-gray-600'}`;
        }
    },

    // Show/hide modification modal
    showModifyModal() {
        document.getElementById('modifyModal').classList.remove('hidden');
    },

    closeModifyModal() {
        document.getElementById('modifyModal').classList.add('hidden');
        this.currentRequisition = null;
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

    // Submit modification form
    submitModification(event) {
        event.preventDefault();
        
        if (!this.currentRequisition) {
            this.showToast('Error', 'No requisition data available', 'error');
            return;
        }
        
        // Collect modified items
        const modifiedItems = [];
        const quantityInputs = document.querySelectorAll('.quantity-input');
        
        console.log('Processing quantity inputs:', quantityInputs.length);
        
        quantityInputs.forEach(input => {
            const newQuantity = parseFloat(input.value);
            const originalQuantity = parseFloat(input.dataset.original);
            const itemContainer = input.closest('[data-item-id]');
            
            if (!itemContainer) {
                console.error('Could not find item container for input:', input);
                return;
            }
            
            const itemId = parseInt(itemContainer.dataset.itemId);
            
            console.log('Item data:', {
                itemId: itemId,
                newQuantity: newQuantity,
                originalQuantity: originalQuantity,
                hasChanged: newQuantity !== originalQuantity,
                isValid: newQuantity > 0
            });
            
            if (newQuantity !== originalQuantity && newQuantity > 0) {
                modifiedItems.push({
                    item_id: itemId,
                    new_quantity: newQuantity
                });
            }
        });
        
        console.log('Modified items collected:', modifiedItems);
        
        if (modifiedItems.length === 0) {
            this.showToast('Warning', 'No quantities were modified', 'error');
            return;
        }
        
        const reason = document.getElementById('modifyReason').value.trim();
        if (!reason) {
            this.showToast('Error', 'Please provide a reason for modification', 'error');
            return;
        }
        
        console.log('Submitting modification with data:', { items: modifiedItems, reason: reason });
        
        // Check for significant changes
        const hasSignificantIncrease = modifiedItems.some(item => {
            const input = document.querySelector(`[data-item-id] input[data-original]`);
            const original = parseFloat(input?.dataset.original || 0);
            return item.new_quantity > original * 1.1; // More than 10% increase
        });
        
        if (hasSignificantIncrease) {
            this.showConfirmModal(
                'Confirm Significant Changes',
                'Some quantities have been significantly increased. Are you sure you want to proceed?',
                () => this.submitModificationRequest(modifiedItems, reason),
                'fa-exclamation-triangle'
            );
        } else {
            // Submit the modification directly
            this.submitModificationRequest(modifiedItems, reason);
        }
    },

    // Submit modification request to server
    submitModificationRequest(items, reason) {
        this.showLoadingState();
        
        const url = items.length === 1 ? 
            this.getModifyUrl(this.currentRequisition.id) : 
            this.getModifyMultiUrl(this.currentRequisition.id);
            
        const payload = items.length === 1 ? {
            item_id: items[0].item_id,
            new_quantity: items[0].new_quantity,
            reason: reason
        } : {
            items: items,
            reason: reason
        };
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Success', data.message || 'Requisition modified successfully', 'success');
                this.closeModifyModal();
                
                // Refresh statistics and requisitions
                this.refreshStatistics();
                this.refreshRequisitions();

                // Show success message after a short delay
                setTimeout(() => {
                    this.showToast('Success', data.message || 'Requisitions approved successfully', 'success');
                }, 500);
            } else {
                this.showToast('Error', data.error || 'Failed to modify requisition', 'error');
            }
        })
        .catch(error => {
            console.error('Error modifying requisition:', error);
            this.showToast('Error', 'Failed to modify requisition', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },
    
    // Open detailed requisition modal
    openCombinedModal(requisitionId) {
        // Show loading state
        this.showLoadingState();
        
        // Fetch requisition details
        fetch(this.getDetailsUrl(requisitionId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentRequisition = data.data;
                this.populateDetailsModal(data.data);
                this.showDetailsModal();
            } else {
                this.showToast('Error', data.error || 'Failed to load requisition details', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading requisition details:', error);
            this.showToast('Error', 'Failed to load requisition details', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },
    
    // Approve requisition
    approve(requisitionId) {
        this.showConfirmModal(
            'Confirm Approval',
            'Are you sure you want to approve this requisition?',
            () => {
                this.showLoadingState();
                
                fetch(this.getApproveUrl(requisitionId), {
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
                        this.showToast('Success', 'Requisition approved successfully', 'success');
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showToast('Error', data.message || 'Failed to approve requisition', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error approving requisition:', error);
                    this.showToast('Error', 'Failed to approve requisition', 'error');
                })
                .finally(() => {
                    this.hideLoadingState();
                });
            },
            'fa-check-circle'
        );
    },
    
    // Reject requisition
    reject(requisitionId) {
        // Show rejection reason modal
        this.currentRequisitionId = requisitionId;
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
        
        fetch(this.getRejectUrl(this.currentRequisitionId), {
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
                this.showToast('Success', 'Requisition rejected successfully', 'success');
                this.closeRejectReasonModal();
                
                // Refresh the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast('Error', data.message || 'Failed to reject requisition', 'error');
            }
        })
        .catch(error => {
            console.error('Error rejecting requisition:', error);
            this.showToast('Error', 'Failed to reject requisition', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },
    
    // Populate details modal with requisition data
    populateDetailsModal(requisition) {
        const content = document.getElementById('detailsContent');
        
        const statusConfig = {
            'pending': { class: 'bg-amber-100 text-amber-800', label: 'Pending' },
            'approved': { class: 'bg-green-100 text-green-800', label: 'Approved' },
            'rejected': { class: 'bg-red-100 text-red-800', label: 'Rejected' },
            'fulfilled': { class: 'bg-gray-100 text-gray-600', label: 'Fulfilled' }
        };
        
        const status = statusConfig[requisition.status] || statusConfig.pending;
        
        // Format date
        const date = new Date(requisition.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Generate items HTML
        let itemsHtml = '';
        if (requisition.items && requisition.items.length > 0) {
            itemsHtml = requisition.items.map(item => `
                <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900">${item.item_name}</p>
                        <p class="text-sm text-gray-500">${item.item_code || ''}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">${item.quantity_requested} ${item.unit_symbol || ''}</p>
                        <p class="text-sm text-gray-500">${item.current_stock || 'N/A'} available</p>
                    </div>
                </div>
            `).join('');
        } else {
            itemsHtml = '<p class="text-gray-500 text-center py-4">No items found</p>';
        }
        
        content.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-cream-bg p-4 rounded-lg border border-border-soft">
                        <p class="text-xs font-bold text-caramel uppercase tracking-wide">Request ID</p>
                        <p class="text-lg font-mono font-bold text-chocolate mt-1">#${requisition.requisition_number}</p>
                    </div>
                    <div class="bg-cream-bg p-4 rounded-lg border border-border-soft">
                        <p class="text-xs font-bold text-caramel uppercase tracking-wide">Status</p>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide ${status.class}">
                                ${status.label}
                            </span>
                        </p>
                    </div>
                    <div class="bg-cream-bg p-4 rounded-lg border border-border-soft">
                        <p class="text-xs font-bold text-caramel uppercase tracking-wide">Requested By</p>
                        <p class="text-lg font-bold text-chocolate mt-1">${requisition.requested_by || 'Unknown'}</p>
                    </div>
                    <div class="bg-cream-bg p-4 rounded-lg border border-border-soft">
                        <p class="text-xs font-bold text-caramel uppercase tracking-wide">Department</p>
                        <p class="text-lg font-bold text-chocolate mt-1">${requisition.department || 'General'}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Purpose</p>
                    <p class="text-gray-700 italic">"${requisition.purpose || 'No purpose specified'}"</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold text-chocolate mb-4 flex items-center">
                        <i class="fas fa-list mr-2"></i> Items Requested (${requisition.total_items || 0} items)
                    </h4>
                    <div class="bg-white border border-border-soft rounded-lg">
                        ${itemsHtml}
                    </div>
                </div>
                
                ${requisition.notes && requisition.notes.includes(' - ') ? `
                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                        <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-2">Rejection Reason</p>
                        <p class="text-red-700 italic">"${requisition.notes}"</p>
                    </div>
                ` : ''}
            </div>
        `;
    },
    
    // Show/hide modal functions
    showDetailsModal() {
        document.getElementById('detailsModalBackdrop').classList.remove('hidden');
    },
    
    closeDetailsModal() {
        document.getElementById('detailsModalBackdrop').classList.add('hidden');
        this.currentRequisition = null;
    },
    
    closeRejectReasonModal() {
        document.getElementById('rejectReasonModal').classList.add('hidden');
        document.getElementById('rejectReasonSelect').value = '';
        document.getElementById('rejectComments').value = '';
        this.currentRequisitionId = null;
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
        
        // Modify modal close on backdrop click
        document.getElementById('modifyModal').addEventListener('click', (e) => {
            if (e.target.id === 'modifyModal') {
                this.closeModifyModal();
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
                this.closeModifyModal();
                this.closeRejectReasonModal();
            }
        });
    },
    
    // Setup bulk selection functionality
    setupBulkSelection() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');

        // Select All checkbox event listener
        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            // Only select checkboxes that are not disabled (sufficient stock)
            const enabledCheckboxes = document.querySelectorAll('.requisition-checkbox:not([disabled])');

            enabledCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            this.updateBulkApproveButton();
        });

        // Individual checkbox event listeners (using event delegation)
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('requisition-checkbox')) {
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
        const enabledCheckboxes = document.querySelectorAll('.requisition-checkbox:not([disabled])');
        const checkedEnabledBoxes = document.querySelectorAll('.requisition-checkbox:not([disabled]):checked');

        if (enabledCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }

        if (checkedEnabledBoxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedEnabledBoxes.length === enabledCheckboxes.length) {
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
        const checkedBoxes = document.querySelectorAll('.requisition-checkbox:checked');

        bulkApproveBtn.disabled = checkedBoxes.length === 0;
    },

    // Get selected requisition IDs
    getSelectedRequisitionIds() {
        const checkedBoxes = document.querySelectorAll('.requisition-checkbox:checked');
        return Array.from(checkedBoxes).map(checkbox => parseInt(checkbox.value));
    },

    // Bulk approve selected requisitions
    bulkApproveSelected() {
        const selectedIds = this.getSelectedRequisitionIds();

        if (selectedIds.length === 0) {
            this.showToast('Warning', 'Please select at least one requisition to approve', 'error');
            return;
        }

        this.showConfirmModal(
            'Confirm Bulk Approval',
            `Are you sure you want to approve ${selectedIds.length} requisition${selectedIds.length > 1 ? 's' : ''}?`,
            () => this.performBulkApprove(selectedIds),
            'fa-check-double'
        );
    },

    // Perform bulk approve API call
    performBulkApprove(requisitionIds) {
        this.showLoadingState();

        fetch('/supervisor/requisitions/bulk-approve', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                requisition_ids: requisitionIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Success', data.message || 'Requisitions approved successfully', 'success');

                // Clear selections
                document.querySelectorAll('.requisition-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.getElementById('selectAllCheckbox').checked = false;
                this.updateBulkApproveButton();

                // Refresh the page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast('Error', data.error || 'Failed to bulk approve requisitions', 'error');
            }
        })
        .catch(error => {
            console.error('Error bulk approving requisitions:', error);
            this.showToast('Error', 'Failed to bulk approve requisitions', 'error');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    },

    // Refresh statistics from server
    refreshStatistics() {
        fetch('/supervisor/requisitions/api/statistics', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateStatisticsDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error refreshing statistics:', error);
        });
    },

    // Update statistics display in the UI
    updateStatisticsDisplay(statistics) {
        // Update pending count
        const pendingBadge = document.querySelector('.bg-amber-100.text-amber-800');
        if (pendingBadge) {
            const pendingText = pendingBadge.querySelector('span:last-child');
            if (pendingText) {
                pendingText.textContent = `${statistics.pending ?? 0} Pending`;
            }
        }

        // Update approved today
        const approvedBadge = document.querySelector('.bg-blue-100.text-blue-800');
        if (approvedBadge) {
            const approvedText = approvedBadge.querySelector('span:last-child');
            if (approvedText) {
                approvedText.textContent = `${statistics.approved_today ?? 0} Approved Today`;
            }
        }

        // Update approved this week
        const weekBadge = document.querySelector('.bg-green-100.text-green-800');
        if (weekBadge) {
            const weekText = weekBadge.querySelector('span:last-child');
            if (weekText) {
                weekText.textContent = `${statistics.total_approved_this_week ?? 0} This Week`;
            }
        }

        // Update rejected this week
        const rejectedBadge = document.querySelector('.bg-red-100.text-red-800');
        if (rejectedBadge) {
            const rejectedText = rejectedBadge.querySelector('span:last-child');
            if (rejectedText) {
                rejectedText.textContent = `${statistics.total_rejected_this_week ?? 0} Rejected`;
            }
        }
    },

    // Refresh requisitions list
    refreshRequisitions() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const highStock = document.getElementById('highStockFilter').checked;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (highStock) params.append('high_stock', '1');

        const url = `/supervisor/requisitions/api/filtered?${params.toString()}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateRequisitionsDisplay(data.data.requisitions);
            }
        })
        .catch(error => {
            console.error('Error refreshing requisitions:', error);
        });
    },

    // Filter functionality
    setupFilters() {
        // Status filter change - submit form immediately
        document.getElementById('statusFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        // Search input with debounce - submit form after delay
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 800);
        });

        // High stock filter - submit form immediately
        document.getElementById('highStockFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    },
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    RequisitionManager.init();
});

// Global functions for onclick handlers
function closeDetailsModal() {
    RequisitionManager.closeDetailsModal();
}

function closeModifyModal() {
    RequisitionManager.closeModifyModal();
}

function closeRejectReasonModal() {
    RequisitionManager.closeRejectReasonModal();
}

function closeConfirmModal() {
    RequisitionManager.closeConfirmModal();
}

// Clear all filters and reset the form
function clearFilters() {
    // Reset form fields
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'pending';
    document.getElementById('highStockFilter').checked = false;
    
    // Submit form to reload with default filters
    document.getElementById('filterForm').submit();
}

// Refresh data using AJAX to maintain current filters
function refreshData() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const highStock = document.getElementById('highStockFilter').checked;
    
    // Show loading state
    RequisitionManager.showLoadingState();
    
    // Build query parameters
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (highStock) params.append('high_stock', '1');
    
    const url = `/supervisor/requisitions/api/filtered?${params.toString()}`;
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the requisitions container with new data
            updateRequisitionsDisplay(data.data.requisitions);
            RequisitionManager.showToast('Success', 'Data refreshed successfully', 'success');
        } else {
            RequisitionManager.showToast('Error', data.error || 'Failed to refresh data', 'error');
        }
    })
    .catch(error => {
        console.error('Error refreshing data:', error);
        RequisitionManager.showToast('Error', 'Failed to refresh data', 'error');
    })
    .finally(() => {
        RequisitionManager.hideLoadingState();
    });
}

// Update the display of requisitions (placeholder for now)
function updateRequisitionsDisplay(requisitions) {
    // For now, just reload the page to show updated data
    // In a full implementation, this would update the DOM directly
    location.reload();
}
</script>
@endpush