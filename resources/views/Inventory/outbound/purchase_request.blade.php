@extends('Inventory.layout.app')

@section('content')
<style>
    /* Custom Scrollbar for Modal */
    .modal-scroll::-webkit-scrollbar { width: 8px; }
    .modal-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .modal-scroll::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    
    /* Animation Classes */
    .modal-backdrop { transition: opacity 0.3s ease-out; opacity: 0; pointer-events: none; }
    .modal-backdrop.active { opacity: 1; pointer-events: auto; }
    .modal-panel { transition: all 0.3s ease-out; transform: scale(0.95) translateY(10px); opacity: 0; }
    .modal-panel.active { transform: scale(1) translateY(0); opacity: 1; }
    
    /* Z-Index Hierarchies */
    .z-60 { z-index: 60; }
    .z-70 { z-index: 70; }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Purchase Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your procurement requests and track their status.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="PRManager.createPR()" 
                    class="px-4 py-2.5 bg-chocolate text-white rounded-xl hover:bg-chocolate-dark transition-all shadow-sm font-medium flex items-center gap-2">
                <i class="fas fa-plus"></i>
                New Request
            </button>
        </div>
    </div>

    {{-- 2. METRICS & FILTERS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Metric Cards -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Pending</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $pendingCount ?? 0 }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ $approvedCount ?? 0 }}</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Search/Filter -->
        <div class="md:col-span-2 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="search" placeholder="Search PR Number..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-chocolate focus:border-transparent outline-none">
            </div>
            <select id="statusFilter" class="py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-chocolate outline-none">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    {{-- 3. DATA TABLE --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PR Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchaseRequests as $pr)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">{{ $pr->pr_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($pr->request_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $pr->department ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $prioColors = [
                                        'low' => 'bg-gray-100 text-gray-800',
                                        'normal' => 'bg-blue-100 text-blue-800',
                                        'high' => 'bg-orange-100 text-orange-800',
                                        'urgent' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $prioColors[$pr->priority] ?? 'bg-gray-100' }} uppercase">
                                    {{ $pr->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₱ {{ number_format($pr->total_estimated_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'draft' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $icon = match($pr->status) {
                                        'approved' => 'fa-check-circle',
                                        'rejected' => 'fa-times-circle',
                                        default => 'fa-clock'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$pr->status] ?? 'bg-gray-100' }}">
                                    <i class="fas {{ $icon }} mr-1.5"></i> {{ ucfirst($pr->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="PRManager.viewDetails({{ $pr->id }})" class="text-blue-600 hover:text-blue-900 mr-3" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($pr->status === 'pending' || $pr->status === 'draft')
                                    <button onclick="PRManager.cancelPR({{ $pr->id }})" class="text-red-600 hover:text-red-900" title="Cancel/Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p>No purchase requests found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $purchaseRequests->links() }}
            </div>
        @endif
    </div>

</div>

{{-- CREATE MODAL --}}
<div id="createModalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto modal-backdrop">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="PRManager.closeCreateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div id="createModalPanel" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full modal-panel">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-gray-900">New Purchase Request</h3>
                        <p class="text-sm text-gray-500 mt-1">Fill in the details for your procurement needs.</p>
                    </div>
                    <button onclick="PRManager.closeCreateModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="createPRForm">
                    @csrf
                    
                    {{-- Header Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                            <input type="text" name="department" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Production">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="normal">Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Date</label>
                            <input type="date" name="request_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Justification</label>
                            <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Why is this purchase needed?"></textarea>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-sm font-bold text-gray-700 uppercase">Items List</h4>
                            <button type="button" onclick="PRManager.addItemRow()" class="text-sm text-chocolate hover:text-chocolate-dark font-medium flex items-center gap-1">
                                <i class="fas fa-plus-circle"></i> Add Item
                            </button>
                        </div>
                        
                        <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-5/12">Item</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-2/12">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-2/12">Est. Price</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Total</th>
                                    <th class="px-3 py-2 w-1/12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white" id="itemsBody">
                                {{-- Dynamic Rows Go Here --}}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-right font-bold text-gray-700">Grand Total Estimate:</td>
                                    <td class="px-3 py-4 text-right font-bold text-chocolate text-lg" id="grandTotal">₱ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <p id="itemsError" class="text-red-500 text-xs mt-2 hidden">Please add at least one item.</p>
                    </div>
                </form>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" onclick="PRManager.savePR()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:w-auto sm:text-sm transition-colors">
                    Submit Request
                </button>
                <button type="button" onclick="PRManager.closeCreateModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto modal-backdrop">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="PRManager.closeDetails()"></div>
        <div id="detailsModalPanel" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-panel">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Request Details</h3>
                    <button onclick="PRManager.closeDetails()" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times text-xl"></i></button>
                </div>
                <div id="detailsContent" class="modal-scroll overflow-y-auto max-h-[60vh]"></div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                <button onclick="PRManager.closeDetails()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium shadow-sm">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- NOTIFICATION --}}
<div id="notificationModalBackdrop" class="hidden fixed inset-0 z-70 flex items-center justify-center px-4 sm:px-6 modal-backdrop">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div id="notificationModalPanel" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col modal-panel overflow-hidden">
        <div class="p-6 text-center">
            <div id="notifIconBg" class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 mb-5">
                <i id="notifIcon" class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" id="notifTitle">Success</h3>
            <p class="text-sm text-gray-500" id="notifMessage"></p>
        </div>
        <div class="bg-gray-50 px-6 py-3">
            <button onclick="PRManager.closeNotification()" class="w-full rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-white hover:bg-gray-900 sm:text-sm">Okay</button>
        </div>
    </div>
</div>

<script>
const PRManager = {
    elements: {
        create: {
            backdrop: document.getElementById('createModalBackdrop'),
            panel: document.getElementById('createModalPanel'),
            form: document.getElementById('createPRForm'),
            itemsBody: document.getElementById('itemsBody'),
            grandTotal: document.getElementById('grandTotal'),
            itemsError: document.getElementById('itemsError')
        },
        details: {
            backdrop: document.getElementById('detailsModalBackdrop'),
            panel: document.getElementById('detailsModalPanel'),
            content: document.getElementById('detailsContent')
        },
        notification: {
            backdrop: document.getElementById('notificationModalBackdrop'),
            panel: document.getElementById('notificationModalPanel'),
            title: document.getElementById('notifTitle'),
            message: document.getElementById('notifMessage'),
            icon: document.getElementById('notifIcon'),
            iconBg: document.getElementById('notifIconBg')
        }
    },

    async getItems() {
        try {
            const response = await fetch('{{ route("inventory.purchase-requests.items") }}');
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching items:', error);
            return [];
        }
    },

    itemList: [],

    async init() {
        // Load items first
        this.itemList = await this.getItems();
        
        // Setup Search
        const search = document.getElementById('search');
        const filter = document.getElementById('statusFilter');
        if(search) {
            search.addEventListener('input', () => this.filterTable());
            filter.addEventListener('change', () => this.filterTable());
        }
    },

    filterTable() {
        const term = document.getElementById('search').value.toLowerCase();
        const status = document.getElementById('statusFilter').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.querySelector('.rounded-full')?.textContent.trim().toLowerCase() || '';
            
            const matchesSearch = text.includes(term);
            const matchesStatus = status === '' || rowStatus.includes(status);

            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    },

    // ================= CREATE LOGIC =================

    createPR() {
        this.elements.create.form.reset();
        this.elements.create.itemsBody.innerHTML = '';
        this.addItemRow(); // Start with one empty row
        this.calculateGrandTotal();
        
        this.elements.create.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            this.elements.create.backdrop.classList.add('active');
            this.elements.create.panel.classList.add('active');
        });
    },

    closeCreateModal() {
        this.elements.create.backdrop.classList.remove('active');
        this.elements.create.panel.classList.remove('active');
        setTimeout(() => this.elements.create.backdrop.classList.add('hidden'), 300);
    },

    addItemRow() {
        this.elements.create.itemsError.classList.add('hidden');
        const rowId = Date.now();
        const row = document.createElement('tr');
        row.id = `row-${rowId}`;
        
        let options = '<option value="">Select Item...</option>';
        this.itemList.forEach(item => {
            options += `<option value="${item.id}">${item.name} (${item.item_code || '-'})</option>`;
        });

        row.innerHTML = `
            <td class="px-3 py-2">
                <select name="items[${rowId}][item_id]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-sm">
                    ${options}
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" step="0.01" name="items[${rowId}][quantity]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-sm text-right qty-input" oninput="PRManager.calculateRowTotal(${rowId})">
            </td>
            <td class="px-3 py-2">
                <input type="number" step="0.01" name="items[${rowId}][unit_price]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-sm text-right price-input" oninput="PRManager.calculateRowTotal(${rowId})">
            </td>
            <td class="px-3 py-2 text-right font-medium text-gray-700">
                <span id="total-${rowId}">0.00</span>
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" onclick="PRManager.removeRow(${rowId})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
            </td>
        `;
        this.elements.create.itemsBody.appendChild(row);
    },

    removeRow(id) {
        const row = document.getElementById(`row-${id}`);
        if(row) row.remove();
        this.calculateGrandTotal();
    },

    calculateRowTotal(id) {
        const row = document.getElementById(`row-${id}`);
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = qty * price;
        
        document.getElementById(`total-${id}`).textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        this.calculateGrandTotal();
    },

    calculateGrandTotal() {
        let grandTotal = 0;
        const rows = this.elements.create.itemsBody.querySelectorAll('tr');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            grandTotal += (qty * price);
        });
        this.elements.create.grandTotal.textContent = '₱ ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    },

    savePR() {
        const form = this.elements.create.form;
        const formData = new FormData(form);
        
        // Basic Validation
        if(this.elements.create.itemsBody.children.length === 0) {
            this.elements.create.itemsError.classList.remove('hidden');
            return;
        }

        // Transform FormData to structured object for JSON
        const data = {
            department: formData.get('department'),
            priority: formData.get('priority'),
            request_date: formData.get('request_date'),
            notes: formData.get('notes'),
            items: []
        };

        // Extract Items
        const rows = this.elements.create.itemsBody.querySelectorAll('tr');
        let valid = true;
        rows.forEach(row => {
            const select = row.querySelector('select');
            const qty = row.querySelector('.qty-input');
            const price = row.querySelector('.price-input');
            
            if(!select.value || !qty.value || !price.value) valid = false;

            data.items.push({
                item_id: select.value,
                quantity_requested: qty.value,
                unit_price_estimate: price.value
            });
        });

        if(!valid) {
            this.showNotification('error', 'Missing Data', 'Please ensure all items have a product, quantity, and price.');
            return;
        }

        // API Call
        fetch('{{ route("inventory.purchase-requests.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                this.closeCreateModal();
                this.showNotification('success', 'Submitted', 'Purchase Request created successfully.', () => location.reload());
            } else {
                this.showNotification('error', 'Error', response.message || 'Failed to create request.');
            }
        })
        .catch(err => {
            console.error(err);
            this.showNotification('error', 'System Error', 'An error occurred.');
        });
    },

    // ================= VIEW DETAILS =================

    viewDetails(id) {
        const el = this.elements.details;
        el.content.innerHTML = '<div class="flex justify-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        
        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active');
            el.panel.classList.add('active');
        });

        fetch(`/inventory/purchase-requests/${id}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.renderDetails(data.data);
                } else {
                    el.content.innerHTML = '<p class="text-center text-red-500">Failed to load details.</p>';
                }
            });
    },

    renderDetails(data) {
        const itemsHtml = data.items.map(item => `
            <tr class="border-b border-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">${item.item_name}</td>
                <td class="px-4 py-3 text-sm text-right">${item.quantity_requested}</td>
                <td class="px-4 py-3 text-sm text-right">₱ ${item.unit_price_estimate}</td>
                <td class="px-4 py-3 text-sm text-right font-medium">₱ ${item.total_estimated_cost}</td>
            </tr>
        `).join('');

        this.elements.details.content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-bold text-gray-500">PR Number:</span> <span class="text-gray-900">${data.pr_number}</span></div>
                    <div><span class="font-bold text-gray-500">Status:</span> <span class="uppercase font-bold text-chocolate">${data.status}</span></div>
                    <div><span class="font-bold text-gray-500">Department:</span> ${data.department}</div>
                    <div><span class="font-bold text-gray-500">Priority:</span> <span class="uppercase">${data.priority}</span></div>
                    <div class="col-span-2"><span class="font-bold text-gray-500">Notes:</span> <span class="italic text-gray-600">${data.notes || 'None'}</span></div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-2">Items</h4>
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right font-bold">Total:</td>
                                <td class="px-4 py-3 text-right font-bold text-chocolate">${data.formatted_total}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                ${data.approved_by ? `<div class="text-xs text-gray-400 text-right mt-2">Approved by ${data.approver_name} on ${data.approved_at}</div>` : ''}
            </div>
        `;
    },

    closeDetails() {
        this.elements.details.backdrop.classList.remove('active');
        this.elements.details.panel.classList.remove('active');
        setTimeout(() => this.elements.details.backdrop.classList.add('hidden'), 300);
    },

    // ================= NOTIFICATION UTILS =================

    showNotification(type, title, message, callback = null) {
        const el = this.elements.notification;
        el.title.textContent = title;
        el.message.textContent = message;
        
        // Style
        el.iconBg.className = `mx-auto flex items-center justify-center h-14 w-14 rounded-full mb-5 ${type === 'success' ? 'bg-green-100' : 'bg-red-100'}`;
        el.icon.className = `fas text-2xl ${type === 'success' ? 'fa-check text-green-600' : 'fa-times text-red-600'}`;

        el.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            el.backdrop.classList.add('active'); // Use active class for opacity transition if defined
        });

        // Auto close after 2s if success
        if(type === 'success') setTimeout(() => { this.closeNotification(); if(callback) callback(); }, 2000);
    },

    closeNotification() {
        this.elements.notification.backdrop.classList.add('hidden');
    },

    cancelPR(id) {
        if(!confirm('Are you sure you want to cancel this request?')) return;
        
        fetch(`/inventory/purchase-requests/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                this.showNotification('success', 'Cancelled', 'Request cancelled.', () => location.reload());
            } else {
                this.showNotification('error', 'Error', 'Could not cancel request.');
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    await PRManager.init();
});
</script>
@endsection