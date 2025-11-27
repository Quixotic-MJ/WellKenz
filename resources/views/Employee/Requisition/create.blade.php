@extends('Employee.layout.app')

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-7rem)] gap-6 pb-4 lg:pb-0 relative font-sans text-gray-600">
    
    {{-- 1. CATALOG SECTION (Left / Main) --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white border border-border-soft rounded-2xl shadow-sm overflow-hidden h-full">
        
        <div class="px-6 py-5 border-b border-border-soft bg-cream-bg z-10">
            <div class="flex flex-col gap-4">
                
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-2xl font-bold text-chocolate">Request Stock</h2>
                    <span class="text-xs font-bold text-caramel bg-white border border-border-soft px-3 py-1 rounded-full shadow-sm">
                        {{ $items->count() ?? 0 }} Items Available
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                        </div>
                        <input type="text" id="searchInput" 
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all sm:text-sm" 
                            placeholder="Search item name or code...">
                    </div>
                    
                    <div class="relative sm:w-64">
                        <select id="categorySelect" onchange="filterItems(this.value)" 
                            class="block w-full pl-3 pr-10 py-2.5 text-base border-gray-200 focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm rounded-xl cursor-pointer bg-white">
                            <option value="all">All Categories</option>
                            @if(isset($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-white custom-scrollbar">
            <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $item)
                    <div class="item-card bg-white p-5 rounded-xl border border-border-soft shadow-sm hover:shadow-md hover:border-caramel/50 transition-all duration-200 group flex flex-col h-full"
                         data-category-id="{{ \App\Models\Category::where('name', $item['category'])->first()->id ?? '' }}" 
                         data-category="{{ $item['category'] ?? '' }}" 
                         data-name="{{ strtolower($item['name'] ?? '') }}"
                         data-code="{{ strtolower($item['item_code'] ?? '') }}">
                        
                        <div class="flex justify-between items-start mb-3">
                            <div class="w-12 h-12 rounded-lg {{ getCategoryIconClass($item['category'] ?? '') }} flex items-center justify-center text-chocolate shadow-sm ring-1 ring-black/5">
                                <i class="{{ getCategoryIcon($item['category'] ?? '') }} text-lg"></i>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 font-mono">
                                {{ $item['item_code'] ?? 'N/A' }}
                            </span>
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-chocolate transition-colors line-clamp-2 mb-1" title="{{ $item['name'] ?? '' }}">
                                {{ $item['name'] ?? 'Unknown Item' }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-3">{{ $item['category'] ?? 'Uncategorized' }}</p>
                            
                            <div class="flex items-center justify-between text-xs bg-cream-bg p-2 rounded-lg border border-border-soft mb-4">
                                <span class="text-gray-500 font-medium">Stock:</span>
                                <span class="font-bold {{ isset($item['stock_class']) ? $item['stock_class'] : 'text-chocolate' }}">
                                    {{ number_format($item['current_stock'] ?? 0, 2) }} {{ $item['unit'] ?? '' }}
                                </span>
                            </div>
                        </div>

                        <button onclick="addToCart({{ $item['id'] ?? 0 }}, '{{ addslashes($item['name'] ?? '') }}', '{{ $item['unit'] ?? '' }}', {{ $item['cost_price'] ?? 0 }})" 
                                class="w-full mt-auto bg-white border border-caramel text-caramel hover:bg-caramel hover:text-white px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 active:scale-95 shadow-sm">
                            <i class="fas fa-plus text-xs"></i> Add to Cart
                        </button>
                    </div>
                    @endforeach
                @else
                    <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-box-open text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">No Items Available</h3>
                        <p class="text-gray-500 text-sm mt-1">There are no items available for requisition at the moment.</p>
                    </div>
                @endif
            </div>

            <div id="noResults" class="hidden h-full flex flex-col items-center justify-center text-center py-12">
                <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-search text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">No items found</h3>
                <p class="text-gray-500 text-sm mt-1">Try adjusting your search or category filter.</p>
                <button onclick="filterItems('all'); document.getElementById('searchInput').value = ''; document.getElementById('categorySelect').value = 'all';" 
                        class="mt-4 text-caramel font-bold text-sm hover:text-chocolate hover:underline">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    {{-- 2. CART SECTION (Right Sidebar) --}}
    <div class="w-full lg:w-96 bg-white border border-border-soft rounded-2xl shadow-xl flex flex-col h-full overflow-hidden shrink-0">
        <form id="requisitionForm" action="{{ route('employee.requisitions.store') }}" method="POST" class="flex flex-col h-full">
            @csrf
            
            <div class="p-5 border-b border-border-soft bg-chocolate text-white flex justify-between items-center shadow-md relative z-10">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span id="cartCount" class="absolute -top-2 -right-2 w-5 h-5 bg-caramel text-white rounded-full flex items-center justify-center font-bold text-[10px] shadow-sm border border-chocolate">0</span>
                    </div>
                    <div>
                        <h2 class="font-display font-bold text-lg leading-none">Your Request</h2>
                    </div>
                </div>
                <button type="button" onclick="clearCart()" class="text-xs text-white/70 hover:text-white font-bold transition-colors flex items-center gap-1 bg-white/10 hover:bg-white/20 px-2 py-1 rounded">
                    <i class="fas fa-trash-alt"></i> Clear
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-cream-bg custom-scrollbar" id="cartContainer">
                <div id="emptyCartMessage" class="flex flex-col items-center justify-center h-full py-8 text-center opacity-60">
                    <i class="fas fa-basket-shopping text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium text-gray-500">Your cart is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Select items from the catalog</p>
                </div>
            </div>

            <input type="hidden" name="cart_items" id="cartItemsInput" value="">
            <input type="hidden" name="department" value="{{ $department }}">

            <div class="p-5 border-t border-border-soft bg-white z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                
                <div class="flex justify-between items-center mb-4 text-sm">
                    <span class="text-gray-500">Total Items:</span>
                    <span class="font-bold text-chocolate text-lg" id="totalItems">0</span>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                        Purpose / Notes <span class="text-red-400">*</span>
                    </label>
                    <textarea name="purpose" rows="2" 
                              class="w-full border-gray-200 bg-gray-50 rounded-lg text-sm focus:ring-caramel focus:border-caramel transition-all resize-none p-2 placeholder-gray-400" 
                              placeholder="e.g., Weekend production stock..." required></textarea>
                </div>
                
                <button type="submit" id="submitBtn" disabled 
                        class="w-full py-3.5 bg-gray-200 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm">
                    <span>Submit Request</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- UI COMPONENTS --}}

<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-border-soft">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation text-amber-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-white border-l-4 border-chocolate rounded-lg shadow-xl p-4 flex items-center w-80 ring-1 ring-black/5">
        <div class="flex-shrink-0">
            <i id="toastIcon" class="fas fa-check-circle text-chocolate text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-bold text-chocolate" id="toastTitle">Success</p>
            <p class="text-xs text-gray-500" id="toastMessage">Operation completed.</p>
        </div>
        <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-chocolate transition-colors focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

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
    icon.className = type === 'success' ? 'fas fa-check-circle text-green-500 text-xl' : 'fas fa-exclamation-circle text-red-500 text-xl';
    
    const borderClass = type === 'success' ? 'border-green-500' : 'border-red-500';
    toast.querySelector('.border-l-4').className = `bg-white border-l-4 rounded-lg shadow-xl p-4 flex items-center w-80 ring-1 ring-black/5 ${borderClass}`;

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

// Initialize cart
document.addEventListener('DOMContentLoaded', function() {
    const cartTimestamp = localStorage.getItem('requisition_cart_timestamp');
    const currentTime = Date.now();
    
    if (!cartTimestamp || (currentTime - parseInt(cartTimestamp)) > 1800000) {
        cart = [];
        localStorage.removeItem('requisition_cart');
    } else {
        loadCartFromStorage();
    }
    
    localStorage.setItem('requisition_cart_timestamp', currentTime.toString());
    updateCartDisplay();
});

// Filter items logic
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
    
    // Simple Button Feedback
    const btn = event ? event.currentTarget : null;
    const originalContent = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="fas fa-check"></i> Added';
        btn.classList.replace('border-caramel', 'border-green-500');
        btn.classList.replace('text-caramel', 'text-green-500');
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.replace('border-green-500', 'border-caramel');
            btn.classList.replace('text-green-500', 'text-caramel');
        }, 1000);
    }
    
    updateCartDisplay();
    saveCartToStorage();
}

