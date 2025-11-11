@extends('Purchasing.layout.app')

@section('title','Purchasing Dashboard - WellKenz ERP')
@section('breadcrumb','Purchasing Dashboard')

@php
$approvedReqs   = $approvedReqs   ?? \App\Models\Requisition::approved()->count();
$totalPOs       = $totalPOs       ?? \App\Models\PurchaseOrder::count();
$suppliers      = $suppliers      ?? \App\Models\Supplier::where('sup_status','active')->count();
$lowStock       = $lowStock       ?? \DB::table('items')
                                      ->whereRaw('item_stock <= reorder_level')
                                      ->where('is_active',true)->count();

$approvedReqsList = $approvedReqsList ?? \App\Models\Requisition::with(['requester','items.item'])
                                      ->approved()
                                      ->latest()
                                      ->limit(20)
                                      ->get();

$recentPOs        = $recentPOs        ?? \App\Models\PurchaseOrder::with(['supplier','requisition'])
                                      ->latest()
                                      ->limit(20)
                                      ->get();
@endphp

@section('content')
<div class="space-y-6">

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="kpiRow">
        @foreach([['label'=>'Approved Requisitions','value'=>$approvedReqs],
                  ['label'=>'Total Purchase Orders','value'=>$totalPOs],
                  ['label'=>'Active Suppliers','value'=>$suppliers],
                  ['label'=>'Low Stock Items','value'=>$lowStock]] as $card)
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-500">{{ $card['label'] }}</h3>
            <p class="text-2xl font-bold text-gray-900 kpi-value" data-key="{{ $card['label'] }}">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Approved Requisitions List -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Approved Requisitions</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2 text-left">Requisition ID</th>
                        <th class="px-3 py-2 text-left">Requester</th>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedReqsList as $req)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $req->req_ref }}</td>
                        <td class="px-3 py-2">{{ $req->requester->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>
                        </td>
                        <td class="px-3 py-2 text-center space-x-2">
                            {{-- NEW: View Details --}}
                            <button onclick="viewDetails({{ $req->req_id }})" class="px-2 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">View Details</button>
                            {{-- Create PO --}}
                            <button onclick="openPoModal({{ $req->req_id }})" class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Create PO</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No approved requisitions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Purchase Orders</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="poTable">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2 text-left">PO Reference</th>
                        <th class="px-3 py-2 text-left">Supplier</th>
                        <th class="px-3 py-2 text-left">Total Amount</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPOs as $po)
                    <tr class="border-t po-row" data-id="{{ $po->po_id }}">
                        <td class="px-3 py-2 po-ref">{{ $po->po_ref }}</td>
                        <td class="px-3 py-2 po-supplier">{{ $po->supplier->sup_name ?? 'N/A' }}</td>
                        <td class="px-3 py-2 po-amount">₱{{ number_format($po->total_amount, 2) }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded-full po-status {{ $po->po_status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($po->po_status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 po-date">{{ $po->created_at->format('M d, Y') }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('purchase_orders.print', $po) }}" target="_blank" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 print-btn">Print</a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyPO">
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">No purchase orders yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- =====================================================================
     DETAIL SIDE-SHEET (VIEW BEFORE CREATE PO)
====================================================================== --}}
<div id="detailSheet" class="hidden fixed inset-y-0 right-0 w-full sm:w-[420px] bg-white shadow-2xl z-50 flex flex-col">
    {{-- header --}}
    <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Requisition Details</h3>
        <button onclick="closeDetailSheet()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- scrollable body --}}
    <div class="flex-1 overflow-y-auto p-4 text-sm" id="sheetBody"></div>

    {{-- footer with Create PO button --}}
    <div class="p-4 border-t bg-gray-50">
        <button onclick="createPOFromSheet()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create PO</button>
    </div>
</div>

{{-- ======================  CREATE-PO MODAL  ====================== --}}
<div id="poModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center p-4 z-40">
    <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
        <form id="poForm">
            @csrf
            <input type="hidden" name="req_id" id="reqId">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create Purchase Order</h3>
                <button type="button" onclick="closePoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Supplier --}}
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="sup_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option value="">-- choose supplier --</option>
                        @foreach(\App\Models\Supplier::where('sup_status','active')->get() as $s)
                            <option value="{{ $s->sup_id }}">{{ $s->sup_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date" min="{{ today()->toDateString() }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>
            </div>

            {{-- Delivery Address --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                <textarea name="delivery_address" required rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
            </div>

            {{-- Items Table --}}
            <div class="mb-4">
                <table class="w-full text-sm" id="itemsTable">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left">Item</th>
                            <th class="px-3 py-2 text-left">Unit</th>
                            <th class="px-3 py-2 text-left">Qty</th>
                            <th class="px-3 py-2 text-left">Unit Price</th>
                            <th class="px-3 py-2 text-right">Sub-total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="border-t-2 font-semibold">
                            <td colspan="4" class="px-3 py-2 text-right">Total</td>
                            <td class="px-3 py-2 text-right" id="grandTotal">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closePoModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-700">Save PO</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* =========================================================
   DETAIL SIDE-SHEET
========================================================= */
let currentReqId = null;   // keep track of which req we are viewing

function viewDetails(reqId){
    currentReqId = reqId;
    $.get(`/api/requisitions/${reqId}`)
     .done(res=>{
         let html = `
            <div class="space-y-4 text-sm text-gray-700">
                <div>
                    <span class="font-semibold text-gray-900">Requisition Ref:</span><br>
                    ${res.req_ref}
                </div>
                <div>
                    <span class="font-semibold text-gray-900">Requester:</span><br>
                    ${res.requester?.name ?? 'N/A'} – ${res.requester?.position ?? ''}
                </div>
                <div>
                    <span class="font-semibold text-gray-900">Purpose:</span><br>
                    ${res.req_purpose}
                </div>
                <div>
                    <span class="font-semibold text-gray-900">Priority:</span><br>
                    <span class="px-2 py-0.5 text-xs rounded-full ${
                        res.req_priority === 'high'  ? 'bg-red-100 text-red-700' :
                        res.req_priority === 'medium'? 'bg-amber-100 text-amber-700' :
                        'bg-gray-100 text-gray-700'}">${res.req_priority.toUpperCase()}</span>
                </div>
                <div>
                    <span class="font-semibold text-gray-900">Items:</span>
                    <table class="w-full text-xs mt-2 border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-1 text-left">Item</th>
                                <th class="px-2 py-1 text-left">Unit</th>
                                <th class="px-2 py-1 text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${res.items.map(i=>`
                                <tr class="border-t">
                                    <td class="px-2 py-1">${i.item.item_name}</td>
                                    <td class="px-2 py-1">${i.item.item_unit}</td>
                                    <td class="px-2 py-1 text-right">${i.req_item_quantity}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>`;
         $('#sheetBody').html(html);
         $('#detailSheet').removeClass('hidden');
     })
     .fail(()=>Swal.fire({icon:'error',title:'Unable to load details'}));
}
function closeDetailSheet(){
    $('#detailSheet').addClass('hidden');
    $('#sheetBody').empty();
}
function createPOFromSheet(){
    closeDetailSheet();
    openPoModal(currentReqId);
}

/* =========================================================
   ORIGINAL MODAL FUNCTIONS (unchanged)
========================================================= */
function openPoModal(reqId){
    $('#reqId').val(reqId);
    loadReqItems(reqId);
    $('#poModal').removeClass('hidden');
}
function closePoModal(){
    $('#poModal').addClass('hidden');
    $('#poForm')[0].reset();
    $('#itemsTable tbody').empty();
}
function loadReqItems(reqId){
    $.get(`/api/requisitions/${reqId}`)
        .done(res=>{
            let rows='';
            res.items.forEach((ri,i)=>{
                rows+=`
                <tr>
                    <td class="px-3 py-2">${ri.item.item_name}</td>
                    <td class="px-3 py-2">${ri.item.item_unit}</td>
                    <td class="px-3 py-2">${ri.req_item_quantity}</td>
                    <td class="px-3 py-2">
                        <input type="number" name="items[${i}][unit_price]" step="0.01" min="0.01" required
                               class="w-full border border-gray-300 rounded px-2 py-1 price-input">
                        <input type="hidden" name="items[${i}][item_id]" value="${ri.item_id}">
                        <input type="hidden" name="items[${i}][quantity]" value="${ri.req_item_quantity}">
                    </td>
                    <td class="px-3 py-2 text-right subtotal">0.00</td>
                </tr>`;
            });
            $('#itemsTable tbody').html(rows);
            attachCalc();
        })
        .fail(()=>Swal.fire({icon:'error',title:'Unable to load items'}));
}
function attachCalc(){
    $('.price-input').on('input', function(){
        let row=$(this).closest('tr');
        let qty=parseFloat(row.find('input[name$="[quantity]"]').val())||0;
        let prc=parseFloat($(this).val())||0;
        let sub=(qty*prc).toFixed(2);
        row.find('.subtotal').text(sub);

        let grand=0;
        $('.subtotal').each(function(){ grand+=parseFloat($(this).text())||0; });
        $('#grandTotal').text(grand.toFixed(2));
    });
}

/* ---------- submit PO ---------- */
$('#poForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: "{{ route('purchase_orders.store') }}",
        method: "POST",
        data: $(this).serialize(),
        success: res=>{
            if(res.success){
                closePoModal();
                // If backend returns redirect for print, follow it; else update table
                if (res.redirect){ window.location = res.redirect; return; }
                if (res.po){ addPoToTable(res.po); }
                refreshKPI();
                Swal.fire({icon:'success', title:'PO Created', text:'You may print it whenever you are ready.', timer:1500, showConfirmButton:false});
            }
        },
        error: xhr=> Swal.fire({icon:'error', title:'Error', text: xhr.responseJSON?.message || 'Save failed'})
    });
});
function addPoToTable(po){
    $('#emptyPO').remove();
    const row=`
    <tr class="border-t po-row" data-id="${po.po_id}">
        <td class="px-3 py-2 po-ref">${po.po_ref}</td>
        <td class="px-3 py-2 po-supplier">${po.supplier?.sup_name ?? 'N/A'}</td>
        <td class="px-3 py-2 po-amount">₱${Number(po.total_amount).toLocaleString('en',{minimumFractionDigits:2})}</td>
        <td class="px-3 py-2"><span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700">Ordered</span></td>
        <td class="px-3 py-2 po-date">${new Date(po.created_at).toLocaleDateString('en-MY',{month:'short',day:'numeric',year:'numeric'})}</td>
        <td class="px-3 py-2 text-center">
            <a href="/purchase-orders/print/${po.po_id}" target="_blank" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 print-btn">Print</a>
        </td>
    </tr>`;
    $('#poTable tbody').prepend(row);
}
function refreshKPI(){
    $.getJSON("{{ route('purchase_orders.api.kpi') }}")
     .done(data=>{
         $('.kpi-value[data-key="Total Purchase Orders"]').text(data.totalPOs);
         $('.kpi-value[data-key="Approved Requisitions"]').text(data.approvedReqs);
     });
}
</script>
@endpush