@extends('Purchasing.layout.app')
@section('title','Approved Requisitions')
@section('breadcrumb','Create Purchase Order')

@section('content')

@php
/* ----------  DATA  ---------- */
$approvedReqs = \App\Models\Requisition::with(['requester','items.item'])
                ->where('req_status','approved')
                ->latest()
                ->limit(20)
                ->get();
$pos          = \App\Models\PurchaseOrder::with(['supplier','requisition'])->latest()->limit(20)->get();
$suppliers    = \App\Models\Supplier::where('sup_status','active')->get();
@endphp

<div class="space-y-8">
    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-2xl p-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">Purchasing Dashboard</h1>
                <p class="text-sm opacity-80 mt-1">Supervisor-approved requisitions → Purchase Orders</p>
            </div>
            <a href="{{ route('Purchasing_dashboard') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-lg transition">Refresh</a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        @foreach([
            ['title'=>'Approved Req','value'=>$approvedReqs->count(),'icon'=>'fas fa-clipboard-check','bg'=>'bg-green-500'],
            ['title'=>'Total POs','value'=>$pos->count(),'icon'=>'fas fa-file-invoice-dollar','bg'=>'bg-blue-500'],
            ['title'=>'Active Suppliers','value'=>$suppliers->count(),'icon'=>'fas fa-users','bg'=>'bg-gray-600'],
            ['title'=>'Low-Stock Items','value'=>DB::table('items')->whereRaw('item_stock <= reorder_level')->where('is_active',true)->count(),'icon'=>'fas fa-exclamation-triangle','bg'=>'bg-red-500','url'=>route('items.low_stock')]
        ] as $kpi)
        <a @if(isset($kpi['url'])) href="{{ $kpi['url'] }}" @endif class="block group">
            <div class="{{ $kpi['bg'] }} text-white rounded-2xl p-5 shadow hover:shadow-xl transition">
                <div class="flex items-center justify-between">
                    <div><p class="text-xs uppercase tracking-wider opacity-80">{{ $kpi['title'] }}</p><p class="text-3xl font-bold mt-1">{{ $kpi['value'] }}</p></div>
                    <i class="{{ $kpi['icon'] }} text-2xl opacity-80"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- APPROVED REQUISITIONS → CREATE PO --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-800">Approved Requisitions</h2>
            <span class="text-sm text-gray-500">Click card to create PO</span>
        </div>
        @if($approvedReqs->isEmpty())
            <p class="text-gray-500 text-sm">No approved requisitions awaiting PO.</p>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($approvedReqs as $req)
                <a href="{{ route('purchase_orders.create',$req->req_id) }}" class="block border border-gray-200 rounded-xl p-4 hover:shadow transition">
                    <div class="flex items-start justify-between mb-2">
                        <p class="font-semibold text-gray-800">{{ $req->req_ref }}</p>
                        <span class="px-2 py-1 text-xs rounded {{ $req->req_priority=='high' ? 'bg-red-100 text-red-700' : ($req->req_priority=='medium' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($req->req_priority) }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">By: {{ $req->requester->name }} · {{ $req->requester->position }}</p>
                    <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $req->req_purpose }}</p>
                    <div class="text-xs text-gray-400">{{ $req->created_at->diffForHumans() }}</div>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- RECENT POS --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-800">Recent Purchase Orders</h2>
            <a href="{{ route('purchase_orders.print',$pos->first()->po_id ?? 0) }}" target="_blank" class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">Print Latest</a>
        </div>
        @if($pos->isEmpty())
            <p class="text-gray-500 text-sm">No purchase orders yet.</p>
        @else
            <div class="space-y-4">
                @foreach($pos as $po)
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:shadow transition">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $po->po_ref }}</p>
                        <p class="text-sm text-gray-600">{{ $po->supplier->sup_name }}</p>
                        <p class="text-xs text-gray-400">Linked: {{ $po->requisition->req_ref }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-800">₱{{ number_format($po->total_amount,2) }}</p>
                        <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700">{{ ucfirst($po->po_status) }}</span>
                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('purchase_orders.print',$po->po_id) }}" target="_blank" class="px-3 py-1 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">Print</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- SUPPLIER MANAGEMENT --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-800">Suppliers</h2>
            <button onclick="openSupplierModal()" class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">Add Supplier</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="supplierTable">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs"><tr><th>Name</th><th>Email</th><th>Contact Person</th><th>Phone</th><th class="w-24"></th></tr></thead>
                <tbody>
                    @foreach($suppliers as $s)
                    <tr data-id="{{ $s->sup_id }}">
                        <td class="px-4 py-3 font-semibold">{{ $s->sup_name }}</td>
                        <td class="px-4 py-3">{{ $s->sup_email }}</td>
                        <td class="px-4 py-3">{{ $s->contact_person }}</td>
                        <td class="px-4 py-3">{{ $s->contact_number }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <button onclick="editSupplier({{ $s->sup_id }})" class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Edit</button>
                            <button onclick="deleteSupplier({{ $s->sup_id }})" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==========  MODALS  ========== --}}
{{-- Supplier Modal --}}
<div id="supplierModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4" id="supplierModalTitle">Add Supplier</h3>
        <form id="supplierForm" class="space-y-4">
            @csrf
            <input type="hidden" id="sup_id">
            <input type="text" id="sup_name" placeholder="Supplier name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <input type="email" id="sup_email" placeholder="Email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <input type="text" id="sup_address" placeholder="Address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <input type="text" id="contact_person" placeholder="Contact person" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <input type="text" id="contact_number" placeholder="Phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeSupplierModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ==========  SUPPLIER AJAX  ========== */
function openSupplierModal(id=null){
    $('#supplierModal').removeClass('hidden');
    if(id){
        const row = $(`tr[data-id="${id}"]`);
        $('#sup_id').val(id);
        $('#sup_name').val(row.find('td:eq(0)').text().trim());
        $('#sup_email').val(row.find('td:eq(1)').text().trim());
        $('#contact_person').val(row.find('td:eq(2)').text().trim());
        $('#contact_number').val(row.find('td:eq(3)').text().trim());
        $('#supplierModalTitle').text('Edit Supplier');
    }else{
        $('#supplierForm')[0].reset();
        $('#sup_id').val('');
        $('#supplierModalTitle').text('Add Supplier');
    }
}
function closeSupplierModal(){
    $('#supplierModal').addClass('hidden');
}
$('#supplierForm').on('submit', function(e){
    e.preventDefault();
    const id = $('#sup_id').val();
    const url = id ? `/purchasing/supplier/${id}` : '{{ route("supplier.store") }}';
    const method = id ? 'PUT' : 'POST';
    $.ajax({
        url: url,
        method: method,
        data: $(this).serialize(),
        success: res => {
            Swal.fire({icon:'success', title:'Saved', timer:1200, showConfirmButton:false});
            location.reload();
        },
        error: xhr => Swal.fire({icon:'error', title:'Error', text: xhr.responseJSON?.message || 'Save failed'})
    });
});
function deleteSupplier(id){
    Swal.fire({
        title: 'Delete supplier?',
        text: 'You will not be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/purchasing/supplier/${id}`,
                method: 'DELETE',
                success: () => location.reload(),
                error: () => Swal.fire({icon:'error', title:'Failed', text:'Could not delete supplier'})
            });
        }
    });
}
</script>
@endsection