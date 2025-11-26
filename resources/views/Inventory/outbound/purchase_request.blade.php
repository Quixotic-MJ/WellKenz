@extends('Inventory.layout.app')

@section('content')
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-8rem)]">

    {{-- 1. CATALOG SECTION (Left) --}}
    <div class="flex-1 flex flex-col bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        {{-- Header Section --}}
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Catalog</h1>
                    <p class="text-sm text-gray-500 mt-1">Browse items and create your requisition slip.</p>
                </div>
                
                <div class="flex gap-3">
                    <div class="px-4 py-2 bg-amber-50 rounded-lg border border-amber-100 flex flex-col items-center min-w-[80px]">
                        <span class="text-[10px] uppercase font-bold text-amber-600 tracking-wider">Pending</span>
                        <span class="text-xl font-bold text-amber-800 leading-none mt-1">{{ $stats['pending'] ?? 0 }}</span>
                    </div>
                    <div class="px-4 py-2 bg-green-50 rounded-lg border border-green-100 flex flex-col items-center min-w-[80px]">
                        <span class="text-[10px] uppercase font-bold text-green-600 tracking-wider">Approved</span>
                        <span class="text-xl font-bold text-green-800 leading-none mt-1">{{ $stats['approved'] ?? 0 }}</span>
                    </div>
                    
                    <div class="flex gap-2 ml-2">
                        <button onclick="PRManager.openHistory()" class="w-10 h-full rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 flex items-center justify-center transition-colors" title="View History">
                            <i class="fas fa-history text-lg"></i>
                        </button>
                        <button onclick="PRManager.refreshData()" class="w-10 h-full rounded-lg bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center transition-colors" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Search items..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>
                
                <select id="categoryFilter" class="border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Categories</option>
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    @endif
                </select>

                <select id="stockFilter" class="border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">All Stock Status</option>
                    <option value="normal_stock">Normal Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="high_stock">High Stock</option>
                </select>

                <select id="priceFilter" class="border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">Any Price</option>
                    <option value="0-50">₱0 - ₱50</option>
                    <option value="51-100">₱51 - ₱100</option>
                    <option value="101-500">₱101 - ₱500</option>
                    <option value="500+">₱500+</option>
                </select>
            </div>
        </div>

        {{-- Items Grid --}}
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $item)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                             data-item-id="{{ $item->id }}"
                             data-category-id="{{ $item->category->id ?? 0 }}"
                             data-stock-status="{{ $item->stock_status ?? 'normal_stock' }}"
                             data-price="{{ $item->cost_price ?? 0 }}"
                             data-name="{{ strtolower($item->name) }}"
                             data-code="{{ strtolower($item->item_code ?? '') }}"
                             data-description="{{ strtolower($item->description ?? '') }}"
                             onclick="PRManager.addToCart({{ $item->id }})">
                            
                            <div class="flex justify-between items-start mb-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="text-right">
                                    @php
                                        $stockStatus = $item->stock_status ?? 'normal_stock';
                                        $badgeClass = match($stockStatus) {
                                            'out_of_stock' => 'bg-red-100 text-red-800',
                                            'low_stock' => 'bg-yellow-100 text-yellow-800',
                                            'high_stock' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-green-100 text-green-800'
                                        };
                                        $badgeText = match($stockStatus) {
                                            'out_of_stock' => 'Out of Stock',
                                            'low_stock' => 'Low Stock',
                                            'high_stock' => 'High Stock',
                                            default => 'In Stock'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $item->name }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $item->item_code ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>{{ $item->category->name ?? 'General' }}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-2">{{ $item->description ?? 'No description available.' }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <div>
                                    <span class="text-xs text-gray-500">Est. Cost</span>
                                    <div class="font-bold text-gray-900">₱{{ number_format($item->cost_price ?? 0, 2) }}</div>
                                </div>
                                <button class="w-8 h-8 rounded-lg bg-gray-900 text-white hover:bg-blue-600 transition-colors flex items-center justify-center">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            {{-- No Items Message --}}
            <div id="noItemsMessage" class="hidden flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-search text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">No items found</h3>
                <p class="text-sm text-gray-500 mt-1">Try adjusting your search criteria.</p>
            </div>
        </div>
    </div>

    {{-- 2. CART SECTION (Right Sidebar) --}}
    <div class="w-full lg:w-96 flex flex-col bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        {{-- Header --}}
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Requisition Slip</h2>
                    <p class="text-sm text-gray-500">{{ date('F d, Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="text-sm text-gray-500">Items</span>
                    <div class="text-2xl font-bold text-blue-600" id="cartCount">0</div>
                </div>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto p-6" id="cartContainer">
            <div id="emptyCartMessage" class="flex flex-col items-center justify-center h-full text-center opacity-60">
                <div class="w-16 h-16 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-shopping-cart text-2xl text-gray-300"></i>
                </div>
                <p class="text-sm font-medium text-gray-600">Your slip is empty</p>
                <p class="text-xs text-gray-400 mt-1">Add items from the catalog</p>
            </div>
        </div>

        {{-- Form Fields --}}
        <div class="p-6 border-t border-gray-200 bg-gray-50">
            <div class="space-y-4 mb-6">
                <div>
                    <input type="text" name="department" id="deptInput" required 
                           placeholder="Department" 
                           value="{{ $defaultDepartment ?? '' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <select name="priority" id="priorityInput" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                    </select>
                    <input type="date" name="request_date" id="dateInput" 
                           value="{{ date('Y-m-d') }}" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>
                <textarea name="notes" id="notesInput" rows="2" 
                          placeholder="Add notes or justification..." 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"></textarea>
            </div>

            {{-- Total and Submit --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Total Estimate</span>
                    <span class="text-xl font-bold text-gray-900" id="cartTotal">₱ 0.00</span>
                </div>
                
                <button type="button" onclick="PRManager.submitPR()" id="submitBtn" disabled 
                        class="w-full py-3 bg-gray-100 text-gray-400 font-semibold rounded-lg cursor-not-allowed transition-colors">
                    Submit Request
                </button>
                <p class="text-center text-xs text-gray-400" id="totalItems">0 items selected</p>
            </div>
        </div>
    </div>
</div>

{{-- HISTORY MODAL --}}
<div id="historyModalBackdrop" class="hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-6xl max-h-[80vh] flex flex-col">
        
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Request History</h2>
                    <p class="text-sm text-gray-500">Track status of your previous requests.</p>
                </div>
            </div>
            <button onclick="PRManager.closeHistory()" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-4 border-b border-gray-200 flex gap-4 items-center">
            <select id="historyStatusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="historyDepartmentFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="all">All Departments</option>
                @if(isset($departments) && count($departments) > 0)
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                @endif
            </select>
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input type="text" id="historySearchInput" placeholder="Search by PR # or Dept..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="historyTableBody">
                    @if(isset($purchaseRequests) && count($purchaseRequests) > 0)
                        @foreach($purchaseRequests as $pr)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900">{{ $pr->pr_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($pr->request_date)->format('M d, Y') }}
                                    <span class="text-xs text-gray-400 block">{{ \Carbon\Carbon::parse($pr->created_at)->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $pr->department }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $pClass = match($pr->priority) {
                                            'urgent' => 'bg-red-100 text-red-800',
                                            'high' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-blue-100 text-blue-800'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pClass }}">
                                        {{ ucfirst($pr->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱ {{ number_format($pr->total_estimated_cost, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $sClass = match($pr->status) {
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sClass }}">
                                        {{ ucfirst($pr->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="PRManager.viewDetails({{ $pr->id }})" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($pr->status === 'pending' || $pr->status === 'draft')
                                            <button onclick="PRManager.cancelPR({{ $pr->id }})" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                                    </div>
                                    <span class="text-gray-500">No purchase requests found.</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Request Details</h3>
            <button onclick="PRManager.closeDetails()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div id="detailsContent" class="p-6 overflow-y-auto max-h-[60vh]"></div>
    </div>
</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-question text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2" id="confirmTitle">Confirm</h3>
        <p class="text-gray-500 mb-6" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeConfirmModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
            <button id="confirmBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Confirm</button>
        </div>
    </div>
</div>

{{-- TOAST NOTIFICATION --}}
<div id="toast" class="hidden fixed top-4 right-4 z-50 transform transition-all duration-300">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 flex items-center gap-3 min-w-[300px]">
        <div id="toastIconContainer" class="w-8 h-8 rounded-full flex items-center justify-center">
            <i id="toastIcon" class="fas fa-check text-sm"></i>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900" id="toastTitle">Notification</h4>
            <p class="text-xs text-gray-500 mt-0.5" id="toastMessage">Message details...</p>
        </div>
    </div>
</div>

<script>
// JavaScript functionality remains the same
const PRManager = {
    cart: [],
    els: {
        cartContainer: document.getElementById('cartContainer'),
        cartCount: document.getElementById('cartCount'),
        cartTotal: document.getElementById('cartTotal'),
        totalItems: document.getElementById('totalItems'),
        submitBtn: document.getElementById('submitBtn'),
        emptyCart: document.getElementById('emptyCartMessage'),
        
        history: {
            backdrop: document.getElementById('historyModalBackdrop'),
            tableBody: document.getElementById('historyTableBody'),
            statusFilter: document.getElementById('historyStatusFilter'),
            departmentFilter: document.getElementById('historyDepartmentFilter'),
            searchInput: document.getElementById('historySearchInput')
        },
        details: {
            backdrop: document.getElementById('detailsModalBackdrop'),
            content: document.getElementById('detailsContent')
        }
    },

    init() {
        this.setupListeners();
        this.loadCart();
    },

    setupListeners() {
        if (this.els.history.statusFilter) this.els.history.statusFilter.addEventListener('change', () => this.filterHistory());
        if (this.els.history.departmentFilter) this.els.history.departmentFilter.addEventListener('change', () => this.filterHistory());
        if (this.els.history.searchInput) this.els.history.searchInput.addEventListener('input', this.debounce(() => this.filterHistory(), 300));
        
        // Filter logic for main catalog
        ['categoryFilter', 'stockFilter', 'priceFilter', 'searchInput'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener(id === 'searchInput' ? 'input' : 'change', () => this.filterCatalog());
            }
        });
    },

    filterCatalog() {
        const cat = document.getElementById('categoryFilter').value;
        const stock = document.getElementById('stockFilter').value;
        const price = document.getElementById('priceFilter').value;
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        
        const items = document.querySelectorAll('[data-item-id]');
        let visibleCount = 0;

        items.forEach(card => {
            const itemCat = card.dataset.categoryId;
            const itemStock = card.dataset.stockStatus;
            const itemPrice = parseFloat(card.dataset.price);
            const itemName = card.dataset.name;
            const itemCode = card.dataset.code;
            const itemDesc = card.dataset.description;
            
            let show = true;
            
            if (cat !== 'all' && itemCat !== cat) show = false;
            if (stock !== 'all' && itemStock !== stock) show = false;
            
            if (price !== 'all') {
                const priceMatch = this.matchesPriceFilter(itemPrice, price);
                if (!priceMatch) show = false;
            }
            
            if (search && !itemName.includes(search) && !itemCode.includes(search) && !itemDesc.includes(search)) {
                show = false;
            }
            
            if (show) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noMsg = document.getElementById('noItemsMessage');
        
        if (visibleCount === 0) {
            noMsg.classList.remove('hidden');
        } else {
            noMsg.classList.add('hidden');
        }
    },
    
    matchesPriceFilter(price, filter) {
        switch(filter) {
            case '0-50': return price >= 0 && price <= 50;
            case '51-100': return price >= 51 && price <= 100;
            case '101-500': return price >= 101 && price <= 500;
            case '500+': return price > 500;
            default: return true;
        }
    },

    addToCart(id) {
        const itemElement = document.querySelector(`[data-item-id="${id}"]`);
        if (!itemElement) return;
        
        const itemName = itemElement.dataset.name;
        const itemPrice = parseFloat(itemElement.dataset.price);
        const itemCode = itemElement.dataset.code;
        
        const existing = this.cart.find(c => c.id === id);
        if (existing) {
            existing.qty++;
        } else {
            this.cart.push({ 
                id: id, 
                name: itemName.charAt(0).toUpperCase() + itemName.slice(1),
                code: itemCode.toUpperCase(), 
                price: itemPrice, 
                qty: 1 
            });
        }
        
        this.updateCartUI();
        this.saveCart();
        showToast('Added to Slip', `${itemName} has been added.`);
    },

    updateCartUI() {
        this.els.cartContainer.innerHTML = '';
        
        if (this.cart.length === 0) {
            this.els.emptyCart.classList.remove('hidden');
            this.els.submitBtn.disabled = true;
            this.els.submitBtn.className = 'w-full py-3 bg-gray-100 text-gray-400 font-semibold rounded-lg cursor-not-allowed';
            this.els.cartCount.textContent = 0;
            this.els.cartTotal.textContent = '₱ 0.00';
            this.els.totalItems.textContent = '0 items selected';
            return;
        }

        this.els.emptyCart.classList.add('hidden');
        this.els.submitBtn.disabled = false;
        this.els.submitBtn.className = 'w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors';

        let total = 0;
        let totalItems = 0;

        this.cart.forEach((c, idx) => {
            total += c.price * c.qty;
            totalItems += c.qty;

            const row = document.createElement('div');
            row.className = 'border border-gray-200 rounded-lg p-4 mb-3';
            row.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">${c.name}</span>
                        <span class="text-xs text-gray-500 block">${c.code}</span>
                    </div>
                    <button onclick="PRManager.removeCartItem(${idx})" class="text-gray-400 hover:text-red-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">₱${(c.price * c.qty).toFixed(2)}</span>
                    <div class="flex items-center gap-2">
                        <button onclick="PRManager.changeQty(${idx}, -1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100">-</button>
                        <input type="number" min="1" step="1" value="${c.qty}" 
                               onchange="PRManager.updateCartItemQty(${idx}, this.value)" 
                               class="w-12 text-center text-sm border border-gray-300 rounded px-2 py-1">
                        <button onclick="PRManager.changeQty(${idx}, 1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100">+</button>
                    </div>
                </div>
            `;
            this.els.cartContainer.appendChild(row);
        });

        this.els.cartCount.textContent = this.cart.length;
        this.els.cartTotal.textContent = '₱ ' + total.toFixed(2);
        this.els.totalItems.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} selected`;
    },

    updateCartItemQty(idx, qty) {
        const newQty = parseInt(qty);
        if (newQty > 0) {
            this.cart[idx].qty = newQty;
        } else {
            this.cart.splice(idx, 1);
        }
        this.updateCartUI();
        this.saveCart();
    },

    changeQty(idx, delta) {
        this.cart[idx].qty += delta;
        if (this.cart[idx].qty <= 0) {
            this.cart.splice(idx, 1);
        }
        this.updateCartUI();
        this.saveCart();
    },

    removeCartItem(idx) {
        this.cart.splice(idx, 1);
        this.updateCartUI();
        this.saveCart();
    },

    clearCart() {
        this.cart = [];
        this.updateCartUI();
        this.saveCart();
        showToast('Cleared', 'Requisition slip has been cleared.');
    },

    saveCart() { localStorage.setItem('emp_pr_cart', JSON.stringify(this.cart)); },
    loadCart() {
        const s = localStorage.getItem('emp_pr_cart');
        if (s) { 
            try { 
                const parsed = JSON.parse(s);
                this.cart = parsed.filter(item => item && typeof item.id !== 'undefined' && typeof item.price === 'number' && !isNaN(item.price));
                this.updateCartUI(); 
            } catch(e) { 
                this.cart = []; 
                localStorage.removeItem('emp_pr_cart');
            } 
        }
    },

    submitPR() {
        const dept = document.getElementById('deptInput').value.trim();
        const prio = document.getElementById('priorityInput').value;
        const date = document.getElementById('dateInput').value;
        const notes = document.getElementById('notesInput').value.trim();

        if (!dept) { 
            showToast('Missing Information', 'Please enter your department.', 'error'); 
            document.getElementById('deptInput').focus();
            return; 
        }
        if (this.cart.length === 0) {
            showToast('No Items', 'Please add items to your requisition slip.', 'error');
            return;
        }

        const data = {
            department: dept,
            priority: prio,
            request_date: date,
            notes: notes,
            items: this.cart.map(c => ({ item_id: c.id, quantity_requested: c.qty, unit_price_estimate: c.price }))
        };

        this.els.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        this.els.submitBtn.disabled = true;

        fetch('{{ route("inventory.purchase-requests.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                this.cart = [];
                this.saveCart();
                this.updateCartUI();
                showToast('Success', 'Purchase Request submitted successfully!');
                document.getElementById('notesInput').value = '';
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error', res.message || 'Failed to submit.', 'error');
                this.updateCartUI();
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error', 'System error occurred.', 'error');
            this.updateCartUI();
        });
    },

    cancelPR(id) {
        openConfirmModal('Cancel Request?', 'This action cannot be undone.', () => {
            fetch(`/inventory/purchase-requests/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    showToast('Cancelled', 'Request removed.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Could not cancel.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error', 'Failed to cancel request.', 'error');
            });
        });
    },

    viewDetails(id) {
        fetch(`/inventory/purchase-requests/${id}`)
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    const d = res.data;
                    let html = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <span class="text-sm text-gray-500">Reference</span>
                                    <div class="font-bold text-gray-900">${d.pr_number}</div>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Total Cost</span>
                                    <div class="font-bold text-blue-600">${d.formatted_total}</div>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Department</span>
                                    <div class="text-gray-900">${d.department}</div>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Date</span>
                                    <div class="text-gray-900">${new Date(d.request_date).toLocaleDateString()}</div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Items Requested</h4>
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="p-3 text-left">Item</th>
                                                <th class="p-3 text-right">Qty</th>
                                                <th class="p-3 text-right">Unit Price</th>
                                                <th class="p-3 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            ${d.items.map(i => `
                                                <tr>
                                                    <td class="p-3">${i.item_name}</td>
                                                    <td class="p-3 text-right text-gray-500">${i.quantity_requested}</td>
                                                    <td class="p-3 text-right text-gray-500">₱${parseFloat(i.unit_price_estimate).toFixed(2)}</td>
                                                    <td class="p-3 text-right font-medium text-gray-700">₱${parseFloat(i.total_estimated_cost).toFixed(2)}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            ${d.notes ? `<div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200"><span class="text-yellow-800 text-sm font-medium block mb-1">Notes</span><p class="text-yellow-900 text-sm">${d.notes}</p></div>` : ''}
                        </div>
                    `;
                    this.els.details.content.innerHTML = html;
                    this.openModal('details');
                }
            })
            .catch(err => { console.error(err); showToast('Error', 'Failed to load details.', 'error'); });
    },

    filterHistory() {
        const status = this.els.history.statusFilter?.value || 'all';
        const department = this.els.history.departmentFilter?.value || 'all';
        const search = this.els.history.searchInput?.value.toLowerCase() || '';

        const rows = this.els.history.tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.children.length < 7) return; 
            const prNumber = row.children[0].textContent.toLowerCase();
            const dept = row.children[2].textContent.toLowerCase();
            const rowStatus = row.children[5].textContent.toLowerCase();

            let showRow = true;
            if (status !== 'all' && !rowStatus.includes(status)) showRow = false;
            if (department !== 'all' && !dept.includes(department.toLowerCase())) showRow = false;
            if (search && !prNumber.includes(search) && !dept.includes(search)) showRow = false;
            row.style.display = showRow ? '' : 'none';
        });
    },

    refreshData() { location.reload(); },
    openHistory() { this.openModal('history'); },
    closeHistory() { this.closeModal('history'); },
    closeDetails() { this.closeModal('details'); },
    openModal(name) {
        const m = this.els[name];
        if (!m) return;
        m.backdrop.classList.remove('hidden');
    },
    closeModal(name) {
        const m = this.els[name];
        if (!m) return;
        m.backdrop.classList.add('hidden');
    },
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => { clearTimeout(timeout); func(...args); };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

let confirmCb = null;
function openConfirmModal(t, m, cb) {
    document.getElementById('confirmTitle').textContent = t;
    document.getElementById('confirmMessage').textContent = m;
    confirmCb = cb;
    document.getElementById('confirmModal').classList.remove('hidden');
}
function closeConfirmModal() { 
    document.getElementById('confirmModal').classList.add('hidden'); 
    confirmCb = null; 
}
document.getElementById('confirmBtn').onclick = () => { if(confirmCb) confirmCb(); closeConfirmModal(); };

function showToast(t, m, type='success') {
    const toast = document.getElementById('toast');
    const container = document.getElementById('toastIconContainer');
    const icon = document.getElementById('toastIcon');
    
    document.getElementById('toastTitle').textContent = t;
    document.getElementById('toastMessage').textContent = m;

    if(type === 'error') {
        container.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-red-100';
        icon.className = 'fas fa-times text-red-600 text-sm';
    } else {
        container.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-green-100';
        icon.className = 'fas fa-check text-green-600 text-sm';
    }

    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

document.addEventListener('DOMContentLoaded', () => { PRManager.init(); });
</script>
@endsection