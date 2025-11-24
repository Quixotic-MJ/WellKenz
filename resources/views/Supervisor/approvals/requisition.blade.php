@extends('Supervisor.layout.app')

@section('content')
<style>
    /* Custom Scrollbar for Modal */
    .modal-scroll::-webkit-scrollbar {
        width: 8px;
    }
    .modal-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .modal-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }
    .modal-scroll::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
    
    /* Animation Classes */
    .modal-backdrop {
        transition: opacity 0.3s ease-out;
        opacity: 0;
        pointer-events: none;
    }
    .modal-backdrop.active {
        opacity: 1;
        pointer-events: auto;
    }
    .modal-panel {
        transition: all 0.3s ease-out;
        transform: scale(0.95) translateY(10px);
        opacity: 0;
    }
    .modal-panel.active {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    
    /* Z-Index Hierarchies */
    .z-60 { z-index: 60; }
    .z-70 { z-index: 70; }
</style>

<div class="space-y-6">

    {{-- 1. HEADER & SUMMARY --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Requisition Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review stock requests from the production team.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                {{ $pendingCount ?? 0 }} Pending
            </div>
            <div class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                {{ $approvedToday ?? 0 }} Approved Today
            </div>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <form method="GET" class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search by requester or item...">
        </form>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form method="GET" class="flex items-center gap-3 w-full md:w-auto">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="status" onchange="this.form.submit()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Status: Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Status: Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Status: Rejected</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All History</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
            </form>
        </div>
    </div>

    {{-- 3. REQUISITIONS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Requested</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    @forelse($requisitions as $requisition)
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

                        <tr class="hover:bg-gray-50 transition-colors {{ $hasHighRequest && !$hasSufficientStock ? 'hover:bg-red-50 border-l-4 border-l-red-400' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">#{{ $requisition->requisition_number }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="far fa-clock mr-1"></i> {{ $requisition->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs border border-gray-300">
                                        {{ $initials }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $requisition->requestedBy->name ?? 'Unknown User' }}</div>
                                        <div class="text-xs text-gray-500">{{ $requisition->department ?? 'General' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($requisition->requisitionItems->count() > 0)
                                    <div class="text-sm text-gray-900 font-medium">{{ $mainItem->item->name ?? 'Unknown Item' }}</div>
                                    <div class="flex items-center gap-2 mt-1 text-xs">
                                        <span class="text-gray-500">Requested: 
                                            <span class="font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-chocolate' }}">
                                                {{ number_format($requestedQty, 1) }} {{ $mainItem->item->unit->symbol ?? '' }}
                                            </span>
                                        </span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-gray-500">Stock: {{ number_format($currentStock, 1) }} {{ $mainItem->item->unit->symbol ?? '' }}</span>
                                        @if($hasSufficientStock)
                                            <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                        @else
                                            <span class="text-red-600 font-bold" title="High Request"><i class="fas fa-exclamation-triangle"></i></span>
                                        @endif
                                    </div>
                                    @if($hasHighRequest && !$hasSufficientStock)
                                        <div class="text-[10px] text-red-500 font-medium mt-0.5">
                                            Consumes {{ $stockPercentage }}% of stock
                                        </div>
                                    @endif
                                    @if($requisition->requisitionItems->count() > 1)
                                        <div class="flex items-center justify-between mt-1">
                                            <div class="text-xs text-gray-400">
                                                +{{ $requisition->requisitionItems->count() - 1 }} more item(s)
                                            </div>
                                            @php
                                                $itemCount = $requisition->requisitionItems->count();
                                                $maxIndividualButtons = 5;
                                            @endphp
                                            @if($itemCount <= $maxIndividualButtons)
                                                <div class="text-xs text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded font-medium">
                                                    <i class="fas fa-edit mr-1"></i>Individual modify
                                                </div>
                                            @else
                                                <div class="text-xs text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded font-medium">
                                                    <i class="fas fa-list mr-1"></i>Bulk modify ({{ $itemCount }} items)
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">- No items -</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($requisition->notes)
                                    <div class="text-xs text-gray-500 italic bg-amber-50 border border-amber-100 p-2 rounded max-w-xs">
                                        "{{ Str::limit($requisition->notes, 100) }}"
                                    </div>
                                @elseif($requisition->purpose)
                                    <div class="text-xs text-gray-500 italic bg-blue-50 border border-blue-100 p-2 rounded max-w-xs">
                                        Purpose: {{ Str::limit($requisition->purpose, 80) }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">- No notes -</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button 
                                        onclick="RequisitionManager.approve({{ $requisition->id }})"
                                        class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 border border-green-200 px-3 py-1 rounded transition {{ !$hasSufficientStock ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        title="Approve {{ !$hasSufficientStock ? '(Insufficient Stock)' : '' }} | Stock: {{ $currentStock }} | Requested: {{ $requestedQty }}"
                                        {{ !$hasSufficientStock ? 'disabled' : '' }}
                                    >
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <!-- Item modification buttons - adaptive based on item count -->
                                    @php
                                        $itemCount = $requisition->requisitionItems->count();
                                        $maxIndividualButtons = 5; // Show individual buttons for up to 5 items
                                    @endphp
                                    
                                    @if($itemCount <= $maxIndividualButtons)
                                        <!-- Individual item modify buttons (for small number of items) -->
                                        @foreach($requisition->requisitionItems as $itemIndex => $item)
                                            @php
                                                $itemStockRecord = $item->currentStockRecord;
                                                $itemCurrentStock = $itemStockRecord ? $itemStockRecord->current_quantity : 0;
                                                $itemStockPercentage = $itemCurrentStock > 0 ? round(($item->quantity_requested / $itemCurrentStock) * 100, 1) : 0;
                                                $itemHasSufficientStock = $itemCurrentStock >= $item->quantity_requested && $itemCurrentStock > 0;
                                            @endphp
                                            <button 
                                                onclick="RequisitionManager.openModifyModal({{ $requisition->id }}, {{ $item->id }}, '{{ $item->item->name ?? 'Unknown Item' }}', {{ $item->quantity_requested }}, '{{ $item->item->unit->symbol ?? '' }}', {{ $itemCurrentStock }})" 
                                                class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2 py-1 rounded transition text-xs" 
                                                title="Modify: {{ $item->item->name ?? 'Unknown Item' }} ({{ $item->quantity_requested }} {{ $item->item->unit->symbol ?? '' }})"
                                            >
                                                <i class="fas fa-edit"></i> {{ $itemIndex + 1 }}
                                            </button>
                                        @endforeach
                                    @else
                                        <!-- Dropdown for many items -->
                                        <div class="relative inline-block">
                                            <button 
                                                onclick="RequisitionManager.openMultiItemModifyModal({{ $requisition->id }})" 
                                                class="text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1 rounded transition" 
                                                title="Modify Items ({{ $itemCount }} items)"
                                            >
                                                <i class="fas fa-edit"></i> {{ $itemCount }} Items
                                            </button>
                                        </div>
                                    @endif
                                    
                                    <button 
                                        onclick="RequisitionManager.viewDetails({{ $requisition->id }})"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1 rounded transition" 
                                        title="View Details"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button 
                                        onclick="RequisitionManager.reject({{ $requisition->id }})"
                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1 rounded transition" 
                                        title="Reject"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4"></i>
                                    <p class="text-lg font-medium">No requisitions found</p>
                                    <p class="text-sm">There are no requisitions matching your current filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($requisitions->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-700">
                        Showing 
                        <span class="font-medium">{{ $requisitions->firstItem() ?? 0 }}</span> 
                        to 
                        <span class="font-medium">{{ $requisitions->lastItem() ?? 0 }}</span> 
                        of 
                        <span class="font-medium">{{ $requisitions->total() }}</span> 
                        results
                    </p>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        {{ $requisitions->links('pagination::tailwind') }}
                    </nav>
                </div>
            </div>
        @endif
    </div>

</div>

<!-- MODIFY QUANTITY MODAL -->
<div id="modifyModalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto modal-backdrop" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="RequisitionManager.closeModifyModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div id="modifyModalPanel" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-panel">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-edit text-amber-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Modify Requisition</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modifyModalText">
                                Adjusting quantity for <strong>Item Name</strong> in request <strong>REQ-XXXX</strong>.
                            </p>
                        </div>

                        <form id="modifyForm">
                            @csrf
                            <input type="hidden" id="requisitionItemId" name="item_id">
                            
                            <div class="mt-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase">Requested Qty</label>
                                        <div class="mt-1 text-lg font-medium text-gray-900 line-through text-red-400" id="originalQtyDisplay">50 kg</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-chocolate uppercase">Approved Qty</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="number" id="newQtyInput" name="new_quantity" step="0.001" min="0.001" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-md border border-gray-300 focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-chocolate" value="50">
                                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm" id="unitDisplay">kg</span>
                                        </div>
                                        <div id="stockWarning" class="text-xs text-red-600 mt-1 hidden">
                                            Warning: Current stock is only <span id="currentStockDisplay">0</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason for Modification <span class="text-red-500">*</span></label>
                                    <select name="reason" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option value="">Select a reason</option>
                                        <option>Insufficient Stock</option>
                                        <option>Rationing (High Demand)</option>
                                        <option>Policy Limit Exceeded</option>
                                        <option>Quality Issues</option>
                                        <option>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                    <textarea name="remarks" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Reduced to conserve stock for weekend."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" onclick="RequisitionManager.submitModifyForm()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:w-auto sm:text-sm">
                    Confirm & Approve
                </button>
                <button type="button" onclick="RequisitionManager.closeModifyModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- REQUISITION DETAILS MODAL -->
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto modal-backdrop" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="RequisitionManager.closeDetailsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div id="detailsModalPanel" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-panel">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-file-invoice text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="detailsModalTitle">Requisition Details</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="detailsModalSubtitle">Loading requisition information...</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6" id="requisitionDetailsContent">
                    <!-- Details will be loaded here via JavaScript -->
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="RequisitionManager.closeDetailsModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmationModalBackdrop" class="fixed inset-0 z-70 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RequisitionManager.closeConfirm()"></div>
    
    <div id="confirmationModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col modal-panel overflow-hidden">
        <div class="p-6 text-center">
            <div id="confirmIconBg" class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <i id="confirmIcon" class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="confirmTitle">Confirmation</h3>
            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
            <button type="button" id="confirmBtn"
                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm">
                Confirm
            </button>
            <button type="button" onclick="RequisitionManager.closeConfirm()"
                    class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

{{-- NOTIFICATION MODAL --}}
<div id="notificationModalBackdrop" class="fixed inset-0 z-70 flex items-center justify-center px-4 sm:px-6 modal-backdrop" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="RequisitionManager.closeNotification()"></div>
    
    <div id="notificationModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col modal-panel overflow-hidden">
        <div class="p-6 text-center">
            <div id="notifIconBg" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-5">
                <i id="notifIcon" class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" id="notifTitle">Success</h3>
            <div class="text-sm text-gray-500 whitespace-pre-line" id="notifMessage">Operation successful.</div>
        </div>
        <div class="bg-gray-50 px-6 py-3">
            <button type="button" id="notifBtn" onclick="RequisitionManager.closeNotification()"
                    class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-900 focus:outline-none sm:text-sm">
                Okay, got it
            </button>
        </div>
    </div>
</div>

<script>
const RequisitionManager = {
    currentRequisitionId: null,
    
    // DOM Elements Cache
    elements: {
        modifyModal: {
            backdrop: document.getElementById('modifyModalBackdrop'),
            panel: document.getElementById('modifyModalPanel'),
            text: document.getElementById('modifyModalText'),
            origQty: document.getElementById('originalQtyDisplay'),
            newQty: document.getElementById('newQtyInput'),
            unit: document.getElementById('unitDisplay'),
            itemId: document.getElementById('requisitionItemId'),
            warning: document.getElementById('stockWarning'),
            stock: document.getElementById('currentStockDisplay'),
            form: document.getElementById('modifyForm')
        },
        detailsModal: {
            backdrop: document.getElementById('detailsModalBackdrop'),
            panel: document.getElementById('detailsModalPanel'),
            title: document.getElementById('detailsModalTitle'),
            subtitle: document.getElementById('detailsModalSubtitle'),
            content: document.getElementById('requisitionDetailsContent')
        },
        confirmation: {
            backdrop: document.getElementById('confirmationModalBackdrop'),
            panel: document.getElementById('confirmationModalPanel'),
            title: document.getElementById('confirmTitle'),
            message: document.getElementById('confirmMessage'),
            btn: document.getElementById('confirmBtn'),
            icon: document.getElementById('confirmIcon'),
            iconBg: document.getElementById('confirmIconBg')
        },
        notification: {
            backdrop: document.getElementById('notificationModalBackdrop'),
            panel: document.getElementById('notificationModalPanel'),
            title: document.getElementById('notifTitle'),
            message: document.getElementById('notifMessage'),
            btn: document.getElementById('notifBtn'),
            icon: document.getElementById('notifIcon'),
            iconBg: document.getElementById('notifIconBg')
        }
    },

    // ================= MODAL UTILITIES =================

    showNotification(type, title, message, callback = null) {
        const el = this.elements.notification;
        
        el.title.textContent = title;
        el.message.textContent = message;
        
        // Style based on type
        el.iconBg.className = 'mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-5 transition-colors';
        el.icon.className = 'text-2xl transition-colors fas';
        
        if (type === 'success') {
            el.iconBg.classList.add('bg-green-100');
            el.icon.classList.add('fa-check', 'text-green-600');
        } else if (type === 'error') {
            el.iconBg.classList.add('bg-red-100');
            el.icon.classList.add('fa-times', 'text-red-600');
        } else if (type === 'warning') {
            el.iconBg.classList.add('bg-amber-100');
            el.icon.classList.add('fa-exclamation', 'text-amber-600');
        }

        el.btn.onclick = () => {
            this.closeNotification();
            if (callback) callback();
        };

        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });
    },

    closeNotification() {
        const el = this.elements.notification;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
        }, 300);
    },

    showConfirm(title, message, type, onConfirm) {
        const el = this.elements.confirmation;
        
        el.title.textContent = title;
        el.message.textContent = message;
        
        // Style based on type (danger/info)
        el.iconBg.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6 transition-colors';
        el.icon.className = 'text-2xl transition-colors fas';
        
        if (type === 'danger') {
            el.iconBg.classList.add('bg-red-100');
            el.icon.classList.add('fa-exclamation-triangle', 'text-red-600');
            el.btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm';
        } else {
            el.iconBg.classList.add('bg-green-100');
            el.icon.classList.add('fa-check', 'text-green-600');
            el.btn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm';
        }

        // Reset button listeners
        const newBtn = el.btn.cloneNode(true);
        el.btn.parentNode.replaceChild(newBtn, el.btn);
        this.elements.confirmation.btn = newBtn;

        newBtn.addEventListener('click', () => {
            // Optional: Loading state on button
            newBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            newBtn.disabled = true;
            if (onConfirm) onConfirm();
            this.closeConfirm();
        });

        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });
    },

    closeConfirm() {
        const el = this.elements.confirmation;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
            // Reset button state
            el.btn.innerHTML = 'Confirm';
            el.btn.disabled = false;
        }, 300);
    },

    // ================= BUSINESS LOGIC =================

    approve(requisitionId) {
        this.showConfirm(
            'Approve Requisition?',
            'Are you sure you want to approve this requisition? Inventory will be deducted upon approval.',
            'success',
            () => this.performAction(requisitionId, 'approve')
        );
    },

    reject(requisitionId) {
        this.showConfirm(
            'Reject Requisition?',
            'Are you sure you want to reject this requisition? This action cannot be undone.',
            'danger',
            () => this.performAction(requisitionId, 'reject')
        );
    },

    performAction(id, action) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            this.showNotification('error', 'System Error', 'CSRF token not found. Please refresh.');
            return;
        }

        fetch(`/supervisor/requisitions/${id}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (body.success) {
                const msg = action === 'approve' ? 'Requisition approved successfully!' : 'Requisition rejected successfully!';
                this.showNotification('success', 'Completed', msg, () => location.reload());
            } else {
                this.showNotification('error', 'Error', body.error || 'An unknown error occurred.');
            }
        })
        .catch(error => {
            this.showNotification('error', 'Network Error', 'An error occurred while processing your request.');
        });
    },

    // ================= MODIFY LOGIC =================

    openMultiItemModifyModal(requisitionId) {
        // For requisitions with many items, show a modal with all items listed
        // This would typically fetch the requisition details via API
        const el = this.elements.detailsModal;
        
        // Loading State
        el.title.textContent = 'Modify Requisition Items';
        el.subtitle.textContent = 'Select items to modify...';
        el.content.innerHTML = '<div class="flex justify-center py-12"><i class="fas fa-circle-notch fa-spin text-4xl text-chocolate/50"></i></div>';
        
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });

        fetch(`/supervisor/requisitions/${requisitionId}/details`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                this.renderMultiItemModifyForm(data.data);
            } else {
                throw new Error(data.error || 'Invalid response');
            }
        })
        .catch(error => {
            el.content.innerHTML = `
                <div class="text-center py-8">
                    <div class="bg-red-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <p class="text-red-800 font-medium">Failed to load items</p>
                    <p class="text-sm text-red-600 mt-1">${error.message || 'Please check your connection.'}</p>
                </div>
            `;
        });
    },

    renderMultiItemModifyForm(data) {
        const el = this.elements.detailsModal;
        el.title.textContent = `Modify Items - Requisition #${data.requisition_number}`;
        el.subtitle.textContent = `Select individual items to modify (${data.total_items} items)`;

        const itemsFormHtml = data.items.map(item => {
            const stockStatus = item.can_fulfill_full ? 
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>In Stock</span>' :
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock</span>';
                
            return `
                <div class="border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <input type="checkbox" id="select_item_${item.item_id}" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                                <label for="select_item_${item.item_id}" class="text-sm font-medium text-gray-900 cursor-pointer">
                                    ${item.item_name}
                                </label>
                                ${stockStatus}
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-sm text-gray-600 ml-6">
                                <div>
                                    <span class="font-medium">Requested:</span> 
                                    <span class="font-bold text-chocolate">${item.quantity_requested} ${item.unit_symbol}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Stock:</span> 
                                    <span class="font-bold">${item.current_stock} ${item.unit_symbol}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Usage:</span> 
                                    <span class="font-bold ${item.stock_percentage > 80 ? 'text-red-600' : 'text-gray-700'}">${item.stock_percentage}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ml-6 mt-3 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">New Quantity</label>
                            <input type="number" id="new_qty_${item.item_id}" step="0.001" min="0" 
                                   value="${item.quantity_requested}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-chocolate focus:border-chocolate">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Reason</label>
                            <select id="reason_${item.item_id}" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-chocolate focus:border-chocolate">
                                <option value="">Select reason</option>
                                <option value="Insufficient Stock">Insufficient Stock</option>
                                <option value="Rationing (High Demand)">Rationing (High Demand)</option>
                                <option value="Policy Limit Exceeded">Policy Limit Exceeded</option>
                                <option value="Quality Issues">Quality Issues</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="ml-6 mt-3">
                        <label class="block text-xs font-medium text-gray-700">Remarks</label>
                        <textarea id="remarks_${item.item_id}" rows="2" 
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-chocolate focus:border-chocolate"
                                  placeholder="Optional remarks..."></textarea>
                    </div>
                </div>
            `;
        }).join('');

        el.content.innerHTML = `
            <div class="space-y-4">
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-amber-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-800">
                                Select the checkboxes next to items you want to modify, then update the quantities and provide reasons.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-0">
                    ${itemsFormHtml}
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="RequisitionManager.submitMultiItemModification(${data.requisition_id})" 
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none">
                        <i class="fas fa-save mr-2"></i>
                        Apply Modifications
                    </button>
                </div>
            </div>
        `;
    },

    submitMultiItemModification(requisitionId) {
        // This would collect all selected items and their modifications
        // and submit them as a batch
        const selectedItems = [];
        const checkboxes = document.querySelectorAll('input[id^="select_item_"]:checked');
        
        checkboxes.forEach(checkbox => {
            const itemId = checkbox.id.replace('select_item_', '');
            const newQty = document.getElementById(`new_qty_${itemId}`).value;
            const reason = document.getElementById(`reason_${itemId}`).value;
            const remarks = document.getElementById(`remarks_${itemId}`).value;
            
            if (!newQty || !reason) {
                this.showNotification('warning', 'Validation Error', 'Please fill in quantity and reason for all selected items.');
                return;
            }
            
            selectedItems.push({
                item_id: itemId,
                new_quantity: newQty,
                reason: reason,
                remarks: remarks
            });
        });
        
        if (selectedItems.length === 0) {
            this.showNotification('warning', 'No Selection', 'Please select at least one item to modify.');
            return;
        }
        
        // Submit the batch modification
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            this.showNotification('error', 'System Error', 'CSRF token missing.');
            return;
        }

        fetch(`/supervisor/requisitions/${requisitionId}/modify-multi`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                modifications: selectedItems
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (body.success) {
                this.closeDetailsModal();
                this.showNotification('success', 'Modified & Approved', 'Requisition items modified and approved successfully.', () => location.reload());
            } else {
                this.showNotification('error', 'Error', body.error || 'Failed to modify requisition items.');
            }
        })
        .catch(error => {
            this.showNotification('error', 'Network Error', 'An error occurred while submitting modifications.');
        });
    },

    openModifyModal(requisitionId, itemId, itemName, originalQty, unit, currentStock) {
        const el = this.elements.modifyModal;
        
        el.text.innerHTML = `Adjusting quantity for <strong>${itemName}</strong> in request <strong>REQ-${requisitionId}</strong>.`;
        el.origQty.innerText = `${originalQty} ${unit}`;
        el.newQty.value = originalQty;
        el.unit.innerText = unit;
        el.itemId.value = itemId;
        this.currentRequisitionId = requisitionId;
        
        el.stock.textContent = `${currentStock} ${unit}`;
        
        if (currentStock < originalQty) {
            el.warning.classList.remove('hidden');
            el.newQty.max = currentStock;
            if (originalQty > currentStock) {
                el.newQty.value = currentStock;
            }
        } else {
            el.warning.classList.add('hidden');
            el.newQty.max = '';
        }
        
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });
    },

    closeModifyModal() {
        const el = this.elements.modifyModal;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
            el.form.reset();
        }, 300);
        this.currentRequisitionId = null;
    },

    submitModifyForm() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            this.showNotification('error', 'System Error', 'CSRF token missing.');
            return;
        }

        const el = this.elements.modifyModal;
        const itemId = el.itemId.value;
        const newQuantity = el.newQty.value;
        const reason = document.querySelector('#modifyForm select[name="reason"]').value;
        const remarks = document.querySelector('#modifyForm textarea[name="remarks"]').value;

        if (!itemId || !newQuantity || !reason) {
            this.showNotification('warning', 'Validation Error', 'Please complete all required fields (Quantity and Reason).');
            return;
        }

        fetch(`/supervisor/requisitions/${this.currentRequisitionId}/modify`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                item_id: itemId,
                new_quantity: newQuantity,
                reason: reason,
                remarks: remarks,
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (body.success) {
                this.closeModifyModal();
                this.showNotification('success', 'Modified & Approved', 'Requisition modified and approved successfully.', () => location.reload());
            } else {
                this.showNotification('error', 'Error', body.error || 'Failed to modify requisition.');
            }
        })
        .catch(error => {
            this.showNotification('error', 'Network Error', 'An error occurred while submitting modifications.');
        });
    },

    // ================= DETAILS LOGIC =================

    viewDetails(requisitionId) {
        const el = this.elements.detailsModal;
        
        // Loading State
        el.title.textContent = 'Requisition Details';
        el.subtitle.textContent = 'Loading information...';
        el.content.innerHTML = '<div class="flex justify-center py-12"><i class="fas fa-circle-notch fa-spin text-4xl text-chocolate/50"></i></div>';
        
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });

        fetch(`/supervisor/requisitions/${requisitionId}/details`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                this.renderDetails(data.data);
            } else {
                throw new Error(data.error || 'Invalid response');
            }
        })
        .catch(error => {
            el.content.innerHTML = `
                <div class="text-center py-8">
                    <div class="bg-red-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <p class="text-red-800 font-medium">Failed to load details</p>
                    <p class="text-sm text-red-600 mt-1">${error.message || 'Please check your connection.'}</p>
                </div>
            `;
        });
    },

    renderDetails(data) {
        const el = this.elements.detailsModal;
        el.title.textContent = `Requisition #${data.requisition_number}`;
        el.subtitle.textContent = `${data.requested_by} • ${data.department} • ${data.time_ago}`;

        const itemsHtml = data.items.map(item => {
            const stockStatus = item.can_fulfill_full ? 
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>In Stock</span>' :
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock</span>';
                
            return `
                <tr class="border-b border-gray-100 last:border-0">
                    <td class="px-4 py-3">
                        <div class="font-bold text-gray-900">${item.item_name}</div>
                        <div class="text-xs text-gray-500">Unit: ${item.unit_symbol}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="font-medium text-gray-900 bg-gray-50 px-2 py-1 rounded inline-block border border-gray-200">${item.quantity_requested} ${item.unit_symbol}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="font-medium text-gray-600">${item.current_stock} ${item.unit_symbol}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="text-sm font-bold ${item.stock_percentage > 80 ? 'text-red-600' : 'text-gray-700'}">${item.stock_percentage}%</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        ${stockStatus}
                    </td>
                </tr>
            `;
        }).join('');

        el.content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    ${data.status.toUpperCase()}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Date</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">${new Date(data.created_at).toLocaleDateString()}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Items</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">${data.total_items}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Department</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">${data.department || 'N/A'}</dd>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Purpose</dt>
                        <dd class="mt-1 text-sm text-gray-700 italic">"${data.purpose || 'No purpose specified'}"</dd>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3">Requested Items</h4>
                    <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Req. Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stock</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Usage</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>

                ${data.notes ? `
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-2">Notes</h4>
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                            <p class="text-sm text-amber-800">${data.notes}</p>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    },

    closeDetailsModal() {
        const el = this.elements.detailsModal;
        el.backdrop.classList.remove('active');
        el.panel.classList.remove('active');
        setTimeout(() => {
            el.backdrop.classList.add('hidden');
        }, 300);
    }
};
</script>
@endsection