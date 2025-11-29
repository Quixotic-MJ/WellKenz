@extends('Purchasing.layout.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 font-sans text-gray-600 pb-24">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Open Purchase Orders</h1>
            <p class="text-sm text-gray-500">
                @if($openOrders->total() > 0)
                    <span class="font-bold text-caramel">{{ number_format($openOrders->total()) }}</span> active orders currently in processing.
                @else
                    Manage and track open purchase orders.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            
            {{-- Export Dropdown --}}
            <div class="relative group">
                <button id="exportDropdown" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-xl border border-border-soft z-50 overflow-hidden animate-fade-in-up">
                    <button onclick="exportData('pdf')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-cream-bg hover:text-chocolate transition-colors border-b border-gray-50">
                        <i class="fas fa-file-pdf mr-2 text-red-500"></i> PDF
                    </button>
                    <button onclick="exportData('excel')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-cream-bg hover:text-chocolate transition-colors border-b border-gray-50">
                        <i class="fas fa-file-excel mr-2 text-green-600"></i> Excel
                    </button>
                    <button onclick="exportData('csv')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-cream-bg hover:text-chocolate transition-colors">
                        <i class="fas fa-file-csv mr-2 text-blue-500"></i> CSV
                    </button>
                </div>
            </div>
            
            {{-- Create PO Button --}}
            <a href="{{ route('purchasing.po.create') }}" 
               class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> New PO
            </a>
        </div>
    </div>

    {{-- 2. ALERTS --}}
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

    {{-- 3. FILTERS & TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        {{-- Filters Toolbar --}}
        <div class="p-5 bg-white border-b border-border-soft">
            <form id="filterForm" method="GET" action="{{ route('purchasing.po.open') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                {{-- Search --}}
                <div class="md:col-span-4 relative group">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm transition-all placeholder-gray-400 focus:ring-2 focus:ring-caramel/20">
                </div>

                {{-- Supplier Filter --}}
                <div class="md:col-span-3 relative">
                    <select name="supplier_id" class="w-full px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all appearance-none">
                        <option value="">All Suppliers</option>
                        @foreach(\App\Models\Supplier::where('is_active', true)->orderBy('name')->limit(10)->get() as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none text-xs"></i>
                </div>

                {{-- Status Filter --}}
                <div class="md:col-span-3 relative">
                    <select name="status" class="w-full px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all appearance-none">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none text-xs"></i>
                </div>

                {{-- Action Buttons --}}
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 px-3 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-sm">
                        Apply
                    </button>
                    <button type="button" onclick="clearFilters()" class="flex-1 px-3 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all">
                        Clear
                    </button>
                </div>
            </form>
        </div>

        {{-- Bulk Actions Bar --}}
        @if($openOrders->count() > 0)
            <div class="bg-cream-bg px-6 py-3 border-b border-border-soft flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span class="font-bold text-chocolate bg-white border border-border-soft px-2 py-0.5 rounded-md shadow-sm" id="bulkSelectedCount">0</span>
                    <span>orders selected</span>
                    <span id="bulkInfo" class="text-xs text-caramel font-medium ml-2 italic"></span>
                </div>
                
                <div class="flex items-center gap-2">
                    @if(auth()->user()->hasRole(['purchasing', 'admin']))
                        <button onclick="bulkConfirm()" id="bulkConfirmBtn" disabled 
                                class="px-4 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-2">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                        <button onclick="bulkExport()" id="bulkExportBtn" disabled 
                                class="px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-2">
                            <i class="fas fa-download"></i> Export
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Orders Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-white">
                    <tr>
                        <th class="w-12 px-6 py-4 text-left">
                            <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">PO Number</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Items</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Order Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Delivery</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($openOrders as $order)
                        <tr class="po-row hover:bg-cream-bg/50 transition-colors group cursor-pointer" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}"
                            data-status="{{ $order->status }}">
                            
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer">
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-chocolate text-sm bg-chocolate/5 px-2 py-1 rounded border border-chocolate/10">
                                    #{{ $order->po_number }}
                                </span>
                                @if($order->is_overdue)
                                    <div class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-cream-bg flex items-center justify-center text-caramel font-bold text-xs border border-border-soft shadow-sm">
                                        {{ strtoupper(substr($order->supplier->name ?? 'N/A', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $order->supplier->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $order->supplier->supplier_code ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->total_items_count }} items</div>
                                <div class="text-xs text-gray-500">{{ number_format($order->total_quantity_ordered, 0) }} ordered</div>
                                <div class="text-xs text-gray-400">{{ number_format($order->total_quantity_received, 0) }} received</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-chocolate">
                                    {{ $order->formatted_total }}
                                </div>
                                @if($order->tax_amount > 0 || $order->discount_amount > 0)
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        @if($order->tax_amount > 0) Tax: {{ number_format($order->tax_amount, 0) }} @endif
                                        @if($order->discount_amount > 0) Disc: -{{ number_format($order->discount_amount, 0) }} @endif
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->order_date?->format('M d') ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $order->createdBy->name ?? 'System' }}</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->expected_delivery_date)
                                    <div class="text-sm text-gray-900">{{ $order->expected_delivery_date->format('M d') }}</div>
                                    <span class="text-[10px] font-bold uppercase tracking-wide {{ $order->delivery_status['class'] ?? 'text-gray-400' }}">
                                        {{ $order->delivery_status['text'] ?? 'Scheduled' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Not set</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    @if($order->action_capabilities['can_submit'])
                                        <button onclick="submitOrder({{ $order->id }})" class="p-1.5 text-green-600 hover:text-white hover:bg-green-600 rounded transition-colors tooltip" title="Submit">
                                            <i class="fas fa-paper-plane text-sm"></i>
                                        </button>
                                    @endif
                                    
                                    <button onclick="viewOrder({{ $order->id }})" class="p-1.5 text-chocolate hover:text-white hover:bg-chocolate rounded transition-colors tooltip" title="View">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    
                                    <button onclick="printOrder({{ $order->id }})" class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-500 rounded transition-colors tooltip" title="Print">
                                        <i class="fas fa-print text-sm"></i>
                                    </button>

                                    @if($order->status === 'sent')
                                        <button onclick="acknowledgeOrder({{ $order->id }})" class="p-1.5 text-blue-600 hover:text-white hover:bg-blue-600 rounded transition-colors tooltip" title="Acknowledge">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                    @endif
                                    
                                    @if($order->action_capabilities['can_delete'])
                                        <button onclick="deleteOrder({{ $order->id }})" class="p-1.5 text-red-500 hover:text-white hover:bg-red-500 rounded transition-colors tooltip" title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                                        <i class="fas fa-inbox text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <p class="font-display text-lg font-bold text-chocolate">No Open Orders</p>
                                    <p class="text-sm text-gray-500 mt-1 mb-4">There are currently no open purchase orders to track.</p>
                                    <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center px-4 py-2 bg-chocolate text-white text-xs font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-sm">
                                        <i class="fas fa-plus mr-2"></i> Create New PO
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($openOrders->hasPages())
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span>Show:</span>
                    <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded bg-white text-xs focus:ring-chocolate focus:border-chocolate py-1">
                        <option value="15" {{ $openOrders->perPage() == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ $openOrders->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $openOrders->perPage() == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div>{{ $openOrders->links() }}</div>
            </div>
        @endif
    </div>

   

</div>

{{-- CONFIRMATION MODAL --}}
<div id="confirmModal" class="hidden fixed inset-0 z-50 bg-chocolate/20 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center border border-border-soft transform transition-all scale-100">
        <div class="w-14 h-14 bg-chocolate/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-chocolate/20">
            <i class="fas fa-question text-chocolate text-2xl"></i>
        </div>
        <h3 class="text-xl font-display font-bold text-chocolate mb-2" id="confirmTitle">Confirm Action</h3>
        <p class="text-gray-600 mb-6 text-sm leading-relaxed" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeConfirm()" class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-all text-sm">Cancel</button>
            <button id="confirmBtn" class="px-4 py-2.5 bg-chocolate text-white rounded-lg font-bold hover:bg-chocolate-dark transition-colors shadow-md text-sm">Confirm</button>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">


@push('scripts')
<script>
// All JavaScript Logic Preserved
class OpenOrdersManager {
    constructor() {
        this.selectedOrders = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateSelectionUI();
    }

    setupEventListeners() {
        document.getElementById('selectAll')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.getElementById('selectAllTable')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updateSelectionUI.bind(this));
        });

        document.getElementById('exportDropdown')?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('exportMenu').classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('exportMenu');
            const button = document.getElementById('exportDropdown');
            if (dropdown && button && !dropdown.contains(e.target) && !button.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    handleSelectAll(e) {
        document.querySelectorAll('.order-checkbox:not(:disabled)').forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        this.updateSelectionUI();
    }

    updateSelectionUI() {
        const checkboxes = document.querySelectorAll('.order-checkbox:checked');
        const selectedCount = checkboxes.length;
        
        const countEls = ['selectedCount', 'bulkSelectedCount'];
        countEls.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.textContent = selectedCount;
        });

        const bulkConfirmBtn = document.getElementById('bulkConfirmBtn');
        const bulkExportBtn = document.getElementById('bulkExportBtn');
        const bulkInfo = document.getElementById('bulkInfo');
        
        const hasSelectableItems = selectedCount > 0;
        if (bulkConfirmBtn) bulkConfirmBtn.disabled = !hasSelectableItems;
        if (bulkExportBtn) bulkExportBtn.disabled = !hasSelectableItems;

        if (bulkInfo && selectedCount > 0) {
            let totalValue = 0;
            let totalItems = 0;

            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalText = row.querySelector('td:nth-child(5) div').textContent;
                const itemsText = row.querySelector('td:nth-child(4) div').textContent;

                const value = parseFloat(totalText.replace(/[₱,]/g, '')) || 0;
                const items = parseInt(itemsText.match(/\d+/)?.[0] || 0);

                totalValue += value;
                totalItems += items;
            });

            bulkInfo.textContent = `(${totalItems} items, ₱${totalValue.toLocaleString()})`;
        } else if (bulkInfo) {
            bulkInfo.textContent = '';
        }
        
        const selectAll = document.getElementById('selectAll');
        const selectAllTable = document.getElementById('selectAllTable');
        
        if(selectAllTable) {
            const total = document.querySelectorAll('.order-checkbox').length;
            const allChecked = selectedCount === total && total > 0;
            selectAllTable.checked = allChecked;
            selectAllTable.indeterminate = selectedCount > 0 && !allChecked;
            if(selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = selectedCount > 0 && !allChecked;
            }
        }
    }

    // Action Wrappers
    viewOrder(id) { window.location.href = `{{ route('purchasing.po.show', '__ID__') }}`.replace('__ID__', id); }
    printOrder(id) { window.open(`{{ route('purchasing.po.print', '__ID__') }}`.replace('__ID__', id), '_blank'); }
    
    acknowledgeOrder(id) {
        this.showConfirm('Acknowledge Order', 'Acknowledge supplier confirmation for this purchase order?', () => {
            this.submitForm(`{{ route('purchasing.po.acknowledge', '__ID__') }}`.replace('__ID__', id), 'PATCH');
        });
    }

    submitForm(action, method) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);

        const m = document.createElement('input');
        m.type = 'hidden'; m.name = '_method'; m.value = method;
        form.appendChild(m);

        document.body.appendChild(form);
        form.submit();
    }

    showConfirm(t, m, cb) {
        document.getElementById('confirmTitle').textContent = t;
        document.getElementById('confirmMessage').textContent = m;
        document.getElementById('confirmBtn').onclick = () => { this.closeConfirm(); cb(); };
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    
    closeConfirm() { document.getElementById('confirmModal').classList.add('hidden'); }

    getSelectedIds() {
        return Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
    }

    bulkConfirm() {
        const ids = this.getSelectedIds();
        if (ids.length === 0) return;
        this.showConfirm('Bulk Confirm', `Confirm ${ids.length} order(s)?`, () => {
            ids.forEach(id => this.submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', id), 'PATCH'));
        });
    }

    bulkExport() {
        const ids = this.getSelectedIds();
        if (ids.length === 0) return;
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'pdf');
        params.set('selected_ids', ids.join(','));
        window.open(`{{ route('purchasing.po.open') }}?${params.toString()}`, '_blank');
    }
}

// Global Functions
let openOrdersManager;
document.addEventListener('DOMContentLoaded', () => { openOrdersManager = new OpenOrdersManager(); });

function clearFilters() { window.location.href = "{{ route('purchasing.po.open') }}"; }
function changePerPage(val) { 
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    window.location.href = url.toString();
}
function exportData(format) {
    const url = new URL(window.location.href);
    url.searchParams.set('export', format);
    const ids = openOrdersManager.getSelectedIds();
    if(ids.length) url.searchParams.set('selected_ids', ids.join(','));
    window.open(url.toString(), '_blank');
    document.getElementById('exportMenu').classList.add('hidden');
}

// Expose methods
window.viewOrder = (id) => openOrdersManager.viewOrder(id);
window.printOrder = (id) => openOrdersManager.printOrder(id);
window.acknowledgeOrder = (id) => openOrdersManager.acknowledgeOrder(id);
window.bulkConfirm = () => openOrdersManager.bulkConfirm();
window.bulkExport = () => openOrdersManager.bulkExport();
window.closeConfirm = () => openOrdersManager.closeConfirm();

// Modal backdrop close
document.getElementById('confirmModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) openOrdersManager.closeConfirm();
});
</script>
@endpush
@endsection