@extends('Supervisor.layout.app')

@section('title', 'Custom Item Requests - WellKenz ERP')
@section('breadcrumb', 'Custom Item Requests')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Custom Item Requests</h1>
                    <p class="text-sm text-gray-500 mt-1">Handle employee requests for new items not yet in inventory</p>
                </div>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $approvedCount }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Rejected</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $rejectedCount }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">This Month</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $thisMonthCount }}
                </p>
            </div>
        </div>

        <!-- pending requests table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pending Requests</h3>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search requests…"
                            onkeyup="searchTable(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearSearch()" id="clearBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                                class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="requestsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('name')">Item Name <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="requestsTableBody">
                        @forelse($pendingList as $req)
                            <tr class="hover:bg-gray-50 transition req-row"
                                data-name="{{ strtolower($req->item_req_name) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->item_req_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_unit }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openViewModal({{ $req->item_req_id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="View details">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button
                                            data-item-name="{{ e($req->item_req_name) }}"
                                            onclick="openApproveModal({{ $req->item_req_id }}, this.dataset.itemName)"
                                            class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 transition text-xs font-semibold rounded">
                                            Approve
                                        </button>
                                        <button
                                            data-item-name="{{ e($req->item_req_name) }}"
                                            onclick="openRejectModal({{ $req->item_req_id }}, this.dataset.itemName)"
                                            class="px-3 py-1 bg-rose-600 text-white hover:bg-rose-700 transition text-xs font-semibold rounded">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No pending custom item
                                    requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $pendingList->count() }}</span> of {{ $pendingList->count() }} pending
                requests
            </div>
        </div>

        <!-- past decisions -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Past Decisions</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterStatus(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="relative">
                        <input type="text" id="searchPast" placeholder="Search past decisions…"
                            onkeyup="searchPastTable(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearPastSearch()" id="clearPastBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                                class="fas fa-times text-xs"></i></button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="pastTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortPastTable('name')">Item Name <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="pastTableBody">
                        @forelse($pastList as $req)
                            <tr class="hover:bg-gray-50 transition past-row"
                                data-name="{{ strtolower($req->item_req_name) }}"
                                data-status="{{ strtolower($req->item_req_status) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->item_req_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_unit }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if ($req->item_req_status === 'approved') bg-green-100 text-green-700
                                @else bg-rose-100 text-rose-700 @endif">
                                        {{ ucfirst($req->item_req_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->updated_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <button onclick="openViewModal({{ $req->item_req_id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No past decisions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visiblePastCount">{{ $pastList->count() }}</span> of {{ $pastList->count() }} past
                decisions
            </div>
        </div>

        <!-- ====== MODALS  ====== -->
        @include('Supervisor.Item_Request.view')
        @include('Supervisor.Item_Request.approve')
        @include('Supervisor.Item_Request.reject')

    </div>

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
            ['viewItemRequestModal', 'approveItemRequestModal', 'rejectItemRequestModal'].forEach(id => document
                .getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function filterStatus(val) {
            const rows = document.querySelectorAll('.past-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = val === 'all' || r.dataset.status === val;
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visiblePastCount').textContent = visible;
        }

        function searchTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.req-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.dataset.name.includes(Q) || r.textContent.toLowerCase().includes(Q);
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
            const btn = document.getElementById('clearBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function searchPastTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.past-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.dataset.name.includes(Q) || r.textContent.toLowerCase().includes(Q);
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visiblePastCount').textContent = visible;
            const btn = document.getElementById('clearPastBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchTable('');
            document.getElementById('clearBtn').classList.add('hidden');
        }

        function clearPastSearch() {
            document.getElementById('searchPast').value = '';
            searchPastTable('');
            document.getElementById('clearPastBtn').classList.add('hidden');
        }

        /* sort */
        let sortField = 'name',
            sortDir = 'asc';

        function sortTable(f) {
            if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = f;
                sortDir = 'asc';
            }
            const tbody = document.getElementById('requestsTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            rows.sort((a, b) => {
                const A = a.dataset[f].toLowerCase(),
                    B = b.dataset[f].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        function sortPastTable(f) {
            if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = f;
                sortDir = 'asc';
            }
            const tbody = document.getElementById('pastTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            rows.sort((a, b) => {
                const A = a.dataset[f].toLowerCase(),
                    B = b.dataset[f].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortPastTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

    /* modal openers */
    function openViewModal(id){
        currentId=id;
        fetch(`/supervisor/item-requests/${id}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r=>{ if(!r.ok) throw new Error('Network response was not ok'); return r.json(); })
            .then(data=>{
                const body = document.getElementById('viewItemRequestBody');
                if (!body) { document.getElementById('viewItemRequestModal').classList.remove('hidden'); return; }
                body.innerHTML = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                                <p class="text-gray-900 font-semibold">${data.item_req_name}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Requested By</label>
                                <p class="text-gray-900">${data.requester ? data.requester.name : 'N/A'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                                <p class="text-gray-900">${data.item_req_unit}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <p class="text-gray-900">${data.item_req_quantity}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded">${data.item_req_description || '—'}</p>
                        </div>
                        ${data.item_req_status === 'rejected' && data.item_req_reject_reason ? `
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                                Rejection Reason: ${data.item_req_reject_reason}
                            </div>
                        ` : ''}
                    </div>`;
                document.getElementById('viewItemRequestModal').classList.remove('hidden');
            })
            .catch(()=>{ showMessage('Error loading item request', 'error'); });
    }
    function openApproveModal(id,name){
        currentId=id;
        const el = document.getElementById('approveItemRequestName'); if (el) el.textContent=name;
        const form = document.querySelector('#approveItemRequestModal form');
        if (form) form.action = `/supervisor/item-requests/${id}/status`;
        document.getElementById('approveItemRequestModal').classList.remove('hidden');
    }
    function openRejectModal(id,name){
        currentId=id;
        const el = document.getElementById('rejectItemRequestName'); if (el) el.textContent=name;
        const form = document.querySelector('#rejectItemRequestModal form');
        if (form) form.action = `/supervisor/item-requests/${id}/status`;
        document.getElementById('rejectItemRequestModal').classList.remove('hidden');
    }

    /* form submissions */
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#approveItemRequestModal form, #rejectItemRequestModal form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeModals();
                        // Reload the page to update the tables
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(() => showMessage('Error processing request', 'error'));
            });
        });
    });
</script>
@endsection
