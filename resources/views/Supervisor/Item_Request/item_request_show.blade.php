@extends('Supervisor.layout.app')

@section('title', 'Review Item Request')
@section('breadcrumb', 'Item Requests / Review')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Review Item Request</h1>
                <p class="text-sm text-gray-500 mt-1">ID #{{ $request->item_req_id }}</p>
            </div>
            <a href="{{ route('supervisor.item-requests.index') }}" class="text-sm text-blue-600 hover:text-blue-800">← Back to list</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Details</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Item Name</dt>
                        <dd class="text-gray-900 font-medium">{{ $request->item_req_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Unit</dt>
                        <dd class="text-gray-900 font-medium">{{ $request->item_req_unit }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Quantity</dt>
                        <dd class="text-gray-900 font-medium">{{ $request->item_req_quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($request->item_req_status==='pending') bg-amber-100 text-amber-700
                                @elseif($request->item_req_status==='approved') bg-green-100 text-green-700
                                @elseif($request->item_req_status==='rejected') bg-rose-100 text-rose-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($request->item_req_status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Description</dt>
                        <dd class="text-gray-900">{{ $request->item_req_description }}</dd>
                    </div>
                </dl>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Requested By</p>
                        <p class="text-gray-900 font-medium">{{ optional($request->requester)->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Requested At</p>
                        <p class="text-gray-900 font-medium">{{ $request->created_at?->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Take Action</h3>

                @if($request->item_req_status !== 'pending')
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700">
                        This request has already been {{ $request->item_req_status }}.
                    </div>
                @else
                <form method="POST" action="{{ route('supervisor.item_requests.update_status', $request->item_req_id) }}" id="decisionForm">
                    @csrf
                    <input type="hidden" name="item_req_status" id="item_req_status" value="approved" />

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (optional for rejection)</label>
                        <textarea name="remarks" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" rows="3" placeholder="Write a reason when rejecting..."></textarea>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="submit" onclick="document.getElementById('item_req_status').value='approved'"
                                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 text-sm font-semibold rounded">Approve</button>
                        <button type="submit" onclick="document.getElementById('item_req_status').value='rejected'"
                                class="px-4 py-2 bg-rose-600 text-white hover:bg-rose-700 text-sm font-semibold rounded">Reject</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
