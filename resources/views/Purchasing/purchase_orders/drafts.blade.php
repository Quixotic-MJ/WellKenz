@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Draft Purchase Orders</h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage and process draft purchase orders 
                @if($draftOrders->total() > 0)
                    <span class="font-medium">({{ number_format($draftOrders->total()) }} total orders)</span>
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
        <form id="filterForm" method="GET" action="{{ route('purchasing.po.drafts') }}">
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
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Date Range</label>
                    <select name="date_filter" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Sort By</label>
                    <select name="sort_by" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                        <option value="po_number" {{ request('sort_by') == 'po_number' ? 'selected' : '' }}>PO Number</option>
                        <option value="order_date" {{ request('sort_by') == 'order_date' ? 'selected' : '' }}>Order Date</option>
                        <option value="grand_total" {{ request('sort_by') == 'grand_total' ? 'selected' : '' }}>Total Amount</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $draftOrders->firstItem() ?? 0 }} to {{ $draftOrders->lastItem() ?? 0 }} 
                    of {{ number_format($draftOrders->total()) }} results
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

    {{-- Draft Orders Table --}}
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
                    @forelse($draftOrders as $order)
                        <tr class="hover:bg-gray-50 po-row transition-colors duration-150" 
                            data-po="{{ strtolower($order->po_number) }}" 
                            data-supplier="{{ strtolower($order->supplier->name ?? '') }}" 
                            data-date="{{ $order->order_date?->format('Y-m-d') ?? '' }}"
                            data-priority="{{ $order->priority }}">
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       name="selected_orders[]" 
                                       value="{{ $order->id }}" 
                                       class="order-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate"
                                       data-can-submit="{{ $order->action_capabilities['can_submit'] ? 'true' : 'false' }}"
                                       data-can-delete="{{ $order->action_capabilities['can_delete'] ? 'true' : 'false' }}">
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
                                    {{ number_format($order->total_quantity_ordered, 1) }} total qty
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
                                    <div class="text-xs text-gray-400">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($sourcePR->priority === 'urgent') bg-red-100 text-red-800
                                            @elseif($sourcePR->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($sourcePR->priority === 'normal') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            <i class="fas fa-circle mr-1 text-xs"></i>
                                            {{ ucfirst($sourcePR->priority) }}
                                        </span>
                                    </div>
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
                                    @if($order->action_capabilities['can_edit'])
                                        <button onclick="editOrder({{ $order->id }})" 
                                                class="p-1 text-blue-600 hover:text-blue-900 transition-colors duration-150" 
                                                title="Edit">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                    @endif
                                    
                                    @if($order->action_capabilities['can_submit'])
                                        <button onclick="submitOrder({{ $order->id }})" 
                                                class="p-1 text-green-600 hover:text-green-900 transition-colors duration-150" 
                                                title="Submit for Approval">
                                            <i class="fas fa-paper-plane text-sm"></i>
                                        </button>
                                    @endif
                                    
                                    <button onclick="printOrder({{ $order->id }})" 
                                            class="p-1 text-purple-600 hover:text-purple-900 transition-colors duration-150" 
                                            title="Print Purchase Order">
                                        <i class="fas fa-print text-sm"></i>
                                    </button>
                                    
                                    <button onclick="viewOrder({{ $order->id }})" 
                                            class="p-1 text-gray-600 hover:text-gray-900 transition-colors duration-150" 
                                            title="View Details">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    
                                    @if($order->action_capabilities['can_delete'])
                                        <button onclick="deleteOrder({{ $order->id }})" 
                                                class="p-1 text-red-600 hover:text-red-900 transition-colors duration-150" 
                                                title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
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
                                    <p class="text-lg font-medium">No draft purchase orders found</p>
                                    <p class="text-sm mt-1">
                                        @if(request()->hasAny(['search', 'supplier_id', 'date_filter']))
                                            Try adjusting your filters or 
                                        @else
                                            Create a new purchase order to get started
                                        @endif
                                    </p>
                                    @if(request()->hasAny(['search', 'supplier_id', 'date_filter']))
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
        @if($draftOrders->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Showing {{ $draftOrders->firstItem() ?? 0 }} to {{ $draftOrders->lastItem() ?? 0 }} of {{ $draftOrders->total() }} results</span>
                        <select onchange="changePerPage(this.value)" class="ml-4 border-gray-300 rounded text-sm focus:ring-chocolate focus:border-chocolate">
                            <option value="15" {{ $draftOrders->perPage() == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ $draftOrders->perPage() == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ $draftOrders->perPage() == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ $draftOrders->perPage() == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </div>
                    <div>
                        {{ $draftOrders->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Enhanced Bulk Actions --}}
    @if($draftOrders->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                    <span id="selectedCount">0</span> order(s) selected
                    <span id="selectedInfo" class="ml-2 text-gray-500"></span>
                </div>
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->hasRole(['purchasing', 'admin']))
                        <button onclick="bulkSubmit()" 
                                class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled id="bulkSubmitBtn">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Selected
                        </button>
                        <button onclick="bulkDelete()" 
                                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled id="bulkDeleteBtn">
                            <i class="fas fa-trash mr-2"></i> Delete Selected
                        </button>
                        <button onclick="bulkEdit()" 
                                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled id="bulkEditBtn">
                            <i class="fas fa-edit mr-2"></i> Edit Selected
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Stats --}}
    @if($draftOrders->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-invoice text-chocolate text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Total Drafts</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($draftOrders->total()) }}</div>
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
                            ₱{{ number_format($draftOrders->sum('grand_total'), 0) }}
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
                            {{ $draftOrders->filter(fn($order) => $order->is_overdue)->count() }}
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
                        <div class="text-sm font-medium text-gray-500">Urgent</div>
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $draftOrders->filter(fn($order) => $order->priority === 'urgent')->count() }}
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
// Enhanced filter functionality
function setupFilters() {
    const searchFilter = document.getElementById('searchFilter');
    const supplierFilter = document.getElementById('supplierFilter');
    const dateFilter = document.getElementById('dateFilter');
    
    if (searchFilter && supplierFilter && dateFilter) {
        [searchFilter, supplierFilter, dateFilter].forEach(filter => {
            filter.addEventListener('input', debounce(applyFilters, 300));
            filter.addEventListener('change', applyFilters);
        });
    }
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Enhanced filter application
function applyFilters() {
    const searchTerm = (document.getElementById('searchFilter')?.value || '').toLowerCase();
    const supplierTerm = (document.getElementById('supplierFilter')?.value || '').toLowerCase();
    const dateTerm = document.getElementById('dateFilter')?.value || '';
    const priorityTerm = document.getElementById('priorityFilter')?.value || '';
    
    const rows = document.querySelectorAll('.po-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const poText = row.dataset.po || '';
        const supplierText = row.dataset.supplier || '';
        const dateText = row.dataset.date || '';
        const priorityText = row.dataset.priority || '';
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !poText.includes(searchTerm) && !supplierText.includes(searchTerm)) {
            showRow = false;
        }
        
        // Supplier filter
        if (supplierTerm && !supplierText.includes(supplierTerm)) {
            showRow = false;
        }
        
        // Priority filter
        if (priorityTerm && priorityText !== priorityTerm) {
            showRow = false;
        }
        
        // Date filter
        if (dateTerm) {
            const today = new Date();
            const rowDate = new Date(dateText);
            
            switch(dateTerm) {
                case 'today':
                    showRow = rowDate.toDateString() === today.toDateString();
                    break;
                case 'week':
                    const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    showRow = rowDate >= weekAgo;
                    break;
                case 'month':
                    const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                    showRow = rowDate >= monthAgo;
                    break;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;
    });
    
    // Update visible count for bulk actions
    updateBulkActionState();
}

function clearFilters() {
    // Reset form
    const form = document.getElementById('filterForm');
    if (form) {
        form.reset();
        // Submit form to clear filters
        window.location.href = "{{ route('purchasing.po.drafts') }}";
    }
}

// Enhanced selection functionality
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
            updateSelectionUI();
        });
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectionUI);
    });
}

