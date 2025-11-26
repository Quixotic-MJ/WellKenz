@extends('Employee.layout.app')

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-7rem)] gap-6 pb-4 lg:pb-0 relative">
    
    {{-- 1. CATALOG SECTION (Left / Main) --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white border border-border-soft rounded-2xl shadow-sm overflow-hidden h-full hover:shadow-md transition-shadow duration-200">
        
        <!-- Search & Filter Header -->
        <div class="px-6 py-5 border-b border-border-soft bg-gradient-to-r from-white to-cream-bg/30 z-10">
            <div class="flex flex-col gap-6">
                <!-- Search Bar -->
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-3.5 text-text-muted text-lg"></i>
                    <input type="text" id="searchInput" placeholder="Search by item name or code..." 
                           class="w-full pl-12 pr-4 py-3 bg-white border border-border-soft rounded-xl focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all text-sm placeholder-text-muted shadow-sm">
                </div>
                
                <!-- Category Dropdown -->
                <div>
                    <div class="flex items-center justify-between mb-2 px-1">
                        <label for="categorySelect" class="text-xs font-bold text-text-muted uppercase tracking-wider">Filter by Category</label>
                        <span class="text-[10px] text-text-muted bg-caramel/10 text-caramel px-2 py-1 rounded-full">{{ $categories->count() ?? 0 }} Categories</span>
                    </div>
                    
                    <div class="relative">
                        <select id="categorySelect" onchange="filterItems(this.value)" 
                                class="w-full appearance-none bg-white border border-border-soft text-text-dark py-3 px-4 pr-8 rounded-xl leading-tight focus:outline-none focus:bg-white focus:border-caramel focus:ring-2 focus:ring-caramel/20 transition-all cursor-pointer font-medium text-sm shadow-sm hover:shadow-md">
                            <option value="all">All Items ({{ count($items ?? []) }})</option>
                            @if(isset($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->items_count ?? 0 }})</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-text-muted">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                        <div class="mt-1 text-xs text-text-muted">
                            Total Categories: {{ count($categories ?? []) }} | Items: {{ count($items ?? []) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Grid (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 bg-cream-bg/30 custom-scrollbar">
            <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $item)
                    <div class="item-card bg-white p-6 rounded-2xl border border-border-soft shadow-sm hover:shadow-lg hover:border-caramel/30 transition-all duration-300 group flex flex-col h-full hover:-translate-y-1"
                         data-category-id="{{ \App\Models\Category::where('name', $item['category'])->first()->id ?? '' }}" 
                         data-category="{{ $item['category'] ?? '' }}" 
                         data-name="{{ strtolower($item['name'] ?? '') }}"
                         data-code="{{ strtolower($item['item_code'] ?? '') }}">
                        
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-xl {{ getCategoryIconClass($item['category'] ?? '') }} flex items-center justify-center text-{{ getCategoryIconColor($item['category'] ?? '') }}-600 shadow-inner ring-1 ring-white">
                                    <i class="{{ getCategoryIcon($item['category'] ?? '') }} text-2xl"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-text-dark leading-tight text-base group-hover:text-caramel transition-colors line-clamp-2" title="{{ $item['name'] ?? '' }}">
                                        {{ $item['name'] ?? 'Unknown Item' }}
                                    </h3>
                                    <span class="inline-flex items-center mt-2 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-caramel/10 text-caramel border border-caramel/20">
                                        {{ $item['item_code'] ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="mt-auto pt-4 space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-muted font-medium">Category</span>
                                <span class="font-bold text-text-dark truncate max-w-[120px]" title="{{ $item['category'] ?? 'N/A' }}">{{ $item['category'] ?? 'N/A' }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-text-muted font-medium">Current Stock</span>
                                <span class="{{ $item['stock_class'] ?? 'text-text-muted' }} font-bold bg-white px-3 py-1.5 rounded-lg border border-border-soft shadow-sm">
                                    {{ number_format($item['current_stock'] ?? 0, 3) }} {{ $item['unit'] ?? '' }}
                                </span>
                            </div>

                            <!-- Add Button -->
                            <button onclick="addToCart({{ $item['id'] ?? 0 }}, '{{ addslashes($item['name'] ?? '') }}', '{{ $item['unit'] ?? '' }}', {{ $item['cost_price'] ?? 0 }})" 
                                    class="w-full mt-4 bg-gradient-to-r from-caramel to-caramel-dark text-white hover:from-caramel-dark hover:to-chocolate px-6 py-3 rounded-xl text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2 active:scale-95 shadow-md hover:shadow-lg">
                                <i class="fas fa-plus text-xs"></i> Add to Request
                            </button>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-24 h-24 bg-caramel/10 rounded-full flex items-center justify-center mb-6 ring-4 ring-caramel/20">
                            <i class="fas fa-box text-4xl text-caramel"></i>
                        </div>
                        <h3 class="text-xl font-bold text-text-dark">No Items Available</h3>
                        <p class="text-text-muted text-sm mt-2 max-w-sm mx-auto">There are no items available for requisition at the moment.</p>
                        <button onclick="window.location.reload()" 
                                class="mt-6 text-caramel font-bold text-sm hover:text-caramel-dark hover:underline transition-colors">
                            <i class="fas fa-refresh mr-2"></i>Refresh Page
                        </button>
                    </div>
                @endif
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden h-full flex flex-col items-center justify-center text-center py-12">
                <div class="w-20 h-20 bg-caramel/10 rounded-full flex items-center justify-center mb-4 ring-4 ring-caramel/20">
                    <i class="fas fa-search text-3xl text-caramel"></i>
                </div>
                <h3 class="text-lg font-bold text-text-dark">No items found</h3>
                <p class="text-text-muted text-sm mt-1 max-w-xs mx-auto">We couldn't find any items matching your search or selected category.</p>
                <button onclick="filterItems('all'); document.getElementById('searchInput').value = ''; document.getElementById('categorySelect').value = 'all';" 
                        class="mt-4 text-caramel font-bold text-sm hover:text-caramel-dark hover:underline transition-colors">
                    <i class="fas fa-undo mr-2"></i>Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- 2. CART SECTION (Right Sidebar) --}}
    <div class="w-full lg:w-96 bg-white border border-border-soft rounded-2xl shadow-xl flex flex-col h-full overflow-hidden hover:shadow-2xl transition-shadow duration-300">
        <form id="requisitionForm" action="{{ route('employee.requisitions.store') }}" method="POST" class="flex flex-col h-full">
            @csrf
            
            <!-- Cart Header -->
            <div class="p-6 border-b border-border-soft bg-gradient-to-r from-caramel/5 to-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-caramel to-chocolate rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-shopping-cart text-white text-lg"></i>
                        </div>
                        <span id="cartCount" class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-[10px] shadow-sm ring-2 ring-white">0</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-text-dark text-lg">Requisition Cart</h2>
                        <p class="text-xs text-text-muted">Selected items for request</p>
                    </div>
                </div>
                <button type="button" onclick="clearCart()" class="text-xs text-text-muted hover:text-red-500 font-semibold transition-colors flex items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-red-50">
                    <i class="fas fa-trash-alt"></i> Clear
                </button>
            </div>

            <!-- Cart Items (Scrollable Area) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-cream-bg/20 custom-scrollbar" id="cartContainer">
                <div id="emptyCartMessage" class="flex flex-col items-center justify-center h-full py-12 text-center">
                    <div class="w-20 h-20 bg-caramel/10 rounded-full flex items-center justify-center mb-4 ring-4 ring-caramel/20">
                        <i class="fas fa-basket-shopping text-3xl text-caramel"></i>
                    </div>
                    <h3 class="text-lg font-bold text-text-dark mb-2">Your cart is empty</h3>
                    <p class="text-text-muted text-sm max-w-[220px] leading-relaxed">Select items from the catalog on the left to build your request.</p>
                    <div class="mt-4 flex items-center gap-2 text-xs text-caramel">
                        <i class="fas fa-lightbulb"></i>
                        <span class="font-medium">Tip: Use search and filters to find items quickly</span>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" name="cart_items" id="cartItemsInput" value="">
            <input type="hidden" name="department" value="{{ $department }}">

            <!-- Cart Footer (Fixed at bottom) -->
            <div class="p-6 border-t border-border-soft bg-gradient-to-t from-white to-cream-bg/30 z-10 space-y-5 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                
                <!-- Info Summary -->
                <div class="bg-gradient-to-r from-caramel/5 to-white rounded-xl p-4 border border-caramel/20 flex justify-between items-center">
                    <div>
                        <span class="text-caramel font-bold block text-sm">Department</span>
                        <span class="text-text-dark font-bold">{{ $department }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-caramel font-bold block text-sm">Total Items</span>
                        <span class="text-text-dark font-bold text-lg" id="totalItems">0</span>
                    </div>
                </div>

                <!-- Notes Input -->
                <div>
                    <label class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">
                        Request Purpose / Notes <span class="text-red-400">*</span>
                    </label>
                    <textarea name="purpose" rows="3" 
                              class="w-full border border-border-soft bg-white rounded-xl text-sm focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all resize-none p-3 placeholder-text-muted shadow-sm hover:shadow-md" 
                              placeholder="e.g., Restocking for weekend production..." required></textarea>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" id="submitBtn" disabled 
                        class="w-full py-4 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm">
                    <span>Submit Request</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- UI COMPONENTS --}}

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-chocolate/10 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation text-chocolate"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-white border-l-4 border-chocolate rounded shadow-lg p-4 flex items-center w-80">
        <div class="flex-shrink-0">
            <i id="toastIcon" class="fas fa-check-circle text-chocolate"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-gray-900" id="toastTitle">Success</p>
            <p class="text-xs text-gray-500" id="toastMessage">Operation completed.</p>
        </div>
        <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- JavaScript for dynamic functionality -->
<script>
let cart = [];
let confirmCallback = null;

/* --- UI HELPER FUNCTIONS --- */

function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    
    document.getElementById('toastTitle').innerText = title;
    document.getElementById('toastMessage').innerText = message;
    
    // Reset classes
    icon.className = type === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500';
    toast.querySelector('.border-l-4').className = `bg-white border-l-4 rounded shadow-lg p-4 flex items-center w-80 ${type === 'success' ? 'border-green-500' : 'border-red-500'}`;

    toast.classList.remove('hidden');
    // Trigger reflow
    void toast.offsetWidth;
    toast.classList.remove('translate-y-full', 'opacity-0');

    setTimeout(hideToast, 3000);
}

window.hideToast = function() {
    const toast = document.getElementById('toast');
    toast.classList.add('translate-y-full', 'opacity-0');
    setTimeout(() => toast.classList.add('hidden'), 300);
};

function openConfirmModal(title, message, callback) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerText = message;
    confirmCallback = callback;
    document.getElementById('confirmModal').classList.remove('hidden');
}

window.closeConfirmModal = function() {
    document.getElementById('confirmModal').classList.add('hidden');
    confirmCallback = null;
};

document.getElementById('confirmBtn').addEventListener('click', function() {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

// Initialize cart - clear for fresh start or load existing session
document.addEventListener('DOMContentLoaded', function() {
    // Check if this is a new session (no existing cart timestamp)
    const cartTimestamp = localStorage.getItem('requisition_cart_timestamp');
    const currentTime = Date.now();
    
    // Clear cart if it's been more than 30 minutes or no timestamp exists
    if (!cartTimestamp || (currentTime - parseInt(cartTimestamp)) > 1800000) {
        cart = [];
        localStorage.removeItem('requisition_cart');
    } else {
        loadCartFromStorage();
    }
    
    // Update timestamp for current session
    localStorage.setItem('requisition_cart_timestamp', currentTime.toString());
    
    updateCartDisplay();
});

// Filter items by category
function filterItems(categoryId) {
    const items = document.querySelectorAll('.item-card');
    
    let visibleCount = 0;
    items.forEach(item => {
        if (categoryId === 'all' || item.dataset.categoryId === categoryId) {
            item.classList.remove('hidden');
            item.classList.add('animate-fade-in');
            visibleCount++;
        } else {
            item.classList.add('hidden');
            item.classList.remove('animate-fade-in');
        }
    });
    
    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    const itemsGrid = document.getElementById('itemsGrid');
    
    if (visibleCount > 0) {
        noResults.classList.add('hidden');
        itemsGrid.classList.remove('hidden');
    } else {
        noResults.classList.remove('hidden');
        itemsGrid.classList.add('hidden');
    }
}

// Search items functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.item-card');
    
    // Get active category from dropdown
    const activeCategory = document.getElementById('categorySelect').value;

    let visibleCount = 0;
    
    items.forEach(item => {
        const name = item.dataset.name;
        const code = item.dataset.code;
        const categoryMatch = activeCategory === 'all' || item.dataset.categoryId === activeCategory;
        const searchMatch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
        
        if (categoryMatch && searchMatch) {
            item.classList.remove('hidden');
            visibleCount++;
        } else {
            item.classList.add('hidden');
        }
    });
    
    // Toggle No Results View
    const noResults = document.getElementById('noResults');
    const itemsGrid = document.getElementById('itemsGrid');
    
    if (visibleCount > 0) {
        noResults.classList.add('hidden');
        itemsGrid.classList.remove('hidden');
    } else {
        noResults.classList.remove('hidden');
        itemsGrid.classList.add('hidden');
    }
});

// Add item to cart
function addToCart(itemId, itemName, unit, costPrice) {
    const existingItem = cart.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: itemId,
            name: itemName,
            unit: unit,
            costPrice: costPrice,
            quantity: 1
        });
    }
    
    // Visual feedback on button
    const btn = event ? event.currentTarget : null;
    const originalContent = btn ? btn.innerHTML : '';
    const originalClasses = btn ? btn.className : '';
    
    if (btn) {
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Added!';
        btn.className = 'w-full mt-4 bg-green-500 text-white border-2 border-green-500 px-6 py-3 rounded-xl text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-md transform scale-105';
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.className = originalClasses;
        }, 1000);
    }
    
    updateCartDisplay();
    saveCartToStorage();
    showToast('Item Added', `${itemName} added to cart.`);
}