// Update cart display UI with New Design System
function updateCartDisplay() {
    const cartContainer = document.getElementById('cartContainer');
    const cartCount = document.getElementById('cartCount');
    const totalItems = document.getElementById('totalItems');
    const submitBtn = document.getElementById('submitBtn');
    
    if (cartCount) cartCount.textContent = cart.length;
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (totalItems) totalItems.textContent = totalQuantity;
    
    if (submitBtn) {
        if (cart.length > 0) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3.5 bg-chocolate hover:bg-chocolate-dark text-white font-bold rounded-xl shadow-md hover:shadow-lg transform transition-all active:scale-95 flex items-center justify-center gap-2';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3.5 bg-gray-200 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2 shadow-sm';
        }
    }
    
    if (cartContainer) {
        cartContainer.innerHTML = '';
        
        if (cart.length === 0) {
            const emptyCartHTML = `
                <div class="flex flex-col items-center justify-center h-full py-8 text-center opacity-60">
                    <i class="fas fa-basket-shopping text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium text-gray-500">Your cart is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Select items from the catalog</p>
                </div>`;
            cartContainer.innerHTML = emptyCartHTML;
            return;
        }
        
        cart.forEach((item, index) => {
            const cartItem = document.createElement('div');
            cartItem.className = 'bg-white p-3 rounded-xl border border-border-soft shadow-sm flex flex-col gap-2 animate-fade-in group';
            cartItem.innerHTML = `
                <div class="flex justify-between items-start">
                    <h4 class="text-sm font-bold text-chocolate leading-tight pr-2 line-clamp-1" title="${item.name}">${item.name}</h4>
                    <button type="button" onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-1">
                    <div class="text-[10px] font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-100 uppercase tracking-wide">
                        ${item.unit}
                    </div>
                    <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg">
                        <button type="button" onclick="updateQuantity(${index}, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-gray-100 rounded-l-lg transition-colors">
                            <i class="fas fa-minus text-[10px]"></i>
                        </button>
                        <input type="number" 
                               value="${item.quantity}" 
                               min="1" 
                               step="0.1"
                               onchange="updateQuantityDirect(${index}, this.value)"
                               class="w-10 text-center text-xs font-bold border-none bg-transparent focus:ring-0 p-0 text-gray-700">
                        <button type="button" onclick="updateQuantity(${index}, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-gray-100 rounded-r-lg transition-colors">
                            <i class="fas fa-plus text-[10px]"></i>
                        </button>
                    </div>
                </div>
            `;
            cartContainer.appendChild(cartItem);
        });
        
        const cartItemsInput = document.getElementById('cartItemsInput');
        if (cartItemsInput) cartItemsInput.value = JSON.stringify(cart);
    }
}

