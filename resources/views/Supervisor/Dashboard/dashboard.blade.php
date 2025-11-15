@extends('Supervisor.layout.app')

@section('title', 'Manager Dashboard - WellKenz ERP')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Manager Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Executive summary of operations</p>
            </div>
        </div>
    </div>

    <!-- quick stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Requisitions</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $pendingReqs }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Item Requests</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $pendingItemReqs }}</p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Low-Stock Items</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ count($lowStock) }}</p>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Expiring ≤ 30 d</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ count($expiry) }}</p>
        </div>
    </div>

    <!-- pending approvals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- pending requisitions -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pending Requisitions</h3>
                <a href="{{ route('supervisor.requisitions.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ref</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pendingReqsList as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $req->req_ref }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($req->req_purpose,40) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                    @if($req->req_priority==='high') bg-rose-100 text-rose-700
                                    @elseif($req->req_priority==='medium') bg-amber-100 text-amber-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    {{ ucfirst($req->req_priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $req->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('supervisor.requisitions.show',$req->requisition_id) }}"
                                       class="px-3 py-1 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-semibold rounded">
                                        Review
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No pending requisitions.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- pending item requests -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pending Item Requests</h3>
                <a href="{{ route('supervisor.item-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pendingItemReqsList as $ir)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $ir->item_req_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $ir->item_req_unit }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $ir->item_req_quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $ir->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('supervisor.item-requests.index') }}"
                                       class="px-3 py-1 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-semibold rounded">
                                        Review
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">No pending item requests.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- alerts & summaries -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- low-stock + expiry alerts -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Inventory Alerts</h3>
                <a href="{{ route('supervisor.inventory-overview') }}" class="text-sm text-blue-600 hover:text-blue-800">Manage →</a>
            </div>
            <div class="p-6 space-y-4">
                <!-- low-stock -->
                <div>
                    <h4 class="text-base font-semibold text-gray-700 mb-2">Low-Stock Items</h4>
                    @if(count($lowStock)>0)
                    <ul class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        @foreach($lowStock as $item)
                        <li class="flex items-center justify-between bg-rose-50 border border-rose-200 rounded px-3 py-2 text-sm">
                            <span class="text-rose-700 font-medium">{{ $item->item_name }}</span>
                            <span class="text-rose-600">{{ $item->current_stock }} / {{ $item->reorder_level }} {{ $item->unit }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No low-stock items.</p>
                    @endif
                </div>

                <!-- expiry -->
                <div>
                    <h4 class="text-base font-semibold text-gray-700 mb-2">Expiring ≤ 30 d</h4>
                    @if(count($expiry)>0)
                    <ul class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        @foreach($expiry as $item)
                        <li class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded px-3 py-2 text-sm">
                            <span class="text-amber-700 font-medium">{{ $item->item_name }}</span>
                            <span class="text-amber-600">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No items expiring within 30 days.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- recent notifications -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Notifications</h3>
                <a href="{{ route('supervisor.notifications') }}" class="text-sm text-blue-600 hover:text-blue-800">View all →</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentNotifications as $n)
                <div class="p-4 hover:bg-gray-50 transition @if(!$n->is_read) bg-blue-50 @endif">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $n->notif_title }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ Str::limit($n->notif_content,100) }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs text-gray-500 ml-4">{{ ucfirst(str_replace('_',' ',$n->related_type)) }}</span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-gray-500 text-sm">No new notifications.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- quick stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- requisition approval ratio this month -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Requisition Approval Ratio – This Month</h3>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500">Approved</span>
                <span class="text-sm font-semibold text-gray-900">{{ $approvedThisMonth }}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500">Rejected</span>
                <span class="text-sm font-semibold text-gray-900">{{ $rejectedThisMonth }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $approvalRatio }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ number_format($approvalRatio,1) }}% approved</p>
        </div>

        <!-- issued acknowledgements this month -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acknowledgements Issued – This Month</h3>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500">Issued</span>
                <span class="text-sm font-semibold text-gray-900">{{ $issuedAckThisMonth }}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500">Received</span>
                <span class="text-sm font-semibold text-gray-900">{{ $receivedAckThisMonth }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $ackReceiptRatio }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ number_format($ackReceiptRatio,1) }}% received</p>
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Supervisor.Dashboard.notificationDetail')

</div>

<script>
/* light helpers */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function openNotificationDetail(id){
    /* ajax fetch then fill modal */
    document.getElementById('notificationDetailModal').classList.remove('hidden');
}
</script>
@endsection