// Update cart display UI
function updateCartDisplay() {
    const cartContainer = document.getElementById('cartContainer');
    const cartCount = document.getElementById('cartCount');
    const totalItems = document.getElementById('totalItems');
    const submitBtn = document.getElementById('submitBtn');
    
    // Update counters with null checks
    if (cartCount) {
        cartCount.textContent = cart.length;
    }
    // Count total individual units, not just lines
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (totalItems) {
        totalItems.textContent = totalQuantity;
    }
    
    // Enable/Disable Submit Button
    if (submitBtn) {
        if (cart.length > 0) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-4 bg-gradient-to-r from-caramel to-caramel-dark text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2 shadow-md';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-4 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm';
        }
    }
    
    // Render Cart Items
    if (cartContainer) {
        cartContainer.innerHTML = '';
        
        if (cart.length === 0) {
            // Show empty cart message
            const emptyCartHTML = `
                <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                    <div class="w-20 h-20 bg-caramel/10 rounded-full flex items-center justify-center mb-4 ring-4 ring-caramel/20">
                        <i class="fas fa-basket-shopping text-3xl text-caramel"></i>
                    </div>
                    <h3 class="text-lg font-bold text-text-dark mb-2">Your cart is empty</h3>
                    <p class="text-text-muted text-sm max-w-[220px] leading-relaxed">Select items from the catalog on the left to build your request.</p>
                    <div class="mt-4 flex items-center gap-2 text-xs text-caramel">
                        <i class="fas fa-lightbulb"></i>
                        <span class="font-medium">Tip: Use search and filters to find items quickly</span>
                    </div>
                </div>
            `;
            cartContainer.innerHTML = emptyCartHTML;
            return;
        }
        
        cart.forEach((item, index) => {
            const cartItem = document.createElement('div');
            cartItem.className = 'bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:border-chocolate/30 hover:shadow-md transition-all duration-200 group';
            cartItem.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <h4 class="text-sm font-bold text-gray-800 leading-tight pr-2 line-clamp-2" title="${item.name}">${item.name}</h4>
                    <button type="button" onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 transition-colors p-1 -mr-1 -mt-1 rounded-full hover:bg-red-50">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                        ${item.unit}
                    </div>
                    <div class="flex items-center bg-white border-2 border-gray-200 rounded-xl focus-within:border-chocolate focus-within:ring-2 focus-within:ring-chocolate/20 transition-all">
                        <button type="button" onclick="updateQuantity(${index}, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-chocolate/5 rounded-l-xl transition-colors">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <input type="number" 
                               value="${item.quantity}" 
                               min="1" 
                               step="0.1"
                               onchange="updateQuantityDirect(${index}, this.value)"
                               class="w-16 text-center text-sm font-bold border-none bg-transparent focus:ring-0 p-2 text-gray-900"
                               placeholder="1">
                        <button type="button" onclick="updateQuantity(${index}, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-chocolate/5 rounded-r-xl transition-colors">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>
            `;
            cartContainer.appendChild(cartItem);
        });
        
        // Update hidden input for form submission
        const cartItemsInput = document.getElementById('cartItemsInput');
        if (cartItemsInput) {
            cartItemsInput.value = JSON.stringify(cart);
        }
    }
}

// Update item quantity logic
function updateQuantity(index, change) {
    cart[index].quantity += change;
    
    if (cart[index].quantity <= 0) {
        removeFromCart(index);
        return;
    }
    
    updateCartDisplay();
    saveCartToStorage();
}

// Direct quantity input update
function updateQuantityDirect(index, value) {
    const quantity = parseFloat(value);
    
    if (isNaN(quantity) || quantity <= 0) {
        // Invalid input, remove item
        removeFromCart(index);
        return;
    }
    
    cart[index].quantity = Math.round(quantity * 10) / 10; // Round to 1 decimal place
    updateCartDisplay();
    saveCartToStorage();
    
    // Show success feedback
    showToast('Quantity Updated', `Set to ${cart[index].quantity} ${cart[index].unit}`);
}

// Remove item from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    saveCartToStorage();
}

// Clear entire cart
function clearCart() {
    if (cart.length === 0) return;
    
    openConfirmModal('Clear Cart', 'Are you sure you want to remove all items from your requisition?', function() {
        cart = [];
        updateCartDisplay();
        saveCartToStorage();
        showToast('Cart Cleared', 'All items have been removed.');
    });
}

// Storage persistence
function saveCartToStorage() {
    localStorage.setItem('requisition_cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const saved = localStorage.getItem('requisition_cart');
    if (saved) {
        try {
            const parsedCart = JSON.parse(saved);
            if (Array.isArray(parsedCart)) {
                // Validate cart items
                cart = parsedCart.filter(item => 
                    item && typeof item.id !== 'undefined' && 
                    typeof item.name !== 'undefined' &&
                    typeof item.quantity !== 'undefined' &&
                    item.quantity > 0
                );
            } else {
                cart = [];
            }
        } catch (e) {
            console.warn('Invalid cart data in localStorage, clearing...');
            localStorage.removeItem('requisition_cart');
            cart = [];
        }
    }
}

// Handle form submission validation
document.getElementById('requisitionForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        showToast('Cart Empty', 'Please add at least one item to your requisition.', 'error');
        return;
    }
    
    e.preventDefault(); // Prevent default form submission
    
    // Calculate total for confirmation
    const totalValue = cart.reduce((sum, item) => sum + (item.quantity * item.costPrice), 0);
    
    // Show confirmation dialog
    openConfirmModal(
        'Submit Requisition',
        `Are you sure you want to submit this requisition?\n\nItems: ${cart.length} different items\nTotal Quantity: ${cart.reduce((sum, item) => sum + item.quantity, 0)} units\nEstimated Value: ₱${totalValue.toFixed(2)}\n\nThis action will send your requisition for supervisor approval.`,
        function() {
            // Update hidden input one last time
            const cartItemsInput = document.getElementById('cartItemsInput');
            if (cartItemsInput) {
                cartItemsInput.value = JSON.stringify(cart);
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';
                submitBtn.disabled = true;
                submitBtn.className = 'w-full py-4 bg-gray-800 text-white font-bold rounded-xl cursor-not-allowed flex items-center justify-center gap-2';
            }
            
            // Clear storage so cart is empty on page reload/redirect
            localStorage.removeItem('requisition_cart');
            localStorage.removeItem('requisition_cart_timestamp');
            
            // Submit the form
            document.getElementById('requisitionForm').submit();
        }
    );
});
</script>

<style>
    /* Custom Fade In Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
    
    /* Hide Scrollbar but keep functionality */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

{{-- Helper functions for category icons (Kept as is for backend logic) --}}
@php
function getCategoryIconClass($category) {
    $iconMap = [
        'Flour & Grains' => 'bg-amber-50',
        'Dairy Products' => 'bg-blue-50', 
        'Sweeteners' => 'bg-purple-50',
        'Fats & Oils' => 'bg-yellow-50',
        'Leavening Agents' => 'bg-orange-50',
        'Flavoring & Spices' => 'bg-green-50',
        'Nuts & Seeds' => 'bg-orange-50',
        'Fruits' => 'bg-red-50',
        'Additives & Preservatives' => 'bg-gray-50',
        'Packaging Materials' => 'bg-gray-50',
        'Cleaning Supplies' => 'bg-indigo-50',
        'Tools & Equipment' => 'bg-slate-50',
        'Beverages' => 'bg-cyan-50',
        'Frozen Goods' => 'bg-sky-50'
    ];
    
    return $iconMap[$category] ?? 'bg-gray-50';
}

function getCategoryIconColor($category) {
    $colorMap = [
        'Flour & Grains' => 'amber',
        'Dairy Products' => 'blue',
        'Sweeteners' => 'purple',
        'Fats & Oils' => 'yellow',
        'Leavening Agents' => 'orange',
        'Flavoring & Spices' => 'green',
        'Nuts & Seeds' => 'orange',
        'Fruits' => 'red',
        'Additives & Preservatives' => 'gray',
        'Packaging Materials' => 'gray',
        'Cleaning Supplies' => 'indigo',
        'Tools & Equipment' => 'slate',
        'Beverages' => 'cyan',
        'Frozen Goods' => 'sky'
    ];
    
    return $colorMap[$category] ?? 'gray';
}

function getCategoryIcon($category) {
    $iconMap = [
        'Flour & Grains' => 'fas fa-wheat',
        'Dairy Products' => 'fas fa-cube',
        'Sweeteners' => 'fas fa-candy-cane',
        'Fats & Oils' => 'fas fa-tint',
        'Leavening Agents' => 'fas fa-flask',
        'Flavoring & Spices' => 'fas fa-pepper-hot',
        'Nuts & Seeds' => 'fas fa-seedling',
        'Fruits' => 'fas fa-apple-alt',
        'Additives & Preservatives' => 'fas fa-vial',
        'Packaging Materials' => 'fas fa-box-open',
        'Cleaning Supplies' => 'fas fa-broom',
        'Tools & Equipment' => 'fas fa-tools',
        'Beverages' => 'fas fa-glass-water',
        'Frozen Goods' => 'fas fa-snowflake'
    ];
    
    return $iconMap[$category] ?? 'fas fa-box';
}
@endphp

@endsection