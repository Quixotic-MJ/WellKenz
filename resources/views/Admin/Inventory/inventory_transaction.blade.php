@extends('Admin.layout.app')

@section('title', 'Inventory & Transactions - WellKenz ERP')
@section('breadcrumb', 'Inventory and Transactions Management')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Inventory & Transactions</h1>
                <p class="text-sm text-gray-500 mt-1">Track and audit stock levels and movement</p>
            </div>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Trans</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalCount }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Stock-In</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $inCount }}</p>
        </div>
        <div class="bg-white border border-red-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Stock-Out</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $outCount }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Adjustments</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $adjCount }}</p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Neg Stock Items</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $negStockCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Ack Receipts</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $ackCount }}</p>
        </div>
    </div>

    <!-- transactions table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Transactions</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterType(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Types</option>
                    <option value="in">Stock-In</option>
                    <option value="out">Stock-Out</option>
                    <option value="adjustment">Adjustment</option>
                </select>
                <select onchange="filterUser(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Users</option>
                    @foreach($users as $u)<option value="{{ $u->user_id }}">{{ $u->name }}</option>@endforeach
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search transactions…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="transactionsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('date')">Date <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ack / Memo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="transactionsTableBody">
                    @foreach($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition tx-row"
                        data-type="{{ $tx->trans_type }}"
                        data-user="{{ $tx->trans_by }}"
                        data-item="{{ strtolower($tx->item->item_name ?? '') }}"
                        data-date="{{ optional($tx->created_at)->format('Y-m-d') }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($tx->trans_date ?? $tx->created_at)->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $tx->item->item_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($tx->trans_type === 'in') bg-green-100 text-green-700
                                @elseif($tx->trans_type === 'out') bg-red-100 text-red-700
                                @else bg-amber-100 text-amber-700
                                @endif">
                                {{ ucfirst($tx->trans_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->quantity }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->user->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->trans_remarks ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($tx->trans_type === 'out' && $tx->acknowledgeReceipt)
                                <a href="{{ url('/admin/acknowledge-receipts/' . $tx->acknowledgeReceipt->id) }}" class="text-blue-600 hover:underline">Ack</a>
                            @elseif($tx->trans_type === 'in' && $tx->memo)
                                <button onclick="openMemoModal({{ $tx->memo->id }})" class="text-blue-600 hover:underline bg-transparent border-none p-0 cursor-pointer">Memo</button>
                            @else
                                        —
                                    @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $tx->inventory_transaction_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if($tx->item && $tx->item->current_stock < 0)
                                    <span class="text-xs text-rose-600 font-semibold">Neg</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $transactions->count() }}</span> of {{ $transactions->total() }} transactions
            </div>
            <div class="px-6 py-3">
                {{ $transactions->links() }}
            </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Admin.Inventory.Item.view')

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
    ['viewTransactionModal', 'viewMemoModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterType(val){
    const rows = document.querySelectorAll('.tx-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.type===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterUser(val){
    const rows = document.querySelectorAll('.tx-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.user===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.tx-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.item.includes(Q) || r.dataset.date.includes(Q) || r.textContent.toLowerCase().includes(Q);
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
let sortField='date', sortDir='desc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('transactionsTableBody');
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
currentId = id;
fetch(`/admin/inventory/transactions/${id}`, { headers: { 'Accept':'application/json' }})
.then(r => r.json())
.then(data => {
if (data.error) throw new Error(data.error);
const body = document.getElementById('viewTransactionBody');
body.innerHTML = `
  <div><span class="text-gray-500">Date:</span> ${data.date || '—'}</div>
  <div><span class="text-gray-500">Type:</span> ${data.type || '—'}</div>
  <div><span class="text-gray-500">Item:</span> ${(data.item_code? data.item_code+' — ' : '') + (data.item_name || '—')}</div>
  <div><span class="text-gray-500">Qty:</span> ${(data.quantity ?? 0)} ${data.item_unit || ''}</div>
  <div><span class="text-gray-500">User:</span> ${data.user || '—'}</div>
  <div><span class="text-gray-500">PO Ref:</span> ${data.po_ref || '—'}</div>
  <div><span class="text-gray-500">Remarks:</span> ${data.remarks || '—'}</div>
`;
document.getElementById('viewTransactionModal').classList.remove('hidden');
})
.catch(err => { console.error(err); showMessage('Failed to load transaction details','error'); });
}

/* memo modal opener */
function openMemoModal(id){
fetch(`/admin/memos/${id}`, { headers: { 'Accept':'application/json' }})
.then(r => r.json())
.then(data => {
if (data.error) throw new Error(data.error);
const memo = data.memo;
const body = document.getElementById('viewMemoBody');
body.innerHTML = `
  <div><span class="text-gray-500">Memo Ref:</span> ${memo.memo_ref || '—'}</div>
  <div><span class="text-gray-500">Remarks:</span> ${memo.memo_remarks || '—'}</div>
  <div><span class="text-gray-500">Received Date:</span> ${memo.received_date || '—'}</div>
  <div><span class="text-gray-500">Received By:</span> ${memo.receivedBy?.name || '—'}</div>
  <div><span class="text-gray-500">PO Ref:</span> ${memo.po_ref || '—'}</div>
`;
document.getElementById('viewMemoModal').classList.remove('hidden');
})
.catch(err => { console.error(err); showMessage('Failed to load memo details','error'); });
}
</script>
@endsection