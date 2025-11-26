@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fulfill Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Pick and issue stock for approved kitchen requisitions.</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $readyToPickCount = $requisitions->where('status', 'approved')->count();
            @endphp
            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
                {{ $readyToPickCount }} Ready to Pick
            </span>
        </div>
    </div>

    {{-- 2. WORKFLOW GUIDE --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Select Batches</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Physically Pick Items</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-700">Confirm Issuance</span>
                </div>
            </div>
            <div class="text-xs text-blue-700 font-medium">
                <i class="fas fa-info-circle mr-1"></i>
                Mark items as picked only after physical verification
            </div>
        </div>
    </div>

    {{-- 3. REQUESTS QUEUE --}}
    <div class="space-y-4">

        @foreach($requisitions as $index => $requisition)
            @php
                $isExpanded = $index === 0 && $requisition->status === 'approved';
                $itemCount = $requisition->requisitionItems->count();
                $isApproved = $requisition->status === 'approved';
                $isPending = $requisition->status === 'pending';
                $isFulfilled = $requisition->status === 'fulfilled';
                
                // Calculate progress for progress bar
                $pickedCount = 0;
                if($isExpanded) {
                    foreach($requisition->requisitionItems as $item) {
                        if(session("picking.{$item->id}")) {
                            $pickedCount++;
                        }
                    }
                }
                $progressPercentage = $itemCount > 0 ? ($pickedCount / $itemCount) * 100 : 0;
            @endphp

            <div class="bg-white border {{ $isExpanded ? 'border-blue-200' : 'border-gray-200' }} rounded-lg {{ $isExpanded ? 'shadow-md' : 'shadow-sm hover:shadow-md' }} transition overflow-hidden {{ !$isExpanded ? 'opacity-75' : '' }}" data-requisition-id="{{ $requisition->id }}">
                
                @if($isExpanded)
                    {{-- Expanded View for Approved Requisitions --}}
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-4">
                                <span class="font-mono font-bold text-blue-700 bg-white px-2 py-1 rounded border border-blue-200">
                                    #{{ $requisition->requisition_number }}
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">
                                        {{ $requisition->requestedBy->name ?? 'Unknown User' }}
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $requisition->department ?? 'General' }} - {{ $requisition->purpose }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase font-bold">Required By</p>
                                <p class="text-sm font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse($requisition->request_date)->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Progress Bar --}}
                        <div class="mt-2">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Picking Progress</span>
                                <span id="progress-text-{{ $requisition->id }}">{{ $pickedCount }} of {{ $itemCount }} items</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="progress-bar-{{ $requisition->id }}" 
                                     class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $progressPercentage }}%">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                                <span class="text-sm text-yellow-700 font-medium">Important: Verify each item physically before marking as picked</span>
                            </div>
                        </div>

                        <table class="min-w-full divide-y divide-gray-100 mb-4">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Requested Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Suggested Batch (FEFO)</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Physical Verification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($requisition->requisitionItems as $requisitionItem)
                                    @php
                                        $item = $requisitionItem->item;
                                        $availableBatches = \App\Models\Batch::where('item_id', $item->id)
                                            ->whereIn('status', ['active', 'quarantine'])
                                            ->where('quantity', '>', 0)
                                            ->orderBy('expiry_date', 'asc')
                                            ->get();
                                        $isPicked = session("picking.{$requisitionItem->id}");
                                    @endphp
                                    <tr class="{{ $isPicked ? 'bg-green-50' : '' }}" data-requisition-item-id="{{ $requisitionItem->id }}">
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-bold text-gray-900">{{ $item->name }}</p>
                                            <p class="text-xs text-gray-500">SKU: {{ $item->item_code }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-chocolate">
                                            {{ number_format($requisitionItem->quantity_requested, 2) }} {{ $item->unit->symbol ?? 'pcs' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($availableBatches->count() > 0)
                                                <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs batch-select" 
                                                    data-item-id="{{ $item->id }}"
                                                    data-requisition-item-id="{{ $requisitionItem->id }}"
                                                    required>
                                                    <option value="">Select Batch</option>
                                                    @foreach($availableBatches as $batch)
                                                        @php
                                                            $daysUntilExpiry = \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now());
                                                            $isExpiringSoon = $daysUntilExpiry <= 7;
                                                        @endphp
                                                        <option value="{{ $batch->id }}" 
                                                                data-quantity="{{ $batch->quantity }}"
                                                                data-expiry="{{ $batch->expiry_date }}"
                                                                {{ $isExpiringSoon ? 'selected' : '' }}>
                                                            Batch #{{ $batch->batch_number }} 
                                                            (Exp: {{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d') }}) 
                                                            - Qty: {{ number_format($batch->quantity, 2) }} 
                                                            - Loc: {{ $batch->location ?? 'Main' }}
                                                            {{ $isExpiringSoon ? ' ⚠️' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="text-xs text-red-600">No batches available</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <label class="inline-flex items-center cursor-pointer transition-all duration-200">
                                                    <input type="checkbox" 
                                                           class="form-checkbox h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500 pick-item-checkbox transition-all"
                                                           data-requisition-item-id="{{ $requisitionItem->id }}"
                                                           data-item-id="{{ $item->id }}"
                                                           data-requisition-id="{{ $requisition->id }}"
                                                           data-quantity="{{ $requisitionItem->quantity_requested }}"
                                                           {{ $isPicked ? 'checked' : '' }}>
                                                    <span class="ml-2 text-xs font-medium pick-status {{ $isPicked ? 'text-green-600 font-bold' : 'text-gray-700' }}">
                                                        {{ $isPicked ? '✓ Physically Verified' : 'Mark as Physically Picked' }}
                                                    </span>
                                                </label>
                                                @if($isPicked)
                                                    <span class="text-green-500 text-xs">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                            <div class="text-sm text-gray-500">
                                <span id="picked-count-{{ $requisition->id }}">{{ $pickedCount }}</span> of {{ $itemCount }} items physically verified
                            </div>
                            <div class="flex items-center space-x-3">
                                <button class="px-4 py-2 bg-gray-500 text-white text-sm font-bold rounded-lg hover:bg-gray-600 shadow-sm flex items-center clear-picking-btn"
                                        data-requisition-id="{{ $requisition->id }}"
                                        title="Clear all picking selections">
                                    <i class="fas fa-undo mr-2"></i> Reset
                                </button>
                                <button class="px-6 py-2 bg-gray-400 text-white text-sm font-bold rounded-lg shadow-sm flex items-center confirm-issuance-btn transition-all"
                                        data-requisition-id="{{ $requisition->id }}"
                                        disabled
                                        title="Confirm issuance after all items are physically verified">
                                    <i class="fas fa-box-open mr-2"></i> Confirm Issuance
                                </button>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Collapsed View --}}
                    <div class="px-6 py-4 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <span class="font-mono font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                #{{ $requisition->requisition_number }}
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">
                                    {{ $requisition->requestedBy->name ?? 'Unknown User' }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    {{ $requisition->department ?? 'General' }} - {{ Str::limit($requisition->purpose, 50) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                {{ $itemCount }} {{ Str::plural('Item', $itemCount) }}
                            </span>
                            @if($isApproved)
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Ready to Pick</span>
                                <a href="{{ route('inventory.outbound.fulfill', ['expand' => $requisition->id]) }}" 
                                   class="text-chocolate hover:text-chocolate-dark font-bold text-sm">
                                    Expand <i class="fas fa-chevron-down ml-1"></i>
                                </a>
                            @elseif($isPending)
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending Approval</span>
                            @elseif($isFulfilled)
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Fulfilled</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        @if($requisitions->isEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                <i class="fas fa-inbox text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Requisitions to Fulfill</h3>
                <p class="text-gray-500">There are currently no approved requisitions ready for picking.</p>
            </div>
        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track picked items with enhanced visual feedback
    document.querySelectorAll('.pick-item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const requisitionItemId = this.dataset.requisitionItemId;
            const requisitionId = this.dataset.requisitionId;
            const isPicked = this.checked;
            
            // Update the visual state with enhanced feedback
            updatePickedUI(this, isPicked);
            
            // Update progress tracking
            updateProgressTracking(requisitionId);
            
            // Validate if issuance is ready
            validateIssuanceReady(requisitionId);
            
            // Store picking status in session
            trackPickingStatus(requisitionItemId, isPicked);
        });
    });

    // Clear picking selections
    document.querySelectorAll('.clear-picking-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requisitionId = this.dataset.requisitionId;
            clearPickingSelections(requisitionId);
        });
    });

    // Confirm issuance
    document.querySelectorAll('.confirm-issuance-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requisitionId = this.dataset.requisitionId;
            confirmIssuance(requisitionId);
        });
    });

    // Batch selection change
    document.querySelectorAll('.batch-select').forEach(select => {
        select.addEventListener('change', function() {
            const requisitionId = this.closest('[data-requisition-id]').dataset.requisitionId;
            validateIssuanceReady(requisitionId);
        });
    });

    function updatePickedUI(checkbox, isPicked) {
        const statusText = checkbox.closest('label').querySelector('.pick-status');
        const row = checkbox.closest('tr');
        
        if (isPicked) {
            statusText.textContent = '✓ Physically Verified';
            statusText.classList.add('text-green-600', 'font-bold');
            statusText.classList.remove('text-gray-700');
            row.classList.add('bg-green-50');
            
            // Add success animation
            row.style.transition = 'all 0.3s ease';
            row.style.transform = 'scale(1.02)';
            setTimeout(() => {
                row.style.transform = 'scale(1)';
            }, 300);
        } else {
            statusText.textContent = 'Mark as Physically Picked';
            statusText.classList.remove('text-green-600', 'font-bold');
            statusText.classList.add('text-gray-700');
            row.classList.remove('bg-green-50');
        }
    }

    function updateProgressTracking(requisitionId) {
        const requisitionElement = document.querySelector(`[data-requisition-id="${requisitionId}"]`);
        const checkboxes = requisitionElement.querySelectorAll('.pick-item-checkbox');
        const pickedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const totalCount = checkboxes.length;
        
        // Update counter
        const countElement = document.getElementById(`picked-count-${requisitionId}`);
        if (countElement) {
            countElement.textContent = pickedCount;
        }
        
        // Update progress text
        const progressText = document.getElementById(`progress-text-${requisitionId}`);
        if (progressText) {
            progressText.textContent = `${pickedCount} of ${totalCount} items`;
        }
        
        // Update progress bar
        const progressBar = document.getElementById(`progress-bar-${requisitionId}`);
        if (progressBar) {
            const progressPercentage = totalCount > 0 ? (pickedCount / totalCount) * 100 : 0;
            progressBar.style.width = `${progressPercentage}%`;
            
            // Change color based on progress
            if (progressPercentage === 100) {
                progressBar.classList.remove('bg-green-600');
                progressBar.classList.add('bg-green-500');
            } else if (progressPercentage >= 50) {
                progressBar.classList.remove('bg-green-500');
                progressBar.classList.add('bg-green-600');
            } else {
                progressBar.classList.remove('bg-green-600');
                progressBar.classList.add('bg-green-400');
            }
        }
    }

    function validateIssuanceReady(requisitionId) {
        const requisitionElement = document.querySelector(`[data-requisition-id="${requisitionId}"]`);
        const checkboxes = requisitionElement.querySelectorAll('.pick-item-checkbox');
        const batchSelects = requisitionElement.querySelectorAll('.batch-select');
        
        const allPicked = Array.from(checkboxes).every(cb => cb.checked);
        const allBatchesSelected = Array.from(batchSelects).every(select => select.value);
        
        const confirmButton = requisitionElement.querySelector('.confirm-issuance-btn');
        if (confirmButton) {
            const isReady = allPicked && allBatchesSelected;
            confirmButton.disabled = !isReady;
            
            // Update button appearance with better visual feedback
            if (isReady) {
                confirmButton.classList.remove('bg-gray-400');
                confirmButton.classList.add('bg-green-600', 'hover:bg-green-700', 'shadow-md');
                confirmButton.title = 'Ready to confirm issuance - all items verified';
            } else {
                confirmButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'shadow-md');
                confirmButton.classList.add('bg-gray-400');
                confirmButton.title = 'Complete physical verification of all items first';
            }
        }
    }

    function trackPickingStatus(requisitionItemId, isPicked) {
        fetch("/inventory/outbound/track-picking", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                requisition_item_id: requisitionItemId,
                picked: isPicked
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to track picking status:', data.message);
                // Revert the UI if saving failed
                const checkbox = document.querySelector(`[data-requisition-item-id="${requisitionItemId}"]`);
                if (checkbox) {
                    checkbox.checked = !isPicked;
                    updatePickedUI(checkbox, !isPicked);
                }
            }
        })
        .catch(error => {
            console.error('Error tracking picking:', error);
        });
    }

    function clearPickingSelections(requisitionId) {
        if (!confirm('Are you sure you want to clear all picking selections? This will reset all physical verification checkmarks.')) {
            return;
        }

        const requisitionElement = document.querySelector(`[data-requisition-id="${requisitionId}"]`);
        const checkboxes = requisitionElement.querySelectorAll('.pick-item-checkbox');
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                updatePickedUI(checkbox, false);
                
                // Clear from session
                trackPickingStatus(checkbox.dataset.requisitionItemId, false);
            }
        });
        
        updateProgressTracking(requisitionId);
        validateIssuanceReady(requisitionId);
    }

    function confirmIssuance(requisitionId) {
        if (!confirm('FINAL CONFIRMATION: Have you physically verified ALL items and are ready to issue stock to the kitchen? This action cannot be undone and will update inventory records.')) {
            return;
        }

        const requisitionElement = document.querySelector(`[data-requisition-id="${requisitionId}"]`);
        
        // Collect all batch selections for this requisition
        const batchSelections = {};
        let hasMissingBatches = false;
        let missingItems = [];

        requisitionElement.querySelectorAll('.batch-select').forEach(select => {
            const requisitionItemId = select.dataset.requisitionItemId;
            const itemName = select.closest('tr').querySelector('p.text-sm').textContent;
            
            if (select.value) {
                batchSelections[requisitionItemId] = select.value;
            } else {
                hasMissingBatches = true;
                missingItems.push(itemName.trim());
            }
        });

        // Validate that all items have batch selections
        if (hasMissingBatches) {
            alert('Cannot proceed: Please select batches for all items:\n\n' + missingItems.join('\n'));
            return;
        }

        // Validate that all items are picked
        const checkboxes = requisitionElement.querySelectorAll('.pick-item-checkbox');
        const unpickedItems = [];
        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                const itemName = checkbox.closest('tr').querySelector('p.text-sm').textContent;
                unpickedItems.push(itemName.trim());
            }
        });

        if (unpickedItems.length > 0) {
            alert('Cannot proceed: Please physically verify all items before issuance:\n\n' + unpickedItems.join('\n'));
            return;
        }

        // Prepare the request data
        const requestData = {
            requisition_id: requisitionId,
            batch_selections: batchSelections
        };

        // Show loading state
        const confirmButton = requisitionElement.querySelector('.confirm-issuance-btn');
        const originalText = confirmButton.innerHTML;
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing Issuance...';
        confirmButton.disabled = true;

        // Send the request
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
                alert('✅ Issuance confirmed successfully! Stock has been updated and requisition marked as fulfilled.');
                window.location.reload();
            } else {
                throw new Error(body.message || 'Failed to confirm issuance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error: ' + error.message);
            
            // Reset button state
            confirmButton.innerHTML = originalText;
            confirmButton.disabled = false;
        });
    }

    // Initialize all requisitions
    document.querySelectorAll('[data-requisition-id]').forEach(requisitionElement => {
        const requisitionId = requisitionElement.dataset.requisitionId;
        updateProgressTracking(requisitionId);
        validateIssuanceReady(requisitionId);
    });
});
</script>
@endsection