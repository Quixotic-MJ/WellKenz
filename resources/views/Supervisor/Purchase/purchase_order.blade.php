@extends('Supervisor.layout.app')

@section('title', 'Purchase Order Oversight - WellKenz ERP')
@section('breadcrumb', 'Purchase Order Oversight')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Purchase Order Oversight</h1>
                    <p class="text-sm text-gray-500 mt-1">Supervisor view – monitor all POs created from approved
                        requisitions</p>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span class="w-2 h-2 bg-amber-400 rounded-full"></span>
                    <span>Read-only with optional high-value approval</span>
                </div>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total POs</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalPOs }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Draft</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $draftCount }}</p>
            </div>
            <div class="bg-white border border-blue-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Ordered</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $orderedCount }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Delivered</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $deliveredCount }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Cancelled</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $cancelledCount }}</p>
            </div>
        </div>

        <!-- oversight table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Purchase Orders</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterStatus(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="ordered">Ordered</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search POs…" onkeyup="searchTable(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearSearch()" id="clearBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                                class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="poTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('ref')">PO Ref <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Linked Req</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total (₱)</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="poTableBody">
                        @foreach ($purchaseOrders as $po)
                            <tr class="hover:bg-gray-50 transition po-row" data-ref="{{ $po->po_ref }}"
                                data-status="{{ $po->po_status }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $po->po_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $po->supplier->sup_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if ($po->requisition)
                                        {{ $po->requisition->req_ref }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">₱ {{ number_format($po->total_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if ($po->po_status == 'draft') bg-gray-100 text-gray-700
                                @elseif($po->po_status == 'ordered') bg-blue-100 text-blue-700
                                @elseif($po->po_status == 'delivered') bg-green-100 text-green-700
                                @elseif($po->po_status == 'cancelled') bg-rose-100 text-rose-700
                                @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($po->po_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button data-po-id="{{ $po->po_id }}" data-action="view"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="View Details">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        @if ($po->po_status == 'ordered' && $po->total_amount > 50000)
                                            <button data-po-id="{{ $po->po_id }}" data-action="approve"
                                                class="p-2 text-amber-600 hover:bg-amber-50 rounded transition"
                                                title="High-value approval">
                                                <i class="fas fa-stamp text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $purchaseOrders->count() }}</span> of {{ $purchaseOrders->count() }}
                purchase orders
            </div>
        </div>

    </div>

    <!-- ====== MODALS  ====== -->
    @include('Supervisor.Purchase.view')
    @include('Supervisor.Purchase.approve')

@endsection

@push('scripts')
    <script>
        /* light helpers */
        let currentId = null;

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['viewPOModal', 'approvePOModal'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function searchTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.po-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.textContent.toLowerCase().includes(Q);
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
            const btn = document.getElementById('clearBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchTable('');
            document.getElementById('clearBtn').classList.add('hidden');
        }

        function filterStatus(val) {
            const rows = document.querySelectorAll('.po-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = val === 'all' || r.dataset.status === val;
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
        }

        /* sort */
        let sortField = 'ref',
            sortDir = 'desc';

        function sortTable(f) {
            if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = f;
                sortDir = 'asc';
            }
            const tbody = document.getElementById('poTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            rows.sort((a, b) => {
                const A = a.dataset[f],
                    B = b.dataset[f];
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        /* modal openers */
        function openViewModal(id) {
            currentId = id;
            fetch(`/supervisor/purchase-orders/${id}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const body = document.getElementById('viewPOBody');
                body.innerHTML = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PO Reference</label>
                                <p class="text-gray-900 font-semibold">${data.po_ref}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                    ${data.po_status === 'ordered' ? 'bg-blue-100 text-blue-700' :
                                      data.po_status === 'delivered' ? 'bg-green-100 text-green-700' :
                                      data.po_status === 'cancelled' ? 'bg-rose-100 text-rose-700' :
                                      'bg-gray-100 text-gray-700'}">${data.po_status.charAt(0).toUpperCase() + data.po_status.slice(1)}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                                <p class="text-gray-900">${data.supplier || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Linked Requisition</label>
                                <p class="text-gray-900">${data.requisition || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order Date</label>
                                <p class="text-gray-900">${data.order_date || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Delivery</label>
                                <p class="text-gray-900">${data.expected_delivery_date || 'N/A'}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded">${data.delivery_address || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${data.items.map(item => `
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-gray-900">${item.item_name}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">${item.quantity} ${item.unit}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">₱${item.unit_price.toFixed(2)}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">₱${item.subtotal.toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">Total:</td>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">₱${data.total_amount.toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('viewPOModal').classList.remove('hidden');
            })
            .catch(() => showMessage('Error loading purchase order details', 'error'));
        }

        function openApproveModal(id) {
            currentId = id;
            /* ajax fetch then fill modal */
            document.getElementById('approvePOModal').classList.remove('hidden');
        }

        // Delegate clicks for action buttons
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-po-id]');
            if (!btn) return;
            const id = btn.dataset.poId;
            const action = btn.dataset.action;
            if (action === 'view') openViewModal(id);
            else if (action === 'approve') openApproveModal(id);
        });
    </script>
@endpush
