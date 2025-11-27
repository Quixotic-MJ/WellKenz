@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600 pb-24" id="adjustments-app">

    {{-- 1. HEADER & STATS --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Inventory Adjustments</h1>
            <p class="text-sm text-gray-500">Manage inventory discrepancies, spoilage, and manual corrections.</p>
        </div>
        
        <div class="flex gap-4 w-full lg:w-auto">
            <!-- Loss Card -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-border-soft flex-1 lg:min-w-[200px] flex items-center gap-4 group hover:border-red-200 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Loss (Today)</p>
                    <p class="text-xl font-bold text-gray-900" id="today-loss">₱ {{ number_format($stats['today_loss_value'] ?? 0, 2) }}</p>
                </div>
            </div>
            
            <!-- Count Card -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-border-soft flex-1 lg:min-w-[200px] flex items-center gap-4 group hover:border-blue-200 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Adjustments</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['total_adjustments_today'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- 2. ADJUSTMENT FORM (Left Column) --}}
        <div class="xl:col-span-1">
            <div class="bg-white border border-border-soft rounded-2xl shadow-lg sticky top-6 overflow-hidden">
                <div class="bg-chocolate px-6 py-4 border-b border-chocolate-dark flex items-center justify-between">
                    <h3 class="text-lg font-display font-bold text-white flex items-center gap-2">
                        <i class="fas fa-edit text-caramel"></i> New Adjustment
                    </h3>
                </div>
                
                <div class="p-6">
                    <form id="adjustment-form" enctype="multipart/form-data" class="space-y-6">
                        @csrf 
                        
                        <!-- ACTION TYPE -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">1. Action Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Deduction -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="adjustment_type" value="remove" class="peer sr-only" checked>
                                    <div class="h-full p-3 rounded-xl border border-border-soft bg-cream-bg/50 hover:bg-red-50/50 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:ring-1 peer-checked:ring-red-500 transition-all duration-200 text-center flex flex-col items-center justify-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-white border border-border-soft text-red-500 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900">Remove</span>
                                            <span class="block text-[10px] text-gray-500">Loss / Damage</span>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2 text-red-600 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fas fa-check-circle"></i></div>
                                </label>
                                
                                <!-- Addition -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="adjustment_type" value="add" class="peer sr-only">
                                    <div class="h-full p-3 rounded-xl border border-border-soft bg-cream-bg/50 hover:bg-green-50/50 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:ring-1 peer-checked:ring-green-500 transition-all duration-200 text-center flex flex-col items-center justify-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-white border border-border-soft text-green-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900">Add Stock</span>
                                            <span class="block text-[10px] text-gray-500">Return / Found</span>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2 text-green-600 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fas fa-check-circle"></i></div>
                                </label>
                            </div>
                        </div>

                        <!-- ITEM SELECTION -->
                        <div class="p-4 bg-cream-bg/30 rounded-xl border border-border-soft space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">2. Select Item</label>
                                <div class="relative">
                                    <select class="block w-full pl-4 pr-10 py-3 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel cursor-pointer bg-white" id="item-select" name="item_id" required>
                                        <option value="" disabled selected>Search inventory item...</option>
                                        @foreach($items as $item)
                                        <option value="{{ $item['id'] }}" data-current-stock="{{ $item['current_stock'] }}" data-unit="{{ $item['unit_symbol'] }}" data-cost="{{ $item['cost_price'] }}">
                                            {{ $item['name'] }} ({{ $item['item_code'] }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                
                                <!-- Stock Context -->
                                <div class="mt-2 flex justify-between items-center text-xs bg-white px-3 py-2 rounded-lg border border-gray-100">
                                    <span class="text-gray-500 font-bold uppercase">Current Stock</span>
                                    <span class="font-mono font-bold text-chocolate text-sm" id="current-stock-display">--</span>
                                </div>
                            </div>

                            <!-- QUANTITY & REASON -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">3. Quantity <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" step="0.001" min="0.001" 
                                               class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm py-2.5 pl-3 pr-12 font-bold" 
                                               id="quantity" name="quantity" placeholder="0.00" required>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <input type="text" class="text-[10px] font-bold text-gray-400 bg-transparent border-none text-right w-12 p-0 uppercase" id="unit-display" value="UNIT" disabled>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-red-600 mt-1 hidden font-bold flex items-center gap-1" id="qty-error">
                                        <i class="fas fa-exclamation-circle"></i> Invalid quantity
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-2">4. Reason <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm py-2.5 pl-3 pr-8 cursor-pointer bg-white" id="reason-code" name="reason_code" required>
                                            <option value="" disabled selected>Select reason...</option>
                                            <optgroup label="Inventory Loss">
                                                <option value="Spoilage / Expired">Spoilage / Expired</option>
                                                <option value="Damaged / Broken">Damaged / Broken</option>
                                                <option value="Spillage (Production)">Spillage</option>
                                                <option value="Theft / Missing">Theft / Missing</option>
                                            </optgroup>
                                            <optgroup label="Inventory Correction">
                                                <option value="Audit Variance Correction">Audit Variance</option>
                                                <option value="Found Item">Found Item</option>
                                            </optgroup>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- REMARKS -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">5. Remarks <span class="text-red-500">*</span></label>
                            <textarea rows="2" class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm p-3 resize-none" id="remarks" name="remarks" placeholder="Briefly describe what happened..." required></textarea>
                        </div>

                        <!-- UPLOAD -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Attach Proof (Optional)</label>
                            <div class="relative group cursor-pointer" id="photo-upload-area">
                                <div class="flex items-center justify-center w-full px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-cream-bg hover:border-caramel transition-all duration-200 bg-gray-50/50">
                                    <input type="file" id="photo" name="photo" class="sr-only" accept="image/*">
                                    <div class="space-y-1 text-center">
                                        <div class="w-10 h-10 mx-auto bg-white rounded-full flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                                            <i class="fas fa-cloud-upload-alt text-gray-400 text-lg group-hover:text-caramel transition-colors" id="photo-icon"></i>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <span class="font-bold text-chocolate hover:underline" id="photo-upload-text">Click to upload</span>
                                        </div>
                                        <p class="text-[10px] text-gray-400 uppercase">PNG, JPG (Max 5MB)</p>
                                        <p class="text-xs text-green-600 font-bold bg-green-50 py-1 px-2 rounded-md mt-2 border border-green-100 shadow-sm" id="photo-info" style="display: none;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-chocolate hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-caramel transition-all duration-300 transform active:scale-95 items-center gap-2" id="submit-btn">
                                <i class="fas fa-save"></i> <span id="submit-text">Submit Adjustment</span>
                                <span id="submit-loading" style="display: none;" class="flex items-center gap-2">
                                    <i class="fas fa-circle-notch fa-spin"></i> Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. RECENT HISTORY (Right Column) --}}
        <div class="xl:col-span-2 h-full">
            <div class="bg-white border border-border-soft rounded-2xl shadow-sm h-full flex flex-col overflow-hidden">
                <div class="px-6 py-5 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                    <div>
                        <h3 class="font-display text-lg font-bold text-chocolate">Recent Activity</h3>
                        <p class="text-xs text-gray-500">History of inventory movements.</p>
                    </div>
                    <span class="text-[10px] font-bold text-caramel bg-white border border-border-soft px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">This Month</span>
                </div>
                
                <div class="overflow-x-auto flex-1 bg-white custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-display">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-display">Details</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider font-display">Amount</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider font-display">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider font-display">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100" id="adjustments-table-body">
                            @forelse($recentAdjustments as $adjustment)
                            <tr class="hover:bg-cream-bg/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                    {{ $adjustment['formatted_date'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ $adjustment['item_name'] }}</span>
                                        <span class="text-xs text-gray-400 font-mono mt-0.5">{{ $adjustment['item_code'] ?? 'N/A' }}</span>
                                        <span class="text-[10px] font-bold mt-1 inline-block px-1.5 py-0.5 rounded w-fit uppercase tracking-wide {{ $adjustment['movement_type'] === 'add' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                            {{ $adjustment['reason'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-base font-bold {{ $adjustment['movement_type'] === 'add' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $adjustment['movement_type'] === 'add' ? '+' : '-' }}{{ number_format(abs($adjustment['quantity']), 3) }} 
                                        <span class="text-xs font-normal text-gray-400 ml-0.5">{{ $adjustment['unit_symbol'] }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-green-50 text-green-700 border border-green-100">
                                        <i class="fas fa-check mr-1.5"></i> {{ $adjustment['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-gray-400 hover:text-chocolate hover:bg-cream-bg p-2 rounded-lg transition-all" 
                                            onclick='viewAdjustment(@json($adjustment))' title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="bg-cream-bg rounded-full p-4 mb-3 shadow-inner">
                                            <i class="fas fa-clipboard-list text-3xl text-chocolate/30"></i>
                                        </div>
                                        <h4 class="font-bold text-chocolate">No adjustments found</h4>
                                        <p class="text-sm text-gray-400 mt-1 max-w-xs">Create your first inventory adjustment using the form on the left to track movement.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center">
                    <a href="{{ route('supervisor.inventory.stock-history') }}" class="text-xs font-bold text-chocolate hover:text-caramel uppercase tracking-widest transition-colors">
                        View Full History &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DETAILS MODAL --}}
<div id="view-modal" class="fixed inset-0 z-50 hidden overflow-y-auto backdrop-blur-sm transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-chocolate/20 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-border-soft">
            <!-- Modal Header -->
            <div class="bg-chocolate px-6 py-4 border-b border-chocolate-dark flex items-center justify-between">
                <h3 class="text-lg font-display font-bold text-white" id="modal-title">Transaction Details</h3>
                <button onclick="closeModal()" class="text-white/70 hover:text-white focus:outline-none transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-6 py-6">
                <!-- Main Info Card -->
                <div class="bg-cream-bg/50 rounded-xl p-5 mb-6 border border-border-soft relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-chocolate/5 rounded-bl-full -mr-4 -mt-4"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Item Name</span>
                            <div class="text-lg font-bold text-chocolate leading-tight mt-1" id="modal-item"></div>
                        </div>
                        <div class="text-right">
                             <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Quantity</span>
                             <div class="text-2xl font-bold mt-1" id="modal-qty"></div>
                        </div>
                    </div>
                </div>

                <!-- Grid Details -->
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Transaction Type</span>
                        <span class="font-bold" id="modal-type"></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Reason</span>
                        <span class="font-medium text-gray-800" id="modal-reason"></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Date Processed</span>
                        <span class="font-medium text-gray-800" id="modal-date"></span>
                    </div>
                    
                    <div class="pt-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Remarks / Notes</span>
                        <div class="bg-yellow-50 text-yellow-900 p-3 rounded-lg text-xs italic border border-yellow-100 leading-relaxed" id="modal-remarks"></div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse border-t border-gray-100">
                <button type="button" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 border-gray-200 focus:outline-none sm:w-auto sm:text-sm transition-colors" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// JavaScript Logic Preserved 100%
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    if(sessionStorage.getItem('adjustment_success')) {
        showNotification(sessionStorage.getItem('adjustment_success'), 'success');
        sessionStorage.removeItem('adjustment_success');
    }
});

function initializeApp() {
    setupEventListeners();
    setupItemSelection();
    setupPhotoUpload();
}

function setupEventListeners() {
    document.getElementById('adjustment-form').addEventListener('submit', handleFormSubmit);
    document.getElementById('item-select').addEventListener('change', updateItemDisplay);
    document.getElementById('quantity').addEventListener('input', validateQuantity);
    document.querySelectorAll('input[name="adjustment_type"]').forEach(radio => {
        radio.addEventListener('change', validateQuantity);
    });
}

function setupItemSelection() {
    updateItemDisplay();
}

function setupPhotoUpload() {
    const uploadArea = document.getElementById('photo-upload-area');
    const photoInput = document.getElementById('photo');
    const photoText = document.getElementById('photo-upload-text');
    const photoInfo = document.getElementById('photo-info');
    const photoIcon = document.getElementById('photo-icon');
    
    uploadArea.addEventListener('click', () => photoInput.click());
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                showNotification('File size must be less than 5MB', 'error');
                photoInput.value = '';
                return;
            }
            if (!file.type.match('image.*')) {
                showNotification('Please select an image file', 'error');
                photoInput.value = '';
                return;
            }
            
            photoText.textContent = "Change Photo";
            photoInfo.textContent = file.name;
            photoInfo.style.display = 'inline-block';
            
            uploadArea.classList.add('border-green-400', 'bg-green-50');
            uploadArea.classList.remove('border-border-soft', 'bg-cream-bg/50');
            photoIcon.classList.remove('text-gray-400');
            photoIcon.classList.add('text-green-500');
        }
    });
}

