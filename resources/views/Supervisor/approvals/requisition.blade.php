@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">
    <!-- 1. HEADER & SUMMARY -->
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

    <!-- 2. FILTERS -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <form method="GET" class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search by requester or item...">
        </form>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form method="GET" class="flex items-center gap-3 w-full md:w-auto">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <select name="status" onchange="this.form.submit()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="pending" {{ request('status', 'pending') == 'pending' ? 'selected' : '' }}>Status: Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Status: Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Status: Rejected</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All History</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </form>
        </div>
    </div>

    <!-- 3. REQUISITIONS TABLE -->
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

                        <tr class="hover:bg-gray-50 transition-colors {{ $hasHighRequest && !$hasSufficientStock ? 'bg-red-50 border-l-4 border-l-red-400' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">#{{ $requisition->requisition_number }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $requisition->created_at->diffForHumans() }}
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
                                            <span class="font-bold {{ $hasHighRequest && !$hasSufficientStock ? 'text-red-600' : 'text-blue-600' }}">
                                                {{ number_format($requestedQty, 1) }} {{ $mainItem->item->unit->symbol ?? '' }}
                                            </span>
                                        </span>
                                        <span class="text-gray-400">|</span>
                                        <span class="text-gray-500">Stock: {{ number_format($currentStock, 1) }} {{ $mainItem->item->unit->symbol ?? '' }}</span>
                                        @if($hasSufficientStock)
                                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
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
                                                    <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Individual modify
                                                </div>
                                            @else
                                                <div class="text-xs text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded font-medium">
                                                    <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Bulk modify ({{ $itemCount }} items)
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
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    
                                    <button 
                                        onclick="RequisitionManager.openCombinedModal({{ $requisition->id }})"
                                        class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1 rounded transition" 
                                        title="View & Modify Items"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                        View & Modify
                                    </button>
                                    
                                    <button 
                                        onclick="RequisitionManager.reject({{ $requisition->id }})"
                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1 rounded transition" 
                                        title="Reject"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
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

<!-- COMBINED MODAL -->
<div id="combinedModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" onclick="RequisitionManager.closeModal()"></div>

        <!-- Spacer for medium screens -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <!-- Modal header -->
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">Requisition Details</h3>
                        <p class="text-sm text-gray-500" id="modalSubtitle">Loading requisition information...</p>
                    </div>
                    <div class="ml-auto">
                        <button onclick="RequisitionManager.closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal content -->
            <div class="bg-white px-6 pb-6">
                <!-- Requisition Overview -->
                <div id="requisitionOverview" class="mt-6">
                    <!-- Overview will be loaded here -->
                </div>

                <!-- Items Section -->
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-900">Items for Modification</h4>
                        <div class="text-sm text-gray-500">
                            <span id="modifiedCount">0</span> of <span id="totalItemsCount">0</span> items modified
                        </div>
                    </div>
                    <div id="itemsContainer" class="space-y-3 max-h-96 overflow-y-auto">
                        <!-- Items will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row gap-3 border-t border-gray-200">
                <button type="button" onclick="RequisitionManager.approveRequisition()" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Approve Requisition
                </button>
                <button type="button" onclick="RequisitionManager.rejectRequisition()" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Reject Requisition
                </button>
                <button type="button" onclick="RequisitionManager.closeModal()" 
                        class="inline-flex justify-center items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- NOTIFICATION TOAST -->
