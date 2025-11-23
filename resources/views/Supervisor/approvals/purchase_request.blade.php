@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & SUMMARY --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Request Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review and approve procurement plans from the Purchasing Officer.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                {{ $statistics['pending_count'] ?? 0 }} Pending
            </div>
            <div class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">
                ₱ {{ number_format($statistics['total_value'] ?? 0, 2) }} Total Value
            </div>
            @if(($statistics['overdue_count'] ?? 0) > 0)
                <div class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                    {{ $statistics['overdue_count'] }} Overdue
                </div>
            @endif
        </div>
    </div>

    {{-- 2. FILTERS & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search PR number, requester, department..." value="{{ request('search') }}">
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="statusFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="pending" {{ (!request('status') || request('status') == 'pending') ? 'selected' : '' }}>Status: Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Status: Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Status: Rejected</option>
            </select>

            <select id="priorityFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Priorities</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
            </select>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" id="highValueFilter" {{ request('high_value') ? 'checked' : '' }} class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                <span class="text-gray-700">High Value</span>
            </label>

            <button onclick="refreshData()" class="px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
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
                @endphp

                {{-- PR CARD --}}
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow {{ $isHighValue ? 'border-l-4 border-l-red-500 relative' : '' }}">
                    
                    @if($isHighValue)
                        <!-- High Value Badge -->
                        <div class="absolute top-0 right-0 bg-red-100 text-red-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg border-b border-l border-red-200 z-10">
                            <i class="fas fa-money-bill-wave mr-1"></i> HIGH VALUE
                        </div>
                    @endif

                    @if($isUrgent)
                        <!-- Urgent Badge -->
                        <div class="absolute top-0 {{ $isHighValue ? 'left-0' : 'right-0' }} bg-orange-100 text-orange-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg border-b border-r border-orange-200 z-10">
                            <i class="fas fa-exclamation-triangle mr-1"></i> URGENT
                        </div>
                    @endif

                    @if($isOverdue)
                        <!-- Overdue Badge -->
                        <div class="absolute top-0 {{ $isHighValue || $isUrgent ? 'left-0' : 'right-0' }} bg-purple-100 text-purple-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg border-b border-r border-purple-200 z-10">
                            <i class="fas fa-clock mr-1"></i> OVERDUE
                        </div>
                    @endif

                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Left: Header Info -->
                            <div class="lg:w-1/4 space-y-4 border-r border-gray-100 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full {{ $isHighValue ? 'bg-red-100 text-red-600 border-red-200' : ($isUrgent ? 'bg-orange-100 text-orange-600 border-orange-200' : 'bg-blue-100 text-blue-600 border-blue-200') }} flex items-center justify-center font-bold border">
                                        PR
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $pr->pr_number }}</h3>
                                        <p class="text-xs text-gray-500">{{ $requesterName }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-600">
                                        <span class="block text-[10px] uppercase text-gray-400 font-bold">Department</span>
                                        <span class="font-medium"><i class="fas fa-building text-gray-400 mr-1"></i> {{ $pr->department ?? 'General' }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span class="block text-[10px] uppercase text-gray-400 font-bold">Priority</span>
                                        <span class="font-medium 
                                            @if($pr->priority === 'urgent') text-red-600
                                            @elseif($pr->priority === 'high') text-orange-600  
                                            @elseif($pr->priority === 'low') text-gray-600
                                            @else text-blue-600 @endif">
                                            <i class="fas fa-flag text-gray-400 mr-1"></i> {{ ucfirst($pr->priority) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span class="block text-[10px] uppercase text-gray-400 font-bold">Request Date</span>
                                        <span class="font-medium">{{ $pr->request_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span class="block text-[10px] uppercase text-gray-400 font-bold">Time Created</span>
                                        <span class="font-medium">{{ $pr->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Items Table -->
                            <div class="lg:w-2/4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Items Requested ({{ $pr->purchaseRequestItems->count() }} items)</h4>
                                <div class="overflow-hidden border border-gray-100 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-100">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($pr->purchaseRequestItems->take(3) as $item)
                                                <tr>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                                        <div class="max-w-[200px] truncate" title="{{ $item->item->name ?? 'Unknown Item' }}">
                                                            {{ $item->item->name ?? 'Unknown Item' }}
                                                        </div>
                                                        <div class="text-xs text-gray-400 font-mono">{{ $item->item->item_code ?? '' }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-sm text-gray-600">
                                                        {{ number_format($item->quantity_requested, 1) }}
                                                        <span class="text-xs text-gray-400">{{ $item->item->unit->symbol ?? '' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-sm text-gray-500">₱ {{ number_format($item->unit_price_estimate, 2) }}</td>
                                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">₱ {{ number_format($item->total_estimated_cost, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        @if($pr->purchaseRequestItems->count() > 3)
                                            <tfoot class="border-t border-gray-200">
                                                <tr>
                                                    <td colspan="4" class="px-3 py-2 text-center text-xs text-gray-500">
                                                        <button onclick="showAllItems({{ $pr->id }})" class="text-chocolate hover:underline">
                                                            <i class="fas fa-eye mr-1"></i> Show {{ $pr->purchaseRequestItems->count() - 3 }} more items
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                        <tfoot class="border-t border-gray-200 bg-gray-50/50">
                                            <tr>
                                                <td colspan="3" class="px-3 py-3 text-right text-sm font-bold text-gray-700">Total Estimate:</td>
                                                <td class="px-3 py-3 text-right text-base font-bold text-chocolate">₱ {{ number_format($pr->total_estimated_cost, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if($pr->notes)
                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                        <p class="text-xs text-blue-700"><i class="fas fa-sticky-note mr-1"></i> <strong>Notes:</strong> {{ $pr->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Actions -->
                            <div class="lg:w-1/4 flex flex-col justify-center space-y-3 border-l border-gray-100 pl-4">
                                <div class="text-center mb-2">
                                    <p class="text-xs text-gray-400 uppercase">Action Required</p>
                                </div>
                                
                                @if($pr->status === 'pending')
                                    <button onclick="approvePR({{ $pr->id }}, '{{ $pr->pr_number }}')" class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium text-sm flex items-center justify-center">
                                        <i class="fas fa-check-circle mr-2"></i> Approve Request
                                    </button>
                                    <button onclick="viewPRDetails({{ $pr->id }})" class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm flex items-center justify-center">
                                        <i class="fas fa-eye mr-2"></i> View Details
                                    </button>
                                    <button onclick="rejectPR({{ $pr->id }}, '{{ $pr->pr_number }}')" class="w-full py-2.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm font-medium text-sm flex items-center justify-center">
                                        <i class="fas fa-times-circle mr-2"></i> Reject
                                    </button>
                                @else
                                    <div class="text-center">
                                        @php
                                            $statusClass = match($pr->status) {
                                                'approved' => 'bg-green-100 text-green-700 border-green-200',
                                                'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                                'converted' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                                            };
                                            $statusIcon = match($pr->status) {
                                                'approved' => 'fa-check-circle',
                                                'rejected' => 'fa-times-circle',
                                                'converted' => 'fa-share',
                                                default => 'fa-circle'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                            <i class="fas {{ $statusIcon }} text-[10px]"></i> {{ ucfirst($pr->status) }}
                                        </span>
                                        @if($pr->approved_at)
                                            <p class="text-xs text-gray-500 mt-1">{{ $pr->approved_at->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
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
                <button onclick="clearFilters()" class="mt-4 text-chocolate font-medium hover:underline">Clear all filters</button>
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
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-gray-900/40" onclick="closeDetailsModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden relative z-10">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Purchase Request Details</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div id="detailsContent" class="p-0 overflow-y-auto max-h-[70vh]"></div>
        </div>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmModal" class="fixed inset-0 z-[80] hidden">
    <div class="absolute inset-0 bg-gray-900/50"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all scale-100 border border-white/20 relative z-10">
            <div id="confirmIconContainer" class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-5 text-chocolate shadow-inner">
                <i id="confirmIcon" class="fas fa-question text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="confirmTitle">Confirm</h3>
            <p class="text-gray-500 mb-8 leading-relaxed" id="confirmMessage">Are you sure you want to proceed?</p>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="closeConfirmModal()" class="px-4 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-bold text-sm transition-colors">Cancel</button>
                <button id="confirmBtn" class="px-4 py-3 bg-gray-900 text-white rounded-xl hover:bg-chocolate hover:shadow-lg font-bold text-sm transition-all shadow-md">Yes, Confirm</button>
            </div>
        </div>
    </div>
</div>

{{-- TOAST NOTIFICATION --}}
<div id="toast" class="fixed top-5 right-5 z-[90] hidden transform transition-all duration-300 translate-y-[-20px] opacity-0">
    <div class="bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-2xl p-4 flex items-center gap-4 min-w-[320px]">
        <div id="toastIconContainer" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
            <i id="toastIcon" class="fas fa-check"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-gray-900" id="toastTitle">Notification</h4>
            <p class="text-xs text-gray-500 mt-0.5" id="toastMessage">Message details...</p>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;

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

function approvePR(prId, prNumber) {
    openConfirmModal(
        'Approve Purchase Request',
        `Are you sure you want to approve purchase request ${prNumber}?`,
        () => performApproval(prId, 'approve')
    );
}

function rejectPR(prId, prNumber) {
    openConfirmModal(
        'Reject Purchase Request',
        `Are you sure you want to reject purchase request ${prNumber}?`,
        () => performApproval(prId, 'reject')
    );
}

function performApproval(prId, action) {
    const buttonText = action === 'approve' ? 'Approving...' : 'Rejecting...';
    showLoadingState(buttonText);
    
    const url = action === 'approve' 
        ? `/supervisor/purchase-requests/${prId}/approve`
        : `/supervisor/purchase-requests/${prId}/reject`;
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
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
function openConfirmModal(title, message, callback, icon = 'question', iconClass = 'bg-orange-50 text-chocolate') {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmIcon').className = `fas fa-${icon} text-2xl`;
    document.getElementById('confirmIconContainer').className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner ${iconClass}`;
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

// Toast notification functions
function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const container = document.getElementById('toastIconContainer');
    const icon = document.getElementById('toastIcon');
    
    document.getElementById('toastTitle').textContent = title;
    document.getElementById('toastMessage').textContent = message;

    if(type === 'error') {
        container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-100 text-red-500';
        icon.className = 'fas fa-times';
    } else {
        container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-green-100 text-green-500';
        icon.className = 'fas fa-check';
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