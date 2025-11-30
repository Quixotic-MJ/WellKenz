@extends('Inventory.layout.app')

@section('title', 'Return to Vendor (RTV)')

@section('content')
<div class="space-y-6 font-sans text-gray-600">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border-soft pb-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Return to Vendor</h1>
            <p class="text-sm text-gray-500">Manage rejected deliveries and product returns.</p>
        </div>
        <div>
            <button onclick="openRtvModal()" 
                    class="px-6 py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 shadow-md transition-all flex items-center gap-2">
                <i class="fas fa-undo"></i> Create New Return
            </button>
        </div>
    </div>

    {{-- 2. RTV HISTORY LIST (Simplified) --}}
    <div class="bg-white rounded-xl shadow-sm border border-border-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">RTV No.</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier / PO</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Value</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($rtvRecords as $rtv)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-chocolate">
                            {{ $rtv->rtv_number }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $rtv->return_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $rtv->supplier->name }}</div>
                            <div class="text-xs text-gray-500">Ref: {{ $rtv->purchaseOrder->po_number ?? 'Direct' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ Str::limit($rtv->rtvItems->first()->reason ?? 'N/A', 30) }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-sm font-bold text-red-600">
                            ₱{{ number_format($rtv->total_value, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold uppercase {{ $rtv->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $rtv->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                            <p>No return transactions found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $rtvRecords->links() }}
        </div>
    </div>

</div>

{{-- 3. THE "UNCOMPLICATED" CREATE MODAL --}}
<div id="rtvModal" class="fixed inset-0 z-50 hidden bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl border border-border-soft flex flex-col max-h-[90vh]">
        
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-border-soft flex justify-between items-center bg-cream-bg">
            <h3 class="font-display text-lg font-bold text-chocolate">Create Return Transaction</h3>
            <button onclick="closeRtvModal()" class="text-gray-400 hover:text-red-500">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="rtvForm" class="flex-1 overflow-hidden flex flex-col">
            @csrf
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
                
                {{-- Step 1: Select Source --}}
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <label class="block text-xs font-bold text-blue-800 uppercase mb-2">1. Select Source Purchase Order</label>
                    <select name="purchase_order_id" id="poSelect" onchange="loadPOItems(this.value)" 
                            class="w-full border-blue-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choose a PO to return items from --</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->supplier->name }} ({{ $po->order_date->format('M d') }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="supplier_id" id="supplierId"> </div>

                {{-- Step 2: The Items Table (Hidden until PO selected) --}}
                <div id="itemsSection" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase">2. Select Items to Return</label>
                        <div class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            <i class="fas fa-info-circle mr-1"></i>
                            Enter quantities for items you want to return
                        </div>
                    </div>
                    <div class="border border-border-soft rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Item</th>
                                    <th class="px-4 py-2 text-right">Cost</th>
                                    <th class="px-4 py-2 text-center w-24">Purchased</th>
                                    <th class="px-4 py-2 text-center w-32">Return Qty</th>
                                    <th class="px-4 py-2 text-left w-1/3">Reason</th>
                                </tr>
                            </thead>
                            <tbody id="poItemsContainer" class="bg-white divide-y divide-gray-100 text-sm">
                                {{-- JS will inject rows here --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right">
                        <span class="text-sm text-gray-500 mr-2">Total Refund Value:</span>
                        <span class="text-xl font-bold text-red-600" id="totalRefundDisplay">₱0.00</span>
                        <input type="hidden" name="total_value" id="totalValueInput" value="0">
                    </div>
                </div>

                {{-- Basic Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">3. Return Date</label>
                        <input type="date" 
                               name="return_date" 
                               value="{{ date('Y-m-d') }}"
                               class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">General Notes</label>
                        <textarea name="notes" class="w-full border-gray-200 rounded-lg text-sm" rows="2" placeholder="Any additional details..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-border-soft flex justify-end gap-3">
                <button type="button" onclick="closeRtvModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 text-sm font-bold">Cancel</button>
                <button type="button" onclick="submitRtvForm()" id="submitBtn" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-bold shadow-sm">
                    <i class="fas fa-spinner fa-spin hidden" id="submitSpinner"></i>
                    <span id="submitText">Submit Return</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Success Modal --}}
<div id="successModal" class="fixed inset-0 z-50 hidden bg-green-500/10 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-green-200">
        <div class="px-6 py-4 border-b border-green-100 flex justify-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>
        <div class="px-6 py-6 text-center">
            <h3 class="font-display text-xl font-bold text-green-800 mb-2">Return Created Successfully!</h3>
            <p class="text-gray-600 mb-4">Your RTV transaction has been processed.</p>
            <div class="bg-green-50 rounded-lg p-4 mb-4">
                <div class="text-sm text-green-800">
                    <div class="font-bold" id="successRtvNumber">RTV-2025-0001</div>
                    <div class="text-green-600">Return to Vendor Transaction</div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-green-50 border-t border-green-100 flex justify-center gap-3">
            <button onclick="closeSuccessModal()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-bold shadow-sm">
                <i class="fas fa-check mr-2"></i>Done
            </button>
        </div>
    </div>
</div>

<script>
    // 0. Form Validation
    function validateForm() {
        // Check if a purchase order is selected
        const poSelect = document.getElementById('poSelect');
        if (!poSelect.value) {
            alert('Please select a Purchase Order first.');
            poSelect.focus();
            return false;
        }

        // Check if any items have quantities entered
        const itemsSection = document.getElementById('itemsSection');
        if (itemsSection.classList.contains('hidden')) {
            alert('Please load items from the selected Purchase Order first.');
            return false;
        }

        // Check if at least one item has a quantity entered
        const quantityInputs = document.querySelectorAll('input[name*="quantity_returned"]');
        let hasQuantity = false;
        
        quantityInputs.forEach(input => {
            if (parseFloat(input.value) > 0) {
                hasQuantity = true;
            }
        });

        if (!hasQuantity) {
            alert('Please enter at least one item quantity to return.');
            return false;
        }

        // Check if return date is selected
        const returnDate = document.querySelector('input[name="return_date"]');
        if (!returnDate.value) {
            alert('Please select a return date.');
            returnDate.focus();
            return false;
        }

        return true;
    }

    // 1. Open/Close Modal
    function openRtvModal() {
        document.getElementById('rtvModal').classList.remove('hidden');
    }
    function closeRtvModal() {
        document.getElementById('rtvModal').classList.add('hidden');
        document.getElementById('rtvForm').reset();
        document.getElementById('itemsSection').classList.add('hidden');
        
        // Reset return date to today
        const returnDateInput = document.querySelector('input[name="return_date"]');
        if (returnDateInput) {
            returnDateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    // Success Modal Functions
    function openSuccessModal(rtvNumber) {
        document.getElementById('successRtvNumber').textContent = rtvNumber;
        document.getElementById('successModal').classList.remove('hidden');
    }
    
    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
        // Optionally refresh the page to show the new RTV in the list
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    // 2. Load Items from Backend
    async function loadPOItems(poId) {
        if (!poId) {
            document.getElementById('itemsSection').classList.add('hidden');
            return;
        }

        const container = document.getElementById('poItemsContainer');
        container.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-400"><i class="fas fa-spinner fa-spin"></i> Loading items...</td></tr>';
        document.getElementById('itemsSection').classList.remove('hidden');

        try {
            // NOTE: Create this route in your web.php: Route::get('/inventory/inbound/rtv/po-items/{id}', ...)
            const response = await fetch(`/inventory/inbound/rtv/po-items/${poId}`);
            const data = await response.json();

            // Set hidden supplier ID
            document.getElementById('supplierId').value = data.supplier_id;

            container.innerHTML = '';
            
            data.items.forEach((item, index) => {
                const html = `
                    <tr class="group hover:bg-red-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-gray-800">${item.item_name}</div>
                            <div class="text-xs text-gray-400">${item.item_code}</div>
                            <input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            ₱${item.unit_price}
                            <input type="hidden" id="price_${index}" name="items[${index}][unit_cost]" value="${item.unit_price}">
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            ${item.quantity_ordered}
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="items[${index}][quantity_returned]" 
                                   class="w-full text-center border-gray-200 rounded text-sm focus:border-red-500 focus:ring-red-500 font-bold text-red-600 bg-white"
                                   placeholder="0"
                                   min="0"
                                   max="${item.quantity_ordered}"
                                   step="0.01"
                                   oninput="calculateTotal()">
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" 
                                   name="items[${index}][reason]" 
                                   class="w-full border-gray-200 rounded text-xs focus:border-red-500 focus:ring-red-500"
                                   placeholder="Why returning?">
                        </td>
                    </tr>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });

        } catch (e) {
            console.error(e);
            container.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-500">Error loading items</td></tr>';
        }
    }

    // 3. Submit Form via AJAX
    async function submitRtvForm() {
        // Validate form first
        if (!validateForm()) {
            return;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitText = document.getElementById('submitText');
        
        submitBtn.disabled = true;
        submitSpinner.classList.remove('hidden');
        submitText.textContent = 'Processing...';

        try {
            const formData = new FormData(document.getElementById('rtvForm'));
            
            const response = await fetch('{{ route('inventory.inbound.rtv.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Close the RTV modal
                closeRtvModal();
                
                // Show success modal
                openSuccessModal(data.rtv_number);
            } else {
                // Show error
                alert('Error: ' + (data.message || 'Failed to create RTV transaction'));
            }

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitSpinner.classList.add('hidden');
            submitText.textContent = 'Submit Return';
        }
    }

    // 4. Auto-Calculate Total Value
    function calculateTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#poItemsContainer tr');
        
        rows.forEach((row, index) => {
            const price = parseFloat(document.getElementById(`price_${index}`).value) || 0;
            const qtyInput = row.querySelector(`input[name="items[${index}][quantity_returned]"]`);
            const qty = parseFloat(qtyInput.value) || 0;
            
            if(qty > 0) {
                total += (price * qty);
                // Visual feedback
                row.classList.add('bg-red-50');
            } else {
                row.classList.remove('bg-red-50');
            }
        });

        document.getElementById('totalRefundDisplay').textContent = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('totalValueInput').value = total;
    }
</script>
@endsection