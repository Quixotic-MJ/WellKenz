@extends('Inventory.layout.app')

@section('title','Stock In - WellKenz ERP')
@section('breadcrumb','Stock In')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
@endpush

@section('content')

<div class="space-y-6">
    {{-- header --}}
    <div class="bg-white border rounded p-6 flex justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Stock-In Management</h1>
            <p class="text-sm text-gray-500">Record newly received items and link to purchase orders</p>
        </div>

      {{-- Receiving Memo (optional) --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
        <div class="md:col-span-1">
          <label class="block text-xs text-gray-600 mb-1">Receiving Memo (optional)</label>
          <select id="memoRefSelect" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">- none -</option>
            @foreach($memoOptions as $m)
              <option value="{{ $m->memo_ref }}">
                {{ $m->memo_ref }} @if($m->po_ref) - PO: {{ $m->po_ref }} @endif - {{ \Carbon\Carbon::parse($m->received_date)->format('M d, Y') }} @if($m->received_by_name) - {{ $m->received_by_name }} @endif
              </option>
            @endforeach
          </select>
          <p class="text-xs text-gray-500 mt-1">If selected, the memo ref will be prefixed to each transaction's remarks.</p>
        </div>
      </div>
        <div class="text-right">
            <p class="text-sm text-gray-900 font-medium">{{ now()->format('F j, Y') }}</p>
            <p class="text-xs text-gray-500">{{ now()->format('l') }}</p>
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
        $cards = [
            ['label'=>"Today's Receipts", 'value'=>$kpi->today_rcpt, 'icon'=>'truck-loading', 'color'=>'gray'],
            ['label'=>'Completed This Week', 'value'=>$kpi->week_rcpt, 'icon'=>'clipboard-check', 'color'=>'gray'],
            ['label'=>'Pending Receipts', 'value'=>$kpi->pending_rcpt, 'icon'=>'clock', 'color'=>'yellow'],
            ['label'=>'Overdue Deliveries', 'value'=>$kpi->overdue_rcpt, 'icon'=>'exclamation-circle', 'color'=>'red'],
        ];
        @endphp
        @foreach($cards as $c)
        <div class="bg-white border rounded p-5 flex justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ $c['label'] }}</p>
                <p class="text-2xl font-semibold mt-2">{{ $c['value'] }}</p>
            </div>
            <div class="w-10 h-10 bg-{{ $c['color'] }}-100 flex items-center justify-center rounded">
                <i class="fas fa-{{ $c['icon'] }} text-{{ $c['color'] }}-600"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- transactions table --}}
        <div class="lg:col-span-3 bg-white border rounded p-6">
            <div class="flex justify-between mb-4">
                <h3 class="text-lg font-semibold">Recent Stock-In Transactions</h3>
                <button onclick="window.openStockInModal()"
                        class="px-4 py-2 bg-gray-900 text-white text-sm rounded hover:bg-gray-800">
                    <i class="fas fa-plus-circle mr-2"></i>New Stock-In
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Transaction & Item</th>
                        <th class="text-left">Category</th>
                        <th class="text-left">Quantity</th>
                        <th class="text-left">Date</th>
                        <th class="text-left">PO / Supplier</th>
                        <th class="text-left">Recorded By</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3">
                                <div class="font-medium">{{ $t->trans_ref }}</div>
                                <div class="text-xs text-gray-500">{{ $t->item->item_name }}</div>
                            </td>
                            <td>{{ $t->item->category->cat_name ?? '-' }}</td>
                            <td>{{ number_format($t->trans_quantity,3) }} {{ $t->item->item_unit }}</td>
                            <td>{{ $t->trans_date->format('M d, Y') }}</td>
                            <td>
                                @if($t->purchaseOrder)
                                    <div class="text-xs">{{ $t->purchaseOrder->po_ref }}</div>
                                    <div class="text-xs text-gray-500">{{ $t->purchaseOrder->supplier->sup_name }}</div>
                                @else
                                    <span class="text-xs text-gray-400">Manual</span>
                                @endif
                            </td>
                            <td>{{ $t->user->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-500">No stock-in records found</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>

        {{-- sidebar --}}
        <div class="bg-white border rounded p-6 space-y-6">
            <div>
                <h4 class="text-sm font-semibold mb-3">Pending Deliveries</h4>
                @forelse($pendingPOs as $po)
                    <div class="flex justify-between items-center py-2 border-b">
                        <div>
                            <div class="text-sm font-medium">{{ $po->po_ref }}</div>
                            <div class="text-xs text-gray-500">{{ $po->supplier->sup_name }}</div>
                        </div>
                        <div class="text-xs text-gray-500">{{ $po->expected_delivery_date->format('M d') }}</div>
                    </div>
                @empty
                    <div class="text-xs text-gray-500">No pending deliveries</div>
                @endforelse
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3 text-red-600">Overdue Deliveries</h4>
                @forelse($overduePOs as $po)
                    <div class="flex justify-between items-center py-2 border-b">
                        <div>
                            <div class="text-sm font-medium">{{ $po->po_ref }}</div>
                            <div class="text-xs text-gray-500">{{ $po->supplier->sup_name }}</div>
                        </div>
                        <div class="text-xs text-red-600">{{ $po->expected_delivery_date->format('M d') }}</div>
                    </div>
                @empty
                    <div class="text-xs text-gray-500">No overdue deliveries</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ==============  BULK MODAL  ============== --}}
