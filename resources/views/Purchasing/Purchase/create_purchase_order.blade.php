@extends('Purchasing.layout.app')

@section('title', 'Approved Requisitions - WellKenz ERP')
@section('breadcrumb', 'Approved Requisitions')

@section('content')
    <div class="space-y-6">

        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Approved Requisitions</h1>
                    <p class="text-sm text-gray-500 mt-1">Ready for Purchase-Order creation</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="createPOFromSelected()"
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-medium rounded">
                        <i class="fas fa-plus mr-2"></i>Create PO from selected
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Approved</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $approvedReqsCount }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Pending PO</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $pendingPOCount }}</p>
            </div>
            <div class="bg-white border border-blue-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Converted Today</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $convertedTodayCount }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">This Week</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $approvedThisWeekCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Overdue Supplier Deliveries</h3>
                <div class="space-y-3">
                    @forelse($overdue as $po)
                        <div class="p-3 border-l-4 border-rose-500 bg-rose-50 rounded">
                            <p class="text-sm font-medium text-gray-900">PO-{{ $po->po_ref }} – ₱
                                {{ number_format($po->total_amount ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-600 mt-1">Expected:
                                {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">No overdue deliveries.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Low-Stock Alerts</h3>
                <div class="space-y-3">
                    @forelse($lowStockItems as $item)
                        <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded">
                            <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                            <p class="text-xs text-gray-600 mt-1">Stock: {{ $item->current_stock }} {{ $item->item_unit }} •
                                Re-order: {{ $item->reorder_level }} {{ $item->item_unit }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">No low-stock items – you're all set!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row items-start md:items-center justify-between space-y-3 md:space-y-0">
                <h3 class="text-lg font-semibold text-gray-900">Approved Requisitions</h3>
                <div class="flex items-center space-x-4 w-full md:w-auto">
                    <div class="relative">
                        <select id="priorityFilter" onchange="filterTable()"
                            class="pl-3 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option value="">All Priorities</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="relative w-full md:w-64">
                        <input type="text" id="searchInput" placeholder="Search requisitions…"
                            onkeyup="searchTable(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-full">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearSearch()" id="clearBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                                class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="reqTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                <input type="checkbox" id="masterCheck" onclick="toggleAll(this)">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('ref')">Req Ref <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('priority')">Priority <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Approved By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('approved_date')">Approved Date <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="reqTableBody">
                        @foreach ($approvedReqs as $req)
                            <tr class="hover:bg-gray-50 transition req-row" data-ref="{{ $req->req_ref }}"
                                data-priority="{{ $req->req_priority }}" data-approved_date="{{ $req->approved_date }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="row-check" value="{{ $req->req_id }}"
                                        onclick="updateMasterCheck()">
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">RQ-{{ $req->req_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ \Illuminate\Support\Str::limit($req->req_purpose, 40) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded
                                        @if ($req->req_priority == 'high') bg-rose-100 text-rose-700
                                        @elseif($req->req_priority == 'medium') bg-amber-100 text-amber-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($req->req_priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->approver->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $req->approved_date ? \Carbon\Carbon::parse($req->approved_date)->format('M d, Y') : '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openViewModal({{ $req->req_id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="View Details">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button onclick="redirectCreatePO({{ $req->req_id }})"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded transition"
                                            title="Create PO">
                                            <i class="fas fa-plus text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $approvedReqs->count() }}</span> of {{ $approvedReqs->total() }}
                requisitions
            </div>
        </div>

    </div>

    @include('Purchasing.Purchase.view')
@endsection

@push('scripts')
    <script>
        /* light helpers */
        let currentId = null;
        const totalReqCount = {{ $approvedReqs->total() }};

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['viewReqModal', 'createPOFromReqModal'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search & filter & sort */
        function applyFilters() {
            const Q = document.getElementById('searchInput').value.toLowerCase();
            const priority = document.getElementById('priorityFilter').value;
            const rows = document.querySelectorAll('.req-row');
            let visible = 0;

            rows.forEach(r => {
                const textMatch = r.textContent.toLowerCase().includes(Q);
                const priorityMatch = !priority || r.dataset.priority === priority;

                const ok = textMatch && priorityMatch;
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });

            document.getElementById('visibleCount').textContent = visible;
            const btn = document.getElementById('clearBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function searchTable(q) {
            applyFilters();
        }

        function filterTable() {
            applyFilters();
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            applyFilters();
            document.getElementById('clearBtn').classList.add('hidden');
        }

        let sortField = 'ref',
            sortDir = 'desc';

        function sortTable(f) {
            if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = f;
                sortDir = (f === 'ref' || f === 'approved_date') ? 'desc' : 'asc';
            }

            const tbody = document.getElementById('reqTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));

            rows.sort((a, b) => {
                let A, B;

                if (f === 'ref') {
                    A = a.dataset.ref;
                    B = b.dataset.ref;
                } else if (f === 'approved_date') {
                    A = new Date(a.dataset.approved_date);
                    B = new Date(b.dataset.approved_date);
                    return sortDir === 'asc' ? A - B : B - A;
                } else if (f === 'priority') {
                    const prioOrder = {
                        'high': 3,
                        'medium': 2,
                        'low': 1
                    };
                    const valA = prioOrder[a.dataset.priority] || 0;
                    const valB = prioOrder[b.dataset.priority] || 0;
                    return sortDir === 'asc' ? valA - valB : valB - valA;
                } else {
                    return 0;
                }

                return sortDir === 'asc' ? String(A).localeCompare(String(B)) : String(B).localeCompare(String(A));
            });

            rows.forEach(r => tbody.appendChild(r));

            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        /* checkbox helpers */
        function toggleAll(master) {
            document.querySelectorAll('.row-check').forEach(chk => chk.checked = master.checked);
        }

        function updateMasterCheck() {
            const all = document.querySelectorAll('.row-check');
            const checked = document.querySelectorAll('.row-check:checked');
            document.getElementById('masterCheck').checked = all.length === checked.length && all.length > 0;
        }
        
        /* --- PO Creation and Redirect Logic (UPDATED) --- */

        function createAndShowPO(ids) {
            if (!ids || ids.length === 0) {
                showMessage('No requisitions provided for PO creation.', 'error');
                return;
            }
            
            // UI Feedback
            const createBtn = document.querySelector('button[onclick="createPOFromSelected()"]');
            const actionBtns = document.querySelectorAll('button[title="Create PO"]');
            
            const originalText = createBtn ? createBtn.innerHTML : null;
            if (createBtn) createBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating PO...';
            actionBtns.forEach(btn => btn.disabled = true);
            
            // Call backend to create a draft PO from multiple requisitions
            fetch("{{ route('purchasing.purchase.from-reqs') }}", { 
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    req_ids: ids
                })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.po_id) {
                        showMessage('Purchase Order Created Successfully! Redirecting...', 'success');
                        // Redirect to the newly created PO's view page
                        window.location.href = `{{ url('purchasing/purchase/view') }}/${res.po_id}`; 
                    } else {
                        showMessage(res.message || 'Error creating Purchase Order. Check server logs.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showMessage('Network error during PO creation.', 'error');
                })
                .finally(() => {
                     // Re-enable buttons
                    if (createBtn) createBtn.innerHTML = originalText;
                    actionBtns.forEach(btn => btn.disabled = false);
                    closeModals();
                });
        }
        
        function redirectCreatePO(id) {
            // Converts single ID action button click to call the API function
            createAndShowPO([id]);
        }


        function createPOFromSelected() {
            const checked = document.querySelectorAll('.row-check:checked');
            if (!checked.length) {
                showMessage('No requisitions selected', 'error');
                return;
            }
            const ids = [...checked].map(chk => chk.value);
            createAndShowPO(ids);
        }

        /* modal opener for View Details */
        function openViewModal(id) {
            currentId = id;
            document.getElementById('viewReqModal').classList.remove('hidden');
            const body = document.getElementById('viewReqModalBody');
            body.innerHTML = '<p class="text-gray-500 text-center py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Loading details...</p>';

            fetch(`/purchasing/requisitions/${id}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    }) : 'N/A';
                    
                    body.innerHTML = `
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b pb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Requisition Reference</label>
                                    <p class="text-gray-900 font-semibold">RQ-${data.req_ref}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Requested By</label>
                                    <p class="text-gray-900">${data.requester ? data.requester.name : 'N/A'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Approved By</label>
                                    <p class="text-gray-900">${data.approver ? data.approver.name : 'N/A'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Approved Date</label>
                                    <p class="text-gray-900">${data.approved_date ? formatDate(data.approved_date) : 'N/A'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                    <p class="text-gray-900">${data.req_priority ? data.req_priority.charAt(0).toUpperCase() + data.req_priority.slice(1) : 'N/A'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <p class="text-gray-900">${data.req_status ? data.req_status.charAt(0).toUpperCase() + data.req_status.slice(1) : 'N/A'}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose/Remarks</label>
                                <p class="text-gray-900 bg-gray-50 p-3 rounded">${data.req_purpose || '—'}</p>
                            </div>
                            
                            ${data.req_remarks ? `
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Approval Remarks</label>
                                    <p class="text-gray-900 bg-green-50 p-3 rounded">${data.req_remarks}</p>
                                </div>
                            ` : ''}

                            <div>
                                <h4 class="block text-lg font-semibold text-gray-900 mb-3">Items Requested</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            ${data.items ? data.items.map(item => `
                                                <tr class="bg-white">
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">${item.item ? item.item.item_name : 'Unknown Item'}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">${item.item_unit}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">${item.req_item_quantity}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-600">${item.item?.current_stock ?? 'N/A'}</td>
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${
                                                            item.req_item_status === 'fulfilled' ? 'bg-green-100 text-green-700' :
                                                            item.req_item_status === 'partially_fulfilled' ? 'bg-yellow-100 text-yellow-700' :
                                                            'bg-gray-100 text-gray-700'
                                                        }">${item.req_item_status ? item.req_item_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Pending'}</span>
                                                    </td>
                                                </tr>
                                            `).join('') : '<tr class="text-center"><td colspan="5" class="py-4 text-gray-500">No items found.</td></tr>'}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;

                    // Updated footer button to call the API function
                    document.getElementById('viewReqModalFooter').innerHTML = `
                        <button onclick="createAndShowPO([${data.req_id}]);" type="button" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 transition text-sm font-medium rounded">
                            <i class="fas fa-plus mr-2"></i>Create Purchase Order
                        </button>
                        <button onclick="closeModals()" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 transition text-sm font-medium rounded ml-2">
                            Close
                        </button>
                    `;
                })
                .catch(() => {
                    body.innerHTML = '<p class="text-red-600 text-center py-8">Error loading requisition details.</p>';
                    document.getElementById('viewReqModalFooter').innerHTML = '<button onclick="closeModals()" type="button" class="px-4 py-2 bg-gray-200 text-gray-700 hover:bg-gray-300 transition text-sm font-medium rounded">Close</button>';
                });
        }
    </script>
@endpush