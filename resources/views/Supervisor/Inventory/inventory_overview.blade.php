@extends('Supervisor.layout.app')

@section('title', 'Inventory Overview - WellKenz ERP')
@section('breadcrumb', 'Inventory Overview')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Inventory Overview</h1>
                <p class="text-sm text-gray-500 mt-1">Supervisor read-only view – monitor stock levels & expiry trends</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                <span>Read-only</span>
            </div>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Items</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalItems }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Low-Stock</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $lowStockCount }}</p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Expiring ≤ 30 d</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $expiringCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Zero Stock</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $zeroStockCount }}</p>
        </div>
    </div>

    <!-- alerts row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- low-stock card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Low-Stock Alerts</h3>
            <div class="space-y-3">
                @forelse($lowStockTop as $item)
                    <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded">
                        <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">Stock: {{ $item->item_stock }} {{ $item->item_unit }} • Re-order: {{ $item->reorder_level }} {{ $item->item_unit }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No low-stock items – you're all set!</p>
                @endforelse
            </div>
        </div>

        <!-- expiry card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Near-Expiry Alerts (≤ 30 days)</h3>
            <div class="space-y-3">
                @forelse($expiryTop as $lot)
                    <div class="p-3 border-l-4 border-rose-500 bg-rose-50 rounded">
                        <p class="text-sm font-medium text-gray-900">{{ $lot->item_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">Expires: {{ \Carbon\Carbon::parse($lot->item_expire_date)->format('M d, Y') }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No items expiring within 30 days.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- full inventory table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Inventory Items</h3>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search items…" onkeyup="searchTable(this.value)"
                    class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="inventoryTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('code')">Code <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reorder Lvl</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="inventoryTableBody">
                    @foreach($items as $item)
                    <tr class="hover:bg-gray-50 transition item-row"
                        data-code="{{ $item->item_code }}"
                        data-stock="{{ $item->item_stock }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->item_code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->category->cat_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->item_stock }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->item_unit }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->reorder_level }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($item->item_expire_date) {{ \Carbon\Carbon::parse($item->item_expire_date)->format('M d, Y') }} @else - @endif
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openViewModal({{ $item->item_id }})"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $items->count() }}</span> of {{ $items->count() }} items
        </div>
    </div>

</div>

<!-- ====== MODAL  ====== -->
@include('Supervisor.Inventory.view')

@endsection

@push('scripts')
<script>
/* light helpers */
let currentId = null;
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['viewItemModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.item-row'); let visible=0;
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
let sortField='code', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('inventoryTableBody');
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

/* modal opener */
function openViewModal(id){
    currentId=id;
    fetch(`/supervisor/items/${id}`, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const body = document.getElementById('viewItemBody');
        body.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Code</label>
                        <p class="text-gray-900 font-semibold">${data.item_code}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                        <p class="text-gray-900">${data.item_name}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <p class="text-gray-900">${data.cat_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                        <p class="text-gray-900">${data.item_unit}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Stock</label>
                        <p class="text-gray-900 font-semibold">${data.item_stock} ${data.item_unit}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Level</label>
                        <p class="text-gray-900">${data.reorder_level} ${data.item_unit}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Stock Level</label>
                        <p class="text-gray-900">${data.min_stock_level || 0} ${data.item_unit}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Stock Level</label>
                        <p class="text-gray-900">${data.max_stock_level || 'N/A'} ${data.max_stock_level ? data.item_unit : ''}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                    <p class="text-gray-900">${data.item_expire_date ? new Date(data.item_expire_date).toLocaleDateString() : 'No expiry'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded">${data.item_description || 'No description'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Custom Item</label>
                    <p class="text-gray-900">${data.is_custom ? 'Yes' : 'No'}</p>
                </div>
            </div>`;
        document.getElementById('viewItemModal').classList.remove('hidden');
    })
    .catch(() => showMessage('Error loading item details', 'error'));
}
</script>
@endpush