@extends('Purchasing.layout.app')

@section('title', 'Delivery Memo Details - WellKenz ERP')
@section('breadcrumb', 'Delivery Memo Details')

@section('content')
<div class="space-y-6">

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Delivery Memo</h1>
                <p class="text-sm text-gray-500 mt-1">Review delivery memo information and related PO</p>
            </div>
            <a href="{{ route('purchasing.memo.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                <i class="fas fa-arrow-left mr-2"></i>Back to Memos
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Memo Reference</p>
                        <p class="font-semibold text-gray-900">{{ $memo->memo_ref }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">PO Reference</p>
                        <p class="font-semibold text-gray-900">PO-{{ $po->po_ref ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Supplier</p>
                        <p class="font-semibold text-gray-900">{{ $po->sup_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Received Date</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($memo->received_date)->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Received By</p>
                        <p class="font-semibold text-gray-900">{{ $received_by_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">PO Status</p>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-700">Delivered</span>
                    </div>
                </div>
                <div class="mt-4 text-sm">
                    <p class="text-gray-500">Remarks</p>
                    <p class="text-gray-900">{{ $memo->memo_remarks ?? 'None' }}</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">PO Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Ordered Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($items as $it)
                            <tr>
                                <td class="px-4 py-2">{{ $it->item_name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $it->unit }}</td>
                                <td class="px-4 py-2 text-right text-gray-900">{{ number_format($it->ordered_qty, 2) }}</td>
                                <td class="px-4 py-2 text-right text-gray-900">₱{{ number_format($it->unit_price ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">No items found for this PO.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6 text-sm">
                <p class="text-gray-500">Delivery Address</p>
                <p class="text-gray-900">{{ $po->delivery_address ?? '—' }}</p>
                <div class="mt-3">
                    <p class="text-gray-500">Expected Delivery Date</p>
                    <p class="text-gray-900">{{ isset($po->expected_delivery_date) ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
