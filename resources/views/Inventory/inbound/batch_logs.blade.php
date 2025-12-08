@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Batch Logs</h1>
            <p class="text-sm text-gray-500">Track inventory batches, expiry dates, and lifecycle status.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button id="refreshBtn" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i> Refresh
            </button>
            <button id="exportBtn" class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-download mr-2"></i> Export Logs
            </button>
        </div>
    </div>

    {{-- 2. STATISTICS CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Batches</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="font-display text-3xl font-bold text-chocolate">{{ $stats['total'] }}</p>
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 mb-1">
                        <i class="fas fa-cube"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-green-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Active</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="font-display text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600 mb-1">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Expiring Soon</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="font-display text-3xl font-bold text-red-600">{{ $stats['expiring_soon'] }}</p>
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600 mb-1">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Quarantine</p>
                <div class="flex items-end justify-between mt-2">
                    <p class="font-display text-3xl font-bold text-amber-600">{{ $stats['quarantine'] }}</p>
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 mb-1">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SIMPLIFIED FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4 items-end">
            {{-- Search --}}
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Search</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    </div>
                    <input type="text" id="searchInput" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                        placeholder="Search by batch, item, or supplier..." 
                        value="{{ request('search') }}">
                </div>
            </div>
            
            {{-- Essential Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full lg:w-auto">
                <div class="w-full">
                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Status</label>
                    <div class="relative">
                        <select id="statusFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="consumed" {{ request('status') === 'consumed' ? 'selected' : '' }}>Consumed</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-xs font-bold text-chocolate uppercase tracking-wide mb-1">Supplier</label>
                    <div class="relative">
                        <select id="supplierFilter" class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between pt-4 border-t border-border-soft">
            <div class="flex items-center gap-3">
                <button id="resetFiltersBtn" class="text-xs font-bold text-gray-500 hover:text-chocolate transition-colors flex items-center">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
            <div class="text-xs font-medium text-gray-500">
                Showing <span class="font-bold text-chocolate">{{ $batches->count() }}</span> of {{ method_exists($batches, 'total') ? $batches->total() : $batches->count() }} records
            </div>
        </div>
    </div>

    {{-- 4. BATCH CARDS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($batches as $batch)
            @php
                $expiryDate = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date) : null;
                $expiryDays = $expiryDate ? now()->diffInDays($expiryDate, false) : null;
                $isExpired = $expiryDays !== null && $expiryDays < 0;
                $isExpiringSoon = $expiryDays !== null && !$isExpired && $expiryDays <= 7;
                
                // Border colors based on status
                if ($batch->status === 'consumed') {
                    $borderColor = 'border-gray-300';
                    $opacityClass = 'opacity-60';
                } elseif ($isExpired) {
                    $borderColor = 'border-red-500';
                    $opacityClass = '';
                } elseif ($isExpiringSoon || $batch->status === 'quarantine') {
                    $borderColor = 'border-yellow-500';
                    $opacityClass = '';
                } else {
                    $borderColor = 'border-green-500';
                    $opacityClass = '';
                }
                
                // Status badge colors
                $statusColors = [
                    'active' => 'bg-green-100 text-green-800 border-green-200',
                    'quarantine' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'expired' => 'bg-red-100 text-red-800 border-red-200',
                    'consumed' => 'bg-gray-100 text-gray-800 border-gray-200',
                ];
                $statusColorClass = $statusColors[$batch->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                
                // Expiry text and color
                $expiryText = '';
                $expiryColor = '';
                if ($expiryDate) {
                    if ($isExpired) {
                        $expiryText = 'Expired ' . abs($expiryDays) . ' days ago';
                        $expiryColor = 'text-red-600';
                    } elseif ($isExpiringSoon) {
                        $expiryText = 'Expires in ' . $expiryDays . ' days';
                        $expiryColor = 'text-red-600 font-bold';
                    } else {
                        $expiryText = 'Expires in ' . $expiryDays . ' days';
                        $expiryColor = 'text-gray-600';
                    }
                } else {
                    $expiryText = 'No Expiry';
                    $expiryColor = 'text-gray-500';
                }
            @endphp
            
            <div class="bg-white rounded-xl border-l-4 {{ $borderColor }} {{ $opacityClass }} shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden group">
                {{-- Card Header --}}
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="font-mono font-bold text-sm text-chocolate">{{ $batch->batch_number }}</div>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusColorClass }}">
                            {{ ucfirst($batch->status) }}
                        </span>
                    </div>
                </div>
                
                {{-- Card Body --}}
                <div class="p-4 space-y-3">
                    {{-- Item Name (Large) --}}
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 leading-tight">{{ $batch->item->name ?? 'N/A' }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $batch->item->item_code ?? 'N/A' }}</p>
                    </div>
                    
                    {{-- Supplier (Smaller) --}}
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-truck text-caramel mr-1"></i>
                        {{ $batch->supplier->name ?? 'N/A' }}
                    </div>
                    
                    {{-- Metrics Row --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Quantity</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ number_format($batch->quantity, 1) }}
                                <span class="text-sm font-normal text-gray-500">{{ $batch->item->unit->symbol ?? 'pcs' }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Expiry</div>
                            <div class="text-sm {{ $expiryColor }}">
                                {{ $expiryText }}
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Card Footer --}}
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs text-gray-400">
                        {{ $batch->location ?? 'Main Storage' }}
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="print-label-btn p-2 text-chocolate hover:text-white hover:bg-chocolate rounded-lg transition-all tooltip text-sm" data-batch-id="{{ $batch->id }}" title="Print Label">
                            <i class="fas fa-print"></i>
                        </button>
                        <button class="view-batch-btn p-2 text-gray-500 hover:text-chocolate hover:bg-gray-200 rounded-lg transition-all tooltip text-sm" data-batch-id="{{ $batch->id }}" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white border border-border-soft rounded-xl p-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                            <i class="fas fa-clipboard-list text-chocolate/30 text-3xl"></i>
                        </div>
                        <p class="font-display text-lg font-bold text-chocolate">No batch records found</p>
                        <p class="text-sm text-gray-400 mt-1">Try adjusting your search filters.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    
    {{-- 5. PAGINATION --}}
    @if(method_exists($batches, 'hasPages') && $batches->hasPages())
    <div class="bg-white px-6 py-4 border-t border-border-soft rounded-xl">
        {{ $batches->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- MODALS SECTION --}}

{{-- Batch Details Modal --}}
<div id="batchDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="document.getElementById('batchDetailsModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-border-soft">
            <div class="bg-chocolate px-6 py-4 flex justify-between items-center">
                <h3 class="font-display text-lg font-bold text-white">Batch Details</h3>
                <button id="closeBatchModal" class="text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-6 bg-cream-bg max-h-[70vh] overflow-y-auto custom-scrollbar" id="batchDetailsContent">
                {{-- Content loaded dynamically --}}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// JavaScript logic for card-based layout
document.addEventListener('DOMContentLoaded', function() {
    const batchLogs = new BatchLogsManager();
    batchLogs.init();
});

class BatchLogsManager {
    constructor() {
        this.selectedBatches = new Set();
        this.currentFilters = {};
    }

    init() {
        this.bindEvents();
        this.updateSelectedCount();
    }

    bindEvents() {
        document.getElementById('searchInput')?.addEventListener('input', this.debounce(() => this.applyFilters(), 300));
        document.getElementById('statusFilter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('supplierFilter')?.addEventListener('change', () => this.applyFilters());

        document.getElementById('resetFiltersBtn')?.addEventListener('click', () => this.resetFilters());
        document.getElementById('refreshBtn')?.addEventListener('click', () => this.refreshData());
        document.getElementById('exportBtn')?.addEventListener('click', () => this.exportData());

        document.querySelectorAll('.view-batch-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewBatchDetails(e.currentTarget.dataset.batchId));
        });

        document.querySelectorAll('.print-label-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.printLabel(e.currentTarget.dataset.batchId));
        });

        document.getElementById('closeBatchModal')?.addEventListener('click', () => this.closeBatchModal());
    }

    applyFilters() {
        const search = document.getElementById('searchInput')?.value || '';
        const status = document.getElementById('statusFilter')?.value || 'all';
        const supplier = document.getElementById('supplierFilter')?.value || '';

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status !== 'all') params.set('status', status);
        if (supplier) params.set('supplier_id', supplier);

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    resetFilters() { window.location.href = window.location.pathname; }
    refreshData() { window.location.reload(); }

    async exportData() {
        try {
            const response = await fetch('/inventory/inbound/batch-logs/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    filters: this.getCurrentFilters(),
                    selected_batches: Array.from(this.selectedBatches)
                })
            });
            const result = await response.json();
            if (result.success) {
                const link = document.createElement('a');
                link.href = result.download_url;
                link.download = result.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                this.showNotification('Batch logs exported successfully', 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error exporting data', 'error');
            console.error('Export error:', error);
        }
    }

    async viewBatchDetails(batchId) {
        try {
            const response = await fetch(`/inventory/inbound/batch-logs/${batchId}/details`);
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const result = await response.json();
            if (result.success) this.showBatchDetails(result.data);
            else this.showNotification(result.message || 'Failed to load batch details', 'error');
        } catch (error) {
            this.showNotification('Error loading batch details: ' + error.message, 'error');
        }
    }

    showBatchDetails(batchData) {
        const modal = document.getElementById('batchDetailsModal');
        const content = document.getElementById('batchDetailsContent');
        
        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm">
                    <h4 class="text-lg font-bold text-chocolate mb-4 border-b border-border-soft pb-2">Basic Information</h4>
                    <div class="grid grid-cols-2 gap-6 text-sm">
                        <div><span class="text-gray-500 font-bold block text-xs uppercase tracking-wide">Batch #</span> ${batchData.batch_number}</div>
                        <div><span class="text-gray-500 font-bold block text-xs uppercase tracking-wide">Status</span> ${batchData.status}</div>
                        <div><span class="text-gray-500 font-bold block text-xs uppercase tracking-wide">Location</span> ${batchData.location || 'N/A'}</div>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-border-soft shadow-sm">
                    <h4 class="text-lg font-bold text-chocolate mb-4 border-b border-border-soft pb-2">Item Information</h4>
                    <div class="grid grid-cols-2 gap-6 text-sm">
                        <div><span class="text-gray-500 font-bold block text-xs uppercase tracking-wide">Name</span> ${batchData.item?.name || 'N/A'}</div>
                        <div><span class="text-gray-500 font-bold block text-xs uppercase tracking-wide">Code</span> ${batchData.item?.item_code || 'N/A'}</div>
                    </div>
                </div>
            </div>
        `;
        modal.classList.remove('hidden');
    }

    closeBatchModal() { document.getElementById('batchDetailsModal').classList.add('hidden'); }
    printLabel(batchId) { window.location.href = `/inventory/inbound/labels?batch=${batchId}`; }

    getCurrentFilters() {
        return {
            search: document.getElementById('searchInput')?.value || '',
            status: document.getElementById('statusFilter')?.value || 'all',
            supplier: document.getElementById('supplierFilter')?.value || ''
        };
    }

    showNotification(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-5 right-5 p-4 rounded-lg shadow-xl z-50 text-white font-bold text-sm transform transition-all duration-300 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    debounce(func, wait) {
        let timeout;
        return function(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endpush
@endsection