@extends('Purchasing.layout.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 font-sans text-gray-600 pb-24">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Purchase History</h1>
            <p class="text-sm text-gray-500">
                @if($completedOrders->total() > 0)
                    <span class="font-bold text-caramel">{{ number_format($completedOrders->total()) }}</span> completed orders archived.
                @else
                    Archive of all completed and fully delivered orders.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
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
            <form id="filterForm" method="GET" action="{{ route('purchasing.po.history') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                {{-- Search --}}
                <div class="md:col-span-4 relative group">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search history..." 
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

                {{-- Date Filter --}}
                <div class="md:col-span-3 relative">
                    <select name="date_filter" class="w-full px-4 py-2.5 bg-cream-bg border-transparent focus:bg-white border focus:border-caramel rounded-lg text-sm text-gray-600 cursor-pointer focus:ring-2 focus:ring-caramel/20 transition-all appearance-none">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
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

        {{-- Bulk Actions Bar (Dynamic) --}}
        @if($completedOrders->count() > 0)
            <div class="bg-cream-bg px-6 py-3 border-b border-border-soft flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span class="font-bold text-chocolate bg-white border border-border-soft px-2 py-0.5 rounded-md shadow-sm" id="bulkSelectedCount">0</span>
                    <span>orders selected</span>
                    <span id="bulkInfo" class="text-xs text-caramel font-medium ml-2 italic"></span>
                </div>
                
                <div class="flex items-center gap-2">
                    <button onclick="bulkExport()" id="bulkExportBtn" disabled 
                            class="px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-download"></i> Export Selected
                    </button>
                </div>
            </div>
        @endif

        {{-- History Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-white">
                    <tr>
                        <th class="w-12 px-6 py-4 text-left">
                            <input type="checkbox" id="selectAllTable" class="rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Date Completed</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">PO Number</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Supplier</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($completedOrders as $order)
                        <tr class="po-row hover:bg-cream-bg/50 transition-colors group" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}">
                            
                            <td class="px-6 py-4">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate w-4 h-4 cursor-pointer">
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $order->actual_delivery_date ? $order->actual_delivery_date->format('M d, Y') : $order->updated_at->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $order->actual_delivery_date ? $order->actual_delivery_date->format('Y') : $order->updated_at->format('Y') }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-chocolate text-sm bg-chocolate/5 px-2 py-1 rounded border border-chocolate/10">
                                    #{{ $order->po_number }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-cream-bg flex items-center justify-center text-caramel font-bold text-xs border border-border-soft shadow-sm">
                                        {{ strtoupper(substr($order->supplier->name ?? 'N/A', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $order->supplier->name ?? 'N/A' }}</div>
                                        @if($order->supplier && $order->supplier->contact_person)
                                            <div class="text-xs text-gray-500">{{ $order->supplier->contact_person }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-chocolate">
                                    ₱{{ number_format($order->grand_total ?? 0, 2) }}
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-green-100 text-green-800 border border-green-200">
                                    <i class="fas fa-check mr-1.5"></i> Completed
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button onclick="viewOrder({{ $order->id }})" 
                                            class="p-1.5 text-chocolate hover:text-white hover:bg-chocolate rounded transition-colors tooltip" title="View">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    
                                    <button onclick="printOrder({{ $order->id }})" 
                                            class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-500 rounded transition-colors tooltip" title="Print">
                                        <i class="fas fa-print text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                                        <i class="fas fa-inbox text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <p class="font-display text-lg font-bold text-chocolate">No History Found</p>
                                    <p class="text-sm text-gray-500 mt-1">Completed purchase orders will be archived here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($completedOrders->hasPages())
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span>Show:</span>
                    <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded bg-white text-xs focus:ring-chocolate focus:border-chocolate py-1">
                        <option value="15" {{ $completedOrders->perPage() == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ $completedOrders->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $completedOrders->perPage() == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div>{{ $completedOrders->links() }}</div>
            </div>
        @endif
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">


@push('scripts')
<script>
class HistoryManager {
    constructor() {
        this.selectedOrders = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateSelectionUI();
    }

    setupEventListeners() {
        // Checkbox handlers
        document.getElementById('selectAllTable')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updateSelectionUI.bind(this));
        });

        // Export dropdown
        document.getElementById('exportDropdown')?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('exportMenu').classList.toggle('hidden');
        });

        // Close dropdown on outside click
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
        const selectAllTable = document.getElementById('selectAllTable');
        const bulkSelectedCountElement = document.getElementById('bulkSelectedCount');
        const bulkExportBtn = document.getElementById('bulkExportBtn');
        const bulkInfo = document.getElementById('bulkInfo');

        if (bulkSelectedCountElement) bulkSelectedCountElement.textContent = selectedCount;

        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.order-checkbox:not(:disabled)');
        const allChecked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
        const noneChecked = selectedCount === 0;

        if (selectAllTable) {
            selectAllTable.checked = allChecked;
            selectAllTable.indeterminate = !allChecked && !noneChecked;
        }

        // Update bulk action buttons
        if (bulkExportBtn) bulkExportBtn.disabled = selectedCount === 0;

        // Update info
        if (bulkInfo && selectedCount > 0) {
            let totalValue = 0;
            let totalItems = 0; // Note: In this table context, items per order isn't easily available in row data without adding data-attributes
            // For simplicity in this view redesign, we'll just show count or sum value if available in DOM

            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalText = row.querySelector('td:nth-child(5) div').textContent; // Adjust index based on table structure
                const value = parseFloat(totalText.replace(/[₱,]/g, '')) || 0;
                totalValue += value;
            });

            bulkInfo.textContent = `(Total Value: ₱${totalValue.toLocaleString()})`;
        } else if (bulkInfo) {
            bulkInfo.textContent = '';
        }
    }

    // Action functions
    viewOrder(orderId) {
        window.location.href = `{{ route('purchasing.po.show', '__ID__') }}`.replace('__ID__', orderId);
    }

    printOrder(orderId) {
        window.open(`{{ route('purchasing.po.print', '__ID__') }}`.replace('__ID__', orderId), '_blank');
    }

    // Bulk actions
    bulkExport() {
        const selectedIds = this.getSelectedIds();
        if (selectedIds.length === 0) return;

        const params = new URLSearchParams(window.location.search);
        params.set('export', 'pdf');
        params.set('selected_ids', selectedIds.join(','));
        window.open(`{{ route('purchasing.po.history') }}?${params.toString()}`, '_blank');
        document.getElementById('exportMenu')?.classList.add('hidden');
    }

    getSelectedIds() {
        return Array.from(document.querySelectorAll('.order-checkbox:checked'))
                    .map(cb => cb.value);
    }
}

// Global functions
function clearFilters() {
    window.location.href = "{{ route('purchasing.po.history') }}";
}

function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    window.location.href = `{{ route('purchasing.po.history') }}?${params.toString()}`;
}

function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    const ids = historyManager.getSelectedIds();
    if(ids.length) params.set('selected_ids', ids.join(','));
    window.open(`{{ route('purchasing.po.history') }}?${params.toString()}`, '_blank');
    document.getElementById('exportMenu').classList.add('hidden');
}

// Initialize
let historyManager;

document.addEventListener('DOMContentLoaded', function() {
    historyManager = new HistoryManager();
});

// Global functions for backward compatibility
function viewOrder(id) { historyManager.viewOrder(id); }
function printOrder(id) { historyManager.printOrder(id); }
function bulkExport() { historyManager.bulkExport(); }
</script>
@endpush
@endsection