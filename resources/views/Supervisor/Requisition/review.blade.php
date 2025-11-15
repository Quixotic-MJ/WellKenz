@extends('Supervisor.layout.app')

@section('title', 'Review Requisition - WellKenz ERP')
@section('breadcrumb', 'Review Requisition')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Review Requisition</h1>
                <p class="text-sm text-gray-500 mt-1">Review and decide on the requisition request</p>
            </div>
            <a href="{{ route('supervisor.requisitions.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Back to List</a>
        </div>
    </div>

    <!-- requisition details -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                    <p class="text-gray-900 font-semibold">{{ $req->req_ref }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requester</label>
                    <p class="text-gray-900">{{ $req->requester->name ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                        @if($req->req_priority==='high') bg-rose-100 text-rose-700
                        @elseif($req->req_priority==='medium') bg-amber-100 text-amber-700
                        @else bg-green-100 text-green-700
                        @endif">
                        {{ ucfirst($req->req_priority) }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-700">
                        {{ ucfirst($req->req_status) }}
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                <p class="text-gray-900 bg-gray-50 p-3 rounded">{{ $req->req_purpose ?: 'No purpose provided' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Requested Items</label>
                <div class="bg-gray-50 rounded p-4">
                    @if($req->items && $req->items->count() > 0)
                        <div class="space-y-3">
                            @foreach($req->items as $item)
                                <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $item->item_name ?: 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->req_item_quantity ?: 'N/A' }} {{ $item->item_unit ?: '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No items requested</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- decision buttons -->
    @if($req->req_status === 'pending')
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Decision</h3>
        <div class="flex items-center space-x-4">
            <form method="POST" action="{{ route('supervisor.requisitions.update-status.post', $req->req_id) }}" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="req_status" value="approved">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white hover:bg-green-700 transition font-semibold rounded">
                    <i class="fas fa-check mr-2"></i>Approve Requisition
                </button>
            </form>

            <button onclick="openRejectModal()" class="px-6 py-2 bg-rose-600 text-white hover:bg-rose-700 transition font-semibold rounded">
                <i class="fas fa-times mr-2"></i>Reject Requisition
            </button>
        </div>
    </div>
    @endif

    <!-- reject modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full rounded-lg border border-gray-200">
            <form method="POST" action="{{ route('supervisor.requisitions.update-status.post', $req->req_id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="req_status" value="rejected">
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Reject Requisition</h3>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea name="req_reject_reason" required rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400" placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white hover:bg-rose-700 rounded">Reject</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}

function openRejectModal(){
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal(){
    document.getElementById('rejectModal').classList.add('hidden');
}

document.addEventListener('keydown', e => { if(e.key === 'Escape') closeRejectModal(); });
</script>
@endsection