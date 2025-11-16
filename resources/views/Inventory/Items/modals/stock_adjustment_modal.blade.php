<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" role="dialog" aria-labelledby="stockAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-green-600 text-white">
                <h5 class="modal-title" id="stockAdjustmentModalLabel">Stock Adjustment</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="stockAdjustmentForm" onsubmit="handleStockAdjustment(event)">
                <div class="modal-body">
                    <!-- Item Information -->
                    <div id="adjustment-item-info" class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Item Code:</span>
                                <p class="text-sm text-gray-900 font-mono" id="adj-item-code">-</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Item Name:</span>
                                <p class="text-sm text-gray-900" id="adj-item-name">-</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Current Stock:</span>
                                <p class="text-sm text-gray-900 font-bold" id="adj-current-stock">-</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Unit:</span>
                                <p class="text-sm text-gray-900" id="adj-item-unit">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Adjustment Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Transaction Type -->
                        <div>
                            <label for="adj_trans_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Transaction Type <span class="text-red-500">*</span>
                            </label>
                            <select id="adj_trans_type" name="trans_type" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Type</option>
                                <option value="in">Stock-In (Add)</option>
                                <option value="out">Stock-Out (Remove)</option>
                                <option value="adjustment">Adjustment (Correction)</option>
                            </select>
                            <div class="text-xs text-gray-500 mt-1">Choose the type of stock adjustment</div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="adj_trans_quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="adj_trans_quantity" name="trans_quantity" required min="0.01" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Amount to adjust</div>
                        </div>

                        <!-- Stock After Preview -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Stock After Adjustment
                            </label>
                            <div class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 font-bold"
                                 id="stock-after-preview">
                                Current stock will be shown here
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Preview of new stock level</div>
                        </div>

                        <!-- Reorder Level Warning -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Reorder Status
                            </label>
                            <div class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700"
                                 id="reorder-status">
                                Current status will be shown here
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Current stock vs reorder level</div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="mt-6">
                        <label for="adj_trans_remarks" class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks <span class="text-red-500">*</span>
                        </label>
                        <textarea id="adj_trans_remarks" name="trans_remarks" required rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter reason for this stock adjustment (e.g., 'Damaged goods removed', 'Found extra inventory', etc.)"></textarea>
                        <div class="text-xs text-gray-500 mt-1">Required: Explain why this adjustment is being made</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm d-none" id="adjustmentSpinner"></span>
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Process Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentAdjustmentItem = null;

    function populateStockAdjustmentModal(item) {
        currentAdjustmentItem = item;
        
        // Populate item information
        document.getElementById('adj-item-code').textContent = item.item_code;
        document.getElementById('adj-item-name').textContent = item.item_name;
        document.getElementById('adj-current-stock').textContent = formatNumber(item.item_stock);
        document.getElementById('adj-item-unit').textContent = item.item_unit;
        
        // Reset form
        document.getElementById('stockAdjustmentForm').reset();
        updateAdjustmentPreview();
        
        // Update reorder status
        updateReorderStatus();
    }

    function adjustStock(itemId) {
        // Fetch item details and show adjustment modal
        fetch(`{{ url('inventory/items') }}/${itemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateStockAdjustmentModal(data.item);
                    $('#stockAdjustmentModal').modal('show');
                } else {
                    showNotification('Error loading item details', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading item details', 'error');
            });
    }

    function updateAdjustmentPreview() {
        if (!currentAdjustmentItem) return;
        
        const transType = document.getElementById('adj_trans_type').value;
        const quantity = parseFloat(document.getElementById('adj_trans_quantity').value) || 0;
        const currentStock = parseFloat(currentAdjustmentItem.item_stock) || 0;
        
        let newStock = currentStock;
        let previewText = '';
        
        if (transType && quantity > 0) {
            if (transType === 'in') {
                newStock = currentStock + quantity;
                previewText = `+${formatNumber(quantity)} = ${formatNumber(newStock)} ${currentAdjustmentItem.item_unit}`;
            } else if (transType === 'out') {
                newStock = Math.max(0, currentStock - quantity);
                previewText = `-${formatNumber(quantity)} = ${formatNumber(newStock)} ${currentAdjustmentItem.item_unit}`;
            } else if (transType === 'adjustment') {
                previewText = `Adjusted to ${formatNumber(quantity)} ${currentAdjustmentItem.item_unit}`;
                newStock = quantity;
            }
        }
        
        document.getElementById('stock-after-preview').textContent = previewText || 'Current stock will be shown here';
        
        // Change color based on new stock level
        const previewElement = document.getElementById('stock-after-preview');
        if (newStock <= currentAdjustmentItem.min_stock_level) {
            previewElement.className = 'block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm bg-red-50 text-red-700 font-bold';
        } else if (newStock <= currentAdjustmentItem.reorder_level) {
            previewElement.className = 'block w-full px-3 py-2 border border-yellow-300 rounded-md shadow-sm bg-yellow-50 text-yellow-700 font-bold';
        } else {
            previewElement.className = 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 font-bold';
        }
    }

    function updateReorderStatus() {
        if (!currentAdjustmentItem) return;
        
        const currentStock = parseFloat(currentAdjustmentItem.item_stock) || 0;
        const reorderLevel = parseFloat(currentAdjustmentItem.reorder_level) || 0;
        const minStockLevel = parseFloat(currentAdjustmentItem.min_stock_level) || 0;
        
        const statusElement = document.getElementById('reorder-status');
        
        if (currentStock <= minStockLevel) {
            statusElement.textContent = 'CRITICAL - Below minimum stock level';
            statusElement.className = 'block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm bg-red-50 text-red-700';
        } else if (currentStock <= reorderLevel) {
            statusElement.textContent = 'LOW - Below reorder level';
            statusElement.className = 'block w-full px-3 py-2 border border-yellow-300 rounded-md shadow-sm bg-yellow-50 text-yellow-700';
        } else {
            statusElement.textContent = 'NORMAL - Above reorder level';
            statusElement.className = 'block w-full px-3 py-2 border border-green-300 rounded-md shadow-sm bg-green-50 text-green-700';
        }
    }

    function handleStockAdjustment(event) {
        event.preventDefault();
        
        if (!currentAdjustmentItem) {
            showNotification('No item selected for adjustment', 'error');
            return;
        }
        
        const form = event.target;
        const formData = new FormData(form);
        const spinner = document.getElementById('adjustmentSpinner');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Validate OUT transactions don't cause negative stock
        const transType = formData.get('trans_type');
        const quantity = parseFloat(formData.get('trans_quantity'));
        const currentStock = parseFloat(currentAdjustmentItem.item_stock);
        
        if (transType === 'out' && quantity > currentStock) {
            showNotification('Cannot issue more than current stock available', 'error');
            return;
        }
        
        // Show loading state
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
        
        // Convert form data to JSON
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        data.item_id = currentAdjustmentItem.item_id;
        
        fetch('{{ route("inventory.adjustments.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Stock adjustment completed successfully!', 'success');
                $('#stockAdjustmentModal').modal('hide');
                loadItems(); // Refresh the items list
            } else {
                showNotification(result.message || 'Error adjusting stock', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adjusting stock. Please try again.', 'error');
        })
        .finally(() => {
            // Hide loading state
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        });
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // Event listeners for live preview
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('input', function(e) {
            if (e.target.id === 'adj_trans_type' || e.target.id === 'adj_trans_quantity') {
                updateAdjustmentPreview();
            }
        });
    });

    // Reset form when modal is closed
    $('#stockAdjustmentModal').on('hidden.bs.modal', function () {
        currentAdjustmentItem = null;
        document.getElementById('stockAdjustmentForm').reset();
        document.getElementById('stock-after-preview').textContent = 'Current stock will be shown here';
        document.getElementById('stock-after-preview').className = 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 font-bold';
    });
</script>
