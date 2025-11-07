@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb', 'User Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded"></div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">User Management</h1>
                <p class="text-text-muted mt-2">Manage user accounts and access permissions</p>
            </div>
            <button onclick="openCreateUserModal()"
                class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
                <i class="fas fa-user-plus mr-2"></i>
                Add User
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Users</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalUsers">{{ $totalUsers }}</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="activeUsers">{{ $activeUsers }}</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Admins</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="adminsCount">{{ $adminsCount }}</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Inactive</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="inactiveUsers">{{ $inactiveUsers }}</p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">User Accounts</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Sort Dropdown -->
                        <select onchange="sortTable(this.value)"
                            class="border-2 border-border-soft px-3 py-2 text-sm focus:outline-none focus:border-chocolate transition bg-white">
                            <option value="username_asc">Sort by: Username A-Z</option>
                            <option value="username_desc">Sort by: Username Z-A</option>
                            <option value="role">Sort by: Role</option>
                            <option value="status">Sort by: Status</option>
                        </select>

                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search users..." onkeyup="searchUsers(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="usersTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('username')">
                                Username <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('role')">
                                Role <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('employee_name')">
                                Employee Name <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('status')">
                                Status <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="usersTableBody">
                        @foreach ($users as $user)
                            <tr class="hover:bg-cream-bg transition user-row" data-username="{{ $user->username }}"
                                data-role="{{ $user->role }}" data-employee-name="{{ $user->employee->emp_name }}"
                                data-status="{{ $user->employee->emp_status }}">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-text-dark">{{ $user->username }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-purple-100 text-purple-700',
                                            'employee' => 'bg-caramel text-white',
                                            'inventory' => 'bg-blue-100 text-blue-700',
                                            'purchasing' => 'bg-green-100 text-green-700',
                                            'supervisor' => 'bg-yellow-100 text-yellow-700',
                                        ];
                                        $color = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-block px-2 py-1 {{ $color }} text-xs font-bold capitalize">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-text-dark">{{ $user->employee->emp_name }}</p>
                                    <p class="text-xs text-text-muted">{{ $user->employee->emp_position }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->employee->emp_status === 'active')
                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                            ACTIVE
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button
                                            class="edit-btn px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition"
                                            data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                            Edit
                                        </button>
                                        <button
                                            class="change-password-btn px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition"
                                            data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                            Change Password
                                        </button>
                                        @if ($user->employee->emp_status === 'active')
                                            <button
                                                class="deactivate-btn px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition"
                                                data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                                Deactivate
                                            </button>
                                        @else
                                            <button
                                                class="activate-btn px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition"
                                                data-user-id="{{ $user->user_id }}" data-username="{{ $user->username }}">
                                                Activate
                                            </button>
                                        @endif
                                        <button
                                            class="delete-btn px-3 py-1 bg-red-700 text-white text-xs font-semibold hover:bg-red-800 transition"
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

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">{{ $users->count() }}</span> of
                    {{ $users->count() }} users</p>
            </div>
        </div>

        <!-- Role Distribution Card -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Role Distribution</h3>
            <div class="space-y-4">
                @php
                    $roleDistribution = $users->groupBy('role')->map->count();
                    $totalUsersCount = $users->count();
                @endphp
                @foreach ($roleDistribution as $role => $count)
                    @php
                        $percentage = $totalUsersCount > 0 ? round(($count / $totalUsersCount) * 100, 1) : 0;
                        $roleColors = [
                            'admin' => 'bg-purple-500',
                            'employee' => 'bg-caramel',
                            'inventory' => 'bg-blue-500',
                            'purchasing' => 'bg-green-500',
                            'supervisor' => 'bg-yellow-500',
                        ];
                        $color = $roleColors[$role] ?? 'bg-gray-500';
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span
                                class="font-semibold text-text-dark capitalize">{{ str_replace('_', ' ', $role) }}</span>
                            <span class="text-text-muted">{{ $count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2">
                            <div class="h-2 {{ $color }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Add New User</h3>
                    <button onclick="closeCreateUserModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="createUserForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Username</label>
                            <input type="text" name="username" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Enter username">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Role</label>
                            <select name="role" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
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
                        <label class="block text-sm font-semibold text-text-dark mb-2">Employee</label>
                        <select name="emp_id" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
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
                            <label class="block text-sm font-semibold text-text-dark mb-2">Password</label>
                            <input type="password" name="password" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Enter password">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Confirm password">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeCreateUserModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Edit User</h3>
                    <button onclick="closeEditUserModal()" class="text-text-muted hover:text-text-dark">
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
                    <div class="bg-gray-50 p-4 rounded border">
                        <label class="block text-sm font-semibold text-text-dark mb-2">Associated Employee</label>
                        <p class="text-sm text-text-dark" id="edit_employee_display"></p>
                        <p class="text-xs text-text-muted mt-1">Employee association cannot be changed</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Username</label>
                            <input type="text" name="username" id="edit_username" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Enter username">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Role</label>
                            <select name="role" id="edit_role" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
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
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
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
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Change Password</h3>
            </div>
            <div class="p-6">
                <form id="changePasswordForm" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" id="change_password_user_id">
                    <p class="text-text-dark mb-4">Change password for <span id="changePasswordUsername"
                            class="font-semibold"></span></p>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">New Password</label>
                        <input type="password" name="password" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Enter new password">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Confirm new password">
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeChangePasswordModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-amber-500 text-white hover:bg-amber-600 transition">
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
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Deactivate User</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to deactivate <span id="deactivateUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">The user will lose system access but their account will be
                    preserved.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeactivateUserModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="deactivateUser()"
                        class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate User Modal -->
    <div id="activateUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Activate User</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to activate <span id="activateUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">The user will regain system access with their existing permissions.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeActivateUserModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="activateUser()"
                        class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition">
                        Activate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Delete User</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to delete <span id="deleteUserName"
                        class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">This action cannot be undone. All user data will be permanently
                    removed.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteUserModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="deleteUser()"
                        class="px-6 py-2 bg-red-700 text-white hover:bg-red-800 transition">
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        .cream-bg {
            background-color: #faf7f3;
        }

        .text-text-dark {
            color: #1a1410;
        }

        .text-text-muted {
            color: #8b7355;
        }

        .bg-caramel {
            background-color: #c48d3f;
        }

        .bg-caramel-dark {
            background-color: #a67332;
        }

        .bg-chocolate {
            background-color: #3d2817;
        }

        .border-border-soft {
            border-color: #e8dfd4;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        let currentUserId = null;
        let currentUsername = null;

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
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

                    // Only set emp_id if the element exists
                    const empIdElement = document.getElementById('edit_emp_id');
                    if (empIdElement) {
                        empIdElement.value = user.emp_id;
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

        // Search functionality
        function searchUsers(query) {
            if (query.length === 0) {
                // Show all rows if query is empty
                document.querySelectorAll('.user-row').forEach(row => {
                    row.style.display = '';
                });
                document.getElementById('visibleCount').textContent = document.querySelectorAll('.user-row').length;
                return;
            }

            fetch(`/users/search?search=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    }
                })
                .then(response => response.json())
                .then(users => {
                    const tbody = document.getElementById('usersTableBody');
                    tbody.innerHTML = '';

                    users.forEach(user => {
                        const row = createUserRow(user);
                        tbody.appendChild(row);
                    });

                    document.getElementById('visibleCount').textContent = users.length;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function createUserRow(user) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-cream-bg transition user-row';
            row.setAttribute('data-username', user.username);
            row.setAttribute('data-role', user.role);
            row.setAttribute('data-employee-name', user.employee.emp_name);
            row.setAttribute('data-status', user.employee.emp_status);

            const roleColors = {
                'admin': 'bg-purple-100 text-purple-700',
                'employee': 'bg-caramel text-white',
                'inventory': 'bg-blue-100 text-blue-700',
                'purchasing': 'bg-green-100 text-green-700',
                'supervisor': 'bg-yellow-100 text-yellow-700'
            };

            const statusBadge = user.employee.emp_status === 'active' ?
                '<span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">ACTIVE</span>' :
                '<span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">INACTIVE</span>';

            const isActive = user.employee.emp_status === 'active';

            row.innerHTML = `
            <td class="px-6 py-4">
                <p class="text-sm font-bold text-text-dark">${user.username}</p>
            </td>
            <td class="px-6 py-4">
                <span class="inline-block px-2 py-1 ${roleColors[user.role]} text-xs font-bold capitalize">
                    ${user.role.replace('_', ' ')}
                </span>
            </td>
            <td class="px-6 py-4">
                <p class="text-sm text-text-dark">${user.employee.emp_name}</p>
                <p class="text-xs text-text-muted">${user.employee.emp_position}</p>
            </td>
            <td class="px-6 py-4">
                ${statusBadge}
            </td>
            <td class="px-6 py-4">
                <div class="flex space-x-2">
                    <button class="edit-btn px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition"
                        data-user-id="${user.user_id}" data-username="${user.username}">
                        Edit
                    </button>
                    <button class="change-password-btn px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition"
                        data-user-id="${user.user_id}" data-username="${user.username}">
                        Change Password
                    </button>
                    ${isActive ? 
                        `<button class="deactivate-btn px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition"
                                data-user-id="${user.user_id}" data-username="${user.username}">
                                Deactivate
                            </button>` :
                        `<button class="activate-btn px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition"
                                data-user-id="${user.user_id}" data-username="${user.username}">
                                Activate
                            </button>`
                    }
                    <button class="delete-btn px-3 py-1 bg-red-700 text-white text-xs font-semibold hover:bg-red-800 transition"
                        data-user-id="${user.user_id}" data-username="${user.username}">
                        Delete
                    </button>
                </div>
            </td>
        `;

            return row;
        }

        // Sort functionality
        function sortTable(criteria) {
            const tbody = document.getElementById('usersTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let aValue, bValue;

                switch (criteria) {
                    case 'username_asc':
                        aValue = a.getAttribute('data-username').toLowerCase();
                        bValue = b.getAttribute('data-username').toLowerCase();
                        return aValue.localeCompare(bValue);
                    case 'username_desc':
                        aValue = a.getAttribute('data-username').toLowerCase();
                        bValue = b.getAttribute('data-username').toLowerCase();
                        return bValue.localeCompare(aValue);
                    case 'role':
                        aValue = a.getAttribute('data-role').toLowerCase();
                        bValue = b.getAttribute('data-role').toLowerCase();
                        return aValue.localeCompare(bValue);
                    case 'status':
                        aValue = a.getAttribute('data-status').toLowerCase();
                        bValue = b.getAttribute('data-status').toLowerCase();
                        return aValue.localeCompare(bValue);
                    default:
                        return 0;
                }
            });

            // Remove existing rows and append sorted rows
            rows.forEach(row => tbody.appendChild(row));
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
