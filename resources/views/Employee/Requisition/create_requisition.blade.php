@extends('Employee.layout.app')

@section('title', 'Requisitions - WellKenz ERP')
@section('breadcrumb', 'Requisitions')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">My Requisitions</h1>
                <p class="text-sm text-gray-500 mt-1">Request items from inventory for production</p>
            </div>
            <button onclick="openCreateModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-plus mr-2"></i>New Requisition
            </button>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $reqTotal ?? 0 }}
            </p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $reqPending ?? 0 }}
            </p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $reqApproved ?? 0 }}
            </p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Rejected</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $reqRejected ?? 0 }}
            </p>
        </div>
    </div>

    <!-- requisitions table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">History</h3>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search requisitions…" onkeyup="searchTable(this.value)"
                    class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="requisitionsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="requisitionsTableBody">
                    @foreach($requisitions as $r)
                    <tr class="hover:bg-gray-50 transition req-row"
                        data-ref="{{ $r->req_ref }}"
                        data-status="{{ $r->req_status }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">RQ-{{ $r->req_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($r->req_purpose,50) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($r->req_priority=='high') bg-rose-100 text-rose-700
                                @elseif($r->req_priority=='medium') bg-amber-100 text-amber-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($r->req_priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($r->req_status=='pending') bg-amber-100 text-amber-700
                                @elseif($r->req_status=='approved') bg-green-100 text-green-700
                                @elseif($r->req_status=='rejected') bg-rose-100 text-rose-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($r->req_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $r->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $r->req_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if($r->req_status=='pending')
                                    <button onclick="cancelReq({{ $r->req_id }})"
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded transition" title="Cancel">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                @endif
                                @if($r->req_status=='approved')
                                    <a href="{{ route('requisitions.print',$r->req_id) }}" target="_blank"
                                       class="p-2 text-gray-600 hover:bg-gray-50 rounded transition" title="Print">
                                        <i class="fas fa-print text-sm"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $requisitions->count() }}</span> of {{ $requisitions->count() }} requisitions
        </div>
    </div>

</div>

<!-- ====== MODALS  ====== -->
@include('Employee.Requisition.create')
@include('Employee.Requisition.view')

@endsection

@section('scripts')
<script>
/* light helpers */
let currentId = null;
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['createRequisitionModal','viewRequisitionModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.req-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}

/* sort */
let sortField='ref', sortDir='desc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('requisitionsTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.dataset[f], B=b.dataset[f];
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* modal openers */
function openCreateModal(){
    document.getElementById('createRequisitionModal').classList.remove('hidden');
}
function openViewModal(id){
    currentId=id;
    fetch(`/requisitions/${id}`)
    .then(r=>r.json())
    .then(data=>{
        if(data.error){
            showMessage(data.error,'error');
            return;
        }
        const body = document.getElementById('viewRequisitionBody');
        let html = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Reference:</strong> ${data.req_ref}</div>
                    <div><strong>Status:</strong>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                            ${data.req_status=='pending'?'bg-amber-100 text-amber-700':
                              data.req_status=='approved'?'bg-green-100 text-green-700':
                              data.req_status=='rejected'?'bg-rose-100 text-rose-700':'bg-gray-100 text-gray-700'}">
                            ${data.req_status.charAt(0).toUpperCase() + data.req_status.slice(1)}
                        </span>
                    </div>
                    <div><strong>Priority:</strong> ${data.req_priority.charAt(0).toUpperCase() + data.req_priority.slice(1)}</div>
                    <div><strong>Requested:</strong> ${new Date(data.created_at).toLocaleDateString()}</div>
                </div>
                <div>
                    <strong>Purpose:</strong>
                    <p class="mt-1 text-gray-700">${data.req_purpose}</p>
                </div>
                <div>
                    <strong>Items Requested:</strong>
                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full text-sm border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left border-b">Item Name</th>
                                    <th class="px-3 py-2 text-left border-b">Quantity</th>
                                    <th class="px-3 py-2 text-left border-b">Unit</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        data.items.forEach(item=>{
            html += `
                                <tr class="border-b">
                                    <td class="px-3 py-2">${item.item.item_name}</td>
                                    <td class="px-3 py-2">${item.req_item_quantity}</td>
                                    <td class="px-3 py-2">${item.item_unit}</td>
                                </tr>
            `;
        });
        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
        `;
        if(data.req_reject_reason){
            html += `
                <div>
                    <strong>Rejection Reason:</strong>
                    <p class="mt-1 text-red-700">${data.req_reject_reason}</p>
                </div>
            `;
        }
        html += `</div>`;
        body.innerHTML = html;
        document.getElementById('viewRequisitionModal').classList.remove('hidden');
    })
    .catch(()=>showMessage('Error loading requisition details','error'));
}

/* cancel */
function cancelReq(id){
    if(!confirm('Cancel this pending requisition?')) return;
    fetch(`/requisitions/${id}`,{
        method:'DELETE',
        headers:{
            'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            showMessage('Requisition cancelled');
            setTimeout(()=>location.reload(),500);
        }else{
            showMessage(res.message||'Error','error');
        }
    })
    .catch(()=>showMessage('Error','error'));
}
</script>
@endsection