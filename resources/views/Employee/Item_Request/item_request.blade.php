@extends('Employee.layout.app')

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
                    <p class="text-sm text-gray-500 mt-1">Request new or missing items not yet in inventory</p>
                </div>
                <button onclick="openCreateModal()"
                    class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                    <i class="fas fa-plus-circle mr-2"></i>New Item Request
                </button>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalCount }}</p>
            </div>
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
        </div>

        <!-- requests table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">My Item Requests</h3>
                <div class="flex items-center space-x-3">
                    <select onchange="filterStatus(this.value)"
                        class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="all">All Status</option>
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
                                onclick="sortTable('name')">Item Name <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="requestsTableBody">
                        @forelse($requests as $req)
                            <tr class="hover:bg-gray-50 transition req-row"
                                data-name="{{ strtolower($req->item_req_name) }}"
                                data-status="{{ strtolower($req->item_req_status) }}">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->item_req_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_unit }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->item_req_quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <span
                                        class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if ($req->item_req_status === 'pending') bg-amber-100 text-amber-700
                                @elseif($req->item_req_status === 'approved') bg-green-100 text-green-700
                                @elseif($req->item_req_status === 'cancelled') bg-gray-100 text-gray-700
                                @else bg-rose-100 text-rose-700 @endif">
                                        {{ ucfirst($req->item_req_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $req->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openViewModal({{ $req->item_req_id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        @if ($req->item_req_status === 'pending')
                                            <a href="{{ route('staff.item-requests.edit', $req->item_req_id) }}"
                                                class="p-2 text-gray-600 hover:bg-gray-50 rounded transition"
                                                title="Edit">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            <button
                                                onclick="openCancelModal({{ $req->item_req_id }}, '{{ $req->item_req_name }}')"
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded transition"
                                                title="Cancel">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-file-alt text-3xl mb-3 opacity-50"></i>
                                    <p>No item requests found.</p>
                                    <button onclick="openCreateModal()" class="text-blue-600 hover:text-blue-800">Create
                                        your first request</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $requests->count() }}</span> of {{ $requests->count() }} requests
            </div>
        </div>

        <!-- ====== MODALS  ====== -->
        @include('Employee.Item_Request.create')
        @include('Employee.Item_Request.view')
        @include('Employee.Item_Request.cancel')

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
            ['createItemRequestModal', 'viewItemRequestModal', 'cancelItemRequestModal'].forEach(id => document
                .getElementById(id)?.classList.add('hidden'));
            currentId = null;
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* search / filter */
        function filterStatus(val) {
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
                const A = a.dataset[f].toLowerCase(),
                    B = b.dataset[f].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${f}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

    /* modal openers */
    function openCreateModal(){ closeModals(); document.getElementById('createItemRequestModal').classList.remove('hidden'); }
    function openViewModal(id){
        currentId=id;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/staff/item-requests/${id}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const body = document.getElementById('viewItemRequestBody');
                body.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Name:</strong> ${data.item_req_name}</div>
                        <div><strong>Unit:</strong> ${data.item_req_unit}</div>
                        <div><strong>Quantity:</strong> ${data.item_req_quantity}</div>
                        <div><strong>Status:</strong> <span class="px-2 py-1 text-xs font-semibold rounded ${
                            data.item_req_status === 'pending' ? 'bg-amber-100 text-amber-700' :
                            data.item_req_status === 'approved' ? 'bg-green-100 text-green-700' :
                            data.item_req_status === 'cancelled' ? 'bg-gray-100 text-gray-700' :
                            'bg-rose-100 text-rose-700'
                        }">${data.item_req_status.charAt(0).toUpperCase() + data.item_req_status.slice(1)}</span></div>
                        <div class="col-span-2"><strong>Description:</strong> ${data.item_req_description}</div>
                        <div><strong>Requested By:</strong> ${data.requester ? data.requester.name : 'N/A'}</div>
                        <div><strong>Requested At:</strong> ${new Date(data.created_at).toLocaleDateString()}</div>
                        ${data.approver ? `<div><strong>Approved By:</strong> ${data.approver.name}</div>` : ''}
                        ${data.updated_at !== data.created_at ? `<div><strong>Updated At:</strong> ${new Date(data.updated_at).toLocaleDateString()}</div>` : ''}
                        ${data.item_req_reject_reason ? `<div class="col-span-2"><strong>Reject Reason:</strong> ${data.item_req_reject_reason}</div>` : ''}
                    </div>
                `;
                document.getElementById('viewItemRequestModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error loading request details', 'error');
            });
    }
    function openCancelModal(id,name){
        currentId=id;
        document.getElementById('cancelItemRequestName').textContent=name;
        document.getElementById('cancelItemRequestId').value=id;
        document.getElementById('cancelItemRequestModal').classList.remove('hidden');
    }
    </script>
@endsection
