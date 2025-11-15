@extends('Employee.layout.app')

@section('title', 'Dashboard - WellKenz ERP')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- 1.  Personal Welcome card  --}}
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
                <h1 class="text-2xl font-semibold text-gray-900">{{ $greeting }}, {{ $userName }}</h1>
                <p class="text-sm text-gray-500 mt-1">Your personal dashboard – requests, alerts & activity</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">{{ date('F j, Y') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    {{-- 2.  PERSONAL REQUISITION COUNTS  --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Submitted --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">My Requisitions</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $myReqTotal }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total submitted</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-file-alt text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Pending (waiting approval) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $myReqPending }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-clock text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Approved --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $myReqApproved }}</p>
                    <p class="text-xs text-gray-400 mt-1">Ready for PO</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-check-circle text-gray-600"></i>
                </div>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Rejected</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $myReqRejected }}</p>
                    <p class="text-xs text-gray-400 mt-1">Needs revision</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                    <i class="fas fa-times-circle text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3.  ITEM REQUESTS STILL PENDING SUPERVISOR  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Item Requests Awaiting Supervisor Approval</h3>
        <div class="space-y-3">
            @forelse($pendingItems as $req)
                <div class="p-3 border border-gray-200 rounded">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ $req->item_req_name }}</p>
                    <span class="text-xs px-2 py-1 bg-amber-100 text-amber-800 rounded">Pending</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Qty: {{ $req->item_req_quantity }} • Requested {{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</p>
            </div>
            @empty
                <p class="text-xs text-gray-500">No item requests pending approval.</p>
            @endforelse
        </div>
    </div>

    {{-- 4.  NOTIFICATIONS & ALERTS  --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Personal Notifications --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <button onclick="markNotifsRead()" class="text-xs text-gray-600 hover:text-gray-900">Mark all read</button>
            </div>
            <div class="space-y-4">
                @foreach($notifs as $n)
                    <div class="flex items-start gap-3 {{ $n->is_read?'opacity-60':'' }}">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-bell text-gray-600 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 font-medium">{{ $n->notif_title }}</p>
                            <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $n->notif_content }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Low-Stock Alerts (read-only) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Low-Stock Alerts</h3>
            <div class="space-y-3">
                @forelse($lowStock as $item)
                    <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                        <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">Stock: {{ $item->item_stock }} {{ $item->item_unit }} • Re-order: {{ $item->reorder_level }} {{ $item->item_unit }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No low-stock items – you're all set!</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 5.  RECENT REQUISITIONS  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">My Recent Requisitions</h3>
            <a href="{{ route('staff.requisitions.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-3 py-2">#</th>
                    <th class="text-left px-3 py-2">Purpose</th>
                    <th class="text-left px-3 py-2">Status</th>
                    <th class="text-left px-3 py-2">Requested</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($recReqs as $r)
                    <tr>
                        <td class="px-3 py-2 text-gray-900">RQ-{{ $r->req_id }}</td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-xs">{{ $r->req_purpose }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($r->req_status=='pending') bg-amber-100 text-amber-800
                                @elseif($r->req_status=='approved') bg-green-100 text-green-800
                                @elseif($r->req_status=='rejected') bg-rose-100 text-rose-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($r->req_status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ \Carbon\Carbon::parse($r->created_at)->format('M d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function markNotifsRead(){
    fetch("{{ route('staff.notifications.mark-all-read') }}",{
        method:"POST",
        headers:{
            "X-CSRF-TOKEN":"{{ csrf_token() }}",
            "Accept":"application/json"
        }
    }).then(r=>r.ok?location.reload():null);
}
</script>
@endsection