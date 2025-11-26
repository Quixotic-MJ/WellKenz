@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. Welcome card --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm relative overflow-hidden">
        <!-- Decorative background pattern -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 -mr-16 -mt-16"></div>
        
        <div class="flex items-center justify-between relative z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Requisition Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review stock requests from the production team.</p>
            </div>
            <div class="text-right">
                <div class="flex items-center gap-3 mb-2">
                    <div class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                        {{ $pendingCount ?? 0 }} Pending
                    </div>
                    <div class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                        {{ $approvedToday ?? 0 }} Approved Today
                    </div>
                </div>
                <p class="text-sm text-gray-900 font-medium">{{ now()->format('M d, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ now()->format('l') }}</p>
            </div>
        </div>
    </div>

    {{-- 2. FILTERS & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">Filter Requisitions</h3>
            <button onclick="clearFilters()" class="text-xs text-blue-600 hover:underline">Clear All Filters →</button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search by requester or item..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" {{ (!request('status') || request('status') == 'pending') ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All History</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="highStockFilter" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 font-medium">High Stock Usage (>80%)</span>
                </label>
            </div>
            
            <button onclick="refreshData()" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded-lg shadow-sm font-medium text-sm flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Refresh
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
                    
                    // Generate initials for avatar
                    $nameParts = explode(' ', $requisition->requestedBy->name ?? 'Unknown User');
                    $initials = '';
                    foreach($nameParts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    $initials = substr($initials, 0, 2);
                @endphp

                {{-- REQUISITION CARD --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow relative {{ $hasHighRequest && !$hasSufficientStock ? 'border-l-4 border-l-red-500' : '' }}">
                    
                    {{-- Status Badges --}}
                    <div class="absolute top-4 right-4 flex gap-2">
                        @if($hasHighRequest && !$hasSufficientStock)
                            <span class="px-2.5 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">HIGH USAGE</span>
                        @endif
                        @if(!$hasSufficientStock && $currentStock > 0)
                            <span class="px-2.5 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">LOW STOCK</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- Left: Header Info -->
                        <div class="lg:col-span-3 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                                    RQ
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">#{{ $requisition->requisition_number }}</h3>
                                    <p class="text-xs text-gray-500">{{ $requisition->requestedBy->name ?? 'Unknown User' }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Department</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $requisition->department ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Status</span>
                                    @php
                                        $statusClass = match($requisition->status) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        $statusIcon = match($requisition->status) {
                                            'approved' => 'fa-check-circle',
                                            'rejected' => 'fa-times-circle',
                                            'pending' => 'fa-clock',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} text-[10px] mr-1"></i>{{ ucfirst($requisition->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Request Date</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $requisition->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Time Created</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $requisition->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Middle: Items Summary -->
                        <div class="lg:col-span-6">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-bold text-gray-900">Items Requested ({{ $requisition->requisitionItems->count() }} items)</h4>
                                    @if($mainItem && $currentStock > 0)
                                        <div class="text-right">
                                            <span class="text-sm font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ number_format($stockPercentage, 1) }}% Stock Usage
                                            </span>
                                            @if($hasSufficientStock)
                                                <i class="fas fa-check-circle text-green-500 ml-1"></i>
                                            @else
                                                <i class="fas fa-exclamation-triangle text-red-500 ml-1"></i>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="space-y-2">
                                    @if($mainItem)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $mainItem->item->name ?? 'Unknown Item' }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($requestedQty, 1) }} {{ $mainItem->item->unit->symbol ?? '' }} requested</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-gray-900' }}">
                                                    {{ number_format($currentStock, 1) }} {{ $mainItem->item->unit->symbol ?? '' }} in stock
                                                </p>
                                                @if($hasSufficientStock)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check text-[10px] mr-1"></i>Sufficient
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-exclamation text-[10px] mr-1"></i>Insufficient
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($requisition->requisitionItems->count() > 1)
                                        <div class="text-center py-2">
                                            <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" class="text-xs text-blue-600 hover:underline font-medium">
                                                View all {{ $requisition->requisitionItems->count() }} items
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($requisition->status === 'rejected' && $requisition->reject_reason)
                                <div class="mt-3 p-3 bg-red-50 rounded-lg border border-red-100">
                                    <p class="text-xs text-red-700"><strong>Reject Reason:</strong> {{ $requisition->reject_reason }}</p>
                                    @if($requisition->rejected_at)
                                        <p class="text-xs text-red-500 mt-1">{{ $requisition->rejected_at->format('M d, Y H:i') }}</p>
                                    @endif
                                </div>
                            @elseif($requisition->notes)
                                <div class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                                    <p class="text-xs text-yellow-700"><strong>Notes:</strong> {{ $requisition->notes }}</p>
                                </div>
                            @elseif($requisition->purpose)
                                <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <p class="text-xs text-blue-700"><strong>Purpose:</strong> {{ $requisition->purpose }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Actions -->
                        <div class="lg:col-span-3 flex flex-col justify-center space-y-3">
                            <div class="text-center mb-4">
                                <p class="text-xs text-gray-500 uppercase font-bold">Action Required</p>
                            </div>
                            
                            @if($requisition->status === 'pending')
                                <button onclick="RequisitionManager.approve({{ $requisition->id }})" 
                                        class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2 {{ !$hasSufficientStock ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$hasSufficientStock ? 'disabled' : '' }}>
                                    <i class="fas fa-check-circle"></i> 
                                    Approve {{ !$hasSufficientStock ? '(Insufficient Stock)' : '' }}
                                </button>
                                <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-eye"></i> View & Modify
                                </button>
                                <button onclick="RequisitionManager.reject({{ $requisition->id }})" class="w-full py-2.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i> Reject
                                </button>
                            @else
                                <div class="text-center">
                                    @php
                                        $statusClass = match($requisition->status) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        $statusIcon = match($requisition->status) {
                                            'approved' => 'fa-check-circle',
                                            'rejected' => 'fa-times-circle',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} text-[10px]"></i> {{ ucfirst($requisition->status) }}
                                    </span>
                                    @if($requisition->approved_at)
                                        <p class="text-xs text-gray-500 mt-2">{{ $requisition->approved_at->format('M d, Y H:i') }}</p>
                                    @endif
                                    <button onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})" class="mt-2 text-xs text-blue-600 hover:underline">View Details</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-gray-300 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No Requisitions Found</h3>
                <p class="text-gray-500">There are no requisitions matching your current filters.</p>
                <button onclick="clearFilters()" class="mt-4 text-blue-600 font-medium hover:underline">Clear all filters</button>
            </div>
        @endif
    </div>

    {{-- 4. PAGINATION --}}
    @if(isset($requisitions) && $requisitions->hasPages())
        <div class="flex justify-center">
            {{ $requisitions->links() }}
        </div>
    @endif

</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl overflow-hidden relative z-10 border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
                <h3 class="text-lg font-bold text-gray-800">Requisition Details</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="detailsContent" class="p-6 overflow-y-auto max-h-[70vh]"></div>
        </div>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center transform transition-all border border-gray-100 relative z-10">
            <div id="confirmIconContainer" class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i id="confirmIcon" class="fas fa-question text-2xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3" id="confirmTitle">Confirm Action</h3>
            <p class="text-gray-600 mb-8 leading-relaxed" id="confirmMessage">Are you sure you want to proceed with this action?</p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button id="confirmBtn" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition-colors shadow-sm">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- REJECT REASON MODAL --}}
<div id="rejectReasonModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 border border-gray-100 relative z-10">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Reject Requisition</h3>
                <p class="text-gray-600" id="rejectReasonTitle">Please provide a reason for rejection</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reject Reason <span class="text-red-500">*</span></label>
                <select id="rejectReasonSelect" class="block w-full py-3 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Select a reason...</option>
                    <option value="Insufficient Stock">Insufficient Stock</option>
                    <option value="Invalid Request">Invalid Request</option>
                    <option value="Duplicate Request">Duplicate Request</option>
                    <option value="Policy Violation">Policy Violation</option>
                    <option value="Quality Issues">Quality Issues</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Comments</label>
                <textarea id="rejectComments" rows="3" class="block w-full py-3 px-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Optional additional details..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeRejectReasonModal()" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium transition-colors">
                    Reject Requisition
                </button>
            </div>
        </div>
    </div>
</div>

{{-- TOAST NOTIFICATION --}}
<div id="toast" class="fixed top-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-[-20px] opacity-0">
    <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-xl shadow-2xl p-4 flex items-center gap-4 min-w-[320px]">
        <div id="toastIconContainer" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
            <i id="toastIcon" class="fas fa-check"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-gray-900" id="toastTitle">Success</h4>
            <p class="text-xs text-gray-600 mt-0.5" id="toastMessage">Action completed successfully.</p>
        </div>
    </div>
</div>

<script>
const RequisitionManager = {
    currentRequisitionId: null,
    modifiedItems: new Map(),
    rejectContext: null,
    
    // Show notification toast
    showToast(type, title, message) {
        const toast = document.getElementById('toast');
        const container = document.getElementById('toastIconContainer');
        const icon = document.getElementById('toastIcon');
        
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastMessage').textContent = message;

        if(type === 'error') {
            container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-100';
            icon.className = 'fas fa-times text-red-600';
        } else {
            container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-green-100';
            icon.className = 'fas fa-check text-green-600';
        }

        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.remove('translate-y-[-20px]', 'opacity-0'), 10);
        setTimeout(() => {
            toast.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    },
    
    // Open combined modal
    openCombinedModal(requisitionId) {
        this.currentRequisitionId = requisitionId;
        this.modifiedItems.clear();
        
        // Load requisition details
        this.loadRequisitionDetails(requisitionId);
        
        // Show modal
        const modal = document.getElementById('detailsModalBackdrop');
        modal.classList.remove('hidden');
        modal.style.opacity = '0';
        requestAnimationFrame(() => {
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '1';
        });
    },
    
    closeModal() {
        const modal = document.getElementById('detailsModalBackdrop');
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.opacity = '';
            modal.style.transition = '';
        }, 300);
        this.currentRequisitionId = null;
        this.modifiedItems.clear();
    },
    
    // Load requisition details via API
    loadRequisitionDetails(requisitionId) {
        const content = document.getElementById('detailsContent');
        
        // Show loading state
        content.innerHTML = `
            <div class="p-8 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-500">Loading requisition details...</p>
            </div>
        `;
        
        // Make API call
        fetch(`/supervisor/requisitions/${requisitionId}/details`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                this.renderRequisitionDetails(data.data);
            } else {
                throw new Error(data.error || 'Failed to load requisition details');
            }
        })
        .catch(error => {
            console.error('Error loading requisition:', error);
            content.innerHTML = `
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Error Loading Details</h3>
                    <p class="text-gray-500">${error.message}</p>
                </div>
            `;
        });
    },
    
    // Render requisition details
    renderRequisitionDetails(data) {
        const content = document.getElementById('detailsContent');
        
        // Render overview
        const statusBadge = data.status === 'pending' 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">PENDING</span>'
            : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${data.status === 'approved' ? 'green' : 'red'}-100 text-${data.status === 'approved' ? 'green' : 'red'}-800">${data.status.toUpperCase()}</span>`;

        let itemsHtml = '';
        data.items.forEach(item => {
            const stockStatus = item.can_fulfill_full 
                ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check w-3 h-3 mr-1"></i>In Stock</span>'
                : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-exclamation w-3 h-3 mr-1"></i>Low Stock</span>';
                
            const stockPercentageClass = item.stock_percentage > 80 ? 'text-red-600' : 'text-gray-700';
                
            itemsHtml += `
                <div class="bg-white rounded-lg p-4 border border-gray-200 hover:border-blue-300 transition-colors" data-item-id="${item.item_id}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h5 class="text-sm font-bold text-gray-900">${item.item_name}</h5>
                                <span class="text-xs text-gray-500">(${item.unit_symbol})</span>
                                ${stockStatus}
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-600 mb-4">
                        <div>
                            <span class="font-medium">Requested:</span> 
                            <span class="font-bold text-blue-600">${item.quantity_requested} ${item.unit_symbol}</span>
                        </div>
                        <div>
                            <span class="font-medium">Stock:</span> 
                            <span class="font-bold">${item.current_stock} ${item.unit_symbol}</span>
                        </div>
                        <div>
                            <span class="font-medium">Usage:</span> 
                            <span class="font-bold ${stockPercentageClass}">${item.stock_percentage}%</span>
                        </div>
                        <div>
                            <span class="font-medium">Modified:</span> 
                            <span id="modified-status-${item.item_id}" class="font-bold text-gray-400">No</span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">New Quantity</label>
                                <input type="number" 
                                       id="new_qty_${item.item_id}" 
                                       data-requisition-item-id="${item.id}"
                                       step="0.001" 
                                       min="0" 
                                       value="${item.quantity_requested}"
                                       data-original="${item.quantity_requested}"
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="RequisitionManager.onQuantityChange(${item.item_id})">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                                <select id="reason_${item.item_id}" 
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="RequisitionManager.onReasonChange(${item.item_id})">
                                    <option value="">Select reason</option>
                                    <option value="Insufficient Stock">Insufficient Stock</option>
                                    <option value="Rationing (High Demand)">Rationing (High Demand)</option>
                                    <option value="Policy Limit Exceeded">Policy Limit Exceeded</option>
                                    <option value="Quality Issues">Quality Issues</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Remarks</label>
                            <textarea id="remarks_${item.item_id}" 
                                      rows="2" 
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Optional remarks..."
                                      onchange="RequisitionManager.onRemarksChange(${item.item_id})"></textarea>
                        </div>
                    </div>
                </div>
            `;
        });
        
        content.innerHTML = `
            <div class="space-y-6 p-6">
                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                    <div class="space-y-1">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Reference Number</span>
                        <div class="font-mono font-bold text-gray-900 text-lg">${data.requisition_number}</div>
                    </div>
                    <div class="space-y-1 text-right">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Status</span>
                        <div class="mt-1">${statusBadge}</div>
                    </div>
                    <div class="col-span-2 border-t border-gray-200 my-1"></div>
                    <div><span class="text-gray-400 text-xs block">Requested By</span><span class="font-medium text-gray-800">${data.requested_by}</span></div>
                    <div><span class="text-gray-400 text-xs block">Department</span><span class="font-medium text-gray-800">${data.department}</span></div>
                    <div><span class="text-gray-400 text-xs block">Request Date</span><span class="font-medium text-gray-800">${new Date(data.created_at).toLocaleDateString()}</span></div>
                    <div><span class="text-gray-400 text-xs block">Total Items</span><span class="font-medium text-gray-800">${data.total_items}</span></div>
                </div>
                
                ${data.purpose ? `
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <span class="text-blue-700 text-xs font-bold uppercase block mb-1">Purpose</span>
                        <p class="text-blue-900 text-sm">${data.purpose}</p>
                    </div>
                ` : ''}
                
                <!-- Items Section -->
                <div>
                    <h4 class="font-bold text-gray-800 mb-3 text-sm">Items for Modification (${data.total_items} items)</h4>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        ${itemsHtml}
                    </div>
                    <div class="mt-4 text-sm text-gray-500 text-right">
                        <span id="modifiedCount">0</span> of <span id="totalItemsCount">${data.total_items}</span> items modified
                    </div>
                </div>
                
                ${data.notes ? `
                    <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                        <span class="text-yellow-700 text-xs font-bold uppercase block mb-1">Notes</span>
                        <p class="text-yellow-900 text-sm">${data.notes}</p>
                    </div>
                ` : ''}
                
                <div class="flex gap-3 justify-end pt-4 border-t">
                    <button onclick="RequisitionManager.closeModal()" 
                            class="px-6 py-3 bg-white text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <button onclick="RequisitionManager.approveRequisition()" 
                            class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium transition-colors">
                        <i class="fas fa-check mr-2"></i>Approve Requisition
                    </button>
                    <button onclick="RequisitionManager.rejectRequisition()" 
                            class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Reject Requisition
                    </button>
                </div>
            </div>
        `;
    },
    
    // Handle quantity changes
    onQuantityChange(itemId) {
        const input = document.getElementById(`new_qty_${itemId}`);
        const original = parseFloat(input.dataset.original);
        const newValue = parseFloat(input.value);
        
        if (newValue !== original) {
            this.trackModification(itemId, { new_quantity: newValue });
            input.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            this.untrackModification(itemId, 'new_quantity');
            input.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        this.updateModifiedCount();
    },
    
    // Handle reason changes
    onReasonChange(itemId) {
        const select = document.getElementById(`reason_${itemId}`);
        const value = select.value;
        
        if (value) {
            this.trackModification(itemId, { reason: value });
            select.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            this.untrackModification(itemId, 'reason');
            select.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        this.updateCardStatus(itemId);
        this.updateModifiedCount();
    },
    
    // Handle remarks changes
    onRemarksChange(itemId) {
        const textarea = document.getElementById(`remarks_${itemId}`);
        const value = textarea.value.trim();
        
        if (value) {
            this.trackModification(itemId, { remarks: value });
            textarea.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            this.untrackModification(itemId, 'remarks');
            textarea.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        this.updateModifiedCount();
    },
    
    // Track modifications
    trackModification(itemId, changes) {
        if (!this.modifiedItems.has(itemId)) {
            this.modifiedItems.set(itemId, {});
        }
        
        const current = this.modifiedItems.get(itemId);
        Object.assign(current, changes);
        
        this.updateCardStatus(itemId);
    },
    
    // Untrack modifications
    untrackModification(itemId, field) {
        if (this.modifiedItems.has(itemId)) {
            const current = this.modifiedItems.get(itemId);
            delete current[field];
            
            if (Object.keys(current).length === 0) {
                this.modifiedItems.delete(itemId);
            }
        }
        
        this.updateCardStatus(itemId);
    },
    
    // Update card status
    updateCardStatus(itemId) {
        const statusSpan = document.getElementById(`modified-status-${itemId}`);
        const card = document.querySelector(`[data-item-id="${itemId}"]`);
        
        if (this.modifiedItems.has(itemId)) {
            statusSpan.textContent = 'Yes';
            statusSpan.className = 'font-bold text-green-600';
            card.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            statusSpan.textContent = 'No';
            statusSpan.className = 'font-bold text-gray-400';
            card.classList.remove('border-blue-500', 'bg-blue-50');
        }
    },
    
    // Update modified count
    updateModifiedCount() {
        const modifiedCountEl = document.getElementById('modifiedCount');
        if (modifiedCountEl) {
            modifiedCountEl.textContent = this.modifiedItems.size;
        }
    },
    
    // Approve requisition
    approveRequisition() {
        if (this.modifiedItems.size > 0) {
            // Validate modifications
            for (let [itemId, modifications] of this.modifiedItems) {
                if (!modifications.reason) {
                    this.showToast('error', 'Validation Required', 'Please provide a reason for all modified items before approving.');
                    return;
                }
            }
            
            // Submit modifications first, then approve
            this.submitModifications(() => {
                this.performAction(this.currentRequisitionId, 'approve');
            });
        } else {
            this.performAction(this.currentRequisitionId, 'approve');
        }
    },
    
    // Reject requisition
    rejectRequisition() {
        this.rejectContext = { id: this.currentRequisitionId };
        document.getElementById('rejectReasonTitle').textContent = `Reject Requisition #${this.currentRequisitionId}`;
        document.getElementById('rejectReasonSelect').value = '';
        document.getElementById('rejectComments').value = '';
        document.getElementById('rejectReasonModal').classList.remove('hidden');
    },
    
    // Submit modifications
    submitModifications(callback) {
        if (this.modifiedItems.size === 0) {
            if (callback) callback();
            return;
        }

        const modifications = [];
        for (let [itemId, changes] of this.modifiedItems) {
            const input = document.getElementById(`new_qty_${itemId}`);
            // Use the requisition_item_id from data attributes instead of item_id
            const requisitionItemId = input.dataset.requisitionItemId;
            modifications.push({
                item_id: requisitionItemId, // This should be the requisition_items.id
                new_quantity: changes.new_quantity || parseFloat(input.dataset.original),
                reason: changes.reason,
                remarks: changes.remarks || ''
            });
        }

        fetch(`/supervisor/requisitions/${this.currentRequisitionId}/modify-multi`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                modifications: modifications
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('success', 'Modified', data.message || 'Items modified successfully.');
                if (callback) callback();
            } else {
                this.showToast('error', 'Error', data.error || 'Failed to modify requisition items.');
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            this.showToast('error', 'Network Error', 'An error occurred while submitting modifications.');
        });
    },
    
    // Perform action (approve/reject)
    performAction(id, action, rejectReason = null) {
        const body = {};
        if (action === 'reject' && rejectReason) {
            body.reject_reason = rejectReason;
        }
        
        fetch(`/supervisor/requisitions/${id}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: Object.keys(body).length > 0 ? JSON.stringify(body) : undefined
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const msg = action === 'approve' ? 'Requisition approved successfully!' : 'Requisition rejected successfully!';
                this.showToast('success', 'Completed', msg);
                this.closeModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showToast('error', 'Error', data.error || 'An unknown error occurred.');
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            this.showToast('error', 'Network Error', 'An error occurred while processing your request.');
        });
    },
    
    // Simple approve from table
    approve(id) {
        this.showConfirmModal(
            'Approve Requisition',
            'Are you sure you want to approve this requisition?',
            () => this.performAction(id, 'approve'),
            'check-circle',
            'bg-green-50 text-green-600'
        );
    },
    
    // Simple reject from table
    reject(id) {
        this.rejectContext = { id: id };
        document.getElementById('rejectReasonTitle').textContent = `Reject Requisition #${id}`;
        document.getElementById('rejectReasonSelect').value = '';
        document.getElementById('rejectComments').value = '';
        document.getElementById('rejectReasonModal').classList.remove('hidden');
    },
    
    // Show confirmation modal
    showConfirmModal(title, message, callback, icon = 'question', iconClass = 'bg-blue-50 text-blue-600') {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmIcon').className = `fas fa-${icon} text-2xl`;
        document.getElementById('confirmIconContainer').className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 ${iconClass}`;
        
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('hidden');
        modal.style.opacity = '0';
        requestAnimationFrame(() => {
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '1';
        });
        
        // Store callback
        this.confirmCallback = callback;
    },
    
    // Close confirmation modal
    closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.opacity = '';
            modal.style.transition = '';
            this.confirmCallback = null;
        }, 300);
    },
    
    // Close reject modal
    closeRejectModal() {
        document.getElementById('rejectReasonModal').classList.add('hidden');
        this.rejectContext = null;
    },
    
    // Confirm reject
    confirmReject() {
        if (!this.rejectContext) return;
        
        const reason = document.getElementById('rejectReasonSelect').value;
        const comments = document.getElementById('rejectComments').value.trim();
        
        if (!reason) {
            this.showToast('error', 'Validation Required', 'Please select a reject reason');
            return;
        }
        
        const fullReason = comments ? `${reason} - ${comments}` : reason;
        this.performAction(this.rejectContext.id, 'reject', fullReason);
        this.closeRejectModal();
    }
};

// Global functions
function closeDetailsModal() {
    RequisitionManager.closeModal();
}

function closeConfirmModal() {
    RequisitionManager.closeConfirmModal();
}

function closeRejectReasonModal() {
    RequisitionManager.closeRejectModal();
}

function refreshData() {
    window.location.reload();
}

function clearFilters() {
    window.location.href = window.location.pathname;
}

// Setup filters
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
});

function setupFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 300);
        });
    }
}

function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status && status !== 'pending') params.append('status', status);
    
    const url = params.toString() ? `?${params.toString()}` : '';
    window.location.href = url;
}

// Event listeners for modals
document.getElementById('confirmBtn').onclick = function() {
    if (RequisitionManager.confirmCallback) {
        RequisitionManager.confirmCallback();
    }
    RequisitionManager.closeConfirmModal();
};

document.getElementById('confirmRejectBtn').onclick = function() {
    RequisitionManager.confirmReject();
};
</script>
@endsection