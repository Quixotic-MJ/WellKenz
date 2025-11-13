@extends('Supervisor.layout.app')

@section('title', 'Requisition Management - WellKenz ERP')
@section('breadcrumb', 'Requisition Management')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Requisition Management</h1>
                <p class="text-sm text-gray-500 mt-1">Review and decide on employee-submitted requisitions</p>
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

    <!-- pending table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Pending Requisitions</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterPriority(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search requisitions…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="pendingTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="pendingTableBody">
                    @forelse($pendingList as $req)
                    <tr class="hover:bg-gray-50 transition req-row"
                        data-ref="{{ strtolower($req->req_ref) }}"
                        data-priority="{{ strtolower($req->req_priority) }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->req_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($req->req_purpose,40) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($req->req_priority==='high') bg-rose-100 text-rose-700
                                @elseif($req->req_priority==='medium') bg-amber-100 text-amber-700
                                @else bg-green-100 text-green-700
                                @endif">
                                {{ ucfirst($req->req_priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->items->count() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $req->req_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button onclick="openApproveModal({{ $req->req_id }}, '{{ $req->req_ref }}')"
                                    class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 transition text-xs font-semibold rounded">
                                    Approve
                                </button>
                                <button onclick="openRejectModal({{ $req->req_id }}, '{{ $req->req_ref }}')"
                                    class="px-3 py-1 bg-rose-600 text-white hover:bg-rose-700 transition text-xs font-semibold rounded">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">No pending requisitions.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $pendingList->count() }}</span> of {{ $pendingList->count() }} pending requisitions
        </div>
    </div>

    <!-- past decisions -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Past Decisions</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterStatus(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchPast" placeholder="Search past decisions…" onkeyup="searchPastTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearPastSearch()" id="clearPastBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="pastTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortPastTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Decision By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="pastTableBody">
                    @forelse($pastList as $req)
                    <tr class="hover:bg-gray-50 transition past-row"
                        data-ref="{{ strtolower($req->req_ref) }}"
                        data-status="{{ strtolower($req->req_status) }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->req_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($req->req_status==='approved') bg-green-100 text-green-700
                                @elseif($req->req_status==='rejected') bg-rose-100 text-rose-700
                                @else bg-blue-100 text-blue-700
                                @endif">
                                {{ ucfirst($req->req_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->approver->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->updated_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <button onclick="openViewModal({{ $req->req_id }})"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">No past decisions.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visiblePastCount">{{ $pastList->count() }}</span> of {{ $pastList->count() }} past decisions
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Supervisor.Requisition.view')
    @include('Supervisor.Requisition.approve')
    @include('Supervisor.Requisition.reject')

</div>

<script>
/* light helpers */
let currentId = null;

function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['viewRequisitionModal','approveRequisitionModal','rejectRequisitionModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterPriority(val){
    const rows = document.querySelectorAll('.req-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.priority===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterStatus(val){
    const rows = document.querySelectorAll('.past-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.status===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visiblePastCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.req-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.ref.includes(Q) || r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function searchPastTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.past-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.ref.includes(Q) || r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visiblePastCount').textContent = visible;
    const btn = document.getElementById('clearPastBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}
function clearPastSearch(){
    document.getElementById('searchPast').value=''; searchPastTable(''); document.getElementById('clearPastBtn').classList.add('hidden');
}

/* sort */
let sortField='ref', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('pendingTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.dataset[f].toLowerCase(), B=b.dataset[f].toLowerCase();
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}
function sortPastTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('pastTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.dataset[f].toLowerCase(), B=b.dataset[f].toLowerCase();
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortPastTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* modal openers */
function openViewModal(id){
    currentId=id;
    fetch(`/supervisor/requisitions/${id}`, {
        method: 'GET',
        headers: {
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
            const body = document.getElementById('viewRequisitionBody');
            body.innerHTML = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                            <p class="text-gray-900 font-semibold">${data.req_ref}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Requester</label>
                            <p class="text-gray-900">${data.requester ? data.requester.name : 'N/A'}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${
                                data.req_priority === 'high' ? 'bg-rose-100 text-rose-700' :
                                data.req_priority === 'medium' ? 'bg-amber-100 text-amber-700' :
                                'bg-green-100 text-green-700'
                            }">${data.req_priority.charAt(0).toUpperCase() + data.req_priority.slice(1)}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded ${
                                data.req_status === 'pending' ? 'bg-amber-100 text-amber-700' :
                                data.req_status === 'approved' ? 'bg-green-100 text-green-700' :
                                data.req_status === 'rejected' ? 'bg-rose-100 text-rose-700' :
                                'bg-blue-100 text-blue-700'
                            }">${data.req_status.charAt(0).toUpperCase() + data.req_status.slice(1)}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded">${data.req_purpose || 'No purpose provided'}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requested Items</label>
                        <div class="bg-gray-50 rounded p-4">
                            ${data.items && data.items.length > 0 ? `
                                <div class="space-y-3">
                                    ${data.items.map(item => `
                                        <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                            <div>
                                                <p class="font-semibold text-gray-900">${item.req_item_name || item.item_name || 'N/A'}</p>
                                                <p class="text-sm text-gray-600">${item.req_item_description || item.item_description || 'No description'}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-semibold text-gray-900">${item.req_item_quantity || item.quantity || 'N/A'} ${item.req_item_unit || item.unit || ''}</p>
                                                <p class="text-xs text-gray-500">${item.category ? item.category.category_name : 'No category'}</p>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-500">No items requested</p>'}
                        </div>
                    </div>

                    ${data.req_status === 'rejected' && data.req_reject_reason ? `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle text-red-400 text-lg mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-red-800 mb-1">Rejection Reason</h4>
                                <p class="text-red-700 whitespace-pre-wrap">${data.req_reject_reason}</p>
                                ${data.approver ? `
                                    <p class="text-xs text-red-600 mt-2">
                                        Rejected by: ${data.approver.name} on ${new Date(data.updated_at).toLocaleDateString()}
                                    </p>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    ${data.req_status === 'approved' && data.approver ? `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400 text-lg mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-green-800 mb-1">Approval Information</h4>
                                <p class="text-green-700">
                                    Approved by: ${data.approver.name} on ${new Date(data.updated_at).toLocaleDateString()}
                                </p>
                            </div>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('viewRequisitionModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error loading requisition details', 'error');
        });
}
function openApproveModal(id,ref){
    currentId=id;
    document.getElementById('approveRequisitionRef').textContent=ref;
    const form = document.querySelector('#approveRequisitionModal form');
    form.action = `/supervisor/requisitions/${id}/status`;
    document.getElementById('approveRequisitionModal').classList.remove('hidden');
}
function openRejectModal(id,ref){
    currentId=id;
    document.getElementById('rejectRequisitionRef').textContent=ref;
    const form = document.querySelector('#rejectRequisitionModal form');
    form.action = `/supervisor/requisitions/${id}/status`;
    document.getElementById('rejectRequisitionModal').classList.remove('hidden');
}
</script>
@endsection