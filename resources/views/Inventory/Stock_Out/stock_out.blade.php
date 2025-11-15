@extends('Inventory.layout.app')

@section('title','Stock-Out / AR – WellKenz ERP')
@section('breadcrumb','Stock-Out / AR')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Stock-Out / Issuance (AR)</h1>
                <p class="text-sm text-gray-500 mt-1">Issue items against approved requisitions – stock deducted via transactions</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inventory.transactions.create') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                    <i class="fas fa-exchange-alt mr-2"></i>Manual Transaction
                </a>
                <a href="{{ route('inventory.transactions.index') }}"
                   class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                    <i class="fas fa-list mr-2"></i>All Transactions
                </a>
            </div>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Approved Reqs</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('requisitions')->where('req_status','approved')->count() }}</p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Issuance</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('requisitions')->where('req_status','approved')->whereDoesntHave('acknowledgeReceipt')->count() }}
            </p>
        </div>
        <div class="bg-white border border-blue-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Issued Today</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('acknowledge_receipts')->whereDate('created_at',today())->count() }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Stock-Out MTD</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ number_format(DB::table('inventory_transactions')->where('trans_type','out')->whereMonth('created_at',now()->month)->sum('trans_quantity')) }}
            </p>
        </div>
    </div>

    <!-- 3. approved requisitions awaiting issuance -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Approved Requisitions Awaiting Issuance</h3>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search requisitions…" onkeyup="searchTable(this.value)"
                    class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="reqTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('ref')">Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requester</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="reqTableBody">
                    @foreach($approvedReqs as $req)
                    <tr class="hover:bg-gray-50 transition req-row cursor-pointer"
                        data-ref="{{ strtolower($req->req_ref) }}"
                        onclick="openIssueModal({{ $req->req_id }})">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">RQ-{{ $req->req_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $req->requester->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($req->req_purpose,50) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($req->req_priority=='high') bg-rose-100 text-rose-700
                                @elseif($req->req_priority=='medium') bg-amber-100 text-amber-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($req->req_priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $req->items->count() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4" onclick="event.stopPropagation()">
                            <button onclick="openIssueModal({{ $req->req_id }})"
                                class="px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-medium rounded">
                                <i class="fas fa-sign-out-alt mr-1"></i>Issue Items
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $approvedReqs->count() }}</span> of {{ $approvedReqs->total() }} requisitions
        </div>
    </div>

    <!-- 4. recent ARs issued -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recently Issued Acknowledge Receipts (AR)</h3>
            <a href="{{ route('inventory.acknowledge-receipts.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="arTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">AR Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Req Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Issued To</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Issued By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="arTableBody">
                    @foreach($recentARs as $ar)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">AR-{{ $ar->ar_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">RQ-{{ $ar->requisition->req_ref ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $ar->issuedTo->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $ar->items->count() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $ar->issuedBy->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $ar->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-700">
                                {{ ucfirst($ar->ar_status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing {{ $recentARs->count() }} of {{ $recentARs->total() }} acknowledge receipts
        </div>
    </div>

</div>

<!-- ====== ISSUE / AR MODAL  ====== -->
@include('Inventory.Stock_Out.issue')

@endsection

@push('scripts')
<script>
/* ===== helpers ===== */
let currentReqId = null;
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['issueItemsModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentReqId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* ===== search / sort ===== */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.req-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.ref.includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}
let sortField='ref', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('reqTableBody');
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

/* ===== open issue modal ===== */
function openIssueModal(reqId){
    currentReqId = reqId;
    /* ajax fetch then fill modal */
    document.getElementById('issueItemsModal').classList.remove('hidden');
}
</script>
@endpush