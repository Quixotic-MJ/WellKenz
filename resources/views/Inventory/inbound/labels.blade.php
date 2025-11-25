@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">Batch Labels</h1>
            <p class="text-sm text-gray-500 mt-1">Print labels for selected batches to track inventory and expiry dates.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button id="selectAllBtn" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-check-double mr-2"></i> Select All
            </button>
            <button id="printLabelsBtn" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Selected
            </button>
            <button id="backToBatchesBtn" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Batches
            </button>
        </div>
    </div>

    {{-- Batch Selection --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold mb-4">Select Batches for Label Printing</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="batchSelectionGrid">
            {{-- Batch cards will be loaded here dynamically --}}
        </div>
        
        <div class="mt-6 text-center text-gray-500" id="noBatchesMessage">
            <i class="fas fa-info-circle mr-2"></i>
            Select batches from the Batch Logs page to print labels
        </div>
    </div>

    {{-- Print Preview (Hidden by default) --}}
    <div id="printPreview" class="hidden">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Print Preview</h3>
                <button id="closePrintPreview" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="labelPreview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4 border-2 border-dashed border-gray-300">
                {{-- Label previews will be shown here --}}
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <button id="cancelPrintBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button id="confirmPrintBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-print mr-2"></i> Print Labels
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Batch Selection Modal for Loading from URL --}}
@if(request('batch'))
<div id="batchSelectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Load Batch for Label Printing</h3>
                    <button id="closeBatchSelectModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-4">
                    Batch ID {{ request('batch') }} has been selected for label printing. Click "Load Batch" to proceed or "Cancel" to go back.
                </p>
                
                <div class="flex justify-end gap-3">
                    <button id="cancelBatchLoadBtn" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button id="loadBatchBtn" class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition">
                        Load Batch
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize label printing functionality
    const labelPrinter = new BatchLabelPrinter();
    labelPrinter.init();
});

class BatchLabelPrinter {
    constructor() {
        this.selectedBatches = new Set();
        this.allBatches = [];
    }

    init() {
        this.bindEvents();
        this.loadBatches();
        
        // Handle URL batch parameter
        const urlBatchId = new URLSearchParams(window.location.search).get('batch');
        if (urlBatchId) {
            this.showBatchSelectModal();
            this.setupBatchSelectModal(urlBatchId);
        }
    }

    bindEvents() {
        // Button events
        document.getElementById('selectAllBtn')?.addEventListener('click', () => this.selectAllBatches());
        document.getElementById('printLabelsBtn')?.addEventListener('click', () => this.showPrintPreview());
        document.getElementById('backToBatchesBtn')?.addEventListener('click', () => {
            window.location.href = '/inventory/inbound/batch-logs';
        });

        // Print preview events
        document.getElementById('closePrintPreview')?.addEventListener('click', () => this.hidePrintPreview());
        document.getElementById('cancelPrintBtn')?.addEventListener('click', () => this.hidePrintPreview());
        document.getElementById('confirmPrintBtn')?.addEventListener('click', () => this.printLabels());

        // Modal events
        document.getElementById('closeBatchSelectModal')?.addEventListener('click', () => this.hideBatchSelectModal());
        document.getElementById('cancelBatchLoadBtn')?.addEventListener('click', () => this.hideBatchSelectModal());
        document.getElementById('loadBatchBtn')?.addEventListener('click', () => this.loadBatchFromModal());
    }

    async loadBatches() {
        try {
            // Load batch data from the API
            const response = await fetch('/inventory/inbound/batch-logs');
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const html = await response.text();
            const batchData = this.extractBatchDataFromHTML(html);
            
            this.allBatches = batchData;
            this.renderBatchSelection();
            
        } catch (error) {
            console.error('Error loading batches:', error);
            this.showNotification('Error loading batch data: ' + error.message, 'error');
        }
    }

    extractBatchDataFromHTML(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const batches = [];
        
        // Find all batch rows in the table
        const batchRows = doc.querySelectorAll('.batch-row');
        
        batchRows.forEach(row => {
            const batchId = row.dataset.batchId;
            if (!batchId) return;
            
            // Extract data from table cells
            const cells = row.querySelectorAll('td');
            if (cells.length < 8) return;
            
            const batchNumberCell = cells[1]; // Batch details cell
            const itemCell = cells[2]; // Item information cell
            const quantityCell = cells[3]; // Quantity & cost cell
            const statusCell = cells[6]; // Status cell
            
            const batchNumber = batchNumberCell?.querySelector('.text-sm.font-medium')?.textContent?.trim() || '';
            const itemName = itemCell?.querySelector('.text-sm.font-medium')?.textContent?.trim() || '';
            const itemCode = itemCell?.querySelector('.text-sm.text-gray-500')?.textContent?.trim() || '';
            const quantity = quantityCell?.querySelector('.text-sm.font-medium')?.textContent?.trim() || '';
            const statusBadge = statusCell?.querySelector('.inline-flex')?.textContent?.trim() || '';
            
            if (batchId && batchNumber && itemName) {
                batches.push({
                    id: batchId,
                    batch_number: batchNumber,
                    item_name: itemName,
                    item_code: itemCode,
                    quantity: quantity,
                    status: statusBadge
                });
            }
        });
        
        console.log('Extracted batches:', batches);
        return batches;
    }



