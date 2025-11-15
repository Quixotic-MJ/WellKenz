@extends('Admin.layout.app')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Item Requests</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage non-listed item requests submitted by employees</p>
                </div>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Requests</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalRequests ?? 0 }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $pendingRequests ?? 0 }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $approvedRequests ?? 0 }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Rejected</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $rejectedRequests ?? 0 }}</p>
            </div>
        </div>

        <!-- requests table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Requests</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterTable(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
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
                                onclick="sortTable('name')">Requested Item <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reviewed By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="requestsTableBody">
                        @foreach ($requests as $req)
                            <tr class="hover:bg-gray-50 transition request-row"
                                data-name="{{ strtolower($req->item_req_name) }}"
                                data-status="{{ strtolower($req->item_req_status) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->item_req_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->approver->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($req->item_req_status === 'pending')
                                        <span
                                            class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Pending</span>
                                    @elseif($req->item_req_status === 'approved')
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Approved</span>
                                    @else
                                        <span
                                            class="inline-block px-2 py-1 bg-rose-100 text-rose-700 text-xs font-semibold rounded">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $req->item_req_reject_reason ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        @if ($req->item_req_status === 'pending')
                                            <button
                                                data-id="{{ $req->item_req_id }}"
                                                data-name="{{ e($req->item_req_name) }}"
                                                onclick="openApproveFromBtn(this)"
                                                class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 transition text-xs font-semibold rounded">
                                                Approve
                                            </button>
                                            <button
                                                data-id="{{ $req->item_req_id }}"
                                                data-name="{{ e($req->item_req_name) }}"
                                                onclick="openRejectFromBtn(this)"
                                                class="px-3 py-1 bg-rose-600 text-white hover:bg-rose-700 transition text-xs font-semibold rounded">
                                                Reject
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-500">Finalised</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $requests->count() }}</span> of {{ $requests->count() }} requests
            </div>
        </div>

        <!-- ====== MODALS  ====== -->
        @include('Admin.Requisition.item_request.approve')
        @include('Admin.Requisition.item_request.reject')

    </div>

    <script>
        /* light helpers */
        let currentId = null;
        const ITEM_REQ_BASE = "{{ url('/item-requests') }}";

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['approveRequestModal', 'rejectRequestModal'].forEach(id => document.getElementById(id)?.classList.add(
                'hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function filterTable(val) {
            const rows = document.querySelectorAll('.request-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = val === 'all' || r.dataset.status === val;
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
        }

        function searchTable(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.request-row');
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

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchTable('');
            document.getElementById('clearBtn').classList.add('hidden');
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
                const A = a.dataset[sortField].toLowerCase(),
                    B = b.dataset[sortField].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        /* modal openers */
        function openApproveModal(id, name) {
            currentId = id;
            document.getElementById('approveRequestName').textContent = name;
            const form = document.querySelector('#approveRequestModal form');
            if (form) form.action = `${ITEM_REQ_BASE}/${id}/status`;
            document.getElementById('approveRequestModal').classList.remove('hidden');
        }

        function openRejectModal(id, name) {
            currentId = id;
            document.getElementById('rejectRequestName').textContent = name;
            const form = document.querySelector('#rejectRequestModal form');
            if (form) form.action = `${ITEM_REQ_BASE}/${id}/status`;
            document.getElementById('rejectRequestModal').classList.remove('hidden');
        }

        function openApproveFromBtn(btn){
            const id = btn.dataset.id;
            const name = btn.dataset.name || '';
            openApproveModal(id, name);
        }
        function openRejectFromBtn(btn){
            const id = btn.dataset.id;
            const name = btn.dataset.name || '';
            openRejectModal(id, name);
        }
    </script>
@endsection
