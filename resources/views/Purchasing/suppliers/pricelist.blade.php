@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Agreed Price Lists</h1>
            <p class="text-sm text-gray-500 mt-1">Manage standard purchasing costs per vendor to control spending.</p>
            <div class="text-xs text-gray-400 mt-1">
                Showing {{ $supplierItems->total() }} price records • 
                {{ $stats['total_active_suppliers'] }} active suppliers • 
                {{ $stats['preferred_supplier_items'] }} preferred items
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm" onclick="exportPriceList()">
                <i class="fas fa-upload mr-2"></i> Export Prices
            </button>
            <button id="updateSelectedBtn" class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition shadow-sm" onclick="updatePrice()">
                <i class="fas fa-edit mr-2"></i> Update Price
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <form method="GET" action="{{ route('purchasing.suppliers.prices') }}" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm" 
                    placeholder="Search Item Name or SKU...">
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select name="supplier_id" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">All Vendors</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                
                @if(request('search') || request('supplier_id'))
                    <a href="{{ route('purchasing.suppliers.prices') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        <i class="fas fa-times mr-1"></i> Clear
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- 3. PRICE TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" onchange="selectAllItems(this)" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Order</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    @forelse($supplierItems as $supplierItem)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $supplierItem->id }}" onchange="toggleItemSelection(this)" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $supplierItem->item->name }}</div>
                                <div class="text-xs text-gray-500">SKU: {{ $supplierItem->item->item_code }}</div>
                                @if($supplierItem->item->category)
                                    <div class="text-[10px] text-gray-400">{{ $supplierItem->item->category->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $supplierItem->supplier->name }}</div>
                                <div class="text-xs text-gray-500">{{ $supplierItem->supplier->supplier_code }}</div>
                                @if($supplierItem->is_preferred)
                                    <span class="text-[10px] bg-green-100 text-green-800 px-1.5 py-0.5 rounded">Preferred</span>
                                @else
                                    <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">Alternate</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-gray-900">₱ {{ number_format($supplierItem->unit_price, 2) }}</span>
                                @if($supplierItem->last_purchase_price)
                                    @if($supplierItem->unit_price < $supplierItem->last_purchase_price)
                                        <i class="fas fa-arrow-down text-green-400 text-xs ml-1" title="Price decreased from ₱{{ number_format($supplierItem->last_purchase_price, 2) }}"></i>
                                    @elseif($supplierItem->unit_price > $supplierItem->last_purchase_price)
                                        <i class="fas fa-arrow-up text-red-400 text-xs ml-1" title="Price increased from ₱{{ number_format($supplierItem->last_purchase_price, 2) }}"></i>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($supplierItem->item->unit)
                                    per {{ $supplierItem->item->unit->name }} ({{ $supplierItem->item->unit->symbol }})
                                @else
                                    per Unit
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($supplierItem->minimum_order_quantity, 0) }}
                                @if($supplierItem->item->unit)
                                    {{ $supplierItem->item->unit->symbol }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $supplierItem->lead_time_days }} day{{ $supplierItem->lead_time_days != 1 ? 's' : '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($supplierItem->updated_at)
                                    {{ $supplierItem->updated_at->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                                @if($supplierItem->last_purchase_date)
                                    <div class="text-xs text-gray-400">
                                        Last: {{ \Carbon\Carbon::parse($supplierItem->last_purchase_date)->format('M d, Y') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($supplierItem->is_preferred)
                                    <span class="text-xs text-green-600">
                                        <i class="fas fa-check mr-1"></i> Preferred
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Backup</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                    <div class="text-lg font-medium">No price records found</div>
                                    <div class="text-sm">
                                        @if(request('search') || request('supplier_id'))
                                            Try adjusting your search criteria or 
                                            <a href="{{ route('purchasing.suppliers.prices') }}" class="text-amber-600 hover:underline">view all records</a>
                                        @else
                                            No supplier items have been configured with pricing yet.
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($supplierItems->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $supplierItems->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

@push('styles')
<style>
.modal-backdrop {
    z-index: 1050;
}

.modal {
    z-index: 1060;
}
</style>
@endpush

@push('scripts')
<!-- Confirmation Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="confirmModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-amber-100">
                <i class="fas fa-exclamation-triangle text-amber-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="items-center px-4 py-3">
                <div class="flex space-x-3 justify-center">
                    <button id="modalCancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button id="modalConfirmBtn" class="px-4 py-2 bg-amber-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="successModal" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="successTitle">Success</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="successMessage">Operation completed successfully.</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="successOkBtn" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let pendingAction = null;

function showConfirmModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmModal').style.display = 'block';
    pendingAction = onConfirm;
}

function hideConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingAction = null;
}

function showSuccessModal(title, message) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').style.display = 'block';
}

function hideSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}

function updatePrice() {
    // Get selected rows or default to bulk update
    const selectedRows = document.querySelectorAll('input[name="selected_items[]"]:checked');
    
    if (selectedRows.length === 0) {
        // No specific items selected, go to bulk update page
        window.location.href = '{{ route("purchasing.suppliers.prices.update") }}';
    } else {
        // Show confirmation modal for bulk update
        showConfirmModal(
            'Confirm Bulk Update',
            `You have selected ${selectedRows.length} item(s) for price updates. This will take you to the bulk update interface. Do you want to continue?`,
            () => {
                const itemIds = Array.from(selectedRows).map(cb => cb.value);
                const params = new URLSearchParams();
                itemIds.forEach(id => params.append('item_ids[]', id));
                
                window.location.href = '{{ route("purchasing.suppliers.prices.update") }}?' + params.toString();
            }
        );
    }
}

function toggleItemSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('bg-blue-50');
    } else {
        row.classList.remove('bg-blue-50');
    }
    
    updateSelectionInfo();
}

function selectAllItems(checkbox) {
    const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        toggleItemSelection(cb);
    });
}

function updateSelectionInfo() {
    const selectedCount = document.querySelectorAll('input[name="selected_items[]"]:checked').length;
    const updateBtn = document.getElementById('updateSelectedBtn');
    
    if (updateBtn) {
        if (selectedCount > 0) {
            updateBtn.textContent = `Update Selected (${selectedCount})`;
            updateBtn.disabled = false;
        } else {
            updateBtn.textContent = 'Update Price';
            updateBtn.disabled = false; // Keep enabled for bulk update
        }
    }
}

function exportPriceList() {
    // Get current filters and redirect to export route
    const params = new URLSearchParams(window.location.search);
    
    // Redirect to export URL with current filters
    window.location.href = '{{ route("purchasing.suppliers.prices.export") }}?' + params.toString();
}

// Auto-submit form when supplier filter changes
document.addEventListener('DOMContentLoaded', function() {
    // Add loading indicator when form is submitted
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            }
        });
    }

    // Modal event handlers
    document.getElementById('modalCancelBtn').addEventListener('click', hideConfirmModal);
    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        if (pendingAction) {
            pendingAction();
            hideConfirmModal();
        }
    });
    
    document.getElementById('successOkBtn').addEventListener('click', hideSuccessModal);
    
    // Close modals when clicking outside
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) hideConfirmModal();
    });
    
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) hideSuccessModal();
    });

    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideConfirmModal();
            hideSuccessModal();
        }
    });
});
</script>
@endpush
@endsection