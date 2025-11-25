@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Open Purchase Orders</h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage and track open purchase orders 
                @if($openOrders->total() > 0)
                    <span class="font-medium">({{ number_format($openOrders->total()) }} total orders)</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <button id="exportDropdown" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition shadow-sm">
                    <i class="fas fa-download mr-2"></i> Export
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportData('pdf')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                        </button>
                        <button onclick="exportData('excel')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-2"></i> Export as Excel
                        </button>
                        <button onclick="exportData('csv')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2"></i> Export as CSV
                        </button>
                    </div>
                </div>
            </div>
            <a href="{{ route('purchasing.po.create') }}" 
               onclick="console.log('Create PO clicked, navigating to:', '{{ route('purchasing.po.create') }}')"
               class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Create New PO
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-green-800">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-red-800">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Enhanced Filters --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <form id="filterForm" method="GET" action="{{ route('purchasing.po.open') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Search</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="PO Number, Supplier Name, or Code..." 
                               class="block w-full pl-10 border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Supplier</label>
                    <select name="supplier_id" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Suppliers</option>
                        @php
                            $availableSuppliers = \App\Models\PurchaseOrder::getAvailableSuppliers();
                        @endphp
                        @foreach($availableSuppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->supplier_code }})
                            </option>
                        @endforeach
                        @if($availableSuppliers->isEmpty())
                            @foreach(\App\Models\Supplier::where('is_active', true)->orderBy('name')->limit(10)->get() as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->supplier_code }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Sort By</label>
                    <select name="sort_by" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="expected_delivery_date" {{ request('sort_by', 'expected_delivery_date') == 'expected_delivery_date' ? 'selected' : '' }}>Expected Delivery</option>
                        <option value="order_date" {{ request('sort_by') == 'order_date' ? 'selected' : '' }}>Order Date</option>
                        <option value="po_number" {{ request('sort_by') == 'po_number' ? 'selected' : '' }}>PO Number</option>
                        <option value="grand_total" {{ request('sort_by') == 'grand_total' ? 'selected' : '' }}>Total Amount</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $openOrders->firstItem() ?? 0 }} to {{ $openOrders->lastItem() ?? 0 }} 
                    of {{ number_format($openOrders->total()) }} results
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="clearFilters()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Clear Filters
                    </button>
                    <button type="submit" class="px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Open Orders Table --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source PR</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="ordersTableBody">
                    @forelse($openOrders as $order)
                        <tr class="hover:bg-gray-50 po-row transition-colors duration-150" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}" 
                            data-date="{{ $order->order_date?->format('Y-m-d') ?? '' }}"
                            data-status="{{ $order->status }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate">
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($order->is_overdue)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                        </span>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $order->po_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {!! $order->status_badge !!}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ strtoupper(substr($order->supplier->name ?? 'N/A', 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $order->supplier->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $order->supplier->supplier_code ?? '' }}
                                            @if($order->supplier?->rating)
                                                <span class="ml-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $order->supplier->rating ? 'text-yellow-400' : 'text-gray-300' }} text-xs"></i>
                                                    @endfor
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $order->total_items_count }} items</div>
                                <div class="text-sm text-gray-500">
                                    {{ number_format($order->total_quantity_ordered, 1) }} ordered
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ number_format($order->total_quantity_received, 1) }} received
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">{{ $order->formatted_total }}</div>
                                @if($order->tax_amount > 0 || $order->discount_amount > 0)
                                    <div class="text-xs text-gray-500">
                                        @if($order->tax_amount > 0)Tax: ₱{{ number_format($order->tax_amount, 2) }}@endif
                                        @if($order->discount_amount > 0)<br>Disc: ₱{{ number_format($order->discount_amount, 2) }}@endif
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $sourcePR = $order->sourcePurchaseRequests->first();
                                @endphp
                                @if($sourcePR)
                                    <div class="text-sm text-gray-900">{{ $sourcePR->pr_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $sourcePR->department ?? '' }}</div>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-pencil-alt mr-1"></i> Direct Creation
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $order->order_date?->format('M d, Y') ?? 'N/A' }}
                                @if($order->order_date)
                                    <div class="text-xs text-gray-500">
                                        {{ $order->order_date->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->expected_delivery_date)
                                    <div class="text-sm text-gray-900">
                                        {{ $order->expected_delivery_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs {{ $order->delivery_status['class'] ?? 'text-gray-500' }}">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $order->delivery_status['text'] ?? 'Scheduled' }}
                                    </div>
                                    @if($order->is_overdue)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                        </span>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-400">
                                        <i class="fas fa-question-circle mr-1"></i> Not set
                                    </span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $order->created_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                                @if($order->createdBy)
                                    <div class="text-xs text-gray-400">
                                        by {{ $order->createdBy->name }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-1">
                                    <button onclick="viewOrder({{ $order->id }})" 
                                            class="p-1 text-gray-600 hover:text-gray-900 transition-colors duration-150" 
                                            title="View Details">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    
                                    <button onclick="printOrder({{ $order->id }})" 
                                            class="p-1 text-purple-600 hover:text-purple-900 transition-colors duration-150" 
                                            title="Print Purchase Order">
                                        <i class="fas fa-print text-sm"></i>
                                    </button>
                                    
                                    @if($order->status === 'sent')
                                        <button onclick="confirmOrder({{ $order->id }})" 
                                                class="p-1 text-blue-600 hover:text-blue-900 transition-colors duration-150" 
                                                title="Confirm Order">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 block"></i>
                                    <p class="text-lg font-medium">No open purchase orders found</p>
                                    <p class="text-sm mt-1">
                                        @if(request()->hasAny(['search', 'supplier_id', 'status']))
                                            Try adjusting your filters or 
                                        @else
                                            Open purchase orders will appear here once created
                                        @endif
                                    </p>
                                    @if(request()->hasAny(['search', 'supplier_id', 'status']))
                                        <button onclick="clearFilters()" class="inline-flex items-center justify-center px-4 py-2 mt-4 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition">
                                            <i class="fas fa-times mr-2"></i> Clear Filters
                                        </button>
                                    @else
                                        <a href="{{ route('purchasing.po.create') }}" class="inline-flex items-center justify-center px-4 py-2 mt-4 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition">
                                            <i class="fas fa-plus mr-2"></i> Create New PO
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Enhanced Pagination --}}
        @if($openOrders->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Showing {{ $openOrders->firstItem() ?? 0 }} to {{ $openOrders->lastItem() ?? 0 }} of {{ $openOrders->total() }} results</span>
                        <select onchange="changePerPage(this.value)" class="ml-4 border-gray-300 rounded text-sm focus:ring-chocolate focus:border-chocolate">
                            <option value="15" {{ $openOrders->perPage() == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ $openOrders->perPage() == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ $openOrders->perPage() == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ $openOrders->perPage() == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>
                    <div>
                        {{ $openOrders->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Quick Stats --}}
    @if($openOrders->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-invoice text-chocolate text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Open Orders</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($openOrders->total()) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Value</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            ₱{{ number_format($openOrders->sum('grand_total'), 0) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Overdue</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $openOrders->filter(fn($order) => $order->is_overdue)->count() }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Due Soon</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $openOrders->filter(fn($order) => $order->expected_delivery_date && $order->expected_delivery_date->isAfter(now()) && $order->expected_delivery_date->diffInDays() <= 3)->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- CSRF Token Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@push('scripts')
<script>
// Filter functionality (similar to drafts but adapted for open orders)
function setupFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const supplierFilter = document.getElementById('supplierFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchFilter && supplierFilter && statusFilter) {
        [searchFilter, supplierFilter, statusFilter].forEach(filter => {
            filter.addEventListener('input', debounce(applyFilters, 300));
            filter.addEventListener('change', applyFilters);
        });
    }
}

// Apply filters functionality
function applyFilters() {
    const searchTerm = (document.getElementById('searchFilter')?.value || '').toLowerCase();
    const supplierTerm = (document.getElementById('supplierFilter')?.value || '').toLowerCase();
    const statusTerm = document.getElementById('statusFilter')?.value || '';
    
    const rows = document.querySelectorAll('.po-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const poText = row.dataset.po || '';
        const supplierText = row.dataset.supplier || '';
        const statusText = row.dataset.status || '';
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !poText.includes(searchTerm) && !supplierText.includes(searchTerm)) {
            showRow = false;
        }
        
        // Supplier filter
        if (supplierTerm && !supplierText.includes(supplierTerm)) {
            showRow = false;
        }
        
        // Status filter
        if (statusTerm && statusText !== statusTerm) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
    });
}

function clearFilters() {
    // Reset form
    const form = document.getElementById('filterForm');
    if (form) {
        form.reset();
        // Submit form to clear filters
        window.location.href = "{{ route('purchasing.po.open') }}";
    }
}

// Selection functionality
function setupSelection() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                if (this.checked) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            });
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });
}

