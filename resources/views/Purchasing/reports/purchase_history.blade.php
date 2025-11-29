@extends('Purchasing.layout.app')

@section('title', 'Purchase History')

@section('content')
<div class="space-y-6 font-sans text-gray-600">
    
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate">Purchase History</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and track all purchase orders and transactions.</p>
        </div>
        <div>
            <a href="{{ route('purchasing.dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-border-soft rounded-lg text-sm font-medium text-chocolate shadow-sm hover:bg-gray-50 transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Summary Statistics (Moved to top for better UX) --}}
    @if(($purchaseOrders ?? collect())->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $totalOrders = $purchaseOrders->count();
                $totalValue = $purchaseOrders->sum('grand_total');
                $completedOrders = $purchaseOrders->where('status', 'completed')->count();
                $pendingOrders = $purchaseOrders->whereIn('status', ['sent', 'confirmed'])->count();
            @endphp

            {{-- Card 1: Total Orders --}}
            <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Orders</p>
                    <p class="font-display text-2xl font-bold text-chocolate mt-1">{{ number_format($totalOrders) }}</p>
                </div>
                <div class="p-3 bg-cream-bg rounded-lg text-chocolate">
                    <i class="fas fa-file-invoice text-xl"></i>
                </div>
            </div>

            {{-- Card 2: Total Value --}}
            <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Value</p>
                    <p class="font-display text-2xl font-bold text-green-600 mt-1">₱{{ number_format($totalValue, 2) }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg text-green-600">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>

            {{-- Card 3: Completed --}}
            <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="font-display text-2xl font-bold text-blue-600 mt-1">{{ number_format($completedOrders) }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>

            {{-- Card 4: Pending --}}
            <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pending Action</p>
                    <p class="font-display text-2xl font-bold text-caramel mt-1">{{ number_format($pendingOrders) }}</p>
                </div>
                <div class="p-3 bg-orange-50 rounded-lg text-caramel">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-border-soft overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-gray-50/50">
            <h3 class="font-display text-lg font-semibold text-chocolate flex items-center">
                <i class="fas fa-filter mr-2 text-caramel"></i> Filter & Search
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('purchasing.reports.history') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Search Input --}}
                <div class="space-y-1">
                    <label for="search" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ request('search') }}"
                           placeholder="PO number or supplier..."
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel transition-all">
                </div>

                {{-- Supplier Select --}}
                <div class="space-y-1">
                    <label for="supplier_id" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Supplier</label>
                    <select name="supplier_id" 
                            id="supplier_id"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel transition-all">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Select --}}
                <div class="space-y-1">
                    <label for="status" class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Status</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel transition-all">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-chocolate uppercase tracking-wide">Date Range</label>
                    <div class="flex items-center space-x-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <span class="text-gray-400">-</span>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel">
                    </div>
                </div>

                {{-- Action Buttons (Full width on mobile, auto on desktop) --}}
                <div class="md:col-span-2 lg:col-span-4 flex justify-end items-center space-x-3 pt-2">
                    <a href="{{ route('purchasing.reports.history') }}" 
                       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-chocolate transition-colors shadow-sm">
                        Reset Filters
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-medium text-white bg-chocolate rounded-lg shadow-md hover:bg-[#2c1d10] transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-border-soft overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft flex justify-between items-center bg-gray-50/50">
            <h3 class="font-display text-lg font-semibold text-chocolate">Order List</h3>
            @if(($purchaseOrders ?? collect())->count() > 0)
                <span class="px-3 py-1 bg-cream-bg text-chocolate text-xs rounded-full border border-border-soft font-medium">
                    {{ ($purchaseOrders ?? collect())->count() }} records
                </span>
            @endif
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-cream-bg text-left border-b border-border-soft">
                        @php
                            $headers = [
                                'order_date' => 'Date',
                                'po_number' => 'PO Number',
                                'supplier' => 'Supplier',
                                'items' => 'Items',
                                'status' => 'Status',
                                'grand_total' => 'Amount',
                            ];
                        @endphp

                        @foreach($headers as $key => $label)
                            <th class="px-6 py-4 text-xs font-bold text-chocolate uppercase tracking-wider">
                                @if(in_array($key, ['order_date', 'po_number', 'status', 'grand_total']))
                                    <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->query(), ['sort' => $key, 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="group inline-flex items-center hover:text-caramel transition-colors">
                                        {{ $label }}
                                        <span class="ml-1 text-gray-400 group-hover:text-caramel">
                                            @if(request('sort') == $key)
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-xs opacity-50"></i>
                                            @endif
                                        </span>
                                    </a>
                                @else
                                    {{ $label }}
                                @endif
                            </th>
                        @endforeach
                        <th class="px-6 py-4 text-center text-xs font-bold text-chocolate uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    @forelse(($purchaseOrders ?? collect()) as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            
                            {{-- Date --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($order->order_date instanceof \Carbon\Carbon)
                                        {{ $order->order_date->format('M d, Y') }}
                                    @elseif($order->order_date)
                                        {{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </div>
                                @if($order->expected_delivery_date)
                                    <div class="text-xs text-caramel mt-0.5">
                                        Exp: {{ $order->expected_delivery_date instanceof \Carbon\Carbon ? $order->expected_delivery_date->format('M d') : \Carbon\Carbon::parse($order->expected_delivery_date)->format('M d') }}
                                    </div>
                                @endif
                            </td>

                            {{-- PO Number --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-mono font-bold text-chocolate bg-cream-bg px-2 py-1 rounded w-fit">
                                    {{ $order->po_number ?? 'N/A' }}
                                </div>
                                @if($order->created_at)
                                    <div class="text-xs text-gray-400 mt-1 pl-1">
                                        {{ $order->created_at instanceof \Carbon\Carbon ? $order->created_at->diffForHumans() : \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}
                                    </div>
                                @endif
                            </td>

                            {{-- Supplier --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $order->supplier->name ?? 'Unknown' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $order->supplier->contact_person ?? '' }}
                                    @if(($order->supplier->city ?? false) || ($order->supplier->province ?? false))
                                        <span class="text-gray-400 mx-1">•</span>
                                        {{ trim(($order->supplier->city ?? '') . ', ' . ($order->supplier->province ?? ''), ', ') }}
                                    @endif
                                </div>
                            </td>

                            {{-- Items --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ ($order->purchaseOrderItems ?? collect())->count() }}
                                    </span>
                                    <span class="text-xs text-gray-500">entries</span>
                                </div>
                                @php
                                    $orderItems = $order->purchaseOrderItems ?? collect();
                                    $totalQuantity = $orderItems->sum('quantity_ordered');
                                @endphp
                                <div class="text-xs text-gray-400 mt-0.5">
                                    Qty: {{ number_format($totalQuantity, 3) }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = [
                                        'completed' => 'bg-green-100 text-green-800 border-green-200',
                                        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'sent' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'partial' => 'bg-orange-100 text-orange-800 border-orange-200',
                                        'cancelled' => 'bg-red-100 text-red-800 border-red-200'
                                    ];
                                    $currentClass = $statusClasses[$order->status] ?? $statusClasses['sent'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $currentClass }}">
                                    {{ ucfirst($order->status ?? 'sent') }}
                                </span>
                                
                                @if($order->status === 'partial')
                                    @php
                                        $totalOrdered = $orderItems->sum('quantity_ordered');
                                        $totalReceived = $orderItems->sum('quantity_received');
                                        $completion = $totalOrdered > 0 ? round(($totalReceived / $totalOrdered) * 100, 1) : 0;
                                    @endphp
                                    <div class="w-24 mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-orange-400" style="width: {{ $completion }}%"></div>
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-0.5 text-center w-24">{{ $completion }}%</div>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-chocolate">
                                    ₱{{ number_format($order->grand_total ?? 0, 2) }}
                                </div>
                                @if(($order->tax_amount ?? 0) > 0 || ($order->discount_amount ?? 0) > 0)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        @if(($order->tax_amount ?? 0) > 0) Tax: ₱{{ number_format($order->tax_amount, 2) }} @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center space-x-3">
                                    <button type="button" 
                                            onclick="viewPODetails({{ $order->id }})"
                                            class="p-1.5 text-chocolate hover:text-white hover:bg-chocolate rounded-md transition-colors"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <a href="{{ route('purchasing.po.print', $order->id) }}" 
                                       target="_blank"
                                       class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-600 rounded-md transition-colors"
                                       title="Print PO">
                                        <i class="fas fa-print"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-search text-2xl text-border-soft"></i>
                                </div>
                                <h3 class="text-lg font-medium text-chocolate">No orders found</h3>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your search filters or create a new order.</p>
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
            <div class="px-6 py-4 border-t border-border-soft bg-gray-50/50">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $purchaseOrderData->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $purchaseOrderData->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $purchaseOrderData->total() ?? 0 }}</span> results
                    </div>
                    <div class="w-full md:w-auto">
                        {{ $purchaseOrderData->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- PO Details Modal --}}
<div id="po-details-modal" 
     class="fixed inset-0 bg-chocolate/30 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity duration-300">
    <div class="relative top-10 mx-auto w-11/12 md:w-3/4 lg:w-2/3 shadow-2xl rounded-xl bg-white border border-border-soft overflow-hidden transform transition-all">
        
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 bg-chocolate text-white">
            <h3 class="font-display text-xl font-medium">Purchase Order Details</h3>
            <button onclick="closePODetailsModal()" class="text-white/70 hover:text-white transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        {{-- Modal Content --}}
        <div id="po-details-content" class="p-6 max-h-[80vh] overflow-y-auto bg-cream-bg/30">
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-3xl text-chocolate mb-4"></i>
                <p class="font-display text-chocolate">Retrieving order information...</p>
            </div>
        </div>
        
        {{-- Modal Footer --}}
        <div class="flex justify-end px-6 py-4 bg-gray-50 border-t border-gray-100">
            <button type="button" 
                    onclick="closePODetailsModal()"
                    class="px-5 py-2 text-sm text-gray-700 font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition-all">
                Close View
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
            <div class="text-center py-12">
                <i class="fas fa-circle-notch fa-spin text-3xl text-chocolate mb-4"></i>
                <p class="font-display text-chocolate text-lg">Loading details...</p>
            </div>
        `;
        
        fetch(`/purchasing/po/${poId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('API not available');
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                throw new Error('HTML response received');
            }
        })
        .then(data => this.displayPODetails(data.purchaseOrder || data))
        .catch(error => {
            const embeddedData = document.querySelector(`[data-po-data="${poId}"]`);
            if (embeddedData) {
                try {
                    const data = JSON.parse(embeddedData.textContent);
                    this.displayPODetails(data);
                } catch (e) {
                    this.showError('Error parsing local data');
                }
            } else {
                this.showError('Unable to load purchase order details.');
            }
        });
    }

    showError(message) {
        this.content.innerHTML = `
            <div class="text-center py-8 text-red-600 bg-red-50 rounded-lg border border-red-100">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
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
            <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                <td class="px-4 py-3">
                    <div class="text-sm font-bold text-chocolate">${item.item?.name || item.name || 'Unknown Item'}</div>
                    <div class="text-xs text-gray-500 font-mono">${item.item?.item_code || item.item_code || 'N/A'}</div>
                    ${item.item?.category?.name ? `<span class="inline-block mt-1 px-2 py-0.5 text-[10px] bg-cream-bg text-chocolate rounded-full">${item.item.category.name}</span>` : ''}
                </td>
                <td class="px-4 py-3 text-sm text-center">
                    ${parseFloat(item.quantity_ordered || 0).toFixed(3)}
                    ${item.item?.unit?.symbol ? `<span class="text-xs text-gray-500 ml-1">${item.item.unit.symbol}</span>` : ''}
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-600">
                    ${parseFloat(item.quantity_received || 0).toFixed(3)}
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">₱${(parseFloat(item.unit_price || 0)).toFixed(2)}</td>
                <td class="px-4 py-3 text-sm text-right font-medium text-chocolate">₱${((parseFloat(item.quantity_ordered || 0)) * (parseFloat(item.unit_price || 0))).toFixed(2)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">No items found for this order</td></tr>';

        const statusBadge = this.getStatusBadge(poData.status);
        
        // Calculate totals
        const subtotal = parseFloat(poData.total_amount || 0);
        const tax = parseFloat(poData.tax_amount || 0);
        const discount = parseFloat(poData.discount_amount || 0);
        const grandTotal = parseFloat(poData.grand_total || 0);

        this.content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-white p-4 rounded-lg border border-border-soft shadow-sm grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">PO Number</label>
                        <p class="font-mono font-bold text-lg text-chocolate">${poData.po_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Status</label>
                        <div class="mt-1">${statusBadge}</div>
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Order Date</label>
                        <p class="font-medium text-gray-800">${poData.order_date ? new Date(poData.order_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Expected Delivery</label>
                        <p class="font-medium text-caramel">${poData.expected_delivery_date ? new Date(poData.expected_delivery_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    
                    <div class="col-span-2 md:col-span-2 border-t border-gray-100 pt-3 mt-1">
                        <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Supplier</label>
                        <p class="font-bold text-gray-800 text-base">${poData.supplier?.name || 'N/A'}</p>
                        ${poData.supplier?.contact_person ? `<p class="text-xs text-gray-500 mt-1"><i class="fas fa-user mr-1"></i> ${poData.supplier.contact_person}</p>` : ''}
                    </div>
                    <div class="col-span-2 md:col-span-2 border-t border-gray-100 pt-3 mt-1">
                         ${poData.payment_terms ? `
                            <label class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Payment Terms</label>
                            <p class="font-medium text-gray-800">${poData.payment_terms} days</p>
                        ` : ''}
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-display text-chocolate font-bold">Ordered Items</h4>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">${items.length} items</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Item Details</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Ordered</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Rcvd</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <div class="w-full md:w-1/3 bg-white p-4 rounded-lg border border-border-soft shadow-sm space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span>₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tax</span>
                            <span>₱${tax.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        </div>
                        ${discount > 0 ? `
                        <div class="flex justify-between text-sm text-red-500">
                            <span>Discount</span>
                            <span>-₱${discount.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        </div>` : ''}
                        <div class="border-t border-dashed border-gray-300 pt-2 flex justify-between items-center">
                            <span class="font-bold text-gray-800 uppercase text-xs tracking-wide">Grand Total</span>
                            <span class="font-display font-bold text-xl text-chocolate">₱${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                        </div>
                    </div>
                </div>
                
                ${poData.notes ? `
                <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-lg">
                    <label class="text-[10px] uppercase font-bold text-yellow-600 tracking-wider flex items-center mb-1">
                        <i class="fas fa-sticky-note mr-1"></i> Notes
                    </label>
                    <p class="text-sm text-gray-700 italic">"${poData.notes}"</p>
                </div>
                ` : ''}
            </div>
        `;
    }

    getStatusBadge(status) {
        const badges = {
            'sent': '<span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-medium rounded border border-blue-100 uppercase tracking-wide">Sent</span>',
            'confirmed': '<span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs font-medium rounded border border-indigo-100 uppercase tracking-wide">Confirmed</span>',
            'partial': '<span class="px-2 py-0.5 bg-orange-50 text-orange-600 text-xs font-medium rounded border border-orange-100 uppercase tracking-wide">Partial</span>',
            'completed': '<span class="px-2 py-0.5 bg-green-50 text-green-600 text-xs font-medium rounded border border-green-100 uppercase tracking-wide">Completed</span>',
            'cancelled': '<span class="px-2 py-0.5 bg-red-50 text-red-600 text-xs font-medium rounded border border-red-100 uppercase tracking-wide">Cancelled</span>',
        };
        return badges[status] || badges['sent'];
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