@extends('Inventory.layout.app')

@section('title','Purchase Orders to Receive')
@section('breadcrumb','POs (Pending/Ordered)')

@section('content')
<div class="space-y-6">
  <div class="bg-white border rounded p-6">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold">Purchase Orders - Pending / Ordered</h3>
      <div class="flex gap-2">
        <input id="search" class="border rounded px-3 py-2 text-sm" placeholder="Search PO ref / supplier">
        <select id="status" class="border rounded px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="ordered" selected>Ordered</option>
        </select>
        <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded" onclick="loadPOs()">Search</button>
      </div>
    </div>
  </div>

  <div class="bg-white border rounded p-6">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
          <tr>
            <th class="px-3 py-2 text-left">PO Ref</th>
            <th class="px-3 py-2 text-left">Supplier</th>
            <th class="px-3 py-2 text-left">Amount</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Date</th>
            <th class="px-3 py-2 text-center">Action</th>
          </tr>
        </thead>
        <tbody id="rows" class="divide-y divide-gray-200">
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Click Search to load POs</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const INVENTORY_RECEIVING_URL = "{{ route('Inventory_Receiving') }}";
async function loadPOs(){
  const q = document.getElementById('search').value.trim().toLowerCase();
  const status = document.getElementById('status').value;
  // Basic query via server-side render in Blade would be ideal, but we can fetch via a simple endpoint using existing DB facade via lightweight API.
  // For now, we piggyback the supervisor POs query with a small custom API here (reusing /purchase-orders index is not exposed); so we hit a tiny inline endpoint via /api/stock-in/po-list
  try{
    const params = new URLSearchParams();
    if (status) params.set('status', status);
    if (q) params.set('search', q);
    const res = await fetch('/api/stock-in/po-list?'+params.toString(), { headers:{ 'Accept':'application/json' } });
    const data = await res.json();
    const rows = document.getElementById('rows');
    rows.innerHTML = '';
    const list = data || [];
    if (!list.length){ rows.innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No results</td></tr>'; return; }
    list.forEach(po =>{
      const st = (po.po_status||'').toLowerCase();
      const badge = st === 'ordered' ? 'bg-amber-100 text-amber-700' : (st==='delivered'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-700');
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td class="px-3 py-2">'+ (po.po_ref||'-') +'</td>'+
        '<td class="px-3 py-2">'+ (po.supplier||'-') +'</td>'+
        '<td class="px-3 py-2">'+ (po.total_amount ? ('₱'+Number(po.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})) : '₱0.00') +'</td>'+
        '<td class="px-3 py-2"><span class="px-2 py-1 rounded text-xs '+badge+'">'+ (po.po_status||'-') +'</span></td>'+
        '<td class="px-3 py-2">'+ (po.created_at ? new Date(po.created_at).toLocaleDateString() : '-') +'</td>'+
        '<td class="px-3 py-2 text-center">'+
          '<a href="'+ INVENTORY_RECEIVING_URL +'?po_id='+ po.po_id +'" class="px-3 py-1 bg-blue-700 text-white rounded text-xs">Receive</a>'+
        '</td>';
      rows.appendChild(tr);
    });
  }catch(e){
    console.error(e);
    document.getElementById('rows').innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-red-600">Error loading POs</td></tr>';
  }
}
// Load immediately on page open
document.addEventListener('DOMContentLoaded', function(){
  loadPOs();
});
</script>
@endpush
