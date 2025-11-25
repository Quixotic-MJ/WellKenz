@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    {{-- 1. HEADER WITH STATISTICS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Batch Logs</h1>
            <p class="text-sm text-gray-500 mt-1">View and track all received deliveries with their batch information and item details.</p>
            
            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Batches</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-cube text-blue-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['expiring_soon'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-red-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Batches</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Quarantine</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $stats['quarantine'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button id="exportBtn" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Export Logs
            </button>
            <button id="refreshBtn" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
    </div>

    {{-- 2. FILTER CONTROLS --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search by batch number, item name, supplier, or PO number..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate" 
                           value="{{ request('search') }}">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="quarantine" {{ request('status') === 'quarantine' ? 'selected' : '' }}>Quarantine</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="consumed" {{ request('status') === 'consumed' ? 'selected' : '' }}>Consumed</option>
                </select>
            </div>
            
            {{-- Category Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="categoryFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                    <option value="all" {{ request('category_id', 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Date Range Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select id="dateFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                    <option value="all" {{ request('date_range', 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ request('date_range') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="resetFiltersBtn" class="text-sm text-chocolate hover:text-chocolate-dark font-medium">
                    <i class="fas fa-undo mr-1"></i> Reset Filters
                </button>
                <button id="advancedFiltersBtn" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                    <i class="fas fa-filter mr-1"></i> Advanced Filters
                </button>
            </div>
            <div class="text-sm text-gray-500">
                Showing {{ $batches->count() }} of {{ method_exists($batches, 'total') ? $batches->total() : $batches->count() }} batch records
            </div>
        </div>
    </div>

    {{-- 3. BATCH LOGS TABLE --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="batch_number">
                                <span>Batch Details</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="item_name">
                                <span>Item Information</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="quantity">
                                <span>Quantity & Cost</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="manufacturing_date">
                                <span>Dates</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="supplier">
                                <span>Supplier</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="flex items-center space-x-1 hover:text-gray-700 sort-btn" data-sort="status">
                                <span>Status</span>
                                <i class="fas fa-sort text-gray-400"></i>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($batches as $index => $batch)
                        <tr class="hover:bg-gray-50 batch-row" data-batch-id="{{ $batch->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="batch-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" 
                                       data-batch-id="{{ $batch->id }}">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                    <div class="text-sm text-gray-500">ID: {{ $batch->id }}</div>
                                    @if($batch->location)
                                        <div class="text-sm text-gray-500">Location: {{ $batch->location }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->item->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ $batch->item->item_code ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $batch->item->category->name ?? 'Uncategorized' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($batch->quantity, 2) }} {{ $batch->item->unit->symbol ?? 'pcs' }}
                                    </div>
                                    <div class="text-sm text-gray-500">Cost: ₱{{ number_format($batch->unit_cost, 2) }}/unit</div>
                                    <div class="text-sm text-gray-900 font-medium">
                                        Total: ₱{{ number_format($batch->quantity * $batch->unit_cost, 2) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    @if($batch->manufacturing_date)
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                                            Mfg: {{ $batch->manufacturing_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                    @if($batch->expiry_date)
                                        <div class="text-sm {{ now()->diffInDays($batch->expiry_date, false) <= 7 ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                            <i class="fas fa-calendar-times mr-1 text-gray-400"></i>
                                            Exp: {{ $batch->expiry_date->format('M d, Y') }}
                                            @if(now()->diffInDays($batch->expiry_date, false) >= 0)
                                                ({{ now()->diffInDays($batch->expiry_date, false) }} days left)
                                            @else
                                                (Expired)
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500">No expiry date</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->supplier->name ?? 'N/A' }}</div>
                                    @if($batch->supplier->contact_person)
                                        <div class="text-sm text-gray-500">{{ $batch->supplier->contact_person }}</div>
                                    @endif
                                    @if($batch->supplier->phone)
                                        <div class="text-sm text-gray-500">{{ $batch->supplier->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $batch->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $batch->status === 'quarantine' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $batch->status === 'expired' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $batch->status === 'consumed' ? 'bg-blue-100 text-blue-800' : '' }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                                @if($batch->expiry_date && now()->diffInDays($batch->expiry_date, false) <= 2 && now()->diffInDays($batch->expiry_date, false) >= 0)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Critical
                                        </span>
                                    </div>
                                @elseif($batch->expiry_date && now()->diffInDays($batch->expiry_date, false) <= 7 && now()->diffInDays($batch->expiry_date, false) >= 0)
                                    <div class="mt-1">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-clock mr-1"></i>Expiring Soon
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button class="view-batch-btn text-chocolate hover:text-chocolate-dark" 
                                            data-batch-id="{{ $batch->id }}" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="print-label-btn text-green-600 hover:text-green-900" 
                                            data-batch-id="{{ $batch->id }}" title="Print Label">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clipboard-list text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No batch records found</h3>
                                <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 4. PAGINATION --}}
        @if(method_exists($batches, 'hasPages') && $batches->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($batches->previousPageUrl())
                        <a href="{{ $batches->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    @if($batches->nextPageUrl())
                        <a href="{{ $batches->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $batches->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $batches->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $batches->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $batches->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- 5. MODALS --}}

{{-- Batch Details Modal --}}
<div id="batchDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Batch Details</h3>
                    <button id="closeBatchModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="batchDetailsContent">
                    {{-- Dynamic content will be loaded here --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Advanced Filters Modal --}}
<div id="advancedFiltersModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Advanced Filters</h3>
                    <button id="closeAdvancedFiltersModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="advancedFiltersForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                            <select name="supplier_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                            <select name="item_type" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                                <option value="">All Types</option>
                                <option value="raw_material">Raw Material</option>
                                <option value="finished_good">Finished Good</option>
                                <option value="semi_finished">Semi-Finished</option>
                                <option value="supply">Supply</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Quantity</label>
                            <input type="number" name="min_quantity" step="0.001" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Quantity</label>
                            <input type="number" name="max_quantity" step="0.001" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manufacturing Date From</label>
                            <input type="date" name="manufacturing_date_from" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manufacturing Date To</label>
                            <input type="date" name="manufacturing_date_to" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date From</label>
                            <input type="date" name="expiry_date_from" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date To</label>
                            <input type="date" name="expiry_date_to" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                <button id="applyAdvancedFiltersBtn" class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition">
                    Apply Filters
                </button>
                <button id="clearAdvancedFiltersBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Clear All
                </button>
                <button id="cancelAdvancedFiltersBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize batch logs functionality
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
        // Filter events
        document.getElementById('searchInput')?.addEventListener('input', this.debounce(() => this.applyFilters(), 300));
        document.getElementById('statusFilter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('categoryFilter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('dateFilter')?.addEventListener('change', () => this.applyFilters());

        // Button events
        document.getElementById('resetFiltersBtn')?.addEventListener('click', () => this.resetFilters());
        document.getElementById('advancedFiltersBtn')?.addEventListener('click', () => this.showAdvancedFilters());
        document.getElementById('refreshBtn')?.addEventListener('click', () => this.refreshData());
        document.getElementById('exportBtn')?.addEventListener('click', () => this.exportData());

        // Selection events
        document.getElementById('selectAll')?.addEventListener('change', (e) => this.handleSelectAll(e));
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handleBatchSelection(e));
        });

        // Action button events
        document.querySelectorAll('.view-batch-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewBatchDetails(e.currentTarget.dataset.batchId));
        });

        document.querySelectorAll('.edit-batch-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.editBatch(e.currentTarget.dataset.batchId));
        });

        document.querySelectorAll('.print-label-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.printLabel(e.currentTarget.dataset.batchId));
        });

        document.querySelectorAll('.quarantine-batch-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.quarantineBatch(e.currentTarget.dataset.batchId));
        });

        // Sort events
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleSort(e.target.dataset.sort));
        });

        // Modal events
        document.getElementById('closeBatchModal')?.addEventListener('click', () => this.closeBatchModal());
        document.getElementById('closeAdvancedFiltersModal')?.addEventListener('click', () => this.closeAdvancedFiltersModal());
        document.getElementById('applyAdvancedFiltersBtn')?.addEventListener('click', () => this.applyAdvancedFilters());
        document.getElementById('clearAdvancedFiltersBtn')?.addEventListener('click', () => this.clearAdvancedFilters());
        document.getElementById('cancelAdvancedFiltersBtn')?.addEventListener('click', () => this.closeAdvancedFiltersModal());
    }

    applyFilters() {
        const search = document.getElementById('searchInput')?.value || '';
        const status = document.getElementById('statusFilter')?.value || 'all';
        const category = document.getElementById('categoryFilter')?.value || 'all';
        const dateRange = document.getElementById('dateFilter')?.value || 'all';

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status !== 'all') params.set('status', status);
        if (category !== 'all') params.set('category_id', category);
        if (dateRange !== 'all') params.set('date_range', dateRange);

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    resetFilters() {
        window.location.href = window.location.pathname;
    }

    refreshData() {
        window.location.reload();
    }

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
                // Create download link
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

    showAdvancedFilters() {
        document.getElementById('advancedFiltersModal').classList.remove('hidden');
    }

    closeAdvancedFiltersModal() {
        document.getElementById('advancedFiltersModal').classList.add('hidden');
    }

    applyAdvancedFilters() {
        const form = document.getElementById('advancedFiltersForm');
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value) params.set(key, value);
        }

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    clearAdvancedFilters() {
        document.getElementById('advancedFiltersForm').reset();
    }

    handleSelectAll(event) {
        const isChecked = event.target.checked;
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
            if (isChecked) {
                this.selectedBatches.add(checkbox.dataset.batchId);
            } else {
                this.selectedBatches.delete(checkbox.dataset.batchId);
            }
        });
        this.updateSelectedCount();
    }

    handleBatchSelection(event) {
        const checkbox = event.target;
        const batchId = checkbox.dataset.batchId;
        
        if (checkbox.checked) {
            this.selectedBatches.add(batchId);
        } else {
            this.selectedBatches.delete(batchId);
        }
        
        this.updateSelectedCount();
    }

    updateSelectedCount() {
        const count = this.selectedBatches.size;
        // You can add a UI element to show selected count if needed
        console.log(`${count} batches selected`);
    }

    async viewBatchDetails(batchId) {
        try {
            console.log('Fetching batch details for ID:', batchId);
            const response = await fetch(`/inventory/inbound/batch-logs/${batchId}/details`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('Batch details API response:', result);
            
            if (result.success) {
                console.log('Batch data:', result.data);
                this.showBatchDetails(result.data);
            } else {
                this.showNotification(result.message || 'Failed to load batch details', 'error');
                console.error('API returned error:', result);
            }
        } catch (error) {
            this.showNotification('Error loading batch details: ' + error.message, 'error');
            console.error('Error fetching batch details:', error);
        }
    }

    showBatchDetails(batchData) {
        const modal = document.getElementById('batchDetailsModal');
        const content = document.getElementById('batchDetailsContent');

        content.innerHTML = `
            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Basic Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Batch Number</label>
                            <p class="text-base text-gray-900">${batchData.batch_number}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="text-base text-gray-900">${batchData.status_badge?.text || batchData.status}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Location</label>
                            <p class="text-base text-gray-900">${batchData.location || 'Not specified'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Created At</label>
                            <p class="text-base text-gray-900">${batchData.created_at}</p>
                        </div>
                    </div>
                </div>

                <!-- Item Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Item Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Item Name</label>
                            <p class="text-base text-gray-900">${batchData.item?.name || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Item Code</label>
                            <p class="text-base text-gray-900">${batchData.item?.item_code || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Barcode</label>
                            <p class="text-base text-gray-900">${batchData.item?.barcode || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Unit</label>
                            <p class="text-base text-gray-900">${batchData.item?.unit?.symbol || 'pcs'}</p>
                        </div>
                    </div>
                </div>

                <!-- Quantity & Cost -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Quantity & Cost</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Quantity</label>
                            <p class="text-base text-gray-900">${batchData.quantity} ${batchData.item?.unit?.symbol || 'pcs'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Unit Cost</label>
                            <p class="text-base text-gray-900">₱${parseFloat(batchData.unit_cost).toFixed(2)}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Total Cost</label>
                            <p class="text-base text-gray-900 font-semibold">₱${(batchData.quantity * batchData.unit_cost).toFixed(2)}</p>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Dates</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Manufacturing Date</label>
                            <p class="text-base text-gray-900">${batchData.manufacturing_date || 'Not specified'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Expiry Date</label>
                            <p class="text-base text-gray-900">${batchData.expiry_date || 'No expiry'}</p>
                            ${batchData.is_expiring_soon ? '<p class="text-sm text-red-600 font-medium">⚠️ Expiring Soon!</p>' : ''}
                            ${batchData.is_expired ? '<p class="text-sm text-red-600 font-medium">🚫 Expired</p>' : ''}
                        </div>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Supplier Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Supplier Name</label>
                            <p class="text-base text-gray-900">${batchData.supplier?.name || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p class="text-base">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${batchData.status_badge?.class || 'bg-gray-100 text-gray-800'}">
                                    ${batchData.status_badge?.text || batchData.status}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                ${batchData.expiry_days !== null ? `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold mb-3">Expiry Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Days Until Expiry</label>
                            <p class="text-base text-gray-900">${batchData.expiry_days} days</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Expiry Status</label>
                            <p class="text-base text-gray-900">${batchData.expiry_status.replace('_', ' ').toUpperCase()}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        modal.classList.remove('hidden');
    }

    closeBatchModal() {
        document.getElementById('batchDetailsModal').classList.add('hidden');
    }

    editBatch(batchId) {
        // Redirect to edit page or open edit modal
        window.location.href = `/inventory/inbound/batch-logs/${batchId}/edit`;
    }

    printLabel(batchId) {
        // Redirect to labels page with this batch pre-selected
        window.location.href = `/inventory/inbound/labels?batch=${batchId}`;
    }

    quarantineBatch(batchId) {
        if (confirm('Are you sure you want to move this batch to quarantine?')) {
            this.updateBatchStatus(batchId, 'quarantine');
        }
    }

    async updateBatchStatus(batchId, status) {
        try {
            const response = await fetch(`/inventory/inbound/batch-logs/${batchId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Batch status updated successfully', 'success');
                window.location.reload();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Error updating batch status', 'error');
            console.error('Error:', error);
        }
    }

    handleSort(sortField) {
        const url = new URL(window.location);
        const currentSort = url.searchParams.get('sort');
        const currentOrder = url.searchParams.get('order') || 'asc';
        
        let newOrder = 'asc';
        if (currentSort === sortField && currentOrder === 'asc') {
            newOrder = 'desc';
        }
        
        url.searchParams.set('sort', sortField);
        url.searchParams.set('order', newOrder);
        
        window.location.href = url.toString();
    }

    getCurrentFilters() {
        return {
            search: document.getElementById('searchInput')?.value || '',
            status: document.getElementById('statusFilter')?.value || 'all',
            category: document.getElementById('categoryFilter')?.value || 'all',
            dateRange: document.getElementById('dateFilter')?.value || 'all'
        };
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
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
.batch-row:hover {
    background-color: #f9fafb;
}

.sort-btn i.fa-sort {
    transition: all 0.2s ease;
}

.sort-btn:hover i.fa-sort {
    color: #92400e;
}

.sort-btn.sorted-asc i.fa-sort:before {
    content: "\f0de";
    color: #92400e;
}

.sort-btn.sorted-desc i.fa-sort:before {
    content: "\f0dd";
    color: #92400e;
}
</style>
@endpush