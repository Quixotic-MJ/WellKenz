@extends('Inventory.layout.app')

@section('title','Receive Purchase Order')
@section('breadcrumb','Receiving')

@section('content')
<div class="space-y-6">
  <div class="bg-white border rounded p-6">
    <h3 class="text-lg font-semibold mb-4">Find Purchase Order</h3>
    <div class="flex gap-2 items-end">
      <div>
        <label class="text-xs text-gray-600">PO ID</label>
        <input id="poId" type="number" class="border rounded px-3 py-2" placeholder="Enter PO ID">
      </div>
      <button class="px-4 py-2 bg-gray-800 text-white rounded" onclick="loadPO()">Load</button>
      <div id="loadMsg" class="text-sm text-gray-600"></div>
    </div>
  </div>

  <div class="bg-white border rounded p-6 hidden" id="poCard">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h4 class="text-lg font-semibold">PO <span id="poRef">-</span></h4>
        <p class="text-sm text-gray-600">Supplier: <span id="poSupplier">-</span></p>
      </div>
      <div>
        <span class="px-2 py-1 rounded text-xs bg-amber-100 text-amber-700" id="poStatus">Pending</span>
      </div>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Receiving Memo Remarks (optional)</label>
      <input type="text" id="memoRemarks" class="w-full border rounded px-3 py-2" placeholder="e.g., Partial delivery; 2 boxes damaged">
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
          <tr>
            <th class="px-3 py-2 text-left">Item</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Ordered</th>
            <th class="px-3 py-2 text-left">Previously Received</th>
            <th class="px-3 py-2 text-left">Receive Now</th>
            <th class="px-3 py-2 text-left">Damaged</th>
            <th class="px-3 py-2 text-left">Remarks</th>
          </tr>
        </thead>
        <tbody id="poItems" class="divide-y divide-gray-200"></tbody>
      </table>
    </div>

    <div class="flex justify-end mt-4">
      <button class="px-4 py-2 bg-blue-700 text-white rounded" onclick="submitReceiving()">Post Receiving</button>
    </div>
  </div>

  <div id="toast" class="hidden px-4 py-2 rounded text-white"></div>
</div>
@endsection

@push('scripts')
<script>
function showToast(msg, ok=true){
  const t = document.getElementById('toast');
  t.className = 'fixed bottom-6 right-6 rounded px-4 py-2 ' + (ok? 'bg-green-600':'bg-red-600');
  t.textContent = msg; t.classList.remove('hidden');
  setTimeout(()=>t.classList.add('hidden'), 3000);
}

async function loadPO(){
  const id = document.getElementById('poId').value;
  if(!id){ showToast('Enter a valid PO ID', false); return; }
  const msg = document.getElementById('loadMsg');
  msg.textContent = 'Loading...';
  try{
    const res = await fetch('/api/stock-in/po/' + id, { headers:{ 'Accept':'application/json' } });
    const d = await res.json();
    if(!res.ok){ throw new Error(d.message || 'Failed'); }
    document.getElementById('poCard').classList.remove('hidden');
    document.getElementById('poRef').textContent = d.po_ref;
    document.getElementById('poSupplier').textContent = d.supplier || '-';
    document.getElementById('poStatus').textContent = (d.po_status || '').toUpperCase();
    const body = document.getElementById('poItems');
    body.innerHTML='';
    (d.items||[]).forEach((it,idx)=>{
      const maxRecv = Math.max(0, (it.ordered||0) - (it.received||0));
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td class="px-3 py-2">'+ (it.item_code||'-') +' - '+ (it.item_name||'-') +'</td>'+
        '<td class="px-3 py-2">'+ (it.item_unit||'-') +'</td>'+
        '<td class="px-3 py-2">'+ (it.ordered||0) +'</td>'+
        '<td class="px-3 py-2">'+ (it.received||0) +'</td>'+
        '<td class="px-3 py-2"><input type="number" step="0.001" min="0.001" max="'+maxRecv+'" class="border rounded px-2 py-1 w-28 recv-qty" data-item-id="'+it.item_id+'"></td>'+
        '<td class="px-3 py-2"><input type="checkbox" class="damaged"></td>'+
        '<td class="px-3 py-2"><input type="text" class="border rounded px-2 py-1 w-56 remarks" placeholder="Optional"></td>';
      body.appendChild(tr);
    });
  }catch(e){
    showToast(e.message || 'Unable to load PO', false);
  }finally{
    msg.textContent='';
  }
}

async function submitReceiving(){
  const id = document.getElementById('poId').value;
  const rows = [];
  const now = new Date();
  const trans_date = now.toISOString().slice(0,10);
  document.querySelectorAll('#poItems tr').forEach(tr =>{
    const qtyEl = tr.querySelector('.recv-qty');
    const qty = parseFloat(qtyEl.value);
    if(!isNaN(qty) && qty > 0){
      rows.push({
        po_id: parseInt(id),
        item_id: parseInt(qtyEl.getAttribute('data-item-id')),
        quantity: qty,
        trans_date: trans_date,
        damaged: tr.querySelector('.damaged').checked,
        remarks: tr.querySelector('.remarks').value || null
      });
    }
  });
  if(rows.length===0){ showToast('Enter at least one valid received quantity', false); return; }

  const payload = { rows: rows, memo_remarks: document.getElementById('memoRemarks').value || null };
  try{
    const res = await fetch('{{ route('stock-in.store-bulk') }}',{
      method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await res.json();
    if(!res.ok || d.success===false){ throw new Error(d.message || 'Failed to save'); }
    showToast('Receiving saved. Memo: ' + (d.memo_ref || 'N/A'));
    loadPO();
  }catch(e){ showToast(e.message || 'Save failed', false); }
}

// Auto-load PO if ?po_id= is provided
document.addEventListener('DOMContentLoaded', function(){
  const params = new URLSearchParams(window.location.search);
  const pid = params.get('po_id');
  if (pid) {
    const inp = document.getElementById('poId');
    if (inp) { inp.value = parseInt(pid); }
    loadPO();
  }
});
</script>
@endpush
