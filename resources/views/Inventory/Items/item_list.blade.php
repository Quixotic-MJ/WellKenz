@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Item List</h1>
            <p class="text-gray-600 mt-1">View, search, and manage all inventory items</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="showAddItemModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Add New Item
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" id="search-input" placeholder="Search items..." 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                <!-- Categories will be loaded dynamically -->
            </select>
            <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button onclick="exportItems()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Export
            </button>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Items</h3>
                <div class="flex space-x-2">
                    <span class="text-sm text-gray-500" id="items-count">0 items</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="items-table-body">
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading items...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="pagination" class="px-6 py-3 border-t border-gray-200">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Modals -->
@include('Inventory.Items.modals.add_item_modal')
@include('Inventory.Items.modals.edit_item_modal')
@include('Inventory.Items.modals.stock_adjustment_modal')
@include('Inventory.Items.modals.delete_item_modal')

@push('scripts')
<script>
    let currentPage = 1;
    let currentFilters = {
        search: '',
        category: '',
        status: ''
    };

    // Load items when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadItems();
        setupEventListeners();
    });

    function setupEventListeners() {
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const statusFilter = document.getElementById('status-filter');

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentFilters.search = this.value;
                currentPage = 1;
                loadItems();
            }, 500);
        });

        categoryFilter.addEventListener('change', function() {
            currentFilters.category = this.value;
            currentPage = 1;
            loadItems();
        });

        statusFilter.addEventListener('change', function() {
            currentFilters.status = this.value;
            currentPage = 1;
            loadItems();
        });
    }

    function loadItems() {
        const params = new URLSearchParams({
            page: currentPage,
            search: currentFilters.search,
            category: currentFilters.category,
            status: currentFilters.status
        });

        fetch(`{{ route('inventory.items.list.data') }}?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayItems(data.items);
                    updatePagination(data.pagination);
                    updateCount(data.pagination.total);
                    populateCategories(data.items);
                }
            })
            .catch(error => {
                console.error('Error loading items:', error);
                document.getElementById('items-table-body').innerHTML = 
                    '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Error loading items</td></tr>';
            });
    }

    function displayItems(items) {
        const tbody = document.getElementById('items-table-body');
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No items found</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => {
            const stockStatus = getStockStatus(item);
            const statusClass = item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            const expiryClass = getExpiryClass(item.item_expire_date);
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.item_code}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${item.item_name}</div>
                        <div class="text-sm text-gray-500">${item.item_description || '-'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.cat_name || 'Uncategorized'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium ${stockStatus.class}">${formatNumber(item.item_stock)} ${item.item_unit}</div>
                        <div class="text-xs text-gray-500">${stockStatus.label}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatNumber(item.reorder_level)} ${item.item_unit}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm ${expiryClass.class}">
                        ${item.item_expire_date ? new Date(item.item_expire_date).toLocaleDateString() : 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                            ${item.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 space-x-2">
                        <button onclick="editItem(${item.item_id})" class="hover:text-blue-800">Edit</button>
                        <button onclick="adjustStock(${item.item_id})" class="hover:text-blue-800">Adjust</button>
                        <button onclick="deleteItem(${item.item_id})" class="hover:text-red-600">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function populateCategories(items) {
        const categorySelect = document.getElementById('category-filter');
        // Clear existing options except "All Categories"
        categorySelect.innerHTML = '<option value="">All Categories</option>';
        
        const categories = [...new Set(items.map(item => item.cat_name).filter(Boolean))];
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categorySelect.appendChild(option);
        });
    }

    function updatePagination(pagination) {
        const paginationDiv = document.getElementById('pagination');
        if (pagination.last_page <= 1) {
            paginationDiv.innerHTML = '';
            return;
        }

        let paginationHTML = '<div class="flex justify-between items-center">';
        paginationHTML += `<div class="text-sm text-gray-700">Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results</div>`;
        paginationHTML += '<div class="flex space-x-1">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHTML += `<button onclick="changePage(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === pagination.current_page ? 'bg-blue-600 text-white' : 'border-gray-300 text-gray-700 hover:bg-gray-50';
            paginationHTML += `<button onclick="changePage(${i})" class="px-3 py-1 border border-gray-300 rounded-md text-sm ${activeClass}">${i}</button>`;
        }
        
        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHTML += `<button onclick="changePage(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded-md text-sm hover:bg-gray-50">Next</button>`;
        }
        
        paginationHTML += '</div></div>';
        paginationDiv.innerHTML = paginationHTML;
    }

    function changePage(page) {
        currentPage = page;
        loadItems();
    }

    function getStockStatus(item) {
        if (item.item_stock <= item.min_stock_level) {
            return { class: 'text-red-600', label: 'Critical' };
        } else if (item.item_stock <= item.reorder_level) {
            return { class: 'text-yellow-600', label: 'Low Stock' };
        } else {
            return { class: 'text-green-600', label: 'Normal' };
        }
    }

    function getExpiryClass(expiryDate) {
        if (!expiryDate) {
            return { class: 'text-gray-500' };
        }
        
        const expiry = new Date(expiryDate);
        const today = new Date();
        const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
        
        if (diffDays <= 30) {
            return { class: 'text-red-600' };
        } else if (diffDays <= 90) {
            return { class: 'text-yellow-600' };
        } else {
            return { class: 'text-gray-500' };
        }
    }

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function updateCount(count) {
        document.getElementById('items-count').textContent = `${count} items`;
    }

    // Modal functions
    function showAddItemModal() {
        // Load categories for the modal
        fetch('{{ route("inventory.items.list.data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categories = [...new Set(data.items.map(item => ({ 
                        id: item.cat_id, 
                        name: item.cat_name 
                    })).filter(c => c.id && c.name))];
                    
                    const categorySelect = document.querySelector('#addItemModal select[name="cat_id"]');
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.name;
                        categorySelect.appendChild(option);
                    });
                }
            });
        
        $('#addItemModal').modal('show');
    }

    function getItemShowUrl(itemId) {
        return `{{ url('inventory/items') }}/${itemId}`;
    }

    function getItemDestroyUrl(itemId) {
        return `{{ url('inventory/items') }}/${itemId}`;
    }

    function editItem(itemId) {
        // Fetch item details and show edit modal
        fetch(getItemShowUrl(itemId))
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

    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch(getItemDestroyUrl(itemId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('Item deleted successfully', 'success');
                    loadItems();
                } else {
                    showNotification(result.message || 'Error deleting item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error deleting item', 'error');
            });
        }
    }

    function exportItems() {
        const params = new URLSearchParams(currentFilters);
        window.open(`{{ route('inventory.items.list.data') }}?${params}&export=1`, '_blank');
    }

    function showNotification(message, type) {
        // Simple notification - you can replace with a more sophisticated notification system
        const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${alertClass} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
@endsection