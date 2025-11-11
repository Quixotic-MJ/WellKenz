@extends('Inventory.layout.app')

@section('title', 'Stock Out / AR - WellKenz ERP')
@section('breadcrumb', 'Stock Out / Acknowledgement Receipt')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')
<div class="space-y-6">
  <div id="arSuccess" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
  <div id="arError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

  <div class="bg-white border rounded p-6">
    <h3 class="text-lg font-semibold mb-4">Create Acknowledgement Receipt (AR)</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm text-gray-700 mb-1">Issued To (User ID)</label>
        <input type="number" id="issued_to" class="w-full border rounded px-3 py-2" placeholder="Enter recipient user_id" />
        <p class="text-xs text-gray-500 mt-1">Enter the Employee’s user_id (recipient)</p>
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Linked Requisition (optional)</label>
        <input type="number" id="req_id" class="w-full border rounded px-3 py-2" placeholder="Enter requisition ID (optional)" />
      </div>
      <div>
        <label class="block text-sm text-gray-700 mb-1">Remarks (optional)</label>
        <input type="text" id="ar_remarks" class="w-full border rounded px-3 py-2" placeholder="e.g., Issue to baker team" />
      </div>
    </div>

    <div class="mt-6">
      <div class="flex items-center justify-between mb-2">
        <h4 class="font-semibold">Items to Issue</h4>
        <button type="button" class="px-3 py-1 bg-gray-800 text-white rounded text-sm" onclick="addRow()">
          <i class="fa fa-plus mr-1"></i> Add Row
        </button>
      </div>
      <div class="space-y-2" id="rows"></div>
    </div>

    <div class="mt-6 flex justify-end">
      <button type="button" class="px-5 py-2 bg-blue-700 text-white rounded" onclick="submitAR()">
        <i class="fa fa-paper-plane mr-2"></i> Submit AR
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { addRow(); });

function addRow() {
  const rows = document.getElementById('rows');
  const div = document.createElement('div');
  div.className = 'grid grid-cols-1 md:grid-cols-4 gap-2 items-end border p-3 rounded';

  div.innerHTML = `
    <div>
      <label class="block text-xs text-gray-600 mb-1">Item (search)</label>
      <input type="text" class="border rounded px-2 py-2 w-full" placeholder="Type to search..." oninput="debouncedSearchItems(this)" />
      <select class="mt-2 border rounded px-2 py-2 w-full item-select">
        <option value="">- select item -</option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Quantity</label>
      <input type="number" min="1" value="1" class="border rounded px-2 py-2 w-full qty-input" />
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Unit</label>
      <input type="text" class="border rounded px-2 py-2 w-full unit-display" placeholder="auto" readonly />
    </div>
    <div class="flex justify-end">
      <button type="button" class="px-3 py-2 bg-red-600 text-white rounded text-sm" onclick="this.closest('.grid').remove()">Remove</button>
    </div>
  `;

  rows.appendChild(div);
}

let searchTimer = null;
function debouncedSearchItems(inp) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => searchItems(inp), 300);
}

async function searchItems(inp) {
  const q = inp.value.trim();
  const parent = inp.closest('.grid');
  const select = parent.querySelector('select.item-select');
  const unitDisplay = parent.querySelector('input.unit-display');

  try {
    const params = new URLSearchParams();
    if (q) params.set('search', q);
    const res = await fetch('/api/inventory/list?' + params.toString(), { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    const items = (data && data.data) || data || [];

    select.innerHTML = '<option value="">- select item -</option>';
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = it.item_id;
      opt.textContent = `${it.item_code} - ${it.item_name}`;
      opt.setAttribute('data-unit', it.item_unit);
      select.appendChild(opt);
    });

    select.onchange = function() {
      const unit = this.options[this.selectedIndex]?.getAttribute('data-unit') || '';
      unitDisplay.value = unit;
    };
  } catch (e) {
    console.error(e);
    select.innerHTML = '<option value="">Error loading</option>';
  }
}

async function submitAR() {
  const issued_to = parseInt(document.getElementById('issued_to').value);
  const req_id_val = document.getElementById('req_id').value;
  const req_id = req_id_val ? parseInt(req_id_val) : null;
  const ar_remarks = document.getElementById('ar_remarks').value;
  const rows = Array.from(document.getElementById('rows').children);

  if (!issued_to) { return showMsg('arError', 'Please enter a valid recipient user_id'); }
  if (rows.length === 0) { return showMsg('arError', 'Add at least one item row'); }

  const items = [];
  for (const r of rows) {
    const sel = r.querySelector('select.item-select');
    const qtyInp = r.querySelector('input.qty-input');
    const item_id = parseInt(sel.value);
    const qty = parseFloat(qtyInp.value);
    if (!item_id || !qty || qty <= 0) {
      return showMsg('arError', 'Each row must have a selected item and quantity > 0');
    }
    items.push({ item_id, quantity: qty });
  }

  try {
    const res = await fetch('/api/ar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      body: JSON.stringify({ issued_to, req_id, ar_remarks, items })
    });
    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.message || 'Failed creating AR');

    showMsg('arSuccess', `AR created successfully. Ref: ${data.ar_ref}`);
    document.getElementById('rows').innerHTML = '';
    addRow();
  } catch (e) {
    showMsg('arError', e.message || 'Failed creating AR');
  }
}

function showMsg(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.classList.remove('hidden');
  setTimeout(() => el.classList.add('hidden'), 5000);
}
</script>
@endpush