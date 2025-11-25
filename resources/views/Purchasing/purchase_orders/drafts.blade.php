@extends('Purchasing.layout.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-3">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white border-b border-gray-200 px-4 py-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Draft Purchase Orders</h1>
            <p class="text-xs text-gray-500">
                @if($draftOrders->total() > 0)
                    {{ number_format($draftOrders->total()) }} total orders
                @else
                    Manage and process draft purchase orders
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
            <a href="{{ route('purchasing.po.create') }}" 
               class="inline-flex items-center px-3 py-1.5 bg-chocolate text-white text-xs font-medium rounded-md hover:bg-chocolate-dark transition-colors">
                <i class="fas fa-plus mr-1"></i>New PO
            </a>
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
        <form id="filterForm" method="GET" action="{{ route('purchasing.po.drafts') }}">
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
                Showing {{ $draftOrders->firstItem() ?? 0 }} to {{ $draftOrders->lastItem() ?? 0 }} of {{ $draftOrders->total() }} results
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
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Delivery</th>
                        <th class="w-20 px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($draftOrders as $order)
                        <tr class="hover:bg-gray-50 po-row" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}">
                            
                            <td class="px-2 py-2">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox text-chocolate focus:ring-chocolate"
                                       data-can-submit="{{ $order->action_capabilities['can_submit'] ? 'true' : 'false' }}"
                                       data-can-delete="{{ $order->action_capabilities['can_delete'] ? 'true' : 'false' }}">
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="text-sm font-medium text-gray-900">{{ $order->po_number }}</div>
                                @if($order->is_overdue)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Overdue
                                    </span>
                                @endif
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
                                        <div class="text-xs text-gray-500">{{ $order->supplier->supplier_code ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="text-sm text-gray-900">{{ $order->total_items_count }} items</div>
                                <div class="text-xs text-gray-500">{{ number_format($order->total_quantity_ordered, 0) }} qty</div>
                            </td>
                            
                            <td class="px-2 py-2 text-right">
                                <div class="text-sm font-medium text-gray-900">{{ $order->formatted_total }}</div>
                            </td>
                            
                            <td class="px-2 py-2">
                                <div class="text-sm text-gray-900">{{ $order->order_date?->format('M d') ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $order->createdBy->name ?? '' }}</div>
                            </td>
                            
                            <td class="px-2 py-2">
                                @if($order->expected_delivery_date)
                                    <div class="text-sm text-gray-900">{{ $order->expected_delivery_date->format('M d') }}</div>
                                    <div class="text-xs {{ $order->delivery_status['class'] ?? 'text-gray-500' }}">
                                        {{ $order->delivery_status['text'] ?? 'Scheduled' }}
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Not set</span>
                                @endif
                            </td>
                            
                            <td class="px-2 py-2 text-center">
                                <div class="flex justify-center space-x-1">
                                    @if($order->action_capabilities['can_submit'])
                                        <button onclick="submitOrder({{ $order->id }})" 
                                                class="p-1 text-green-600 hover:text-green-900 transition-colors" 
                                                title="Submit">
                                            <i class="fas fa-paper-plane text-xs"></i>
                                        </button>
                                    @endif
                                    
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
                                    
                                    @if($order->action_capabilities['can_delete'])
                                        <button onclick="deleteOrder({{ $order->id }})" 
                                                class="p-1 text-red-600 hover:text-red-900 transition-colors" 
                                                title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-2 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                <p class="text-sm">No draft purchase orders found</p>
                                <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center mt-2 px-3 py-1.5 bg-chocolate text-white text-xs rounded-md hover:bg-chocolate-dark transition-colors">
                                    <i class="fas fa-plus mr-1"></i>Create New PO
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($draftOrders->hasPages())
            <div class="px-3 py-2 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500">Rows per page:</span>
                        <select onchange="changePerPage(this.value)" class="border border-gray-300 rounded text-xs focus:ring-1 focus:ring-chocolate focus:border-chocolate">
                            <option value="15" {{ $draftOrders->perPage() == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $draftOrders->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $draftOrders->perPage() == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div>
                        {{ $draftOrders->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    @if($draftOrders->count() > 0)
        <div class="mx-4 bg-white border border-gray-200 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-600">
                    <span id="bulkSelectedCount">0</span> orders selected
                    <span id="bulkInfo" class="ml-2"></span>
                </div>
                <div class="flex items-center space-x-2">
                    @if(auth()->user()->hasRole(['purchasing', 'admin']))
                        <button onclick="bulkSubmit()" 
                                class="px-3 py-1.5 bg-green-600 text-white text-xs rounded-md hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled id="bulkSubmitBtn">
                            <i class="fas fa-paper-plane mr-1"></i>Submit
                        </button>
                        <button onclick="bulkDelete()" 
                                class="px-3 py-1.5 bg-red-600 text-white text-xs rounded-md hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled id="bulkDeleteBtn">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    @if($draftOrders->count() > 0)
        <div class="mx-4 grid grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-file-invoice text-chocolate text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Total</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $draftOrders->total() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Value</div>
                        <div class="text-lg font-semibold text-gray-900">
                            ₱{{ number_format($draftOrders->sum('grand_total'), 0) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Overdue</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $draftOrders->filter(fn($order) => $order->is_overdue)->count() }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center">
                    <i class="fas fa-clock text-blue-600 text-lg"></i>
                    <div class="ml-2">
                        <div class="text-xs text-gray-500">Urgent</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $draftOrders->filter(fn($order) => $order->priority === 'urgent')->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Simple Confirmation Modal --}}
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white rounded-lg p-4 max-w-sm mx-4">
            <div class="flex items-center mb-3">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                <h3 class="text-sm font-medium text-gray-900" id="confirmTitle">Confirm Action</h3>
            </div>
            <p class="text-xs text-gray-600 mb-4" id="confirmMessage">Are you sure?</p>
            <div class="flex justify-end space-x-2">
                <button onclick="closeConfirm()" class="px-3 py-1 text-xs text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                <button id="confirmBtn" class="px-3 py-1 bg-chocolate text-white text-xs rounded hover:bg-chocolate-dark">Confirm</button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script>
class DraftsManager {
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
        const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
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
        let canSubmitCount = 0;
        let canDeleteCount = 0;

        checkboxes.forEach(checkbox => {
            if (checkbox.dataset.canSubmit === 'true') canSubmitCount++;
            if (checkbox.dataset.canDelete === 'true') canDeleteCount++;
        });

        if (bulkSubmitBtn) bulkSubmitBtn.disabled = !hasSelectableItems || canSubmitCount === 0;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = !hasSelectableItems || canDeleteCount === 0;

        // Update info
        if (bulkInfo && selectedCount > 0) {
            let totalValue = 0;
            let totalItems = 0;

            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalText = row.querySelector('td:nth-child(5) .text-sm').textContent;
                const itemsText = row.querySelector('td:nth-child(4) .text-sm').textContent;

                const value = parseFloat(totalText.replace(/[₱,]/g, '')) || 0;
                const items = parseInt(itemsText.match(/\d+/)?.[0] || 0);

                totalValue += value;
                totalItems += items;
            });

            bulkInfo.textContent = `(${totalItems} items, ₱${totalValue.toLocaleString()})`;
        } else if (bulkInfo) {
            bulkInfo.textContent = '';
        }
    }

    // Action functions
    submitOrder(orderId) {
        this.showConfirm('Submit Order', 'Submit this purchase order for approval?', () => {
            this.submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', orderId), 'PATCH');
        });
    }

    viewOrder(orderId) {
        window.location.href = `{{ route('purchasing.po.show', '__ID__') }}`.replace('__ID__', orderId);
    }

    printOrder(orderId) {
        window.open(`{{ route('purchasing.po.print', '__ID__') }}`.replace('__ID__', orderId), '_blank');
    }

    deleteOrder(orderId) {
        this.showConfirm('Delete Order', 'Are you sure you want to delete this purchase order?', () => {
            this.submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', orderId), 'DELETE');
        });
    }

    // Bulk actions
    bulkSubmit() {
        const selectedIds = this.getSelectedIds();
        if (selectedIds.length === 0) return;

        this.showConfirm('Bulk Submit', `Submit ${selectedIds.length} order(s) for approval?`, () => {
            selectedIds.forEach(id => {
                this.submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', id), 'PATCH');
            });
        });
    }

    bulkDelete() {
        const selectedIds = this.getSelectedIds();
        if (selectedIds.length === 0) return;

        this.showConfirm('Bulk Delete', `Delete ${selectedIds.length} order(s)? This cannot be undone.`, () => {
            selectedIds.forEach(id => {
                this.submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', id), 'DELETE');
            });
        });
    }

    getSelectedIds() {
        return Array.from(document.querySelectorAll('.order-checkbox:checked'))
                    .map(cb => cb.value);
    }

    // Utility functions
    submitForm(action, method) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = method;

        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = csrfToken;

        form.appendChild(methodField);
        form.appendChild(csrfField);
        document.body.appendChild(form);
        form.submit();
    }

    showConfirm(title, message, callback) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmBtn').onclick = () => {
            this.closeConfirm();
            callback();
        };
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    closeConfirm() {
        document.getElementById('confirmModal').classList.add('hidden');
    }
}

// Global functions
function clearFilters() {
    window.location.href = "{{ route('purchasing.po.drafts') }}";
}

function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    window.location.href = `{{ route('purchasing.po.drafts') }}?${params.toString()}`;
}

function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    params.set('selected_ids', draftsManager.getSelectedIds().join(','));
    window.open(`{{ route('purchasing.po.drafts') }}?${params.toString()}`, '_blank');
    document.getElementById('exportMenu').classList.add('hidden');
}

// Initialize
let draftsManager;

document.addEventListener('DOMContentLoaded', function() {
    draftsManager = new DraftsManager();
});

// Global functions for backward compatibility
function submitOrder(id) { draftsManager.submitOrder(id); }
function viewOrder(id) { draftsManager.viewOrder(id); }
function printOrder(id) { draftsManager.printOrder(id); }
function deleteOrder(id) { draftsManager.deleteOrder(id); }
function bulkSubmit() { draftsManager.bulkSubmit(); }
function bulkDelete() { draftsManager.bulkDelete(); }
function closeConfirm() { draftsManager.closeConfirm(); }

// Close modal on backdrop click
document.getElementById('confirmModal')?.addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        draftsManager.closeConfirm();
    }
});
</script>
@endpush