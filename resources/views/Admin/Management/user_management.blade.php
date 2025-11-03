@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb', 'User Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            User created successfully!
        </div>

        <div id="errorMessage" class="hidden bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3">
            Error processing request.
        </div>

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
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalUsers">8</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="activeUsers">7</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Admins</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="adminsCount">2</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Inactive</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="inactiveUsers">1</p>
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('username')">
                                Username <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('role')">
                                Role <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('employee_name')">
                                Employee Name <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('status')">
                                Status <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="usersTableBody">
                        <!-- Sample User Data -->
                        <tr class="hover:bg-cream-bg transition user-row" data-username="admin.maria" data-role="admin" data-employee-name="Maria Garcia" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">admin.maria</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold">
                                    Admin
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Maria Garcia</p>
                                <p class="text-xs text-text-muted">Head Baker</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(1, 'admin.maria')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(1, 'admin.maria')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(1, 'admin.maria')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="john.smith" data-role="employee" data-employee-name="John Smith" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">john.smith</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-caramel text-white text-xs font-bold">
                                    Employee
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">John Smith</p>
                                <p class="text-xs text-text-muted">Pastry Chef</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(2, 'john.smith')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(2, 'john.smith')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(2, 'john.smith')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="inventory.sarah" data-role="inventory_staff" data-employee-name="Sarah Wilson" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">inventory.sarah</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">
                                    Inventory Staff
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Sarah Wilson</p>
                                <p class="text-xs text-text-muted">Inventory Clerk</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(3, 'inventory.sarah')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(3, 'inventory.sarah')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(3, 'inventory.sarah')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="purchase.robert" data-role="purchase_staff" data-employee-name="Robert Johnson" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">purchase.robert</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    Purchase Staff
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Robert Johnson</p>
                                <p class="text-xs text-text-muted">Purchasing Officer</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(4, 'purchase.robert')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(4, 'purchase.robert')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(4, 'purchase.robert')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="baker.david" data-role="employee" data-employee-name="David Brown" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">baker.david</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-caramel text-white text-xs font-bold">
                                    Employee
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">David Brown</p>
                                <p class="text-xs text-text-muted">Baker</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(5, 'baker.david')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(5, 'baker.david')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(5, 'baker.david')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="sales.emily" data-role="sales_staff" data-employee-name="Emily Chen" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">sales.emily</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">
                                    Sales Staff
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Emily Chen</p>
                                <p class="text-xs text-text-muted">Sales Staff</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(6, 'sales.emily')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(6, 'sales.emily')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(6, 'sales.emily')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="admin.michael" data-role="admin" data-employee-name="Michael Wong" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">admin.michael</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold">
                                    Admin
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Michael Wong</p>
                                <p class="text-xs text-text-muted">Supervisor</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(7, 'admin.michael')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(7, 'admin.michael')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openDeactivateUserModal(7, 'admin.michael')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition user-row" data-username="driver.tom" data-role="driver" data-employee-name="Tom Jackson" data-status="inactive">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">driver.tom</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">
                                    Driver
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Tom Jackson</p>
                                <p class="text-xs text-text-muted">Delivery Driver</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">
                                    INACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditUserModal(8, 'driver.tom')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button onclick="openResetPasswordModal(8, 'driver.tom')"
                                        class="px-3 py-1 bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition">
                                        Reset Password
                                    </button>
                                    <button onclick="openActivateUserModal(8, 'driver.tom')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition">
                                        Activate
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">8</span> of 8 users</p>
            </div>
        </div>

        <!-- Role Distribution Card -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Role Distribution</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Employee</span>
                        <span class="text-text-muted">2 (25%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-caramel h-2" style="width: 25%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Admin</span>
                        <span class="text-text-muted">2 (25%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-purple-500 h-2" style="width: 25%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Inventory Staff</span>
                        <span class="text-text-muted">1 (12.5%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-blue-500 h-2" style="width: 12.5%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Purchase Staff</span>
                        <span class="text-text-muted">1 (12.5%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-green-500 h-2" style="width: 12.5%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Sales Staff</span>
                        <span class="text-text-muted">1 (12.5%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-yellow-500 h-2" style="width: 12.5%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Driver</span>
                        <span class="text-text-muted">1 (12.5%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-gray-500 h-2" style="width: 12.5%"></div>
                    </div>
                </div>
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
                                <option value="inventory_staff">Inventory Staff</option>
                                <option value="purchase_staff">Purchase Staff</option>
                                <option value="sales_staff">Sales Staff</option>
                                <option value="driver">Driver</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Employee Name</label>
                        <select name="employee_id" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="">Select Employee</option>
                            <option value="1">Maria Garcia - Head Baker</option>
                            <option value="2">John Smith - Pastry Chef</option>
                            <option value="3">Robert Johnson - Purchasing Officer</option>
                            <option value="4">Sarah Wilson - Inventory Clerk</option>
                            <option value="5">David Brown - Baker</option>
                            <option value="6">Emily Chen - Sales Staff</option>
                            <option value="7">Michael Wong - Supervisor</option>
                            <option value="8">Tom Jackson - Delivery Driver</option>
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

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Status</label>
                        <select name="status" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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
                                <option value="inventory_staff">Inventory Staff</option>
                                <option value="purchase_staff">Purchase Staff</option>
                                <option value="sales_staff">Sales Staff</option>
                                <option value="driver">Driver</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Employee Name</label>
                        <select name="employee_id" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="">Select Employee</option>
                            <option value="1">Maria Garcia - Head Baker</option>
                            <option value="2">John Smith - Pastry Chef</option>
                            <option value="3">Robert Johnson - Purchasing Officer</option>
                            <option value="4">Sarah Wilson - Inventory Clerk</option>
                            <option value="5">David Brown - Baker</option>
                            <option value="6">Emily Chen - Sales Staff</option>
                            <option value="7">Michael Wong - Supervisor</option>
                            <option value="8">Tom Jackson - Delivery Driver</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Status</label>
                        <select name="status" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Reset Password</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Reset password for <span id="resetPasswordUsername" class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">A temporary password will be generated and sent to the user's email.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeResetPasswordModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="resetPassword()" class="px-6 py-2 bg-amber-500 text-white hover:bg-amber-600 transition">
                        Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate User Modal -->
    <div id="deactivateUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Deactivate User</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to deactivate <span id="deactivateUserName" class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">The user will lose system access but their account will be preserved.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeactivateUserModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="deactivateUser()" class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate User Modal -->
    <div id="activateUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Activate User</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to activate <span id="activateUserName" class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">The user will regain system access with their existing permissions.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeActivateUserModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="activateUser()" class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition">
                        Activate
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
        // Sample user data for UI demonstration
        let users = [
            {
                id: 1,
                username: "admin.maria",
                fullName: "Maria Garcia",
                role: "admin",
                email: "maria.garcia@wellkenz.com",
                status: "active"
            },
            {
                id: 2,
                username: "john.smith",
                fullName: "John Smith",
                role: "employee",
                email: "john.smith@wellkenz.com",
                status: "active"
            },
            {
                id: 3,
                username: "inventory.sarah",
                fullName: "Sarah Wilson",
                role: "inventory_staff",
                email: "sarah.wilson@wellkenz.com",
                status: "active"
            },
            {
                id: 4,
                username: "purchase.robert",
                fullName: "Robert Johnson",
                role: "purchase_staff",
                email: "robert.johnson@wellkenz.com",
                status: "active"
            },
            {
                id: 5,
                username: "baker.david",
                fullName: "David Brown",
                role: "employee",
                email: "david.brown@wellkenz.com",
                status: "active"
            },
            {
                id: 6,
                username: "sales.emily",
                fullName: "Emily Chen",
                role: "sales_staff",
                email: "emily.chen@wellkenz.com",
                status: "active"
            },
            {
                id: 7,
                username: "admin.michael",
                fullName: "Michael Wong",
                role: "admin",
                email: "michael.wong@wellkenz.com",
                status: "active"
            },
            {
                id: 8,
                username: "driver.tom",
                fullName: "Tom Jackson",
                role: "driver",
                email: "tom.jackson@wellkenz.com",
                status: "inactive"
            }
        ];

        let currentUserId = null;
        let currentUsername = null;

        // Modal Functions
        function openCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.add('hidden');
            document.getElementById('createUserForm').reset();
        }

        function openEditUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            
            // In a real app, you would fetch user data here
            // For demo, we'll just populate with sample data
            const user = users.find(u => u.id === userId);
            if (user) {
                document.getElementById('editUserForm').querySelector('[name="username"]').value = user.username;
                document.getElementById('editUserForm').querySelector('[name="role"]').value = user.role;
                document.getElementById('editUserForm').querySelector('[name="status"]').value = user.status;
            }
            
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            currentUserId = null;
            currentUsername = null;
        }

        function openResetPasswordModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('resetPasswordUsername').textContent = username;
            document.getElementById('resetPasswordModal').classList.remove('hidden');
        }

        function closeResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
            currentUserId = null;
            currentUsername = null;
        }

        function openDeactivateUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('deactivateUserName').textContent = username;
            document.getElementById('deactivateUserModal').classList.remove('hidden');
        }

        function closeDeactivateUserModal() {
            document.getElementById('deactivateUserModal').classList.add('hidden');
            currentUserId = null;
            currentUsername = null;
        }

        function openActivateUserModal(userId, username) {
            currentUserId = userId;
            currentUsername = username;
            document.getElementById('activateUserName').textContent = username;
            document.getElementById('activateUserModal').classList.remove('hidden');
        }

        function closeActivateUserModal() {
            document.getElementById('activateUserModal').classList.add('hidden');
            currentUserId = null;
            currentUsername = null;
        }

        // User Actions
        function resetPassword() {
            showMessage('Password reset successfully! Temporary password sent to user email.', 'success');
            closeResetPasswordModal();
        }

        function deactivateUser() {
            showMessage('User deactivated successfully!', 'success');
            closeDeactivateUserModal();
        }

        function activateUser() {
            showMessage('User activated successfully!', 'success');
            closeActivateUserModal();
        }

        // Form Handling
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Simulate creating user
            showMessage('User created successfully!', 'success');
            closeCreateUserModal();
            this.reset();
        });

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Simulate updating user
            showMessage('User updated successfully!', 'success');
            closeEditUserModal();
        });

        // Search functionality
        function searchUsers(query) {
            const rows = document.querySelectorAll('.user-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const username = row.getAttribute('data-username');
                const role = row.getAttribute('data-role');
                const employeeName = row.getAttribute('data-employee-name');
                const searchText = (username + ' ' + role + ' ' + employeeName).toLowerCase();
                
                if (searchText.includes(query.toLowerCase()) || query === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Sort functionality
        function sortTable(criteria) {
            // Simple client-side sorting demonstration
            const tbody = document.getElementById('usersTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const aValue = a.querySelector('td:first-child').textContent.toLowerCase();
                const bValue = b.querySelector('td:first-child').textContent.toLowerCase();
                
                if (criteria === 'username_asc' || criteria === 'username') {
                    return aValue.localeCompare(bValue);
                } else if (criteria === 'username_desc') {
                    return bValue.localeCompare(aValue);
                }
                return 0;
            });
            
            // Remove existing rows and append sorted rows
            rows.forEach(row => tbody.appendChild(row));
            
            showMessage(`Sorted by ${criteria}`, 'success');
        }

        // Utility Functions
        function showMessage(message, type) {
            const messageDiv = type === 'success' ? 
                document.getElementById('successMessage') : 
                document.getElementById('errorMessage');
            
            messageDiv.textContent = message;
            messageDiv.classList.remove('hidden');
            
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }

        // Close modals when clicking outside
        document.getElementById('createUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateUserModal();
        });

        document.getElementById('editUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditUserModal();
        });

        document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) closeResetPasswordModal();
        });

        document.getElementById('deactivateUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeactivateUserModal();
        });

        document.getElementById('activateUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeActivateUserModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateUserModal();
                closeEditUserModal();
                closeResetPasswordModal();
                closeDeactivateUserModal();
                closeActivateUserModal();
            }
        });
    </script>
@endsection