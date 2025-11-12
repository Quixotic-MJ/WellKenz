@extends('Admin.layout.app')

@section('title', 'Requisition Management - WellKenz ERP')
@section('breadcrumb', 'Requisition Management')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Requisition Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Oversee internal supply requisitions submitted by employees</p>
                </div>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalCount }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $pendingCount }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $approvedCount }}</p>
            </div>
            <div class="bg-white border border-rose-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Rejected</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $rejectedCount }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Completed</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">
                    {{ $completedCount }}</p>
            </div>
        </div>

        <!-- requisitions table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Requisitions</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterTable(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search requisitions…"
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
                <table class="w-full text-sm" id="requisitionsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer"
                                onclick="sortTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reviewed By</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="requisitionsTableBody">
                        @foreach ($requisitions as $req)
                            <tr class="hover:bg-gray-50 transition req-row" data-ref="{{ strtolower($req->req_ref) }}"
                                data-status="{{ strtolower($req->req_status) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->req_ref }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->req_purpose }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if ($req->req_priority === 'high') bg-rose-100 text-rose-700
                                @elseif($req->req_priority === 'medium') bg-amber-100 text-amber-700
                                @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($req->req_priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->approver->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($req->req_status === 'pending')
                                        <span
                                            class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Pending</span>
                                    @elseif($req->req_status === 'approved')
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Approved</span>
                                    @elseif($req->req_status === 'rejected')
                                        <span
                                            class="inline-block px-2 py-1 bg-rose-100 text-rose-700 text-xs font-semibold rounded">Rejected</span>
                                    @else
                                        <span
                                            class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded">Completed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->items->count() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->req_reject_reason ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openViewModal({{ $req->req_id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                                            title="View items">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        @if ($req->req_status === 'pending')
                                            <button
                                                data-id="{{ $req->req_id }}"
                                                data-ref="{{ e($req->req_ref) }}"
                                                onclick="openApproveFromBtn(this)"
                                                class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 transition text-xs font-semibold rounded">
                                                Approve
                                            </button>
                                            <button
                                                data-id="{{ $req->req_id }}"
                                                data-ref="{{ e($req->req_ref) }}"
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
                Showing <span id="visibleCount">{{ $requisitions->count() }}</span> of {{ $requisitions->total() }} requisitions
            </div>
            <div class="px-6 py-3">
                {{ $requisitions->links() }}
            </div>
        </div>

        <!-- ====== MODALS  ====== -->
        @include('Admin.Requisition.requisition.view')
        @include('Admin.Requisition.requisition.approve')
        @include('Admin.Requisition.requisition.reject')

    </div>

    <script>
        /* light helpers */
        let currentId = null;
        const REQ_BASE = "{{ url('/requisitions') }}";

        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        function closeModals() {
            ['viewRequisitionModal', 'approveRequisitionModal', 'rejectRequisitionModal'].forEach(id => document
                .getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function filterTable(val) {
            const rows = document.querySelectorAll('.req-row');
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
            const rows = document.querySelectorAll('.req-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.dataset.ref.includes(Q) || r.textContent.toLowerCase().includes(Q);
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
            const tbody = document.getElementById('requisitionsTableBody');
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
        function openViewModal(id) {
            currentId = id;
            fetch(`/requisitions/${id}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('viewItemsBody');
                tbody.innerHTML = '';
                data.items.forEach(item => {
                    const row = `<tr>
                        <td class="px-4 py-2">${item.item.item_name}</td>
                        <td class="px-4 py-2">${item.req_item_quantity}</td>
                        <td class="px-4 py-2">${item.item_unit}</td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
                document.getElementById('viewRequisitionModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to load requisition details.', 'error');
            });
        }

        function openApproveModal(id, ref) {
            currentId = id;
            document.getElementById('approveRequisitionRef').textContent = ref;
            const form = document.querySelector('#approveRequisitionModal form');
            if (form) form.action = `${REQ_BASE}/${id}/status`;
            document.getElementById('approveRequisitionModal').classList.remove('hidden');
        }

        function openRejectModal(id, ref) {
            currentId = id;
            document.getElementById('rejectRequisitionRef').textContent = ref;
            const form = document.querySelector('#rejectRequisitionModal form');
            if (form) form.action = `${REQ_BASE}/${id}/status`;
            document.getElementById('rejectRequisitionModal').classList.remove('hidden');
        }

        function openApproveFromBtn(btn){
            const id = btn.dataset.id;
            const ref = btn.dataset.ref || '';
            openApproveModal(id, ref);
        }
        function openRejectFromBtn(btn){
            const id = btn.dataset.id;
            const ref = btn.dataset.ref || '';
            openRejectModal(id, ref);
        }

        // Form submissions
        document.addEventListener('DOMContentLoaded', function(){
            document.getElementById('approveRequisitionForm').addEventListener('submit', function(e){
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        showMessage(data.message);
                        closeModals();
                        location.reload();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred.', 'error');
                });
            });

            document.getElementById('rejectRequisitionForm').addEventListener('submit', function(e){
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        showMessage(data.message);
                        closeModals();
                        location.reload();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred.', 'error');
                });
            });
        });
    </script>
@endsection
