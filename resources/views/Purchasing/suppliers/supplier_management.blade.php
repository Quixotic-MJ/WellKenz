@extends('Purchasing.layout.app')

@section('content')
<div class="h-[calc(100vh-6rem)] flex flex-col font-sans text-gray-600">
    
    {{-- HEADER (Compact) --}}
    <div class="flex-none pb-4 border-b border-border-soft mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-chocolate">Supplier Management</h1>
                <div class="flex items-center gap-3 mt-1 text-xs font-medium">
                    <span class="flex items-center text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-100">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        {{ $stats['active_suppliers'] }} Active
                    </span>
                    <span class="text-gray-400">|</span>
                    <span class="text-amber-600">
                        <i class="fas fa-star text-amber-400 mr-1"></i> {{ number_format($stats['avg_rating'], 1) }} Avg Rating
                    </span>
                </div>
            </div>
            <button onclick="openAddSupplierModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i> New Supplier
            </button>
        </div>
    </div>

    {{-- MAIN CONTENT GRID --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-0">
        
        {{-- LEFT COLUMN: SUPPLIER LIST (Scrollable) --}}
        <div class="lg:col-span-4 xl:col-span-3 flex flex-col bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden h-full">
            {{-- Sticky Search Header --}}
            <div class="p-3 border-b border-border-soft bg-white z-10">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                    <input type="text" id="supplierSearch" onkeyup="filterSuppliers()" 
                        class="block w-full pl-9 pr-3 py-2 border border-gray-200 bg-cream-bg/50 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                        placeholder="Search name or code...">
                </div>
            </div>
            
            {{-- Scrollable List --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar" id="supplierList">
                @forelse($suppliers as $supplier)
                    <div class="supplier-item group p-3 border-b border-gray-50 cursor-pointer hover:bg-blue-50/50 transition-all border-l-4 border-l-transparent hover:border-l-blue-300" 
                         data-supplier-id="{{ $supplier->id }}" 
                         onclick="selectSupplier({{ $supplier->id }})">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 group-hover:from-chocolate group-hover:to-caramel group-hover:text-white rounded-lg flex items-center justify-center font-bold text-xs transition-all duration-300 shadow-sm">
                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-sm font-bold text-gray-800 truncate group-hover:text-chocolate">{{ $supplier->name }}</h3>
                                    @if($supplier->rating >= 4)
                                        <i class="fas fa-star text-[10px] text-amber-400 mt-0.5"></i>
                                    @endif
                                </div>
                                <div class="flex justify-between items-end mt-0.5">
                                    <span class="text-xs text-gray-400 font-mono">{{ $supplier->supplier_code }}</span>
                                    <span class="text-[10px] px-1.5 rounded {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="text-gray-400 text-sm">No suppliers found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILS & ITEMS (Scrollable) --}}
        <div class="lg:col-span-8 xl:col-span-9 flex flex-col bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden h-full relative">
            
            {{-- Default Empty State --}}
            <div id="emptyState" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50 z-20">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-200 mb-4">
                    <i class="fas fa-store-alt text-chocolate/20 text-4xl"></i>
                </div>
                <h3 class="font-display text-xl font-bold text-chocolate">Select a Supplier</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-xs text-center">Click on a supplier from the list to view their profile, manage items, and update pricing.</p>
            </div>

            {{-- Loading State --}}
            <div id="loadingState" class="absolute inset-0 flex items-center justify-center bg-white z-30 hidden">
                <i class="fas fa-circle-notch fa-spin text-chocolate text-3xl"></i>
            </div>

            {{-- Content Container --}}
            <div id="supplierContent" class="hidden flex flex-col h-full">
                {{-- Content injected by JS --}}
            </div>
        </div>
    </div>
</div>

{{-- MODALS (Add Supplier & Add Items) --}}
{{-- (Keep your existing modal HTML structures here, they are fine) --}}
@include('Purchasing.partials.modals') 

@push('styles')
<style>
/* Refined Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

/* Selection States */
.supplier-item.selected { 
    background-color: #eff6ff !important; /* blue-50 */
    border-left-color: #3b82f6 !important; /* blue-500 */
}
.supplier-item.selected .bg-gradient-to-br {
    --tw-gradient-from: #8B4513; --tw-gradient-to: #D2691E; /* chocolate to caramel */
    color: white;
}

/* Animations */
.animate-fade-in { animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Toast Notifications */
#toastContainer .toast {
    animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-in 4.7s forwards;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Enhanced Modal Styles */
[id$="Modal"] {
    backdrop-filter: blur(4px);
    transition: all 0.3s ease-in-out;
}

[id$="Modal"] > div {
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-20px) scale(0.95);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

/* Improved Form Styles */
[id$="Form"] input:focus,
[id$="Form"] select:focus,
[id$="Form"] textarea:focus {
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

/* Enhanced Table Styles */
#supplierItemsList table {
    font-size: 0.875rem;
}

#supplierItemsList tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Better Mobile Responsiveness */
@media (max-width: 768px) {
    #supplierItemsList .grid {
        grid-template-columns: 1fr;
    }
    
    [id$="Modal"] > div {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
    }
    
    .grid.grid-cols-1.md\\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .grid.grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #8B4513;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
// Global variables
let currentSupplier = null;
let currentTab = 'info';
let selectedItems = [];
let pendingGlobalAction = null;

// Utility Functions
function formatNumber(num) {
    if (num === null || num === undefined || isNaN(num)) return '0';
    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(num);
}

function generateStars(rating) {
    if (!rating || rating < 1) return '<span class="text-gray-300">No rating</span>';
    
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star text-amber-400 text-xs"></i>';
        } else {
            stars += '<i class="far fa-star text-amber-300 text-xs"></i>';
        }
    }
    return stars;
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const typeClasses = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    const iconClasses = {
        success: 'fas fa-check-circle text-green-500',
        error: 'fas fa-exclamation-circle text-red-500',
        warning: 'fas fa-exclamation-triangle text-yellow-500',
        info: 'fas fa-info-circle text-blue-500'
    };
    
    toast.className = `${typeClasses[type]} border rounded-lg p-4 shadow-lg animate-fade-in flex items-center`;
    toast.innerHTML = `
        <i class="${iconClasses[type]} mr-3"></i>
        <span class="text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Supplier Selection and Loading
function selectSupplier(supplierId) {
    // Validate supplierId - ensure it's a valid number
    if (!supplierId || isNaN(supplierId) || supplierId <= 0) {
        console.error('Invalid supplier ID:', supplierId);
        showToast('Invalid supplier selected', 'error');
        return;
    }
    
    // Convert to integer to ensure clean ID
    const cleanSupplierId = parseInt(supplierId, 10);
    
    // Update visual selection
    document.querySelectorAll('.supplier-item').forEach(item => {
        item.classList.remove('selected');
    });
    const supplierElement = document.querySelector(`[data-supplier-id="${cleanSupplierId}"]`);
    if (supplierElement) {
        supplierElement.classList.add('selected');
    } else {
        console.error('Supplier element not found for ID:', cleanSupplierId);
        showToast('Supplier not found', 'error');
        return;
    }
    
    // Show loading state
    const loadingState = document.getElementById('loadingState');
    loadingState.classList.remove('hidden');
    
    // Load supplier details
    loadSupplierDetails(cleanSupplierId);
}

function loadSupplierDetails(supplierId) {
    // Validate supplierId
    if (!supplierId || isNaN(supplierId) || supplierId <= 0) {
        console.error('Invalid supplier ID for API call:', supplierId);
        showToast('Invalid supplier ID', 'error');
        document.getElementById('loadingState').classList.add('hidden');
        return;
    }
    
    const cleanSupplierId = parseInt(supplierId, 10);
    
    fetch(`/purchasing/api/suppliers/${cleanSupplierId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            currentSupplier = data;
            renderSupplierDetails(data);
        })
        .catch(error => {
            console.error('Error loading supplier details:', error);
            showToast('Error loading supplier details. Please try again.', 'error');
            document.getElementById('loadingState').classList.add('hidden');
        });
}

function loadSupplierItems(supplierId) {
    // Validate supplierId
    if (!supplierId || isNaN(supplierId) || supplierId <= 0) {
        console.error('Invalid supplier ID for items:', supplierId);
        showToast('Invalid supplier ID', 'error');
        return;
    }
    
    const cleanSupplierId = parseInt(supplierId, 10);
    
    fetch(`/purchasing/suppliers/${cleanSupplierId}/items`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            renderSupplierItems(data);
        })
        .catch(error => {
            console.error('Error loading supplier items:', error);
            showToast('Error loading supplier items', 'error');
        });
}

function renderSupplierDetails(supplierData) {
    const container = document.getElementById('supplierContent');
    const emptyState = document.getElementById('emptyState');
    const loadingState = document.getElementById('loadingState');
    
    emptyState.classList.add('hidden');
    loadingState.classList.add('hidden');
    container.classList.remove('hidden');
    
    // Header HTML
    const headerHtml = `
        <div class="flex-none border-b border-border-soft bg-white px-6 py-5">
            <div class="flex justify-between items-start">
                <div class="flex items-center">
                    <div class="h-14 w-14 rounded-lg bg-chocolate text-white flex items-center justify-center text-2xl font-bold shadow-md mr-4">
                        ${supplierData.name.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 leading-tight">${supplierData.name}</h2>
                        <div class="flex items-center text-sm text-gray-500 mt-1 space-x-3">
                            <span class="font-mono bg-gray-100 px-1.5 rounded text-xs">${supplierData.supplier_code}</span>
                            <span>•</span>
                            <span class="${supplierData.is_active ? 'text-green-600' : 'text-red-600'} flex items-center">
                                <i class="fas fa-circle text-[8px] mr-1.5"></i> ${supplierData.is_active ? 'Active Trading' : 'Inactive'}
                            </span>
                            <span>•</span>
                            <div class="flex text-amber-400 text-xs">
                                ${generateStars(supplierData.rating)}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="toggleSupplierStatus(${supplierData.id})" class="px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors">
                        ${supplierData.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    <button onclick="editSupplier(${supplierData.id})" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-gray-800 transition-colors shadow-sm">
                        Edit Profile
                    </button>
                </div>
            </div>
            
            <div class="flex space-x-6 mt-6 border-b border-gray-100">
                <button onclick="switchTab('info')" id="infoTab" class="pb-3 text-sm font-bold border-b-2 border-chocolate text-chocolate transition-colors">
                    Company Profile
                </button>
                <button onclick="switchTab('items')" id="itemsTab" class="pb-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:text-gray-800 transition-colors">
                    Items & Pricelist <span class="ml-1 bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full text-[10px]" id="itemCountBadge">-</span>
                </button>
            </div>
        </div>
    `;

    // Body HTML
    const bodyHtml = `
        <div class="flex-1 overflow-y-auto bg-gray-50/50 custom-scrollbar p-6">
            <div id="infoContent" class="animate-fade-in max-w-4xl">
                ${renderSupplierInfo(supplierData)}
            </div>
            <div id="itemsContent" class="hidden animate-fade-in h-full flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <div class="relative w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="itemTableSearch" onkeyup="filterItemTable()" placeholder="Filter items..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-md focus:border-chocolate focus:ring-1 focus:ring-chocolate">
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openAddItemsModal()" class="inline-flex items-center px-3 py-1.5 bg-chocolate text-white text-xs font-bold rounded shadow-sm hover:bg-chocolate-dark transition-all">
                            <i class="fas fa-plus mr-1.5"></i> Add Items
                        </button>
                    </div>
                </div>
                <div id="supplierItemsList" class="flex-1 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
                    </div>
            </div>
        </div>
    `;

    container.innerHTML = headerHtml + bodyHtml;
    
    // Load items immediately
    loadSupplierItems(supplierData.id);
}

function renderSupplierInfo(supplierData) {
    return `
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Company Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-2">Contact Details</h4>
                    <div class="space-y-2 text-sm">
                        ${supplierData.contact_person ? `<div><span class="font-medium">Contact:</span> ${supplierData.contact_person}</div>` : ''}
                        ${supplierData.phone ? `<div><span class="font-medium">Phone:</span> ${supplierData.phone}</div>` : ''}
                        ${supplierData.mobile ? `<div><span class="font-medium">Mobile:</span> ${supplierData.mobile}</div>` : ''}
                        ${supplierData.email ? `<div><span class="font-medium">Email:</span> ${supplierData.email}</div>` : ''}
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-2">Business Details</h4>
                    <div class="space-y-2 text-sm">
                        ${supplierData.payment_terms ? `<div><span class="font-medium">Payment Terms:</span> ${supplierData.payment_terms} days</div>` : ''}
                        ${supplierData.credit_limit ? `<div><span class="font-medium">Credit Limit:</span> ₱${formatNumber(supplierData.credit_limit)}</div>` : ''}
                        ${supplierData.tax_id ? `<div><span class="font-medium">Tax ID:</span> ${supplierData.tax_id}</div>` : ''}
                        ${supplierData.rating ? `<div><span class="font-medium">Rating:</span> ${generateStars(supplierData.rating)}</div>` : ''}
                    </div>
                </div>
            </div>
            ${supplierData.address ? `
                <div class="mt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">Address</h4>
                    <p class="text-sm text-gray-600">${supplierData.address}</p>
                    ${supplierData.city || supplierData.province ? `
                        <p class="text-sm text-gray-600 mt-1">
                            ${supplierData.city ? supplierData.city : ''}${supplierData.city && supplierData.province ? ', ' : ''}${supplierData.province || ''} ${supplierData.postal_code || ''}
                        </p>
                    ` : ''}
                </div>
            ` : ''}
            ${supplierData.notes ? `
                <div class="mt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-2">Notes</h4>
                    <p class="text-sm text-gray-600">${supplierData.notes}</p>
                </div>
            ` : ''}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-bold text-gray-700 mb-2">Record Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500">
                    <div><span class="font-medium">Created:</span> ${new Date(supplierData.created_at).toLocaleDateString()}</div>
                    <div><span class="font-medium">Updated:</span> ${new Date(supplierData.updated_at).toLocaleDateString()}</div>
                </div>
            </div>
        </div>
    `;
}

// Tab Management
function switchTab(tabName) {
    currentTab = tabName;
    
    // Update tab buttons
    document.querySelectorAll('[id$="Tab"]').forEach(tab => {
        tab.classList.remove('border-chocolate', 'text-chocolate');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById(tabName + 'Tab').classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(tabName + 'Tab').classList.add('border-chocolate', 'text-chocolate');
    
    // Update content
    document.getElementById('infoContent').classList.toggle('hidden', tabName !== 'info');
    document.getElementById('itemsContent').classList.toggle('hidden', tabName !== 'items');
}

// Item Management Functions
function renderSupplierItems(items) {
    const container = document.getElementById('supplierItemsList');
    document.getElementById('itemCountBadge').textContent = items.length;

    if (items.length === 0) {
        container.innerHTML = `<div class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
            <p class="text-sm">No items assigned yet.</p>
        </div>`;
        return;
    }

    const tableHtml = `
        <div class="overflow-auto custom-scrollbar flex-1">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10"></th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Details</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Pricing</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Logistics</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    ${items.map(item => {
                        // Pricing Logic
                        const priceChange = calculatePriceChange(item.unit_price, item.last_purchase_price);
                        const priceDiffHtml = getPriceDiffHtml(priceChange);
                        
                        return `
                        <tr class="hover:bg-blue-50/30 transition-colors group" data-item-id="${item.id}">
                            <td class="px-4 py-3 text-center">
                                <button onclick="togglePreferred(${item.id}, ${!item.is_preferred})" 
                                        class="transition-transform hover:scale-110 focus:outline-none" 
                                        title="${item.is_preferred ? 'Preferred Supplier' : 'Set as Preferred'}">
                                    <i class="fas fa-star ${item.is_preferred ? 'text-amber-400 text-sm' : 'text-gray-200 text-xs hover:text-amber-300'}"></i>
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">${item.item_name}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-500 font-mono bg-gray-100 px-1 rounded">${item.item_code}</span>
                                            ${item.category_name ? `<span class="text-[10px] text-gray-400">${item.category_name}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-col items-end">
                                    <div class="text-sm font-bold text-chocolate cursor-pointer border-b border-dashed border-transparent hover:border-chocolate transition-colors" 
                                         onclick="editSupplierItem(${item.id})" title="Click to edit price">
                                        ₱${formatNumber(item.unit_price)}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        per ${item.unit_symbol || 'unit'}
                                    </div>
                                    ${priceDiffHtml}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600">
                                    <div class="flex items-center gap-1 mb-1">
                                        <i class="fas fa-truck text-gray-400 w-4"></i> 
                                        <span class="font-medium">${item.lead_time_days} days</span> lead
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-boxes text-gray-400 w-4"></i> 
                                        <span>Min: ${formatNumber(item.minimum_order_quantity)}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex justify-center gap-2">
                                    <button onclick="editSupplierItem(${item.id})" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-md" title="Edit Item">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button onclick="removeSupplierItem(${item.id})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-md" title="Remove">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `}).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = tableHtml;
}

// Helper for Pricing Intelligence
function calculatePriceChange(current, last) {
    if (!last || last == 0) return null;
    if (current == last) return 0;
    return ((current - last) / last) * 100;
}

function getPriceDiffHtml(percent) {
    if (percent === null) return '';
    if (percent === 0) return '<span class="text-[10px] text-gray-400">No change</span>';
    
    const isUp = percent > 0;
    const color = isUp ? 'text-red-500' : 'text-green-500';
    const icon = isUp ? 'fa-arrow-up' : 'fa-arrow-down';
    
    return `<div class="${color} text-[10px] font-medium flex items-center gap-1">
        <i class="fas ${icon} text-[8px]"></i> ${Math.abs(percent).toFixed(1)}% vs last
    </div>`;
}

// Search and Filter Functions
function filterSuppliers() {
    const searchTerm = document.getElementById('supplierSearch').value.toLowerCase();
    const supplierItems = document.querySelectorAll('.supplier-item');
    
    supplierItems.forEach(item => {
        const name = item.querySelector('h3').textContent.toLowerCase();
        const code = item.querySelector('.font-mono').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || code.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterItemTable() {
    const searchTerm = document.getElementById('itemTableSearch').value.toLowerCase();
    const itemRows = document.querySelectorAll('#supplierItemsList tbody tr');
    
    itemRows.forEach(row => {
        const itemName = row.querySelector('.text-sm.font-bold').textContent.toLowerCase();
        const itemCode = row.querySelector('.text-xs.text-gray-500').textContent.toLowerCase();
        
        if (itemName.includes(searchTerm) || itemCode.includes(searchTerm)) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}

// Modal Functions
function openAddSupplierModal() {
    document.getElementById('addSupplierModal').style.display = 'block';
}

function closeAddSupplierModal() {
    document.getElementById('addSupplierModal').style.display = 'none';
    document.getElementById('addSupplierForm').reset();
}

function openEditSupplierModal(supplierId) {
    // Populate form with supplier data
    if (currentSupplier && currentSupplier.id === supplierId) {
        document.getElementById('edit_supplier_id').value = currentSupplier.id;
        document.getElementById('edit_name').value = currentSupplier.name || '';
        document.getElementById('edit_supplier_code').value = currentSupplier.supplier_code || '';
        document.getElementById('edit_contact_person').value = currentSupplier.contact_person || '';
        document.getElementById('edit_email').value = currentSupplier.email || '';
        document.getElementById('edit_phone').value = currentSupplier.phone || '';
        document.getElementById('edit_mobile').value = currentSupplier.mobile || '';
        document.getElementById('edit_payment_terms').value = currentSupplier.payment_terms || '';
        document.getElementById('edit_credit_limit').value = currentSupplier.credit_limit || '';
        document.getElementById('edit_rating').value = currentSupplier.rating || '';
        document.getElementById('edit_is_active').checked = currentSupplier.is_active;
        document.getElementById('edit_address').value = currentSupplier.address || '';
        document.getElementById('edit_city').value = currentSupplier.city || '';
        document.getElementById('edit_province').value = currentSupplier.province || '';
        document.getElementById('edit_postal_code').value = currentSupplier.postal_code || '';
        document.getElementById('edit_tax_id').value = currentSupplier.tax_id || '';
        document.getElementById('edit_notes').value = currentSupplier.notes || '';
        
        document.getElementById('editSupplierModal').style.display = 'block';
    }
}

function closeEditSupplierModal() {
    document.getElementById('editSupplierModal').style.display = 'none';
}

function openAddItemsModal() {
    if (!currentSupplier) {
        showToast('Please select a supplier first', 'warning');
        return;
    }
    
    document.getElementById('items_supplier_id').value = currentSupplier.id;
    loadAvailableItems();
    document.getElementById('addItemsModal').style.display = 'block';
}

function closeAddItemsModal() {
    document.getElementById('addItemsModal').style.display = 'none';
    selectedItems = [];
    document.getElementById('selectedCount').textContent = '0';
    document.getElementById('addItemsSubmitBtn').disabled = true;
}

function openEditSupplierItemModal(supplierItemId) {
    // Find the item data from the current supplier items
    fetch(`/purchasing/supplier-items/${supplierItemId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_supplier_item_id').value = data.id;
            document.getElementById('edit_unit_price').value = data.unit_price;
            document.getElementById('edit_minimum_order_quantity').value = data.minimum_order_quantity;
            document.getElementById('edit_lead_time_days').value = data.lead_time_days;
            document.getElementById('edit_is_preferred').checked = data.is_preferred;
            document.getElementById('editSupplierItemModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading supplier item:', error);
            showToast('Error loading item data', 'error');
        });
}

function closeEditSupplierItemModal() {
    document.getElementById('editSupplierItemModal').style.display = 'none';
}

function openConfirmRemoveItemModal(itemName) {
    document.getElementById('removeItemMessage').textContent = `Are you sure you want to remove "${itemName}" from this supplier?`;
    document.getElementById('confirmRemoveItemModal').style.display = 'block';
}

function closeConfirmRemoveItemModal() {
    document.getElementById('confirmRemoveItemModal').style.display = 'none';
}

// Global Confirm Modal
function showGlobalConfirmModal(title, message, action) {
    document.getElementById('globalConfirmTitle').textContent = title;
    document.getElementById('globalConfirmMessage').textContent = message;
    pendingGlobalAction = action;
    document.getElementById('globalConfirmModal').style.display = 'block';
}

function closeGlobalConfirmModal() {
    document.getElementById('globalConfirmModal').style.display = 'none';
    pendingGlobalAction = null;
}

function confirmGlobalAction() {
    if (pendingGlobalAction) {
        pendingGlobalAction();
        closeGlobalConfirmModal();
    }
}

// Form Submission Functions
function submitAddSupplier(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    fetch('/purchasing/suppliers', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier created successfully!', 'success');
            closeAddSupplierModal();
            // Refresh the page or reload suppliers list
            location.reload();
        } else {
            showToast('Error creating supplier: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error creating supplier', 'error');
    });
}

function submitEditSupplier(event) {
    event.preventDefault();
    
    const supplierId = document.getElementById('edit_supplier_id').value;
    const formData = new FormData(event.target);
    
    fetch(`/purchasing/suppliers/${supplierId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Supplier updated successfully!', 'success');
            closeEditSupplierModal();
            // Refresh supplier details
            loadSupplierDetails(supplierId);
        } else {
            showToast('Error updating supplier: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating supplier', 'error');
    });
}

function submitEditSupplierItem(event) {
    event.preventDefault();
    
    const supplierItemId = document.getElementById('edit_supplier_item_id').value;
    const formData = new FormData(event.target);
    
    fetch(`/purchasing/supplier-items/${supplierItemId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PATCH'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Item updated successfully!', 'success');
            closeEditSupplierItemModal();
            // Reload supplier items
            loadSupplierItems(currentSupplier.id);
        } else {
            showToast('Error updating item: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating item', 'error');
    });
}

function submitAddItems(event) {
    event.preventDefault();
    
    if (selectedItems.length === 0) {
        showToast('Please select at least one item', 'warning');
        return;
    }
    
    const supplierId = document.getElementById('items_supplier_id').value;
    
    // 1. Prepare simple data (just IDs)
    const itemsData = selectedItems.map(itemId => ({
        item_id: itemId
        // We don't send price/moq/lead_time here; the Controller will use the defaults we set (0, 1, 1)
    }));
    
    // 2. Button Loading State
    const btn = document.getElementById('addItemsSubmitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Adding...';
    
    // 3. Send as JSON (Fixes the 422 Error)
    fetch(`/purchasing/suppliers/${supplierId}/items`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', // <--- CRITICAL: Tells Laravel this is JSON data
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            items: itemsData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Items added successfully', 'success');
            closeAddItemsModal();
            // Refresh the page or the list
            window.location.reload(); 
        } else {
            // Handle specific validation errors from Laravel
            if (data.errors && data.errors.items) {
                showToast(data.errors.items[0], 'error');
            } else {
                showToast(data.message || 'Error adding items', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Action Functions
function editSupplier(supplierId) {
    openEditSupplierModal(supplierId);
}

function toggleSupplierStatus(supplierId) {
    const supplier = currentSupplier;
    const action = supplier.is_active ? 'deactivate' : 'activate';
    
    showGlobalConfirmModal(
        `${action.charAt(0).toUpperCase() + action.slice(1)} Supplier`,
        `Are you sure you want to ${action} ${supplier.name}?`,
        () => {
            fetch(`/purchasing/suppliers/${supplierId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Supplier ${action}d successfully!`, 'success');
                    loadSupplierDetails(supplierId);
                } else {
                    showToast('Error updating supplier status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating supplier status', 'error');
            });
        }
    );
}

function togglePreferred(supplierItemId, isPreferred) {
    const formData = new FormData();
    formData.append('is_preferred', isPreferred ? '1' : '0');
    
    fetch(`/purchasing/supplier-items/${supplierItemId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PATCH'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(isPreferred ? 'Marked as preferred supplier' : 'Removed from preferred suppliers', 'success');
            loadSupplierItems(currentSupplier.id);
        } else {
            showToast('Error updating preferred status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating preferred status', 'error');
    });
}

function editSupplierItem(supplierItemId) {
    openEditSupplierItemModal(supplierItemId);
}

function removeSupplierItem(supplierItemId) {
    // Get item name for confirmation
    const row = document.querySelector(`[data-item-id="${supplierItemId}"]`);
    const itemName = row ? row.querySelector('.text-sm.font-bold').textContent : 'this item';
    
    showGlobalConfirmModal(
        'Remove Item',
        `Are you sure you want to remove "${itemName}" from this supplier?`,
        () => {
            fetch(`/purchasing/supplier-items/${supplierItemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Item removed successfully!', 'success');
                    loadSupplierItems(currentSupplier.id);
                } else {
                    showToast('Error removing item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error removing item', 'error');
            });
        }
    );
}

// Load Available Items for Add Items Modal
function loadAvailableItems() {
    const supplierId = currentSupplier.id;
    
    fetch(`/purchasing/suppliers/${supplierId}/available-items`)
        .then(response => response.json())
        .then(items => {
            const container = document.getElementById('availableItemsList');
            container.innerHTML = items.map(item => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="available_items[]" value="${item.id}" 
                               onchange="toggleAvailableItem(${item.id})" 
                               class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-bold text-gray-900">${item.name}</div>
                        <div class="text-xs text-gray-500 font-mono">${item.item_code}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">${item.category_name || 'N/A'}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${item.unit_name || 'Unit'}</td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading available items:', error);
            showToast('Error loading available items', 'error');
        });
}

function toggleAvailableItem(itemId) {
    const checkbox = document.querySelector(`input[value="${itemId}"]`);
    const isChecked = checkbox.checked;
    
    if (isChecked) {
        if (!selectedItems.includes(itemId)) {
            selectedItems.push(itemId);
        }
    } else {
        selectedItems = selectedItems.filter(id => id !== itemId);
    }
    
    // Update the counter
    document.getElementById('selectedCount').textContent = selectedItems.length;
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('addItemsSubmitBtn');
    submitBtn.disabled = selectedItems.length === 0;
}

function toggleSelectAllAvailableItems() {
    const selectAllCheckbox = document.getElementById('selectAllAvailableItems');
    const checkboxes = document.querySelectorAll('input[name="available_items[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        toggleAvailableItem(checkbox.value);
    });
}



function filterAvailableItems() {
    const searchTerm = document.getElementById('itemSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#availableItemsList tr');
    
    rows.forEach(row => {
        const name = row.querySelector('.text-sm.font-bold').textContent.toLowerCase();
        const code = row.querySelector('.text-xs.text-gray-500').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || code.includes(searchTerm)) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking outside
    const modals = document.querySelectorAll('[id$="Modal"]');
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            closeGlobalConfirmModal();
            closeConfirmRemoveItemModal();
        }
    });
    
    // Auto-select first supplier if available
    const firstSupplier = document.querySelector('.supplier-item');
    if (firstSupplier) {
        const supplierId = firstSupplier.getAttribute('data-supplier-id');
        if (supplierId && !isNaN(supplierId)) {
            selectSupplier(parseInt(supplierId, 10));
        }
    }
});
</script>
@endpush
@endsection