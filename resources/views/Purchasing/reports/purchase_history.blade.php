@extends('Purchasing.layout.app')

@section('title', 'Purchase History')

@section('content')
<div class="max-w-7xl mx-auto space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between bg-white border-b border-gray-200 px-6 py-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Purchase History</h1>
            <p class="text-sm text-gray-500">View all purchase orders and transactions</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('purchasing.dashboard') }}" 
               class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors">
                <i class="fas fa-arrow-left mr-1.5"></i>Dashboard
            </a>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mx-6">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-medium text-gray-900">Filters & Search</h3>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('purchasing.reports.history') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search PO Number/Supplier</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}"
                           placeholder="PO number or supplier name"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                </div>
                <div>
                    <label for="supplier_id" class="block text-xs font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" 
                            id="supplier_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           id="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           id="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-chocolate focus:border-transparent">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 text-sm text-white bg-chocolate hover:bg-chocolate-dark rounded-md transition-colors">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a href="{{ route('purchasing.reports.history') }}" 
                       class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Purchase Orders Table --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mx-6">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-900">Purchase Orders</h3>
                @if(($purchaseOrders ?? collect())->count() > 0)
                    <span class="text-xs text-gray-500">
                        {{ ($purchaseOrders ?? collect())->count() }} records found
                    </span>
                @endif
            </div>
        </div>
        
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'order_date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="hover:text-gray-700">
                                Date 
                                @if(request('sort') == 'order_date')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'po_number', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="hover:text-gray-700">
                                PO Number 
                                @if(request('sort') == 'po_number')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="hover:text-gray-700">
                                Status 
                                @if(request('sort') == 'status')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300"></i>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                            <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => 'grand_total', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                               class="hover:text-gray-700">
                                Amount 
                                @if(request('sort') == 'grand_total')
                                    <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300"></i>
                                @endif
                            </a>
                        </th>
                        <th class="w-20 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse(($purchaseOrders ?? collect()) as $order)
                        <tr class="hover:bg-gray-50">
                            
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">
                                    @if($order->order_date instanceof \Carbon\Carbon)
                                        {{ $order->order_date->format('M d, Y') }}
                                    @elseif($order->order_date)
                                        {{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </div>
                                @if($order->expected_delivery_date)
                                    <div class="text-xs text-gray-500">
                                        Expected: 
                                        @if($order->expected_delivery_date instanceof \Carbon\Carbon)
                                            {{ $order->expected_delivery_date->format('M d') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d') }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3">
                                <div class="text-sm font-mono font-medium text-gray-900">{{ $order->po_number ?? 'N/A' }}</div>
                                @if($order->created_at)
                                    <div class="text-xs text-gray-500">
                                        Created {{ $order->created_at instanceof \Carbon\Carbon ? $order->created_at->diffForHumans() : \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">
                                    {{ $order->supplier->name ?? 'Unknown Supplier' }}
                                </div>
                                @if($order->supplier->contact_person ?? false)
                                    <div class="text-xs text-gray-500">
                                        Contact: {{ $order->supplier->contact_person }}
                                    </div>
                                @endif
                                @if(($order->supplier->city ?? false) || ($order->supplier->province ?? false))
                                    <div class="text-xs text-gray-400">
                                        {{ trim(($order->supplier->city ?? '') . ', ' . ($order->supplier->province ?? ''), ', ') }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">
                                    {{ ($order->purchaseOrderItems ?? collect())->count() }} 
                                    {{ ($order->purchaseOrderItems ?? collect())->count() == 1 ? 'item' : 'items' }}
                                </div>
                                @php
                                    $orderItems = $order->purchaseOrderItems ?? collect();
                                                            $categories = $orderItems->filter(function($item) {
                                                                return $item->item && $item->item->category;
                                                            })->pluck('item.category.name')->unique();
                                                        @endphp
                                @if($categories->count() > 0)
                                    <div class="text-xs text-gray-500">
                                        Categories: {{ $categories->implode(', ') }}
                                    </div>
                                @endif
                                @php
                                    $totalQuantity = $orderItems->sum('quantity_ordered');
                                @endphp
                                <div class="text-xs text-gray-400">
                                    Total Qty: {{ number_format($totalQuantity, 3) }}
                                </div>
                            </td>
                            
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'sent') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'partial') bg-orange-100 text-orange-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status ?? 'draft') }}
                                </span>
                                @if($order->status === 'partial')
                                    <div class="text-xs text-gray-500 mt-1">
                                        @php
                                            $totalOrdered = $orderItems->sum('quantity_ordered');
                                            $totalReceived = $orderItems->sum('quantity_received');
                                            $completion = $totalOrdered > 0 ? round(($totalReceived / $totalOrdered) * 100, 1) : 0;
                                        @endphp
                                        {{ $completion }}% complete
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3 text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ₱{{ number_format($order->grand_total ?? 0, 2) }}
                                </div>
                                @if(($order->tax_amount ?? 0) > 0 || ($order->discount_amount ?? 0) > 0)
                                    <div class="text-xs text-gray-500">
                                        @if(($order->tax_amount ?? 0) > 0)
                                            Tax: ₱{{ number_format($order->tax_amount, 2) }}
                                        @endif
                                        @if(($order->discount_amount ?? 0) > 0) 
                                            Discount: -₱{{ number_format($order->discount_amount, 2) }}
                                        @endif
                                    </div>
                                @endif
                                @if(($order->total_amount ?? 0) > 0 && abs(($order->total_amount ?? 0) - ($order->grand_total ?? 0)) > 0.01)
                                    <div class="text-xs text-gray-400">
                                        Subtotal: ₱{{ number_format($order->total_amount, 2) }}
                                    </div>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button type="button" 
                                            onclick="viewPODetails({{ $order->id }})"
                                            class="text-chocolate hover:text-chocolate-dark text-sm"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('purchasing.po.print', $order->id) }}" 
                                       target="_blank"
                                       class="text-gray-400 hover:text-gray-600 text-sm"
                                       title="Print PO">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($order->status === 'draft' && auth()->user()?->hasAnyRole(['purchasing', 'admin']))
                                        <a href="{{ route('purchasing.po.edit', $order->id) }}" 
                                           class="text-blue-500 hover:text-blue-700 text-sm"
                                           title="Edit PO">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                <p class="text-sm">No purchase orders found</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    @if(request()->anyFilled(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                                        Try adjusting your filters or search criteria
                                    @else
                                        Purchase orders will appear here once created
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @php
            $purchaseOrderData = $purchaseOrders ?? collect();
            $isPaginator = method_exists($purchaseOrderData, 'hasPages');
        @endphp
        @if($isPaginator && $purchaseOrderData->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $purchaseOrderData->firstItem() ?? 0 }} to {{ $purchaseOrderData->lastItem() ?? 0 }} 
                        of {{ $purchaseOrderData->total() ?? 0 }} results
                    </div>
                    <div class="flex space-x-1">
                        {{ $purchaseOrderData->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Summary Statistics --}}
    @if(($purchaseOrders ?? collect())->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mx-6">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-medium text-gray-900">Summary Statistics</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $totalOrders = $purchaseOrders->count();
                        $totalValue = $purchaseOrders->sum('grand_total');
                        $completedOrders = $purchaseOrders->where('status', 'completed')->count();
                        $pendingOrders = $purchaseOrders->whereIn('status', ['draft', 'sent', 'confirmed'])->count();
                    @endphp
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</div>
                        <div class="text-xs text-gray-500">Total Orders</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">₱{{ number_format($totalValue, 2) }}</div>
                        <div class="text-xs text-gray-500">Total Value</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($completedOrders) }}</div>
                        <div class="text-xs text-gray-500">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ number_format($pendingOrders) }}</div>
                        <div class="text-xs text-gray-500">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- PO Details Modal --}}
