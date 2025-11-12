@extends('Admin.layout.app')

@section('title', 'Purchase Order Management - WellKenz ERP')
@section('breadcrumb', 'Purchase Order Management')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Purchase Order Management</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor and manage all purchase orders created by the purchasing officer</p>
            </div>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total POs</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalPOs }}</p>
        </div>
        <div class="bg-white border border-blue-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Draft</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $draftCount }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Ordered</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $orderedCount }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Delivered</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $deliveredCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Month</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $thisMonthCount }}
            </p>
        </div>
    </div>

    <!-- purchase orders table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Purchase Orders</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterTable(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All</option>
                    <option value="draft">Draft</option>
                    <option value="ordered">Ordered</option>
                    <option value="delivered">Delivered</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search POs…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="purchaseOrdersTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('ref')">PO Ref <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requisition</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expected Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="purchaseOrdersTableBody">
                    @foreach($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50 transition po-row"
                        data-ref="{{ strtolower($po->po_ref) }}"
                        data-status="{{ strtolower($po->po_status) }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $po->po_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->supplier->sup_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->requisition->req_ref ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">₱ {{ number_format($po->total_amount,2) }}</td>
                        <td class="px-6 py-4">
                            @if($po->po_status === 'draft')
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded">Draft</span>
                            @elseif($po->po_status === 'ordered')
                                <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Ordered</span>
                            @elseif($po->po_status === 'delivered')
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Delivered</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->items->count() }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button data-id="{{ $po->po_id }}" onclick="openViewFromBtn(this)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View items">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if($po->po_status === 'ordered' && auth()->user()->role === 'admin')
                                    <button data-id="{{ $po->po_id }}" data-ref="{{ e($po->po_ref) }}" data-status="{{ $po->po_status }}" onclick="openStatusFromBtn(this)"
                                        class="p-2 text-indigo-600 hover:bg-indigo-50 rounded transition" title="Change status">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $purchaseOrders->count() }}</span> of {{ $purchaseOrders->total() }} orders
        </div>
        <div class="px-6 py-3">
            {{ $purchaseOrders->links() }}
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Admin.Purchasing.Purchase.view')
    @include('Admin.Purchasing.Purchase.status')

</div>

<script>
/* light helpers */
let currentId = null, currentStatus = null;
const ADMIN_PO_BASE = "{{ url('/admin/purchase-orders') }}";

function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}

function closeModals(){
    ['viewPurchaseOrderModal','statusPurchaseOrderModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null; currentStatus = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterTable(val){
    const rows = document.querySelectorAll('.po-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.status===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.po-row'); let visible=0;
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
let sortField='ref', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('purchaseOrdersTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.dataset[sortField].toLowerCase(), B=b.dataset[sortField].toLowerCase();
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* modal openers */
function openViewFromBtn(btn){
    const id = btn.dataset.id;
    openViewModal(id);
}
function openStatusFromBtn(btn){
    const id = btn.dataset.id;
    const ref = btn.dataset.ref || '';
    const status = btn.dataset.status || '';
    openStatusModal(id, ref, status);
}
function openViewModal(id){
    currentId=id;
    fetch(`${ADMIN_PO_BASE}/${id}`, {
        headers: { 'Accept':'application/json' }
    })
    .then(r=>r.json())
    .then(data=>{
        const tbody = document.getElementById('viewPOItemsBody');
        tbody.innerHTML = '';
        (data.items||[]).forEach(it=>{
            tbody.innerHTML += `<tr>
                <td class="px-4 py-2">${it.item_name||'—'}</td>
                <td class="px-4 py-2">${it.quantity||0}</td>
                <td class="px-4 py-2">${it.unit||'—'}</td>
                <td class="px-4 py-2">${Number(it.unit_price||0).toFixed(2)}</td>
                <td class="px-4 py-2">${Number(it.subtotal||0).toFixed(2)}</td>
            </tr>`;
        });
        document.getElementById('viewPurchaseOrderModal').classList.remove('hidden');
    })
    .catch(err=>{
        console.error(err);
        showMessage('Failed to load PO details','error');
    });
}
function openStatusModal(id,ref,status){
    currentId=id; currentStatus=status;
    document.getElementById('statusPORef').textContent=ref;
    const form = document.getElementById('statusPurchaseOrderForm');
    if(form) form.action = `${ADMIN_PO_BASE}/${id}/status`;
    document.getElementById('statusPurchaseOrderModal').classList.remove('hidden');
}

// AJAX form submit for status update
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('statusPurchaseOrderForm');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const fd = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept':'application/json' }
            })
            .then(r=>r.json())
            .then(data=>{
                if(data.success){
                    showMessage(data.message);
                    closeModals();
                    location.reload();
                } else {
                    showMessage(data.message||'Update failed','error');
                }
            })
            .catch(err=>{
                console.error(err);
                showMessage('An error occurred','error');
            });
        });
    }
});
</script>

@endsection