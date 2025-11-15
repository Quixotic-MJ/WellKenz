@extends('Purchasing.layout.app')

@section('title', 'Delivery Recording - WellKenz ERP')
@section('breadcrumb', 'Delivery Recording')

@section('content')
    <div class="space-y-6">

        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Delivery Recording</h1>
                    <p class="text-sm text-gray-500 mt-1">Verify & log supplier deliveries via memo</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Ordered POs</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ DB::table('purchase_orders')->where('po_status', 'ordered')->count() }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Delivered Today</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ DB::table('purchase_orders')->where('po_status', 'delivered')->whereDate('updated_at', today())->count() }}
                </p>
            </div>
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Overdue</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ DB::table('purchase_orders')->where('po_status', 'ordered')->whereDate('expected_delivery_date', '<', today())->count() }}
                </p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Awaiting Stock-In</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ DB::table('purchase_orders')->where('po_status', 'delivered')->count() }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Ordered Purchase Orders</h3>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search POs…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn"
                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                            class="fas fa-times text-xs"></i></button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="poTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('ref')">PO Ref <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Delivery Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="poTableBody">
                        @foreach ($orderedPOs as $po)
                            <tr class="hover:bg-gray-50 transition po-row" data-ref="{{ strtolower($po->po_ref) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">PO-{{ $po->po_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $po->sup_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $po->items_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700">
                                        Ordered
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="openDeliveryModal({{ $po->po_id }})"
                                        class="px-3 py-1.5 bg-green-600 text-white hover:bg-green-700 transition text-sm font-medium rounded">
                                        <i class="fas fa-truck mr-1"></i>Record Delivery
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $orderedPOs->count() }}</span> of {{ $orderedPOs->total() }} purchase
                orders
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Delivery Memos</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="memoTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Memo Ref</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PO Ref</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Recorded On</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="memoTableBody">

                        {{-- This assumes you pass $recentMemos from your controller --}}
                        @forelse($recentMemos as $memo)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $memo->memo_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">PO-{{ $memo->po_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $memo->sup_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($memo->created_at)->format('M d, Y H:i A') }}</td>
                                <td class="px-6 py-4">
                                    {{-- This status aligns with your "Awaiting Stock-In" card logic --}}
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                        Awaiting Stock-In
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('purchasing.memo.show', $memo->memo_ref) }}"
                                        class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-medium rounded">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fas fa-file-alt text-2xl mb-2"></i>
                                    <p>No delivery memos have been recorded yet.</p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Footer (optional) --}}
            @if (isset($recentMemos) && $recentMemos->count() > 0)
                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                    Showing {{ $recentMemos->count() }} most recent memos
                </div>
            @endif
        </div>

    </div>

    @include('Purchasing.Memo.record')

@endsection

@push('scripts')
    <script>
        /* light helpers */
        let currentPOId = null;

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['recordDeliveryModal'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
            currentPOId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search */
        function searchTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.po-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.dataset.ref.includes(Q);
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

        /* sort */
        let sortField = 'ref',
            sortDir = 'asc';

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

        /* open delivery modal */
        function openDeliveryModal(poId) {
            currentPOId = poId;
            const modal = document.getElementById('recordDeliveryModal');
            const body = document.getElementById('deliveryBody');
            const itemsBody = document.getElementById('itemsBody');
            // reset
            itemsBody.innerHTML = '<tr><td colspan="4" class="px-3 py-2 text-gray-500">Loading…</td></tr>';
            document.getElementById('poIdInput').value = poId;
            // fetch ordered PO + items
            fetch(`/purchasing/delivery/${poId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) throw new Error(res.message || 'Error');
                    const po = res.po;
                    const items = res.items || [];
                    // Auto-generate memo ref: DM-YYYYMMDD-<po_ref>
                    const d = new Date();
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    const memoRef = `DM-${y}${m}${day}-${po.po_ref}`;
                    const form = document.getElementById('deliveryForm');
                    if (form.memo_ref) form.memo_ref.value = memoRef;
                    // build rows
                    if (!items.length) {
                        itemsBody.innerHTML =
                            '<tr><td colspan="4" class="px-3 py-2 text-gray-500">No items found on this PO.</td></tr>';
                    } else {
                        itemsBody.innerHTML = items.map((it, idx) => {
                            const ord = Number(it.ordered_qty || 0);
                            return `<tr class=\"border-t\">` +
                                `<td class=\"px-3 py-2\">${it.item_name||'-'}<input type=\"hidden\" name=\"items[${idx}][item_id]\" value=\"${it.item_id}\"/></td>` +
                                `<td class=\"px-3 py-2 text-gray-600\">${it.unit||'-'}</td>` +
                                `<td class=\"px-3 py-2 text-right text-gray-900\">${ord}</td>` +
                                `<td class=\"px-3 py-2 text-right\"><input type=\"number\" step=\"0.01\" min=\"0\" max=\"${ord}\" name=\"items[${idx}][delivered_qty]\" value=\"${ord}\" class=\"w-28 border border-gray-300 rounded px-2 py-1 text-right\" /></td>` +
                                `</tr>`;
                        }).join('');
                    }
                    modal.classList.remove('hidden');
                })
                .catch(() => {
                    itemsBody.innerHTML =
                        '<tr><td colspan="4" class="px-3 py-2 text-rose-600">Failed to load PO items.</td></tr>';
                    modal.classList.remove('hidden');
                });
        }
    </script>
@endpush
