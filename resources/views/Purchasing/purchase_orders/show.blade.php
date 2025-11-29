@extends('Purchasing.layout.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 font-sans text-gray-600 pb-24">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ url()->previous() }}" class="flex items-center justify-center w-10 h-10 bg-white border border-border-soft rounded-xl text-chocolate hover:bg-cream-bg transition-colors shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Purchase Order</h1>
                <p class="text-sm text-gray-500 flex items-center gap-2">
                    <span>Ref:</span> 
                    <span class="font-mono font-bold text-caramel">{{ $purchaseOrder->po_number }}</span>
                </p>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            
            <a href="{{ route('purchasing.po.print', $purchaseOrder->id) }}" target="_blank"
               class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-print mr-2"></i> Print PO
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="text-sm font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 shadow-sm animate-fade-in-down">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            <span class="text-sm font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left Column - PO Details --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Order Information --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                    <h3 class="font-display text-lg font-bold text-chocolate">Order Information</h3>
                    @php
                        $statusColors = [
                            'sent' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'confirmed' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                            'partial' => 'bg-orange-50 text-orange-700 border-orange-100',
                            'completed' => 'bg-green-50 text-green-700 border-green-100',
                            'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                        ];
                        $statusClass = $statusColors[$purchaseOrder->status] ?? 'bg-blue-50 text-blue-700 border-blue-100';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusClass }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                        {{ ucfirst($purchaseOrder->status) }}
                    </span>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">PO Number</span>
                            <span class="font-mono text-lg font-bold text-chocolate">{{ $purchaseOrder->po_number }}</span>
                        </div>
                        
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Order Date</span>
                            <span class="text-base font-medium text-gray-900">{{ $purchaseOrder->order_date?->format('F d, Y') ?? 'N/A' }}</span>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-2 gap-6 pt-2">
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Expected Delivery</span>
                                @if($purchaseOrder->expected_delivery_date)
                                    <span class="text-gray-900 font-medium">{{ $purchaseOrder->expected_delivery_date->format('F d, Y') }}</span>
                                    @if($purchaseOrder->is_overdue ?? false)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Not specified</span>
                                @endif
                            </div>

                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Actual Delivery</span>
                                <span class="text-gray-900 font-medium">{{ $purchaseOrder->actual_delivery_date?->format('F d, Y') ?? 'Pending' }}</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2">
                            <div class="flex justify-between">
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Payment Terms</span>
                                    <span class="text-gray-900 font-medium">{{ $purchaseOrder->payment_terms ?? 'N/A' }} days</span>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 bg-cream-bg/50 p-4 rounded-lg border border-border-soft">
                            <span class="block text-xs font-bold text-chocolate uppercase tracking-widest mb-1">Notes</span>
                            <p class="text-sm text-gray-700 italic leading-relaxed">{{ $purchaseOrder->notes ?: 'No additional notes provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supplier Information --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-soft bg-white">
                    <h3 class="font-display text-lg font-bold text-chocolate">Supplier Details</h3>
                </div>
                <div class="p-6">
                    @if($purchaseOrder->supplier)
                        <div class="flex items-start gap-5">
                            <div class="w-14 h-14 bg-cream-bg rounded-xl flex items-center justify-center border border-border-soft shrink-0 text-caramel font-bold text-xl shadow-sm">
                                {{ strtoupper(substr($purchaseOrder->supplier->name, 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-gray-900">{{ $purchaseOrder->supplier->name }}</h4>
                                <p class="text-xs text-gray-400 uppercase font-mono mt-0.5 mb-4">Code: {{ $purchaseOrder->supplier->supplier_code }}</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-user"></i></div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase font-bold">Contact</p>
                                            <p class="text-gray-900">{{ $purchaseOrder->supplier->contact_person ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-phone"></i></div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase font-bold">Phone</p>
                                            <p class="text-gray-900">{{ $purchaseOrder->supplier->phone ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-envelope"></i></div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase font-bold">Email</p>
                                            <p class="text-gray-900">{{ $purchaseOrder->supplier->email ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400"><i class="fas fa-star"></i></div>
                                        <div>
                                            <p class="text-xs text-gray-400 uppercase font-bold">Rating</p>
                                            <div class="text-yellow-400 text-xs">
                                                @if($purchaseOrder->supplier->rating)
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $purchaseOrder->supplier->rating ? '' : 'text-gray-200' }}"></i>
                                                    @endfor
                                                @else
                                                    <span class="text-gray-400 italic">Not Rated</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400 italic">
                            <i class="fas fa-exclamation-circle mb-2 text-xl"></i>
                            <p>Supplier information unavailable</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Items --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-soft flex justify-between items-center bg-white">
                    <h3 class="font-display text-lg font-bold text-chocolate">Items Ordered</h3>
                    <span class="bg-chocolate/10 text-chocolate text-xs font-bold px-2.5 py-1 rounded-full">{{ $purchaseOrder->purchaseOrderItems->count() }} Items</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-soft">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Item Description</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Ordered</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Received</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($purchaseOrder->purchaseOrderItems as $item)
                                <tr class="hover:bg-cream-bg/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $item->item->name ?? 'Unknown Item' }}</div>
                                                <div class="flex gap-2 text-[10px] text-gray-500 mt-0.5">
                                                    <span class="font-mono bg-gray-100 px-1.5 rounded">{{ $item->item->item_code ?? '' }}</span>
                                                    @if($item->item->category)
                                                        <span>{{ $item->item->category->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="font-medium text-gray-900">{{ number_format($item->quantity_ordered, 2) }}</span>
                                        <span class="text-xs text-gray-500 ml-1">{{ $item->item->unit->symbol ?? 'pcs' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @php
                                            $received = $item->quantity_received ?? 0;
                                            $ordered = $item->quantity_ordered;
                                            $isComplete = $received >= $ordered;
                                            $qtyClass = $isComplete ? 'text-green-600' : ($received > 0 ? 'text-amber-600' : 'text-gray-400');
                                        @endphp
                                        <span class="font-bold {{ $qtyClass }}">{{ number_format($received, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-600">
                                        ₱{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="font-bold text-chocolate">₱{{ number_format($item->total_price, 2) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No items found in this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right Column - Summary --}}
        <div class="space-y-8">
            
            {{-- Order Summary Card --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-chocolate text-white border-b border-chocolate-dark">
                    <h3 class="font-display text-lg font-bold">Order Summary</h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 font-medium">Subtotal</span>
                            <span class="text-gray-900 font-bold">₱{{ number_format($purchaseOrder->total_amount ?? $purchaseOrder->grand_total, 2) }}</span>
                        </div>
                        
                        @if($purchaseOrder->tax_amount > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Tax</span>
                                <span class="text-gray-900">₱{{ number_format($purchaseOrder->tax_amount, 2) }}</span>
                            </div>
                        @endif

                        @if($purchaseOrder->discount_amount > 0)
                            <div class="flex justify-between items-center text-green-600">
                                <span>Discount</span>
                                <span>-₱{{ number_format($purchaseOrder->discount_amount, 2) }}</span>
                            </div>
                        @endif

                        <div class="border-t border-dashed border-gray-200 my-3 pt-3">
                            <div class="flex justify-between items-end">
                                <span class="text-base font-bold text-chocolate uppercase tracking-wide">Total</span>
                                <span class="text-2xl font-display font-bold text-chocolate">₱{{ number_format($purchaseOrder->grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <div class="text-xs text-center text-gray-400 font-medium uppercase tracking-wider">
                        Verified by Finance
                    </div>
                </div>
            </div>

            {{-- Audit Information --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Audit Trail</h4>
                
                <div class="relative pl-4 border-l-2 border-gray-100 space-y-6">
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-blue-400 ring-4 ring-white"></div>
                        <p class="text-xs text-gray-400 uppercase font-bold">Created</p>
                        <p class="text-sm font-medium text-gray-900">{{ $purchaseOrder->createdBy->name ?? 'System' }}</p>
                        <p class="text-xs text-gray-500">{{ $purchaseOrder->created_at?->format('M d, Y • h:i A') }}</p>
                    </div>

                    @if($purchaseOrder->approvedBy)
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-green-500 ring-4 ring-white"></div>
                        <p class="text-xs text-gray-400 uppercase font-bold">Approved</p>
                        <p class="text-sm font-medium text-gray-900">{{ $purchaseOrder->approvedBy->name }}</p>
                        <div class="text-xs text-green-600 font-medium mt-0.5 bg-green-50 inline-block px-1.5 rounded">Auto-approved</div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $purchaseOrder->approved_at?->format('M d, Y • h:i A') }}</p>
                    </div>
                    @endif

                    @if($purchaseOrder->updated_at && $purchaseOrder->updated_at != $purchaseOrder->created_at)
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-3 h-3 rounded-full bg-gray-300 ring-4 ring-white"></div>
                        <p class="text-xs text-gray-400 uppercase font-bold">Last Update</p>
                        <p class="text-xs text-gray-500">{{ $purchaseOrder->updated_at->format('M d, Y • h:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Source Requests --}}
            @if($purchaseOrder->sourcePurchaseRequests->count() > 0)
            <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Source Requests</h4>
                <div class="space-y-3">
                    @foreach($purchaseOrder->sourcePurchaseRequests as $sourcePR)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-caramel/30 transition-colors group">
                            <div>
                                <div class="text-sm font-bold text-chocolate group-hover:text-caramel transition-colors">#{{ $sourcePR->pr_number }}</div>
                                <div class="text-xs text-gray-500">{{ $sourcePR->department ?? 'General' }}</div>
                            </div>
                            @php
                                $prioClass = match($sourcePR->priority) {
                                    'urgent' => 'bg-red-100 text-red-800',
                                    'high' => 'bg-orange-100 text-orange-800',
                                    default => 'bg-blue-100 text-blue-800'
                                };
                            @endphp
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $prioClass }}">
                                {{ ucfirst($sourcePR->priority) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- CSRF Token Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">



@push('scripts')
<script>
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

function showConfirmationModal(title, message, iconType, onConfirm) {
    if (!document.getElementById('showPageModal')) {
        const modalHTML = `
            <div id="showPageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
                        <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 transition-colors duration-200" id="showModalIcon">
                                    <i class="fas text-lg" id="showModalIconElement"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-display font-bold text-chocolate" id="showModalTitle">Confirm Action</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500" id="showModalMessage">Are you sure?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                            <button type="button" id="showModalConfirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 text-base font-bold text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all transform active:scale-95">
                                Confirm
                            </button>
                            <button type="button" id="showModalCancelBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
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
    
    // Set styles based on type
    modalIcon.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 transition-colors duration-200';
    modalIconElement.className = 'fas text-lg';
    
    switch(iconType) {
        case 'delete':
            modalIcon.classList.add('bg-red-100');
            modalIconElement.classList.add('fa-trash', 'text-red-600');
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-5 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all';
            break;
        default:
            modalIcon.classList.add('bg-blue-100');
            modalIconElement.classList.add('fa-info-circle', 'text-blue-600');
            confirmBtn.className = 'w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-5 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all';
    }
    
    modal.classList.remove('hidden');
    
    // Handle clicks
    const handleConfirm = () => {
        modal.classList.add('hidden');
        onConfirm();
        cleanup();
    };
    
    const handleCancel = () => {
        modal.classList.add('hidden');
        cleanup();
    };
    
    const cleanup = () => {
        confirmBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
    };

    confirmBtn.addEventListener('click', handleConfirm);
    cancelBtn.addEventListener('click', handleCancel);
}

function deleteOrder(orderId) {
    showConfirmationModal(
        'Delete Purchase Order',
        'Are you sure you want to delete this purchase order? This action cannot be undone.',
        'delete',
        () => {
            submitForm(`{{ route('purchasing.po.destroy', '__ID__') }}`.replace('__ID__', orderId), 'DELETE');
        }
    );
}
</script>
@endpush
@endsection