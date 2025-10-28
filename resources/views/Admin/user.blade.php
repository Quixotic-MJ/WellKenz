@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb')
<div class="flex items-center space-x-2 text-sm">
    <span class="text-text-muted">Administration</span>
    <span class="text-border-soft">/</span>
    <span class="text-text-dark font-semibold">User Management</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">User Management</h1>
            <p class="text-text-muted mt-2">Manage system accounts and assign roles to employees</p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Add User Button -->
            <button onclick="openAddUserModal()" class="flex items-center space-x-2 px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition-colors rounded-lg">
                <i class="fas fa-user-plus"></i>
                <span class="font-semibold">Add User</span>
            </button>
            
            <!-- Bulk Actions -->
            <div class="relative">
                <select class="appearance-none bg-white border-2 border-border-soft px-4 py-2 pr-8 rounded-lg text-sm text-text-dark focus:outline-none focus:border-caramel transition-colors">
                    <option>Bulk Actions</option>
                    <option>Activate Users</option>
                    <option>Deactivate Users</option>
                    <option>Reset Passwords</option>
                    <option>Export Users</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-3 text-text-muted text-xs"></i>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">47</p>
                    <p class="text-xs text-green-600 mt-1 font-semibold">↑ 3 new this month</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active Users</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">42</p>
                    <p class="text-xs text-green-600 mt-1 font-semibold">89% active rate</p>
                </div>
                <div class="w-10 h-10 bg-green-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Admin Users</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">8</p>
                    <p class="text-xs text-purple-600 mt-1 font-semibold">System administrators</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-user-shield text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Pending Activation -->
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">3</p>
                    <p class="text-xs text-orange-600 mt-1 font-semibold">Needs activation</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-user-clock text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-sm border-2 border-border-soft rounded-lg overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-bold text-text-dark">System Users</h3>
                <div class="flex items-center space-x-3">
                    <!-- Role Filter -->
                    <div class="relative">
                        <select class="appearance-none bg-white border border-border-soft px-3 py-2 pr-8 rounded text-sm text-text-dark focus:outline-none focus:border-caramel transition-colors">
                            <option value="all">All Roles</option>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="baker">Baker</option>
                            <option value="staff">Staff</option>
                            <option value="viewer">Viewer</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-2 top-3 text-text-muted text-xs"></i>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="relative">
                        <select class="appearance-none bg-white border border-border-soft px-3 py-2 pr-8 rounded text-sm text-text-dark focus:outline-none focus:border-caramel transition-colors">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-2 top-3 text-text-muted text-xs"></i>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" placeholder="Search users..." 
                            class="pl-9 pr-4 py-2 bg-white border border-border-soft placeholder-text-muted text-text-dark text-sm focus:outline-none focus:border-caramel transition-colors w-64 rounded">
                        <i class="fas fa-search absolute left-3 top-2.5 text-text-muted text-xs"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <!-- Admin User -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-sm font-bold">JD</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">John Doe</div>
                                    <div class="text-xs text-text-muted">john.doe@wellkenz.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                <i class="fas fa-user-shield mr-1"></i>
                                Administrator
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Management</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Today, 09:42 AM</div>
                            <div class="text-xs text-text-muted">From: Office Network</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditUserModal('user1')" class="flex items-center space-x-1 px-2 py-1 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </button>
                                <button onclick="openResetPasswordModal('user1')" class="flex items-center space-x-1 px-2 py-1 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-key"></i>
                                    <span>Password</span>
                                </button>
                                <div class="relative">
                                    <button onclick="toggleActionMenu('user-menu-1')" class="p-1.5 text-text-muted hover:text-text-dark hover:bg-border-soft rounded transition-colors">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="user-menu-1" class="hidden absolute right-0 mt-1 w-48 bg-white shadow-lg border-2 border-border-soft rounded-lg z-10">
                                        <button class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 flex items-center space-x-2">
                                            <i class="fas fa-eye w-4"></i>
                                            <span>View Profile</span>
                                        </button>
                                        <button class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 flex items-center space-x-2">
                                            <i class="fas fa-user-lock w-4"></i>
                                            <span>Deactivate</span>
                                        </button>
                                        <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2">
                                            <i class="fas fa-trash w-4"></i>
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Manager User -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-600 flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-sm font-bold">SM</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Sarah Martinez</div>
                                    <div class="text-xs text-text-muted">sarah.m@wellkenz.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                <i class="fas fa-user-tie mr-1"></i>
                                Manager
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Production</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Yesterday, 03:15 PM</div>
                            <div class="text-xs text-text-muted">From: Mobile App</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditUserModal('user2')" class="flex items-center space-x-1 px-2 py-1 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </button>
                                <button onclick="openResetPasswordModal('user2')" class="flex items-center space-x-1 px-2 py-1 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-key"></i>
                                    <span>Password</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Baker User -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-500 flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-sm font-bold">MW</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Mike Wilson</div>
                                    <div class="text-xs text-text-muted">mike.w@wellkenz.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                <i class="fas fa-user-chef mr-1"></i>
                                Head Baker
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Kitchen</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 14, 2024</div>
                            <div class="text-xs text-text-muted">From: Kitchen Terminal</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditUserModal('user3')" class="flex items-center space-x-1 px-2 py-1 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </button>
                                <button onclick="openResetPasswordModal('user3')" class="flex items-center space-x-1 px-2 py-1 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-key"></i>
                                    <span>Password</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Pending User -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-400 flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-sm font-bold">JR</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Jessica Rivera</div>
                                    <div class="text-xs text-text-muted">jessica.r@wellkenz.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">
                                <i class="fas fa-user mr-1"></i>
                                Staff
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Pastry</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Never logged in</div>
                            <div class="text-xs text-text-muted">Account created: Dec 10</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                <i class="fas fa-clock mr-1"></i>
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="activateUser('user4')" class="flex items-center space-x-1 px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded hover:bg-green-600 transition-colors">
                                    <i class="fas fa-check"></i>
                                    <span>Activate</span>
                                </button>
                                <button onclick="openEditUserModal('user4')" class="flex items-center space-x-1 px-2 py-1 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <div class="text-sm text-text-muted">
                    Showing 1 to 4 of 47 users
                </div>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-caramel text-white border border-caramel rounded">
                        1
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        2
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        3
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Permissions Overview -->
    <div class="bg-white shadow-sm border-2 border-border-soft p-6 rounded-lg">
        <h3 class="font-display text-xl font-bold text-text-dark mb-6">Role Permissions</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Permission</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Administrator</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Baker</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Staff</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Viewer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 font-semibold text-text-dark">User Management</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 font-semibold text-text-dark">Approve Requisitions</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 font-semibold text-text-dark">Create Purchase Orders</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 font-semibold text-text-dark">View Inventory</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 font-semibold text-text-dark">Generate Reports</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Add New User</h3>
                <button onclick="closeAddUserModal()" class="text-text-muted hover:text-text-dark transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <form id="addUserForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">First Name</label>
                        <input type="text" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Enter first name">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Last Name</label>
                        <input type="text" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Enter last name">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Email Address</label>
                    <input type="email" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="user@wellkenz.com">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Role</label>
                        <select class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors">
                            <option value="">Select Role</option>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="baker">Baker</option>
                            <option value="staff">Staff</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                        <select class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors">
                            <option value="">Select Department</option>
                            <option value="management">Management</option>
                            <option value="production">Production</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="pastry">Pastry</option>
                            <option value="packaging">Packaging</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Password</label>
                        <input type="password" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Enter password">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Confirm Password</label>
                        <input type="password" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Confirm password">
                    </div>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Send welcome email with login instructions</span>
                    </label>
                </div>
            </form>
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeAddUserModal()" class="px-6 py-2 border-2 border-border-soft text-text-dark hover:border-chocolate transition-colors rounded-lg">
                Cancel
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition-colors rounded-lg">
                Create User
            </button>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-xl font-bold text-text-dark">Reset Password</h3>
                <button onclick="closeResetPasswordModal()" class="text-text-muted hover:text-text-dark transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <p class="text-text-dark mb-4">Reset password for <span id="resetUserName" class="font-semibold">John Doe</span>?</p>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">New Password</label>
                    <input type="password" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Enter new password">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Confirm Password</label>
                    <input type="password" class="w-full border-2 border-border-soft rounded-lg px-4 py-2 focus:outline-none focus:border-caramel transition-colors" placeholder="Confirm new password">
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Require password change on next login</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeResetPasswordModal()" class="px-6 py-2 border-2 border-border-soft text-text-dark hover:border-chocolate transition-colors rounded-lg">
                Cancel
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition-colors rounded-lg">
                Reset Password
            </button>
        </div>
    </div>
