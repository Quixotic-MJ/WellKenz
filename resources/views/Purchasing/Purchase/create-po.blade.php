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
                    <a href="{{ route('purchasing.approved.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-800 hover:bg-gray-300 transition text-sm font-medium rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Return
                    </a>
                    <a href="{{ route('purchasing.purchase.print', $purchaseOrder->po_id) }}" target="_blank"
                       class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 transition text-sm font-medium rounded">
                        <i class="fas fa-print mr-2"></i>Generate/Print PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Supplier and General Details --}}
            <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">PO Information</h3>
                @if(($purchaseOrder->po_status ?? 'draft') === 'draft')
                    <form id="poUpdateForm" action="{{ route('purchasing.purchase.update', $purchaseOrder->po_id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Supplier</label>
                            <select name="sup_id" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                                <option value="">Select supplier</option>
                                @foreach(($suppliers ?? []) as $s)
                                    <option value="{{ $s->sup_id }}" {{ ($purchaseOrder->supplier->name ?? null) === $s->sup_name ? 'selected' : '' }}>{{ $s->sup_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" value="{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('Y-m-d') : '' }}" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Delivery Address</label>
                            <input type="text" name="delivery_address" value="{{ $purchaseOrder->delivery_address ?? '' }}" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Enter delivery address" required />
                        </div>
                @else
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Supplier</p>
                            <p class="font-medium text-gray-900">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Expected Delivery</p>
                            <p class="font-medium text-gray-900">{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Delivery Address</p>
                            <p class="font-medium text-gray-900">{{ $purchaseOrder->delivery_address ?? '-' }}</p>
                        </div>
                @endif

                        <div>
                            <p class="text-xs text-gray-500 uppercase">Date Created</p>
                            <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($purchaseOrder->created_at)->format('M d, Y') }}</p>
                        </div>

                        @if(($purchaseOrder->po_status ?? 'draft') === 'draft')
                        <div class="pt-2 border-t flex items-center space-x-2">
                            <button type="submit" name="action" value="save" class="px-4 py-2 bg-gray-700 text-white rounded text-sm">Save Draft</button>
                            <button type="submit" name="action" value="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Submit (Finalize)</button>
                        </div>
                        @endif

                    @if(($purchaseOrder->po_status ?? 'draft') === 'draft')
                    </form>
                    @endif
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
                            @foreach ($purchaseOrder->items as $index => $item)
                                @php $lineTotal = $item->quantity * ($item->unit_price ?? 0); $grandTotal += $lineTotal; @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">
                                        @if(($purchaseOrder->po_status ?? 'draft') === 'draft')
                                            <input type="hidden" form="poUpdateForm" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}" />
                                            <input type="number" step="0.01" min="0" form="poUpdateForm" name="items[{{ $index }}][unit_price]" value="{{ number_format($item->unit_price ?? 0, 2, '.', '') }}" class="w-32 border border-gray-300 rounded px-2 py-1 text-right" />
                                        @else
                                            ₱{{ number_format($item->unit_price, 2) }}
                                        @endif
                                    </td>
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