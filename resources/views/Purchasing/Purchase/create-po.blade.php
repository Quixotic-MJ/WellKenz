@extends('Purchasing.layout.app')

@section('title', 'Purchase Order Details')
@section('breadcrumb', 'Purchase Order Details')

@section('content')
<form action="{{ route('purchasing.purchase.update', $purchaseOrder->po_id) }}" method="POST">
    @csrf

    <div classs="space-y-6">
        <!-- Header -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Purchase Order: {{ $purchaseOrder->po_ref }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Manage details, update prices, and submit this PO to the supplier.
                </p>
            </div>
            <div class="flex gap-x-3">
                <!-- Back to List -->
                <a href="{{ route('purchasing.approved.index') }}"
                    class="py-2 px-4 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
                @if($purchaseOrder->po_status == 'draft')
                <!-- Save Draft -->
                <button type="submit" name="action" value="save"
                    class="py-2 px-4 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium">
                    Save Draft
                </button>
                <!-- Submit PO -->
                <button type="submit" name="action" value="submit"
                    class="py-2 px-4 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium">
                    Submit PO to Supplier
                </button>
                @else
                <!-- Print -->
                <a href="{{ route('purchasing.purchase.print', $purchaseOrder->po_id) }}" target="_blank"
                    class="py-2 px-4 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-sm font-medium">
                    Print PO
                </a>
                @endif
            </div>
        </div>

        <!-- PO Details -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-3">PO Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Supplier -->
                <div>
                    <label for="sup_id" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select id="sup_id" name="sup_id"
                        class="w-full rounded-lg border-gray-300 text-sm"
                        {{ $purchaseOrder->po_status != 'draft' ? 'disabled' : '' }}>
                        <option value="">Select a supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->sup_id }}"
                            {{ $purchaseOrder->sup_id == $supplier->sup_id ? 'selected' : '' }}>
                            {{ $supplier->sup_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Expected Delivery -->
                <div>
                    <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery</label>
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date"
                        value="{{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('Y-m-d') : '' }}"
                        class="w-full rounded-lg border-gray-300 text-sm"
                        {{ $purchaseOrder->po_status != 'draft' ? 'disabled' : '' }}>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span class="inline-block px-3 py-1.5 text-sm font-semibold rounded
                        @if($purchaseOrder->po_status=='draft') bg-gray-100 text-gray-700
                        @elseif($purchaseOrder->po_status=='ordered') bg-blue-100 text-blue-700
                        @elseif($purchaseOrder->po_status=='delivered') bg-green-100 text-green-700
                        @elseif($purchaseOrder->po_status=='cancelled') bg-rose-100 text-rose-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($purchaseOrder->po_status) }}
                    </span>
                </div>

                <!-- Delivery Address -->
                <div class="md:col-span-3">
                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="2"
                        class="w-full rounded-lg border-gray-300 text-sm"
                        {{ $purchaseOrder->po_status != 'draft' ? 'disabled' : '' }}>{{ $purchaseOrder->delivery_address }}</textarea>
                </div>
            </div>
        </div>

        <!-- PO Items -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit Price (₱)</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Subtotal (₱)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="po-items-body">
                        @foreach($purchaseOrder->items as $index => $item)
                        <tr class="item-row">
                            <!-- Item Name -->
                            <td class="px-6 py-4">
                                <!-- THIS IS THE LINE THAT WAS FIXED -->
                                <input type="hidden" name="items[{{ $index }}][pi_id]" value="{{ $item->pi_id }}">
                                <!-- --- -->
                                <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                            </td>
                            <!-- Qty -->
                            <td class="px-6 py-4">
                                <p class="text-gray-900">{{ $item->pi_quantity }}</p>
                            </td>
                            <!-- Unit -->
                            <td class="px-6 py-4">
                                <p class="text-gray-500">{{ $item->unit }}</p>
                            </td>
                            <!-- Unit Price -->
                            <td class="px-6 py-4" style="width: 150px;">
                                @if($purchaseOrder->po_status == 'draft')
                                <input type="number" step="0.01" name="items[{{ $index }}][unit_price]"
                                    class="w-full rounded-lg border-gray-300 text-sm unit-price"
                                    value="{{ number_format($item->pi_unit_price, 2, '.', '') }}"
                                    data-quantity="{{ $item->pi_quantity }}"
                                    oninput="calculateSubtotal(this)">
                                @else
                                <p>₱ {{ number_format($item->pi_unit_price, 2) }}</p>
                                @endif
                            </td>
                            <!-- Subtotal -->
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 subtotal">
                                    ₱ {{ number_format($item->pi_subtotal, 2) }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700 uppercase">Total Amount</td>
                            <td class="px-6 py-3">
                                <p class="text-lg font-bold text-gray-900" id="total-amount">
                                    ₱ {{ number_format($purchaseOrder->total_amount, 2) }}
                                </p>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</form>

<script>
    function calculateSubtotal(input) {
        const row = input.closest('.item-row');
        const quantity = parseFloat(input.getAttribute('data-quantity'));
        const unitPrice = parseFloat(input.value) || 0;
        const subtotal = quantity * unitPrice;

        const subtotalCell = row.querySelector('.subtotal');
        subtotalCell.textContent = `₱ ${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    
        updateTotalAmount();
    }

    function updateTotalAmount() {
        let total = 0;
        document.querySelectorAll('#po-items-body .item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.unit-price').getAttribute('data-quantity'));
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            total += quantity * unitPrice;
        });

        document.getElementById('total-amount').textContent = `₱ ${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
</script>
@endsection