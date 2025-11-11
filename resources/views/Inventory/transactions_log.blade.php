@extends('Inventory.layout.app')

@section('title','Inventory Transactions Log')
@section('breadcrumb','Inventory Transactions')

@section('content')
<div class="space-y-6">
  <div class="bg-white border rounded p-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <div>
        <label class="text-xs text-gray-600">Type</label>
        <select id="type" class="w-full border rounded px-3 py-2">
          <option value="">All</option>
          <option value="IN">IN</option>
          <option value="OUT">OUT</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-600">Item Name/Code</label>
        <input id="q" class="w-full border rounded px-3 py-2" placeholder="Search item...">
      </div>
      <div>
        <label class="text-xs text-gray-600">Memo Ref</label>
        <input id="memo" class="w-full border rounded px-3 py-2" placeholder="e.g., RM-0001">
      </div>
      <div>
        <label class="text-xs text-gray-600">Date From</label>
        <input id="from" type="date" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="text-xs text-gray-600">Date To</label>
        <div class="flex gap-2">
          <input id="to" type="date" class="w-full border rounded px-3 py-2">
          <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded" onclick="loadTx()">Filter</button>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-white border rounded p-6">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
          <tr>
            <th class="px-3 py-2 text-left">Date</th>
            <th class="px-3 py-2 text-left">Type</th>
            <th class="px-3 py-2 text-left">Item</th>
            <th class="px-3 py-2 text-left">Quantity</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Memo Ref</th>
            <th class="px-3 py-2 text-left">Ref</th>
          </tr>
        </thead>
        <tbody id="txRows" class="divide-y divide-gray-200">
          <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Use the filters to load transactions</td></tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-between items-center">
      <button id="prevBtn" class="px-3 py-1 border rounded" disabled onclick="prevPage()">Prev</button>
      <span id="pageInfo" class="text-sm text-gray-600">Page 1</span>
      <button id="nextBtn" class="px-3 py-1 border rounded" disabled onclick="nextPage()">Next</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let page = 1;

async function loadTx(){
  const type = document.getElementById('type').value;
  const q    = document.getElementById('q').value.trim();
  const memo = document.getElementById('memo').value.trim();
  const from = document.getElementById('from').value;
  const to   = document.getElementById('to').value;

  const params = new URLSearchParams({ page });
  if (type) params.set('type', type);
  if (q)    params.set('search', q);
  if (memo) params.set('memo_ref', memo);
  if (from) params.set('date_from', from);
  if (to)   params.set('date_to', to);

  const res = await fetch('/api/inventory/transactions?' + params.toString(), { headers:{ 'Accept':'application/json' } });
  const data = await res.json();
  const rows = document.getElementById('txRows');
  rows.innerHTML = '';

  const items = data.data || data || [];
  if (!items.length){
    rows.innerHTML = '<tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">No results</td></tr>';
  }else{
    items.forEach(tx => {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td class="px-3 py-2">'+ (tx.created_at ? new Date(tx.created_at).toLocaleString() : '-') +'</td>' +
        '<td class="px-3 py-2">'+ (tx.trans_type || '-') +'</td>' +
        '<td class="px-3 py-2">'+ ((tx.item_code || '-') + ' - ' + (tx.item_name || '-')) +'</td>' +
        '<td class="px-3 py-2">'+ (tx.quantity ?? 0) +'</td>' +
        '<td class="px-3 py-2">'+ (tx.item_unit || '-') +'</td>' +
        '<td class="px-3 py-2">'+ (tx.memo_ref || '-') +'</td>' +
        '<td class="px-3 py-2">'+ (tx.related_ref || '-') +'</td>';
      rows.appendChild(tr);
    });
  }

  const meta = data.meta || {};
  const current = meta.current_page || page;
  const last = meta.last_page || current;
  page = current;
  document.getElementById('pageInfo').textContent = 'Page ' + current + (last ? (' of ' + last) : '');
  document.getElementById('prevBtn').disabled = current <= 1;
  document.getElementById('nextBtn').disabled = last && current >= last;
}

function prevPage(){ if (page > 1){ page -= 1; loadTx(); } }
function nextPage(){ page += 1; loadTx(); }
</script>
@endpush