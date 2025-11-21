@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Define access levels and operational capabilities for each staff role.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('createRoleModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-shield-alt mr-2"></i> Create New Role
            </button>
        </div>
    </div>

    {{-- 2. ROLE CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roleData as $role)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 {{ $role['color'] }} rounded-lg flex items-center justify-center text-white">
                        <i class="{{ $role['icon'] }} text-xl"></i>
                    </div>
                    <span class="{{ $role['color'] }} text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $role['category'] }}</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ $role['formatted_role'] }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $role['description'] }}</p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                        @foreach($role['users']->take(3) as $user)
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ $role['color'] }} ring-2 ring-white text-xs font-medium text-white">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                        @endforeach
                        @if($role['user_count'] > 3)
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 ring-2 ring-white text-xs font-medium text-gray-600">
                                +{{ $role['user_count'] - 3 }}
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500">{{ $role['user_count'] }} user{{ $role['user_count'] != 1 ? 's' : '' }} assigned</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition" onclick="viewRoleDetails('{{ $role['role'] }}')">
                        View Details
                    </button>
                    <button class="px-3 py-2 text-sm text-white rounded hover:opacity-90 transition {{ $role['color'] }}" onclick="openPermissionsModal('{{ $role['formatted_role'] }}', '{{ $role['role'] }}')">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="flex flex-col items-center">
                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Roles Found</h3>
                <p class="text-gray-500">Start by creating your first role to organize user permissions.</p>
            </div>
        </div>
        @endforelse
    </div>

</div>

<!-- CREATE ROLE MODAL -->
<div id="createRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('createRoleModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900">Create New Role</h3>
                <button onclick="document.getElementById('createRoleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="createRoleForm" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="role_name" class="block text-sm font-medium text-gray-700">Role Name</label>
                        <input type="text" id="role_name" name="role_name" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-chocolate focus:border-chocolate">
                        <p class="mt-1 text-xs text-gray-500">Enter a unique role identifier (e.g., 'manager', 'technician')</p>
                    </div>
                    
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
                        <input type="text" id="display_name" name="display_name" required 
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-chocolate focus:border-chocolate">
                        <p class="mt-1 text-xs text-gray-500">Human-readable role name (e.g., 'Department Manager')</p>
                    </div>
                    
                    <div>
                        <label for="role_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="role_description" name="description" rows="3" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-chocolate focus:border-chocolate"
                                  placeholder="Brief description of this role's responsibilities..."></textarea>
                    </div>
                    
                    <div>
                        <label for="role_category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select id="role_category" name="category" required
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-chocolate focus:border-chocolate">
                            <option value="">Select a category</option>
                            <option value="Management">Management</option>
                            <option value="Operations">Operations</option>
                            <option value="Staff">Staff</option>
                            <option value="System Owner">System Owner</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Initial Permissions</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="view_requisitions" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                                <span class="ml-2 text-sm text-gray-700">Can view requisitions</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="create_requisitions" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                                <span class="ml-2 text-sm text-gray-700">Can create requisitions</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="view_reports" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                                <span class="ml-2 text-sm text-gray-700">Can view reports</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="createNewRole()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Create Role
                </button>
                <button type="button" onclick="document.getElementById('createRoleModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ROLE DETAILS MODAL -->
<div id="roleDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRoleDetailsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900" id="roleDetailsTitle">Role Details</h3>
                <button onclick="closeRoleDetailsModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 max-h-[60vh] overflow-y-auto" id="roleDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- PERMISSIONS MATRIX MODAL -->
