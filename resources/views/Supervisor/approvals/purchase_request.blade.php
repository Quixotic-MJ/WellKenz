@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. Welcome card --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm relative overflow-hidden">
        <!-- Decorative background pattern -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 -mr-16 -mt-16"></div>
        
        <div class="flex items-center justify-between relative z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Purchase Request Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Review and approve procurement plans from the Purchasing Officer.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">{{ now()->format('M d, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ now()->format('l') }}</p>
            </div>
        </div>
    </div>

    

    {{-- 3. FILTERS & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">Filter Purchase Requests</h3>
            <button onclick="clearFilters()" class="text-xs text-blue-600 hover:underline">Clear All Filters →</button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search PR number, requester, department..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" {{ (!request('status') || request('status') == 'pending') ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select id="priorityFilter" class="block w-full py-2.5 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="highValueFilter" {{ request('high_value') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 font-medium">High Value (≥ ₱10,000)</span>
                </label>
            </div>
            
            <button onclick="refreshData()" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded-lg shadow-sm font-medium text-sm flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    {{-- 4. PURCHASE REQUESTS LIST --}}
    <div class="space-y-6" id="purchaseRequestsContainer">
        @if(isset($purchaseRequests) && $purchaseRequests->count() > 0)
            @foreach($purchaseRequests as $pr)
                @php
                    $isHighValue = $pr->total_estimated_cost >= 10000;
                    $isUrgent = $pr->priority === 'urgent';
                    $isOverdue = $pr->created_at->lt(now()->subDays(7));
                    $requesterName = $pr->requestedBy ? ($pr->requestedBy->name ?? 'Unknown User') : 'Unknown User';
                @endphp

                {{-- PR CARD --}}
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow relative {{ $isHighValue ? 'border-l-4 border-l-red-500' : '' }}">
                    
                    {{-- Status Badges --}}
                    <div class="absolute top-4 right-4 flex gap-2">
                        @if($isHighValue)
                            <span class="px-2.5 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">HIGH VALUE</span>
                        @endif
                        @if($isUrgent)
                            <span class="px-2.5 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">URGENT</span>
                        @endif
                        @if($isOverdue)
                            <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">OVERDUE</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- Left: Header Info -->
                        <div class="lg:col-span-3 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                                    PR
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $pr->pr_number }}</h3>
                                    <p class="text-xs text-gray-500">{{ $requesterName }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Department</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $pr->department ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Priority</span>
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full 
                                        @if($pr->priority === 'urgent') bg-red-100 text-red-700
                                        @elseif($pr->priority === 'high') bg-orange-100 text-orange-700  
                                        @elseif($pr->priority === 'low') bg-gray-100 text-gray-700
                                        @else bg-blue-100 text-blue-700 @endif">
                                        {{ ucfirst($pr->priority) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Request Date</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $pr->request_date->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Time Created</span>
                                    <span class="text-xs font-medium text-gray-900">{{ $pr->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Middle: Items Summary -->
                        <div class="lg:col-span-6">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-bold text-gray-900">Items Requested ({{ $pr->purchaseRequestItems->count() }} items)</h4>
                                    <span class="text-sm font-bold text-gray-900">₱ {{ number_format($pr->total_estimated_cost, 2) }}</span>
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($pr->purchaseRequestItems->take(3) as $item)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->item->name ?? 'Unknown Item' }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($item->quantity_requested, 1) }} {{ $item->item->unit->symbol ?? '' }} × ₱ {{ number_format($item->unit_price_estimate, 2) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-gray-900">₱ {{ number_format($item->total_estimated_cost, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($pr->purchaseRequestItems->count() > 3)
                                        <div class="text-center py-2">
                                            <button onclick="showAllItems({{ $pr->id }})" class="text-xs text-blue-600 hover:underline font-medium">
                                                Show {{ $pr->purchaseRequestItems->count() - 3 }} more items
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($pr->notes)
                                <div class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                                    <p class="text-xs text-yellow-700"><strong>Notes:</strong> {{ $pr->notes }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Actions -->
                        <div class="lg:col-span-3 flex flex-col justify-center space-y-3">
                            <div class="text-center mb-4">
                                <p class="text-xs text-gray-500 uppercase font-bold">Action Required</p>
                            </div>
                            
                            @if($pr->status === 'pending')
                                <button onclick="approvePR({{ $pr->id }}, '{{ $pr->pr_number }}')" class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Approve Request
                                </button>
                                <button onclick="viewPRDetails({{ $pr->id }})" class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <button onclick="rejectPR({{ $pr->id }}, '{{ $pr->pr_number }}')" class="w-full py-2.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm font-medium text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-times-circle"></i> Reject
                                </button>
                            @else
                                <div class="text-center">
                                    @php
                                        $statusClass = match($pr->status) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'converted' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        $statusIcon = match($pr->status) {
                                            'approved' => 'fa-check-circle',
                                            'rejected' => 'fa-times-circle',
                                            'converted' => 'fa-share',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} text-[10px]"></i> {{ ucfirst($pr->status) }}
                                    </span>
                                    @if($pr->status === 'rejected')
                                        <div class="mt-2">
                                            @if($pr->reject_reason)
                                                <div class="bg-red-50 border border-red-200 rounded-lg p-2 mt-1">
                                                    <p class="text-xs text-red-700"><strong>Reject Reason:</strong> {{ $pr->reject_reason }}</p>
                                                </div>
                                            @endif
                                            @if($pr->rejected_at)
                                                <p class="text-xs text-gray-500 mt-1">{{ $pr->rejected_at->format('M d, Y H:i') }}</p>
                                            @endif
                                        </div>
                                    @elseif($pr->approved_at)
                                        <p class="text-xs text-gray-500 mt-2">{{ $pr->approved_at->format('M d, Y H:i') }}</p>
                                    @endif
                                    <button onclick="viewPRDetails({{ $pr->id }})" class="mt-2 text-xs text-blue-600 hover:underline">View Details</button>
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
                <h3 class="text-lg font-bold text-gray-900 mb-2">No Purchase Requests Found</h3>
                <p class="text-gray-500">There are no purchase requests matching your current filters.</p>
                <button onclick="clearFilters()" class="mt-4 text-blue-600 font-medium hover:underline">Clear all filters</button>
            </div>
        @endif
    </div>

    {{-- 4. PAGINATION --}}
    @if(isset($purchaseRequests) && $purchaseRequests->hasPages())
        <div class="flex justify-center">
            {{ $purchaseRequests->links() }}
        </div>
    @endif

</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden relative z-10 border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl">
                <h3 class="text-lg font-bold text-gray-800">Purchase Request Details</h3>
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
                <h3 class="text-xl font-bold text-gray-900 mb-2">Reject Purchase Request</h3>
                <p class="text-gray-600" id="rejectReasonTitle">Please provide a reason for rejection</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reject Reason <span class="text-red-500">*</span></label>
                <select id="rejectReasonSelect" class="block w-full py-3 px-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
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
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Comments</label>
                <textarea id="rejectComments" rows="3" class="block w-full py-3 px-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Optional additional details..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeRejectReasonModal()" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium transition-colors">
                    Reject Request
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
let confirmCallback = null;
let rejectContext = null;

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    setupSearch();
});

function setupFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const highValueFilter = document.getElementById('highValueFilter');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    
    if (priorityFilter) {
        priorityFilter.addEventListener('change', applyFilters);
    }
    
    if (highValueFilter) {
        highValueFilter.addEventListener('change', applyFilters);
    }
}

function setupSearch() {
    const searchInput = document.getElementById('searchInput');
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
    const priority = document.getElementById('priorityFilter').value;
    const highValue = document.getElementById('highValueFilter').checked;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status && status !== '') params.append('status', status);
    if (priority && priority !== '') params.append('priority', priority);
    if (highValue) params.append('high_value', '1');
    
    const url = params.toString() ? `?${params.toString()}` : '';
    window.location.href = url;
}

function refreshData() {
    window.location.reload();
}

function clearFilters() {
    window.location.href = window.location.pathname;
}

function closeRejectReasonModal() {
    document.getElementById('rejectReasonModal').classList.add('hidden');
    rejectContext = null;
}

function confirmReject() {
    if (!rejectContext) return;
    
    const reason = document.getElementById('rejectReasonSelect').value;
    const comments = document.getElementById('rejectComments').value.trim();
    
    if (!reason) {
        showToast('Validation Required', 'Please select a reject reason', 'error');
        return;
    }
    
    const fullReason = comments ? `${reason} - ${comments}` : reason;
    performApproval(rejectContext.id, 'reject', fullReason);
    closeRejectReasonModal();
}

function approvePR(prId, prNumber) {
    openConfirmModal(
        'Approve Purchase Request',
        `Are you sure you want to approve purchase request ${prNumber}?`,
        () => performApproval(prId, 'approve')
    );
}

function rejectPR(prId, prNumber) {
    rejectContext = { id: prId, number: prNumber };
    document.getElementById('rejectReasonTitle').textContent = `Reject Purchase Request ${prNumber}`;
    document.getElementById('rejectReasonSelect').value = '';
    document.getElementById('rejectComments').value = '';
    document.getElementById('rejectReasonModal').classList.remove('hidden');
}

function performApproval(prId, action, rejectReason = null) {
    const buttonText = action === 'approve' ? 'Approving...' : 'Rejecting...';
    showLoadingState(buttonText);
    
    const url = action === 'approve' 
        ? `/supervisor/purchase-requests/${prId}/approve`
        : `/supervisor/purchase-requests/${prId}/reject`;
    
    const body = {};
    if (action === 'reject' && rejectReason) {
        body.reject_reason = rejectReason;
    }
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: Object.keys(body).length > 0 ? JSON.stringify(body) : undefined
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message || `Purchase request ${action}d successfully`);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('Error', data.error || `Failed to ${action} purchase request`, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', `Failed to ${action} purchase request`, 'error');
    })
    .finally(() => {
        hideLoadingState();
    });
}

function viewPRDetails(prId) {
    const modal = document.getElementById('detailsModalBackdrop');
    const content = document.getElementById('detailsContent');
    
    // Show loading state
    content.innerHTML = `
        <div class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-chocolate mx-auto mb-4"></div>
            <p class="text-gray-500">Loading purchase request details...</p>
        </div>
    `;
    
    openModal('details');
    
    fetch(`/supervisor/purchase-requests/${prId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPRDetails(data.data);
            } else {
                content.innerHTML = `
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Error Loading Details</h3>
                        <p class="text-gray-500">${data.error || 'Failed to load purchase request details.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Error</h3>
                    <p class="text-gray-500">Failed to load purchase request details.</p>
                </div>
            `;
        });
}

function renderPRDetails(data) {
    const content = document.getElementById('detailsContent');
    
    let itemsHtml = '';
    data.items.forEach(item => {
        itemsHtml += `
            <tr>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-900">${item.item_name}</div>
                    <div class="text-sm text-gray-500 font-mono">${item.item_code}</div>
                </td>
                <td class="px-4 py-3 text-right text-gray-600">${item.quantity_requested} ${item.unit_symbol}</td>
                <td class="px-4 py-3 text-right text-gray-600">₱${parseFloat(item.unit_price_estimate).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900">₱${parseFloat(item.total_estimated_cost).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
    });
    
    content.innerHTML = `
        <div class="space-y-6 p-6">
            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                <div class="space-y-1">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Reference Number</span>
                    <div class="font-mono font-bold text-gray-900 text-lg">${data.pr_number}</div>
                </div>
                <div class="space-y-1 text-right">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Total Cost</span>
                    <div class="font-bold text-chocolate text-xl">${data.formatted_total}</div>
                </div>
                <div class="col-span-2 border-t border-gray-200 my-1"></div>
                <div><span class="text-gray-400 text-xs block">Requested By</span><span class="font-medium text-gray-800">${data.requested_by}</span></div>
                <div><span class="text-gray-400 text-xs block">Department</span><span class="font-medium text-gray-800">${data.department}</span></div>
                <div><span class="text-gray-400 text-xs block">Request Date</span><span class="font-medium text-gray-800">${new Date(data.request_date).toLocaleDateString()}</span></div>
                <div><span class="text-gray-400 text-xs block">Priority</span><span class="font-medium uppercase text-gray-800">${data.priority}</span></div>
            </div>
            
            <!-- Items Table -->
            <div>
                <h4 class="font-bold text-gray-800 mb-3 text-sm">Items Requested (${data.total_items} items)</h4>
                <div class="border border-gray-100 rounded-xl overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-500 font-bold text-xs uppercase">
                            <tr><th class="p-4">Item</th><th class="p-4 text-right">Quantity</th><th class="p-4 text-right">Unit Price</th><th class="p-4 text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            ${itemsHtml}
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="p-4 text-right text-sm font-bold text-gray-700">Total Estimate:</td>
                                <td class="p-4 text-right font-bold text-chocolate text-base">${data.formatted_total}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            ${data.notes ? `
                <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                    <span class="text-yellow-700 text-xs font-bold uppercase block mb-1">Notes</span>
                    <p class="text-yellow-900 text-sm">${data.notes}</p>
                </div>
            ` : ''}
            
            <div class="text-xs text-gray-400 text-center">
                Request created ${data.time_ago} • Last updated ${new Date(data.updated_at || data.created_at).toLocaleDateString()}
            </div>
        </div>
    `;
}

function showAllItems(prId) {
    viewPRDetails(prId);
}

// Modal functions
function openModal(name) {
    const modal = document.getElementById(name + 'ModalBackdrop');
    const panel = document.getElementById(name + 'ModalPanel');
    if (modal && panel) {
        modal.classList.remove('hidden');
        // Simple fade-in effect without complex transitions
        modal.style.opacity = '0';
        requestAnimationFrame(() => {
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '1';
        });
    }
}

function closeModal(name) {
    const modal = document.getElementById(name + 'ModalBackdrop');
    if (modal) {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.opacity = '';
            modal.style.transition = '';
        }, 300);
    }
}

function closeDetailsModal() {
    closeModal('details');
}

// Confirmation modal functions
function openConfirmModal(title, message, callback, icon = 'question', iconClass = 'bg-blue-50 text-blue-600') {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmIcon').className = `fas fa-${icon} text-2xl`;
    document.getElementById('confirmIconContainer').className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 ${iconClass}`;
    confirmCallback = callback;
    
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('hidden');
    // Simple fade-in effect
    modal.style.opacity = '0';
    requestAnimationFrame(() => {
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '1';
    });
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.style.transition = 'opacity 0.3s ease';
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.style.opacity = '';
        modal.style.transition = '';
        confirmCallback = null;
    }, 300);
}

document.getElementById('confirmBtn').onclick = function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
};

document.getElementById('confirmRejectBtn').onclick = confirmReject;

// Toast notification functions
function showToast(title, message, type = 'success') {
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
}

function showLoadingState(text = 'Processing...') {
    // You can implement a global loading state here
    console.log(text);
}

function hideLoadingState() {
    // Hide global loading state
    console.log('Processing complete');
}
</script>
@endsection