@extends('Employee.layout.app')

@section('title', 'Acknowledgement Receipts - WellKenz ERP')
@section('breadcrumb', 'Acknowledgement Receipts')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Acknowledgement Receipts</h1>
                <p class="text-sm text-gray-500 mt-1">Confirm receipt of items issued from inventory</p>
            </div>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalCount }}</p>
        </div>
        <div class="bg-white border border-blue-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Issued</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $issuedCount }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Received</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $receivedCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Month</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $thisMonthCount }}</p>
        </div>
    </div>

    <!-- receipts table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">My Receipts</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterStatus(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Status</option>
                    <option value="issued">Issued</option>
                    <option value="received">Received</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search receipts…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="receiptsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Issued Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Issued By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="receiptsTableBody">
                    @forelse($receipts as $rec)
                    <tr class="hover:bg-gray-50 transition rec-row"
                        data-ref="{{ strtolower($rec->ar_ref) }}"
                        data-status="{{ strtolower($rec->ar_status) }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $rec->ar_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($rec->ar_status==='issued') bg-blue-100 text-blue-700
                                @else bg-green-100 text-green-700
                                @endif">
                                {{ ucfirst($rec->ar_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $rec->issued_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $rec->issuer->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($rec->ar_remarks,40) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $rec->ar_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if($rec->ar_status==='issued')
                                    <button onclick="openConfirmModal({{ $rec->ar_id }}, '{{ $rec->ar_ref }}')"
                                        class="px-3 py-1 bg-green-600 text-white hover:bg-green-700 transition text-xs font-semibold rounded">
                                        Confirm
                                    </button>
                                @endif
                                <button onclick="openPrintModal({{ $rec->ar_id }})"
                                    class="p-2 text-gray-600 hover:bg-gray-50 rounded transition" title="Print">
                                    <i class="fas fa-print text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-file-alt text-3xl mb-3 opacity-50"></i>
                            <p>No acknowledgement receipts found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $receipts->count() }}</span> of {{ $receipts->count() }} receipts
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Employee.AR.view')
    @include('Employee.AR.confirm')
    @include('Employee.AR.print')

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
    ['viewAcknowledgementModal','confirmAcknowledgementModal','printAcknowledgementModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterStatus(val){
    const rows = document.querySelectorAll('.rec-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.status===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.rec-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.ref.includes(Q) || r.textContent.toLowerCase().includes(Q);
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
    const tbody=document.getElementById('receiptsTableBody');
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

/* modal openers */
function openViewModal(id){
    currentId=id;
    fetch(`/api/ar/${id}`, { headers: { 'Accept':'application/json' }})
        .then(async (response)=>{
            const ct = response.headers.get('content-type')||'';
            if(!response.ok){
                let msg = `Failed to load (HTTP ${response.status})`;
                try{
                    if(ct.includes('application/json')){ const j = await response.json(); msg = j.message || j.error || msg; }
                    else { const t = await response.text(); if(t) msg = t.substring(0,200); }
                }catch(_){}
                throw new Error(msg);
            }
            if(!ct.includes('application/json')){ const t = await response.text(); throw new Error((t&&t.substring(0,200))||'Unexpected response'); }
            return response.json();
        })
        .then(({ar,items})=>{
            const body = document.getElementById('viewAcknowledgementBody');
            const statusClass = ar.ar_status==='issued' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700';
            const issuedDate = ar.issued_date ? new Date(ar.issued_date).toLocaleDateString() : '—';
            body.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Reference:</strong> ${ar.ar_ref}</div>
                    <div><strong>Status:</strong> <span class="px-2 py-1 text-xs font-semibold rounded ${statusClass}">${ar.ar_status}</span></div>
                    <div><strong>Issued Date:</strong> ${issuedDate}</div>
                    <div><strong>Issued By:</strong> ${ar.issued_by_name || '—'}</div>
                    <div class="col-span-2"><strong>Remarks:</strong> ${ar.ar_remarks || '—'}</div>
                </div>
                <div>
                    <h4 class="mt-4 mb-2 font-semibold">Items</h4>
                    <div class="border rounded">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item ID</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(i=>`<tr class="border-t">
                                    <td class="px-3 py-2">${i.item_id}</td>
                                    <td class="px-3 py-2">${i.trans_quantity}</td>
                                    <td class="px-3 py-2">${i.trans_type}</td>
                                    <td class="px-3 py-2">${new Date(i.trans_date).toLocaleDateString()}</td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('viewAcknowledgementModal').classList.remove('hidden');
        })
        .catch(err=>{
            console.error(err);
            showMessage('Error loading acknowledgement details','error');
        });
}
function openConfirmModal(id,ref){
    currentId=id;
    document.getElementById('confirmAcknowledgementRef').textContent=ref;
    document.getElementById('confirmAcknowledgementId').value=id;
    document.getElementById('confirmAcknowledgementModal').classList.remove('hidden');
}
function openPrintModal(id){
    currentId=id;
    /* ajax fetch then open print window */
    window.open('/employee/acknowledgements/'+id+'/print','_blank');
}
</script>
@endsection