@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Acknowledgement Receipt Details</h1>
            <p class="text-gray-600 mt-1">View detailed information of acknowledgment receipt</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.open('{{ route('inventory.acknowledge-receipts.print', $receipt->ar_id) }}', '_blank')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-print mr-2"></i>Print
            </button>
            <a href="{{ route('inventory.acknowledge-receipts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Receipt Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Receipt Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">AR Reference</label>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $receipt->ar_ref }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="mt-1 inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $receipt->ar_status === 'issued' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($receipt->ar_status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Issued Date</label>
                        <p class="mt-1 text-gray-900">{{ $receipt->issued_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created At</label>
                        <p class="mt-1 text-gray-900">{{ $receipt->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>

                <!-- People Information -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Issued To</label>
                        <p class="mt-1 text-gray-900">{{ $receipt->receiver->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $receipt->receiver->position ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Issued By</label>
                        <p class="mt-1 text-gray-900">{{ $receipt->issuer->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $receipt->issuer->position ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Related Requisition</label>
                        @if($receipt->requisition)
                            <p class="mt-1 text-gray-900">{{ $receipt->requisition->req_ref }}</p>
                            <p class="text-sm text-gray-500">Purpose: {{ Str::limit($receipt->requisition->req_purpose, 50) }}</p>
                        @else
                            <p class="mt-1 text-gray-500">No related requisition</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Remarks Section -->
            @if($receipt->ar_remarks)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-900">{{ $receipt->ar_remarks }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Requisition Items (if available) -->
    @if($receipt->requisition)
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Related Requisition Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($receipt->requisition->items ?? [] as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $item->item->item_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->req_item_quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->item_unit }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No items found for this requisition</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .p-6 { padding: 1rem !important; }
}
</style>
@endsection