function updateSelectionUI() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
    const selectedCount = selectedCheckboxes.length;
    const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkEditBtn = document.getElementById('bulkEditBtn');
    const selectAll = document.getElementById('selectAll');
    const selectedCountElement = document.getElementById('selectedCount');
    const selectedInfoElement = document.getElementById('selectedInfo');
    
    // Update count display - only if element exists
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedCount;
    }
    
    // Calculate totals for selected items
    if (selectedCount > 0) {
        let totalValue = 0;
        let totalItems = 0;
        let canSubmitCount = 0;
        let canDeleteCount = 0;
        
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const totalText = row.querySelector('td:nth-child(5) .text-sm').textContent;
            const itemsText = row.querySelector('td:nth-child(4) .text-sm').textContent;
            
            // Extract numeric value (simplified)
            const value = parseFloat(totalText.replace(/[₱,]/g, '')) || 0;
            const items = parseInt(itemsText.match(/\d+/)?.[0] || 0);
            
            totalValue += value;
            totalItems += items;
            
            if (checkbox.dataset.canSubmit === 'true') canSubmitCount++;
            if (checkbox.dataset.canDelete === 'true') canDeleteCount++;
        });
        
        if (selectedInfoElement) {
            selectedInfoElement.textContent = 
                `(${totalItems} items, ₱${totalValue.toLocaleString()})`;
        }
    } else {
        if (selectedInfoElement) {
            selectedInfoElement.textContent = '';
        }
    }
    
    // Update select all checkbox
    const allChecked = selectedCount === checkboxes.length && checkboxes.length > 0;
    const noneChecked = selectedCount === 0;
    
    if (selectAll) {
        selectAll.checked = allChecked;
        selectAll.indeterminate = !allChecked && !noneChecked;
    }
    
    // Update button states
    const hasSelectableItems = selectedCount > 0;
    
    if (bulkSubmitBtn) {
        bulkSubmitBtn.disabled = !hasSelectableItems || canSubmitCount === 0;
    }
    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = !hasSelectableItems || canDeleteCount === 0;
    }
    if (bulkEditBtn) {
        bulkEditBtn.disabled = !hasSelectableItems;
    }
}

