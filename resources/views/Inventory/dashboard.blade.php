@extends('Inventory.layout.app')

@section('title','Inventory Overview - WellKenz ERP')
@section('breadcrumb','Inventory Overview')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. welcome / date card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                @php
                    $hour = date('H');
                    $greeting = 'Good ';
                    if ($hour < 12) { $greeting .= 'morning'; }
                    elseif ($hour < 17) { $greeting .= 'afternoon'; }
                    else { $greeting .= 'evening'; }
                @endphp
                <h1 class="text-2xl font-semibold text-gray-900">{{ $greeting }}, {{ session('emp_name') }}</h1>
                <p class="text-sm text-gray-500 mt-1">Inventory custodian overview – live health at a glance</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">{{ date('F j, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Items</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('items')->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-boxes text-gray-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Low Stock</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">
                        {{ count(DB::select('SELECT * FROM get_low_stock_items()')) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 flex items-center justify-center rounded">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Critical Stock</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">
                        {{ DB::table('items')->whereRaw('item_stock <= minimum_stock')->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-rose-100 flex items-center justify-center rounded">
                    <i class="fas fa-exclamation-circle text-rose-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-blue-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Expiring ≤ 30 d</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">
                        {{ count(DB::select('SELECT * FROM get_expiry_alerts(30)')) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-100 flex items-center justify-center rounded">
                    <i class="fas fa-calendar-times text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. today’s movement -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today’s Stock-In</h3>
            @php
                $stockIn = DB::table('inventory_transactions')
                           ->where('trans_type','in')
                           ->whereDate('created_at',today())
                           ->sum('trans_quantity');
            @endphp
            <p class="text-3xl font-bold text-green-700">{{ number_format($stockIn) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total units received</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today’s Stock-Out</h3>
            @php
                $stockOut = DB::table('inventory_transactions')
                            ->where('trans_type','out')
                            ->whereDate('created_at',today())
                            ->sum('trans_quantity');
            @endphp
            <p class="text-3xl font-bold text-rose-700">{{ number_format($stockOut) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total units issued</p>
        </div>
    </div>

    <!-- 4. pending deliveries -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Deliveries (Ordered)</h3>
        @php
            $pending = DB::table('purchase_orders')
                       ->where('po_status','ordered')
                       ->select('po_ref','sup_id as supplier_id','expected_delivery_date as delivery_date','total_amount as po_total')
                       ->orderBy('expected_delivery_date')
                       ->limit(5)
                       ->get();
        @endphp
        <div class="space-y-3">
            @forelse($pending as $po)
                <div class="p-3 border-l-4 border-blue-500 bg-blue-50 rounded">
                    <p class="text-sm font-medium text-gray-900">PO-{{ $po->po_ref }} – ₱ {{ number_format($po->po_total,2) }}</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Expected: {{ \Carbon\Carbon::parse($po->delivery_date)->format('M d, Y') }}
                    </p>
                </div>
            @empty
                <p class="text-xs text-gray-500">No pending deliveries.</p>
            @endforelse
        </div>
    </div>

    <!-- 5. alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- overdue deliveries -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Overdue Deliveries</h3>
            @php
                $overdue = DB::table('purchase_orders')
                           ->where('po_status','ordered')
                           ->whereDate('expected_delivery_date','<',today())
                           ->select('po_ref','sup_id as supplier_id','expected_delivery_date as delivery_date','total_amount as po_total')
                           ->orderBy('expected_delivery_date')
                           ->limit(5)
                           ->get();
            @endphp
            <div class="space-y-3">
                @forelse($overdue as $po)
                    <div class="p-3 border-l-4 border-rose-500 bg-rose-50 rounded">
                        <p class="text-sm font-medium text-gray-900">PO-{{ $po->po_ref }} – ₱ {{ number_format($po->po_total,2) }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Expected: {{ \Carbon\Carbon::parse($po->delivery_date)->format('M d, Y') }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No overdue deliveries – great job!</p>
                @endforelse
            </div>
        </div>

        <!-- near-expiry -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Near-Expiry Alerts (≤ 30 d)</h3>
            @php
                try {
                    $expiry = collect(DB::select('SELECT * FROM get_expiry_alerts(30)'));
                } catch (\Throwable $e) {
                    $expiry = DB::table('items')
                              ->whereNotNull('item_expire_date')
                              ->whereRaw("item_expire_date <= CURRENT_DATE + INTERVAL '30 day'")
                              ->select('item_name','item_expire_date','item_stock','item_unit')
                              ->get();
                }
                $expiry = $expiry->take(5);
            @endphp
            <div class="space-y-3">
                @forelse($expiry as $item)
                    <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded">
                        <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Expires: {{ \Carbon\Carbon::parse($item->item_expire_date)->format('M d, Y') }} • Stock: {{ $item->item_stock }} {{ $item->item_unit }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No items expiring within 30 days.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 6. recent transactions -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recent Inventory Transactions</h3>
            <a href="{{ route('inventory.transactions.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="txTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="txTableBody">
                    @foreach($recentTx as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->item->item_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $tx->trans_type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->trans_quantity }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->balance_qty ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->trans_ref ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing {{ $recentTx->count() }} of {{ $recentTx->total() }} transactions
        </div>
    </div>

</div>

<script>
/* ===== helpers ===== */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}

/* ===== search recent tx ===== */
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('#txTableBody tr'); let visible=0;
    rows.forEach(r=>{
        const ok = r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount')?.textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}
function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}

/* ===== sort ===== */
let sortField='date', sortDir='desc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('txTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.children[f==='date'?0:1].textContent, B=b.children[f==='date'?0:1].textContent;
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}
</script>
@endsection