<div id="stockInModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
  <div class="bg-white rounded p-6 w-full max-w-5xl">
    <form id="stockInForm" class="space-y-4">
      <h3 class="text-lg font-semibold">Record New Stock-In (Bulk)</h3>

      {{-- Item category filter / PO search & filters / global PO --}}
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-1">
          <label class="block text-xs text-gray-600 mb-1">Item Category</label>
          <select id="itemCategoryFilter" class="w-full border rounded px-3 py-2 text-sm">
            <option value="all">All Categories</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->cat_id }}">{{ $cat->cat_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-1">
          <label class="block text-xs text-gray-600 mb-1">Filter PO by Ref/Supplier</label>
          <input type="text" id="poSearch" placeholder="Search PO Ref or Supplier..." class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-1">
          <label class="block text-xs text-gray-600 mb-1">Status</label>
          <select id="poStatusFilter" class="w-full border rounded px-3 py-2 text-sm">
            <option value="all">All</option>
            <option value="draft">Draft</option>
            <option value="ordered">Ordered</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>
        <div class="md:col-span-1 md:col-start-4">
          <label class="block text-xs text-gray-600 mb-1">Use One PO for All Rows</label>
          <div class="flex items-center gap-2">
            <input type="checkbox" id="useGlobalPO" class="h-4 w-4">
            <select id="globalPOSelect" class="flex-1 border rounded px-3 py-2 text-sm" disabled>
              <option value="" disabled selected>- select PO Ref -</option>
            </select>
          </div>
          <p class="text-xs text-gray-500 mt-1">Enable to apply the same PO to all rows.</p>
        </div>
      </div>

      {{-- table header --}}
      <div class="grid grid-cols-7 gap-2 text-sm font-semibold text-gray-700">
        <div>Item</div><div>Qty</div><div>Unit</div><div>Date</div><div>Expiry</div><div>PO Ref</div><div></div>
      </div>

      {{-- dynamic rows --}}
      <div id="bulkRows" class="space-y-2"></div>

      <div class="flex items-center justify-between">
        <button type="button" onclick="window.addBulkRow()"
                class="px-3 py-1 border rounded text-sm hover:bg-gray-100">+ Add row</button>
        <div class="flex space-x-3">
          <button type="button" onclick="window.closeStockInModal()"
                  class="px-4 py-2 border rounded">Cancel</button>
          <button class="px-4 py-2 bg-gray-900 text-white rounded">Save all</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script id="eligiblePOsData" type="application/json">@json($eligiblePOOptions)</script>
<script id="itemOptionsData" type="application/json">@json($itemOptions)</script>
<script id="memoOptionsData" type="application/json">@json($memoOptions)</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
/* -------- now flatpickr IS loaded -------- */
// Prepare eligible POs data for client-side search
const poDataEl = document.getElementById('eligiblePOsData');
window.allPOs = poDataEl ? JSON.parse(poDataEl.textContent) : [];
window.filteredPOs = window.allPOs.slice();
// Prepare item options for client-side category filter
const itemDataEl = document.getElementById('itemOptionsData');
window.allItems = itemDataEl ? JSON.parse(itemDataEl.textContent) : [];
window.filteredItems = window.allItems.slice();
// Prepare memo options map
const memoDataEl = document.getElementById('memoOptionsData');
window.allMemos = memoDataEl ? JSON.parse(memoDataEl.textContent) : [];
window.memoByRef = {};
for (const m of window.allMemos){ if (m.memo_ref) window.memoByRef[m.memo_ref] = m; }

