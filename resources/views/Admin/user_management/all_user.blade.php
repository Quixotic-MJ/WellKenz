@extends('Admin.layout.app')

@section('content')
<script>
    // Global variables
    let isEditMode = false;
    let editingUserId = null;
    
    // Variables for Modal Callbacks
    let pendingDeleteId = null;
    let pendingDeleteName = null;
    let pendingToggleId = null;
    let pendingToggleState = null;
    let pendingResetId = null;

    const adminUsersBaseUrl = "{{ url('/admin/users') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Global variables for user data
    let userData = {
        departments: [],
        positions: []
    };

    // --- 1. User Create/Edit Modal Functions ---

    // Load department and position data for dropdowns
    function loadUserData() {
        fetch('{{ route('admin.users.data') }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            userData.departments = data.departments || [];
            userData.positions = data.positions || [];
            
            // Populate department dropdown
            const departmentSelect = document.getElementById('department');
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            userData.departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept;
                option.textContent = dept;
                departmentSelect.appendChild(option);
            });

            // Populate position dropdown
            const positionSelect = document.getElementById('position');
            positionSelect.innerHTML = '<option value="">Select Position</option>';
            userData.positions.forEach(pos => {
                const option = document.createElement('option');
                option.value = pos;
                option.textContent = pos;
                positionSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading user data:', error);
            showNotification('Error', 'Failed to load department and position options.', true);
        });
    }

    // Generate employee ID (frontend - will be overridden by backend if needed)
    function generateEmployeeId() {
        // Simple client-side generation based on current timestamp
        // This will be replaced by server-generated ID during save
        const timestamp = Date.now().toString();
        const lastTwoDigits = timestamp.slice(-2);
        return 'EMP' + lastTwoDigits;
    }

    // Auto-fill employee ID field
    function fillEmployeeId() {
        const employeeIdField = document.getElementById('employee_id');
        employeeIdField.value = generateEmployeeId();
    }

    function openUserModal() {
        isEditMode = false;
        editingUserId = null;
        document.getElementById('modal-title').textContent = 'Create New User';
        document.getElementById('submitBtnText').textContent = 'Create Account';
        document.getElementById('passwordField').style.display = 'block';
        document.getElementById('userForm').reset();
        
        // Load user data and populate dropdowns
        loadUserData();
        
        // Auto-generate employee ID
        fillEmployeeId();
        
        document.getElementById('userModal').classList.remove('hidden');
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.add('hidden');
        isEditMode = false;
        editingUserId = null;
    }

    function togglePassword() {
        const passwordField = document.getElementById('password');
        const eyeIcon = document.querySelector('#passwordField .fa-eye, #passwordField .fa-eye-slash');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    function resetFilters() {
        window.location.href = "{{ url('/admin/users') }}";
    }

    // --- 6. Search and Filter Functionality ---

    // Debounce function to limit API calls
    function debounce(func, wait) {
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

    // Apply filters and search
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value;
        const roleFilter = document.getElementById('roleFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        
        // Build query parameters
        const params = new URLSearchParams();
        
        if (searchTerm.trim()) {
            params.append('search', searchTerm.trim());
        }
        if (roleFilter) {
            params.append('role', roleFilter);
        }
        if (statusFilter) {
            params.append('status', statusFilter);
        }
        
        // Redirect with parameters
        const baseUrl = "{{ url('/admin/users') }}";
        const queryString = params.toString();
        const newUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;
        
        window.location.href = newUrl;
    }

    // Setup search and filter event listeners
    function setupSearchAndFilters() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        // Search input with debounce
        if (searchInput) {
            const debouncedSearch = debounce(applyFilters, 500);
            searchInput.addEventListener('input', debouncedSearch);
        }

        // Filter dropdowns
        if (roleFilter) {
            roleFilter.addEventListener('change', applyFilters);
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFilters);
        }

        // Handle Enter key in search input
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyFilters();
                }
            });
        }
    }

    // --- 2. Custom Notification & Confirmation Logic ---

    // Show simple Success/Error modal
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

    // --- 3. Bulk Operations Logic ---

    function openBulkModal() {
        const selectedUsers = [];
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            selectedUsers.push(checkbox.dataset.userId);
        });

        if (selectedUsers.length === 0) {
            showNotification('Selection Required', 'Please select at least one user to perform bulk operations.', true);
            return;
        }

        // Update count in modal
        document.getElementById('bulkCount').innerText = selectedUsers.length;
        document.getElementById('bulkModal').classList.remove('hidden');
    }

    function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
    }

    function submitBulkOperation() {
        const operation = document.getElementById('bulkActionSelect').value;
        const selectedUsers = [];
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            selectedUsers.push(checkbox.dataset.userId);
        });

        if (!operation) {
            showNotification('Error', 'Please select an action.', true);
            return;
        }

        // Close bulk modal and perform request
        closeBulkModal();

        fetch('{{ route('admin.users.bulk-operations') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_ids: selectedUsers,
                operation: operation
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error', data.message || 'An error occurred', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error', 'An error occurred during bulk operation.', true);
        });
    }

    // --- 4. Action Confirmations (Delete, Toggle, Reset) ---

    // DELETE
    function confirmDeleteUser(userId, userName) {
        pendingDeleteId = userId;
        pendingDeleteName = userName;
        
        document.getElementById('confirmTitle').innerText = 'Delete User';
        document.getElementById('confirmMessage').innerText = `Are you sure you want to delete "${userName}"? This action cannot be undone.`;
        document.getElementById('confirmBtn').onclick = executeDelete;
        document.getElementById('confirmBtn').classList.remove('bg-blue-600', 'hover:bg-blue-700');
        document.getElementById('confirmBtn').classList.add('bg-red-600', 'hover:bg-red-700');
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function executeDelete() {
        if(!pendingDeleteId) return;
        document.getElementById('confirmModal').classList.add('hidden');

        fetch(`${adminUsersBaseUrl}/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Deleted', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error', data.message, true);
            }
        })
        .catch(error => showNotification('Error', 'Failed to delete user.', true));
    }

    // TOGGLE STATUS
    function confirmToggleStatus(userId, isChecked) {
        pendingToggleId = userId;
        pendingToggleState = isChecked;
        
        const action = isChecked ? 'activate' : 'deactivate';
        
        document.getElementById('confirmTitle').innerText = `${action.charAt(0).toUpperCase() + action.slice(1)} User`;
        document.getElementById('confirmMessage').innerText = `Are you sure you want to ${action} this user account?`;
        
        document.getElementById('confirmBtn').onclick = executeToggle;
        // Reset button color
        document.getElementById('confirmBtn').className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
        
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    // Handle "Cancel" on toggle (revert checkbox state)
    function cancelConfirm() {
        document.getElementById('confirmModal').classList.add('hidden');
        if(pendingToggleId !== null) {
            // Revert the checkbox visual state because user cancelled
            const checkbox = document.querySelector(`.user-status-toggle[data-user-id="${pendingToggleId}"]`);
            if(checkbox) checkbox.checked = !pendingToggleState; 
            pendingToggleId = null;
        }
    }

    function executeToggle() {
        if(!pendingToggleId) return;
        document.getElementById('confirmModal').classList.add('hidden');

        fetch(`${adminUsersBaseUrl}/${pendingToggleId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error', data.message, true);
                // Revert
                const checkbox = document.querySelector(`.user-status-toggle[data-user-id="${pendingToggleId}"]`);
                if(checkbox) checkbox.checked = !pendingToggleState;
            }
            pendingToggleId = null;
        })
        .catch(error => {
            showNotification('Error', 'Failed to update status.', true);
             // Revert
             const checkbox = document.querySelector(`.user-status-toggle[data-user-id="${pendingToggleId}"]`);
             if(checkbox) checkbox.checked = !pendingToggleState;
             pendingToggleId = null;
        });
    }

    // RESET PASSWORD
    function confirmResetPassword(userId) {
        pendingResetId = userId;
        
        document.getElementById('confirmTitle').innerText = 'Reset Password';
        document.getElementById('confirmMessage').innerText = 'Are you sure? The user will need to set a new password on next login.';
        document.getElementById('confirmBtn').onclick = executeReset;
        document.getElementById('confirmBtn').className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
        
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function executeReset() {
        if(!pendingResetId) return;
        document.getElementById('confirmModal').classList.add('hidden');

        fetch(`${adminUsersBaseUrl}/${pendingResetId}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show the temp password in a special modal or alert
                showNotification('Success', `Password reset! Temporary password: ${data.new_password}`);
            } else {
                showNotification('Error', data.message, true);
            }
            pendingResetId = null;
        })
        .catch(error => {
            showNotification('Error', 'Failed to reset password.', true);
            pendingResetId = null;
        });
    }

    // --- 5. Event Listeners Setup ---

    function setupEventListeners() {
        // User status toggle event listeners
        document.querySelectorAll('.user-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function(e) {
                const userId = this.dataset.userId;
                const isChecked = this.checked;
                confirmToggleStatus(userId, isChecked);
            });
        });

        // Select all users checkbox
        const selectAllCheckbox = document.getElementById('selectAllUsers');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const userCheckboxes = document.querySelectorAll('.user-checkbox');
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }

        // User form submission
        const userForm = document.getElementById('userForm');
        if (userForm) {
            userForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveUser();
            });
        }

        // Bulk action form
        const bulkForm = document.getElementById('bulkActionForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitBulkOperation();
            });
        }

        // Update select all checkbox when individual checkboxes change
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.user-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
                const selectAllCheckbox = document.getElementById('selectAllUsers');
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                }
            });
        });

        console.log('Event listeners setup complete');
    }

    // --- 6. Standard Edit/Save Functions ---

    function editUser(userId) {
        isEditMode = true;
        editingUserId = userId;
        document.getElementById('modal-title').textContent = 'Edit User';
        document.getElementById('submitBtnText').textContent = 'Update User';
        document.getElementById('passwordField').style.display = 'none';
        document.getElementById('userForm').reset();
        
        // Load user data and populate dropdowns
        loadUserData();
        
        fetch(`${adminUsersBaseUrl}/${userId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('name').value = data.name;
                document.getElementById('email').value = data.email;
                document.getElementById('role').value = data.role;
                document.getElementById('employee_id').value = data.employee_id || '';
                document.getElementById('phone').value = data.phone || '';
                
                // Set dropdown values after they're loaded
                setTimeout(() => {
                    document.getElementById('department').value = data.department || '';
                    document.getElementById('position').value = data.position || '';
                }, 100);
                
                document.getElementById('userModal').classList.remove('hidden');
            })
            .catch(error => showNotification('Error', 'Failed to load user details', true));
    }

    function saveUser() {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);
        
        // Convert FormData to JSON object
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        const url = isEditMode ? `${adminUsersBaseUrl}/${editingUserId}` : adminUsersBaseUrl;
        const method = isEditMode ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(data => { throw new Error(Object.values(data.errors).join('\n')); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Success', data.message);
                closeUserModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error', data.message || 'An error occurred', true);
            }
        })
        .catch(error => {
            showNotification('Validation Error', error.message, true);
        });
    }

