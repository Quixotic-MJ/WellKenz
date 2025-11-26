@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fulfill Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Process approved kitchen requisitions and issue stock.</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $readyToPickCount = $requisitions->where('status', 'approved')->count();
                $pendingCount = $requisitions->where('status', 'pending')->count();
                $fulfilledCount = $requisitions->where('status', 'fulfilled')->count();
            @endphp
            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full">
                {{ $readyToPickCount }} Ready to Pick
            </span>
            <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                {{ $pendingCount }} Pending
            </span>
            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
                {{ $fulfilledCount }} Fulfilled
            </span>
        </div>
    </div>

    {{-- 2. WORKFLOW GUIDE --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Review Requisitions</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Process via Modal</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Confirm Issuance</span>
                </div>
            </div>
            <div class="text-xs text-blue-700 font-medium">
                <i class="fas fa-info-circle mr-1"></i>
                Click "Issue Items" to process each requisition
            </div>
        </div>
    </div>

    {{-- 3. REQUESTS QUEUE TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        {{-- Hidden data store for modals --}}
        <script type="application/json" id="requisitionData">
            {!! json_encode($requisitions->map(function($requisition) {
                return [
                    'id' => $requisition->id,
                    'number' => $requisition->requisition_number,
                    'requestedBy' => $requisition->requestedBy->name ?? 'Unknown User',
                    'department' => $requisition->department ?? 'General',
                    'purpose' => $requisition->purpose,
                    'requestDate' => $requisition->request_date,
                    'items' => $requisition->requisitionItems->map(function($requisitionItem) {
                        $item = $requisitionItem->item;
                        $availableBatches = \App\Models\Batch::where('item_id', $item->id)
                            ->whereIn('status', ['active', 'quarantine'])
                            ->where('quantity', '>', 0)
                            ->orderBy('expiry_date', 'asc')
                            ->get();
                        
                        $batchIndex = 0;
                        return [
                            'id' => $requisitionItem->id,
                            'name' => $item->name,
                            'sku' => $item->item_code,
                            'requestedQty' => number_format($requisitionItem->quantity_requested, 2) . ' ' . ($item->unit->symbol ?? 'pcs'),
                            'unit' => $item->unit->symbol ?? 'pcs',
                            'availableBatches' => $availableBatches->map(function($batch) use ($item, &$batchIndex) {
                                $isFefo = $batchIndex === 0;
                                $batchIndex++;
                                return [
                                    'id' => $batch->id,
                                    'number' => $batch->batch_number,
                                    'expiry' => $batch->expiry_date,
                                    'location' => $batch->location ?? 'Main',
                                    'available' => number_format($batch->quantity, 2),
                                    'isFEFO' => $isFefo
                                ];
                            })->values()
                        ];
                    })->values()
                ];
            })->values()) !!}
        </script>
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Requisition Queue</h2>
            <p class="text-sm text-gray-500 mt-1">Click on any requisition to view details or use "Issue Items" to process</p>
        </div>

        @if($requisitions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req #</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Department</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Due Date</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($requisitions as $index => $requisition)
                            @php
                                $itemCount = $requisition->requisitionItems->count();
                                $isApproved = $requisition->status === 'approved';
                                $isPending = $requisition->status === 'pending';
                                $isFulfilled = $requisition->status === 'fulfilled';
                                
                                // Calculate progress
                                $pickedCount = 0;
                                foreach($requisition->requisitionItems as $item) {
                                    if(session("picking.{$item->id}")) {
                                        $pickedCount++;
                                    }
                                }
                                $progressPercentage = $itemCount > 0 ? ($pickedCount / $itemCount) * 100 : 0;
                                
                                // Check if any items have available batches
                                $hasAvailableStock = false;
                                foreach($requisition->requisitionItems as $requisitionItem) {
                                    $availableBatches = \App\Models\Batch::where('item_id', $requisitionItem->item->id)
                                        ->whereIn('status', ['active', 'quarantine'])
                                        ->where('quantity', '>', 0)
                                        ->count();
                                    if($availableBatches > 0) {
                                        $hasAvailableStock = true;
                                        break;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer requisition-row" 
                                data-requisition-id="{{ $requisition->id }}"
                                data-expand-url="{{ route('inventory.outbound.fulfill', ['expand' => $requisition->id]) }}">
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">
                                        #{{ $requisition->requisition_number }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-xs">
                                        <div class="font-medium text-gray-900 truncate max-w-24">
                                            {{ $requisition->requestedBy->name ?? 'Unknown' }}
                                        </div>
                                        <div class="text-gray-500 truncate max-w-24">
                                            {{ $requisition->requestedBy->department ?? 'General' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 hidden md:table-cell">
                                    <div class="text-xs text-gray-900">{{ $requisition->department ?? 'General' }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ Str::limit($requisition->purpose, 25) }}</div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-900">{{ $itemCount }}</div>
                                    <div class="text-xs text-gray-500">{{ $pickedCount }}/{{ $itemCount }} ✓</div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap hidden lg:table-cell">
                                    <div class="text-xs text-gray-900">
                                        {{ \Carbon\Carbon::parse($requisition->request_date)->format('M d') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($requisition->request_date)->format('D') }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                    @if($isApproved)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Ready
                                        </span>
                                    @elseif($isPending)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($isFulfilled)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Done
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($requisition->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-1 min-w-0">
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full transition-all duration-300 {{ $progressPercentage == 100 ? 'bg-green-500' : 'bg-green-600' }}" 
                                                     style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                        </div>
                                        <span class="ml-1 text-xs text-gray-500">{{ round($progressPercentage) }}%</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-center">
                                    @if($isApproved && $hasAvailableStock && $progressPercentage < 100)
                                        <button class="issue-items-btn px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700 transition-all"
                                                data-requisition-id="{{ $requisition->id }}"
                                                onclick="event.stopPropagation();">
                                            <i class="fas fa-clipboard-list"></i>
                                        </button>
                                    @elseif($progressPercentage == 100)
                                        <span class="text-green-600 text-xs font-bold">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    @elseif($isPending)
                                        <span class="text-yellow-600 text-xs">⏰</span>
                                    @elseif(!$hasAvailableStock)
                                        <span class="text-red-600 text-xs">❌</span>
                                    @else
                                        <span class="text-gray-400 text-xs">➖</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Requisitions Found</h3>
                <p class="text-gray-500">There are currently no requisitions to fulfill.</p>
            </div>
        @endif
    </div>

    {{-- 4. QUICK STATS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100">
                    <i class="fas fa-clipboard-list text-blue-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-600">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ $requisitions->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100">
                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-600">Ready</p>
                    <p class="text-lg font-bold text-gray-900">{{ $readyToPickCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-yellow-100">
                    <i class="fas fa-clock text-yellow-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-600">Pending</p>
                    <p class="text-lg font-bold text-gray-900">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100">
                    <i class="fas fa-box text-blue-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-600">Fulfilled</p>
                    <p class="text-lg font-bold text-gray-900">{{ $fulfilledCount }}</p>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal for Issue Items --}}
<div id="issueItemsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-4 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Process Requisition</h3>
                    <p class="text-sm text-gray-500" id="modalSubtitle">Select batches and verify items physically</p>
                </div>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="mt-6" id="modalContent">
                {{-- Dynamic content will be loaded here --}}
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 mt-6 sticky bottom-0 bg-white">
                <div class="text-sm text-gray-500" id="modalProgress">
                    Processing 0 of 0 items
                </div>
                <div class="flex space-x-3">
                    <button id="cancelModal" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="confirmModal" class="px-6 py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 disabled:bg-gray-400" disabled>
                        <i class="fas fa-check mr-2"></i>Confirm Issuance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentRequisitionId = null;

    // Row click handler for expanding details
    document.querySelectorAll('.requisition-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't expand if clicking on the action button
            if (e.target.closest('.issue-items-btn')) {
                return;
            }
            
            const expandUrl = this.dataset.expandUrl;
            window.location.href = expandUrl;
        });
    });

    // Issue Items button handler
    document.querySelectorAll('.issue-items-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent row click
            currentRequisitionId = this.dataset.requisitionId;
            openIssueModal();
        });
    });

    // Modal controls
    document.getElementById('closeModal').addEventListener('click', closeIssueModal);
    document.getElementById('cancelModal').addEventListener('click', closeIssueModal);
    document.getElementById('confirmModal').addEventListener('click', confirmIssuance);

    function openIssueModal() {
        const modal = document.getElementById('issueItemsModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        
        modalTitle.textContent = 'Process Requisition';
        modalSubtitle.textContent = 'Select batches and verify items physically';
        
        // Load requisition data
        loadRequisitionData(currentRequisitionId);
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }

    function closeIssueModal() {
        const modal = document.getElementById('issueItemsModal');
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function loadRequisitionData(requisitionId) {
        // In a real implementation, this would fetch from the server
        // For demo, we'll create sample data structure
        const requisitionData = getRequisitionDataFromPage(requisitionId);
        
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = createModalContent(requisitionData);
        
        // Add event listeners
        addModalEventListeners(requisitionData);
        updateProgress();
    }

    function getRequisitionDataFromPage(requisitionId) {
        // Extract real data from the page
        const dataStore = document.getElementById('requisitionData');
        if (!dataStore) {
            console.error('Requisition data store not found');
            return null;
        }
        
        try {
            const allRequisitionData = JSON.parse(dataStore.textContent);
            const requisitionData = allRequisitionData.find(req => req.id == requisitionId);
            
            if (!requisitionData) {
                console.error(`Requisition ${requisitionId} not found in data`);
                return null;
            }
            
            return requisitionData;
        } catch (error) {
            console.error('Error parsing requisition data:', error);
            return null;
        }
    }

    function createModalContent(data) {
        let content = `
            <div class="space-y-6">
                {{-- Requisition Summary --}}
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h4 class="font-bold text-gray-900 mb-2 text-sm">Requisition Summary</h4>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="font-medium text-gray-600">Req #:</span>
                            <span class="ml-1 font-mono font-bold">${data.number}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Requested By:</span>
                            <span class="ml-1">${data.requestedBy}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Department:</span>
                            <span class="ml-1">${data.department}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-600">Required By:</span>
                            <span class="ml-1">${formatDate(data.requestDate)}</span>
                        </div>
                        <div class="col-span-2 lg:col-span-4">
                            <span class="font-medium text-gray-600">Purpose:</span>
                            <span class="ml-1">${data.purpose}</span>
                        </div>
                    </div>
                </div>

                {{-- Items Processing --}}
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">Items to Process</h4>
                    <div class="space-y-6" id="itemsContainer">
        `;

        data.items.forEach((item, index) => {
            const hasBatches = item.availableBatches.length > 0;
            const fefoBatch = item.availableBatches.find(b => b.isFEFO);
            
            content += `
                <div class="border border-gray-200 rounded-lg p-3 item-container" data-item-id="${item.id}">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-gray-900 text-sm">${item.name}</h5>
                            <p class="text-xs text-gray-600 truncate">${item.sku} • ${item.requestedQty}</p>
                        </div>
                        <div class="ml-2">
                            ${hasBatches ? 
                                '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">Stock</span>' :
                                '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">No Stock</span>'
                            }
                        </div>
                    </div>

                    ${hasBatches ? `
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-medium text-gray-700">Suggested Batch (FEFO):</label>
                                <div class="text-xs text-gray-500" id="stock-summary-${item.id}">
                                    Calculating available stock...
                                </div>
                            </div>
                            <div class="space-y-1 max-h-32 overflow-y-auto">
                                ${item.availableBatches.map((batch, batchIndex) => `
                                    <div class="flex items-center space-x-2 p-2 border border-gray-200 rounded hover:bg-gray-50 text-xs">
                                        <input type="checkbox" 
                                               class="batch-checkbox" 
                                               data-batch-id="${batch.id}"
                                               data-item-id="${item.id}"
                                               data-max-qty="${batch.available}"
                                               ${batch.isFEFO ? 'checked' : ''}>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <div class="truncate">
                                                    <span class="font-medium">Batch #${batch.number}</span>
                                                    ${batch.isFEFO ? '<span class="text-green-600 ml-1">🎯</span>' : ''}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ${formatDate(batch.expiry)}
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div class="text-xs text-gray-600 truncate">
                                                    ${batch.available} ${item.unit} • ${batch.location}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-16">
                                            <input type="number" 
                                                   class="batch-qty-input w-full px-1 py-0.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-green-500" 
                                                   placeholder="Qty"
                                                   min="0"
                                                   max="${batch.available}"
                                                   step="0.01"
                                                   ${!batch.isFEFO ? 'disabled' : ''}>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            
                            ${calculateShortage(item) ? `
                                <div class="mt-2 p-2 bg-orange-50 border border-orange-200 rounded text-xs">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>
                                        <span class="text-orange-700">
                                            <strong>Shortage Alert:</strong> ${calculateShortage(item)} will be backordered
                                        </span>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    ` : '<div class="text-xs text-red-600 mb-3">⚠️ No stock available - will be fully backordered</div>'}

                    ${hasBatches ? `
                        <div class="border-t pt-2">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" 
                                       id="verify-${item.id}" 
                                       class="form-checkbox h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500 verification-checkbox"
                                       data-item-id="${item.id}">
                                <label for="verify-${item.id}" class="text-xs text-gray-700">
                                    Physically verified
                                </label>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        });

        content += `
                    </div>
                </div>

                {{-- Fulfillment Summary --}}
                <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg" id="fulfillmentSummary">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-blue-900 text-sm">Fulfillment Summary</h4>
                        <div class="text-xs text-blue-700" id="summaryStats">
                            0 of 0 items processed
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white p-2 rounded">
                            <div class="font-medium text-green-700">Will Issue</div>
                            <div class="text-lg font-bold text-green-600" id="willIssue">0</div>
                        </div>
                        <div class="bg-white p-2 rounded">
                            <div class="font-medium text-orange-700">Will Backorder</div>
                            <div class="text-lg font-bold text-orange-600" id="willBackorder">0</div>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-yellow-500 mr-2 text-sm"></i>
                        <div class="text-xs text-yellow-700">
                            <strong>Instructions:</strong> Partial fulfillment is supported. Available stock will be issued, shortages automatically generate purchase requests.
                        </div>
                    </div>
                </div>
            </div>
        `;

        return content;
    }

    function addModalEventListeners(data) {
        // Update stock summaries for all items
        data.items.forEach(item => {
            updateStockSummary(item.id, item);
        });

        // Batch selection handlers
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const quantityInput = this.closest('.flex').querySelector('.batch-qty-input');
                if (this.checked) {
                    quantityInput.disabled = false;
                    // Auto-fill FEFO batch
                    if (this.dataset.maxQty) {
                        quantityInput.value = this.dataset.maxQty;
                    }
                } else {
                    quantityInput.disabled = true;
                    quantityInput.value = '';
                }
                updateProgress();
            });
        });

        document.querySelectorAll('.batch-qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const maxQty = parseFloat(this.max);
                const currentValue = parseFloat(this.value) || 0;
                
                if (currentValue > maxQty) {
                    this.value = maxQty;
                }
                updateProgress();
            });
        });

        // Verification handlers
        document.querySelectorAll('.verification-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateProgress);
        });
    }

    function updateProgress() {
        const totalItems = document.querySelectorAll('.item-container').length;
        const processedItems = document.querySelectorAll('.verification-checkbox:checked').length;
        
        const progressText = document.getElementById('modalProgress');
        progressText.textContent = `Processed ${processedItems} of ${totalItems} items`;
        
        // Update fulfillment summary
        updateFulfillmentSummary();
        
        // Enable confirm button only if all items are processed
        const confirmButton = document.getElementById('confirmModal');
        confirmButton.disabled = processedItems !== totalItems;
    }

    function updateFulfillmentSummary() {
        const summary = calculateIssuanceSummary();
        
        // Update summary stats
        const summaryStats = document.getElementById('summaryStats');
        const willIssue = document.getElementById('willIssue');
        const willBackorder = document.getElementById('willBackorder');
        
        if (summaryStats && willIssue && willBackorder) {
            summaryStats.textContent = `${summary.verifiedItems.length} of ${summary.totalItems} items verified`;
            willIssue.textContent = summary.willIssue;
            willBackorder.textContent = summary.willBackorder;
            
            // Reset and set correct colors
            willIssue.className = 'text-lg font-bold text-green-600';
            willBackorder.className = 'text-lg font-bold text-orange-600';
            
            // If no items will be issued, change to gray
            if (summary.willIssue === 0) {
                willIssue.className = 'text-lg font-bold text-gray-400';
            }
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function calculateShortage(item) {
        // Extract numeric quantity from requestedQty string (e.g., "50.00 kg" -> 50.00)
        const requestedQty = parseFloat(item.requestedQty.match(/[\d.]+/)[0]);
        
        // Calculate total available from all batches
        const totalAvailable = item.availableBatches.reduce((sum, batch) => {
            return sum + parseFloat(batch.available);
        }, 0);
        
        const shortage = requestedQty - totalAvailable;
        return shortage > 0 ? `${shortage.toFixed(2)} ${item.unit}` : null;
    }

    function updateStockSummary(itemId, item) {
        const summaryElement = document.getElementById(`stock-summary-${itemId}`);
        if (!summaryElement) return;
        
        const requestedQty = parseFloat(item.requestedQty.match(/[\d.]+/)[0]);
        const totalAvailable = item.availableBatches.reduce((sum, batch) => {
            return sum + parseFloat(batch.available);
        }, 0);
        
        const shortage = requestedQty - totalAvailable;
        
        if (shortage <= 0) {
            summaryElement.innerHTML = `<span class="text-green-600">✓ Full stock available (${totalAvailable.toFixed(2)} ${item.unit})</span>`;
        } else {
            summaryElement.innerHTML = `<span class="text-orange-600">⚠️ Partial stock: ${totalAvailable.toFixed(2)} ${item.unit} available, ${shortage.toFixed(2)} ${item.unit} shortage</span>`;
        }
    }

    function confirmIssuance() {
        // Calculate summary before confirmation
        const issuanceSummary = calculateIssuanceSummary();
        
        let confirmMessage = 'Confirm issuance of selected items?';
        if (issuanceSummary.hasShortages) {
            confirmMessage += '\n\n⚠️ PARTIAL FULFILLMENT:\n';
            confirmMessage += `• Will issue: ${issuanceSummary.willIssue} items\n`;
            confirmMessage += `• Will backorder: ${issuanceSummary.willBackorder} items\n`;
            confirmMessage += `• Automatic purchase requests will be generated for shortages`;
        }
        
        if (!confirm(confirmMessage)) {
            return;
        }

        // Process the issuance
        processPartialIssuance(issuanceSummary);
    }

    function calculateIssuanceSummary() {
        const summary = {
            multiBatchSelections: {},
            processedItems: [],
            verifiedItems: [],
            shortages: [],
            willIssue: 0,
            willBackorder: 0,
            totalItems: 0,
            hasShortages: false
        };

        // Get the requisition data from the page
        const requisitionData = JSON.parse(document.getElementById('requisitionData').textContent);
        const currentRequisition = requisitionData.find(req => req.id == currentRequisitionId);

        document.querySelectorAll('.item-container').forEach(container => {
            const itemId = container.dataset.itemId;
            const batchSelections = [];
            let totalSelectedQuantity = 0;
            
            // Collect batch selections
            container.querySelectorAll('.batch-checkbox:checked').forEach(checkbox => {
                const quantityInput = checkbox.closest('.flex').querySelector('.batch-qty-input');
                const quantity = parseFloat(quantityInput.value) || 0;
                
                if (quantity > 0) {
                    batchSelections.push({
                        batch_id: checkbox.dataset.batchId,
                        quantity: quantity
                    });
                    totalSelectedQuantity += quantity;
                }
            });

            if (batchSelections.length > 0) {
                summary.multiBatchSelections[itemId] = batchSelections;
            }
            
            // Check if item is verified
            const verificationCheckbox = container.querySelector('.verification-checkbox');
            if (verificationCheckbox && verificationCheckbox.checked) {
                summary.verifiedItems.push(itemId);
                summary.processedItems.push(itemId); // For backend compatibility
            }

            // Calculate availability based on TOTAL AVAILABLE STOCK from all batches
            const itemData = currentRequisition.items.find(item => item.id == itemId);
            const requestedQty = parseFloat(itemData.requestedQty.match(/[\d.]+/)[0]);
            
            // Calculate total available from ALL batches for this item
            const totalAvailableStock = itemData.availableBatches.reduce((sum, batch) => {
                return sum + parseFloat(batch.available);
            }, 0);
            
            summary.totalItems++;
            
            // Determine fulfillment based on AVAILABLE STOCK, not selected quantity
            if (totalAvailableStock >= requestedQty) {
                // Sufficient stock available - will issue
                summary.willIssue++;
            } else {
                // Insufficient stock available - will backorder
                summary.willBackorder++;
                summary.hasShortages = true;
                
                const shortage = requestedQty - totalAvailableStock;
                summary.shortages.push({
                    itemId: itemId,
                    itemName: itemData.name,
                    requestedQty: requestedQty,
                    availableQty: totalAvailableStock,
                    shortageQty: shortage
                });
            }
        });

        return summary;
    }

    function processPartialIssuance(summary) {
        // Prepare request data for partial fulfillment
        const requestData = {
            requisition_id: currentRequisitionId,
            multi_batch_selections: summary.multiBatchSelections,
            processed_items: summary.verifiedItems,
            shortages: summary.shortages,
            partial_fulfillment: summary.hasShortages,
            auto_generate_pr: summary.hasShortages // Auto-generate purchase requests
        };

        console.log('Sending partial issuance request:', requestData);
        processIssuance(requestData);
    }

    function processIssuance(requestData) {
        // Show loading
        const confirmButton = document.getElementById('confirmModal');
        const originalText = confirmButton.innerHTML;
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        confirmButton.disabled = true;

        // Send to server
        fetch("/inventory/outbound/confirm-issuance", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                let successMessage = '✅ Requisition processed successfully!';
                
                if (body.partial_fulfillment) {
                    successMessage += `\n\n📋 PARTIAL FULFILLMENT:`;
                    successMessage += `\n• Issued: ${body.will_issue || 0} items`;
                    successMessage += `\n• Backordered: ${body.will_backorder || 0} items`;
                    successMessage += `\n• Automatic purchase requests generated for shortages`;
                }
                
                successMessage += '\n\nStock levels updated.';
                alert(successMessage);
                window.location.reload();
            } else {
                throw new Error(body.message || 'Failed to process issuance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error: ' + error.message);
            
            // Reset button
            confirmButton.innerHTML = originalText;
            confirmButton.disabled = false;
        });
    }
});
</script>
@endsection