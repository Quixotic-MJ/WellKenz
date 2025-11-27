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
            <h3 class="font-display text-lg font-bold text-chocolate">Filter Requisitions</h3>
            <button onclick="clearFilters()" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider hover:underline decoration-caramel/30 underline-offset-2 transition-colors">
                Reset Filters
            </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" id="searchInput" 
                       class="block w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                       placeholder="Search by requester or item..." 
                       value="{{ request('search') }}">
            </div>

            <div class="relative">
                <select id="statusFilter" class="block w-full py-2.5 px-4 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer shadow-sm">
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
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" id="highStockFilter" class="rounded border-gray-300 text-chocolate focus:ring-caramel w-4 h-4 transition-all cursor-pointer">
                <span class="text-sm text-gray-600 font-medium group-hover:text-chocolate transition-colors">Show High Stock Usage Only (>80%)</span>
            </label>
            
            <button onclick="refreshData()" class="w-full sm:w-auto px-5 py-2 bg-chocolate text-white hover:bg-chocolate-dark rounded-lg shadow-md transition-all text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 transform active:scale-95">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
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
                                <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" 
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
{{-- Insert the JS block from the previous response here (RequisitionManager) --}}
{{-- It is perfectly compatible and contains all the logic for these modals and interactions. --}}
@endpush