</div>

<style>
    .font-display {
        font-family: 'Playfair Display', serif;
    }
    
    .fa-user-chef:before {
        content: "👨‍🍳";
    }
</style>

<script>
    function toggleActionMenu(menuId) {
        const menu = document.getElementById(menuId);
        menu.classList.toggle('hidden');
        
        // Close other open menus
        document.querySelectorAll('[id^="user-menu-"]').forEach(otherMenu => {
            if (otherMenu.id !== menuId) {
                otherMenu.classList.add('hidden');
            }
        });
    }

    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }

    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }

    function openResetPasswordModal(userId) {
        // In a real app, you would fetch user data
        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }

    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }

    function activateUser(userId) {
        if (confirm('Are you sure you want to activate this user?')) {
            // In a real app, you would make an API call
            console.log(`Activating user: ${userId}`);
            alert('User activated successfully!');
        }
    }

    function openEditUserModal(userId) {
        // In a real app, you would open edit modal with user data
        console.log(`Editing user: ${userId}`);
        // For now, redirect to a hypothetical edit page
        // window.location.href = `/admin/users/${userId}/edit`;
    }

    // Close modals when clicking outside
    document.getElementById('addUserModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddUserModal();
        }
    });

    document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResetPasswordModal();
        }
    });

    // Close action menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="user-menu-"]') && !e.target.closest('button[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="user-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // Set user management as active by default
    document.addEventListener('DOMContentLoaded', function() {
        setActiveMenu('menu-users');
    });
</script>
@endsection