@extends('Purchasing.layout.app')

@section('title', 'Purchase Order ' . $purchaseOrder->po_ref . ' - WellKenz ERP')
@section('breadcrumb', 'Purchase Order Details')

@section('content')
    <div class="space-y-6">

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Purchase Order: PO-{{ $purchaseOrder->po_ref }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Status: <span class="font-medium text-green-600">{{ ucfirst($purchaseOrder->po_status) }}</span></p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('purchasing.purchase.print', $purchaseOrder->po_id) }}" target="_blank"
                       class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 transition text-sm font-medium rounded">
                        <i class="fas fa-print mr-2"></i>Generate/Print PDF
                    </a>
                    <button class="px-4 py-2 bg-amber-600 text-white hover:bg-amber-700 transition text-sm font-medium rounded">
                        <i class="fas fa-edit mr-2"></i>Edit PO
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Supplier and General Details --}}
            <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">PO Information</h3>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Supplier</p>
                    <p class="font-medium text-gray-900">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Date Created</p>
                    <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Expected Delivery</p>
                    <p class="font-medium text-gray-900">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : '-' }}</p>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Items Ordered</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php $grandTotal = 0; @endphp
                            @foreach ($purchaseOrder->items as $item)
                                @php $lineTotal = $item->quantity * $item->unit_price; $grandTotal += $lineTotal; @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">₱{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="3" class="px-6 py-4 text-right text-sm text-gray-900 uppercase">Grand Total:</td>
                                <td class="px-6 py-4 text-right text-lg text-blue-700">₱{{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection