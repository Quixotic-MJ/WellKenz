@extends('Purchasing.layout.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-3">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white border-b border-gray-200 px-4 py-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Purchase History</h1>
            <p class="text-xs text-gray-500">
                @if($completedOrders->total() > 0)
                    {{ number_format($completedOrders->total()) }} total orders
                @else
                    Archive of all completed and fully delivered orders
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <div class="relative">
                <button id="exportDropdown" class="inline-flex items-center px-2 py-1.5 text-xs text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors">
                    <i class="fas fa-download mr-1"></i>Export
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-1 w-32 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportData('pdf')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-1"></i>PDF
                        </button>
                        <button onclick="exportData('excel')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-1"></i>Excel
                        </button>
                        <button onclick="exportData('csv')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-1"></i>CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mx-4 bg-green-50 border-l-4 border-green-400 p-2">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-2"></i>
                <span class="text-xs text-green-700">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mx-4 bg-red-50 border-l-4 border-red-400 p-2">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                <span class="text-xs text-red-700">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="mx-4 bg-white border border-gray-200 rounded-lg">
        <form id="filterForm" method="GET" action="{{ route('purchasing.po.history') }}">
            <div class="p-3 border-b border-gray-100">
                <div class="grid grid-cols-4 gap-2">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search orders..."
                               class="w-full pl-7 pr-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                        <i class="fas fa-search absolute left-2 top-2 text-gray-400 text-xs"></i>
                    </div>
                    <select name="supplier_id" class="px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Suppliers</option>
                        @foreach(\App\Models\Supplier::where('is_active', true)->orderBy('name')->limit(10)->get() as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="date_filter" class="px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <div class="flex space-x-1">
                        <button type="submit" class="flex-1 px-2 py-1.5 bg-chocolate text-white text-xs rounded-md hover:bg-chocolate-dark transition-colors">
                            Apply
                        </button>
                        <button type="button" onclick="clearFilters()" class="px-2 py-1.5 bg-gray-100 text-gray-700 text-xs rounded-md hover:bg-gray-200 transition-colors">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="mx-4 bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-3 py-2 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div class="text-xs text-gray-600">
                Showing {{ $completedOrders->firstItem() ?? 0 }} to {{ $completedOrders->lastItem() ?? 0 }} of {{ $completedOrders->total() }} results
            </div>
            <div class="flex items-center space-x-2">
                <input type="checkbox" id="selectAll" class="text-chocolate focus:ring-chocolate">
                <span class="text-xs text-gray-500">Select All</span>
                <span id="selectedCount" class="text-xs font-medium text-chocolate">0</span>selected
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-6 px-2 py-2">
                            <input type="checkbox" id="selectAllTable" class="text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date Completed</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="w-20 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($completedOrders as $order)
                        <tr class="hover:bg-gray-50 po-row" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}">
                            
                            <td class="px-2 py-2">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox text-chocolate focus:ring-chocolate">
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="text-sm text-gray-900">
                                    {{ $order->actual_delivery_date ? $order->actual_delivery_date->format('M d') : $order->updated_at->format('M d') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->actual_delivery_date ? $order->actual_delivery_date->format('Y') : $order->updated_at->format('Y') }}
                                </div>
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="text-sm font-medium text-gray-900">{{ $order->po_number }}</div>
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">
                                            {{ strtoupper(substr($order->supplier->name ?? 'N/A', 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="ml-2">
                                        <div class="text-sm text-gray-900">{{ $order->supplier->name ?? 'N/A' }}</div>
                                        @if($order->supplier && $order->supplier->contact_person)
                                            <div class="text-xs text-gray-500">{{ $order->supplier->contact_person }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-2 py-2 text-right">
                                <div class="text-sm font-medium text-gray-900">₱{{ number_format($order->grand_total ?? 0, 0) }}</div>
                            </td>
                            
                            <td class="px-2 py-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Completed
                                </span>
                            </td>
                            
                            <td class="px-2 py-2 text-center">
                                <div class="flex justify-center space-x-1">
                                    <button onclick="viewOrder({{ $order->id }})" 
                                            class="p-1 text-gray-600 hover:text-gray-900 transition-colors" 
                                            title="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    
                                    <button onclick="printOrder({{ $order->id }})" 
                                            class="p-1 text-purple-600 hover:text-purple-900 transition-colors" 
                                            title="Print">
                                        <i class="fas fa-print text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                <p class="text-sm">No completed purchase orders found</p>
                                <p class="text-xs text-gray-400 mt-1">Completed orders will appear here once fully delivered.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($completedOrders->hasPages())
            <div class="px-3 py-2 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500">Rows per page:</span>
                        <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded text-xs focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                            <option value="15" {{ $completedOrders->perPage() == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $completedOrders->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $completedOrders->perPage() == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div>
                        {{ $completedOrders->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    @if($completedOrders->count() > 0)
        <div class="mx-4 bg-white border border-gray-200 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-600">
                    <span id="bulkSelectedCount">0</span> orders selected
                    <span id="bulkInfo" class="ml-2"></span>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="bulkExport()" 
                            class="px-3 py-1.5 bg-chocolate text-white text-xs rounded-md hover:bg-chocolate-dark transition disabled:opacity-50 disabled:cursor-not-allowed" 
                            disabled id="bulkExportBtn">
                        <i class="fas fa-download mr-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    @if($completedOrders->count() > 0)
        <div class="mx-4 grid grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice text-chocolate text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Total Orders</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $completedOrders->total() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Total Value</div>
                        <div class="text-lg font-semibold text-gray-900">
                            ₱{{ number_format($completedOrders->sum('grand_total'), 0) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-calendar text-blue-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">This Month</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $completedOrders->filter(function($order) { 
                                return $order->actual_delivery_date && $order->actual_delivery_date->isCurrentMonth(); 
                            })->count() }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-users text-purple-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Suppliers</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $completedOrders->pluck('supplier_id')->unique()->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

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
        document.getElementById('selectAll')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.getElementById('selectAllTable')?.addEventListener('change', this.handleSelectAll.bind(this));
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', this.updateSelectionUI.bind(this));
        });

        // Export dropdown
        document.getElementById('exportDropdown')?.addEventListener('click', () => {
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
        const selectAll = document.getElementById('selectAll');
        const selectAllTable = document.getElementById('selectAllTable');
        const selectedCountElement = document.getElementById('selectedCount');
        const bulkSelectedCountElement = document.getElementById('bulkSelectedCount');
        const bulkExportBtn = document.getElementById('bulkExportBtn');
        const bulkInfo = document.getElementById('bulkInfo');

        if (selectedCountElement) selectedCountElement.textContent = selectedCount;
        if (bulkSelectedCountElement) bulkSelectedCountElement.textContent = selectedCount;

        // Update select all checkboxes
        const allCheckboxes = document.querySelectorAll('.order-checkbox:not(:disabled)');
        const allChecked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
        const noneChecked = selectedCount === 0;

        if (selectAll) {
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && !noneChecked;
        }
        if (selectAllTable) {
            selectAllTable.checked = allChecked;
            selectAllTable.indeterminate = !allChecked && !noneChecked;
        }

        // Update bulk action buttons
        const hasSelectableItems = selectedCount > 0;
        if (bulkExportBtn) bulkExportBtn.disabled = !hasSelectableItems;

        // Update info
        if (bulkInfo && selectedCount > 0) {
            let totalValue = 0;
            let totalItems = 0;

            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalText = row.querySelector('td:nth-child(5) .text-sm').textContent;
                const itemsText = '1'; // Each row represents one order

                const value = parseFloat(totalText.replace(/[₱,]/g, '')) || 0;
                const items = parseInt(itemsText) || 1;

                totalValue += value;
                totalItems += items;
            });

            bulkInfo.textContent = `(${totalItems} orders, ₱${totalValue.toLocaleString()})`;
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
    params.set('selected_ids', historyManager.getSelectedIds().join(','));
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