</script>

<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage system access, roles, and passwords.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openUserModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Create New User
            </button>
            <button onclick="openBulkModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                <i class="fas fa-cogs mr-2"></i> Bulk Operations
            </button>
        </div>
    </div>

    {{-- 2. FILTERS & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                   placeholder="Search by name, email or ID..." value="{{ request('search', '') }}">
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select id="roleFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="purchasing" {{ request('role') == 'purchasing' ? 'selected' : '' }}>Purchasing Officer</option>
                <option value="inventory" {{ request('role') == 'inventory' ? 'selected' : '' }}>Inventory Manager</option>
                <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Staff</option>
            </select>
            <select id="statusFilter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button onclick="resetFilters()" class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
    </div>

    {{-- 3. USERS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAllUsers" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Profile</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors {{ !$user->is_active ? 'opacity-75 bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="user-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" 
                                   data-user-id="{{ $user->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($user->profile && $user->profile->profile_photo_path)
                                        <img class="h-10 w-10 rounded-full" src="{{ $user->profile->profile_photo_path }}" alt="{{ $user->name }}">
                                    @else
                                        <span class="h-10 w-10 rounded-full {{ $user->is_active ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center font-bold">
                                            {{ $user->initials }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    @if($user->profile && $user->profile->employee_id)
                                        <div class="text-xs text-gray-400">ID: {{ $user->profile->employee_id }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role_color_class }}">
                                {{ $user->formatted_role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer user-status-toggle" 
                                       data-user-id="{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ml-3 text-sm font-medium {{ $user->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->formatted_last_login }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button class="text-amber-600 hover:text-amber-900 tooltip" onclick="confirmResetPassword({{ $user->id }})" title="Reset Password">
                                <i class="fas fa-key bg-amber-50 p-2 rounded-md"></i>
                            </button>
                            <button class="text-blue-600 hover:text-blue-900 tooltip" onclick="editUser({{ $user->id }})" title="Edit User">
                                <i class="fas fa-edit bg-blue-50 p-2 rounded-md"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 tooltip" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete User">
                                <i class="fas fa-trash bg-red-50 p-2 rounded-md"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-gray-300 text-3xl mb-2"></i>
                                <p>No users found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         
        <!-- Pagination -->
        @if($users->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span> 
                        to <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span> 
                        of <span class="font-medium">{{ $users->total() }}</span> results
                    </p>
                </div>
                <div>
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

<!-- ==================== MODALS ==================== -->

<!-- 1. USER CREATE/EDIT MODAL -->
<div id="userModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUserModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="userForm">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New User</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Form Fields (Same as before) -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" id="email" name="email" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Assign Role</label>
                                    <select id="role" name="role" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option value="">Select Role</option>
                                        <option value="admin">Administrator</option>
                                        <option value="supervisor">Supervisor</option>
                                        <option value="purchasing">Purchasing Officer</option>
                                        <option value="inventory">Inventory Manager</option>
                                        <option value="employee">Staff</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                                        <div class="relative">
                                            <input type="text" id="employee_id" name="employee_id" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 text-gray-500 focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Auto-generated">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-lock text-gray-400 text-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                        <select id="department" name="department" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                            <option value="">Select Department</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                                        <select id="position" name="position" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                            <option value="">Select Position</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="passwordField">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Initial Password</label>
                                    <div class="relative">
                                        <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" value="Wellkenz123!">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <i class="fas fa-eye text-gray-400 cursor-pointer" onclick="togglePassword()"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        <span id="submitBtnText">Create Account</span>
                    </button>
                    <button type="button" onclick="closeUserModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. CONFIRMATION MODAL (Reusable) -->
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
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
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

<!-- 3. NOTIFICATION MODAL (Success/Error) -->
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
                            <p class="text-sm text-gray-500" id="notifMessage">Message goes here...</p>
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

<!-- 4. BULK ACTION MODAL -->
<div id="bulkModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeBulkModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Bulk User Operations</h3>
                <p class="text-sm text-gray-500 mb-4">You have selected <span id="bulkCount" class="font-bold text-chocolate">0</span> users.</p>
                
                <label class="block text-sm font-medium text-gray-700 mb-2">Choose Action</label>
                <select id="bulkActionSelect" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="" disabled selected>Select an operation...</option>
                    <option value="activate">Activate Selected</option>
                    <option value="deactivate">Deactivate Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="submitBulkOperation()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Apply Action
                </button>
                <button type="button" onclick="closeBulkModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Event listeners setup for user management...');
        setupEventListeners();
        setupSearchAndFilters();
    });
</script>
@endpush