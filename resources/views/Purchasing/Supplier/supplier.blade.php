@extends('Purchasing.layout.app')

@section('title', 'Supplier Management - WellKenz ERP')
@section('breadcrumb', 'Supplier Management')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Supplier Management</h1>
                <p class="text-sm text-gray-500 mt-1">Maintain supplier information for accurate ordering</p>
            </div>
            <button onclick="openCreateModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-plus mr-2"></i>New Supplier
            </button>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Suppliers</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('suppliers')->count() }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Active</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('suppliers')->where('sup_status','active')->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Used in POs</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('suppliers')->whereIn('sup_id', function($q){
                    $q->select('supplier_id')->from('purchase_orders');
                })->count() }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Month</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('suppliers')->whereMonth('created_at',now()->month)->count() }}
            </p>
        </div>
    </div>

    <!-- 3. suppliers table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Suppliers</h3>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search suppliers…" onkeyup="searchTable(this.value)"
                    class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="supTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('name')">Name <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">POs</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="supTableBody">
                    @foreach($suppliers as $sup)
                    <tr class="hover:bg-gray-50 transition sup-row"
                        data-name="{{ strtolower($sup->sup_name) }}">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $sup->sup_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $sup->contact_person ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sup->sup_email ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sup->contact_number ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($sup->sup_status=='active') bg-green-100 text-green-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($sup->sup_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sup->purchaseOrders->count() }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $sup->sup_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button onclick="openEditModal({{ $sup->sup_id }})"
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 rounded transition" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button onclick="openPOsModal({{ $sup->sup_id }})"
                                    class="p-2 text-green-600 hover:bg-green-50 rounded transition" title="POs">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                                <button onclick="toggleStatus({{ $sup->sup_id }},'{{ $sup->sup_status }}')"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded transition" title="Toggle status">
                                    <i class="fas fa-power-off text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $suppliers->count() }}</span> of {{ $suppliers->total() }} suppliers
        </div>
    </div>

</div>

<!-- ====== MODALS  ====== -->
@include('Purchasing.Supplier.create')
@include('Purchasing.Supplier.view')
@include('Purchasing.Supplier.edit')
@include('Purchasing.Supplier.pos')

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
    ['createSupModal','viewSupModal','editSupModal','posModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.sup-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.name.includes(Q);
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
let sortField='name', sortDir='asc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('supTableBody');
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

/* status toggle */
function toggleStatus(id,current){
    fetch(`/purchasing/suppliers/${id}/toggle-status`,{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}'}
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            showMessage('Status updated');
            setTimeout(()=>location.reload(),500);
        }else{
            showMessage(res.message||'Error','error');
        }
    })
    .catch(()=>showMessage('Error','error'));
}

/* modal openers */
function openCreateModal(){
    document.getElementById('createSupModal').classList.remove('hidden');
}
function openViewModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('viewSupModal').classList.remove('hidden');
}
function openEditModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('editSupModal').classList.remove('hidden');
}
function openPOsModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('posModal').classList.remove('hidden');
}
</script>
@endpush