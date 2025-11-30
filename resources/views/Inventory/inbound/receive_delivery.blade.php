@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600 pb-24">

    {{-- 1. HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="bg-gradient-to-br from-chocolate to-caramel rounded-2xl p-4 shadow-lg text-white transform rotate-3">
                <i class="fas fa-boxes-packing text-3xl"></i>
            </div>
            <div>
                <h1 class="font-display text-3xl font-bold text-chocolate mb-1 tracking-tight">
                    Incoming Shipments
                </h1>
                <p class="text-sm text-gray-500">Process deliveries directly into inventory.</p>
            </div>
        </div>
      
    </div>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- 2. MAIN GRID --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
        
        {{-- LEFT COLUMN: SELECTION & LIST --}}
        <div class="xl:col-span-9 space-y-6">
            
            {{-- PO SELECTION --}}
            <div class="bg-white rounded-2xl shadow-sm border border-border-soft p-6 flex flex-col md:flex-row gap-6 items-center">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Select Pending Purchase Order</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                        </div>
                        <select id="purchaseOrderSelect" 
                                class="block w-full pl-11 pr-10 py-3 border-gray-200 bg-cream-bg/30 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer transition-all font-medium"
                                onchange="handlePurchaseOrderSelect(this)">
                            <option value="">Choose a PO number...</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}">
                                    {{ $po->po_number }} • {{ $po->supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                {{-- PO Info Badges (Hidden until selected) --}}
                <div id="po-meta-info" class="hidden flex-shrink-0 flex gap-4">
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-gray-400">Supplier</div>
                        <div class="font-bold text-gray-800" id="meta-supplier">-</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-gray-400">Expected</div>
                        <div class="font-bold text-chocolate" id="meta-date">-</div>
                    </div>
                </div>
            </div>

            {{-- EMPTY STATE --}}
            <div id="emptyState" class="border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-arrow-up text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-400 font-medium">Select a purchase order above to view items.</p>
            </div>

            {{-- RECEIVING TABLE --}}
            <div id="deliveryDetailsSection" class="hidden animate-fade-in-up">
                <form id="receiveDeliveryForm" class="bg-white rounded-2xl shadow-lg border border-border-soft overflow-hidden">
                    @csrf
                    <input type="hidden" id="purchase_order_id" name="purchase_order_id">

                    {{-- Toolbar --}}
                    <div class="bg-cream-bg/50 px-6 py-3 border-b border-border-soft flex justify-between items-center">
                        <div class="text-xs font-bold text-chocolate uppercase tracking-wider">Items to Receive</div>
                        <button type="button" onclick="receiveAllRemaining()" 
                                class="text-xs font-bold bg-white border border-green-200 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-50 transition-colors shadow-sm flex items-center">
                            <i class="fas fa-check-double mr-1.5"></i> Receive All Remaining
                        </button>
                    </div>

                    {{-- Table Header --}}
                    <div class="hidden md:grid grid-cols-12 gap-4 bg-gray-50/50 px-6 py-2 border-b border-gray-100 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                        <div class="col-span-4">Item</div>
                        <div class="col-span-2 text-center">Status</div>
                        <div class="col-span-2 text-right">Ordered</div>
                        <div class="col-span-4 pl-4">Receive Now</div>
                    </div>

                    {{-- Table Body --}}
                    <div id="deliveryItemsContainer" class="divide-y divide-gray-100">
                        {{-- Rows injected by JS --}}
                    </div>

                    {{-- Footer Actions --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100 sticky bottom-0 z-10">
                        <button type="button" onclick="resetForm()" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm">
                            Reset
                        </button>
                        <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-xl hover:shadow-lg hover:to-green-600 transition-all transform hover:-translate-y-0.5 text-sm flex items-center">
                            <i class="fas fa-save mr-2"></i> Update Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT COLUMN: SUMMARY --}}
        <div class="xl:col-span-3 space-y-6 sticky top-6">
            <div class="bg-white rounded-2xl shadow-sm border border-border-soft p-6">
                <h3 class="font-display font-bold text-gray-800 mb-4">Receipt Summary</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-xs font-bold text-gray-500 uppercase">Total Items</span>
                        <span class="font-bold text-gray-900" id="summaryTotalItems">0</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <span class="text-xs font-bold text-blue-600 uppercase">Receiving Now</span>
                        <span class="font-bold text-blue-800" id="summaryReceivingCount">0</span>
                    </div>

                    <div class="h-px bg-gray-100 my-2"></div>

                    <div class="text-center">
                        <div class="text-xs text-gray-400 mb-1">Estimated Value</div>
                        <div class="text-2xl font-bold text-chocolate" id="summaryValue">₱0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SUCCESS MODAL --}}
<div id="successModal" class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-border-soft p-6 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600 text-3xl">
            <i class="fas fa-check"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Inventory Updated</h3>
        <p class="text-sm text-gray-500 mb-6">The quantities have been added to your stock levels successfully.</p>
        <button onclick="location.reload()" class="w-full px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all">
            Process Next
        </button>
    </div>
</div>

@push('styles')
<style>
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    /* Visual cue for completed rows */
    .row-completed { background-color: #f0fdf4 !important; } /* green-50 */
    .row-completed input { border-color: #86efac !important; background-color: white !important; }
</style>
@endpush

@push('scripts')
<script>
    let currentPoData = null;

    /**
     * Generate a simplified batch number in the format: BATCH-XXX-YEAR-ZZ
     * @param {Object} item - The item object containing category, name, and other data
     * @param {string|number} poId - The purchase order ID for uniqueness
     * @returns {string} A simplified batch number
     */
    function generateValidBatchNumber(item, poId) {
        const now = new Date();
        const year = now.getFullYear(); // 4-digit year
        
        // Category code mapping for simplified 3-letter codes
        const categoryMap = {
            'FLOUR': 'FLR', 'FLOURS': 'FLR',
            'SUGAR': 'SUG', 'SUGARS': 'SUG',
            'BUTTER': 'BTR', 'DAIRY': 'DAI',
            'EGGS': 'EGG', 'MEAT': 'MEA',
            'VEGETABLES': 'VEG', 'VEGETABLE': 'VEG',
            'FRUIT': 'FRU', 'FRUITS': 'FRU',
            'BAKING': 'BAK', 'SEASONING': 'SEA',
            'OIL': 'OIL', 'OILS': 'OIL'
        };
        
        // Get category code (fallback to GEN)
        const categoryName = (item.category_name || 'GENERAL').toUpperCase();
        let categoryCode = categoryMap[categoryName] || 'GEN';
        
        // Generate simple sequential number (01-99)
        const timestamp = now.getTime().toString().slice(-2);
        const itemId = String(item.id).padStart(2, '0').slice(-2);
        const sequentialNumber = String((parseInt(timestamp) + parseInt(itemId)) % 100).padStart(2, '0');
        
        // Construct batch number: BATCH-XXX-YEAR-ZZ
        const batchNum = `BATCH-${categoryCode}-${year}-${sequentialNumber}`;
        
        return batchNum;
    }

    function handlePurchaseOrderSelect(selectElement) {
        const poId = selectElement.value;
        loadPurchaseOrder(poId);
    }

    async function loadPurchaseOrder(poId) {
        if (!poId) {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('deliveryDetailsSection').classList.add('hidden');
            document.getElementById('po-meta-info').classList.add('hidden');
            return;
        }

        // Show loading state
        const selectElement = document.getElementById('purchaseOrderSelect');
        selectElement.disabled = true;
        selectElement.style.opacity = '0.6';

        try {
            console.log('Loading PO with ID:', poId, typeof poId);
            const url = `{{ route('inventory.inbound.purchase-orders.receive', ['id' => '__ID__']) }}`.replace('__ID__', poId);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();
            console.log('API Response:', result);

            if (result.success) {
                currentPoData = result.data;
                renderInterface(currentPoData);
            } else {
                throw new Error(result.message || 'Failed to load purchase order data');
            }
        } catch (e) {
            console.error('Error loading PO data:', e);
            alert("Error loading PO data: " + e.message);
            
            // Reset UI
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('deliveryDetailsSection').classList.add('hidden');
            document.getElementById('po-meta-info').classList.add('hidden');
        } finally {
            // Restore select element
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
        }
    }

    // Debug function to check available POs
    function debugAvailablePOs() {
        const select = document.getElementById('purchaseOrderSelect');
        console.log('Available Purchase Orders:');
        for (let i = 0; i < select.options.length; i++) {
            const option = select.options[i];
            console.log(`ID: ${option.value}, Text: ${option.text}`);
        }
    }

    // Call debug function on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(debugAvailablePOs, 1000);
    });

    function renderInterface(data) {
        // 1. Update Meta Headers
        document.getElementById('meta-supplier').textContent = data.supplier_name;
        document.getElementById('meta-date').textContent = new Date(data.expected_delivery_date).toLocaleDateString();
        document.getElementById('po-meta-info').classList.remove('hidden');
        document.getElementById('purchase_order_id').value = data.id;

        // 2. Render Rows
        const container = document.getElementById('deliveryItemsContainer');
        container.innerHTML = '';

        data.items.forEach(item => {
            const remaining = item.quantity_remaining;
            const isFullyReceived = remaining <= 0;
            const rowClass = isFullyReceived ? 'bg-gray-50 opacity-75' : 'bg-white hover:bg-blue-50/20';
            
            // Auto-generate unique batch number with validation
            const batchNum = generateValidBatchNumber(item, data.id);

            const html = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 items-center transition-colors ${rowClass}" id="row-${item.id}">
                    
                    {{-- ITEM INFO --}}
                    <div class="md:col-span-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white border border-border-soft flex items-center justify-center text-chocolate shadow-sm">
                            <i class="fas fa-box"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800 text-sm">${item.item_name}</div>
                            <div class="text-[10px] text-gray-400 font-mono">${item.sku || 'N/A'}</div>
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="md:col-span-2 text-center">
                        ${isFullyReceived 
                            ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase">Completed</span>'
                            : `<span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded text-[10px] font-bold uppercase">Pending (${remaining})</span>`
                        }
                    </div>

                    {{-- ORDERED / PREV --}}
                    <div class="md:col-span-2 text-right text-xs">
                        <div class="font-bold text-gray-800">${item.quantity_ordered} <span class="text-gray-400">${item.unit_symbol || ''}</span></div>
                        <div class="text-gray-400 text-[10px]">Prev: ${item.quantity_received}</div>
                    </div>

                    {{-- INPUT ACTION --}}
                    <div class="md:col-span-4 pl-4 border-l border-gray-100">
                        <div class="flex gap-2 items-start">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Receive Now</label>
                                <input type="number" 
                                       id="input-${item.id}"
                                       name="items[${item.id}][quantity_received]" 
                                       class="w-full border-gray-200 rounded-lg text-sm font-bold text-chocolate focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                       placeholder="0"
                                       min="0"
                                       max="${remaining}"
                                       ${isFullyReceived ? 'disabled' : ''}
                                       oninput="updateCalculation(${item.id}, ${remaining}, ${item.unit_cost})">
                                
                                {{-- Hidden fields required for backend processing --}}
                                <input type="hidden" name="items[${item.id}][purchase_order_item_id]" value="${item.id}">
                                <input type="hidden" name="items[${item.id}][batch_number]" value="${batchNum}">
                                <input type="hidden" name="items[${item.id}][condition]" value="good">
                                
                                ${item.is_perishable ? `
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 mt-2">Expiry Date</label>
                                    <input type="date" 
                                           name="items[${item.id}][expiry_date]" 
                                           class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500 focus:border-green-500"
                                           min="${new Date().toISOString().split('T')[0]}"
                                           onchange="validateExpiryDate(this, '${item.item_name}')">
                                ` : ''}
                            </div>
                            
                            ${!isFullyReceived ? `
                                <button type="button" onclick="fillRow(${item.id}, ${remaining}, ${item.unit_cost})" 
                                        class="mt-6 px-3 py-2 bg-green-50 text-green-700 rounded-lg text-xs font-bold hover:bg-green-100 transition-colors"
                                        title="Receive All Remaining">
                                    ALL
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });

        // Show UI
        document.getElementById('emptyState').classList.add('hidden');
        document.getElementById('deliveryDetailsSection').classList.remove('hidden');
        updateSummary();
    }

    // --- LOGIC ---

    function fillRow(id, amount, price) {
        const input = document.getElementById(`input-${id}`);
        input.value = amount;
        updateCalculation(id, amount, price);
        
        // Visual Feedback
        input.classList.add('bg-green-50');
        setTimeout(() => input.classList.remove('bg-green-50'), 300);
    }

    function receiveAllRemaining() {
        if(!currentPoData) return;
        currentPoData.items.forEach(item => {
            const remaining = item.quantity_remaining;
            if (remaining > 0) {
                fillRow(item.id, remaining, item.unit_cost);
            }
        });
    }

    function updateCalculation(id, max, price) {
        const input = document.getElementById(`input-${id}`);
        const row = document.getElementById(`row-${id}`);
        let val = parseFloat(input.value) || 0;

        // Validation Visuals
        if (val > max) {
            input.classList.add('text-red-600', 'border-red-300');
        } else if (val === max) {
            input.classList.remove('text-red-600', 'border-red-300');
            row.classList.add('row-completed');
        } else {
            input.classList.remove('text-red-600', 'border-red-300');
            row.classList.remove('row-completed');
        }

        updateSummary();
    }

    function validateExpiryDate(input, itemName) {
        const selectedDate = new Date(input.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert(`Expiry date for ${itemName} cannot be in the past`);
            input.value = '';
            return false;
        }
        return true;
    }

    function updateSummary() {
        const inputs = document.querySelectorAll('input[type="number"]:not(:disabled)');
        let count = 0;
        let value = 0;

        inputs.forEach(input => {
            const val = parseFloat(input.value) || 0;
            if (val > 0) {
                count++;
                // Find item price in data
                const itemId = input.id.split('-')[1];
                const item = currentPoData.items.find(i => i.id == itemId);
                if(item) value += (val * item.unit_cost);
            }
        });

        document.getElementById('summaryTotalItems').textContent = currentPoData.items.length;
        document.getElementById('summaryReceivingCount').textContent = count;
        document.getElementById('summaryValue').textContent = '₱' + value.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    function resetForm() {
        document.getElementById('receiveDeliveryForm').reset();
        document.querySelectorAll('.row-completed').forEach(el => el.classList.remove('row-completed'));
        updateSummary();
    }

    /**
     * Validate batch numbers before submission
     * @param {Object} itemsData - The form data being submitted
     * @returns {Array} Array of validation errors (empty if valid)
     */
    function validateBatchNumbers(itemsData) {
        const errors = [];
        
        for (const [itemId, itemData] of Object.entries(itemsData)) {
            if (itemData.quantity_received && parseFloat(itemData.quantity_received) > 0) {
                const batchNumber = itemData.batch_number;
                
                // Check for forbidden patterns
                if (batchNumber.match(/^(N\/A|NA)-/)) {
                    errors.push(`Item ${itemId}: Batch number "${batchNumber}" starts with invalid pattern. Please refresh the page to generate a new batch number.`);
                }
                
                // Check new simplified format pattern
                if (!/^BATCH-[A-Z]{3}-\d{4}-\d{2}$/.test(batchNumber)) {
                    errors.push(`Item ${itemId}: Batch number "${batchNumber}" does not match the expected format BATCH-XXX-YYYY-ZZ. Please refresh the page to generate a new batch number.`);
                }
            }
        }
        
        return errors;
    }

    // Form Submit
    document.getElementById('receiveDeliveryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        submitButton.disabled = true;
        
        try {
            // Collect form data
            const formData = new FormData(this);
            const itemsData = {};
            
            // Convert FormData to structured object for API
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('items[')) {
                    const match = key.match(/items\[(\d+)\]\[(.+?)\]/);
                    if (match) {
                        const [, itemId, field] = match;
                        if (!itemsData[itemId]) {
                            itemsData[itemId] = {};
                        }
                        itemsData[itemId][field] = value;
                    }
                } else if (key === 'purchase_order_id') {
                    // Handle purchase_order_id separately
                    itemsData._purchase_order_id = value;
                }
            }
            
            // Validate that at least one item has quantity > 0
            const hasQuantities = Object.values(itemsData).some(item => 
                typeof item === 'object' && parseFloat(item.quantity_received || 0) > 0
            );
            
            if (!hasQuantities) {
                throw new Error('Please enter quantity for at least one item');
            }
            
            // Validate batch numbers before submission
            const batchValidationErrors = validateBatchNumbers(itemsData);
            if (batchValidationErrors.length > 0) {
                throw new Error('Batch number validation failed:\n' + batchValidationErrors.join('\n'));
            }
            
            // Prepare API payload
            const payload = {
                purchase_order_id: itemsData._purchase_order_id,
                items: itemsData
            };
            
            // Remove the helper property
            delete itemsData._purchase_order_id;
            
            const response = await fetch('/inventory/inbound/receive-delivery/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success modal
                document.getElementById('successModal').classList.remove('hidden');
                
                // Update success modal content
                const modal = document.querySelector('#successModal .bg-white');
                modal.innerHTML = `
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600 text-3xl">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Inventory Updated</h3>
                    <p class="text-sm text-gray-500 mb-6">${result.message}</p>
                    <button onclick="location.reload()" class="w-full px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all">
                        Process Next
                    </button>
                `;
            } else {
                // Handle validation errors specifically
                if (result.errors && result.errors.length > 0) {
                    const errorList = result.errors.map(err => `<li>${err}</li>`).join('');
                    const errorHtml = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-bold text-red-800 mb-2">Please fix the following issues:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                ${errorList}
                            </ul>
                        </div>
                    `;
                    
                    // Insert error display above the form
                    let errorContainer = document.getElementById('errorContainer');
                    if (!errorContainer) {
                        errorContainer = document.createElement('div');
                        errorContainer.id = 'errorContainer';
                        document.getElementById('deliveryDetailsSection').insertBefore(errorContainer, document.getElementById('receiveDeliveryForm'));
                    }
                    errorContainer.innerHTML = errorHtml;
                    errorContainer.classList.remove('hidden');
                    
                    // Scroll to first error
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Auto-hide errors after 10 seconds
                    setTimeout(() => {
                        errorContainer.classList.add('hidden');
                    }, 10000);
                    
                } else {
                    throw new Error(result.message || 'Failed to process delivery');
                }
            }
            
        } catch (error) {
            console.error('Delivery processing error:', error);
            
            // Show user-friendly error message
            let errorMessage = 'Error processing delivery: ' + error.message;
            
            // Provide specific guidance for common errors
            if (error.message.includes('duplicate key') || error.message.includes('already exists')) {
                errorMessage += '\n\nThis usually happens when the page has been open for too long. Please refresh the page and try again.';
            } else if (error.message.includes('network') || error.message.includes('fetch')) {
                errorMessage = 'Network error. Please check your connection and try again.';
            } else if (error.message.includes('Batch number') || error.message.includes('batch number')) {
                errorMessage += '\n\nThis is likely due to an invalid batch number format. Please refresh the page to generate new batch numbers.';
            }
            
            alert(errorMessage);
        } finally {
            // Restore button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });

</script>
@endpush
@endsection