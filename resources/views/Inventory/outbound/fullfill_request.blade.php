@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6 font-sans text-gray-600">

    {{-- 1. HEADER (Simplified) --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Fulfill Requests</h1>
            <p class="text-sm text-gray-500">Pick and issue stock for approved requisitions.</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-lg text-xs font-bold">
                {{ $requisitions->where('status', 'approved')->count() }} Ready
            </span>
            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs font-bold">
                {{ $requisitions->where('status', 'pending')->count() }} Pending
            </span>
        </div>
    </div>

    {{-- 2. THE QUEUE (Clean Table) --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        {{-- Data Store for JS --}}
        <script type="application/json" id="requisitionData">
            {!! json_encode($requisitions->map(function($req) {
                return [
                    'id' => $req->id,
                    'number' => $req->requisition_number,
                    'requester' => $req->requestedBy->name ?? 'Unknown',
                    'department' => $req->department ?? 'General',
                    'items' => $req->requisitionItems->map(function($item) {
                        // Get batches ordered by FEFO (First Expire First Out)
                        $batches = \App\Models\Batch::where('item_id', $item->item_id)
                            ->where('status', 'active')
                            ->where('quantity', '>', 0)
                            ->orderBy('expiry_date', 'asc')
                            ->get()
                            ->map(function($b) {
                                return [
                                    'id' => $b->id,
                                    'number' => $b->batch_number,
                                    'qty' => $b->quantity,
                                    'expiry' => $b->expiry_date,
                                    'location' => $b->location ?? 'Main Stock'
                                ];
                            });
                        
                        return [
                            'id' => $item->id,
                            'name' => $item->item->name,
                            'requested' => $item->quantity_requested,
                            'unit' => $item->item->unit->symbol ?? 'pcs',
                            'batches' => $batches
                        ];
                    })
                ];
            })) !!}
        </script>

        <table class="min-w-full divide-y divide-border-soft">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Requisition</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($requisitions as $req)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-chocolate font-bold">#{{ $req->requisition_number }}</span>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $req->request_date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $req->requestedBy->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $req->department }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $req->requisitionItems->count() }} items</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($req->status === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">Ready to Pick</span>
                            @elseif($req->status === 'fulfilled')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase">Fulfilled</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase">{{ ucfirst($req->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($req->status === 'approved')
                                <button onclick="openPickList({{ $req->id }})" 
                                        class="px-4 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark shadow-sm transition-all">
                                    Fulfill Now
                                </button>
                            @else
                                <button onclick="viewRequisitionDetails({{ $req->id }})" 
                                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-200 transition-all border border-gray-300">
                                    View
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 3. THE "DIGITAL PICK TICKET" MODAL --}}
<div id="pickModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl border border-border-soft flex flex-col max-h-[90vh]">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">Digital Pick Ticket</h3>
                <p class="text-xs text-gray-500" id="modalSubtitle">Review auto-allocated items</p>
            </div>
            <button onclick="closePickModal()" class="text-gray-400 hover:text-chocolate"><i class="fas fa-times text-xl"></i></button>
        </div>

        {{-- Content: The Pick List --}}
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1" id="pickListContent">
            </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-between items-center">
            <div class="text-xs text-gray-500">
                * Items are auto-allocated by First-Expiring-First-Out (FEFO)
            </div>
            <div class="flex gap-3">
                <button onclick="closePickModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-600 font-bold rounded-lg hover:bg-gray-100 text-sm">Cancel</button>
                <button onclick="confirmFulfillment()" id="confirmIssueBtn" class="px-6 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-md transition-all text-sm flex items-center gap-2">
                    <i class="fas fa-check"></i> Confirm & Issue
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. CONFIRMATION MODAL --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4" onclick="handleBackdropClick(event, 'confirmModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-border-soft modal-enter" onclick="event.stopPropagation()">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-amber-50 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-amber-600"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-amber-800">Confirm Fulfillment</h3>
                <p class="text-xs text-amber-600">Please review and confirm the details</p>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6" id="confirmModalContent">
            {{-- Dynamic content will be inserted here --}}
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end gap-3">
            <button onclick="closeConfirmModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-600 font-bold rounded-lg hover:bg-gray-100 text-sm">
                Cancel
            </button>
            <button onclick="confirmAction()" id="confirmModalBtn" class="px-6 py-2 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 shadow-md transition-all text-sm flex items-center gap-2">
                <i class="fas fa-check"></i> Confirm
            </button>
        </div>
    </div>
</div>

{{-- 5. SUCCESS MODAL --}}
<div id="successModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4" onclick="handleBackdropClick(event, 'successModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-border-soft modal-enter" onclick="event.stopPropagation()">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-green-50 flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-green-800">Success!</h3>
                <p class="text-xs text-green-600">Operation completed successfully</p>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6" id="successModalContent">
            {{-- Dynamic content will be inserted here --}}
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end">
            <button onclick="closeSuccessModal()" class="px-6 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-md transition-all text-sm flex items-center gap-2">
                <i class="fas fa-check"></i> OK
            </button>
        </div>
    </div>
</div>

{{-- 6. ERROR MODAL --}}
<div id="errorModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4" onclick="handleBackdropClick(event, 'errorModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-border-soft modal-enter" onclick="event.stopPropagation()">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-red-50 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-red-600"></i>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-red-800">Error</h3>
                <p class="text-xs text-red-600">An error occurred during the operation</p>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6" id="errorModalContent">
            {{-- Dynamic content will be inserted here --}}
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end">
            <button onclick="closeErrorModal()" class="px-6 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 shadow-md transition-all text-sm flex items-center gap-2">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

{{-- 7. REQUISITION DETAILS MODAL --}}
<div id="requisitionDetailsModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4" onclick="handleBackdropClick(event, 'requisitionDetailsModal')">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl border border-border-soft modal-enter flex flex-col max-h-[90vh]" onclick="event.stopPropagation()">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-chocolate flex justify-between items-center">
            <div>
                <h3 class="font-display text-lg font-bold text-white">Requisition Details</h3>
                <p class="text-xs text-white/70" id="detailModalSubtitle">View requisition information</p>
            </div>
            <button onclick="closeRequisitionDetailsModal()" class="text-white/70 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        </div>

        {{-- Content: Details --}}
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1" id="requisitionDetailsContent">
            {{-- Loading state --}}
            <div id="detailsLoading" class="flex flex-col items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-border-soft border-t-chocolate"></div>
                <span class="mt-3 text-sm font-bold text-chocolate">Loading details...</span>
            </div>
            
            {{-- Details content --}}
            <div id="detailsContent" class="hidden space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                        <p class="text-[10px] font-bold text-chocolate uppercase tracking-widest">Requisition ID</p>
                        <p class="text-sm font-mono font-bold text-chocolate mt-0.5" id="detailRequisitionNumber">-</p>
                    </div>
                    <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                        <p class="text-[10px] font-bold text-chocolate uppercase tracking-widest">Status</p>
                        <div class="mt-0.5" id="detailStatus">-</div>
                    </div>
                    <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                        <p class="text-[10px] font-bold text-chocolate uppercase tracking-widest">Date Submitted</p>
                        <p class="text-sm font-medium text-gray-700 mt-0.5" id="detailDate">-</p>
                    </div>
                    <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                        <p class="text-[10px] font-bold text-chocolate uppercase tracking-widest">Department</p>
                        <p class="text-sm font-medium text-gray-700 mt-0.5" id="detailDepartment">-</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-chocolate uppercase tracking-wide mb-2 flex items-center">
                        <i class="fas fa-user mr-1.5 text-chocolate"></i> Requested By
                    </p>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 text-sm text-gray-700" id="detailRequester">
                        -
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold text-chocolate uppercase tracking-wide mb-3 flex items-center">
                        <i class="fas fa-list mr-1.5 text-chocolate"></i> Items Requested
                    </p>
                    <div class="border border-border-soft rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-border-soft">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100" id="detailItemsTable">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex justify-end">
            <button onclick="closeRequisitionDetailsModal()" class="px-6 py-2 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700 shadow-md transition-all text-sm flex items-center gap-2">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
    let currentReqId = null;
    let pendingConfirmAction = null;

    function validateBatchInput(input, maxQty) {
        const value = parseFloat(input.value) || 0;
        const min = parseFloat(input.min) || 0;
        const max = parseFloat(input.max) || maxQty;
        
        // Ensure value is within bounds
        if (value < min) {
            input.value = min;
        } else if (value > max) {
            input.value = max;
        }
        
        // Add visual feedback for validation
        if (value > max) {
            input.classList.add('border-red-300', 'bg-red-50');
            setTimeout(() => {
                input.classList.remove('border-red-300', 'bg-red-50');
                input.classList.add('border-gray-300');
            }, 2000);
        } else {
            input.classList.remove('border-red-300', 'bg-red-50');
            input.classList.add('border-gray-300');
        }
    }

    function openPickList(id) {
        currentReqId = id;
        const data = JSON.parse(document.getElementById('requisitionData').textContent);
        const req = data.find(r => r.id === id);
        
        if(!req) return;

        document.getElementById('modalSubtitle').textContent = `Fulfilling Requisition #${req.number} for ${req.requester}`;
        const container = document.getElementById('pickListContent');
        
        let html = '<div class="space-y-4">';
        
        req.items.forEach(item => {
            // --- AUTO-ALLOCATION LOGIC (The "Smart" Part) ---
            let remainingNeeded = item.requested;
            let allocationHtml = '';
            let isShortage = false;

            if (item.batches.length === 0) {
                allocationHtml = `<div class="text-red-500 text-xs font-bold bg-red-50 p-2 rounded border border-red-100"><i class="fas fa-times-circle mr-1"></i> Out of Stock</div>`;
                isShortage = true;
            } else {
                item.batches.forEach(batch => {
                    if (remainingNeeded <= 0) return; // Filled

                    let take = Math.min(remainingNeeded, batch.qty);
                    remainingNeeded -= take;

                    // The "Simple" Row
                    allocationHtml += `
                        <div class="flex justify-between items-center text-sm p-2 bg-blue-50/50 rounded border border-blue-100 mb-1">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-map-marker-alt text-blue-400"></i>
                                <div>
                                    <span class="font-mono font-bold text-blue-800">${batch.number}</span>
                                    <span class="text-xs text-gray-500 ml-2">Loc: ${batch.location}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" 
                                       name="items[${item.id}][batches][${batch.id}]" 
                                       value="${take}" 
                                       min="0" 
                                       max="${batch.qty}" 
                                       class="w-20 text-right border-gray-300 rounded text-sm"
                                       onchange="validateBatchInput(this, ${batch.qty})">
                                <span class="font-bold text-chocolate text-sm">${item.unit}</span>
                            </div>
                        </div>
                    `;
                });
            }

            if (remainingNeeded > 0 && !isShortage) {
                allocationHtml += `<div class="text-orange-600 text-xs font-bold mt-1"><i class="fas fa-exclamation-triangle"></i> Insufficient Stock (Missing ${remainingNeeded})</div>`;
            }

            // Render Item Block
            html += `
                <div class="bg-white border border-border-soft rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between mb-2">
                        <h4 class="font-bold text-gray-800">${item.name}</h4>
                        <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded">Req: ${item.requested} ${item.unit}</span>
                    </div>
                    <div class="pl-2 border-l-2 border-blue-200">
                        ${allocationHtml}
                    </div>
                    <input type="hidden" name="items[${item.id}][requested]" value="${item.requested}">
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
        
        document.getElementById('pickModal').classList.remove('hidden');
    }

    function closePickModal() {
        document.getElementById('pickModal').classList.add('hidden');
    }

    // Modal control functions
    function showConfirmModal(message, onConfirm) {
        const modal = document.getElementById('confirmModal');
        const content = document.getElementById('confirmModalContent');
        const btn = document.getElementById('confirmModalBtn');
        
        content.innerHTML = `<div class="text-gray-700 whitespace-pre-line">${message}</div>`;
        pendingConfirmAction = onConfirm;
        modal.classList.remove('hidden');
        
        // Focus management and keyboard support
        btn.focus();
        document.addEventListener('keydown', handleModalKeydown);
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        pendingConfirmAction = null;
        document.removeEventListener('keydown', handleModalKeydown);
    }

    function confirmAction() {
        if (pendingConfirmAction) {
            pendingConfirmAction();
            closeConfirmModal();
        }
    }

    function showSuccessModal(title, message) {
        const modal = document.getElementById('successModal');
        const content = document.getElementById('successModalContent');
        
        content.innerHTML = `
            <div class="text-center">
                <div class="text-gray-700 whitespace-pre-line">${message}</div>
            </div>
        `;
        modal.classList.remove('hidden');
        
        // Auto close after 3 seconds for success messages
        setTimeout(() => {
            closeSuccessModal();
            window.location.reload();
        }, 3000);
        
        document.addEventListener('keydown', handleModalKeydown);
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
        document.removeEventListener('keydown', handleModalKeydown);
    }

    function showErrorModal(title, message) {
        const modal = document.getElementById('errorModal');
        const content = document.getElementById('errorModalContent');
        
        content.innerHTML = `
            <div class="text-center">
                <div class="text-gray-700 whitespace-pre-line">${message}</div>
            </div>
        `;
        modal.classList.remove('hidden');
        document.addEventListener('keydown', handleModalKeydown);
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.add('hidden');
        document.removeEventListener('keydown', handleModalKeydown);
    }

    // Global keyboard handler for all modals
    function handleModalKeydown(event) {
        if (event.key === 'Escape') {
            // Close any open modal
            if (!document.getElementById('confirmModal').classList.contains('hidden')) {
                closeConfirmModal();
            } else if (!document.getElementById('successModal').classList.contains('hidden')) {
                closeSuccessModal();
            } else if (!document.getElementById('errorModal').classList.contains('hidden')) {
                closeErrorModal();
            }
        } else if (event.key === 'Enter') {
            // Handle Enter key for confirm modal
            if (!document.getElementById('confirmModal').classList.contains('hidden')) {
                event.preventDefault();
                confirmAction();
            }
        }
    }

    // Handle click outside modal to close
    function handleBackdropClick(event, modalId) {
        if (event.target.id === modalId) {
            if (modalId === 'confirmModal') {
                closeConfirmModal();
            } else if (modalId === 'successModal') {
                closeSuccessModal();
            } else if (modalId === 'errorModal') {
                closeErrorModal();
            } else if (modalId === 'requisitionDetailsModal') {
                closeRequisitionDetailsModal();
            }
        }
    }

    // Requisition Details Functions
    function viewRequisitionDetails(requisitionId) {
        const modal = document.getElementById('requisitionDetailsModal');
        const loading = document.getElementById('detailsLoading');
        const content = document.getElementById('detailsContent');
        
        // Show modal and loading state
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        
        // Fetch requisition details via AJAX
        fetch(`/inventory/outbound/requisitions/${requisitionId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateRequisitionDetails(data.requisition);
                } else {
                    showErrorModal('Error', 'Failed to load requisition details.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Error', 'Error loading requisition details.');
            })
            .finally(() => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            });
    }

    function closeRequisitionDetailsModal() {
        document.getElementById('requisitionDetailsModal').classList.add('hidden');
    }

    function populateRequisitionDetails(requisition) {
        // Format status with badge
        const statusConfig = {
            'pending': { label: 'Pending', class: 'bg-amber-100 text-amber-800' },
            'approved': { label: 'Approved', class: 'bg-green-100 text-green-800' },
            'rejected': { label: 'Rejected', class: 'bg-red-100 text-red-800' },
            'fulfilled': { label: 'Fulfilled', class: 'bg-gray-100 text-gray-600' }
        };
        
        const status = statusConfig[requisition.status] || statusConfig['pending'];
        
        // Update subtitle
        document.getElementById('detailModalSubtitle').textContent = `Requisition #${requisition.requisition_number} for ${requisition.requestedBy?.name || 'Unknown'}`;
        
        // Populate header info
        document.getElementById('detailRequisitionNumber').textContent = `#${requisition.requisition_number}`;
        document.getElementById('detailStatus').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide ${status.class}">
            ${status.label}
        </span>`;
        
        const dateObj = new Date(requisition.request_date);
        document.getElementById('detailDate').textContent = dateObj.toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        
        document.getElementById('detailDepartment').textContent = requisition.department || 'N/A';
        document.getElementById('detailRequester').innerHTML = `
            <div class="font-bold text-gray-800">${requisition.requestedBy?.name || 'Unknown'}</div>
            <div class="text-xs text-gray-500 mt-1">${requisition.department || 'General Department'}</div>
        `;
        
        // Populate items table
        const itemsTable = document.getElementById('detailItemsTable');
        itemsTable.innerHTML = '';
        
        if (requisition.requisitionItems && requisition.requisitionItems.length > 0) {
            requisition.requisitionItems.forEach(item => {
                const row = document.createElement('tr');
                row.className = "hover:bg-gray-50 transition-colors";
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-800">${item.item?.name || 'Unknown Item'}</div>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">${item.item?.item_code || 'N/A'}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        <span class="font-bold">${parseFloat(item.quantity_requested)}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-600">
                        ${item.item?.unit?.symbol || 'pcs'}
                    </td>
                `;
                itemsTable.appendChild(row);
            });
        } else {
            itemsTable.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">No items found</td></tr>';
        }
    }

    async function confirmFulfillment() {
        try {
            // Show loading state
            const confirmBtn = document.getElementById('confirmIssueBtn');
            const originalBtnText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            confirmBtn.classList.add('opacity-75');
            confirmBtn.disabled = true;

            // Collect form data from hidden inputs
            const formData = new FormData();
            formData.append('requisition_id', currentReqId);

            // Parse the hidden inputs to collect multi-batch selections
            const hiddenInputs = document.querySelectorAll('input[name^="items["]');
            const multiBatchSelections = {};
            const processedItems = [];
            const shortages = [];
            const itemRequestedQty = {};

            hiddenInputs.forEach(input => {
                const match = input.name.match(/items\[(\d+)\]\[batches\]\[(\d+)\]/);
                if (match) {
                    const itemId = parseInt(match[1]);
                    const batchId = parseInt(match[2]);
                    const quantity = parseFloat(input.value);

                    if (!multiBatchSelections[itemId]) {
                        multiBatchSelections[itemId] = [];
                    }
                    multiBatchSelections[itemId].push({
                        batch_id: batchId,
                        quantity: quantity
                    });

                    // Track processed items
                    if (!processedItems.includes(itemId)) {
                        processedItems.push(itemId);
                    }

                    // Calculate requested quantities for shortage detection
                    const requestedElement = document.querySelector(`input[name="items[${itemId}][requested]"]`);
                    if (requestedElement) {
                        itemRequestedQty[itemId] = parseFloat(requestedElement.value);
                    }
                }
            });

            // Detect shortages and calculate total allocated per item
            for (const [itemId, batches] of Object.entries(multiBatchSelections)) {
                const totalAllocated = batches.reduce((sum, batch) => sum + batch.quantity, 0);
                const requestedQty = itemRequestedQty[itemId] || 0;

                if (totalAllocated < requestedQty) {
                    shortages.push({
                        itemId: parseInt(itemId),
                        requestedQty: requestedQty,
                        shortageQty: requestedQty - totalAllocated
                    });
                }
            }

            console.log('Shortage analysis:', shortages);

            // Validate that we have selections to process
            if (Object.keys(multiBatchSelections).length === 0) {
                throw new Error('No items selected for fulfillment. Please allocate stock from available batches.');
            }

            // Determine if this will be a partial fulfillment
            const isPartialFulfillment = shortages.length > 0;

            // Add processed data to form - send as individual batch entries
            for (const [itemId, batches] of Object.entries(multiBatchSelections)) {
                batches.forEach((batch, index) => {
                    formData.append(`multi_batch_selections[${itemId}][${index}][batch_id]`, batch.batch_id);
                    formData.append(`multi_batch_selections[${itemId}][${index}][quantity]`, batch.quantity);
                });
            }
            
            // Add processed data to form as individual fields for proper array parsing
            processedItems.forEach((itemId, index) => {
                formData.append(`processed_items[${index}]`, itemId);
            });

            shortages.forEach((shortage, index) => {
                formData.append(`shortages[${index}][itemId]`, shortage.itemId);
                formData.append(`shortages[${index}][requestedQty]`, shortage.requestedQty);
                formData.append(`shortages[${index}][shortageQty]`, shortage.shortageQty);
            });

            formData.append('partial_fulfillment', isPartialFulfillment ? 'true' : 'false');

            // Show confirmation modal
            const totalBatches = Object.values(multiBatchSelections).reduce((sum, batches) => sum + batches.length, 0);
            let dialogMessage = `Requisition: #${currentReqId}\n` +
                `Items to process: ${processedItems.length}\n` +
                `Batches to issue from: ${totalBatches}\n`;

            if (isPartialFulfillment) {
                dialogMessage += `\n⚠️ PARTIAL FULFILLMENT DETECTED\n` +
                    `Shortages: ${shortages.length} items\n` +
                    `Stock will be backordered for missing quantities.\n\n`;
            }

            dialogMessage += `This will update stock levels permanently. Continue?`;

            // Store the processing logic to be called after confirmation
            const processFulfillment = async () => {
                try {
                    console.log('Sending fulfillment request:', {
                        requisition_id: currentReqId,
                        multi_batch_selections: multiBatchSelections,
                        processed_items: processedItems,
                        shortages: shortages
                    });

                    // Send AJAX request to backend
                    const response = await fetch('/inventory/outbound/confirm-issuance', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to process fulfillment');
                    }

                    // Add visual success feedback
                    const modal = document.getElementById('pickModal');
                    modal.classList.add('opacity-75');
                    
                    // Show success modal
                    if (result.partial_fulfillment) {
                        showSuccessModal('Partial Fulfillment Complete', `✅ Partial fulfillment completed!\n\nIssued: ${result.will_issue} items\nBackordered: ${result.will_backorder} items\n\nStock has been updated and notifications sent.`);
                    } else {
                        showSuccessModal('Fulfillment Complete', `✅ Requisition fulfilled successfully!\n\nAll requested items have been issued and stock has been updated.`);
                    }

                    closePickModal();
                    
                    // Reload page to show updated status after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

                } catch (error) {
                    console.error('Fulfillment error:', error);
                    
                    // Show error modal
                    showErrorModal('Fulfillment Failed', `❌ Fulfillment failed: ${error.message}\n\nPlease check stock availability and try again.`);
                } finally {
                    // Reset button state
                    const confirmBtn = document.getElementById('confirmIssueBtn');
                    confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm & Issue';
                    confirmBtn.classList.remove('opacity-75');
                    confirmBtn.disabled = false;
                }
            };

            // Show custom confirmation modal
            showConfirmModal(dialogMessage, processFulfillment);
            return;

        } catch (error) {
            console.error('Fulfillment error:', error);
            
            // Reset button state
            const confirmBtn = document.getElementById('confirmIssueBtn');
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm & Issue';
            confirmBtn.classList.remove('opacity-75');
            confirmBtn.disabled = false;

            // Show error modal
            showErrorModal('Fulfillment Failed', `❌ Fulfillment failed: ${error.message}\n\nPlease check stock availability and try again.`);
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
    
    /* Modal animations */
    .modal-enter {
        animation: modalEnter 0.3s ease-out;
    }
    
    .modal-exit {
        animation: modalExit 0.3s ease-in;
    }
    
    @keyframes modalEnter {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes modalExit {
        from {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        to {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
    }
    
    /* Backdrop blur effect */
    .backdrop-blur {
        backdrop-filter: blur(4px);
    }
</style>
@endsection