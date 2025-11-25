@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Order Details</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        PO Number: <span class="font-medium">{{ $purchaseOrder->po_number }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($purchaseOrder->status === 'draft')
                <button onclick="deleteOrder({{ $purchaseOrder->id }})" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition shadow-sm">
                    <i class="fas fa-trash mr-2"></i> Delete Draft
                </button>
            @endif
            <a href="{{ route('purchasing.po.print', $purchaseOrder->id) }}" 
               target="_blank"
               class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print PO
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-green-800">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-2"></i>
                <div class="text-sm text-red-800">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - PO Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Information --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">PO Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $purchaseOrder->po_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($purchaseOrder->status === 'draft') bg-gray-100 text-gray-800
                                    @elseif($purchaseOrder->status === 'sent') bg-blue-100 text-blue-800
                                    @elseif($purchaseOrder->status === 'confirmed') bg-yellow-100 text-yellow-800
                                    @elseif($purchaseOrder->status === 'partial') bg-orange-100 text-orange-800
                                    @elseif($purchaseOrder->status === 'completed') bg-green-100 text-green-800
                                    @elseif($purchaseOrder->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    <i class="fas fa-circle mr-1 text-xs"></i>
                                    {{ ucfirst($purchaseOrder->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->order_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expected Delivery</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($purchaseOrder->expected_delivery_date)
                                    {{ $purchaseOrder->expected_delivery_date->format('M d, Y') }}
                                    @if($purchaseOrder->is_overdue ?? false)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                        </span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Actual Delivery</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->actual_delivery_date?->format('M d, Y') ?? 'Pending' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->payment_terms ?? 'N/A' }} days</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->notes ?: 'No notes provided' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Supplier Information --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Supplier Information</h3>
                </div>
                <div class="p-6">
                    @if($purchaseOrder->supplier)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-lg font-medium text-gray-600">
                                        {{ strtoupper(substr($purchaseOrder->supplier->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="text-lg font-medium text-gray-900">{{ $purchaseOrder->supplier->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->supplier_code }}</p>
                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Contact Person</dt>
                                        <dd class="text-sm text-gray-900">{{ $purchaseOrder->supplier->contact_person ?: 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Phone</dt>
                                        <dd class="text-sm text-gray-900">{{ $purchaseOrder->supplier->phone ?: 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                                        <dd class="text-sm text-gray-900">{{ $purchaseOrder->supplier->email ?: 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Rating</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($purchaseOrder->supplier->rating)
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $purchaseOrder->supplier->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                @endfor
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No supplier information available</p>
                    @endif
                </div>
            </div>

            {{-- Order Items --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                        <span class="text-sm text-gray-500">{{ $purchaseOrder->purchaseOrderItems->count() }} items</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Received</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($purchaseOrder->purchaseOrderItems as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->item->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->item->item_code ?? '' }}</div>
                                            @if($item->item->category)
                                                <div class="text-xs text-gray-400">{{ $item->item->category->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        {{ number_format($item->quantity_ordered, 2) }}
                                        <div class="text-xs text-gray-500">{{ $item->item->unit->symbol ?? 'pcs' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        {{ number_format($item->quantity_received ?? 0, 2) }}
                                        <div class="text-xs text-gray-500">{{ $item->item->unit->symbol ?? 'pcs' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        ₱{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        ₱{{ number_format($item->total_price, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-4 block"></i>
                                            <p class="text-lg font-medium">No items in this order</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Source Purchase Requests --}}
            @if($purchaseOrder->sourcePurchaseRequests->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Source Purchase Requests</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($purchaseOrder->sourcePurchaseRequests as $sourcePR)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $sourcePR->pr_number }}</div>
                                        <div class="text-sm text-gray-500">{{ $sourcePR->department ?? 'N/A' }}</div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($sourcePR->priority === 'urgent') bg-red-100 text-red-800
                                        @elseif($sourcePR->priority === 'high') bg-orange-100 text-orange-800
                                        @elseif($sourcePR->priority === 'normal') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        <i class="fas fa-circle mr-1 text-xs"></i>
                                        {{ ucfirst($sourcePR->priority) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column - Summary --}}
        <div class="space-y-6">
            {{-- Order Summary --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Summary</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Subtotal</dt>
                            <dd class="text-sm font-medium text-gray-900">₱{{ number_format($purchaseOrder->total_amount ?? $purchaseOrder->grand_total, 2) }}</dd>
                        </div>
                        @if($purchaseOrder->tax_amount > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Tax</dt>
                                <dd class="text-sm font-medium text-gray-900">₱{{ number_format($purchaseOrder->tax_amount, 2) }}</dd>
                            </div>
                        @endif
                        @if($purchaseOrder->discount_amount > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Discount</dt>
                                <dd class="text-sm font-medium text-gray-900">-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</dd>
                            </div>
                        @endif
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-medium text-gray-900">₱{{ number_format($purchaseOrder->grand_total, 2) }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Status Information --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Status Information</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $purchaseOrder->createdBy->name ?? 'N/A' }}
                                <div class="text-xs text-gray-500">{{ $purchaseOrder->created_at?->format('M d, Y H:i') ?? '' }}</div>
                            </dd>
                        </div>
                        @if($purchaseOrder->approvedBy)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $purchaseOrder->approvedBy->name }}
                                    <div class="text-xs text-gray-500">{{ $purchaseOrder->approved_at?->format('M d, Y H:i') ?? '' }}</div>
                                    <div class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Auto-approved via Purchase Request
                                    </div>
                                </dd>
                            </div>
                        @endif
                        @if($purchaseOrder->updated_at && $purchaseOrder->updated_at != $purchaseOrder->created_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $purchaseOrder->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSRF Token Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@push('scripts')
<script>
// Helper function for form submission (must be defined first)
function submitForm(action, method) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = method;
    
    const csrfField = document.createElement('input');
    csrfField.type = 'hidden';
    csrfField.name = '_token';
    csrfField.value = csrfToken;
    
    form.appendChild(methodField);
    form.appendChild(csrfField);
    document.body.appendChild(form);
    form.submit();
}

// Modal handler functions
function showConfirmationModal(title, message, iconType, onConfirm) {
    // Create modal HTML if it doesn't exist
    if (!document.getElementById('showPageModal')) {
        const modalHTML = `
            <div id="showPageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10" id="showModalIcon">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-xl" id="showModalIconElement"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="showModalTitle">Confirm Action</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500" id="showModalMessage">Are you sure you want to perform this action?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" id="showModalConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Confirm
                            </button>
                            <button type="button" id="showModalCancelBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    const modal = document.getElementById('showPageModal');
    const modalTitle = document.getElementById('showModalTitle');
    const modalMessage = document.getElementById('showModalMessage');
    const modalIcon = document.getElementById('showModalIcon');
    const modalIconElement = document.getElementById('showModalIconElement');
    const confirmBtn = document.getElementById('showModalConfirmBtn');
    const cancelBtn = document.getElementById('showModalCancelBtn');
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Reset icon classes
    modalIcon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10';
    modalIconElement.className = 'text-xl';
    
    // Set icon based on type
    switch(iconType) {
        case 'delete':
        case 'warning':
            modalIcon.classList.add('bg-red-100');
            modalIconElement.classList.add('text-red-600', 'fas', 'fa-trash');
            confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm';
            break;
        case 'info':
            modalIcon.classList.add('bg-blue-100');
            modalIconElement.classList.add('text-blue-600', 'fas', 'fa-info-circle');
            confirmBtn.className = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm';
            break;
        default:
            modalIcon.classList.add('bg-gray-100');
            modalIconElement.classList.add('text-gray-600', 'fas', 'fa-question-circle');
    }
    
    modal.classList.remove('hidden');
    
    confirmBtn.onclick = function() {
        modal.classList.add('hidden');
        onConfirm();
    };
    
    cancelBtn.onclick = function() {
        modal.classList.add('hidden');
    };
    
    // Close on backdrop click
    modal.querySelector('.fixed.inset-0').onclick = function(e) {
        if (e.target === this) {
            modal.classList.add('hidden');
        }
    };
}

function deleteOrder(orderId) {
    showConfirmationModal(
        'Delete Draft Order',
        'Are you sure you want to delete this draft purchase order? This action cannot be undone.',
        'delete',
        () => {
            submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', orderId), 'DELETE');
        }
    );
}
</script>
@endpush