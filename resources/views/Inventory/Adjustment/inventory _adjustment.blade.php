@extends('Inventory.layout.app')

@section('title','Inventory Adjustments – WellKenz ERP')
@section('breadcrumb','Inventory Adjustments')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Inventory Adjustments</h1>
                <p class="text-sm text-gray-500 mt-1">Correct mistakes or record spoilage / damage – always logged</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inventory.transactions.index') }}"
                   class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                    <i class="fas fa-list mr-2"></i>All Transactions
                </a>
            </div>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Adjustments</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('inventory_transactions')->where('trans_type','adjustment')->count() }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Positive Adj (MTD)</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ number_format(DB::table('inventory_transactions')->where('trans_type','adjustment')->where('trans_quantity','>',0)->whereMonth('created_at',now()->month)->sum('trans_quantity')) }}
            </p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Negative Adj (MTD)</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ number_format(abs(DB::table('inventory_transactions')->where('trans_type','adjustment')->where('trans_quantity','<',0)->whereMonth('created_at',now()->month)->sum('trans_quantity'))) }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">
                {{ DB::table('inventory_transactions')->where('trans_type','adjustment')->whereBetween('created_at',[now()->startOfWeek(),now()->endOfWeek()])->count() }}
            </p>
        </div>
    </div>

    <!-- 3. quick adjustment card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Adjustment</h2>
        <form id="adjustForm" method="POST" action="{{ route('inventory.adjustments.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item <span class="text-rose-500">*</span></label>
                    <select name="item_id" required id="itemSelect" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"
                            onchange="loadItemDetails(this.value)">
                        <option value="">-- choose item --</option>
                        @foreach($items as $i)
                            <option value="{{ $i->item_id }}" data-stock="{{ $i->item_stock }}">{{ $i->item_name }} ({{ $i->item_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                    <input type="text" id="currentStock" readonly class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment <span class="text-rose-500">*</span></label>
                    <input type="number" name="adjustment" step="1" required id="adjustmentInput"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"
                           oninput="calcNewStock()">
                    <p class="text-xs text-gray-500 mt-1">Positive = add, Negative = remove</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Stock</label>
                    <input type="text" id="newStock" readonly class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-600">
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason / Remarks <span class="text-rose-500">*</span></label>
                <textarea name="remarks" rows="2" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400"></textarea>
            </div>
        </form>
    </div>

    <!-- 4. recent adjustments -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recent Adjustments</h3>
            <a href="{{ route('inventory.transactions.index',['type'=>'adjustment']) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="adjTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Adjustment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Staff</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="adjTableBody">
                    @foreach($recentAdjustments as $adj)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $adj->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $adj->item->item_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm {{ $adj->trans_quantity > 0 ? 'text-green-700' : 'text-rose-700' }} font-semibold">
                            {{ $adj->trans_quantity > 0 ? '+' : '' }}{{ $adj->trans_quantity }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $adj->remarks ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $adj->staff->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $adj->trans_ref ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing {{ $recentAdjustments->count() }} of {{ $recentAdjustments->total() }} adjustments
        </div>
    </div>

</div>

<!-- ====== CONFIRMATION MODAL  ====== -->
@include('Inventory.Inventory.confirm')

@endsection

@push('scripts')
<script>
/* ===== helpers ===== */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['confirmModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
}

/* ===== load item details ===== */
function loadItemDetails(id){
    if(!id){
        document.getElementById('currentStock').value = '';
        document.getElementById('newStock').value = '';
        return;
    }
    const opt = document.getElementById('itemSelect').selectedOptions[0];
    const stock = parseFloat(opt.dataset.stock) || 0;
    document.getElementById('currentStock').value = stock;
    calcNewStock();
}

/* ===== calculate new stock ===== */
function calcNewStock(){
    const current = parseFloat(document.getElementById('currentStock').value) || 0;
    const adj     = parseFloat(document.getElementById('adjustmentInput').value) || 0;
    const after   = current + adj;
    document.getElementById('newStock').value = after;
}

/* ===== form submit with confirmation ===== */
document.getElementById('adjustForm').addEventListener('submit', function(e){
    e.preventDefault();
    const adj = parseFloat(document.getElementById('adjustmentInput').value) || 0;
    const reason = document.querySelector('textarea[name="remarks"]').value.trim();
    if(!reason){ showMessage('Reason is required','error'); return; }
    document.getElementById('confirmAdj').textContent = adj > 0 ? '+'+adj : adj;
    document.getElementById('confirmReason').textContent = reason;
    document.getElementById('confirmModal').classList.remove('hidden');
});

/* ===== confirmed submit ===== */
function confirmSubmit(){
    fetch("{{ route('inventory.adjustments.store') }}",{
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: new FormData(document.getElementById('adjustForm'))
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(res => {
        if(res.success){
            showMessage(res.message);
            setTimeout(()=> location.reload(), 500);
        }else{
            showMessage(res.message || 'Save failed','error');
        }
    })
    .catch(() => showMessage('Server error','error'))
    .finally(()=> closeModals());
}
</script>
@endpush