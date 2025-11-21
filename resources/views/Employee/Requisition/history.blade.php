@extends('Employee.layout.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    {{-- Success Notification --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    @if(session('new_requisition'))
                        <p class="text-xs text-green-600 mt-1">Your new requisition should appear at the top of the list below.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Request History</h1>
            <p class="text-sm text-gray-500 mt-1">Track the status of your ingredient requisitions.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition shadow-sm">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
            <a href="{{ route('employee.requisitions.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> New Request
            </a>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <form method="GET" action="{{ route('employee.requisitions.history') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                    placeholder="Search by Item or ID..."
                >
            </div>

            <!-- Filter -->
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select 
                    name="status" 
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
                
                @if(request('search') || request('status'))
                    <a href="{{ route('employee.requisitions.history') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 3. HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Submitted</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Summary</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor Note</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($requisitions as $requisition)
                        @php
                            $statusConfig = [
                                'pending' => ['class' => 'bg-amber-100 text-amber-800', 'icon' => 'fa-clock', 'label' => 'Pending'],
                                'approved' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check-circle', 'label' => 'Approved'],
                                'rejected' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times-circle', 'label' => 'Rejected'],
                                'fulfilled' => ['class' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-check', 'label' => 'Fulfilled']
                            ];
                            
                            $status = $statusConfig[$requisition->status] ?? $statusConfig['pending'];
                            
                            // Format date nicely
                            $requestDate = \Carbon\Carbon::parse($requisition->request_date);
                            $now = \Carbon\Carbon::now();
                            
                            if ($requestDate->isToday()) {
                                $dateDisplay = 'Today, ' . $requestDate->format('h:i A');
                            } elseif ($requestDate->isYesterday()) {
                                $dateDisplay = 'Yesterday, ' . $requestDate->format('h:i A');
                            } else {
                                $dateDisplay = $requestDate->format('M j, Y') . ($requestDate->year == $now->year ? '' : ', ' . $requestDate->format('Y'));
                            }
                            
                            // Get items summary
                            $items = $requisition->requisitionItems ?? collect();
                            $itemNames = $items->take(2)->map(function($item) {
                                return $item->item->name ?? 'Unknown Item';
                            })->implode(', ');
                            $totalItems = $items->count();
                            
                            // Get quantities for display
                            $quantities = $items->take(2)->map(function($item) {
                                return $item->quantity_requested . ' ' . ($item->item->unit->symbol ?? 'pcs');
                            })->implode(', ');
                        @endphp
                        
                        <tr class="hover:bg-gray-50 transition-colors {{ $requisition->status === 'rejected' ? 'bg-red-50/30 border-l-4 border-red-300' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm font-bold text-gray-700">
                                #{{ $requisition->requisition_number ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $dateDisplay }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">
                                    @if($totalItems > 0)
                                        @if($totalItems > 2)
                                            {{ $itemNames }}, +{{ $totalItems - 2 }} more
                                        @else
                                            {{ $itemNames }}
                                        @endif
                                    @else
                                        No items specified
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($totalItems == 1)
                                        {{ $quantities }}
                                    @else
                                        {{ $totalItems }} {{ Str::plural('Item', $totalItems) }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['class'] }}">
                                    <i class="fas {{ $status['icon'] }} mr-1"></i> {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button onclick="viewRequisitionDetails({{ $requisition->id }})" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-chocolate text-white hover:bg-chocolate-dark transition-colors">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if($requisition->status === 'pending')
                                    <span class="text-gray-400 italic">
                                        - No action yet -
                                    </span>
                                @elseif($requisition->status === 'approved')
                                    <span class="text-green-600 flex items-center justify-end">
                                        <i class="fas fa-box-open mr-1"></i> Ready for Pickup
                                    </span>
                                @elseif($requisition->status === 'rejected')
                                    @if($requisition->notes)
                                        <button onclick="showRejectionReason('{{ addslashes($requisition->notes) }}', '{{ $requisition->approvedBy->name ?? 'Supervisor' }}')" 
                                                class="text-red-600 hover:text-red-800 font-bold text-xs underline">
                                            Why?
                                        </button>
                                    @else
                                        <span class="text-red-600">Rejected</span>
                                    @endif
                                @elseif($requisition->status === 'fulfilled')
                                    <span class="text-gray-400 flex items-center justify-end">
                                        <i class="fas fa-check-circle mr-1"></i> Received
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium mb-2">No requisitions found</p>
                                    <p class="text-sm">Your requisition history will appear here once you submit requests.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(method_exists($requisitions, 'hasPages') && $requisitions->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($requisitions->previousPageUrl())
                        <a href="{{ $requisitions->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    @if($requisitions->nextPageUrl())
                        <a href="{{ $requisitions->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $requisitions->firstItem() ?? 0 }}</span>
                            to
                            <span class="font-medium">{{ $requisitions->lastItem() ?? 0 }}</span>
                            of
                            <span class="font-medium">{{ $requisitions->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            {{-- Previous Page Link --}}
                            @if($requisitions->previousPageUrl())
                                <a href="{{ $requisitions->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach($requisitions->getUrlRange(1, $requisitions->lastPage()) as $page => $url)
                                @if($page == $requisitions->currentPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-chocolate bg-chocolate text-sm font-medium text-white">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if($requisitions->nextPageUrl())
                                <a href="{{ $requisitions->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        @elseif(isset($isNewRequisition) && $isNewRequisition)
            {{-- Show "View All" button when showing recent requisitions after creation --}}
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-center">
                    <a href="{{ route('employee.requisitions.history') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-list mr-2"></i>
                        View All Requisitions
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>

<!-- REJECTION REASON MODAL -->
<div id="rejectionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('rejectionModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-comment-alt text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Rejection Details</h3>
                        <div class="mt-4 bg-red-50 p-3 rounded border border-red-100">
                            <p class="text-sm text-gray-800 italic" id="reasonText">"..."</p>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 text-right">- <span id="rejectorName">Supervisor</span></p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('rejectionModal').classList.add('hidden')" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- REQUISITION DETAILS MODAL -->
<div id="requisitionDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="requisitionModalTitle">Requisition Details</h3>
                    <button type="button" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Loading State -->
                <div id="detailsLoading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-chocolate"></div>
                    <span class="ml-2 text-gray-600">Loading details...</span>
                </div>
                
                <!-- Details Content -->
                <div id="detailsContent" class="hidden">
                    <!-- Header Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Requisition ID</p>
                            <p class="text-sm font-mono font-bold text-gray-900" id="detailRequisitionNumber">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                            <p class="text-sm font-bold text-gray-900" id="detailStatus">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Date Submitted</p>
                            <p class="text-sm text-gray-900" id="detailDate">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Department</p>
                            <p class="text-sm text-gray-900" id="detailDepartment">-</p>
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="mb-6">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Purpose/Notes</p>
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                            <p class="text-sm text-gray-800" id="detailPurpose">-</p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Items Requested</p>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="detailItemsTable">
                                    <!-- Items will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-end">
                        <div class="bg-chocolate text-white px-4 py-2 rounded-lg">
                            <span class="text-xs font-semibold uppercase tracking-wide">Total Estimated Value: </span>
                            <span class="text-lg font-bold" id="detailTotal">₱0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showRejectionReason(reason, user) {
        document.getElementById('reasonText').innerText = `"${reason}"`;
        document.getElementById('rejectorName').innerText = user;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }

    function viewRequisitionDetails(requisitionId) {
        const modal = document.getElementById('requisitionDetailsModal');
        const loading = document.getElementById('detailsLoading');
        const content = document.getElementById('detailsContent');
        
        // Show modal and loading state
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        
        // Fetch requisition details via AJAX
        fetch(`/employee/requisitions/${requisitionId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateRequisitionDetails(data.requisition);
                } else {
                    alert('Failed to load requisition details.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading requisition details.');
            })
            .finally(() => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            });
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
        
        // Populate header info
        document.getElementById('detailRequisitionNumber').textContent = `#${requisition.requisition_number}`;
        document.getElementById('detailStatus').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${status.class}">
            <i class="fas ${status.icon || 'fa-clock'} mr-1"></i> ${status.label}
        </span>`;
        document.getElementById('detailDate').textContent = new Date(requisition.request_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('detailDepartment').textContent = requisition.department || 'N/A';
        document.getElementById('detailPurpose').textContent = requisition.purpose || 'No purpose specified';
        
        // Populate items table
        const itemsTable = document.getElementById('detailItemsTable');
        itemsTable.innerHTML = '';
        
        if (requisition.requisition_items && requisition.requisition_items.length > 0) {
            requisition.requisition_items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${item.item.name}</div>
                        <div class="text-sm text-gray-500">${item.item.item_code}</div>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${parseFloat(item.quantity_requested)} ${item.item.unit.symbol}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        ₱${parseFloat(item.unit_cost_estimate).toFixed(2)}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ₱${parseFloat(item.total_estimated_value).toFixed(2)}
                    </td>
                `;
                itemsTable.appendChild(row);
            });
        } else {
            itemsTable.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No items found</td></tr>';
        }
        
        // Populate total
        document.getElementById('detailTotal').textContent = `₱${parseFloat(requisition.total_estimated_value || 0).toFixed(2)}`;
    }
</script>

@endsection