window.renderPOOptions = function(selectEl, list, selectedId){
  const placeholder = '<option value="" disabled selected>- select PO Ref -</option>';
  const opts = list.map(po => `<option value="${po.id}" ${selectedId && String(selectedId)===String(po.id) ? 'selected' : ''}>${po.ref}${po.supplier ? ' - '+po.supplier : ''}</option>`).join('');
  selectEl.innerHTML = placeholder + opts;
}

const poSearch = document.getElementById('poSearch');
const statusFilter = document.getElementById('poStatusFilter');
const itemCategoryFilter = document.getElementById('itemCategoryFilter');
function applyPOFilters(){
  const q = (poSearch?.value || '').toLowerCase();
  const status = statusFilter ? statusFilter.value : 'all';
  window.filteredPOs = window.allPOs.filter(po => {
    const matchesText = !q || (po.ref && po.ref.toLowerCase().includes(q)) || (po.supplier && po.supplier.toLowerCase().includes(q));
    const matchesStatus = status === 'all' || po.status === status;
    return matchesText && matchesStatus;
  });

// ---- Client-side validation: memo_ref must match selected PO ----
function validateMemoMatchesPO(){
  const memoRef = document.getElementById('memoRefSelect')?.value || '';
  if (!memoRef) return true; // no memo, nothing to validate
  const memo = window.memoByRef[memoRef];
  if (!memo) return true; // unknown memo; let server validate
  // derive selected PO ids in rows
  const poIds = Array.from(document.querySelectorAll('select.po-select')).map(sel => sel.value).filter(Boolean);
  const uniquePoIds = Array.from(new Set(poIds));
  if (uniquePoIds.length !== 1){
    alert('Receiving Memo can only be used when all rows share the same PO. Please enable "Use One PO for All Rows" or make all rows use the same PO.');
    return false;
  }
  const poId = uniquePoIds[0];
  const po = (window.allPOs||[]).find(p => String(p.id)===String(poId));
  const poRef = po ? po.ref : '';
  if (memo.po_ref && poRef && String(memo.po_ref) !== String(poRef)){
    alert('Selected Receiving Memo does not belong to the selected PO. Please choose a memo that matches the PO.');
    return false;
  }
  return true;
}

// hook validation on memoRef change
const memoRefSelect = document.getElementById('memoRefSelect');
if (memoRefSelect){ memoRefSelect.addEventListener('change', validateMemoMatchesPO); }
  // Re-render global select
  const globalSel = document.getElementById('globalPOSelect');
  if (globalSel) {
    const currentGlobal = globalSel.value || '';
    window.renderPOOptions(globalSel, window.filteredPOs, currentGlobal);
  }
  // Re-render all row selects
  const useGlobal = document.getElementById('useGlobalPO')?.checked;
  const selectedGlobal = document.getElementById('globalPOSelect')?.value || '';
  document.querySelectorAll('select.po-select').forEach(sel => {
    window.renderPOOptions(sel, window.filteredPOs, useGlobal ? selectedGlobal : sel.value);
    if (useGlobal && selectedGlobal) { sel.value = selectedGlobal; sel.disabled = true; }
  });
}
if (poSearch) poSearch.addEventListener('input', applyPOFilters);
if (statusFilter) statusFilter.addEventListener('change', applyPOFilters);

function applyItemFilters(){
  const cat = itemCategoryFilter ? itemCategoryFilter.value : 'all';
  window.filteredItems = window.allItems.filter(it => cat==='all' || String(it.cat_id)===String(cat));
  // update all item selects in rows
  document.querySelectorAll('.bulk-row').forEach(row => {
    const itemSel = row.querySelector('select[name="item_id[]"]');
    const current = itemSel.value || '';
    renderItemOptions(itemSel, window.filteredItems, current);
    // trigger category label update
    itemSel.dispatchEvent(new Event('change'));
  });
}
if (itemCategoryFilter) itemCategoryFilter.addEventListener('change', applyItemFilters);

window.renderItemOptions = function(selectEl, list, selectedId){
  const placeholder = '<option value="">- select -</option>';
  const opts = list.map(it => `<option value="${it.id}" ${selectedId && String(selectedId)===String(it.id) ? 'selected' : ''}>${it.code} - ${it.name}</option>`).join('');
  selectEl.innerHTML = placeholder + opts;
}

