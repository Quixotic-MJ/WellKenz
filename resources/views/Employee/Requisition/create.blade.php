<div id="createRequisitionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-6xl w-full rounded-lg border border-gray-200 max-h-[95vh] flex flex-col">
        <form method="POST" action="{{ route('staff.requisitions.store') }}" id="createReqForm" class="flex flex-col h-full">@csrf
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">New Requisition</h3>
                <button type="button" onclick="closeModals()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            
            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-rose-500">*</span></label>
                            <input type="text" name="req_purpose" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-rose-500">*</span></label>
                            <select name="req_priority" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Items <span class="text-rose-500">*</span></label>
                        
                        <!-- Search -->
                        <div class="mb-4">
                            <input type="text" id="itemSearch" placeholder="🔍 Search items by name..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
                        </div>

                        <!-- Items Grid -->
                        <div class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto">
                            <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 p-4">
                                @foreach($items as $i)
                                <div class="item-card border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200" 
                                     data-item-id="{{ $i->item_id }}" 
                                     data-item-name="{{ $i->item_name }}"
                                     data-unit="{{ $i->item_unit }}" 
                                     data-stock="{{ $i->item_stock }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="font-medium text-gray-900 text-sm line-clamp-2">{{ $i->item_name }}</h4>
                                        <div class="w-4 h-4 border-2 border-gray-300 rounded item-checkbox flex-shrink-0 ml-2"></div>
                                    </div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Unit:</span>
                                            <span class="font-medium">{{ $i->item_unit }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Stock:</span>
                                            <span class="font-medium">{{ $i->item_stock }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <input type="number" name="items[{{ $loop->index }}][quantity]" min="1" value="1" 
                                               class="quantity-input w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400 hidden">
                                        <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="" class="selected-item-id">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Selected Items Summary -->
                        <div id="selectedItemsSummary" class="mt-4 hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="font-medium text-green-800 mb-2">Selected Items</h4>
                                <div id="selectedItemsList" class="space-y-2 max-h-32 overflow-y-auto"></div>
                                <div class="mt-3 pt-3 border-t border-green-200">
                                    <button type="button" onclick="clearAllSelections()" class="text-sm text-green-600 hover:text-green-800">Clear All</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer with Submit Button -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3 flex-shrink-0">
                <button type="button" onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded font-medium">Submit Requisition</button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedItems = [];
let originalItems = @json($items);

function toggleItemSelection(card) {
    const itemId = card.dataset.itemId;
    const itemName = card.dataset.itemName;
    const unit = card.dataset.unit;
    const stock = card.dataset.stock;
    const checkbox = card.querySelector('.item-checkbox');
    const quantityInput = card.querySelector('.quantity-input');
    const hiddenInput = card.querySelector('.selected-item-id');
    
    if (card.classList.contains('selected')) {
        // Deselect
        card.classList.remove('selected', 'border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
        checkbox.innerHTML = '';
        checkbox.classList.remove('border-blue-500', 'bg-blue-500');
        quantityInput.classList.add('hidden');
        hiddenInput.value = '';
        
        // Remove from selected items
        selectedItems = selectedItems.filter(item => item.id !== itemId);
    } else {
        // Select
        card.classList.add('selected', 'border-blue-500', 'bg-blue-50');
        card.classList.remove('border-gray-200');
        checkbox.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
        checkbox.classList.add('border-blue-500', 'bg-blue-500');
        quantityInput.classList.remove('hidden');
        hiddenInput.value = itemId;
        
        // Add to selected items
        selectedItems.push({
            id: itemId,
            name: itemName,
            unit: unit,
            stock: stock,
            quantity: quantityInput.value
        });
    }
    
    updateSelectedItemsSummary();
}

function updateSelectedItemsSummary() {
    const summaryDiv = document.getElementById('selectedItemsSummary');
    const listDiv = document.getElementById('selectedItemsList');
    
    if (selectedItems.length === 0) {
        summaryDiv.classList.add('hidden');
        return;
    }
    
    summaryDiv.classList.remove('hidden');
    listDiv.innerHTML = selectedItems.map((item, index) => `
        <div class="flex items-center justify-between bg-white rounded p-2 border border-green-200">
            <div class="flex-1">
                <span class="font-medium text-sm">${item.name}</span>
                <span class="text-xs text-gray-500 ml-2">(${item.unit})</span>
            </div>
            <div class="flex items-center space-x-2">
                <input type="number" min="1" value="${item.quantity}" 
                       onchange="updateItemQuantity('${item.id}', this.value)"
                       class="w-16 border border-gray-300 rounded px-1 py-1 text-xs">
                <button type="button" onclick="removeSelectedItem('${item.id}')" 
                        class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function updateItemQuantity(itemId, quantity) {
    const item = selectedItems.find(item => item.id === itemId);
    if (item) {
        item.quantity = quantity;
        // Update the quantity input in the card
        const card = document.querySelector(`[data-item-id="${itemId}"]`);
        if (card) {
            card.querySelector('.quantity-input').value = quantity;
        }
    }
}

function removeSelectedItem(itemId) {
    const card = document.querySelector(`[data-item-id="${itemId}"]`);
    if (card) {
        toggleItemSelection(card);
    }
}

function clearAllSelections() {
    const selectedCards = document.querySelectorAll('.item-card.selected');
    selectedCards.forEach(card => {
        toggleItemSelection(card);
    });
}

function filterItems() {
    const searchTerm = document.getElementById('itemSearch').value.toLowerCase();
    const cards = document.querySelectorAll('.item-card');
    
    cards.forEach(card => {
        const itemName = card.dataset.itemName.toLowerCase();
        const matchesSearch = itemName.includes(searchTerm);
        
        if (matchesSearch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function attachItemCardListeners() {
    document.querySelectorAll('.item-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.classList.contains('quantity-input')) {
                toggleItemSelection(this);
            }
        });
    });
}

// Search event listener
document.getElementById('itemSearch').addEventListener('input', filterItems);

// Form submission
document.getElementById('createReqForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    if (selectedItems.length === 0) {
        showMessage('Please select at least one item', 'error');
        return;
    }
    
    // Build form data manually to ensure proper structure
    const formData = new FormData();
    
    // Add form fields
    formData.append('req_purpose', this.querySelector('[name="req_purpose"]').value);
    formData.append('req_priority', this.querySelector('[name="req_priority"]').value);
    formData.append('_token', this.querySelector('[name="_token"]').value);
    
    // Add items array
    selectedItems.forEach((item, index) => {
        formData.append(`items[${index}][item_id]`, item.id);
        formData.append(`items[${index}][quantity]`, item.quantity);
    });
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success){
            showMessage(data.message);
            closeModals();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(data.message || 'Error', 'error');
        }
    })
    .catch(() => showMessage('Error submitting requisition', 'error'));
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    attachItemCardListeners();
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.item-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.item-card.selected {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

/* Ensure scrollbar is visible */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>