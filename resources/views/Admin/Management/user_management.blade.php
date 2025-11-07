@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb', 'User Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-gray-900">User Management</h1>
                <p class="text-gray-500 mt-2">Manage user accounts and access permissions</p>
            </div>
            <button onclick="openCreateUserModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-user-plus mr-2"></i>
                Add User
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Users</p>
                <p class="text-3xl font-semibold text-gray-900 mt-2" id="totalUsers">{{ $totalUsers }}</p>
            </div>

            <div class="bg-white border border-green-200 rounded-lg p-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Active</p>
                <p class="text-3xl font-semibold text-gray-900 mt-2" id="activeUsers">{{ $activeUsers }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Admins</p>
                <p class="text-3xl font-semibold text-gray-900 mt-2" id="adminsCount">{{ $adminsCount }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Inactive</p>
                <p class="text-3xl font-semibold text-gray-900 mt-2" id="inactiveUsers">{{ $inactiveUsers }}</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">User Accounts</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search users..." onkeyup="searchUsers(this.value)"
                                class="pl-9 pr-4 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition w-64"
                                onfocus="this.placeholder='Search by username, role, name...'">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                            <button type="button" onclick="clearSearch()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden" id="clearSearchBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="usersTable">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('username')">
                                Username <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('role')">
                                Role <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('employee_name')">
                                Employee Name <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('status')">
                                Status <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="usersTableBody">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition user-row" 
                                data-username="{{ strtolower($user->username) }}"
                                data-role="{{ strtolower($user->role) }}" 
                                data-employee-name="{{ strtolower($user->employee->emp_name) }}"
                                data-status="{{ strtolower($user->employee->emp_status) }}"
                                data-position="{{ strtolower($user->employee->emp_position) }}">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $user->username }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-purple-100 text-purple-700',
                                            'employee' => 'bg-gray-600 text-white',
                                            'inventory' => 'bg-blue-100 text-blue-700',
                                            'purchasing' => 'bg-green-100 text-green-700',
                                            'supervisor' => 'bg-yellow-100 text-yellow-700',
                                        ];
                                        $color = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-block px-2 py-1 {{ $color }} text-xs font-semibold capitalize rounded">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $user->employee->emp_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->employee->emp_position }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->employee->emp_status === 'active')
                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button
                                            class="edit-btn px-3 py-1 bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition rounded"
                                            data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                            Edit
                                        </button>
                                        <button
                                            class="change-password-btn px-3 py-1 bg-amber-600 text-white text-xs font-medium hover:bg-amber-700 transition rounded"
                                            data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                            Change Password
                                        </button>
                                        @if ($user->employee->emp_status === 'active')
                                            <button
                                                class="deactivate-btn px-3 py-1 bg-red-600 text-white text-xs font-medium hover:bg-red-700 transition rounded"
                                                data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                                Deactivate
                                            </button>
                                        @else
                                            <button
                                                class="activate-btn px-3 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700 transition rounded"
                                                data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                                Activate
                                            </button>
                                        @endif
                                        <button
                                            class="delete-btn px-3 py-1 bg-red-800 text-white text-xs font-medium hover:bg-red-900 transition rounded"
                                            data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <p class="text-sm text-gray-500">Showing <span id="visibleCount">{{ $users->count() }}</span> of
                    {{ $users->count() }} users</p>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-semibold text-gray-900">Add New User</h3>
                    <button onclick="closeCreateUserModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="createUserForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="Enter username">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="role" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                                <option value="inventory">Inventory Staff</option>
                                <option value="purchasing">Purchase Staff</option>
                                <option value="supervisor">Supervisor</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <select name="emp_id" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Select Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->emp_id }}">
                                    {{ $employee->emp_name }} - {{ $employee->emp_position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="Enter password">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="Confirm password">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeCreateUserModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-semibold text-gray-900">Edit User</h3>
                    <button onclick="closeEditUserModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="editUserForm" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <!-- Display employee info (read-only) -->
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Associated Employee</label>
                        <p class="text-sm text-gray-900" id="edit_employee_display"></p>
                        <p class="text-xs text-gray-500 mt-1">Employee association cannot be changed</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" id="edit_username" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="Enter username">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="role" id="edit_role" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                                <option value="inventory">Inventory Staff</option>
                                <option value="purchasing">Purchase Staff</option>
                                <option value="supervisor">Supervisor</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditUserModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Change Password</h3>
            </div>
            <div class="p-6">
                <form id="changePasswordForm" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="change_password_user_id">
                    <p class="text-gray-900 mb-4">Change password for <span id="changePasswordUsername"
                            class="font-semibold"></span></p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Enter new password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Confirm new password">
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeChangePasswordModal()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-amber-600 text-white hover:bg-amber-700 transition rounded">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deactivate User Modal -->
    <div id="deactivateUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Deactivate User</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-900 mb-4">Are you sure you want to deactivate <span id="deactivateUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-gray-500 mb-4">The user will lose system access but their account will be
                    preserved.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeactivateUserModal()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                        Cancel
                    </button>
                    <button type="button" onclick="deactivateUser()"
                        class="px-6 py-2 bg-red-600 text-white hover:bg-red-700 transition rounded">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate User Modal -->
    <div id="activateUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Activate User</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-900 mb-4">Are you sure you want to activate <span id="activateUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-gray-500 mb-4">The user will regain system access with their existing permissions.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeActivateUserModal()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                        Cancel
                    </button>
                    <button type="button" onclick="activateUser()"
                        class="px-6 py-2 bg-green-600 text-white hover:bg-green-700 transition rounded">
                        Activate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Delete User</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-900 mb-4">Are you sure you want to delete <span id="deleteUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-gray-500 mb-4">This action cannot be undone. All user data will be permanently
                    removed.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteUserModal()"
                        class="px-6 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                        Cancel
                    </button>
                    <button type="button" onclick="deleteUser()"
                        class="px-6 py-2 bg-red-800 text-white hover:bg-red-900 transition rounded">
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let currentUsername = null;
        let currentSearchTerm = '';

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            setupSearchClearButton();
        });

        function initializeEventListeners() {
            // Form Handling
            const createUserForm = document.getElementById('createUserForm');
            const editUserForm = document.getElementById('editUserForm');
            const changePasswordForm = document.getElementById('changePasswordForm');

            if (createUserForm) {
                createUserForm.addEventListener('submit', handleCreateUser);
            }

            if (editUserForm) {
                editUserForm.addEventListener('submit', handleEditUser);
            }

            if (changePasswordForm) {
                changePasswordForm.addEventListener('submit', handleChangePassword);
            }

            // Event delegation for action buttons
            document.getElementById('usersTableBody').addEventListener('click', function(e) {
                const target = e.target;

                // Edit button
                if (target.classList.contains('edit-btn') || target.closest('.edit-btn')) {
                    const button = target.classList.contains('edit-btn') ? target : target.closest('.edit-btn');
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    openEditUserModal(userId, username);
                }

                // Change Password button
                if (target.classList.contains('change-password-btn') || target.closest('.change-password-btn')) {
                    const button = target.classList.contains('change-password-btn') ? target : target.closest(
                        '.change-password-btn');
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    openChangePasswordModal(userId, username);
                }

                // Deactivate button
                if (target.classList.contains('deactivate-btn') || target.closest('.deactivate-btn')) {
                    const button = target.classList.contains('deactivate-btn') ? target : target.closest(
                        '.deactivate-btn');
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    openDeactivateUserModal(userId, username);
                }

                // Activate button
                if (target.classList.contains('activate-btn') || target.closest('.activate-btn')) {
                    const button = target.classList.contains('activate-btn') ? target : target.closest(
                        '.activate-btn');
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    openActivateUserModal(userId, username);
                }

                // Delete button
                if (target.classList.contains('delete-btn') || target.closest('.delete-btn')) {
                    const button = target.classList.contains('delete-btn') ? target : target.closest('.delete-btn');
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    openDeleteUserModal(userId, username);
                }
            });

            // Modal close handlers
            setupModalCloseHandlers();
        }

        function setupModalCloseHandlers() {
            // Close modals when clicking outside
            const modals = ['createUserModal', 'editUserModal', 'changePasswordModal', 'deactivateUserModal',
                'activateUserModal', 'deleteUserModal'
            ];

            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeModal(modalId);
                        }
                    });
                }
            });

            // Close modals with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modals.forEach(modalId => closeModal(modalId));
                }
            });
        }

        function setupSearchClearButton() {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearSearchBtn');

            if (searchInput && clearBtn) {
                searchInput.addEventListener('input', function() {
                    if (this.value.length > 0) {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                });
            }
        }

        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            searchUsers('');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }

            // Reset forms if needed
            if (modalId === 'createUserModal') {
                document.getElementById('createUserForm').reset();
            }
            if (modalId === 'changePasswordModal') {
                document.getElementById('changePasswordForm').reset();
            }

            currentUserId = null;
            currentUsername = null;
        }

        // Modal Functions
        function openCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function closeCreateUserModal() {
            closeModal('createUserModal');
        }

        function openEditUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;

            // Show loading state
            const modal = document.getElementById('editUserModal');
            modal.classList.remove('hidden');

            // Fetch user data via AJAX
            fetch(`/users/${userId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(user => {
                    document.getElementById('edit_user_id').value = user.user_id;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_role').value = user.role;

                    // Display employee information
                    if (user.employee) {
                        document.getElementById('edit_employee_display').textContent = 
                            `${user.employee.emp_name} - ${user.employee.emp_position}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error loading user data', 'error');
                    closeEditUserModal();
                });
        }

        function closeEditUserModal() {
            closeModal('editUserModal');
        }

        function openChangePasswordModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('changePasswordUsername').textContent = username;
            document.getElementById('change_password_user_id').value = userId;
            document.getElementById('changePasswordModal').classList.remove('hidden');
        }

        function closeChangePasswordModal() {
            closeModal('changePasswordModal');
        }

        function openDeactivateUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('deactivateUserName').textContent = username;
            document.getElementById('deactivateUserModal').classList.remove('hidden');
        }

        function closeDeactivateUserModal() {
            closeModal('deactivateUserModal');
        }

        function openActivateUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('activateUserName').textContent = username;
            document.getElementById('activateUserModal').classList.remove('hidden');
        }

        function closeActivateUserModal() {
            closeModal('activateUserModal');
        }

        function openDeleteUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('deleteUserName').textContent = username;
            document.getElementById('deleteUserModal').classList.remove('hidden');
        }

        function closeDeleteUserModal() {
            closeModal('deleteUserModal');
        }

        // User Actions
        function deactivateUser() {
            toggleUserStatus('deactivate');
        }

        function activateUser() {
            toggleUserStatus('activate');
        }

        function deleteUser() {
            if (!currentUserId) {
                showMessage('No user selected', 'error');
                return;
            }

            // Create form data with CSRF token
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', getCsrfToken());

            fetch(`/users/${currentUserId}`, {
                    method: 'POST', // Use POST method for DELETE with _method parameter
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        // Reload the page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                    closeDeleteUserModal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error deleting user', 'error');
                    closeDeleteUserModal();
                });
        }

        function toggleUserStatus(action) {
            if (!currentUserId) {
                showMessage('No user selected', 'error');
                return;
            }

            // Create form data with CSRF token
            const formData = new FormData();
            formData.append('_token', getCsrfToken());

            fetch(`/users/${currentUserId}/toggle-status`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        // Reload the page to reflect changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                    if (action === 'deactivate') {
                        closeDeactivateUserModal();
                    } else {
                        closeActivateUserModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error updating user status', 'error');
                    if (action === 'deactivate') {
                        closeDeactivateUserModal();
                    } else {
                        closeActivateUserModal();
                    }
                });
        }

        // Form Handlers
        function handleCreateUser(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/users', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeCreateUserModal();
                        // Reload the page to show new user
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error creating user', 'error');
                });
        }

        function handleEditUser(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(`/users/${currentUserId}`, {
                    method: 'POST', // Use POST method for PUT with _method parameter
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeEditUserModal();
                        // Reload the page to show updated user
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error updating user', 'error');
                });
        }

        function handleChangePassword(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(`/users/${currentUserId}/password`, {
                    method: 'POST', // Use POST method for PUT with _method parameter
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeChangePasswordModal();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error changing password', 'error');
                });
        }

        // Search functionality - Fixed version
        function searchUsers(query) {
            currentSearchTerm = query.toLowerCase().trim();
            const rows = document.querySelectorAll('.user-row');
            let visibleCount = 0;

            // Show clear button if there's text
            const clearBtn = document.getElementById('clearSearchBtn');
            if (clearBtn) {
                if (currentSearchTerm.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            rows.forEach(row => {
                const username = row.getAttribute('data-username').toLowerCase();
                const role = row.getAttribute('data-role').toLowerCase();
                const employeeName = row.getAttribute('data-employee-name').toLowerCase();
                const status = row.getAttribute('data-status').toLowerCase();
                const position = row.getAttribute('data-position').toLowerCase();

                // Search in multiple fields
                const matches = username.includes(currentSearchTerm) || 
                               role.includes(currentSearchTerm) || 
                               employeeName.includes(currentSearchTerm) ||
                               status.includes(currentSearchTerm) ||
                               position.includes(currentSearchTerm);

                if (matches || currentSearchTerm === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Sort functionality
        let currentSort = {
            field: 'username',
            direction: 'asc'
        };

        function sortTable(field) {
            const tbody = document.getElementById('usersTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));

            // Toggle direction if same field
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'asc';
            }

            rows.sort((a, b) => {
                let aValue, bValue;

                switch (field) {
                    case 'username':
                        aValue = a.getAttribute('data-username').toLowerCase();
                        bValue = b.getAttribute('data-username').toLowerCase();
                        break;
                    case 'role':
                        aValue = a.getAttribute('data-role').toLowerCase();
                        bValue = b.getAttribute('data-role').toLowerCase();
                        break;
                    case 'employee_name':
                        aValue = a.getAttribute('data-employee-name').toLowerCase();
                        bValue = b.getAttribute('data-employee-name').toLowerCase();
                        break;
                    case 'status':
                        aValue = a.getAttribute('data-status').toLowerCase();
                        bValue = b.getAttribute('data-status').toLowerCase();
                        break;
                    default:
                        return 0;
                }

                if (currentSort.direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });

            // Remove existing rows and append sorted rows
            rows.forEach(row => tbody.appendChild(row));

            // Update sort indicators
            updateSortIndicators(field, currentSort.direction);
        }

        function updateSortIndicators(field, direction) {
            // Remove all sort indicators
            document.querySelectorAll('th i').forEach(icon => {
                icon.className = 'fas fa-sort ml-1 text-xs';
            });

            // Add active sort indicator
            const header = document.querySelector(`th[onclick="sortTable('${field}')"]`);
            if (header) {
                const icon = header.querySelector('i');
                if (icon) {
                    icon.className = direction === 'asc' ? 
                        'fas fa-sort-up ml-1 text-xs' : 
                        'fas fa-sort-down ml-1 text-xs';
                }
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

        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }

            // Fallback: try to get from forms
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                return tokenInput.value;
            }

            console.error('CSRF token not found');
            return '';
        }
    </script>
@endsection