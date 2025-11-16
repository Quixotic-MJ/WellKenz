<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-indigo-600 text-white">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editItemForm" onsubmit="handleEditItem(event)">
                <div class="modal-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Item Code (Read-only) -->
                        <div>
                            <label for="edit_item_code" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Code
                            </label>
                            <input type="text" id="edit_item_code" name="item_code" readonly
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            <div class="text-xs text-gray-500 mt-1">Item code cannot be changed</div>
                        </div>

                        <!-- Item Name -->
                        <div>
                            <label for="edit_item_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Item Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="edit_item_name" name="item_name" required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="edit_cat_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="edit_cat_id" name="cat_id" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                            </select>
                        </div>

                        <!-- Unit -->
                        <div>
                            <label for="edit_item_unit" class="block text-sm font-medium text-gray-700 mb-1">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <select id="edit_item_unit" name="item_unit" required
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
                            <label for="edit_item_description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea id="edit_item_description" name="item_description" rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Enter item description (optional)"></textarea>
                        </div>

                        <!-- Current Stock (Read-only) -->
                        <div>
                            <label for="edit_current_stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Current Stock
                            </label>
                            <input type="number" id="edit_current_stock" name="current_stock" readonly
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            <div class="text-xs text-gray-500 mt-1">Current stock level</div>
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="edit_item_expire_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Expiry Date
                            </label>
                            <input type="date" id="edit_item_expire_date" name="item_expire_date"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Leave blank if item doesn't expire</div>
                        </div>

                        <!-- Reorder Level -->
                        <div>
                            <label for="edit_reorder_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Reorder Level <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="edit_reorder_level" name="reorder_level" required min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Alert when stock reaches this level</div>
                        </div>

                        <!-- Min Stock Level -->
                        <div>
                            <label for="edit_min_stock_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Minimum Stock Level <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="edit_min_stock_level" name="min_stock_level" required min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Critical alert level</div>
                        </div>

                        <!-- Max Stock Level -->
                        <div>
                            <label for="edit_max_stock_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Maximum Stock Level
                            </label>
                            <input type="number" id="edit_max_stock_level" name="max_stock_level" min="0" step="0.01"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="text-xs text-gray-500 mt-1">Maximum recommended stock level</div>
                        </div>

                        <!-- Is Custom -->
                        <div class="flex items-center">
                            <input type="checkbox" id="edit_is_custom" name="is_custom" value="1"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edit_is_custom" class="ml-2 block text-sm text-gray-900">
                                Custom Item
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="editItemSpinner"></span>
                        Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentEditItem = null;

    function populateEditModal(item) {
        currentEditItem = item;
        
        // Populate form fields
        document.getElementById('edit_item_code').value = item.item_code;
        document.getElementById('edit_item_name').value = item.item_name;
        document.getElementById('edit_cat_id').value = item.cat_id;
        document.getElementById('edit_item_unit').value = item.item_unit;
        document.getElementById('edit_item_description').value = item.item_description || '';
        document.getElementById('edit_current_stock').value = item.item_stock;
        document.getElementById('edit_item_expire_date').value = item.item_expire_date || '';
        document.getElementById('edit_reorder_level').value = item.reorder_level;
        document.getElementById('edit_min_stock_level').value = item.min_stock_level;
        document.getElementById('edit_max_stock_level').value = item.max_stock_level || '';
        document.getElementById('edit_is_custom').checked = item.is_custom;
    }

    function editItem(itemId) {
        // Load categories first
        loadCategoriesForEdit();
        
        // Fetch item details and show edit modal
        fetch(`{{ url('inventory/items') }}/${itemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditModal(data.item);
                    $('#editItemModal').modal('show');
                } else {
                    showNotification('Error loading item details', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error loading item details', 'error');
            });
    }

    function loadCategoriesForEdit() {
        fetch('{{ route("inventory.items.list.data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categorySelect = document.getElementById('edit_cat_id');
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    const categories = [...new Set(data.items.map(item => ({ 
                        id: item.cat_id, 
                        name: item.cat_name 
                    })).filter(c => c.id && c.name))];
                    
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        categorySelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading categories for edit:', error));
    }

    function handleEditItem(event) {
        event.preventDefault();
        
        if (!currentEditItem) {
            showNotification('No item selected for editing', 'error');
            return;
        }
        
        const form = event.target;
        const formData = new FormData(form);
        const spinner = document.getElementById('editItemSpinner');
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
        
        fetch(`{{ url('inventory/items') }}/${currentEditItem.item_id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Item updated successfully!', 'success');
                $('#editItemModal').modal('hide');
                loadItems(); // Refresh the items list
            } else {
                showNotification(result.message || 'Error updating item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error updating item. Please try again.', 'error');
        })
        .finally(() => {
            // Hide loading state
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        });
    }
    
    // Reset form when modal is closed
    $('#editItemModal').on('hidden.bs.modal', function () {
        currentEditItem = null;
        document.getElementById('editItemForm').reset();
    });
</script>
