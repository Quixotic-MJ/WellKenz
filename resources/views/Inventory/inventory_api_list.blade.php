@extends('Inventory.layout.app')

@section('title','Inventory List (API)')
@section('breadcrumb','Inventory List (API)')

@section('content')
<div class="space-y-6">
  <div class="bg-white border rounded p-6">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold">Inventory (via API)</h3>
      <div class="flex gap-2">
        <input id="q" placeholder="Search name/code" class="border rounded px-3 py-2 text-sm">
        <select id="status" class="border rounded px-3 py-2 text-sm">
          <option value="">All</option>
          <option value="low">Low</option>
          <option value="out">Out</option>
        </select>
        <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded" onclick="loadList()">Search</button>
      </div>
    </div>
  </div>

  <div class="bg-white border rounded p-6">
    <div id="listContainer" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
          <tr>
            <th class="px-3 py-2 text-left">Code</th>
            <th class="px-3 py-2 text-left">Name</th>
            <th class="px-3 py-2 text-left">Category</th>
            <th class="px-3 py-2 text-left">Stock</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Status</th>
          </tr>
        </thead>
        <tbody id="rows" class="divide-y divide-gray-200">
          <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">Use the search to load items</td></tr>
        </tbody>
      </table>
      <div class="mt-4 flex justify-between items-center">
        <button id="prevBtn" class="px-3 py-1 border rounded" disabled onclick="prevPage()">Prev</button>
        <span id="pageInfo" class="text-sm text-gray-600">Page 1</span>
        <button id="nextBtn" class="px-3 py-1 border rounded" disabled onclick="nextPage()">Next</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let page = 1;

function statusBadge(stock, reorder) {
  let c='bg-green-100 text-green-700', t='Normal';
  if (stock <= 0) { c='bg-red-100 text-red-700'; t='Out'; }
  else if (reorder != null && stock <= reorder) { c='bg-amber-100 text-amber-700'; t='Low'; }
  return '<span class="px-2 py-1 rounded text-xs '+c+'">'+t+'</span>';
}

async function loadList() {
  const q = document.getElementById('q').value.trim();
  const status = document.getElementById('status').value;
  const params = new URLSearchParams({ page });
  if (q) params.set('search', q);
  if (status) params.set('status', status);

  const res = await fetch('/api/inventory/list?' + params.toString(), { headers: { 'Accept': 'application/json' } });
  const data = await res.json();

  const rows = document.getElementById('rows');
  rows.innerHTML = '';
  const items = data.data || data || [];
  if (!items.length) {
    rows.innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No results</td></tr>';
  } else {
    items.forEach(it => {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td class="px-3 py-2">'+ (it.item_code || '-') +'</td>' +
        '<td class="px-3 py-2">'+ (it.item_name || '-') +'</td>' +
        '<td class="px-3 py-2">'+ (it.cat_name || '-') +'</td>' +
        '<td class="px-3 py-2">'+ (it.item_stock ?? 0) +'</td>' +
        '<td class="px-3 py-2">'+ (it.item_unit || '-') +'</td>' +
        '<td class="px-3 py-2">'+ statusBadge(it.item_stock ?? 0, it.reorder_level ?? null) +'</td>';
      rows.appendChild(tr);
    });
  }

  const pi = document.getElementById('pageInfo');
  const prev = document.getElementById('prevBtn');
  const next = document.getElementById('nextBtn');
  const meta = data.meta || {};
  const current = meta.current_page || page;
  const last = meta.last_page || current;

  page = current;
  pi.textContent = 'Page ' + current + (last ? (' of ' + last) : '');
  prev.disabled = current <= 1;
  next.disabled = last && current >= last;
}

function prevPage(){ if (page > 1){ page -= 1; loadList(); } }
function nextPage(){ page += 1; loadList(); }
</script>
@endpush