<div id="po-details-modal" 
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-4 border w-11/12 md:w-3/4 shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
            <button onclick="closePODetailsModal()" 
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="po-details-content">
            <div class="text-center py-6">
                <i class="fas fa-spinner fa-spin text-xl text-chocolate mb-2"></i>
                <p class="text-gray-600">Loading purchase order details...</p>
            </div>
        </div>
        
        <div class="flex justify-end mt-6 pt-4 border-t">
            <button type="button" 
                    onclick="closePODetailsModal()"
                    class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class PODetailsModal {
    constructor() {
        this.modal = document.getElementById('po-details-modal');
        this.content = document.getElementById('po-details-content');
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.close();
            }
        });
    }

    open(poId) {
        this.modal.classList.remove('hidden');
        this.content.innerHTML = `
            <div class="text-center py-6">
                <i class="fas fa-spinner fa-spin text-xl text-chocolate mb-2"></i>
                <p class="text-gray-600">Loading purchase order details...</p>
            </div>
        `;
        
        // Try to fetch from API first, fallback to embedded data
        // Try to fetch from the show route, but handle both HTML and JSON
        fetch(`/purchasing/po/${poId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('API not available');
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If it's HTML, return empty object to trigger fallback
                    throw new Error('HTML response received');
                }
            })
            .then(data => this.displayPODetails(data.purchaseOrder || data))
            .catch(error => {
                // Fallback: try to find embedded data
                const embeddedData = document.querySelector(`[data-po-data="${poId}"]`);
                if (embeddedData) {
                    try {
                        const data = JSON.parse(embeddedData.textContent);
                        this.displayPODetails(data);
                    } catch (e) {
                        this.showError('Error parsing embedded data');
                    }
                } else {
                    this.showError('Error loading purchase order details. Please try refreshing the page.');
                }
            });
    }

    showError(message) {
        this.content.innerHTML = `
            <div class="text-center py-6 text-red-600">
                <i class="fas fa-exclamation-triangle text-xl mb-2"></i>
                <p>${message}</p>
            </div>
        `;
    }

    close() {
        this.modal.classList.add('hidden');
    }

    displayPODetails(poData) {
        const items = poData.purchase_order_items || poData.purchaseOrderItems || [];
        const itemsHtml = items.length > 0 ? items.map(item => `
            <tr>
                <td class="px-3 py-2">
                    <div class="text-sm font-medium">${item.item?.name || item.name || 'Unknown Item'}</div>
                    <div class="text-xs text-gray-500">${item.item?.item_code || item.item_code || 'N/A'}</div>
                    ${item.item?.category?.name ? `<div class="text-xs text-gray-400">${item.item.category.name}</div>` : ''}
                </td>
                <td class="px-3 py-2 text-sm">
                    ${parseFloat(item.quantity_ordered || item.quantity_ordered || 0).toFixed(3)}
                    ${item.item?.unit?.symbol ? `<span class="text-xs text-gray-500"> ${item.item.unit.symbol}</span>` : ''}
                </td>
                <td class="px-3 py-2 text-sm">
                    ${parseFloat(item.quantity_received || 0).toFixed(3)}
                </td>
                <td class="px-3 py-2 text-sm">₱${(parseFloat(item.unit_price || 0)).toFixed(2)}</td>
                <td class="px-3 py-2 text-sm font-medium">₱${((parseFloat(item.quantity_ordered || 0)) * (parseFloat(item.unit_price || 0))).toFixed(2)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">No items found</td></tr>';

        const statusBadge = this.getStatusBadge(poData.status);
        
        // Calculate totals
        const subtotal = parseFloat(poData.total_amount || 0);
        const tax = parseFloat(poData.tax_amount || 0);
        const discount = parseFloat(poData.discount_amount || 0);
        const grandTotal = parseFloat(poData.grand_total || 0);

        this.content.innerHTML = `
            <div class="space-y-6">
                <!-- PO Header Info -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-xs font-medium text-gray-500">PO Number</label>
                        <p class="font-medium">${poData.po_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Status</label>
                        <div>${statusBadge}</div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Order Date</label>
                        <p class="font-medium">${poData.order_date ? new Date(poData.order_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Expected Delivery</label>
                        <p class="font-medium">${poData.expected_delivery_date ? new Date(poData.expected_delivery_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    ${poData.actual_delivery_date ? `
                    <div>
                        <label class="text-xs font-medium text-gray-500">Actual Delivery</label>
                        <p class="font-medium">${new Date(poData.actual_delivery_date).toLocaleDateString()}</p>
                    </div>
                    ` : ''}
                    <div>
                        <label class="text-xs font-medium text-gray-500">Supplier</label>
                        <p class="font-medium">${poData.supplier?.name || 'N/A'}</p>
                        ${poData.supplier?.contact_person ? `<p class="text-xs text-gray-500">Contact: ${poData.supplier.contact_person}</p>` : ''}
                        ${poData.supplier?.phone ? `<p class="text-xs text-gray-500">Phone: ${poData.supplier.phone}</p>` : ''}
                    </div>
                    ${poData.payment_terms ? `
                    <div>
                        <label class="text-xs font-medium text-gray-500">Payment Terms</label>
                        <p class="font-medium">${poData.payment_terms} days</p>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Items Table -->
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-2">Items (${items.length})</label>
                    <div class="border rounded-md overflow-hidden">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Item</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Ordered</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Received</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Unit Price</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Summary -->
                <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Subtotal</p>
                        <p class="font-medium">₱${subtotal.toLocaleString()}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Tax</p>
                        <p class="font-medium">₱${tax.toLocaleString()}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Grand Total</p>
                        <p class="font-bold text-lg">₱${grandTotal.toLocaleString()}</p>
                    </div>
                </div>
                
                ${discount > 0 ? `
                <div class="text-right text-sm">
                    <span class="text-gray-500">Discount:</span>
                    <span class="font-medium text-red-600">-₱${discount.toLocaleString()}</span>
                </div>
                ` : ''}
                
                <!-- Notes -->
                ${poData.notes ? `
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Notes</label>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-md">${poData.notes}</p>
                </div>
                ` : ''}
                
                <!-- Approval Info -->
                ${poData.approved_by || poData.approved_at ? `
                <div class="text-xs text-gray-500 pt-2 border-t">
                    ${poData.approved_at ? `Approved on ${new Date(poData.approved_at).toLocaleString()}` : ''}
                    ${poData.approved_by && poData.approvedBy ? ` by ${poData.approvedBy.name}` : ''}
                </div>
                ` : ''}
            </div>
        `;
    }

    getStatusBadge(status) {
        const badges = {
            'draft': '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Draft</span>',
            'sent': '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Sent</span>',
            'confirmed': '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Confirmed</span>',
            'partial': '<span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">Partial</span>',
            'completed': '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Completed</span>',
            'cancelled': '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Cancelled</span>',
        };
        return badges[status] || badges['draft'];
    }
}

// Global functions
function viewPODetails(poId) { 
    if (typeof poDetailsModal !== 'undefined') {
        poDetailsModal.open(poId); 
    }
}
function closePODetailsModal() { 
    if (typeof poDetailsModal !== 'undefined') {
        poDetailsModal.close(); 
    }
}

// Initialize
let poDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    poDetailsModal = new PODetailsModal();
});


</script>
@endpush