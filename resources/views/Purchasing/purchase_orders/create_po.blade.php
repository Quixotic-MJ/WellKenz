@extends('Purchasing.layout.app')

@section('title', 'Order Builder')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6 font-sans text-gray-600">
    
    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Order Builder</h1>
            <p class="text-sm text-gray-500">Build your purchase order by selecting a supplier and adding items.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchasing.dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-home mr-2 opacity-70 group-hover:opacity-100"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="text-sm font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            <span class="text-sm font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    {{-- 3-PANEL GRID LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-300px)]">
        
        {{-- PANEL 1: SUPPLIER SELECT --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                <h3 class="font-display text-lg font-bold text-chocolate">1. Select Supplier</h3>
                <p class="text-xs text-gray-500 mt-0.5">Choose a supplier to see available items.</p>
            </div>
            
            <div class="p-6 flex-1 flex flex-col">
                {{-- Searchable Dropdown --}}
                <div class="relative mb-4">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" 
                           id="supplier-search" 
                           placeholder="Search suppliers..." 
                           class="w-full pl-10 pr-4 py-3 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
                </div>

                {{-- Supplier List --}}
                <div id="supplier-list" class="flex-1 overflow-y-auto custom-scrollbar space-y-2 max-h-96">
                    @foreach($suppliers ?? [] as $supplier)
                        <div class="supplier-item border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-chocolate hover:bg-chocolate/5 transition-all"
                             data-supplier-id="{{ $supplier->id }}"
                             data-supplier-name="{{ strtolower($supplier->name) }}"
                             data-pending-count="{{ $supplier->pending_items_count ?? 0 }}"
                             onclick="orderBuilder.selectSupplier({{ $supplier->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-bold text-chocolate">{{ $supplier->name }}</h4>
                                        @if(($supplier->pending_items_count ?? 0) > 0)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i>{{ $supplier->pending_items_count }} Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600">
                                                <i class="fas fa-check mr-1"></i>No Pending
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $supplier->supplier_code ?? 'No code' }}</p>
                                    @if($supplier->contact_person)
                                        <p class="text-xs text-gray-600 mt-1">{{ $supplier->contact_person }}</p>
                                    @endif
                                    @if($supplier->payment_terms)
                                        <p class="text-xs text-blue-600 mt-1">{{ $supplier->payment_terms }}-day terms</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="w-8 h-8 bg-chocolate/10 rounded-full flex items-center justify-center">
                                        <i class="fas fa-building text-chocolate text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- No Suppliers Message --}}
                @if(empty($suppliers))
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-building text-3xl mb-2"></i>
                        <p class="text-sm">No active suppliers found</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- PANEL 2: ITEM PICKER --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                <h3 class="font-display text-lg font-bold text-chocolate">2. Add Items</h3>
                <p class="text-xs text-gray-500 mt-0.5">Browse approved requests or full catalog.</p>
            </div>
            
            <div class="flex-1 flex flex-col">
                {{-- Tab Navigation --}}
                <div class="border-b border-border-soft">
                    <nav class="flex">
                        <button type="button" 
                                id="tab-requests" 
                                onclick="orderBuilder.switchTab('requests')"
                                class="flex-1 px-4 py-3 text-sm font-bold text-chocolate border-b-2 border-chocolate bg-chocolate/5">
                            <i class="fas fa-clipboard-list mr-2"></i>From Requests
                            <span id="requests-count" class="ml-1 bg-chocolate text-white text-xs px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                        <button type="button" 
                                id="tab-catalog" 
                                onclick="orderBuilder.switchTab('catalog')"
                                class="flex-1 px-4 py-3 text-sm font-bold text-gray-500 border-b-2 border-transparent hover:text-chocolate hover:bg-chocolate/5">
                            <i class="fas fa-boxes mr-2"></i>Full Catalog
                            <span id="catalog-count" class="ml-1 bg-gray-400 text-white text-xs px-1.5 py-0.5 rounded-full">0</span>
                        </button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="flex-1 flex flex-col p-6">
                    
                    {{-- From Requests Tab --}}
                    <div id="content-requests" class="flex-1 flex flex-col">
                        <div class="relative mb-4">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" 
                                   id="requests-search" 
                                   placeholder="Search requested items..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
                        </div>
                        
                        <div id="requests-list" class="flex-1 overflow-y-auto custom-scrollbar space-y-3 max-h-80">
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-clipboard-list text-3xl mb-2"></i>
                                <p class="text-sm">Select a supplier to view requested items</p>
                            </div>
                        </div>
                    </div>

                    {{-- Full Catalog Tab --}}
                    <div id="content-catalog" class="hidden flex-1 flex flex-col">
                        <div class="relative mb-4">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" 
                                   id="catalog-search" 
                                   placeholder="Search catalog items..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
                        </div>
                        
                        <div id="catalog-list" class="flex-1 overflow-y-auto custom-scrollbar space-y-3 max-h-80">
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-boxes text-3xl mb-2"></i>
                                <p class="text-sm">Select a supplier to view catalog</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL 3: ORDER SUMMARY --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                <h3 class="font-display text-lg font-bold text-chocolate">3. Order Summary</h3>
                <p class="text-xs text-gray-500 mt-0.5">Review and finalize your purchase order.</p>
            </div>
            
            <div class="flex-1 flex flex-col">
                {{-- Order Details Form --}}
                <div class="p-6 border-b border-border-soft space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-chocolate mb-2">Expected Delivery Date *</label>
                        <input type="date" 
                               id="expected_delivery_date" 
                               name="expected_delivery_date"
                               value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-chocolate mb-2">Payment Terms (days)</label>
                        <input type="number" 
                               id="payment_terms" 
                               name="payment_terms"
                               value="30"
                               class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel focus:bg-white transition-all text-sm shadow-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-chocolate mb-2">Notes</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="2"
                                  placeholder="Internal notes..."
                                  class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel/20 focus:border-caramel resize-none text-sm transition-all placeholder-gray-400 shadow-sm"></textarea>
                    </div>
                </div>

                {{-- Order Items Table --}}
                <div class="flex-1 flex flex-col">
                    <div class="px-6 py-3 bg-gray-50 border-b border-border-soft">
                        <div class="flex justify-between items-center">
                            <h4 class="font-bold text-gray-800 text-sm">Selected Items</h4>
                            <span id="order-items-count" class="text-xs font-semibold text-chocolate">0 items</span>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar max-h-80">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-20">Qty</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-20">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="order-items-table" class="bg-white divide-y divide-gray-100">
                                <tr id="no-items-row">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                                        <p class="text-sm">No items selected</p>
                                        <p class="text-xs mt-1">Add items from the middle panel</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Order Total --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-border-soft">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-600">Total Amount:</span>
                            <span id="order-total" class="text-xl font-bold text-chocolate">₱0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Create PO Button --}}
                <div class="p-6 border-t border-border-soft">
                    <button type="button" 
                            id="create-po-btn"
                            onclick="orderBuilder.createPO()"
                            disabled
                            class="w-full px-6 py-3 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Create Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class OrderBuilder {
    constructor() {
        this.selectedSupplier = null;
        this.supplierData = null;
        this.orderItems = [];
        this.currentTab = 'requests';
        
        this.init();
    }
    
    init() {
        // Search functionality
        document.getElementById('supplier-search')?.addEventListener('input', this.filterSuppliers.bind(this));
        document.getElementById('requests-search')?.addEventListener('input', this.filterRequests.bind(this));
        document.getElementById('catalog-search')?.addEventListener('input', this.filterCatalog.bind(this));
        
        // Set default dates
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 7);
        document.getElementById('expected_delivery_date').value = tomorrow.toISOString().split('T')[0];
    }
    
    selectSupplier(supplierId) {
        // Update UI
        document.querySelectorAll('.supplier-item').forEach(item => {
            item.classList.remove('border-chocolate', 'bg-chocolate/5');
        });
        
        const selectedItem = document.querySelector(`[data-supplier-id="${supplierId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('border-chocolate', 'bg-chocolate/5');
        }
        
        this.selectedSupplier = supplierId;
        this.loadSupplierData(supplierId);
    }
    
    loadSupplierData(supplierId) {
        // Show loading state
        this.showLoadingState();
        
        fetch(`/purchasing/api/supplier/${supplierId}/data`)
            .then(response => response.json())
            .then(data => {
                this.supplierData = data;
                this.renderItems();
                this.clearOrder(); // Clear cart when supplier changes
                this.autoAddPendingItems(); // Auto-add pending items from approved PRs
            })
            .catch(error => {
                console.error('Error loading supplier data:', error);
                this.showError('Failed to load supplier data');
            });
    }
    
    showLoadingState() {
        // Clear and show loading in both tabs
        document.getElementById('requests-list').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-chocolate text-2xl mb-2"></i>
                <p class="text-sm text-gray-600">Loading requested items...</p>
            </div>
        `;
        
        document.getElementById('catalog-list').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-chocolate text-2xl mb-2"></i>
                <p class="text-sm text-gray-600">Loading catalog items...</p>
            </div>
        `;
        
        // Update counts
        document.getElementById('requests-count').textContent = '0';
        document.getElementById('catalog-count').textContent = '0';
    }
    
    renderItems() {
        if (!this.supplierData) return;
        
        this.renderRequests();
        this.renderCatalog();
    }
    
    autoAddPendingItems() {
        const pendingItems = this.supplierData?.pending_items || [];
        
        if (pendingItems.length === 0) {
            return; // No pending items to add
        }
        
        let addedCount = 0;
        
        // Auto-add each pending item to the cart
        pendingItems.forEach(item => {
            // Add item with its remaining quantity and mark as auto-added
            const existingItemIndex = this.orderItems.findIndex(orderItem => orderItem.item_id === item.item_id);
            
            if (existingItemIndex >= 0) {
                // Update existing item
                this.orderItems[existingItemIndex].quantity += item.remaining_quantity;
                this.orderItems[existingItemIndex].auto_added = true;
            } else {
                // Add new auto-added item
                this.orderItems.push({
                    item_id: item.item_id,
                    item_name: item.item_name,
                    item_code: item.item_code,
                    quantity: item.remaining_quantity,
                    unit_price: 0, // Will be updated when supplier data is fully loaded
                    source: 'requests',
                    auto_added: true,
                    source_prs: item.source_prs
                });
            }
            addedCount++;
        });
        
        // Show toast notification
        this.showToast(`${addedCount} pending items auto-added from approved requests.`, 'success');
        
        // Re-render the order items with highlighting
        this.renderOrderItems();
    }

    renderRequests() {
        const container = document.getElementById('requests-list');
        const prItems = this.supplierData.pending_items || [];
        
        if (prItems.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-clipboard-list text-3xl mb-2"></i>
                    <p class="text-sm">No requested items available</p>
                    <p class="text-xs mt-1">Items from approved purchase requests will appear here</p>
                </div>
            `;
            document.getElementById('requests-count').textContent = '0';
            return;
        }
        
        container.innerHTML = prItems.map(item => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-chocolate hover:bg-chocolate/5 transition-all" data-item-id="${item.item_id}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm">${item.item_name}</h4>
                        <p class="text-xs text-gray-500 font-mono">${item.item_code}</p>
                        <p class="text-xs text-gray-600 mt-1">${item.category}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Remaining</div>
                        <div class="font-bold text-green-600">${item.remaining_quantity} ${item.unit_symbol}</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="text-xs text-gray-600 mb-2">Source Purchase Requests:</div>
                    <div class="space-y-1">
                        ${item.source_prs.map(pr => `
                            <div class="flex justify-between items-center text-xs bg-blue-50 border border-blue-100 rounded px-2 py-1">
                                <span class="font-mono text-blue-700">#${pr.pr_number}</span>
                                <span class="text-blue-600">${pr.remaining_quantity} ${item.unit_symbol}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <button type="button" 
                        onclick="orderBuilder.addItem('${item.item_id}', '${item.item_name}', '${item.item_code}', ${item.remaining_quantity}, 'requests')"
                        class="w-full px-3 py-2 bg-chocolate text-white text-xs font-bold rounded hover:bg-chocolate-dark transition-all">
                    <i class="fas fa-plus mr-1"></i>Add All (${item.remaining_quantity} ${item.unit_symbol})
                </button>
            </div>
        `).join('');
        
        document.getElementById('requests-count').textContent = prItems.length.toString();
    }
    
    renderCatalog() {
        const container = document.getElementById('catalog-list');
        const catalogItems = this.supplierData.catalog_items || [];
        
        if (catalogItems.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-boxes text-3xl mb-2"></i>
                    <p class="text-sm">No catalog items available</p>
                    <p class="text-xs mt-1">Items from this supplier's catalog will appear here</p>
                </div>
            `;
            document.getElementById('catalog-count').textContent = '0';
            return;
        }
        
        container.innerHTML = catalogItems.map(item => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-chocolate hover:bg-chocolate/5 transition-all" data-item-id="${item.item_id}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm">${item.item_name}</h4>
                        <p class="text-xs text-gray-500 font-mono">${item.item_code}</p>
                        <p class="text-xs text-gray-600 mt-1">${item.category}</p>
                        ${item.is_preferred ? '<span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Preferred</span>' : ''}
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Unit Price</div>
                        <div class="font-bold text-chocolate">₱${item.unit_price.toFixed(2)}</div>
                        <div class="text-xs text-gray-500 mt-1">${item.unit_symbol}</div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        ${item.minimum_order_quantity ? `Min order: ${item.minimum_order_quantity}` : ''}
                        ${item.lead_time_days ? ` • ${item.lead_time_days} days` : ''}
                    </div>
                    <button type="button" 
                            onclick="orderBuilder.addItem('${item.item_id}', '${item.item_name}', '${item.item_code}', 1, 'catalog', ${item.unit_price})"
                            class="px-3 py-2 bg-gray-600 text-white text-xs font-bold rounded hover:bg-gray-700 transition-all">
                        <i class="fas fa-plus mr-1"></i>Add
                    </button>
                </div>
            </div>
        `).join('');
        
        document.getElementById('catalog-count').textContent = catalogItems.length.toString();
    }
    
    addItem(itemId, itemName, itemCode, quantity, source, unitPrice = null) {
        // Check if item already exists in order
        const existingItemIndex = this.orderItems.findIndex(item => item.item_id === itemId);
        
        if (existingItemIndex >= 0) {
            // Update existing item
            this.orderItems[existingItemIndex].quantity += quantity;
        } else {
            // Add new item
            this.orderItems.push({
                item_id: itemId,
                item_name: itemName,
                item_code: itemCode,
                quantity: quantity,
                unit_price: unitPrice || 0,
                source: source
            });
        }
        
        this.renderOrderItems();
        this.updateCreateButtonState();
    }
    
    removeItem(itemId) {
        this.orderItems = this.orderItems.filter(item => item.item_id !== itemId);
        this.renderOrderItems();
        this.updateCreateButtonState();
    }
    
    updateItemQuantity(itemId, quantity) {
        const item = this.orderItems.find(item => item.item_id === itemId);
        if (item) {
            item.quantity = Math.max(0.01, parseFloat(quantity) || 0);
            this.renderOrderItems();
        }
    }
    
    updateItemPrice(itemId, price) {
        const item = this.orderItems.find(item => item.item_id === itemId);
        if (item) {
            item.unit_price = Math.max(0, parseFloat(price) || 0);
            this.renderOrderItems();
        }
    }
    
    renderOrderItems() {
        const tbody = document.getElementById('order-items-table');
        const noItemsRow = document.getElementById('no-items-row');
        
        if (this.orderItems.length === 0) {
            noItemsRow.style.display = '';
            document.getElementById('order-items-count').textContent = '0 items';
            document.getElementById('order-total').textContent = '₱0.00';
            return;
        }
        
        noItemsRow.style.display = 'none';
        
        tbody.innerHTML = this.orderItems.map(item => {
            const total = item.quantity * item.unit_price;
            const isAutoAdded = item.auto_added === true;
            const rowClass = isAutoAdded ? 'bg-blue-50 border-l-4 border-blue-400' : '';
            
            return `
                <tr class="${rowClass}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <div class="font-bold text-gray-800 text-sm">${item.item_name}</div>
                                <div class="text-xs text-gray-500 font-mono">${item.item_code}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="text-xs text-blue-600">
                                        <i class="fas fa-tag mr-1"></i>${item.source === 'requests' ? 'From PR' : 'Catalog'}
                                    </div>
                                    ${isAutoAdded ? '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800"><i class="fas fa-magic mr-1"></i>Auto-added</span>' : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="number" 
                               value="${item.quantity}" 
                               min="0.01" 
                               step="0.01"
                               onchange="orderBuilder.updateItemQuantity('${item.item_id}', this.value)"
                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-center focus:border-chocolate focus:ring-1 focus:ring-chocolate">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="relative">
                            <span class="absolute left-2 top-1.5 text-gray-500 text-xs">₱</span>
                            <input type="number" 
                                   value="${item.unit_price}" 
                                   min="0" 
                                   step="0.01"
                                   onchange="orderBuilder.updateItemPrice('${item.item_id}', this.value)"
                                   class="w-full pl-6 pr-2 py-1 text-sm border border-gray-300 rounded text-center focus:border-chocolate focus:ring-1 focus:ring-chocolate">
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-bold text-chocolate">₱${total.toFixed(2)}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button type="button" 
                                onclick="orderBuilder.removeItem('${item.item_id}')"
                                class="text-red-500 hover:text-red-700 p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update summary
        const totalItems = this.orderItems.length;
        const totalAmount = this.orderItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        
        document.getElementById('order-items-count').textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        document.getElementById('order-total').textContent = `₱${totalAmount.toFixed(2)}`;
    }
    
    clearOrder() {
        this.orderItems = [];
        this.renderOrderItems();
        this.updateCreateButtonState();
    }
    
    updateCreateButtonState() {
        const btn = document.getElementById('create-po-btn');
        const hasItems = this.orderItems.length > 0;
        const hasSupplier = this.selectedSupplier !== null;
        
        btn.disabled = !(hasItems && hasSupplier);
    }
    
    switchTab(tabName) {
        // Update tab states
        document.querySelectorAll('[id^="tab-"]').forEach(tab => {
            tab.classList.remove('text-chocolate', 'border-chocolate', 'bg-chocolate/5');
            tab.classList.add('text-gray-500', 'border-transparent');
        });
        
        document.querySelectorAll('[id^="content-"]').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Activate selected tab
        document.getElementById(`tab-${tabName}`).classList.remove('text-gray-500', 'border-transparent');
        document.getElementById(`tab-${tabName}`).classList.add('text-chocolate', 'border-chocolate', 'bg-chocolate/5');
        document.getElementById(`content-${tabName}`).classList.remove('hidden');
        
        this.currentTab = tabName;
    }
    
    filterSuppliers() {
        const search = document.getElementById('supplier-search').value.toLowerCase();
        document.querySelectorAll('.supplier-item').forEach(item => {
            const name = item.dataset.supplierName;
            item.style.display = name.includes(search) ? '' : 'none';
        });
    }
    
    filterRequests() {
        const search = document.getElementById('requests-search').value.toLowerCase();
        document.querySelectorAll('#requests-list [data-item-id]').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    }
    
    filterCatalog() {
        const search = document.getElementById('catalog-search').value.toLowerCase();
        document.querySelectorAll('#catalog-list [data-item-id]').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? '' : 'none';
        });
    }
    
    createPO() {
        if (this.orderItems.length === 0 || !this.selectedSupplier) {
            alert('Please select a supplier and add at least one item.');
            return;
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('supplier_id', this.selectedSupplier);
        formData.append('expected_delivery_date', document.getElementById('expected_delivery_date').value);
        formData.append('payment_terms', document.getElementById('payment_terms').value);
        formData.append('notes', document.getElementById('notes').value);
        
        // Add items
        this.orderItems.forEach((item, index) => {
            formData.append(`items[${index}][item_id]`, item.item_id);
            formData.append(`items[${index}][quantity]`, item.quantity);
            formData.append(`items[${index}][unit_price]`, item.unit_price);
        });
        
        // Submit form
        fetch('/purchasing/po', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '/purchasing/po/open';
            } else {
                alert('Error creating purchase order: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating purchase order. Please try again.');
        });
    }
    
    showToast(message, type = 'info') {
        // Create toast notification element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
        
        // Set toast style based on type
        switch(type) {
            case 'success':
                toast.className += ' bg-green-50 border border-green-200 text-green-800';
                toast.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
                break;
            case 'error':
                toast.className += ' bg-red-50 border border-red-200 text-red-800';
                toast.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
                break;
            default:
                toast.className += ' bg-blue-50 border border-blue-200 text-blue-800';
                toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
        }
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Remove after 4 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 4000);
    }

    showError(message) {
        this.showToast(message, 'error');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.orderBuilder = new OrderBuilder();
});
</script>

<style>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #e8dfd4 transparent;
}

.custom-scrollbar::-webkit-scrollbar { 
    width: 6px; 
}

.custom-scrollbar::-webkit-scrollbar-track { 
    background: transparent; 
}

.custom-scrollbar::-webkit-scrollbar-thumb { 
    background-color: #e8dfd4; 
    border-radius: 20px; 
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover { 
    background-color: #c48d3f; 
}

/* Additional scrollable container styling */
.scrollable-container {
    max-height: 400px;
    overflow-y: auto;
}

/* Ensure proper flex behavior for scrollable areas */
.flex-1.overflow-y-auto {
    min-height: 0;
}
</style>
@endsection