<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue-600 text-white">
                <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addItemForm" onsubmit="handleAddItem(event)">
                <div class="modal-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Item Code -->
                        <div>
                            <label for="item_code" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="item_code" name="item_code" required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., ITM001">
                            <div class="text-xs text-gray-500 mt-1">Must be unique</div>
                        </div>

                        <!-- Item Name -->
                        <div>
                            <label for="item_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="item_name" name="item_name" required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., Office Paper A4">
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="cat_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="cat_id" name="cat_id" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                <option value="{{ $category->cat_id }}">{{ $category->cat_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Unit -->
                        <div>
                            <label for="item_unit" class="block text-sm font-medium text-gray-700 mb-1">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <select id="item_unit" name="item_unit" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Unit</option>
                                <option value="piece">Piece</option>
                                <option value="box">Box</option>
                                <option value="pack">Pack</option>
                                <option value="kilogram">Kilogram</option>
                                <option value="liter">Liter</option>
                                <option value="meter">Meter</option>
                                <option value="ream">Ream</option>
                                <option value="bottle">Bottle</option>
                                <option value="roll">Roll</option>
                                <option value="set">Set</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="item_description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea id="item_description" name="item_description" rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Enter item description (optional)"></textarea>
                        </div>

                        <!-- Initial Stock -->
                        <div>
                            <label for="item_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Initial Stock <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="item_stock" name="item_stock" required min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   value="0">
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="item_expire_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Expiry Date
                            </label>
                            <input type="date" id="item_expire_date" name="item_expire_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Leave blank if item doesn't expire</div>
                        </div>

                        <!-- Reorder Level -->
                        <div>
                            <label for="reorder_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Reorder Level <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="reorder_level" name="reorder_level" required min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., 50">
                            <div class="text-xs text-gray-500 mt-1">Alert when stock reaches this level</div>
                        </div>

                        <!-- Min Stock Level -->
                        <div>
                            <label for="min_stock_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Minimum Stock Level <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="min_stock_level" name="min_stock_level" required min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., 20">
                            <div class="text-xs text-gray-500 mt-1">Critical alert level</div>
                        </div>

                        <!-- Max Stock Level -->
                        <div>
                            <label for="max_stock_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Maximum Stock Level
                            </label>
                            <input type="number" id="max_stock_level" name="max_stock_level" min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Optional">
                            <div class="text-xs text-gray-500 mt-1">Maximum recommended stock level</div>
                        </div>

                        <!-- Is Custom -->
                        <div class="flex items-center">
                            <input type="checkbox" id="is_custom" name="is_custom" value="1"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_custom" class="ml-2 block text-sm text-gray-900">
                                Custom Item
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="addItemSpinner"></span>
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function handleAddItem(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const spinner = document.getElementById('addItemSpinner');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Show loading state
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
        
        // Convert form data to JSON
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        // Convert checkbox
        data.is_custom = formData.get('is_custom') ? 1 : 0;
        
        fetch('{{ route("inventory.items.store") }}', {
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
                showNotification('Item added successfully!', 'success');
                $('#addItemModal').modal('hide');
                form.reset();
                loadItems(); // Refresh the items list
            } else {
                showNotification(result.message || 'Error adding item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error adding item. Please try again.', 'error');
        })
        .finally(() => {
            // Hide loading state
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        });
    }
    
    // Reset form when modal is closed
    $('#addItemModal').on('hidden.bs.modal', function () {
        document.getElementById('addItemForm').reset();
    });
</script>