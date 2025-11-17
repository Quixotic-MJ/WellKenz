@extends('Purchasing.layout.app')

@section('title', 'Purchasing Overview - WellKenz ERP')
@section('breadcrumb', 'Purchasing Overview')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Purchasing Overview</h1>
                <p class="text-sm text-gray-500 mt-1">Quick visibility on all procurement activities</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">{{ date('F j, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    <!-- 2. live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- approved requisitions waiting PO -->
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Approved Reqs</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $approvedReqs }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting PO creation</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-clipboard-check text-gray-600"></i>
                </div>
            </div>
        </div>

        <!-- purchase-order statuses -->
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Draft POs</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $draftPOs }}</p>
                    <p class="text-xs text-gray-400 mt-1">Not yet ordered</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-file-alt text-gray-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-blue-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Ordered</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $orderedPOs }}</p>
                    <p class="text-xs text-gray-400 mt-1">With suppliers</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-truck text-gray-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white border border-green-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Delivered</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $deliveredPOs }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting stock-in</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-box text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. quick alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- overdue POs -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Overdue Deliveries</h3>
            <div class="space-y-3">
                @forelse($overdue as $po)
                    <div class="p-3 border-l-4 border-rose-500 bg-rose-50 rounded">
                        <p class="text-sm font-medium text-gray-900">
                            PO-{{ $po->po_ref }} – ₱ {{ number_format($po->total_amount ?? 0,2) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Expected: {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No overdue deliveries – great job!</p>
                @endforelse
            </div>
        </div>

        <!-- delivered awaiting stock-in -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivered Awaiting Stock-In</h3>
            <div class="space-y-3">
                @forelse($awaiting as $po)
                    <div class="p-3 border-l-4 border-blue-500 bg-blue-50 rounded">
                        <p class="text-sm font-medium text-gray-900">
                            PO-{{ $po->po_ref }} – ₱ {{ number_format($po->total_amount ?? 0,2) }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            Delivered: {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">All delivered POs have been stocked-in.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. recent POs -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recently Created Purchase Orders</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="recentPOTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PO Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="recentPOTableBody">
                    @foreach($recentPOs as $po)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">PO-{{ $po->po_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->supplier->sup_name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($po->po_status=='draft') bg-gray-100 text-gray-700
                                @elseif($po->po_status=='ordered') bg-blue-100 text-blue-700
                                @elseif($po->po_status=='delivered') bg-green-100 text-green-700
                                @elseif($po->po_status=='cancelled') bg-rose-100 text-rose-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($po->po_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">₱ {{ number_format($po->total_amount,2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $po->created_at ? \Carbon\Carbon::parse($po->created_at)->format('M d, Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing {{ $recentPOs->count() }} of {{ $totalPOs }} purchase orders
        </div>
    </div>

    <!-- 5. supplier snapshot -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Supplier Snapshot</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Suppliers</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalSuppliers }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Active This Month</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $activeSuppliersThisMonth }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Top Supplier (Qty)</p>
                <p class="text-sm font-semibold text-gray-900 mt-2">
                    {{ $topSupplierQtyName ?? '-' }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Top Supplier (Value)</p>
                <p class="text-sm font-semibold text-gray-900 mt-2">
                    {{ $topSupplierValName ?? '-' }}
                </p>
            </div>
        </div>
    </div>

    <!-- 6. PO-related notifications -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent PO Notifications</h3>
        <div class="space-y-3">
            @forelse($notifs as $n)
                <div class="p-3 border border-gray-200 rounded hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900">{{ $n->notif_title }}</p>
                        <span class="text-xs text-gray-500">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">{{ $n->notif_content }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-500">No new notifications.</p>
            @endforelse
        </div>
    </div>

</div>

<script>
/* toast helper */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
</script>
@endsection