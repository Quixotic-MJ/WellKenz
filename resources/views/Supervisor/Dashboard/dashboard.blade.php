@extends('Supervisor.layout.app')

@section('title', 'Manager Dashboard - WellKenz ERP')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">

    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Manager Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Executive summary of operations</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-amber-50 text-amber-500">
                    <i class="fas fa-file-alt fa-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Requisitions</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $pendingReqs }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-amber-50 text-amber-500">
                    <i class="fas fa-tags fa-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Item Requests</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $pendingItemReqs }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-rose-50 text-rose-500">
                    <i class="fas fa-box-open fa-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Low-Stock Items</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ count($lowStock) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-rose-200 rounded-lg p-5">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-rose-50 text-rose-500">
                    <i class="fas fa-calendar-times fa-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Expiring ≤ 30 d</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ count($expiry) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clock text-gray-400 mr-3"></i>
                    Pending Requisitions
                </h3>
                <a href="{{ route('supervisor.requisitions.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all →</a>
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
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    @if($req->req_priority==='high') bg-rose-100 text-rose-700
                                    @elseif($req->req_priority==='medium') bg-amber-100 text-amber-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    {{ ucfirst($req->req_priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $req->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('supervisor.requisitions.show',$req->requisition_id) }}"
                                   class="px-3 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 transition text-xs font-semibold rounded-full">
                                    Review
                                </a>
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

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clock text-gray-400 mr-3"></i>
                    Pending Item Requests
                </h3>
                <a href="{{ route('supervisor.item-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all →</a>
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
                                <a href="{{ route('supervisor.item-requests.index') }}"
                                   class="px-3 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 transition text-xs font-semibold rounded-full">
                                    Review
                                </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle text-gray-400 mr-3"></i>
                    Inventory Alerts
                </h3>
                <a href="{{ route('supervisor.inventory-overview') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Manage →</a>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <h4 class="text-base font-semibold text-gray-700 mb-2">Low-Stock Items</h4>
                    @if(count($lowStock)>0)
                    <ul class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        @foreach($lowStock as $item)
                        <li class="flex items-center bg-rose-50 border border-rose-200 rounded px-3 py-2 text-sm">
                            <i class="fas fa-arrow-down text-rose-500 w-6 text-center" title="Low Stock"></i>
                            <span class="text-rose-700 font-medium flex-1 ml-2">{{ $item->item_name }}</span>
                            <span class="text-rose-600 font-mono">{{ $item->current_stock }} / {{ $item->reorder_level }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No low-stock items.</p>
                    @endif
                </div>

                <div>
                    <h4 class="text-base font-semibold text-gray-700 mb-2">Expiring ≤ 30 d</h4>
                    @if(count($expiry)>0)
                    <ul class="space-y-2 max-h-40 overflow-y-auto pr-2">
                        @foreach($expiry as $item)
                        <li class="flex items-center bg-amber-50 border border-amber-200 rounded px-3 py-2 text-sm">
                            <i class="fas fa-hourglass-half text-amber-500 w-6 text-center" title="Expiring Soon"></i>
                            <span class="text-amber-700 font-medium flex-1 ml-2">{{ $item->item_name }}</span>
                            <span class="text-amber-600 font-mono">{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm text-gray-500">No items expiring within 30 days.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-bell text-gray-400 mr-3"></i>
                    Recent Notifications
                </h3>
                <a href="{{ route('supervisor.notifications') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all →</a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentNotifications as $n)
                    @php
                        $icon = [
                            'requisition' => 'fas fa-file-alt',
                            'item_request' => 'fas fa-tags',
                            'purchase_order' => 'fas fa-shopping-cart',
                            'announcement' => 'fas fa-bullhorn',
                        ][$n->related_type] ?? 'fas fa-bell';
                        $iconColor = [
                            'requisition' => 'text-blue-500 bg-blue-50',
                            'item_request' => 'text-indigo-500 bg-indigo-50',
                            'purchase_order' => 'text-green-500 bg-green-50',
                            'announcement' => 'text-purple-500 bg-purple-50',
                        ][$n->related_type] ?? 'text-gray-500 bg-gray-50';
                    @endphp
                    <div class="flex items-start p-4 hover:bg-gray-50 transition @if(!$n->is_read) bg-blue-50 hover:bg-blue-100 @endif">
                        <div class="flex-shrink-0 mr-4 pt-1">
                            <span class="flex items-center justify-center h-10 w-10 rounded-full {{ $iconColor }}">
                                <i class="{{ $icon }}"></i>
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900">{{ $n->notif_title }}</p>
                                <p class="text-xs text-gray-500 flex-shrink-0 ml-4">{{ $n->created_at->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($n->notif_content,100) }}</p>
                        </div>
                    </div>
                @empty
                <div class="p-6 text-center text-gray-500 text-sm">No new notifications.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-chart-pie text-gray-400 mr-3"></i>
                Requisition Approval Ratio – This Month
            </h3>
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

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-handshake text-gray-400 mr-3"></i>
                Acknowledgements Issued – This Month
            </h3>
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
    // Note: You still need to create this modal and the route for it
    // For now, it just shows the modal container
    document.getElementById('notificationDetailModal').classList.remove('hidden');
}
</script>
@endsection