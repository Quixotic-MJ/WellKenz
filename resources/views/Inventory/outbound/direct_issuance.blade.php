@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER (RESTRICTED STYLE) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-red-50 border border-red-200 p-4 rounded-lg">
        <div>
            <h1 class="text-2xl font-bold text-red-900 flex items-center">
                <i class="fas fa-lock mr-3"></i> Direct Issuance
            </h1>
            <p class="text-sm text-red-700 mt-1">Restricted Action: Issue stock without a prior request.</p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-xs font-bold text-red-400 uppercase tracking-wider">Security Level</p>
            <p class="text-sm font-bold text-red-800">Supervisor Override Required</p>
        </div>
    </div>

    {{-- WRAP EVERYTHING IN ONE FORM --}}
    <form action="{{ route('inventory.outbound.direct.store') }}" method="POST" id="directIssuanceForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 2. ISSUANCE FORM --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-2">Issuance Details</h3>
                    
                    <div class="space-y-5">
                        
                        <!-- Recipient -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Issued To (Staff)</label>
                                <select name="issued_to" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" required>
                                    <option value="">Select Staff Member</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} - {{ $staff->profile->department ?? 'No Department' }}</option>
                                    @endforeach
                                    <option value="external">Other / External</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department / Section</label>
                                <input type="text" name="department" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="e.g. Main Kitchen" required>
                            </div>
                        </div>

                        <!-- Item Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Item</label>
                            <select name="item_id" id="itemSelect" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" required>
                                <option value="" disabled selected>Search inventory...</option>
                                @foreach($items as $item)
                                    @if($item->currentStockRecord && $item->currentStockRecord->current_quantity > 0)
                                        <option value="{{ $item->id }}" 
                                                data-current-stock="{{ $item->currentStockRecord->current_quantity }}"
                                                data-unit="{{ $item->unit->symbol }}"
                                                data-cost="{{ $item->currentStockRecord->average_cost ?? $item->cost_price }}">
                                            {{ $item->name }} ({{ $item->item_code }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="mt-2 p-3 bg-gray-50 rounded border border-gray-200 flex justify-between items-center text-sm">
                                <span class="text-gray-500">Current Availability:</span>
                                <span id="currentStockDisplay" class="font-bold text-gray-900">Select an item to view stock</span>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Issue</label>
                                <input type="number" name="quantity" id="quantityInput" step="0.001" min="0.001" 
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm font-bold text-red-600" 
                                       placeholder="0.000" required>
                                <p class="text-xs text-gray-500 mt-1" id="unitDisplay"></p>
                                <p class="text-xs text-red-600 mt-1 hidden" id="quantityError">Quantity exceeds available stock</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                                <select name="reason" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" required>
                                    <option value="emergency_production">Emergency Production</option>
                                    <option value="replacement_spoilage">Replacement (Spoilage)</option>
                                    <option value="sample_testing">Sample / Testing</option>
                                    <option value="transfer_out">Transfer Out</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Cost Information -->
                        <div class="p-3 bg-blue-50 rounded border border-blue-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-blue-600">Unit Cost:</span>
                                    <span id="unitCostDisplay" class="font-bold ml-2">--</span>
                                </div>
                                <div>
                                    <span class="text-blue-600">Total Value:</span>
                                    <span id="totalValueDisplay" class="font-bold ml-2">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                            <textarea name="remarks" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Why was a requisition not created?" required></textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- 3. SECURITY OVERRIDE PANEL (NOW INSIDE FORM) --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm p-6 h-full flex flex-col justify-center">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user-shield text-2xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Authorization</h3>
                        <p class="text-xs text-gray-500 mt-1">A supervisor must enter their PIN to authorize this direct issuance.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 text-center">Supervisor PIN</label>
                            <input type="password" name="supervisor_pin" id="supervisorPin" 
                                   class="block w-full text-center tracking-[0.5em] text-xl font-bold border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 py-3" 
                                   placeholder="••••" maxlength="4" required>
                        </div>
                        
                        <button type="button" id="authorizeButton" 
                                class="w-full py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition shadow-sm flex items-center justify-center disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-key mr-2"></i> Authorize & Issue
                        </button>

                        <div id="errorMessage" class="hidden p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700"></div>
                        
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if($errors->any())
                            <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('itemSelect');
    const currentStockDisplay = document.getElementById('currentStockDisplay');
    const unitDisplay = document.getElementById('unitDisplay');
    const quantityInput = document.getElementById('quantityInput');
    const quantityError = document.getElementById('quantityError');
    const unitCostDisplay = document.getElementById('unitCostDisplay');
    const totalValueDisplay = document.getElementById('totalValueDisplay');
    const authorizeButton = document.getElementById('authorizeButton');
    const supervisorPin = document.getElementById('supervisorPin');
    const errorMessage = document.getElementById('errorMessage');
    const form = document.getElementById('directIssuanceForm');

    let selectedItem = null;

    // Update stock display when item is selected
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const currentStock = selectedOption.getAttribute('data-current-stock');
            const unit = selectedOption.getAttribute('data-unit');
            const unitCost = selectedOption.getAttribute('data-cost');
            
            selectedItem = {
                id: selectedOption.value,
                currentStock: parseFloat(currentStock),
                unit: unit,
                unitCost: parseFloat(unitCost) || 0
            };

            currentStockDisplay.textContent = `${currentStock} ${unit}`;
            unitDisplay.textContent = `Unit: ${unit}`;
            unitCostDisplay.textContent = `₱${selectedItem.unitCost.toFixed(2)}`;
            
            // Set max quantity based on available stock
            quantityInput.max = currentStock;
            quantityInput.value = '';
            totalValueDisplay.textContent = '--';
            
            updateStockDisplay();
        } else {
            resetItemDisplay();
        }
    });

    // Update total value when quantity changes
    quantityInput.addEventListener('input', function() {
        if (selectedItem && this.value) {
            const quantity = parseFloat(this.value);
            const totalValue = quantity * selectedItem.unitCost;
            totalValueDisplay.textContent = `₱${totalValue.toFixed(2)}`;
            
            // Validate quantity
            if (quantity > selectedItem.currentStock) {
                quantityError.classList.remove('hidden');
                quantityInput.classList.add('border-red-500', 'text-red-600');
            } else {
                quantityError.classList.add('hidden');
                quantityInput.classList.remove('border-red-500', 'text-red-600');
            }
            
            updateStockDisplay();
        } else {
            totalValueDisplay.textContent = '--';
        }
    });

    // Authorization and form submission
    authorizeButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Basic validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!selectedItem) {
            showError('Please select an item to issue.');
            return;
        }

        const quantity = parseFloat(quantityInput.value);
        if (!quantity || quantity <= 0) {
            showError('Please enter a valid quantity.');
            return;
        }

        if (quantity > selectedItem.currentStock) {
            showError('Quantity exceeds available stock.');
            return;
        }

        const pin = supervisorPin.value.trim();
        if (!pin || pin.length !== 4) {
            showError('Please enter a valid 4-digit supervisor PIN.');
            return;
        }

        // Verify supervisor PIN via AJAX
        verifySupervisorPin();
    });

    function verifySupervisorPin() {
        const pin = supervisorPin.value;
        
        // Show loading state
        authorizeButton.disabled = true;
        authorizeButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';

        fetch('{{ route("inventory.outbound.verify-supervisor-pin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ pin: pin })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // PIN verified, submit the form
                hideError();
                form.submit();
            } else {
                showError(data.message || 'Invalid supervisor PIN. Please try again.');
                resetAuthorizationButton();
            }
        })
        .catch(error => {
            showError('Error verifying PIN. Please try again.');
            resetAuthorizationButton();
        });
    }

    function updateStockDisplay() {
        if (!selectedItem) return;
        
        const quantity = parseFloat(quantityInput.value) || 0;
        const remainingStock = selectedItem.currentStock - quantity;
        
        if (remainingStock < selectedItem.currentStock * 0.1) {
            currentStockDisplay.classList.add('text-red-600');
            currentStockDisplay.classList.remove('text-yellow-600', 'text-gray-900');
        } else if (remainingStock < selectedItem.currentStock * 0.3) {
            currentStockDisplay.classList.add('text-yellow-600');
            currentStockDisplay.classList.remove('text-red-600', 'text-gray-900');
        } else {
            currentStockDisplay.classList.remove('text-red-600', 'text-yellow-600');
            currentStockDisplay.classList.add('text-gray-900');
        }
    }

    function resetItemDisplay() {
        currentStockDisplay.textContent = 'Select an item to view stock';
        unitDisplay.textContent = '';
        unitCostDisplay.textContent = '--';
        totalValueDisplay.textContent = '--';
        quantityInput.value = '';
        selectedItem = null;
        quantityError.classList.add('hidden');
        quantityInput.classList.remove('border-red-500', 'text-red-600');
        currentStockDisplay.classList.remove('text-red-600', 'text-yellow-600');
        currentStockDisplay.classList.add('text-gray-900');
    }

    function resetAuthorizationButton() {
        authorizeButton.disabled = false;
        authorizeButton.innerHTML = '<i class="fas fa-key mr-2"></i> Authorize & Issue';
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }
});
</script>
@endsection