function updateItemDisplay() {
    const select = document.getElementById('item-select');
    const currentStockDisplay = document.getElementById('current-stock-display');
    const unitDisplay = document.getElementById('unit-display');
    
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;
        const unit = selectedOption.dataset.unit || '';
        
        currentStockDisplay.textContent = currentStock.toFixed(3) + ' ' + unit;
        unitDisplay.value = unit;
        
        select.classList.add('bg-cream-bg');
        setTimeout(() => select.classList.remove('bg-cream-bg'), 300);
        
        validateQuantity();
    } else {
        currentStockDisplay.textContent = '--';
        unitDisplay.value = 'UNIT';
    }
}

function validateQuantity() {
    const quantityInput = document.getElementById('quantity');
    const qtyError = document.getElementById('qty-error');
    const adjustmentType = document.querySelector('input[name="adjustment_type"]:checked').value;
    const select = document.getElementById('item-select');
    const selectedOption = select.options[select.selectedIndex];
    
    quantityInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500', 'bg-red-50');
    qtyError.style.display = 'none';
    
    if (selectedOption && selectedOption.value && adjustmentType === 'remove') {
        const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        
        if (quantity > currentStock) {
            quantityInput.setCustomValidity('Cannot exceed stock');
            quantityInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500', 'bg-red-50');
            qtyError.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Exceeds current stock (${currentStock})`;
            qtyError.style.display = 'flex'; // Changed to flex for icon alignment
            return false;
        }
    }
    quantityInput.setCustomValidity('');
    return true;
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    if(!validateQuantity()) {
        showNotification('Please fix errors before submitting.', 'error');
        document.getElementById('quantity').classList.add('animate-pulse');
        setTimeout(() => document.getElementById('quantity').classList.remove('animate-pulse'), 500);
        return;
    }
    
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    
    submitBtn.disabled = true;
    submitText.style.display = 'none';
    submitLoading.style.display = 'flex'; // Changed to flex for alignment
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch("{{ route('supervisor.inventory.adjustments.store') }}", { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            sessionStorage.setItem('adjustment_success', 'Adjustment recorded successfully!');
            window.location.reload();
        } else {
            let msg = result.message || 'Failed to create adjustment';
            if(result.errors) {
                msg = Object.values(result.errors).flat().join('\n');
            }
            showNotification(msg, 'error');
            submitBtn.disabled = false;
            submitText.style.display = 'inline';
            submitLoading.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error occurred. Please check your connection.', 'error');
        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoading.style.display = 'none';
    }
}

function viewAdjustment(data) {
    document.getElementById('modal-item').textContent = data.item_name;
    
    const typeEl = document.getElementById('modal-type');
    typeEl.textContent = data.movement_type === 'add' ? 'Addition (Return)' : 'Deduction (Loss)';
    typeEl.className = data.movement_type === 'add' ? 'font-bold text-green-600' : 'font-bold text-red-600';
    
    const qtyEl = document.getElementById('modal-qty');
    qtyEl.textContent = (data.movement_type === 'add' ? '+' : '-') + Math.abs(data.quantity) + ' ' + (data.unit_symbol || '');
    qtyEl.className = data.movement_type === 'add' ? 'text-2xl font-bold text-green-600 mt-1' : 'text-2xl font-bold text-red-600 mt-1';

    document.getElementById('modal-reason').textContent = data.reason;
    document.getElementById('modal-remarks').textContent = data.remarks || 'No remarks provided.';
    document.getElementById('modal-date').textContent = data.formatted_date;
    
    document.getElementById('view-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('view-modal').classList.add('hidden');
}

function showNotification(message, type = 'info') {
    const existing = document.getElementById('toast-notification');
    if(existing) existing.remove();

    const notification = document.createElement('div');
    notification.id = 'toast-notification';
    // Updated toast style to match design system
    notification.className = `fixed top-5 right-5 z-[60] px-6 py-4 rounded-xl shadow-2xl text-white font-medium slide-in flex items-center gap-4 border border-white/10 backdrop-blur-md ${
        type === 'success' ? 'bg-gray-900' : 
        type === 'error' ? 'bg-red-600' : 'bg-blue-600'
    }`;
    
    const icon = type === 'success' ? '<i class="fas fa-check-circle text-green-400 text-xl"></i>' : 
                 type === 'error' ? '<i class="fas fa-times-circle text-white text-xl"></i>' : 
                 '<i class="fas fa-info-circle text-xl"></i>';
                 
    notification.innerHTML = `${icon} <div><p class="font-bold text-sm uppercase tracking-wider mb-0.5">${type}</p><p class="text-sm opacity-90">${message}</p></div>`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        notification.style.transition = 'all 0.5s ease-in-out';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}
</script>
@endsection