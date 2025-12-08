@extends('Purchasing.layout.app')

@section('title', 'Order Builder')

@section('content')
<div class="w-full px-6 space-y-6 font-sans text-gray-600 h-[calc(100vh-100px)]">
    
    {{-- HEADER WITH SUPPLIER DROPDOWN --}}
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Order Builder</h1>
            <p class="text-sm text-gray-500">Build your purchase order by selecting a supplier and adding items.</p>
        </div>
        <div class="flex items-center gap-4">
            {{-- SUPPLIER DROPDOWN --}}
            <div class="relative">
                <button type="button" 
                        id="supplier-dropdown-btn"
                        onclick="orderBuilder.toggleSupplierDropdown()"
                        class="inline-flex items-center px-4 py-2 bg-white border border-border-soft text-gray-700 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm">
                    <i class="fas fa-building mr-2 opacity-70"></i>
                    <span id="selected-supplier-text">Select Supplier</span>
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                
                <div id="supplier-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-border-soft rounded-xl shadow-lg z-50 max-h-96 overflow-hidden">
                    <div class="p-4 border-b border-border-soft bg-cream-bg">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                            <input type="text" 
                                   id="supplier-search" 
                                   placeholder="Search suppliers..." 
                                   class="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20 focus:border-caramel">
                        </div>
                    </div>
                    
                    <div id="supplier-list" class="max-h-80 overflow-y-auto custom-scrollbar">
                        @foreach($suppliers ?? [] as $supplier)
                            <div class="supplier-item p-3 cursor-pointer hover:bg-chocolate/5 transition-all border-b border-gray-100 last:border-b-0"
                                 data-supplier-id="{{ $supplier->id }}"
                                 data-supplier-name="{{ strtolower($supplier->name) }}"
                                 data-pending-count="{{ $supplier->pending_items_count ?? 0 }}"
                                 onclick="orderBuilder.selectSupplier({{ $supplier->id }})">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-bold text-chocolate text-sm">{{ $supplier->name }}</h4>
                                            @if(($supplier->pending_items_count ?? 0) > 0)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800">
                                                    <i class="fas fa-clock mr-1"></i>{{ $supplier->pending_items_count }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-600">
                                                    <i class="fas fa-check mr-1"></i>0
                                                </span>
                                            @endif
                                        </div>
                                        @if($supplier->payment_terms)
                                            <p class="text-xs text-blue-600">{{ $supplier->payment_terms }}-day terms</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="w-6 h-6 bg-chocolate/10 rounded-full flex items-center justify-center">
                                            <i class="fas fa-building text-chocolate text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if(empty($suppliers))
                            <div class="text-center py-6 text-gray-400">
                                <i class="fas fa-building text-2xl mb-2"></i>
                                <p class="text-sm">No active suppliers found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
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

    {{-- FULL WORKSPACE LAYOUT --}}
    <div class="h-[calc(100vh-200px)]">
        
        {{-- WORKSPACE: Item Picker + Order Summary --}}
        <div class="grid grid-cols-12 gap-6 h-full">
            
            {{-- WORKSPACE PANEL 1: ITEM PICKER (col-span-8) --}}
            <div class="col-span-8 bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-border-soft bg-cream-bg">
                    <h3 class="font-display text-lg font-bold text-chocolate">2. Add Items</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Browse approved requests or full catalog.</p>
                </div>
                
                <div class="flex-1 flex flex-col min-h-0">
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
                            
                            <div id="requests-list" class="flex-1 overflow-y-auto custom-scrollbar space-y-3">
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
                            
                            <div id="catalog-list" class="flex-1 overflow-y-auto custom-scrollbar space-y-3">
                                <div class="text-center py-8 text-gray-400">
                                    <i class="fas fa-boxes text-3xl mb-2"></i>
                                    <p class="text-sm">Select a supplier to view catalog</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WORKSPACE PANEL 2: ORDER SUMMARY (col-span-4) --}}
            <div class="col-span-4 bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="px-4 lg:px-6 py-3 lg:py-4 border-b border-border-soft bg-cream-bg">
                    <h3 class="font-display text-base lg:text-lg font-bold text-chocolate">3. Order Summary</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Review and finalize your purchase order.</p>
                </div>
                
                <div class="flex-1 flex flex-col min-h-0">
                    {{-- Order Details Form --}}
                    <div class="p-4 lg:p-6 border-b border-border-soft space-y-3 lg:space-y-4">
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
                                      class="w-full px-4 py-2.5 border-gray-200 bg-gray-50 rounded-lg focus:ring-2 focus:ring-caramel resize-none text-sm transition-all placeholder-gray-400 shadow-sm"></textarea>
                        </div>
                    </div>

                    {{-- Order Items Table --}}
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="px-4 lg:px-6 py-3 bg-gray-50 border-b border-border-soft">
                            <div class="flex justify-between items-center">
                                <h4 class="font-bold text-gray-800 text-sm">Selected Items</h4>
                                <span id="order-items-count" class="text-xs font-semibold text-chocolate">0 items</span>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto custom-scrollbar">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Qty</th>
                                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28">Price</th>
                                        <th class="px-3 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Total</th>
                                        <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="order-items-table" class="bg-white divide-y divide-gray-100">
                                    <tr id="no-items-row">
                                        <td colspan="5" class="px-2 lg:px-4 py-6 lg:py-8 text-center text-gray-400">
                                            <i class="fas fa-shopping-cart text-xl lg:text-2xl mb-2"></i>
                                            <p class="text-sm">No items selected</p>
                                            <p class="text-xs mt-1">Add items from the middle panel</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Order Total --}}
                        <div class="px-4 lg:px-6 py-4 bg-gray-50 border-t border-border-soft">
                            <div class="flex justify-between items-center">
                                <span class="text-sm lg:text-sm font-bold text-gray-600">Total Amount:</span>
                                <span id="order-total" class="text-lg lg:text-xl font-bold text-chocolate">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Create PO Button --}}
                    <div class="p-4 lg:p-6 border-t border-border-soft">
                        <button type="button" 
                                id="create-po-btn"
                                onclick="orderBuilder.createPO()"
                                disabled
                                class="w-full px-4 lg:px-6 py-2 lg:py-3 bg-chocolate text-white text-sm lg:text-base font-bold rounded-lg hover:bg-chocolate-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i>Create Purchase Order
                        </button>
                    </div>
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
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('supplier-dropdown');
            const button = document.getElementById('supplier-dropdown-btn');
            
            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Set default dates
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 7);
        document.getElementById('expected_delivery_date').value = tomorrow.toISOString().split('T')[0];
    }
    
    toggleSupplierDropdown() {
        const dropdown = document.getElementById('supplier-dropdown');
        dropdown.classList.toggle('hidden');
    }
    
    selectSupplier(supplierId) {
        // Update UI - find supplier in dropdown
        document.querySelectorAll('#supplier-dropdown .supplier-item').forEach(item => {
            item.classList.remove('bg-chocolate/10');
        });
        
        const selectedItem = document.querySelector(`#supplier-dropdown [data-supplier-id="${supplierId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('bg-chocolate/10');
            
            // Update button text
            const supplierName = selectedItem.querySelector('h4').textContent;
            document.getElementById('selected-supplier-text').textContent = supplierName;
        }
        
        // Close dropdown
        document.getElementById('supplier-dropdown').classList.add('hidden');
        
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
        let updatedCount = 0;
        
        // Auto-add each pending item to the cart
        pendingItems.forEach(item => {
            // Check if item already exists in order (including auto-added items)
            const existingItemIndex = this.orderItems.findIndex(orderItem => orderItem.item_id === item.item_id);
            
            if (existingItemIndex >= 0) {
                // Check if the existing item is auto-added or if we need to merge quantities
                const existingItem = this.orderItems[existingItemIndex];
                
                // If existing item is not auto-added, merge the quantities
                if (!existingItem.auto_added) {
                    existingItem.quantity += item.remaining_quantity;
                    existingItem.auto_added = true; // Mark as auto-added since it now includes pending items
                    // Merge source_prs if available
                    if (item.source_prs && Array.isArray(item.source_prs)) {
                        if (!existingItem.source_prs) {
                            existingItem.source_prs = [];
                        }
                        // Add source_prs that don't already exist
                        item.source_prs.forEach(pr => {
                            const prExists = existingItem.source_prs.some(existingPr => existingPr.pr_id === pr.pr_id);
                            if (!prExists) {
                                existingItem.source_prs.push(pr);
                            }
                        });
                    }
                    updatedCount++;
                } else {
                    // Item is already auto-added, skip to avoid duplicate
                    console.log(`Skipping duplicate auto-add for item: ${item.item_name}`);
                }
            } else {
                // Add new auto-added item - ensure unit_price is properly set
                const unitPrice = parseFloat(item.unit_price) || 0;
                this.orderItems.push({
                    item_id: item.item_id,
                    item_name: item.item_name,
                    item_code: item.item_code,
                    quantity: item.remaining_quantity,
                    unit_price: unitPrice,
                    source: 'requests',
                    auto_added: true,
                    source_prs: item.source_prs
                });
                
                // Debug log to check if price is being set correctly
                console.log(`Auto-added item ${item.item_name} with price: ₱${unitPrice.toFixed(2)}`);
                addedCount++;
            }
        });
        
        // Show toast notification with proper counts
        const messages = [];
        if (addedCount > 0) messages.push(`${addedCount} new items added`);
        if (updatedCount > 0) messages.push(`${updatedCount} existing items updated`);
        
        if (messages.length > 0) {
            this.showToast(`${messages.join(', ')} from approved requests.`, 'success');
        }
        
        // Re-render the order items with highlighting
        this.renderOrderItems();
        this.updateCreateButtonState();
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
        
        container.innerHTML = `
            <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                ${prItems.map(item => `
                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-chocolate/5 transition-all" data-item-id="${item.item_id}">
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
                                onclick="orderBuilder.addItem('${item.item_id}', '${item.item_name}', '${item.item_code}', ${item.remaining_quantity}, 'requests', ${item.unit_price})"
                                class="w-full px-3 py-2 bg-chocolate text-white text-xs font-bold rounded hover:bg-chocolate-dark transition-all ${this.orderItems.find(orderItem => String(orderItem.item_id) === String(item.item_id)) ? 'opacity-75' : ''}">
                            <i class="fas fa-plus mr-1"></i>Add All (${item.remaining_quantity} ${item.unit_symbol})
                            ${this.orderItems.find(orderItem => String(orderItem.item_id) === String(item.item_id)) ? '<br><small class="text-xs opacity-90">Update existing</small>' : ''}
                        </button>
                    </div>
                `).join('')}
            </div>
        `;
        
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
        
        container.innerHTML = `
            <div class="grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                ${catalogItems.map(item => `
                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-chocolate/5 transition-all" data-item-id="${item.item_id}">
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
                `).join('')}
            </div>
        `;
        
        document.getElementById('catalog-count').textContent = catalogItems.length.toString();
    }
    
    addItem(itemId, itemName, itemCode, quantity, source, unitPrice = null) {
        // Check if item already exists in order
        const existingItemIndex = this.orderItems.findIndex(item => String(item.item_id) === String(itemId));
        
        if (existingItemIndex >= 0) {
            // Update existing item - add to existing quantity
            const existingItem = this.orderItems[existingItemIndex];
            const oldQuantity = existingItem.quantity;
            existingItem.quantity += quantity;
            
            // Show notification about the merge
            this.showToast(`Updated ${itemName}: ${oldQuantity} + ${quantity} = ${existingItem.quantity}`, 'info');
        } else {
            // Add new item - ensure unit_price is properly handled
            const price = parseFloat(unitPrice) || 0;
            this.orderItems.push({
                item_id: itemId,
                item_name: itemName,
                item_code: itemCode,
                quantity: quantity,
                unit_price: price,
                source: source,
                auto_added: false // Mark as manually added
            });
            
            // Debug log to check if price is being set correctly
            console.log(`Added item ${itemName} with price: ₱${price.toFixed(2)}`);
            
            // Show success notification for new items
            this.showToast(`Added ${itemName} (${quantity})`, 'success');
        }
        
        this.renderOrderItems();
        this.updateCreateButtonState();
    }
    
    removeItem(itemId) {
        console.log('removeItem called with itemId:', itemId);
        console.log('Current orderItems:', this.orderItems);
        
        // Ensure itemId is treated as string for consistent comparison
        const targetItemId = String(itemId);
        const initialCount = this.orderItems.length;
        
        // Filter out the item with matching ID (convert to string for consistent comparison)
        this.orderItems = this.orderItems.filter(item => String(item.item_id) !== targetItemId);
        
        const finalCount = this.orderItems.length;
        console.log(`Removed item. Items before: ${initialCount}, after: ${finalCount}`);
        
        // Update UI
        this.renderOrderItems();
        this.updateCreateButtonState();
        
        // Show confirmation toast
        this.showToast('Item removed from order', 'info');
    }
    
    updateItemQuantity(itemId, quantity, immediate = false) {
        const item = this.orderItems.find(item => String(item.item_id) === String(itemId));
        if (item) {
            const newQuantity = Math.max(0.01, parseFloat(quantity) || 0);
            if (item.quantity !== newQuantity) {
                item.quantity = newQuantity;
                
                // Only update totals and re-render if immediate or on change event
                if (immediate) {
                    this.updateOrderTotals();
                    this.renderOrderItems();
                } else {
                    // For real-time input, just update totals without re-rendering
                    this.updateOrderTotals();
                }
            }
        }
    }
    
    updateItemPrice(itemId, price, immediate = false) {
        const item = this.orderItems.find(item => String(item.item_id) === String(itemId));
        if (item) {
            const newPrice = Math.max(0, parseFloat(price) || 0);
            if (item.unit_price !== newPrice) {
                item.unit_price = newPrice;
                
                // Only update totals and re-render if immediate or on change event
                if (immediate) {
                    this.updateOrderTotals();
                    this.renderOrderItems();
                } else {
                    // For real-time input, just update totals without re-rendering
                    this.updateOrderTotals();
                }
            }
        }
    }
    
    updateOrderTotals() {
        const totalItems = this.orderItems.length;
        const totalAmount = this.orderItems.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        
        document.getElementById('order-items-count').textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
        document.getElementById('order-total').textContent = `₱${totalAmount.toFixed(2)}`;
    }
    
    renderOrderItems() {
        const tbody = document.getElementById('order-items-table');
        
        if (this.orderItems.length === 0) {
            // Set tbody innerHTML to the empty state HTML string directly
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-2 lg:px-4 py-6 lg:py-8 text-center text-gray-400">
                        <i class="fas fa-shopping-cart text-xl lg:text-2xl mb-2"></i>
                        <p class="text-sm">No items selected</p>
                        <p class="text-xs mt-1">Add items from the middle panel</p>
                    </td>
                </tr>
            `;
            // Update totals for empty state
            this.updateOrderTotals();
            return;
        }
        
        // Set tbody innerHTML to the item rows HTML string
        tbody.innerHTML = this.orderItems.map(item => {
            const total = item.quantity * item.unit_price;
            const isAutoAdded = item.auto_added === true;
            const rowClass = isAutoAdded ? 'bg-blue-50 border-l-4 border-blue-400' : '';
            
            return `
                <tr class="${rowClass}">
                    <td class="px-3 py-3">
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
                    <td class="px-3 py-3 text-center">
                        <input type="number" 
                               data-quantity-item-id="${item.item_id}"
                               value="${item.quantity}" 
                               min="0.01" 
                               step="0.01"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded text-center focus:border-chocolate focus:ring-1 focus:ring-chocolate quantity-input">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="relative">
                            <span class="absolute left-2 top-2 text-gray-500 text-xs">₱</span>
                            <input type="number" 
                                   data-price-item-id="${item.item_id}"
                                   value="${item.unit_price}" 
                                   min="0" 
                                   step="0.01"
                                   class="w-full pl-6 pr-3 py-2 text-sm border border-gray-300 rounded text-center focus:border-chocolate focus:ring-1 focus:ring-chocolate price-input">
                        </div>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="font-bold text-chocolate">₱${total.toFixed(2)}</span>
                    </td>
                    <td class="px-2 py-3 text-center">
                        <button type="button" 
                                data-remove-item-id="${item.item_id}"
                                class="text-red-500 hover:text-red-700 p-1 remove-item-btn">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update summary using the dedicated method
        this.updateOrderTotals();
        
        // Add event listeners for remove buttons
        this.addRemoveButtonListeners();
    }
    
    addRemoveButtonListeners() {
        // Remove existing listeners to avoid duplicates
        const existingButtons = document.querySelectorAll('.remove-item-btn');
        existingButtons.forEach(button => {
            button.replaceWith(button.cloneNode(true));
        });
        
        // Add new event listeners for remove buttons
        const removeButtons = document.querySelectorAll('.remove-item-btn');
        removeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const itemId = button.getAttribute('data-remove-item-id');
                console.log('Remove button clicked for item ID:', itemId);
                this.removeItem(itemId);
            });
        });
        
        // Add event listeners for quantity inputs
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            // Real-time update during typing (no re-render)
            input.addEventListener('input', (e) => {
                const itemId = input.getAttribute('data-quantity-item-id');
                this.updateItemQuantity(itemId, input.value, false);
            });
            // Full update when user finishes editing
            input.addEventListener('change', (e) => {
                const itemId = input.getAttribute('data-quantity-item-id');
                this.updateItemQuantity(itemId, input.value, true);
            });
            input.addEventListener('blur', (e) => {
                const itemId = input.getAttribute('data-quantity-item-id');
                this.updateItemQuantity(itemId, input.value, true);
            });
        });
        
        // Add event listeners for price inputs
        const priceInputs = document.querySelectorAll('.price-input');
        priceInputs.forEach(input => {
            // Real-time update during typing (no re-render)
            input.addEventListener('input', (e) => {
                const itemId = input.getAttribute('data-price-item-id');
                this.updateItemPrice(itemId, input.value, false);
            });
            // Full update when user finishes editing
            input.addEventListener('change', (e) => {
                const itemId = input.getAttribute('data-price-item-id');
                this.updateItemPrice(itemId, input.value, true);
            });
            input.addEventListener('blur', (e) => {
                const itemId = input.getAttribute('data-price-item-id');
                this.updateItemPrice(itemId, input.value, true);
            });
        });
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
        
        // Collect unique pr_ids from orderItems source_prs arrays
        const linkedPRIds = new Set();
        this.orderItems.forEach(item => {
            if (item.source_prs && Array.isArray(item.source_prs)) {
                item.source_prs.forEach(pr => {
                    if (pr.pr_id) {
                        linkedPRIds.add(pr.pr_id);
                    }
                });
            }
        });
        
        // Add linked_pr_ids to form data
        linkedPRIds.forEach(prId => {
            formData.append('linked_pr_ids[]', prId);
        });
        
        console.log('Collected PR IDs for linking:', Array.from(linkedPRIds));
        
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

/* Fix layout overflow for all flex containers */
.flex {
    min-height: 0;
}

.flex-1 {
    min-height: 0;
}

/* Ensure proper scrolling in nested flex containers */
.flex.flex-col {
    min-height: 0;
}

.flex-1.flex.flex-col {
    min-height: 0;
}
</style>
@endsection