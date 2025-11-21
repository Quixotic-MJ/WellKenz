@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Category Management</h1>
            <p class="text-sm text-gray-500 mt-1">Organize your inventory items into logical groups for easier reporting and requisitions.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openCategoryModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Create Category
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" 
                   id="searchInput" 
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                   placeholder="Search categories..."
                   onkeyup="searchCategories()">
        </div>
        
        <!-- Status Filter -->
        <div class="w-full md:w-48">
             <select id="statusFilter" 
                     class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm"
                     onchange="filterCategories()">
                <option value="">All Status</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    {{-- 3. CATEGORIES TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Linked Items</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 transition-colors {{ !$category->is_active ? 'bg-gray-50 opacity-75' : '' }}" 
                        id="category-row-{{ $category->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 {{ $category->is_active ? 'bg-amber-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center {{ $category->is_active ? 'text-amber-700' : 'text-gray-500' }}">
                                    <i class="{{ getCategoryIcon($category->name) }} text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900 category-name">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-xs text-gray-500 truncate max-w-xs category-desc">{{ $category->description }}</div>
                                    @else
                                        <div class="text-xs text-gray-400 category-desc">No description</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                {{ $category->description ?? 'No description available' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->linked_items_count > 0 ? 'bg-gray-100 text-gray-800' : 'bg-gray-200 text-gray-600' }}">
                                {{ $category->linked_items_count }} {{ $category->linked_items_count == 1 ? 'Item' : 'Items' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="sr-only peer category-toggle" 
                                       data-category-id="{{ $category->id }}"
                                       {{ $category->is_active ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ml-3 text-sm font-medium {{ $category->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-blue-600 hover:text-blue-900 bg-blue-50 p-2 rounded hover:bg-blue-100 transition"
                                    onclick="editCategory({{ $category->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded hover:bg-red-100 transition ml-2"
                                    onclick="confirmDeleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                    {{ $category->linked_items_count > 0 || $category->children()->count() > 0 ? 'disabled' : '' }}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-4xl mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">No categories found</p>
                                <p class="text-sm text-gray-400">Get started by creating your first category</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($categories->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>

<!-- ==================== MODALS ==================== -->

<!-- 1. CREATE/EDIT CATEGORY MODAL -->
<div id="categoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCategoryModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create Category</h3>
                        <div class="mt-4 space-y-4">
                            
                            <!-- Name -->
                            <div>
                                <label for="categoryName" class="block text-sm font-medium text-gray-700">Category Name</label>
                                <input type="text" 
                                       id="categoryName" 
                                       name="name"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                                       placeholder="e.g., Decor & Toppers"
                                       required>
                                <div id="nameError" class="mt-1 text-sm text-red-600 hidden"></div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="categoryDescription" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="categoryDescription" 
                                          name="description"
                                          rows="3" 
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                                          placeholder="Briefly describe what items belong here..."></textarea>
                                <div id="descriptionError" class="mt-1 text-sm text-red-600 hidden"></div>
                            </div>

                            <!-- Parent Category (Optional) -->
                            <div>
                                <label for="parentCategory" class="block text-sm font-medium text-gray-700">Parent Category (Optional)</label>
                                <select id="parentCategory" 
                                        name="parent_id"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                    <option value="">No Parent Category</option>
                                    <!-- Options will be loaded dynamically -->
                                </select>
                                <div id="parentError" class="mt-1 text-sm text-red-600 hidden"></div>
                            </div>

                            <!-- Icon Selection (Visual Only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Visual Icon</label>
                                <div class="mt-2 flex gap-3 flex-wrap">
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-chocolate text-white flex items-center justify-center ring-2 ring-offset-2 ring-chocolate" data-icon="fas fa-tag"><i class="fas fa-tag"></i></button>
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center" data-icon="fas fa-utensils"><i class="fas fa-utensils"></i></button>
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center" data-icon="fas fa-box"><i class="fas fa-box"></i></button>
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center" data-icon="fas fa-tint"><i class="fas fa-tint"></i></button>
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center" data-icon="fas fa-wheat"><i class="fas fa-wheat"></i></button>
                                    <button type="button" class="icon-option w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center" data-icon="fas fa-snowflake"><i class="fas fa-snowflake"></i></button>
                                </div>
                                <input type="hidden" id="selectedIcon" name="icon" value="fas fa-tag">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        id="saveCategoryBtn"
                        onclick="saveCategory()" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Category
                </button>
                <button type="button" 
                        onclick="closeCategoryModal()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. CONFIRMATION MODAL -->
<div id="confirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="cancelConfirm()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="cancelConfirm()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 3. NOTIFICATION MODAL -->
<div id="notificationModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeNotification()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="notifIcon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <!-- Icon inserted via JS -->
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="notifTitle">Notification</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="notifMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeNotification()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-chocolate"></div>
        <span class="text-gray-700">Processing...</span>
    </div>
</div>

@endsection

@php
function getCategoryIcon($categoryName) {
    $name = strtolower($categoryName);
    if (strpos($name, 'flour') !== false || strpos($name, 'grain') !== false || strpos($name, 'wheat') !== false) {
        return 'fas fa-wheat';
    } elseif (strpos($name, 'dairy') !== false || strpos($name, 'milk') !== false || strpos($name, 'cold') !== false) {
        return 'fas fa-snowflake';
    } elseif (strpos($name, 'packaging') !== false || strpos($name, 'box') !== false || strpos($name, 'container') !== false) {
        return 'fas fa-box-open';
    } elseif (strpos($name, 'sweet') !== false || strpos($name, 'sugar') !== false || strpos($name, 'honey') !== false) {
        return 'fas fa-tint';
    } elseif (strpos($name, 'seasonal') !== false || strpos($name, 'holiday') !== false || strpos($name, 'christmas') !== false) {
        return 'fas fa-candy-cane';
    } elseif (strpos($name, 'spice') !== false || strpos($name, 'flavor') !== false) {
        return 'fas fa-pepper-hot';
    } elseif (strpos($name, 'nut') !== false || strpos($name, 'seed') !== false) {
        return 'fas fa-seedling';
    } elseif (strpos($name, 'fruit') !== false || strpos($name, 'berry') !== false) {
        return 'fas fa-apple-alt';
    } else {
        return 'fas fa-tag';
    }
}
@endphp

<script>
// Global variables
let currentEditId = null;
let csrfToken = '';
let pendingToggleId = null;
let pendingToggleState = null;
let pendingDeleteId = null;
let pendingDeleteName = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token from meta tag
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        csrfToken = csrfMeta.getAttribute('content');
    }
    
    loadParentCategories();
    setupIconSelection();
    setupToggleListeners();
});

// --- Modal Helper Functions ---

function showNotification(title, message, isError = false) {
    const modal = document.getElementById('notificationModal');
    document.getElementById('notifTitle').innerText = title;
    document.getElementById('notifMessage').innerText = message;
    
    const iconContainer = document.getElementById('notifIcon');
    if (isError) {
        iconContainer.innerHTML = '<i class="fas fa-times-circle text-red-500 text-3xl"></i>';
    } else {
        iconContainer.innerHTML = '<i class="fas fa-check-circle text-green-500 text-3xl"></i>';
    }

    modal.classList.remove('hidden');
}

function closeNotification() {
    document.getElementById('notificationModal').classList.add('hidden');
}

function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.remove('hidden');
    } else {
        overlay.classList.add('hidden');
    }
}

function setupIconSelection() {
    const iconButtons = document.querySelectorAll('.icon-option');
    iconButtons.forEach(button => {
        button.addEventListener('click', function() {
            iconButtons.forEach(btn => {
                btn.classList.remove('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
                btn.classList.add('bg-gray-100', 'text-gray-500', 'hover:bg-gray-200');
            });
            this.classList.remove('bg-gray-100', 'text-gray-500', 'hover:bg-gray-200');
            this.classList.add('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
            document.getElementById('selectedIcon').value = this.dataset.icon;
        });
    });
}

function setupToggleListeners() {
    const toggles = document.querySelectorAll('.category-toggle');
    toggles.forEach(toggle => {
        // Remove existing listeners to prevent duplicates if called multiple times
        toggle.removeEventListener('change', toggle.handleToggle);
        toggle.handleToggle = function() {
            const categoryId = this.dataset.categoryId;
            const isChecked = this.checked;
            // Prevent toggle during pending operation
            if (pendingToggleId !== null) {
                this.checked = !isChecked;
                return;
            }
            confirmToggleStatus(categoryId, isChecked);
        };
        toggle.addEventListener('change', toggle.handleToggle);
    });
}

function loadParentCategories(excludeId = null) {
    const select = document.getElementById('parentCategory');
    select.innerHTML = '<option value="">No Parent Category</option>';
    
    // Fetch actual active categories
    const url = `/admin/categories/parent${excludeId ? `?exclude_id=${excludeId}` : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(categories => {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading parent categories:', error);
        });
}

function openCategoryModal() {
    currentEditId = null;
    document.getElementById('modal-title').textContent = 'Create Category';
    document.getElementById('saveCategoryBtn').textContent = 'Save Category';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('parentCategory').value = '';
    document.getElementById('selectedIcon').value = 'fas fa-tag';
    setupIconSelection();
    clearErrors();
    document.getElementById('categoryModal').classList.remove('hidden');
    loadParentCategories();
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    currentEditId = null;
    clearErrors();
}

function clearErrors() {
    const errorElements = ['nameError', 'descriptionError', 'parentError'];
    errorElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.classList.add('hidden');
            element.textContent = '';
        }
    });
}

function showError(elementId, message) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.classList.remove('hidden');
}

// --- Logic ---

function saveCategory() {
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();
    const parentId = document.getElementById('parentCategory').value;
    
    if (!name) {
        showError('nameError', 'Category name is required');
        return;
    }
    
    showLoading(true);
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    if (parentId) formData.append('parent_id', parentId);
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    const url = currentEditId ? `/admin/categories/${currentEditId}` : '/admin/categories';
    const method = currentEditId ? 'POST' : 'POST'; // Always use POST
    
    // Add method override for editing
    if (currentEditId) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            showNotification('Success', data.message || (currentEditId ? 'Category updated successfully.' : 'Category created successfully.'));
            closeCategoryModal();
            
            // Refresh categories in other pages/tabs if they exist
            if (typeof refreshCategories === 'function') {
                refreshCategories();
            }
            
            // Reload page after short delay to show the new category
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification('Error', data.message || 'Error saving category', true);
            // Show validation errors if any
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    showError(field + 'Error', data.errors[field][0]);
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showLoading(false);
        showNotification('Error', 'Error saving category. Please try again.', true);
    });
}

function editCategory(categoryId) {
    showLoading(true);
    
    fetch(`/admin/categories/${categoryId}/edit`)
        .then(response => response.json())
        .then(category => {
            showLoading(false);
            currentEditId = categoryId;
            document.getElementById('modal-title').textContent = 'Edit Category';
            document.getElementById('saveCategoryBtn').textContent = 'Update Category';
            
            // Fill in the form fields
            document.getElementById('categoryName').value = category.name || '';
            document.getElementById('categoryDescription').value = category.description || '';
            
            // Load parent categories excluding current category
            loadParentCategories(categoryId);
            
            // Set parent category if exists
            if (category.parent_id) {
                // Wait a moment for parent categories to load
                setTimeout(() => {
                    document.getElementById('parentCategory').value = category.parent_id;
                }, 500);
            }
            
            document.getElementById('categoryModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            showLoading(false);
            showNotification('Error', 'Error loading category data', true);
        });
}

// --- Toggle Logic ---

function confirmToggleStatus(categoryId, isChecked) {
    pendingToggleId = categoryId;
    pendingToggleState = isChecked;
    
    const action = isChecked ? 'activate' : 'deactivate';
    
    document.getElementById('confirmTitle').innerText = `${action.charAt(0).toUpperCase() + action.slice(1)} Category`;
    document.getElementById('confirmMessage').innerText = `Are you sure you want to ${action} this category?`;
    
    document.getElementById('confirmBtn').onclick = executeToggle;
    // Reset styling
    document.getElementById('confirmBtn').className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

function cancelConfirm() {
    document.getElementById('confirmModal').classList.add('hidden');
    
    // Revert toggle state if cancelled
    if(pendingToggleId !== null) {
        const checkbox = document.querySelector(`input.category-toggle[data-category-id="${pendingToggleId}"]`);
        if(checkbox) checkbox.checked = !pendingToggleState;
        pendingToggleId = null;
    }
    // Clear delete state
    pendingDeleteId = null;
}

function executeToggle() {
    if(!pendingToggleId) return;
    document.getElementById('confirmModal').classList.add('hidden');
    showLoading(true);

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PATCH');
    
    fetch(`/admin/categories/${pendingToggleId}/toggle-status`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            const action = pendingToggleState ? 'activated' : 'deactivated';
            showNotification('Success', data.message || `Category has been ${action}.`);
            
            // Update UI locally
            const row = document.getElementById(`category-row-${pendingToggleId}`);
            if (row) {
                // Update status text
                const span = row.querySelector('.ml-3.text-sm.font-medium');
                if(span) {
                    span.textContent = pendingToggleState ? 'Active' : 'Inactive';
                    span.className = `ml-3 text-sm font-medium ${pendingToggleState ? 'text-green-600' : 'text-gray-500'}`;
                }
                
                // Update checkbox state
                const checkbox = row.querySelector('input.category-toggle');
                if(checkbox) {
                    checkbox.checked = pendingToggleState;
                }
                
                // Update row styling
                if (!pendingToggleState) {
                    row.classList.add('bg-gray-50', 'opacity-75');
                } else {
                    row.classList.remove('bg-gray-50', 'opacity-75');
                }
            }
            
            // Refresh categories in other pages/tabs to reflect status changes
            if (typeof refreshCategories === 'function') {
                refreshCategories();
            }
        } else {
            showNotification('Error', data.message || 'Error updating category status', true);
            // Revert checkbox state on error
            const checkbox = document.querySelector(`input.category-toggle[data-category-id="${pendingToggleId}"]`);
            if(checkbox) checkbox.checked = !pendingToggleState;
        }
        
        pendingToggleId = null;
    })
    .catch(error => {
        console.error('Error:', error);
        showLoading(false);
        showNotification('Error', 'Network error: Unable to update category status. Please try again.', true);
        // Revert checkbox state on error
        const checkbox = document.querySelector(`input.category-toggle[data-category-id="${pendingToggleId}"]`);
        if(checkbox) checkbox.checked = !pendingToggleState;
        pendingToggleId = null;
    });
}

// --- Delete Logic ---

function confirmDeleteCategory(categoryId, categoryName) {
    pendingDeleteId = categoryId;
    
    document.getElementById('confirmTitle').innerText = 'Delete Category';
    document.getElementById('confirmMessage').innerText = `Are you sure you want to delete "${categoryName}"? This cannot be undone.`;
    
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.onclick = executeDelete;
    confirmBtn.classList.remove('bg-chocolate', 'hover:bg-chocolate-dark');
    confirmBtn.classList.add('bg-red-600', 'hover:bg-red-700');
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

function executeDelete() {
    if(!pendingDeleteId) return;
    document.getElementById('confirmModal').classList.add('hidden');
    showLoading(true);

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'DELETE');
    
    fetch(`/admin/categories/${pendingDeleteId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        
        if (data.success) {
            showNotification('Success', data.message || 'Category deleted successfully.');
            
            const row = document.getElementById(`category-row-${pendingDeleteId}`);
            if(row) row.remove();
            
            // Refresh categories in other pages/tabs to remove deleted category
            if (typeof refreshCategories === 'function') {
                refreshCategories();
            }
            
            // If no more rows, reload the page to show empty state
            const tableBody = document.getElementById('categoriesTableBody');
            if (tableBody && tableBody.children.length === 0) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showNotification('Error', data.message || 'Error deleting category', true);
        }
        
        pendingDeleteId = null;
    })
    .catch(error => {
        console.error('Error:', error);
        showLoading(false);
        showNotification('Error', 'Error deleting category', true);
        pendingDeleteId = null;
    });
}

// Search & Filter functions
function searchCategories() {
    // Mock search - filter table rows
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById("categoriesTableBody");
    const tr = table.getElementsByTagName("tr");

    for (let i = 0; i < tr.length; i++) {
        const tdName = tr[i].getElementsByTagName("td")[0];
        if (tdName) {
            const txtValue = tdName.textContent || tdName.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}

function filterCategories() {
    const status = document.getElementById('statusFilter').value;
    const table = document.getElementById("categoriesTableBody");
    const tr = table.getElementsByTagName("tr");
    
    for (let i = 0; i < tr.length; i++) {
        const checkbox = tr[i].querySelector('input.category-toggle');
        if (checkbox) {
            const isActive = checkbox.checked;
            if (status === 'active' && !isActive) {
                tr[i].style.display = "none";
            } else if (status === 'inactive' && isActive) {
                tr[i].style.display = "none";
            } else {
                tr[i].style.display = "";
            }
        }
    }
}
</script>

<style>
.icon-option {
    transition: all 0.2s ease-in-out;
}
.icon-option:hover {
    transform: scale(1.1);
}
.peer:checked ~ span {
    transition: color 0.2s ease-in-out;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>