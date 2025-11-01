@extends('Admin.layout.app')

@section('title', 'User Management - WellKenz ERP')

@section('breadcrumb', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

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
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['total_users'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-green-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['active_users'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Administrators</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['admin_users'] ?? 0 }}</p>
        </div>

        <div class="bg-white border-2 border-orange-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stats['pending_users'] ?? 0 }}</p>
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
                    @forelse($users as $user)
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">{{ $user->employee->emp_name ?? 'N/A' }}</p>
                            <p class="text-xs text-text-muted">{{ $user->username }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $roleColors = [
                                    'admin' => 'bg-purple-100 text-purple-700',
                                    'supervisor' => 'bg-blue-100 text-blue-700',
                                    'purchasing' => 'bg-green-100 text-green-700',
                                    'inventory' => 'bg-orange-100 text-orange-700',
                                    'employee' => 'bg-gray-100 text-gray-700'
                                ];
                                $color = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-block px-2 py-1 {{ $color }} text-xs font-bold uppercase">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">{{ $user->employee->department->dept_name ?? 'N/A' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">
                                {{ $user->updated_at->diffForHumans() }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">ACTIVE</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <button onclick="openEditUserModal({{ $user->user_id }})" 
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                    Edit
                                </button>
                                <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to delete this user?')"
                                            class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-text-muted">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p>No users found in the database.</p>
                            <p class="text-sm mt-2">Make sure you have run the migrations and seeders.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Showing {{ $users->count() }} users</p>
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
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Purchasing</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Inventory</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Employee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">User Management</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
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
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">Create Purchase Orders</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">Manage Inventory</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
                        <td class="px-6 py-4"><i class="fas fa-times text-red-600"></i></td>
                    </tr>
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4 font-semibold">View Reports</td>
                        <td class="px-6 py-4"><i class="fas fa-check text-green-600"></i></td>
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
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Add New User</h3>
                <button onclick="closeAddUserModal()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Employee</label>
                    <select name="emp_id" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                        <option value="">Select Employee</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->emp_id }}">
                                {{ $employee->emp_name }} - {{ $employee->emp_position }}
                            </option>
                        @endforeach
                    </select>
                    @if(empty($employees) || $employees->isEmpty())
                        <p class="text-red-500 text-xs mt-1">No employees available. Please add employees first.</p>
                    @endif
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Username</label>
                    <input type="text" name="username" required 
                           class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                           placeholder="Enter username">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Password</label>
                        <input type="password" name="password" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required 
                               class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Role</label>
                    <select name="role" required class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                        <option value="">Select Role</option>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddUserModal()" 
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                        Create User
                    </button>
                </div>
            </form>
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
        window.location.href = "{{ url('admin/users') }}/" + userId + "/edit";
    }

    // Close modal when clicking outside
    document.getElementById('addUserModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddUserModal();
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddUserModal();
        }
    });
</script>
@endsection