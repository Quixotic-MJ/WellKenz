@extends('Inventory.layout.app')

@section('title','Low-Stock Alerts - WellKenz ERP')
@section('breadcrumb','Low-Stock Alerts')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Low-Stock Alerts</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor and respond to items at or below reorder level</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="notifyPurchasing()"
                    class="px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 transition text-sm font-medium rounded">
                    <i class="fas fa-bell mr-2"></i>Notify Purchasing
                </button>
                <a href="{{ route('inventory.transactions.create') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                    <i class="fas fa-exchange-alt mr-2"></i>Manual Adjustment
                </a>
            </div>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Low Stock</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ count(DB::select('SELECT * FROM get_low_stock_items()')) }}
            </p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Critical Stock</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('items')->where('is_active',true)->whereRaw('item_stock <= min_stock_level')->count() }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Zero Stock</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('items')->where('is_active',true)->where('item_stock',0)->count() }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('items')->where('is_active',true)->whereRaw('item_stock <= reorder_level')->whereBetween('updated_at',[now()->startOfWeek(),now()->endOfWeek()])->count() }}
            </p>
        </div>
    </div>

    <!-- 3. filter bar -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select id="statusFilter" onchange="filterStatus(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                <option value="all">All Status</option>
                <option value="CRITICAL">Critical</option>
                <option value="LOW">Low</option>
                <option value="NORMAL">Normal</option>
            </select>
            <select id="catFilter" onchange="filterCategory(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                <option value="all">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->cat_id }}">{{ $c->cat_name }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center text-sm text-gray-700">
                <input type="checkbox" id="activeOnly" checked onchange="filterActive(this.checked)" class="mr-2">
                Active only
            </label>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search by code / name…" onkeyup="searchTable(this.value)"
                    class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
            </div>
        </div>
    </div>

    <!-- 4. low-stock items table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Low-Stock Items (≤ Reorder Level)</h3>
            <span class="text-xs text-gray-500">Click row to view details – stock changes via transactions only</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="itemsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('code')">Code <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reorder</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Min / Max</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="itemsTableBody">
                    @foreach($lowStockItems as $item)
                    <tr class="hover:bg-gray-50 transition item-row cursor-pointer"
                        data-code="{{ strtolower($item->item_code) }}"
                        data-name="{{ strtolower($item->item_name) }}"
                        data-cat="{{ $item->cat_id }}"
                        data-status="{{ $item->stockStatus ?? 'LOW' }}"
                        data-active="{{ $item->is_active ? '1' : '0' }}"
                        onclick="openViewModal({{ $item->item_id }})">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->item_code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->category->cat_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_stock }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->item_unit }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->reorder_level }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->min_stock_level }} / {{ $item->max_stock_level ?? '∞' }}</td>
                        <td class="px-6 py-4">
                            @php
                                $status = 'LOW';
                                if($item->item_stock <= $item->min_stock_level) $status = 'CRITICAL';
                            @endphp
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($status=='CRITICAL') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4" onclick="event.stopPropagation()">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $item->item_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button onclick="openTransactionModal({{ $item->item_id }})"
                                    class="p-2 text-green-600 hover:bg-green-50 rounded transition" title="Stock Transaction">
                                    <i class="fas fa-exchange-alt text-sm"></i>
                                </button>
                                <button onclick="createRequisition({{ $item->item_id }})"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded transition" title="Create Requisition">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $lowStockItems->count() }}</span> of {{ $lowStockItems->total() }} items
        </div>
    </div>

    <!-- 5. quick actions -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button onclick="exportLowStockCSV()"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                <i class="fas fa-download mr-2"></i>Export Low-Stock CSV
            </button>
            <button onclick="printLowStockReport()"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
            <button onclick="notifyPurchasing()"
                class="w-full px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 transition text-sm font-medium rounded">
                <i class="fas fa-bell mr-2"></i>Notify Purchasing
            </button>
        </div>
    </div>

</div>

<!-- ====== MODALS  ====== -->
@include('Inventory.Alert.view')
@include('Inventory.Alert.transaction')
@include('Inventory.Alert.notify')

@endsection

@push('scripts')
<script>
/* ===== helpers ===== */
let currentId = null;
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['viewItemModal','transactionModal','notifyModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* ===== search / filter ===== */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.item-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.code.includes(Q) || r.dataset.name.includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}
function filterCategory(val){
    const rows = document.querySelectorAll('.item-row'); let visible=0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.cat===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterStatus(val){
    const rows = document.querySelectorAll('.item-row'); let visible=0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.status===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterActive(checked){
    const rows = document.querySelectorAll('.item-row'); let visible=0;
    rows.forEach(r=>{
        const ok = checked ? r.dataset.active==='1' : true;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}

/* ===== sort ===== */
let sortField='code', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('itemsTableBody');
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

/* ===== modal openers ===== */
function openViewModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('viewItemModal').classList.remove('hidden');
}
function openTransactionModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('transactionModal').classList.remove('hidden');
}
function createRequisition(id){
    window.location.href = `/inventory/requisitions/create?item_id=${id}`;
}

/* ===== export / print / notify ===== */
function exportLowStockCSV(){
    const rows = Array.from(document.querySelectorAll('#itemsTableBody tr:not([style*="display: none"])'));
    let csv = ['Code,Name,Category,Stock,Unit,Reorder,Status'];
    rows.forEach(tr=>{
        const cells = tr.querySelectorAll('td');
        const row = [
            cells[0].textContent.trim(),
            cells[1].textContent.trim(),
            cells[2].textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].textContent.trim(),
            cells[5].textContent.trim(),
            cells[7].textContent.trim()
        ].map(field => `"${field.replace(/"/g, '""')}"`).join(',');
        csv.push(row);
    });
    const blob = new Blob([csv.join('\n')], {type:'text/csv'});
    const url  = window.URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = 'Low-Stock-Report-{{ now()->format('Y-m-d') }}.csv';
    a.click(); window.URL.revokeObjectURL(url);
}
function printLowStockReport(){
    window.print();
}
function notifyPurchasing(){
    document.getElementById('notifyModal').classList.remove('hidden');
}
</script>
@endpush