function updateSelectionUI() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    const selectedCount = checkboxes.length;
    // Add bulk action functionality here if needed
}

// Action functions
function viewOrder(orderId) {
    window.location.href = `{{ route('purchasing.po.show', '__ID__') }}`.replace('__ID__', orderId);
}

function printOrder(orderId) {
    window.open(`{{ route('purchasing.po.print', '__ID__') }}`.replace('__ID__', orderId), '_blank');
}

function confirmOrder(orderId) {
    if (confirm('Are you sure you want to confirm this purchase order?')) {
        submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', orderId), 'PATCH');
    }
}

// Helper function for form submission
function submitForm(action, method) {
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

// Export functionality
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    params.set('selected_ids', getSelectedIds().join(','));
    
    window.open(`{{ route('purchasing.po.open') }}?${params.toString()}`, '_blank');
    
    // Hide dropdown
    document.getElementById('exportMenu').classList.add('hidden');
}

// Utility functions
function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    window.location.href = `{{ route('purchasing.po.open') }}?${params.toString()}`;
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.order-checkbox:checked'))
                .map(cb => cb.value);
}

function showNotification(message, type = 'info') {
    // Simple notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Export dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    const exportDropdown = document.getElementById('exportDropdown');
    const exportMenu = document.getElementById('exportMenu');
    
    if (exportDropdown && exportMenu) {
        exportDropdown.addEventListener('click', function() {
            exportMenu.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!exportDropdown.contains(event.target) && !exportMenu.contains(event.target)) {
                exportMenu.classList.add('hidden');
            }
        });
    }
});

// Initialize all functionality
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    setupSelection();
    updateSelectionUI();
});
</script>
@endpush