<div id="roleModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('roleModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">Edit Permissions: Supervisor</h3>
                <button onclick="document.getElementById('roleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 max-h-[60vh] overflow-y-auto">
                
                <!-- User Management Permission Group -->
                <div class="mb-6">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">User Management</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">View All Users</p>
                                <p class="text-xs text-gray-500">Can see list of all staff members</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="view_users" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Create/Edit Users</p>
                                <p class="text-xs text-gray-500">Can add new staff or change details</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="manage_users"> <!-- Unchecked for most roles -->
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Requisition Group -->
                <div class="mb-6">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">Inventory & Requisition</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Request Items (Requisition)</p>
                                <p class="text-xs text-gray-500">Can create requests for raw materials</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="create_requisitions" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Approve Requisitions</p>
                                <p class="text-xs text-gray-500">Can approve/reject requests from staff</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="approve_requisitions"> 
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Manage Inventory</p>
                                <p class="text-xs text-gray-500">Can view and adjust stock levels</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="manage_inventory">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- System & Reports Group -->
                <div class="mb-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">System & Reports</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">View Audit Logs</p>
                                <p class="text-xs text-gray-500">Can see who did what in the system</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="view_audit_logs" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Download Reports</p>
                                <p class="text-xs text-gray-500">Can generate and download system reports</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="download_reports">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">System Administration</p>
                                <p class="text-xs text-gray-500">Full system configuration access</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer permission-checkbox" data-permission="system_admin">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="savePermissions()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button type="button" onclick="document.getElementById('roleModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRole = null;
    let currentRoleName = '';

    function openPermissionsModal(roleName, roleKey) {
        currentRole = roleKey;
        currentRoleName = roleName;
        document.getElementById('modalTitle').innerText = 'Edit Permissions: ' + roleName;
        document.getElementById('roleModal').classList.remove('hidden');
        
        // Load permissions for this role from the server
        loadRolePermissions(roleKey);
    }

    function viewRoleDetails(roleKey) {
        // Fetch role details and users
        fetch(`/admin/roles/${roleKey}/details`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('roleDetailsTitle').innerText = `${data.formatted_role} Details`;
                document.getElementById('roleDetailsContent').innerHTML = generateRoleDetailsHTML(data);
                document.getElementById('roleDetailsModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading role details');
            });
    }

    function closeRoleDetailsModal() {
        document.getElementById('roleDetailsModal').classList.add('hidden');
    }

    function generateRoleDetailsHTML(data) {
        let html = `
            <div class="space-y-6">
                <div class="flex items-start space-x-4">
                    <div class="w-16 h-16 ${data.color} rounded-lg flex items-center justify-center text-white">
                        <i class="${data.icon} text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-gray-900">${data.formatted_role}</h4>
                        <p class="text-sm text-gray-500 mt-1">${data.description}</p>
                        <span class="inline-block mt-2 ${data.color} text-xs font-bold px-2.5 py-0.5 rounded-full">${data.category}</span>
                    </div>
                </div>

                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-3">Assigned Users (${data.user_count})</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        `;

        data.users.forEach(user => {
            html += `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="h-10 w-10 rounded-full ${data.color} flex items-center justify-center text-white font-bold">
                        ${user.name.charAt(0).toUpperCase() + (user.name.charAt(1) || '').toUpperCase()}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">${user.name}</p>
                        <p class="text-sm text-gray-500">${user.email}</p>
                        ${user.profile?.department ? `<p class="text-xs text-gray-400">${user.profile.department}</p>` : ''}
                    </div>
                </div>
            `;
        });

        html += `
                    </div>
                </div>
            </div>
        `;

        return html;
    }

    function loadRolePermissions(roleKey) {
        // Fetch permissions from the server
        fetch(`/admin/roles/${roleKey}/permissions`)
            .then(response => response.json())
            .then(data => {
                const permissions = data.permissions || [];
                
                // Set checkboxes based on fetched permissions
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    const permission = checkbox.dataset.permission;
                    checkbox.checked = permissions.includes(permission);
                });
            })
            .catch(error => {
                console.error('Error loading permissions:', error);
                // Fallback to default permissions if server call fails
                const rolePermissions = {
                    'admin': ['view_users', 'manage_users', 'create_requisitions', 'approve_requisitions', 'manage_inventory', 'view_audit_logs', 'download_reports', 'system_admin'],
                    'supervisor': ['view_users', 'create_requisitions', 'approve_requisitions', 'view_audit_logs', 'download_reports'],
                    'purchasing': ['view_users', 'create_requisitions', 'manage_inventory', 'download_reports'],
                    'inventory': ['view_users', 'create_requisitions', 'manage_inventory'],
                    'employee': ['create_requisitions']
                };

                const permissions = rolePermissions[roleKey] || [];
                
                // Set checkboxes based on fallback permissions
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    const permission = checkbox.dataset.permission;
                    checkbox.checked = permissions.includes(permission);
                });
            });
    }

    function savePermissions() {
        // Collect all permission checkboxes
        const permissions = [];
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                permissions.push(checkbox.dataset.permission);
            }
        });

        // Send to server
        fetch(`/admin/roles/${currentRole}/permissions`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ permissions })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Permissions updated successfully!');
                document.getElementById('roleModal').classList.add('hidden');
            } else {
                alert(data.message || 'Error updating permissions');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating permissions');
        });
    }

    function createNewRole() {
        const form = document.getElementById('createRoleForm');
        const formData = new FormData(form);
        
        // Validate form
        const roleName = formData.get('role_name');
        const displayName = formData.get('display_name');
        const description = formData.get('description');
        const category = formData.get('category');
        
        if (!roleName || !displayName || !description || !category) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Collect selected permissions
        const permissions = [];
        form.querySelectorAll('input[name="permissions[]"]:checked').forEach(checkbox => {
            permissions.push(checkbox.value);
        });
        
        // Prepare data for submission
        const data = {
            role_name: roleName,
            display_name: displayName,
            description: description,
            category: category,
            permissions: permissions
        };
        
        // Send to server
        fetch('/admin/roles/create', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.getElementById('createRoleModal').classList.add('hidden');
                // Reset form
                form.reset();
                // Refresh the page to show new role
                window.location.reload();
            } else {
                alert(data.message || 'Error creating role');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating role');
        });
    }
</script>

@endsection