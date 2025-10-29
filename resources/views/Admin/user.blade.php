@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">User Management</h1>
            <p class="text-text-muted mt-2">Manage system accounts and roles</p>
        </div>
        <button onclick="openAddUserModal()" class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
            <i class="fas fa-user-plus mr-2"></i>
            Add User
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Users</p>
            <p class="text-3xl font-bold text-text-dark mt-2">47</p>
        </div>

        <div class="bg-white border-2 border-green-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
            <p class="text-3xl font-bold text-text-dark mt-2">42</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Administrators</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-orange-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white border-2 border-border-soft">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-xl font-bold text-text-dark">System Users</h3>
                <div class="relative">
                    <input type="text" placeholder="Search..." 
                        class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <!-- Admin User -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">John Doe</p>
                            <p class="text-xs text-text-muted">john.doe@wellkenz.com</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold">ADMIN</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Management</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Today, 09:42 AM</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">ACTIVE</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openEditUserModal('user1')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Manager User -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">Sarah Martinez</p>
                            <p class="text-xs text-text-muted">sarah.m@wellkenz.com</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">MANAGER</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Production</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Yesterday, 03:15 PM</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">ACTIVE</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openEditUserModal('user2')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Baker User -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">Mike Wilson</p>
                            <p class="text-xs text-text-muted">mike.w@wellkenz.com</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold">BAKER</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Kitchen</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 14, 2024</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">ACTIVE</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openEditUserModal('user3')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Pending User -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">Jessica Rivera</p>
                            <p class="text-xs text-text-muted">jessica.r@wellkenz.com</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">STAFF</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Pastry</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Never logged in</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold">PENDING</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="activateUser('user4')" class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition">
                                Activate
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Showing 1 to 4 of 47 users</p>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Previous</button>
                    <button class="px-3 py-1 bg-caramel text-white">1</button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">2</button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Permissions -->
    <div class="bg-white border-2 border-border-soft p-6">
        <h3 class="font-display text-xl font-bold text-text-dark mb-6">Role Permissions</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Permission</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Manager</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Baker</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">User Management</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">Approve Requisitions</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">Create Purchase Orders</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">View Inventory</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Add New User</h3>
                <button onclick="closeAddUserModal()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <form class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">First Name</label>
                        <input type="text" class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Last Name</label>
                        <input type="text" class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                    <input type="email" class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Role</label>
                        <select class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option>Select Role</option>
                            <option>Administrator</option>
                            <option>Manager</option>
                            <option>Baker</option>
                            <option>Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                        <select class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                            <option>Select Department</option>
                            <option>Management</option>
                            <option>Production</option>
                            <option>Kitchen</option>
                            <option>Pastry</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Password</label>
                        <input type="password" class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Confirm</label>
                        <input type="password" class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                </div>
            </form>
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeAddUserModal()" class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                Cancel
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                Create User
            </button>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    
    .font-display { font-family: 'Playfair Display', serif; }
    .cream-bg { background-color: #faf7f3; }
    .text-dark { color: #1a1410; }
    .text-muted { color: #8b7355; }
    .chocolate { background-color: #3d2817; }
    .caramel { background-color: #c48d3f; }
    .caramel-dark { background-color: #a67332; }
    .border-soft { border-color: #e8dfd4; }
</style>

<script>
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }

    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }

    function openEditUserModal(userId) {
        console.log(`Edit user: ${userId}`);
        openAddUserModal(); // Reuse same modal
    }

    function activateUser(userId) {
        if (confirm('Activate this user?')) {
            alert('User activated successfully!');
        }
    }

    document.getElementById('addUserModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddUserModal();
    });
</script>
@endsection