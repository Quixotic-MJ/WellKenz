<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1" role="dialog" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-600 text-white">
                <h5 class="modal-title" id="deleteItemModalLabel">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Delete Item
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-gray-900">
                            Are you sure you want to delete this item?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="delete-item-details">
                                You are about to delete item <span class="font-mono" id="delete-item-code">-</span> - <span id="delete-item-name">-</span>
                            </p>
                            <p class="text-sm text-red-600 mt-2 font-medium">
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteItem()">
                    <span class="spinner-border spinner-border-sm d-none" id="deleteItemSpinner"></span>
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDeleteItem = null;

    function showDeleteItemModal(item) {
        currentDeleteItem = item;
        
        // Populate delete confirmation details
        document.getElementById('delete-item-code').textContent = item.item_code;
        document.getElementById('delete-item-name').textContent = item.item_name;
        
        $('#deleteItemModal').modal('show');
    }

    function confirmDeleteItem() {
        if (!currentDeleteItem) {
            showNotification('No item selected for deletion', 'error');
            return;
        }
        
        const spinner = document.getElementById('deleteItemSpinner');
        const button = document.querySelector('#deleteItemModal .btn-danger');
        
        // Show loading state
        spinner.classList.remove('d-none');
        button.disabled = true;
        
        fetch(`{{ url('inventory/items') }}/${currentDeleteItem.item_id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Item deleted successfully!', 'success');
                $('#deleteItemModal').modal('hide');
                loadItems(); // Refresh the items list
            } else {
                showNotification(result.message || 'Error deleting item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting item. Please try again.', 'error');
        })
        .finally(() => {
            // Hide loading state
            spinner.classList.add('d-none');
            button.disabled = false;
        });
    }

    // Reset when modal is closed
    $('#deleteItemModal').on('hidden.bs.modal', function () {
        currentDeleteItem = null;
    });
</script>