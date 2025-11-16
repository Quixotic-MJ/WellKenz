@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('inventory.deliveries.incoming') }}" class="mr-4 text-blue-600 hover:text-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Delivery Receive</h1>
            <p class="text-gray-600 mt-1">Create delivery memo for PO: {{ $purchaseOrder->po_ref ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- PO Information Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Purchase Order Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">PO Reference:</span>
                        <span class="font-medium text-gray-900">{{ $purchaseOrder->po_ref ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Supplier:</span>
                        <span class="font-medium text-gray-900">{{ $purchaseOrder->supplier->sup_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Expected Delivery:</span>
                        <span class="font-medium text-gray-900">{{ $purchaseOrder->expected_delivery_date ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-medium text-gray-900">₱{{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Memo Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Memo Reference:</span>
                        <span class="font-medium text-blue-600">{{ $memoRef ?? 'Auto-generated' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Generated:</span>
                        <span class="font-medium text-gray-900">{{ now()->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Items Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-medium text-gray-900">{{ $purchaseOrder->purchaseItems->count() ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Categories:</span>
                        <span class="font-medium text-gray-900">{{ $purchaseOrder->purchaseItems->pluck('item.category.cat_name')->unique()->count() ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Memo Creation Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Create Delivery Memo</h3>
            <p class="text-sm text-gray-600 mt-1">Record the actual delivery received and prepare for stock-in processing</p>
        </div>
        
        <form id="deliveryMemoForm" class="p-6">
            @csrf
            <input type="hidden" name="po_id" value="{{ $purchaseOrder->po_id ?? '' }}">
            <input type="hidden" name="memo_ref" value="{{ $memoRef ?? '' }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="received_date" class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>
                    <input type="date" 
                           id="received_date" 
                           name="received_date" 
                           value="{{ date('Y-m-d') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>
                
                <div>
                    <label for="received_by" class="block text-sm font-medium text-gray-700 mb-2">Received By</label>
                    <input type="text" 
                           id="received_by" 
                           name="received_by" 
                           value="{{ auth()->user()->name ?? '' }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50" 
                           readonly>
                </div>
            </div>

            <div class="mb-6">
                <label for="memo_remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks (Optional)</label>
                <textarea id="memo_remarks" 
                          name="memo_remarks" 
                          rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                          placeholder="Enter any notes about the delivery condition, damage, missing items, etc."></textarea>
            </div>

            <!-- Items Checklist -->
            <div class="mb-6">
                <h4 class="text-md font-semibold text-gray-900 mb-3">Items Checklist</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($purchaseOrder->purchaseItems ?? collect() as $purchaseItem)
                        <div class="flex items-center space-x-3 p-3 bg-white rounded border">
                            <input type="checkbox" 
                                   id="item-{{ $purchaseItem->item_id ?? '' }}" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="flex-1">
                                <label for="item-{{ $purchaseItem->item_id ?? '' }}" class="text-sm font-medium text-gray-900">
                                    {{ $purchaseItem->item->item_name ?? 'Item Name' }}
                                </label>
                                <div class="text-xs text-gray-500">
                                    Ordered: {{ $purchaseItem->ordered_quantity ?? 0 }} {{ $purchaseItem->item->item_unit ?? '' }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-2 text-center text-gray-500 py-4">
                            No items found for this purchase order
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Action Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h4 class="text-md font-semibold text-blue-900 mb-2">Next Steps After Memo Creation</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Memo will be created and purchase order status updated to "delivered"</li>
                    <li>• Notifications will be sent to Supervisor and Purchasing staff</li>
                    <li>• Proceed to Stock-In Processing to update actual inventory levels</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('inventory.deliveries.incoming') }}" 
                   class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="button" 
                        onclick="previewMemo()" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Preview Memo
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Create Delivery Memo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Delivery Memo Preview</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="memoPreview" class="border rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                <!-- Preview content will be generated here -->
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closePreview()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Close
                </button>
                <button onclick="confirmAndSubmit()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Confirm & Create Memo
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('deliveryMemoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitMemo();
    });

    function previewMemo() {
        const formData = getFormData();
        const previewContent = `
            <div class="space-y-4">
                <div class="text-center border-b pb-4">
                    <h2 class="text-xl font-bold text-gray-900">DELIVERY MEMO</h2>
                    <p class="text-sm text-gray-600">${formData.memo_ref}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-900">PO Reference:</h3>
                        <p class="text-gray-700">{{ $purchaseOrder->po_ref ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Supplier:</h3>
                        <p class="text-gray-700">{{ $purchaseOrder->supplier->sup_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Received Date:</h3>
                        <p class="text-gray-700">${formData.received_date}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Received By:</h3>
                        <p class="text-gray-700">${formData.received_by}</p>
                    </div>
                </div>
                
                ${formData.memo_remarks ? `
                    <div>
                        <h3 class="font-semibold text-gray-900">Remarks:</h3>
                        <p class="text-gray-700">${formData.memo_remarks}</p>
                    </div>
                ` : ''}
                
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Items Checklist:</h3>
                    <div class="space-y-2">
                        @forelse($purchaseOrder->purchaseItems ?? collect() as $purchaseItem)
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 border rounded flex-shrink-0"></div>
                            <span class="text-sm text-gray-700">{{ $purchaseItem->item->item_name ?? 'Item Name' }} ({{ $purchaseItem->ordered_quantity ?? 0 }} {{ $purchaseItem->item->item_unit ?? '' }})</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">No items found</p>
                        @endforelse
                    </div>
                </div>
                
                <div class="text-xs text-gray-500 border-t pt-4">
                    Generated on ${new Date().toLocaleString()}
                </div>
            </div>
        `;
        
        document.getElementById('memoPreview').innerHTML = previewContent;
        document.getElementById('previewModal').classList.remove('hidden');
    }

    function getFormData() {
        return {
            memo_ref: document.querySelector('input[name="memo_ref"]').value,
            po_id: document.querySelector('input[name="po_id"]').value,
            received_date: document.getElementById('received_date').value,
            received_by: document.getElementById('received_by').value,
            memo_remarks: document.getElementById('memo_remarks').value
        };
    }

    function closePreview() {
        document.getElementById('previewModal').classList.add('hidden');
    }

    function confirmAndSubmit() {
        closePreview();
        submitMemo();
    }

    function submitMemo() {
        const formData = getFormData();
        
        fetch('{{ route("inventory.delivery.memo.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Delivery memo created successfully! Reference: ' + data.memo_ref);
                
                // Redirect to stock-in processing page
                setTimeout(() => {
                    window.location.href = `{{ route('inventory.stock-in.index') }}`;
                }, 1500);
            } else {
                alert('Error creating memo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating memo. Please try again.');
        });
    }
</script>
@endpush
@endsection