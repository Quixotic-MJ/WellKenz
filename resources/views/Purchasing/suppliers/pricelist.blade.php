@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Agreed Price Lists</h1>
            <p class="text-sm text-gray-500">Manage standard purchasing costs per vendor to control spending.</p>
            <div class="flex items-center gap-4 mt-3 text-xs font-medium text-gray-400">
                <span class="flex items-center">
                    <i class="fas fa-list-ul mr-1.5 text-caramel"></i>
                    {{ $supplierItems->total() }} records
                </span>
                <span class="flex items-center">
                    <i class="fas fa-building mr-1.5 text-caramel"></i>
                    {{ $stats['total_active_suppliers'] }} suppliers
                </span>
                <span class="flex items-center">
                    <i class="fas fa-star mr-1.5 text-caramel"></i>
                    {{ $stats['preferred_supplier_items'] }} preferred items
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportPriceList()" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-upload mr-2 opacity-70 group-hover:opacity-100"></i> Export Prices
            </button>
            <button id="updateSelectedBtn" onclick="updatePrice()" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-edit mr-2"></i> Update Price
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form method="GET" action="{{ route('purchasing.suppliers.prices') }}" class="flex flex-col lg:flex-row items-center gap-4 w-full">
            <div class="relative w-full lg:flex-1 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                    placeholder="Search Item Name or SKU...">
            </div>
            
            <div class="w-full lg:w-auto relative">
                <select name="supplier_id" onchange="this.form.submit()" 
                    class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer min-w-[200px]">
                    <option value="">All Vendors</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            @if(request('search') || request('supplier_id'))
                <a href="{{ route('purchasing.suppliers.prices') }}" class="px-5 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-50 transition-all shadow-sm flex items-center">
                    <i class="fas fa-times mr-2 text-red-500"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- 3. PRICE TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left w-10">
                            <input type="checkbox" id="selectAll" onchange="selectAllItems(this)" class="rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Item Details</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Supplier</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Unit Price</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Unit</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Logistics</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Updated</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($supplierItems as $supplierItem)
                        <tr class="hover:bg-cream-bg/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $supplierItem->id }}" onchange="toggleItemSelection(this)" class="rounded border-gray-300 text-chocolate focus:ring-chocolate cursor-pointer w-4 h-4">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $supplierItem->item->name }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $supplierItem->item->item_code }}</div>
                                @if($supplierItem->item->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-500 mt-1">
                                        {{ $supplierItem->item->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-chocolate">{{ $supplierItem->supplier->name }}</div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $supplierItem->supplier->supplier_code }}</div>
                                @if($supplierItem->is_preferred)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-green-50 text-green-700 border border-green-100 mt-1">
                                        <i class="fas fa-check mr-1"></i> Preferred
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-50 text-gray-500 border border-gray-200 mt-1">
                                        Backup
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900">₱ {{ number_format($supplierItem->unit_price, 2) }}</span>
                                @if($supplierItem->last_purchase_price)
                                    <div class="text-xs mt-1">
                                        @if($supplierItem->unit_price < $supplierItem->last_purchase_price)
                                            <span class="text-green-600 flex items-center justify-end">
                                                <i class="fas fa-arrow-down mr-1"></i> ₱{{ number_format($supplierItem->last_purchase_price, 2) }}
                                            </span>
                                        @elseif($supplierItem->unit_price > $supplierItem->last_purchase_price)
                                            <span class="text-red-500 flex items-center justify-end">
                                                <i class="fas fa-arrow-up mr-1"></i> ₱{{ number_format($supplierItem->last_purchase_price, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $supplierItem->item->unit ? $supplierItem->item->unit->name . ' (' . $supplierItem->item->unit->symbol . ')' : 'Unit' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs text-gray-600">
                                    <span class="font-bold text-gray-400 uppercase w-16 inline-block">Min Order:</span> 
                                    {{ number_format($supplierItem->minimum_order_quantity, 0) }} {{ $supplierItem->item->unit->symbol ?? '' }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span class="font-bold text-gray-400 uppercase w-16 inline-block">Lead Time:</span> 
                                    {{ $supplierItem->lead_time_days }} day{{ $supplierItem->lead_time_days != 1 ? 's' : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs text-gray-500">
                                    {{ $supplierItem->updated_at ? $supplierItem->updated_at->format('M d, Y') : 'N/A' }}
                                </div>
                                @if($supplierItem->last_purchase_date)
                                    <div class="text-[10px] text-gray-400 mt-1">
                                        Purchased: {{ \Carbon\Carbon::parse($supplierItem->last_purchase_date)->format('M d') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button class="text-gray-400 hover:text-chocolate p-2 rounded-full hover:bg-cream-bg transition-colors" title="Edit Details">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-tags text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate">No Price Records Found</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        @if(request('search') || request('supplier_id'))
                                            Try adjusting your search criteria.
                                        @else
                                            No supplier items have been configured yet.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($supplierItems->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                {{ $supplierItems->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

{{-- MODALS --}}

<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="confirmModal">
    <div class="relative top-20 mx-auto p-6 border border-border-soft w-96 shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-amber-100 border border-amber-200 mb-4">
                <i class="fas fa-exclamation-triangle text-amber-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-display font-bold text-chocolate" id="modalTitle">Confirm Action</h3>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed" id="modalMessage">Are you sure you want to proceed?</p>
            
            <div class="flex justify-center gap-3 mt-6">
                <button id="modalCancelBtn" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button id="modalConfirmBtn" class="px-5 py-2.5 bg-amber-600 text-white text-sm font-bold rounded-lg hover:bg-amber-700 shadow-md transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fixed inset-0 bg-chocolate/20 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden transition-opacity" id="successModal">
    <div class="relative top-20 mx-auto p-6 border border-border-soft w-96 shadow-2xl rounded-xl bg-white transform transition-all scale-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-green-100 border border-green-200 mb-4">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-display font-bold text-chocolate" id="successTitle">Success</h3>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed" id="successMessage">Operation completed successfully.</p>
            
            <div class="mt-6">
                <button id="successOkBtn" class="w-full px-5 py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 shadow-md transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
// JavaScript logic preserved 100%
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
    const selectedRows = document.querySelectorAll('input[name="selected_items[]"]:checked');
    
    if (selectedRows.length === 0) {
        // Bulk update all
        window.location.href = '{{ route("purchasing.suppliers.prices.update") }}';
    } else {
        // Update specific items
        showConfirmModal(
            'Confirm Bulk Update',
            `You have selected ${selectedRows.length} item(s) for price updates. Continue to bulk update?`,
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
        row.classList.add('bg-cream-bg');
    } else {
        row.classList.remove('bg-cream-bg');
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
            updateBtn.innerHTML = `<i class="fas fa-edit mr-2"></i> Update Selected (${selectedCount})`;
        } else {
            updateBtn.innerHTML = `<i class="fas fa-edit mr-2"></i> Update Price`;
        }
    }
}

function exportPriceList() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("purchasing.suppliers.prices.export") }}?' + params.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Optional loading state logic if needed
        });
    }

    document.getElementById('modalCancelBtn').addEventListener('click', hideConfirmModal);
    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        if (pendingAction) {
            pendingAction();
            hideConfirmModal();
        }
    });
    
    document.getElementById('successOkBtn').addEventListener('click', hideSuccessModal);
    
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) hideConfirmModal();
    });
    
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) hideSuccessModal();
    });

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