// Enhanced action functions
function editOrder(orderId) {
    if (confirm('Are you sure you want to edit this purchase order?')) {
        window.location.href = `{{ route('purchasing.po.edit', '__ID__') }}`.replace('__ID__', orderId);
    }
}

function submitOrder(orderId) {
    if (confirm('Are you sure you want to submit this purchase order for approval? This action cannot be undone.')) {
        submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', orderId), 'PATCH');
    }
}

function viewOrder(orderId) {
    window.location.href = `{{ route('purchasing.po.show', '__ID__') }}`.replace('__ID__', orderId);
}

function printOrder(orderId) {
    window.open(`{{ route('purchasing.po.print', '__ID__') }}`.replace('__ID__', orderId), '_blank');
}

function deleteOrder(orderId) {
    if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
        submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', orderId), 'DELETE');
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

// Enhanced bulk operations
function bulkSubmit() {
    const selectedIds = getSelectedIds();
    
    if (selectedIds.length === 0) {
        showNotification('Please select at least one order to submit.', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to submit ${selectedIds.length} purchase order(s) for approval?`)) {
        // Submit individually (can be improved to batch API endpoint)
        selectedIds.forEach(id => {
            submitForm(`{{ route('purchasing.po.submit', '__ID__') }}`.replace('__ID__', id), 'PATCH');
        });
        
        showNotification('Submitting selected orders...', 'info');
    }
}

function bulkDelete() {
    const selectedIds = getSelectedIds();
    
    if (selectedIds.length === 0) {
        showNotification('Please select at least one order to delete.', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedIds.length} purchase order(s)? This action cannot be undone.`)) {
        selectedIds.forEach(id => {
            submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', id), 'DELETE');
        });
        
        showNotification('Deleting selected orders...', 'info');
    }
}

function bulkEdit() {
    const selectedIds = getSelectedIds();
    
    if (selectedIds.length === 0) {
        showNotification('Please select at least one order to edit.', 'warning');
        return;
    }
    
    if (selectedIds.length === 1) {
        editOrder(selectedIds[0]);
    } else {
        showNotification('Bulk edit feature coming soon. Please select only one order for editing.', 'info');
    }
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.order-checkbox:checked'))
                .map(cb => cb.value);
}

// Export functionality
function exportData(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    params.set('selected_ids', getSelectedIds().join(','));
    
    window.open(`{{ route('purchasing.po.drafts') }}?${params.toString()}`, '_blank');
    
    // Hide dropdown
    document.getElementById('exportMenu').classList.add('hidden');
}

// Utility functions
function changePerPage(perPage) {
    const params = new URLSearchParams(window.location.search);
    params.set('per_page', perPage);
    window.location.href = `{{ route('purchasing.po.drafts') }}?${params.toString()}`;
}

function updateBulkActionState() {
    updateSelectionUI();
}

function showNotification(message, type = 'info') {
    // Simple notification - can be enhanced with toast library
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
    
    // Auto-refresh every 5 minutes for real-time updates
    setInterval(function() {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 300000);
});

// Handle page visibility change
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Refresh data when page becomes visible
        applyFilters();
    }
});
</script>
@endpush