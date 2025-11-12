@extends('Admin.layout.app')
@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Supplier Management</h1>
                <p class="text-sm text-gray-500 mt-1">Maintain supplier database integrity for purchase transactions</p>
            </div>
            <button onclick="openCreateModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-plus-circle mr-2"></i>Add Supplier
            </button>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Suppliers</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalSuppliers ?? ($suppliers->count() ?? 0) }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Active</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $activeSuppliers ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Inactive</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $inactiveSuppliers ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Month</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ $thisMonthSuppliers ?? 0 }}
            </p>
        </div>
    </div>

    <!-- suppliers table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Suppliers</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterTable(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search suppliers…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="suppliersTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('name')">Supplier <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact Number</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="suppliersTableBody">
                    @foreach($suppliers as $s)
                    <tr class="hover:bg-gray-50 transition supplier-row"
                        data-name="@json(strtolower($s->sup_name))"
                        data-status="@json(strtolower($s->sup_status))"
                        data-original-name="@json($s->sup_name)">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $s->sup_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $s->contact_person }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $s->contact_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $s->sup_email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $s->sup_address }}</td>
                        <td class="px-6 py-4">
                            @if($s->sup_status === 'active')
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Active</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditModal({{ $s->sup_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button onclick="openToggleModal({{ $s->sup_id }})"
                                    class="p-2 text-amber-600 hover:bg-amber-50 rounded transition" title="Toggle status">
                                    <i class="fas fa-user-slash text-sm"></i>
                                </button>
                                <button onclick="openDeleteModal({{ $s->sup_id }})"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded transition" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $suppliers->count() }}</span> of {{ $suppliers->count() }} suppliers
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Admin.Purchasing.Supplier.create')
    @include('Admin.Purchasing.Supplier.edit')
    @include('Admin.Purchasing.Supplier.toggle')
    @include('Admin.Purchasing.Supplier.delete')

</div>

<script>
/* light helpers */
let currentId = null;
const SUPPLIER_BASE = "{{ url('/admin/suppliers') }}";
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['createSupplierModal','editSupplierModal','toggleSupplierModal','deleteSupplierModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterTable(val){
    const rows = document.querySelectorAll('.supplier-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.status===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.supplier-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.dataset.name.includes(Q) || r.textContent.toLowerCase().includes(Q);
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
    const tbody=document.getElementById('suppliersTableBody');
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
function openCreateModal(){
    closeModals();
    const form = document.querySelector('#createSupplierModal form');
    if (form) form.action = SUPPLIER_BASE;
    document.getElementById('createSupplierModal').classList.remove('hidden');
}
function openEditModal(id){
    currentId=id;
    /* ajax fetch then fill form */
    fetch(`${SUPPLIER_BASE}/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r=>r.json())
        .then(data=>{
            const form = document.getElementById('editSupplierForm');
            form.action = `${SUPPLIER_BASE}/${id}`;
            form.querySelector('input[name="sup_name"]').value = data.sup_name || '';
            form.querySelector('input[name="contact_person"]').value = data.contact_person || '';
            form.querySelector('input[name="contact_number"]').value = data.contact_number || '';
            form.querySelector('input[name="sup_email"]').value = data.sup_email || '';
            form.querySelector('textarea[name="sup_address"]').value = data.sup_address || '';
            const sel = form.querySelector('select[name="sup_status"]'); if (sel) sel.value = data.sup_status || 'active';
            document.getElementById('editSupplierModal').classList.remove('hidden');
        })
        .catch(()=>{
            showMessage('Failed to load supplier details', 'error');
        });
}
function openToggleModal(id){
    const row = event.target.closest('tr');
    const name = row.dataset.originalName;
    const status = row.dataset.status;
    currentId=id;
    document.getElementById('toggleSupplierName').textContent=name;
    document.getElementById('toggleCurrentStatus').textContent=status;
    const form = document.querySelector('#toggleSupplierModal form');
    if (form) form.action = `${SUPPLIER_BASE}/${id}/toggle`;
    document.getElementById('toggleSupplierModal').classList.remove('hidden');
}
function openDeleteModal(id){
    const row = event.target.closest('tr');
    const name = row.dataset.originalName;
    currentId=id;
    document.getElementById('deleteSupplierName').textContent=name;
    const form = document.getElementById('deleteSupplierForm');
    if (form) form.action = `${SUPPLIER_BASE}/${id}`;
    document.getElementById('deleteSupplierModal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function(){
    // Create form
    document.querySelector('#createSupplierModal form').addEventListener('submit', function(e){
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                showMessage('Supplier created successfully!', 'success');
                closeModals();
                location.reload();
            } else {
                showMessage('Failed to create supplier.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred.', 'error');
        });
    });

    // Edit form
    document.getElementById('editSupplierForm').addEventListener('submit', function(e){
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                showMessage('Supplier updated successfully!', 'success');
                closeModals();
                location.reload();
            } else {
                showMessage('Failed to update supplier.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred.', 'error');
        });
    });

    // Toggle form
    document.querySelector('#toggleSupplierModal form').addEventListener('submit', function(e){
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                showMessage('Supplier status updated successfully!', 'success');
                closeModals();
                location.reload();
            } else {
                showMessage('Failed to update supplier status.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred.', 'error');
        });
    });

    // Delete form
    document.getElementById('deleteSupplierForm').addEventListener('submit', function(e){
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                showMessage(data.message || 'Supplier deleted successfully!', 'success');
                closeModals();
                location.reload();
            } else {
                showMessage(data.message || 'Failed to delete supplier.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred.', 'error');
        });
    });
});
</script>
@endsection