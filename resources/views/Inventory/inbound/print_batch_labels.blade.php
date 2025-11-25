@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    {{-- 1. HEADER WITH STATISTICS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Generate Batch Labels</h1>
            <p class="text-sm text-gray-500 mt-1">Print QR codes for recently received items to enable tracking and FEFO management.</p>
            
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
                            <p class="text-sm font-medium text-gray-500">Perishable</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $stats['perishable'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-snowflake text-orange-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Non-Perishable</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['non_perishable'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-archive text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button id="bulkPrintBtn" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-print mr-2"></i> Print Selected (<span id="selectedCount">0</span>)
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
                    <input type="text" id="searchInput" placeholder="Search by batch number, item name, or supplier..." 
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
            
            {{-- Urgency Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Urgency</label>
                <select id="urgencyFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-chocolate focus:border-chocolate">
                    <option value="all" {{ request('urgency', 'all') === 'all' ? 'selected' : '' }}>All Urgency</option>
                    <option value="critical" {{ request('urgency') === 'critical' ? 'selected' : '' }}>Critical (≤2 days)</option>
                    <option value="high" {{ request('urgency') === 'high' ? 'selected' : '' }}>High (≤7 days)</option>
                    <option value="medium" {{ request('urgency') === 'medium' ? 'selected' : '' }}>Medium (7-14 days)</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="selectAllBtn" class="text-sm text-chocolate hover:text-chocolate-dark font-medium">
                    <i class="fas fa-check-square mr-1"></i> Select All
                </button>
                <button id="clearSelectionBtn" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                    <i class="fas fa-times mr-1"></i> Clear Selection
                </button>
            </div>
            <div class="text-sm text-gray-500">
                Showing {{ $batches->count() }} of {{ method_exists($batches, 'total') ? $batches->total() : $batches->count() }} batches
            </div>
        </div>
    </div>

    {{-- 3. BATCHES GRID --}}
    <div id="batchesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($batches as $batch)
            <div class="batch-card bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col relative overflow-hidden group" 
                 data-batch-id="{{ $batch->id }}"
                 data-category-id="{{ $batch->item->category_id ?? 0 }}"
                 data-status="{{ $batch->status }}"
                 data-expiry-date="{{ $batch->expiry_date?->format('Y-m-d') }}"
                 data-is-perishable="{{ $batch->item->shelf_life_days > 0 ? 'true' : 'false' }}">
                
                {{-- Checkbox and Status Indicators --}}
                <div class="absolute top-2 right-2 flex items-center gap-2">
                    @if($batch->status === 'quarantine')
                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Quarantine
                        </span>
                    @endif
                    
                    @if($batch->expiry_date && now()->diffInDays($batch->expiry_date, false) <= 2)
                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded animate-pulse">
                            <i class="fas fa-fire mr-1"></i>Critical
                        </span>
                    @elseif($batch->expiry_date && now()->diffInDays($batch->expiry_date, false) <= 7)
                        <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded">
                            <i class="fas fa-clock mr-1"></i>Expiring Soon
                        </span>
                    @endif
                    
                    <input type="checkbox" 
                           class="batch-checkbox w-5 h-5 text-chocolate border-gray-300 rounded focus:ring-chocolate batch-select" 
                           data-batch-id="{{ $batch->id }}">
                </div>

                {{-- Label Preview --}}
                <div class="border-2 border-dashed border-gray-300 bg-gray-50 p-4 rounded flex items-center space-x-4 mb-4 group-hover:border-chocolate transition-colors">
                    <div class="w-20 h-20 bg-white border border-gray-200 flex items-center justify-center qr-code-placeholder" data-qr-for="{{ $batch->id }}">
                        <i class="fas fa-qrcode text-4xl text-gray-800"></i>
                    </div>
                    <div class="space-y-1 flex-1">
                        <p class="text-xs font-bold text-gray-900 uppercase line-clamp-2">{{ $batch->item->name }}</p>
                        <p class="text-[10px] text-gray-500">SKU: {{ $batch->item->item_code }}</p>
                        <p class="text-[10px] text-gray-500">Batch: <span class="font-mono font-bold text-gray-800">{{ $batch->batch_number }}</span></p>
                        
                        @if($batch->expiry_date)
                            <p class="text-[10px] {{ now()->diffInDays($batch->expiry_date, false) <= 7 ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                Exp: {{ $batch->expiry_date->format('M d, Y') }}
                                @if(now()->diffInDays($batch->expiry_date, false) >= 0)
                                    ({{ now()->diffInDays($batch->expiry_date, false) }} days)
                                @endif
                            </p>
                        @else
                            <p class="text-[10px] text-gray-400">Non-perishable</p>
                        @endif
                        
                        <p class="text-[10px] text-gray-500">
                            Qty: {{ number_format($batch->quantity, 1) }} {{ $batch->item->unit->symbol ?? 'pcs' }}
                        </p>
                        <p class="text-[10px] text-gray-500">
                            Supplier: {{ $batch->supplier->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Print Quantity Control --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Number of Labels</label>
                    <div class="flex items-center gap-2">
                        <button class="qty-decrease px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition text-sm" 
                                data-batch-id="{{ $batch->id }}">−</button>
                        <input type="number" 
                               class="qty-input w-16 text-center border border-gray-300 rounded py-1 text-sm" 
                               value="10" 
                               min="1" 
                               max="100"
                               data-batch-id="{{ $batch->id }}">
                        <button class="qty-increase px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition text-sm" 
                                data-batch-id="{{ $batch->id }}">+</button>
                        <span class="text-xs text-gray-500 ml-2">copies</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center mt-auto">
                    <span class="text-xs text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $batch->created_at->diffForHumans() }}
                    </span>
                    <div class="flex gap-2">
                        <button class="preview-btn text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded hover:bg-gray-50 transition" 
                                data-batch-id="{{ $batch->id }}">
                            <i class="fas fa-eye mr-1"></i>Preview
                        </button>
                        <button class="print-single-btn text-xs bg-chocolate text-white px-3 py-1.5 rounded hover:bg-chocolate-dark transition" 
                                data-batch-id="{{ $batch->id }}">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-qrcode text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No batches found</h3>
                <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
            </div>
        @endforelse
    </div>

    {{-- 4. PAGINATION --}}
    @if(method_exists($batches, 'hasPages') && $batches->hasPages())
        <div class="flex justify-center">
            {{ $batches->links() }}
        </div>
    @endif
</div>

{{-- 5. MODALS --}}

{{-- QR Code Preview Modal --}}
<div id="qrPreviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">QR Code Preview</h3>
                    <button id="closeQrModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="qrPreviewContent" class="text-center">
                    {{-- Dynamic content will be loaded here --}}
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                <button id="printQrBtn" class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition">
                    <i class="fas fa-print mr-2"></i>Print Label
                </button>
                <button id="cancelQrBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Print Progress Modal --}}
<div id="printProgressModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-sm w-full">
            <div class="p-6 text-center">
                <div class="mb-4">
                    <div class="w-16 h-16 mx-auto mb-4 bg-chocolate bg-opacity-10 rounded-full flex items-center justify-center">
                        <i class="fas fa-print text-2xl text-chocolate"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Printing Labels</h3>
                    <p class="text-sm text-gray-500" id="printProgressText">Preparing labels for printing...</p>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="printProgressBar" class="bg-chocolate h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize batch labels functionality
    const batchLabels = new BatchLabelManager();
    batchLabels.init();
});

class BatchLabelManager {
    constructor() {
        this.selectedBatches = new Map();
        this.batchData = new Map();
    }

    init() {
        this.bindEvents();
        this.loadBatchData();
        this.updateBulkPrintButton();
    }

    bindEvents() {
        // Filter events
        document.getElementById('searchInput')?.addEventListener('input', this.debounce(() => this.applyFilters(), 300));
        document.getElementById('statusFilter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('categoryFilter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('urgencyFilter')?.addEventListener('change', () => this.applyFilters());

        // Selection events
        document.getElementById('selectAllBtn')?.addEventListener('click', () => this.selectAll());
        document.getElementById('clearSelectionBtn')?.addEventListener('click', () => this.clearSelection());
        
        // Bulk print
        document.getElementById('bulkPrintBtn')?.addEventListener('click', () => this.bulkPrint());

        // Individual batch events
        document.querySelectorAll('.batch-select').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handleBatchSelection(e));
        });

        document.querySelectorAll('.qty-increase').forEach(btn => {
            btn.addEventListener('click', (e) => this.changeQuantity(e, 1));
        });

        document.querySelectorAll('.qty-decrease').forEach(btn => {
            btn.addEventListener('click', (e) => this.changeQuantity(e, -1));
        });

        document.querySelectorAll('.preview-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.showQRPreview(e.target.dataset.batchId));
        });

        document.querySelectorAll('.print-single-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.printSingle(e.target.dataset.batchId));
        });

        // Modal events
        document.getElementById('closeQrModal')?.addEventListener('click', () => this.closeQrModal());
        document.getElementById('cancelQrBtn')?.addEventListener('click', () => this.closeQrModal());
        document.getElementById('printQrBtn')?.addEventListener('click', () => this.printFromModal());
    }

    loadBatchData() {
        // Load batch data from server
        document.querySelectorAll('.batch-card').forEach(card => {
            const batchId = card.dataset.batchId;
            if (batchId) {
                this.fetchBatchData(batchId);
            }
        });
    }

    async fetchBatchData(batchId) {
        try {
            const response = await fetch(`/inventory/inbound/labels/batch/${batchId}`);
            const result = await response.json();
            
            if (result.success) {
                this.batchData.set(batchId, result.data);
            }
        } catch (error) {
            console.error('Error fetching batch data:', error);
        }
    }

    handleBatchSelection(event) {
        const checkbox = event.target;
        const batchId = checkbox.dataset.batchId;
        
        if (checkbox.checked) {
            const quantity = parseInt(checkbox.closest('.batch-card').querySelector('.qty-input').value) || 10;
            this.selectedBatches.set(batchId, { quantity });
        } else {
            this.selectedBatches.delete(batchId);
        }
        
        this.updateBulkPrintButton();
    }

    changeQuantity(event, delta) {
        const button = event.target;
        const batchId = button.dataset.batchId;
        const input = button.parentElement.querySelector('.qty-input');
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(1, Math.min(100, currentValue + delta));
        
        input.value = newValue;
        
        // Update selection if batch is selected
        if (this.selectedBatches.has(batchId)) {
            this.selectedBatches.set(batchId, { quantity: newValue });
        }
    }

    selectAll() {
        document.querySelectorAll('.batch-select:not(:checked)').forEach(checkbox => {
            checkbox.checked = true;
            const batchId = checkbox.dataset.batchId;
            const quantity = parseInt(checkbox.closest('.batch-card').querySelector('.qty-input').value) || 10;
            this.selectedBatches.set(batchId, { quantity });
        });
        this.updateBulkPrintButton();
    }

    clearSelection() {
        document.querySelectorAll('.batch-select:checked').forEach(checkbox => {
            checkbox.checked = false;
        });
        this.selectedBatches.clear();
        this.updateBulkPrintButton();
    }

    updateBulkPrintButton() {
        const selectedCount = this.selectedBatches.size;
        const button = document.getElementById('bulkPrintBtn');
        const countSpan = document.getElementById('selectedCount');
        
        if (button && countSpan) {
            countSpan.textContent = selectedCount;
            button.disabled = selectedCount === 0;
        }
    }

    async bulkPrint() {
        if (this.selectedBatches.size === 0) return;

        const batchSelections = Array.from(this.selectedBatches.entries()).map(([batchId, data]) => ({
            batch_id: parseInt(batchId),
            quantity: data.quantity
        }));

        this.showPrintProgress();
        
        try {
            const response = await fetch('/inventory/inbound/labels/print', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ batch_selections: batchSelections })
            });

            const result = await response.json();

            if (result.success) {
                this.hidePrintProgress();
                this.showSuccessMessage(`Successfully created ${result.printed_count} print jobs`);
                
                // Generate print window
                this.generatePrintWindow(result.print_jobs);
            } else {
                this.hidePrintProgress();
                this.showErrorMessage(result.message);
            }
        } catch (error) {
            this.hidePrintProgress();
            this.showErrorMessage('Error processing print jobs');
            console.error('Print error:', error);
        }
    }

    async showQRPreview(batchId) {
        const batchData = this.batchData.get(batchId);
        if (!batchData) {
            await this.fetchBatchData(batchId);
        }

        const data = this.batchData.get(batchId);
        if (!data) return;

        const modal = document.getElementById('qrPreviewModal');
        const content = document.getElementById('qrPreviewContent');

        // Generate QR code
        const qrCanvas = document.createElement('canvas');
        await QRCode.toCanvas(qrCanvas, data.qr_code_data, { width: 200 });

        content.innerHTML = `
            <div class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 bg-gray-50 p-6 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            ${qrCanvas.outerHTML}
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-lg">${data.batch.item_name}</h4>
                            <div class="space-y-1 text-sm">
                                <p><strong>SKU:</strong> ${data.batch.item_code}</p>
                                <p><strong>Batch:</strong> ${data.batch.batch_number}</p>
                                <p><strong>Quantity:</strong> ${data.batch.quantity} ${data.batch.unit_symbol}</p>
                                <p><strong>Mfg Date:</strong> ${data.batch.manufacturing_date}</p>
                                ${data.batch.expiry_date ? `<p><strong>Expiry:</strong> ${data.batch.expiry_date}</p>` : ''}
                                <p><strong>Supplier:</strong> ${data.batch.supplier_name}</p>
                                <p><strong>Location:</strong> ${data.batch.location}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-left">
                    <h5 class="font-medium mb-2">QR Code Data:</h5>
                    <pre class="bg-gray-100 p-2 rounded text-xs overflow-auto">${JSON.stringify(JSON.parse(data.qr_code_data), null, 2)}</pre>
                </div>
            </div>
        `;

        modal.classList.remove('hidden');
    }

    closeQrModal() {
        document.getElementById('qrPreviewModal').classList.add('hidden');
    }

    async printSingle(batchId) {
        const quantity = parseInt(document.querySelector(`[data-batch-id="${batchId}"] .qty-input`).value) || 1;
        
        const batchSelections = [{
            batch_id: parseInt(batchId),
            quantity: quantity
        }];

        try {
            const response = await fetch('/inventory/inbound/labels/print', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ batch_selections: batchSelections })
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage(`Print job created successfully`);
                this.generatePrintWindow(result.print_jobs);
            } else {
                this.showErrorMessage(result.message);
            }
        } catch (error) {
            this.showErrorMessage('Error creating print job');
            console.error('Print error:', error);
        }
    }

    generatePrintWindow(printJobs) {
        const printWindow = window.open('', '_blank');
        const styles = `
            <style>
                @media print {
                    @page { 
                        size: 2in 1in; 
                        margin: 0.1in; 
                    }
                    .label {
                        page-break-inside: avoid;
                        width: 1.8in;
                        height: 0.8in;
                        border: 1px solid #ccc;
                        padding: 0.05in;
                        margin: 0.05in;
                        font-family: Arial, sans-serif;
                        font-size: 8px;
                        display: inline-block;
                        box-sizing: border-box;
                    }
                    .label-content {
                        display: flex;
                        align-items: center;
                        height: 100%;
                    }
                    .qr-section {
                        width: 0.6in;
                        text-align: center;
                    }
                    .info-section {
                        flex: 1;
                        padding-left: 0.05in;
                    }
                    .item-name {
                        font-weight: bold;
                        font-size: 9px;
                        margin-bottom: 2px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .batch-info {
                        font-size: 7px;
                        margin: 1px 0;
                    }
                    .expiry-info {
                        font-size: 7px;
                        font-weight: bold;
                        color: #d32f2f;
                    }
                }
                body { margin: 0; padding: 10px; }
            </style>
        `;

        let labelsHtml = '';
        printJobs.forEach(job => {
            const qrData = JSON.parse(job.qr_code_data);
            const isExpiringSoon = qrData.expiry_date && (new Date(qrData.expiry_date) - new Date()) <= (7 * 24 * 60 * 60 * 1000);
            
            labelsHtml += `
                <div class="label">
                    <div class="label-content">
                        <div class="qr-section">
                            <canvas id="qr-${job.batch_id}-${Math.random()}" width="60" height="60"></canvas>
                        </div>
                        <div class="info-section">
                            <div class="item-name">${job.item_name}</div>
                            <div class="batch-info">Batch: ${job.batch_number}</div>
                            <div class="batch-info">Qty: ${job.quantity} ${qrData.unit || 'pcs'}</div>
                            ${qrData.expiry_date ? `<div class="expiry-info">EXP: ${new Date(qrData.expiry_date).toLocaleDateString()}</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        printWindow.document.write(`
            <html>
                <head>
                    <title>Batch Labels</title>
                    ${styles}
                </head>
                <body>
                    ${labelsHtml}
                </body>
            </html>
        `);

        // Generate QR codes after window loads
        printWindow.onload = () => {
            printJobs.forEach(job => {
                const qrData = JSON.parse(job.qr_code_data);
                const canvases = printWindow.document.querySelectorAll(`canvas[id^="qr-${job.batch_id}"]`);
                canvases.forEach(canvas => {
                    QRCode.toCanvas(canvas, job.qr_code_data, { width: 60, height: 60 });
                });
            });
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        };
    }

    applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const category = document.getElementById('categoryFilter').value;
        const urgency = document.getElementById('urgencyFilter').value;

        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status !== 'all') params.set('status', status);
        if (category !== 'all') params.set('category_id', category);
        if (urgency !== 'all') params.set('urgency', urgency);

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    showPrintProgress() {
        document.getElementById('printProgressModal').classList.remove('hidden');
        const progressBar = document.getElementById('printProgressBar');
        const progressText = document.getElementById('printProgressText');
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 90) progress = 90;
            
            progressBar.style.width = progress + '%';
            progressText.textContent = `Processing... ${Math.round(progress)}%`;
            
            if (progress >= 90) {
                clearInterval(interval);
            }
        }, 200);
    }

    hidePrintProgress() {
        document.getElementById('printProgressModal').classList.add('hidden');
    }

    showSuccessMessage(message) {
        this.showNotification(message, 'success');
    }

    showErrorMessage(message) {
        this.showNotification(message, 'error');
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

/* Print-specific styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .print-section, .print-section * {
        visibility: visible;
    }
    
    .print-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endpush