@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Category Management</h1>
            <p class="text-sm text-gray-500">Organize your inventory items into logical groups for easier reporting and requisitions.</p>
        </div>
        <div>
            <button onclick="openCategoryModal()" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Create Category
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input type="text" 
                    id="searchInput" 
                    class="block w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                    placeholder="Search categories...">
            </div>
            
            <div class="w-full md:w-48 relative">
                <select id="statusFilter" 
                        class="block w-full py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer">
                    <option value="">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CATEGORIES TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Category Name</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Description</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Linked Items</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody" class="bg-white divide-y divide-border-soft">
                    @forelse($categories as $category)
                    <tr class="group hover:bg-cream-bg transition-colors duration-200 {{ !$category->is_active ? 'bg-gray-50 opacity-60' : '' }}" 
                        id="category-row-{{ $category->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 {{ $category->is_active ? 'bg-white border border-border-soft' : 'bg-gray-100' }} rounded-lg flex items-center justify-center {{ $category->is_active ? 'text-chocolate' : 'text-gray-400' }} shadow-sm">
                                    <i class="{{ $category->icon ?? 'fas fa-tag' }} text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-chocolate category-name">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-[10px] text-gray-400 uppercase tracking-wide truncate max-w-xs category-desc font-medium">{{ Str::limit($category->description, 25) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                {{ $category->description ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $category->linked_items_count > 0 ? 'bg-chocolate/10 text-chocolate' : 'bg-gray-100 text-gray-500' }}">
                                {{ $category->linked_items_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       class="sr-only peer category-toggle" 
                                       data-category-id="{{ $category->id }}"
                                       {{ $category->is_active ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-caramel/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600 shadow-inner"></div>
                                <span class="ml-3 text-xs font-bold uppercase tracking-wide {{ $category->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                <button class="text-chocolate hover:text-white hover:bg-chocolate p-2 rounded-lg transition-all tooltip"
                                        onclick="editCategory({{ $category->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-white hover:bg-red-600 p-2 rounded-lg transition-all tooltip"
                                        onclick="confirmDeleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                        {{ $category->linked_items_count > 0 || $category->children()->count() > 0 ? 'disabled' : '' }} title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                    <i class="fas fa-folder-open text-chocolate/30 text-2xl"></i>
                                </div>
                                <p class="font-display text-lg font-bold text-chocolate">No categories found</p>
                                <p class="text-sm text-gray-400 mt-1">Get started by creating your first category</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($categories->hasPages())
        <div class="bg-white px-6 py-4 border-t border-border-soft">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>

<div id="categoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeCategoryModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-border-soft">
            
            <div class="bg-chocolate px-6 py-4">
                <h3 class="font-display text-lg font-bold text-white" id="modal-title">Create Category</h3>
            </div>

            <div class="bg-white px-6 pt-6 pb-6">
                <div class="space-y-5">
                    
                    <div>
                        <label for="categoryName" class="block text-sm font-bold text-chocolate mb-1">Category Name</label>
                        <input type="text" 
                               id="categoryName" 
                               name="name"
                               class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2.5 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" 
                               placeholder="e.g., Decor & Toppers"
                               required>
                        <div id="nameError" class="mt-1 text-xs text-red-600 hidden font-bold"></div>
                    </div>

                    <div>
                        <label for="categoryDescription" class="block text-sm font-bold text-chocolate mb-1">Description</label>
                        <textarea id="categoryDescription" 
                                  name="description"
                                  rows="3" 
                                  class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2.5 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" 
                                  placeholder="Briefly describe what items belong here..."></textarea>
                        <div id="descriptionError" class="mt-1 text-xs text-red-600 hidden font-bold"></div>
                    </div>

                    <div>
                        <label for="parentCategory" class="block text-sm font-bold text-chocolate mb-1">Parent Category (Optional)</label>
                        <select id="parentCategory" 
                                name="parent_id"
                                class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2.5 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                            <option value="">No Parent Category</option>
                            </select>
                        <div id="parentError" class="mt-1 text-xs text-red-600 hidden font-bold"></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-border-soft">
                        <label class="block text-sm font-bold text-chocolate mb-2">Visual Icon</label>
                        <div class="flex gap-3 flex-wrap justify-center sm:justify-start">
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-chocolate text-white flex items-center justify-center ring-2 ring-offset-2 ring-chocolate shadow-sm transition-all" data-icon="fas fa-tag"><i class="fas fa-tag"></i></button>
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-caramel hover:border-caramel flex items-center justify-center transition-all" data-icon="fas fa-utensils"><i class="fas fa-utensils"></i></button>
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-caramel hover:border-caramel flex items-center justify-center transition-all" data-icon="fas fa-box"><i class="fas fa-box"></i></button>
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-caramel hover:border-caramel flex items-center justify-center transition-all" data-icon="fas fa-tint"><i class="fas fa-tint"></i></button>
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-caramel hover:border-caramel flex items-center justify-center transition-all" data-icon="fas fa-wheat"><i class="fas fa-wheat"></i></button>
                            <button type="button" class="icon-option w-10 h-10 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-caramel hover:border-caramel flex items-center justify-center transition-all" data-icon="fas fa-snowflake"><i class="fas fa-snowflake"></i></button>
                        </div>
                        <input type="hidden" id="selectedIcon" name="icon" value="fas fa-tag">
                    </div>

                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" 
                        id="saveCategoryBtn"
                        onclick="saveCategory()" 
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Save Category
                </button>
                <button type="button" 
                        onclick="closeCategoryModal()" 
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="confirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="cancelConfirm()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-cream-bg border border-border-soft sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-caramel text-lg"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Confirm
                </button>
                <button type="button" onclick="cancelConfirm()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="notificationModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeNotification()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="notifIcon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="notifTitle">Notification</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="notifMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" onclick="closeNotification()" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-chocolate/20 backdrop-blur-sm">
    <div class="bg-white rounded-xl p-6 flex items-center space-x-4 shadow-2xl border border-border-soft">
        <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-border-soft border-t-caramel"></div>
        <span class="text-chocolate font-bold font-display">Processing...</span>
    </div>
</div>

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
    setupPaginationHandlers();
    
    // Initialize search and filter functionality
    initializeSearchAndFilter();
});

// Initialize search and filter functionality
function initializeSearchAndFilter() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) {
        // Remove any existing listeners to prevent duplicates
        searchInput.removeEventListener('keyup', handleSearch);
        // Add new listener with debouncing
        searchInput.addEventListener('keyup', handleSearch);
    }
    
    if (statusFilter) {
        // Remove any existing listeners to prevent duplicates
        statusFilter.removeEventListener('change', handleFilter);
        // Add new listener
        statusFilter.addEventListener('change', handleFilter);
    }
}

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
                // Remove active classes
                btn.classList.remove('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
                // Add inactive classes
                btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
            });
            // Add active classes to clicked
            this.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
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
    
    // Reset visual selection of icons
    const iconButtons = document.querySelectorAll('.icon-option');
    iconButtons.forEach(btn => {
        btn.classList.remove('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
        btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
    });
    // Select the first one (tag)
    const firstBtn = iconButtons[0];
    if(firstBtn) {
        firstBtn.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
        firstBtn.classList.add('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
    }

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
    const icon = document.getElementById('selectedIcon').value;
    
    if (!name) {
        showError('nameError', 'Category name is required');
        return;
    }
    
    showLoading(true);
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    // Note: The backend logic likely doesn't save the icon from this form field based on your original code
    // but we send it anyway in case you implement it later.
    formData.append('icon', icon); 
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
            
            // Update UI immediately for better user experience
            updateCategoryRow(name, icon);
            
            // Re-initialize search functionality in case DOM changed
            setTimeout(() => {
                initializeSearchAndFilter();
                applyFilters(); // Refresh the current search/filter state
            }, 100);
            
            // Refresh categories in other pages/tabs if they exist
            if (typeof refreshCategories === 'function') {
                refreshCategories();
            }
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
            
            // Set the selected icon
            const selectedIcon = category.icon || 'fas fa-tag';
            document.getElementById('selectedIcon').value = selectedIcon;
            
            // Update icon selection UI
            const iconButtons = document.querySelectorAll('.icon-option');
            iconButtons.forEach(btn => {
                btn.classList.remove('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
                btn.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
                
                if (btn.dataset.icon === selectedIcon) {
                    btn.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-400', 'hover:text-caramel', 'hover:border-caramel');
                    btn.classList.add('bg-chocolate', 'text-white', 'ring-2', 'ring-offset-2', 'ring-chocolate');
                }
            });
            
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
    document.getElementById('confirmBtn').className = "w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all";
    
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
                const span = row.querySelector('.ml-3.text-xs.font-bold');
                if(span) {
                    span.textContent = pendingToggleState ? 'Active' : 'Inactive';
                    span.className = `ml-3 text-xs font-bold uppercase tracking-wide ${pendingToggleState ? 'text-green-600' : 'text-gray-400'}`;
                }
                
                // Update checkbox state
                const checkbox = row.querySelector('input.category-toggle');
                if(checkbox) {
                    checkbox.checked = pendingToggleState;
                }
                
                // Update row styling
                if (!pendingToggleState) {
                    row.classList.add('bg-gray-50', 'opacity-60');
                } else {
                    row.classList.remove('bg-gray-50', 'opacity-60');
                }
            }
            
            // Re-initialize search functionality in case DOM changed
            setTimeout(() => {
                initializeSearchAndFilter();
                applyFilters(); // Refresh the current search/filter state
            }, 100);
            
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
            
            // Re-initialize search functionality in case DOM changed
            setTimeout(() => {
                initializeSearchAndFilter();
                applyFilters(); // Refresh the current search/filter state
            }, 100);
            
            // Refresh categories in other pages/tabs to remove deleted category
            if (typeof refreshCategories === 'function') {
                refreshCategories();
            }
            
            // If no more rows, reload the page to show empty state
            // const tableBody = document.getElementById('categoriesTableBody');
            // if (tableBody && tableBody.children.length === 0) {
            //     setTimeout(() => window.location.reload(), 1500);
            // }
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

// Enhanced Search & Filter functions with server-side integration
let searchTimeout;
let currentRequest = null;

// Unified search and filter handler
function handleSearch() {
    // Clear existing timeout
    clearTimeout(searchTimeout);
    
    // Add debouncing to improve performance
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300); // 300ms delay
}

function handleFilter() {
    // Clear existing timeout
    clearTimeout(searchTimeout);
    
    // Apply filters immediately for filter changes
    applyFilters();
}

function applyFilters(page = 1) {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    
    if (!searchInput || !statusFilter) {
        console.error('Search input or status filter not found');
        return;
    }
    
    const searchTerm = searchInput.value.trim();
    const status = statusFilter.value;
    
    // Cancel any pending request
    if (currentRequest) {
        currentRequest.abort();
    }
    
    showLoading(true);
    
    // Build query parameters
    const params = new URLSearchParams();
    if (searchTerm !== '') {
        params.append('search', searchTerm);
    }
    if (status !== '') {
        params.append('status', status);
    }
    params.append('page', page);
    
    // Make AJAX request to server
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `/admin/categories?${params.toString()}`);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        showLoading(false);
        currentRequest = null;
        
        if (xhr.status === 200) {
            try {
                const response = xhr.responseText;
                // Create a temporary DOM element to parse the response
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;
                
                // Extract the table body from the response
                const newTableBody = tempDiv.querySelector('#categoriesTableBody');
                const newPagination = tempDiv.querySelector('.pagination');
                
                if (newTableBody) {
                    // Update the table body
                    const currentTableBody = document.getElementById('categoriesTableBody');
                    currentTableBody.innerHTML = newTableBody.innerHTML;
                    
                    // Update pagination if it exists
                    const currentPaginationContainer = document.querySelector('.bg-white.px-6.py-4.border-t.border-border-soft');
                    if (currentPaginationContainer && newPagination) {
                        currentPaginationContainer.innerHTML = newPagination.outerHTML;
                        
                        // Re-attach pagination click handlers
                        setupPaginationHandlers();
                    } else if (currentPaginationContainer && !newPagination) {
                        // No pagination in response, remove pagination container
                        currentPaginationContainer.remove();
                    }
                    
                    // Re-initialize toggle listeners for new rows
                    setupToggleListeners();
                    
                    // Show message if no results
                    const rows = currentTableBody.querySelectorAll('tr');
                    const hasData = Array.from(rows).some(row => row.querySelector('.category-name'));
                    
                    if (!hasData) {
                        showNoResultsMessage(searchTerm, status);
                    }
                    
                    // Scroll to top of table
                    currentTableBody.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (error) {
                console.error('Error parsing server response:', error);
                showNotification('Error', 'Error loading search results', true);
            }
        } else {
            showNotification('Error', 'Error loading search results', true);
        }
    };
    
    xhr.onerror = function() {
        showLoading(false);
        currentRequest = null;
        showNotification('Error', 'Network error occurred', true);
    };
    
    xhr.ontimeout = function() {
        showLoading(false);
        currentRequest = null;
        showNotification('Error', 'Request timeout', true);
    };
    
    currentRequest = xhr;
    xhr.send();
}

function showNoResultsMessage(searchTerm, status) {
    const tableBody = document.getElementById('categoriesTableBody');
    if (!tableBody) return;
    
    // Clear existing content
    tableBody.innerHTML = `
        <tr>
            <td colspan="5" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                        <i class="fas fa-search text-chocolate/30 text-2xl"></i>
                    </div>
                    <p class="font-display text-lg font-bold text-chocolate">No categories found</p>
                    <p class="text-sm text-gray-400 mt-1">
                        ${searchTerm ? `No categories match "${searchTerm}"` : 'No categories match the selected filters'}
                    </p>
                </div>
            </td>
        </tr>
    `;
}

function setupPaginationHandlers() {
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;
            applyFilters(page);
        });
    });
}

// Legacy function names for backward compatibility
function searchCategories() {
    handleSearch();
}

function filterCategories() {
    handleFilter();
}

function updateCategoryRow(categoryName, categoryIcon) {
    const categoryNameElements = document.querySelectorAll('.category-name');
    
    // Find the row with matching category name and update it
    categoryNameElements.forEach(element => {
        if (element.textContent.trim() === categoryName.trim()) {
            const row = element.closest('tr');
            if (row) {
                // Update the icon
                const iconElement = row.querySelector('.flex-shrink-0 i');
                if (iconElement) {
                    iconElement.className = `${categoryIcon} text-lg`;
                }
            }
        }
    });
}
</script>
@endsection