    renderBatchSelection() {
        const grid = document.getElementById('batchSelectionGrid');
        const noBatchesMessage = document.getElementById('noBatchesMessage');
        
        if (this.allBatches.length === 0) {
            grid.innerHTML = '';
            noBatchesMessage.classList.remove('hidden');
            return;
        }
        
        noBatchesMessage.classList.add('hidden');
        
        grid.innerHTML = this.allBatches.map(batch => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-chocolate transition cursor-pointer batch-card" 
                 data-batch-id="${batch.id}">
                <div class="flex items-center justify-between mb-2">
                    <input type="checkbox" class="batch-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" 
                           data-batch-id="${batch.id}">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        ${batch.status.toLowerCase() === 'active' ? 'bg-green-100 text-green-800' : ''}
                        ${batch.status.toLowerCase() === 'quarantine' ? 'bg-red-100 text-red-800' : ''}
                        ${batch.status.toLowerCase() === 'expired' ? 'bg-gray-100 text-gray-800' : ''}">
                        ${batch.status}
                    </span>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">${batch.batch_number}</h4>
                <p class="text-sm text-gray-600 mt-1">${batch.item_name}</p>
                <p class="text-xs text-gray-500">${batch.item_code}</p>
                <p class="text-sm font-medium text-gray-900 mt-2">${batch.quantity}</p>
            </div>
        `).join('');
        
        // Bind checkbox events
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const batchId = e.target.dataset.batchId;
                if (e.target.checked) {
                    this.selectedBatches.add(batchId);
                } else {
                    this.selectedBatches.delete(batchId);
                }
                this.updatePrintButtonState();
            });
        });
        
        // Bind card click events
        document.querySelectorAll('.batch-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.classList.contains('batch-checkbox')) {
                    const checkbox = card.querySelector('.batch-checkbox');
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    selectAllBatches() {
        const checkboxes = document.querySelectorAll('.batch-checkbox');
        const shouldSelect = this.selectedBatches.size !== checkboxes.length;
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = shouldSelect;
            const batchId = checkbox.dataset.batchId;
            if (shouldSelect) {
                this.selectedBatches.add(batchId);
            } else {
                this.selectedBatches.delete(batchId);
            }
        });
        
        this.updatePrintButtonState();
    }

    updatePrintButtonState() {
        const printBtn = document.getElementById('printLabelsBtn');
        const count = this.selectedBatches.size;
        
        if (count > 0) {
            printBtn.disabled = false;
            printBtn.innerHTML = `<i class="fas fa-print mr-2"></i> Print ${count} Label${count > 1 ? 's' : ''}`;
        } else {
            printBtn.disabled = true;
            printBtn.innerHTML = '<i class="fas fa-print mr-2"></i> Print Selected';
        }
    }

    showPrintPreview() {
        if (this.selectedBatches.size === 0) {
            this.showNotification('Please select at least one batch to print labels', 'error');
            return;
        }
        
        const preview = document.getElementById('printPreview');
        const labelPreview = document.getElementById('labelPreview');
        
        // Generate label previews
        const selectedBatchData = this.allBatches.filter(batch => 
            this.selectedBatches.has(batch.id)
        );
        
        labelPreview.innerHTML = selectedBatchData.map(batch => this.generateLabelPreview(batch)).join('');
        
        preview.classList.remove('hidden');
    }

    generateLabelPreview(batch) {
        return `
            <div class="border border-gray-300 p-3 text-center bg-white" style="width: 3in; height: 2in;">
                <div class="text-xs font-bold mb-1">${batch.item_name}</div>
                <div class="text-xs mb-1">Batch: ${batch.batch_number}</div>
                <div class="text-xs mb-1">SKU: ${batch.item_code}</div>
                <div class="text-xs mb-1">Qty: ${batch.quantity}</div>
                <div class="text-xs text-gray-600">${new Date().toLocaleDateString()}</div>
            </div>
        `;
    }

    hidePrintPreview() {
        document.getElementById('printPreview').classList.add('hidden');
    }

    async printLabels() {
        try {
            const selectedBatchData = this.allBatches.filter(batch => 
                this.selectedBatches.has(batch.id)
            );

            const response = await fetch('/inventory/inbound/labels/print', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    batch_selections: selectedBatchData.map(batch => ({
                        batch_id: batch.id,
                        quantity: 1 // Default to 1 label per batch
                    }))
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(`${result.printed_count} label jobs created successfully`, 'success');
                this.hidePrintPreview();
                
                // Open print dialog
                setTimeout(() => {
                    window.print();
                }, 500);
                
            } else {
                this.showNotification(result.message || 'Error creating print jobs', 'error');
            }
        } catch (error) {
            console.error('Print error:', error);
            this.showNotification('Error printing labels', 'error');
        }
    }

    setupBatchSelectModal(batchId) {
        document.getElementById('loadBatchBtn').addEventListener('click', () => {
            this.selectSpecificBatch(batchId);
            this.hideBatchSelectModal();
        });
    }

    selectSpecificBatch(batchId) {
        this.selectedBatches.add(batchId);
        const checkbox = document.querySelector(`[data-batch-id="${batchId}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
        this.updatePrintButtonState();
    }

    loadBatchFromModal() {
        // This will be handled by the modal setup
    }

    showBatchSelectModal() {
        document.getElementById('batchSelectModal').classList.remove('hidden');
    }

    hideBatchSelectModal() {
        document.getElementById('batchSelectModal').classList.add('hidden');
        // Clear URL parameter
        const url = new URL(window.location);
        url.searchParams.delete('batch');
        window.history.replaceState({}, '', url);
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
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    
    .printable, .printable * {
        visibility: visible;
    }
    
    .printable {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

/* Label styling for printing */
.batch-label {
    width: 3in;
    height: 2in;
    border: 1px solid #000;
    padding: 0.1in;
    margin: 0.05in;
    display: inline-block;
    text-align: center;
    font-family: Arial, sans-serif;
    font-size: 10px;
    line-height: 1.2;
}
</style>
@endpush