// Global PO toggle logic
const useGlobalPO = document.getElementById('useGlobalPO');
const globalPOSelect = document.getElementById('globalPOSelect');
if (globalPOSelect) {
  // initial render of global select from filtered list
  window.renderPOOptions(globalPOSelect, window.filteredPOs, '');
}
if (useGlobalPO) {
  useGlobalPO.addEventListener('change', function(){
    const enabled = this.checked;
    globalPOSelect.disabled = !enabled;
    const selectedGlobal = globalPOSelect.value || '';
    document.querySelectorAll('select.po-select').forEach(sel => {
      sel.disabled = enabled && !!selectedGlobal;
      if (enabled && selectedGlobal) sel.value = selectedGlobal;
    });
  });
}
if (globalPOSelect) {
  globalPOSelect.addEventListener('change', function(){
    const selectedGlobal = this.value || '';
    if (useGlobalPO?.checked) {
      document.querySelectorAll('select.po-select').forEach(sel => {
        sel.value = selectedGlobal;
        sel.disabled = !!selectedGlobal;
      });
    }
    // run memo/po validation when global changes
    validateMemoMatchesPO();
  });
}
window.addBulkRow = function() {
  const div = document.createElement('div');
  div.className = 'grid grid-cols-7 gap-2 items-center bulk-row';
  div.innerHTML = `
    <div>
      <select name="item_id[]" required class="border rounded px-2 py-1 text-sm w-full"></select>
      <div class="text-[11px] text-gray-500 mt-1"><span class="item-cat-label">Category: -</span></div>
    </div>
    <input type="number" step="0.001" name="quantity[]" required placeholder="0" class="border rounded px-2 py-1 text-sm">
    <select name="unit[]" required class="border rounded px-2 py-1 text-sm">
      @foreach($items as $it)
        <option value="{{ $it->item_unit }}">{{ $it->item_unit }}</option>
      @endforeach
    </select>
    <input type="date" name="trans_date[]" required value="{{ now()->format('Y-m-d') }}" class="border rounded px-2 py-1 text-sm flatpickr">
    <input type="date" name="expiry_date[]" class="border rounded px-2 py-1 text-sm flatpickr">
    <select name="po_id[]" required class="border rounded px-2 py-1 text-sm po-select">
      <option value="" disabled selected>- select PO Ref -</option>
    </select>
    <button type="button" onclick="this.closest('.bulk-row').remove()" class="text-red-600 text-sm">Remove</button>
  `;
  document.getElementById('bulkRows').appendChild(div);
  flatpickr(div.querySelectorAll('.flatpickr'), { dateFormat: 'Y-m-d' });
  // populate PO options based on current filter
  const select = div.querySelector('select.po-select');
  const useGlobal = document.getElementById('useGlobalPO')?.checked;
  const selectedGlobal = document.getElementById('globalPOSelect')?.value || '';
  window.renderPOOptions(select, window.filteredPOs || window.allPOs || [], useGlobal ? selectedGlobal : '');
  if (useGlobal && selectedGlobal) { select.value = selectedGlobal; select.disabled = true; }
  // populate Item options based on current category filter
  const itemSel = div.querySelector('select[name="item_id[]"]');
  window.renderItemOptions(itemSel, window.filteredItems || window.allItems || [], '');
  itemSel.addEventListener('change', function(){
    const it = (window.allItems||[]).find(x => String(x.id)===String(this.value));
    const catLbl = this.closest('.bulk-row').querySelector('.item-cat-label');
    if (catLbl) catLbl.textContent = `Category: ${it && it.cat_name ? it.cat_name : '-'}`;
  });
}

window.openStockInModal = function() {
    document.getElementById('stockInModal').classList.remove('hidden');
    if (document.getElementById('bulkRows').children.length === 0) window.addBulkRow();
}
window.closeStockInModal = function() {
    document.getElementById('stockInModal').classList.add('hidden');
    document.getElementById('bulkRows').innerHTML = '';
}


/* ---------- submit bulk ---------- */
document.getElementById('stockInForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const rows = Array.from(document.querySelectorAll('.bulk-row')).map(r => ({
    item_id:     r.querySelector('[name="item_id[]"]').value,
    quantity:    r.querySelector('[name="quantity[]"]').value,
    unit:        r.querySelector('[name="unit[]"]').value,
    trans_date:  r.querySelector('[name="trans_date[]"]').value,
    expiry_date: r.querySelector('[name="expiry_date[]"]').value,
    po_id:       r.querySelector('[name="po_id[]"]').value,
    remarks:     ''
  }));

  const res = await fetch("{{ route('inventory.stock-in.store-bulk') }}", {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
    body: JSON.stringify({ rows, memo_ref: (document.getElementById('memoRefSelect')?.value || null) })
  }).then(r => r.json());

  if (res.success) { location.reload(); }
  else { alert(res.message); }
});

});
</script>
@endpush