@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1.  Welcome card (unchanged look, text tweaked)  --}}
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
                <p class="text-sm text-gray-500 mt-1">Admin overview – everything at a glance</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">{{ date('F j, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    {{-- 2.  MASTER COUNTS (NO EMPLOYEES)  --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('users')->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Active system accounts</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-users text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Total Suppliers --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Suppliers</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('suppliers')->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Registered vendors</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-truck text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Total Items / SKU --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Items (SKU)</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('items')->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Active items</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-boxes text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Pending Requisitions (kept as useful KPI) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Requisitions</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ DB::table('requisitions')->where('req_status','pending')->count() }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-clock text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3.  REQUISITION & PO SNAPSHOT  --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Requisition Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Requisition Summary</h3>
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-600">Pending</span>
                <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-medium rounded">{{ DB::table('requisitions')->where('req_status','pending')->count() }}</span>
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-600">Approved</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">{{ DB::table('requisitions')->where('req_status','approved')->count() }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Rejected</span>
                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">{{ DB::table('requisitions')->where('req_status','rejected')->count() }}</span>
            </div>
        </div>

        {{-- Purchase Order Statistics --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Purchase Order Stats</h3>
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-600">Draft</span>
                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded">{{ DB::table('purchase_orders')->where('po_status','draft')->count() }}</span>
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-600">Ordered</span>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">{{ DB::table('purchase_orders')->where('po_status','ordered')->count() }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Delivered</span>
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">{{ DB::table('purchase_orders')->where('po_status','delivered')->count() }}</span>
            </div>
        </div>
    </div>

    {{-- 4.  ALERTS & SHORTCUTS  --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Low-Stock Alerts --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Low-Stock Alerts</h3>
            @php
                // Prefer stored function if available; fallback to simple query
                try {
                    $lowStock = collect(DB::select('SELECT * FROM get_low_stock_items()'));
                } catch (\Throwable $e) {
                    $lowStock = DB::table('items')
                                  ->whereColumn('item_stock','<=','reorder_level')
                                  ->select('item_name as name','item_stock as current_stock','item_unit as unit','reorder_level')
                                  ->get();
                }
                $lowStock = $lowStock->take(4);
            @endphp
            @forelse($lowStock as $item)
                <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded mb-3">
                    <p class="text-sm font-medium text-gray-900">{{ $item->name ?? $item->item_name ?? '-' }}</p>
                    <p class="text-xs text-gray-600 mt-1">Stock: {{ $item->current_stock ?? $item->item_stock }} {{ $item->unit ?? $item->item_unit }} • Re-order: {{ $item->reorder_level }} {{ $item->unit ?? $item->item_unit }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-500">No low-stock items – you're all set!</p>
            @endforelse
        </div>

        {{-- Near-Expiry Alerts --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Near-Expiry Alerts</h3>
            @php
                try {
                    $expiry = collect(DB::select('SELECT * FROM get_expiry_alerts(?)', [30]));
                } catch (\Throwable $e) {
                    $expiry = DB::table('items')
                              ->whereNotNull('item_expire_date')
                              ->whereRaw("item_expire_date <= CURRENT_DATE + INTERVAL '30 day'")
                              ->select('item_name','item_expire_date as expiry_date')
                              ->get();
                }
                $expiry = $expiry->take(4);
            @endphp
            @forelse($expiry as $lot)
                <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded mb-3">
                    <p class="text-sm font-medium text-gray-900">{{ $lot->item_name ?? '-' }}</p>
                    <p class="text-xs text-gray-600 mt-1">Expires: {{ \Carbon\Carbon::parse(($lot->item_expire_date ?? $lot->expiry_date))->format('M d, Y') }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-500">No items expiring within 30 days.</p>
            @endforelse
        </div>

        {{-- Quick Admin Shortcuts --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('Admin_Requisition') }}" class="block w-full px-4 py-2.5 bg-gray-900 text-white hover:bg-gray-800 transition text-center text-sm font-medium rounded">
                    <i class="fas fa-plus-circle mr-2"></i>New Requisition
                </a>
                <a href="{{ route('Admin_Purchase_Order') }}" class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                    <i class="fas fa-shopping-cart mr-2"></i>Purchase Orders
                </a>
                <a href="{{ route('Admin_Item_Management') }}" class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                    <i class="fas fa-warehouse mr-2"></i>Inventory Check
                </a>
                <a href="{{ route('Admin_Report') }}" class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                    <i class="fas fa-chart-bar mr-2"></i>View Reports
                </a>
            </div>
        </div>
    </div>

    {{-- 5.  LATEST TRANSACTIONS & ACTIVITIES  --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Latest Stock Transactions --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Latest Stock Transactions</h3>
                <a href="{{ route('Admin_Inventory_Transaction') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
            </div>
            @php
                $txns = DB::table('inventory_transactions as t')
                        ->join('items as i','i.item_id','=','t.item_id')
                        ->select('i.item_name as name','t.trans_type as type','t.trans_quantity as quantity','t.created_at')
                        ->orderByDesc('t.created_at')
                        ->limit(5)
                        ->get();
            @endphp
            <div class="space-y-3">
                @foreach($txns as $t)
                    <div class="p-3 border border-gray-200 rounded">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">{{ $t->name }}</p>
                            <span class="text-xs px-2 py-1 rounded
                                {{ ($t->type=='in') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ($t->type=='in') ? 'Stock-In' : 'Stock-Out' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Qty: {{ $t->quantity }} • {{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
            @php
                $acts = DB::table('notifications')
                      ->orderByDesc('created_at')
                      ->limit(5)
                      ->get();
            @endphp
            <div class="space-y-4">
                @foreach($acts as $act)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-info-circle text-gray-600 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 font-medium">{{ $act->notif_title }}</p>
                            <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $act->notif_content }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