<div id="notificationToast" class="hidden fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200">
    <div class="p-4">
        <div class="flex items-start">
            <div id="toastIcon" class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 w-0 flex-1">
                <p id="toastTitle" class="text-sm font-medium text-gray-900">Success</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500">Operation completed successfully.</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="RequisitionManager.hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const RequisitionManager = {
    currentRequisitionId: null,
    modifiedItems: new Map(),
    
    // Show notification toast
    showToast(type, title, message) {
        const toast = document.getElementById('notificationToast');
        const icon = document.getElementById('toastIcon');
        const titleEl = document.getElementById('toastTitle');
        const messageEl = document.getElementById('toastMessage');
        
        titleEl.textContent = title;
        messageEl.textContent = message;
        
        // Set icon based on type
        icon.innerHTML = type === 'success' 
            ? '<svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
            : '<svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        
        toast.classList.remove('hidden');
        
        // Auto hide after 4 seconds
        setTimeout(() => {
            this.hideToast();
        }, 4000);
    },
    
    hideToast() {
        document.getElementById('notificationToast').classList.add('hidden');
    },
    
    // Open combined modal
    openCombinedModal(requisitionId) {
        this.currentRequisitionId = requisitionId;
        this.modifiedItems.clear();
        
        const modal = document.getElementById('combinedModal');
        modal.classList.remove('hidden');
        
        // Load requisition details
        this.loadRequisitionDetails(requisitionId);
    },
    
    closeModal() {
        document.getElementById('combinedModal').classList.add('hidden');
        this.currentRequisitionId = null;
        this.modifiedItems.clear();
    },
    
    // Load requisition details via API
    loadRequisitionDetails(requisitionId) {
        const titleEl = document.getElementById('modalTitle');
        const subtitleEl = document.getElementById('modalSubtitle');
        const overviewEl = document.getElementById('requisitionOverview');
        const itemsEl = document.getElementById('itemsContainer');
        const totalCountEl = document.getElementById('totalItemsCount');
        
        // Show loading state
        titleEl.textContent = 'Loading...';
        subtitleEl.textContent = 'Please wait while we load the requisition details.';
        overviewEl.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        itemsEl.innerHTML = '<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
        
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
                this.renderModal(data.data);
            } else {
                throw new Error(data.error || 'Failed to load requisition details');
            }
        })
        .catch(error => {
            console.error('Error loading requisition:', error);
            overviewEl.innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-800 font-medium">Failed to load requisition</p>
                    <p class="text-sm text-red-600 mt-1">${error.message}</p>
                    <button onclick="RequisitionManager.closeModal()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Close
                    </button>
                </div>
            `;
        });
    },
    
    // Render modal content
    renderModal(data) {
        const titleEl = document.getElementById('modalTitle');
        const subtitleEl = document.getElementById('modalSubtitle');
        const overviewEl = document.getElementById('requisitionOverview');
        const itemsEl = document.getElementById('itemsContainer');
        const totalCountEl = document.getElementById('totalItemsCount');
        
        // Set header info
        titleEl.textContent = `Requisition #${data.requisition_number}`;
        subtitleEl.textContent = `${data.requested_by} • ${data.department || 'General'} • ${data.time_ago}`;
        totalCountEl.textContent = data.total_items;
        
        // Render overview
        const statusBadge = data.status === 'pending' 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">PENDING</span>'
            : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${data.status === 'approved' ? 'green' : 'red'}-100 text-${data.status === 'approved' ? 'green' : 'red'}-800">${data.status.toUpperCase()}</span>`;

        overviewEl.innerHTML = `
            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Status</dt>
                        <dd class="mt-1">${statusBadge}</dd>
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
                ${data.purpose ? `
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Purpose</dt>
                        <dd class="mt-1 text-sm text-gray-700 italic">"${data.purpose}"</dd>
                    </div>
                ` : ''}
                ${data.notes ? `
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <dt class="text-xs font-bold text-gray-500 uppercase tracking-wide">Notes</dt>
                        <dd class="mt-1 text-sm text-amber-800 bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg">${data.notes}</dd>
                    </div>
                ` : ''}
            </div>
        `;
        
        // Render items
        this.renderItems(data.items);
    },
    
    // Render items list
    renderItems(items) {
        const itemsEl = document.getElementById('itemsContainer');
        const modifiedCountEl = document.getElementById('modifiedCount');
        
        const itemsHtml = items.map(item => {
            const stockStatus = item.can_fulfill_full 
                ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>In Stock</span>'
                : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Low Stock</span>';
                
            const stockPercentageClass = item.stock_percentage > 80 ? 'text-red-600' : 'text-gray-700';
                
            return `
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
        }).join('');

        itemsEl.innerHTML = itemsHtml;
        modifiedCountEl.textContent = '0';
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
        if (confirm('Are you sure you want to reject this requisition? This action cannot be undone.')) {
            this.performAction(this.currentRequisitionId, 'reject');
        }
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
    performAction(id, action) {
        fetch(`/supervisor/requisitions/${id}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({})
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
        if (confirm('Are you sure you want to approve this requisition?')) {
            this.performAction(id, 'approve');
        }
    },
    
    // Simple reject from table
    reject(id) {
        if (confirm('Are you sure you want to reject this requisition? This action cannot be undone.')) {
            this.performAction(id, 'reject');
        }
    }
};
</script>
@endsection