// Logic functions (Standard)
function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) {
        removeFromCart(index);
        return;
    }
    updateCartDisplay();
    saveCartToStorage();
}

function updateQuantityDirect(index, value) {
    const quantity = parseFloat(value);
    if (isNaN(quantity) || quantity <= 0) {
        removeFromCart(index);
        return;
    }
    cart[index].quantity = Math.round(quantity * 10) / 10;
    updateCartDisplay();
    saveCartToStorage();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
    saveCartToStorage();
}

function clearCart() {
    if (cart.length === 0) return;
    openConfirmModal('Clear Cart', 'Remove all items from your request?', function() {
        cart = [];
        updateCartDisplay();
        saveCartToStorage();
    });
}

function saveCartToStorage() {
    localStorage.setItem('requisition_cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const saved = localStorage.getItem('requisition_cart');
    if (saved) {
        try {
            const parsedCart = JSON.parse(saved);
            if (Array.isArray(parsedCart)) {
                cart = parsedCart.filter(item => item && item.id && item.quantity > 0);
            }
        } catch (e) {
            localStorage.removeItem('requisition_cart');
            cart = [];
        }
    }
}

document.getElementById('requisitionForm').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        showToast('Cart Empty', 'Please add items.', 'error');
        return;
    }
    
    e.preventDefault();
    const totalValue = cart.reduce((sum, item) => sum + (item.quantity * item.costPrice), 0);
    
    openConfirmModal(
        'Submit Request',
        `Submit requisition for ${cart.length} items?`,
        function() {
            const cartItemsInput = document.getElementById('cartItemsInput');
            if (cartItemsInput) cartItemsInput.value = JSON.stringify(cart);
            
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';
                submitBtn.disabled = true;
            }
            
            localStorage.removeItem('requisition_cart');
            localStorage.removeItem('requisition_cart_timestamp');
            
            document.getElementById('requisitionForm').submit();
        }
    );
});
</script>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>

{{-- PHP Helpers --}}
@php
function getCategoryIconClass($category) {
    $map = [
        'Flour & Grains' => 'bg-amber-100', 'Dairy Products' => 'bg-blue-100', 
        'Sweeteners' => 'bg-pink-100', 'Fats & Oils' => 'bg-yellow-100',
        'Fruits' => 'bg-red-100', 'Flavoring & Spices' => 'bg-green-100',
        'Nuts & Seeds' => 'bg-orange-100'
    ];
    return $map[$category] ?? 'bg-gray-100';
}
function getCategoryIcon($category) {
    $map = [
        'Flour & Grains' => 'fas fa-wheat', 'Dairy Products' => 'fas fa-cheese',
        'Sweeteners' => 'fas fa-candy-cane', 'Fats & Oils' => 'fas fa-bottle-droplet',
        'Fruits' => 'fas fa-apple-alt', 'Flavoring & Spices' => 'fas fa-pepper-hot',
        'Nuts & Seeds' => 'fas fa-seedling'
    ];
    return $map[$category] ?? 'fas fa-box';
}
@endphp
@endsection