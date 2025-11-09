@extends('Employee.layout.app')

@section('title', 'Create Requisition - WellKenz ERP')

@section('breadcrumb', 'Create Requisition')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create New Requisition</h1>
            <p class="text-gray-600">Request items from inventory for your needs</p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Create Requisition Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Requisition Details Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Requisition Details</h3>

                    <form id="requisitionForm" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Requisition Reference</label>
                                <input type="text" name="req_ref" id="req_ref" readonly
                                    class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                                <select name="req_priority" required
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Requested By</label>
                                <input type="text" id="requested_by" readonly
                                    class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="text" id="current_date" readonly
                                    class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 text-gray-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Remarks</label>
                            <textarea name="req_purpose" rows="3" required placeholder="Enter the purpose of this requisition"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"></textarea>
                        </div>
                    </form>
                </div>

                <!-- Requisition Items Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Requisition Items</h3>
                        <button type="button" onclick="openItemModal()"
                            class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 text-sm font-medium rounded">
                            <i class="fas fa-plus mr-2"></i>
                            Add Item
                        </button>
                    </div>

                    <!-- Selected Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full" id="selectedItemsTable">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="selectedItemsBody">
                                <tr id="noItemsRow">
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        <i class="fas fa-box-open text-2xl mb-2 opacity-50"></i>
                                        <p>No items added yet. Click "Add Item" to get started.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4">
                        <button type="button" id="submitRequisitionBtn" onclick="submitRequisition()"
                            class="px-6 py-2 bg-gray-800 text-white hover:bg-gray-700 text-sm font-medium rounded">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit Requisition
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Requisition Summary</h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Total Items</span>
                            <span id="totalItemsCount" class="text-lg font-semibold text-gray-800">0</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Priority</span>
                            <span id="selectedPriority" class="text-sm font-medium text-gray-600">Not set</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <span class="text-sm font-medium text-gray-700">Requester</span>
                            <span id="requesterName"
                                class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Guidelines</h3>

                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-info text-blue-500 mt-1"></i>
                            <p class="text-sm text-gray-600">Provide clear purpose for better approval</p>
                        </div>

                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-500 mt-1"></i>
                            <p class="text-sm text-gray-600">High priority requests are reviewed first</p>
                        </div>

                        <div class="flex items-start gap-2">
                            <i class="fas fa-box text-gray-500 mt-1"></i>
                            <p class="text-sm text-gray-600">Check stock availability before requesting</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Select Items from Inventory</h3>
                    <button onclick="closeItemModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-4">
                <!-- Search Bar -->
                <div class="mb-4">
                    <div class="relative">
                        <input type="text" id="itemSearch" placeholder="Search items..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            onkeyup="searchItems(this.value)">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Inventory Items Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" id="inventoryItemsGrid">
                    <div class="col-span-2 text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl mb-3 opacity-50"></i>
                        <p class="text-gray-500">Loading items...</p>
                    </div>
                </div>

                <!-- Selected Item Details -->
                <div id="selectedItemDetails" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Add Item to Requisition</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                            <input type="text" id="selectedItemName" readonly
                                class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Available Stock</label>
                            <input type="text" id="selectedItemStock" readonly
                                class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Requested</label>
                            <input type="number" id="itemQuantity" min="1" value="1"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                onchange="validateQuantity(this)">
                            <p id="quantityWarning" class="text-xs text-amber-600 mt-1 hidden">
                                Requested quantity exceeds available stock
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <input type="text" id="selectedItemUnit" readonly
                                class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2 text-gray-700">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" onclick="closeItemDetails()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                            Cancel
                        </button>
                        <button type="button" onclick="addItemToRequisition()" id="addToRequisitionBtn"
                            class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded">
                            Add to Requisition
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedItems = [];
        let currentSelectedItem = null;
        let inventoryItems = [];

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
            setupEventListeners();
            loadInventoryItems();
        });

        function initializePage() {
            // Set current date
            const now = new Date();
            const formattedDate = now.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('current_date').value = formattedDate;

            // Set requested by
            document.getElementById('requested_by').value = '{{ Auth::user()->name }}';
            document.getElementById('requesterName').textContent = '{{ Auth::user()->name }}';

            // Generate requisition reference
            generateRequisitionReference();

            updateSummary();
        }

        function generateRequisitionReference() {
            // Generate a reference like REQ-2023-0001
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');

            // In a real application, you might want to get the next sequence from the database
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(4, '0');
            const reference = `REQ-${year}${month}${day}-${randomNum}`;

            document.getElementById('req_ref').value = reference;
        }

        function setupEventListeners() {
            // Priority change listener
            const prioritySelect = document.querySelector('select[name="req_priority"]');
            if (prioritySelect) {
                prioritySelect.addEventListener('change', function() {
                    const displayText = this.value ?
                        this.value.charAt(0).toUpperCase() + this.value.slice(1) :
                        'Not set';
                    document.getElementById('selectedPriority').textContent = displayText;
                });
            }
        }

        // Item Modal Functions
        function openItemModal() {
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeItemModal() {
            document.getElementById('itemModal').classList.add('hidden');
            closeItemDetails();
        }

        function searchItems(query) {
            const items = document.querySelectorAll('.inventory-item');
            const searchTerm = query.toLowerCase();

            items.forEach(item => {
                const name = item.getAttribute('data-item-name').toLowerCase();
                const code = item.getAttribute('data-item-code').toLowerCase();
                const category = item.getAttribute('data-category').toLowerCase();

                if (name.includes(searchTerm) || code.includes(searchTerm) || category.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function handleSelectItem(button) {
            const itemElement = button.closest('.inventory-item');

            currentSelectedItem = {
                id: parseInt(itemElement.getAttribute('data-item-id')),
                name: itemElement.getAttribute('data-item-name'),
                code: itemElement.getAttribute('data-item-code'),
                stock: parseFloat(itemElement.getAttribute('data-stock')),
                unit: itemElement.getAttribute('data-unit'),
                category: itemElement.getAttribute('data-category')
            };

            document.getElementById('selectedItemName').value = currentSelectedItem.name;
            document.getElementById('selectedItemStock').value = `${currentSelectedItem.stock} ${currentSelectedItem.unit}`;
            document.getElementById('selectedItemUnit').value = currentSelectedItem.unit;
            document.getElementById('itemQuantity').value = 1;
            document.getElementById('quantityWarning').classList.add('hidden');

            document.getElementById('selectedItemDetails').classList.remove('hidden');

            // Scroll to the selected item details section
            setTimeout(() => {
                document.getElementById('selectedItemDetails').scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }, 100);
        }

        function closeItemDetails() {
            document.getElementById('selectedItemDetails').classList.add('hidden');
            currentSelectedItem = null;
        }

        function quickAddItem(button) {
            const itemElement = button.closest('.inventory-item');

            const item = {
                id: parseInt(itemElement.getAttribute('data-item-id')),
                name: itemElement.getAttribute('data-item-name'),
                code: itemElement.getAttribute('data-item-code'),
                stock: parseFloat(itemElement.getAttribute('data-stock')),
                unit: itemElement.getAttribute('data-unit'),
                category: itemElement.getAttribute('data-category')
            };

            // Check if item already exists
            const existingIndex = selectedItems.findIndex(selectedItem => parseInt(selectedItem.id) === item.id);

            if (existingIndex > -1) {
                // Update existing item quantity by 1
                selectedItems[existingIndex].quantity += 1;
                showMessage('Item quantity increased!', 'success');
            } else {
                // Add new item with quantity 1
                selectedItems.push({
                    id: item.id,
                    name: item.name,
                    code: item.code,
                    quantity: 1,
                    unit: item.unit,
                    stock: item.stock,
                    category: item.category
                });
                showMessage('Item added to requisition!', 'success');
            }

            updateItemsTable();
        }

        function validateQuantity(input) {
            const quantity = parseFloat(input.value);
            const warning = document.getElementById('quantityWarning');

            if (quantity > currentSelectedItem.stock) {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        }

        function addItemToRequisition() {
            if (!currentSelectedItem) {
                showMessage('Please select an item first', 'error');
                return;
            }

            const quantityInput = document.getElementById('itemQuantity');
            const quantity = parseFloat(quantityInput.value);

            if (isNaN(quantity) || quantity < 1) {
                showMessage('Please enter a valid quantity (at least 1)', 'error');
                return;
            }

            const itemId = parseInt(currentSelectedItem.id);

            // Check if item already exists
            const existingIndex = selectedItems.findIndex(item => parseInt(item.id) === itemId);

            if (existingIndex > -1) {
                // Update existing item
                selectedItems[existingIndex].quantity = quantity;
                showMessage('Item quantity updated successfully!', 'success');
            } else {
                // Add new item
                const newItem = {
                    id: itemId,
                    name: currentSelectedItem.name,
                    code: currentSelectedItem.code,
                    quantity: quantity,
                    unit: currentSelectedItem.unit,
                    stock: currentSelectedItem.stock,
                    category: currentSelectedItem.category
                };
                selectedItems.push(newItem);
                showMessage('Item added to requisition successfully!', 'success');
            }

            updateItemsTable();
            closeItemDetails();
            closeItemModal();
        }

        function updateItemsTable() {
            const tbody = document.getElementById('selectedItemsBody');
            const noItemsRow = document.getElementById('noItemsRow');

            if (selectedItems.length === 0) {
                // Show no items message
                if (noItemsRow) {
                    noItemsRow.style.display = '';
                }
                // Remove all rows except noItemsRow
                const rows = Array.from(tbody.children);
                rows.forEach(row => {
                    if (row.id !== 'noItemsRow') {
                        row.remove();
                    }
                });
                updateSummary();
                return;
            }

            // Hide no items row
            if (noItemsRow) {
                noItemsRow.style.display = 'none';
            }

            // Remove all existing item rows (but keep noItemsRow)
            const rows = Array.from(tbody.children);
            rows.forEach(row => {
                if (row.id !== 'noItemsRow') {
                    row.remove();
                }
            });

            // Add items
            selectedItems.forEach((item, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-2">
                        <p class="text-sm font-semibold text-gray-800">${escapeHtml(item.name)}</p>
                        <p class="text-xs text-gray-500">${escapeHtml(item.code)}</p>
                        <p class="text-xs text-gray-400">${escapeHtml(item.category)}</p>
                    </td>
                    <td class="px-4 py-2">
                        <p class="text-sm text-gray-800">${item.quantity}</p>
                    </td>
                    <td class="px-4 py-2">
                        <p class="text-sm text-gray-800">${escapeHtml(item.unit)}</p>
                    </td>
                    <td class="px-4 py-2">
                        <button type="button" onclick="removeItem(${index})" 
                            class="px-3 py-1 bg-red-600 text-white text-xs font-medium hover:bg-red-700 rounded">
                            Remove
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            updateSummary();
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function removeItem(index) {
            selectedItems.splice(index, 1);
            updateItemsTable();
            showMessage('Item removed from requisition', 'success');
        }

        function updateSummary() {
            const totalItemsElement = document.getElementById('totalItemsCount');
            if (totalItemsElement) {
                totalItemsElement.textContent = selectedItems.length;
            }
        }

        function loadInventoryItems() {
            const grid = document.getElementById('inventoryItemsGrid');

            fetch('{{ route('items.requisition') }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(items => {
                    inventoryItems = items;

                    if (!items || items.length === 0) {
                        grid.innerHTML = `
                    <div class="col-span-2 text-center py-8">
                        <i class="fas fa-box-open text-3xl mb-3 opacity-50"></i>
                        <p class="text-gray-500">No inventory items available.</p>
                    </div>
                `;
                        return;
                    }

                    grid.innerHTML = '';

                    items.forEach(item => {
                        const itemElement = document.createElement('div');
                        itemElement.className =
                            'border border-gray-200 rounded-lg p-4 hover:bg-gray-50 inventory-item cursor-pointer';
                        itemElement.setAttribute('data-item-id', item.item_id);
                        itemElement.setAttribute('data-item-name', item.item_name);
                        itemElement.setAttribute('data-item-code', item.item_code);
                        itemElement.setAttribute('data-stock', item.item_stock);
                        itemElement.setAttribute('data-unit', item.item_unit);
                        itemElement.setAttribute('data-category', item.cat_name || '');

                        itemElement.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold text-gray-800">${escapeHtml(item.item_name)}</h4>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${escapeHtml(item.item_code)}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${escapeHtml(item.cat_name || '')}</p>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-700">Stock: ${item.item_stock} ${escapeHtml(item.item_unit)}</span>
                        <div class="flex gap-2">
                            <button type="button" onclick="event.stopPropagation(); quickAddItem(this);" 
                                class="px-2 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700 rounded"
                                title="Quick Add (Qty: 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" onclick="event.stopPropagation(); handleSelectItem(this);" 
                                class="px-3 py-1 bg-gray-800 text-white text-xs font-medium hover:bg-gray-700 rounded">
                                Select
                            </button>
                        </div>
                    </div>
                `;

                        grid.appendChild(itemElement);
                    });
                })
                .catch(error => {
                    console.error('Error loading inventory items:', error);
                    grid.innerHTML = `
                <div class="col-span-2 text-center py-8">
                    <i class="fas fa-exclamation-triangle text-2xl mb-3 opacity-50"></i>
                    <p class="text-gray-500">Error loading inventory items. Please try again.</p>
                </div>
            `;
                });
        }

        function submitRequisition() {
            if (selectedItems.length === 0) {
                showMessage('Please add at least one item to the requisition', 'error');
                return;
            }

            const form = document.getElementById('requisitionForm');
            const formData = new FormData(form);

            // Validate priority
            const priority = formData.get('req_priority');
            if (!priority) {
                showMessage('Please select a priority level', 'error');
                return;
            }

            // Validate purpose
            const purpose = formData.get('req_purpose');
            if (!purpose || purpose.trim() === '') {
                showMessage('Please enter a purpose for this requisition', 'error');
                return;
            }

            if (purpose.trim().length < 10) {
                showMessage('Please provide a more detailed purpose (at least 10 characters)', 'error');
                return;
            }

            // Prepare data for submission
            const submissionData = {
                req_ref: document.getElementById('req_ref').value,
                req_priority: priority,
                req_purpose: purpose.trim(),
                items: selectedItems.map(item => ({
                    item_id: parseInt(item.id),
                    quantity: parseInt(item.quantity)
                }))
            };

            console.log('Submitting requisition:', submissionData);

            // Show confirmation
            if (confirm('Are you sure you want to submit this requisition?')) {
                const submitBtn = document.getElementById('submitRequisitionBtn');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';

                fetch('{{ route('requisitions.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(submissionData)
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (!response.ok) {
                            // Handle validation errors
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat().join(', ');
                                throw new Error(errorMessages);
                            }
                            throw new Error(data.message || 'Server error');
                        }

                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            showMessage('Requisition submitted successfully! Purchasing officers have been notified.',
                                'success');

                            // Reset form after submission
                            setTimeout(() => {
                                selectedItems = [];
                                updateItemsTable();
                                form.reset();
                                document.getElementById('selectedPriority').textContent = 'Not set';
                                generateRequisitionReference(); // Generate new reference for next requisition
                                updateSummary();
                            }, 3000);
                        } else {
                            throw new Error(data.message || 'Error submitting requisition');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage(error.message || 'An error occurred while submitting the requisition', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            }
        }

        // Utility Functions
        function showMessage(message, type) {
            const messageDiv = type === 'success' ?
                document.getElementById('successMessage') :
                document.getElementById('errorMessage');

            if (messageDiv) {
                messageDiv.textContent = message;
                messageDiv.classList.remove('hidden');

                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 5000);
            }
        }
    </script>
@endsection
