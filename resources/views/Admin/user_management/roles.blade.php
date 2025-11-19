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
            <button onclick="document.getElementById('roleModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-shield-alt mr-2"></i> Create New Role
            </button>
        </div>
    </div>

    {{-- 2. ROLE CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Role 1: Administrator --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                        <i class="fas fa-user-astronaut text-xl"></i>
                    </div>
                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-2.5 py-0.5 rounded-full">System Owner</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Administrator</h3>
                <p class="text-sm text-gray-500 mt-1">Full access to all system configurations, user management, and financial reports.</p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-500 ring-2 ring-white text-xs font-medium text-white">AD</span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-400 ring-2 ring-white text-xs font-medium text-white">JD</span>
                    </div>
                    <span class="text-xs text-gray-500">2 Users assigned</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition">
                        View Details
                    </button>
                    <button class="px-3 py-2 text-sm text-white bg-purple-600 rounded hover:bg-purple-700 transition" onclick="openPermissionsModal('Administrator')">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Role 2: Supervisor --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Management</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Supervisor</h3>
                <p class="text-sm text-gray-500 mt-1">Can approve requisitions, view audit logs, and manage inventory adjustments.</p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-500 ring-2 ring-white text-xs font-medium text-white">MJ</span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-400 ring-2 ring-white text-xs font-medium text-white">AK</span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 ring-2 ring-white text-xs font-medium text-gray-600">+1</span>
                    </div>
                    <span class="text-xs text-gray-500">3 Users assigned</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition">
                        View Details
                    </button>
                    <button class="px-3 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700 transition" onclick="openPermissionsModal('Supervisor')">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Role 3: Head Baker --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600">
                        <i class="fas fa-bread-slice text-xl"></i>
                    </div>
                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Operational</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Head Baker</h3>
                <p class="text-sm text-gray-500 mt-1">Restricted to Item Requests, Recipe Viewing, and Production Modules.</p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-500 ring-2 ring-white text-xs font-medium text-white">CK</span>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-400 ring-2 ring-white text-xs font-medium text-white">DL</span>
                    </div>
                    <span class="text-xs text-gray-500">5 Users assigned</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition">
                        View Details
                    </button>
                    <button class="px-3 py-2 text-sm text-white bg-amber-600 rounded hover:bg-amber-700 transition" onclick="openPermissionsModal('Head Baker')">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Role 4: Sales Staff --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <i class="fas fa-cash-register text-xl"></i>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Frontline</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Sales Staff</h3>
                <p class="text-sm text-gray-500 mt-1">Limited access to POS, Order Entry, and Customer Directory only.</p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex -space-x-2">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-500 ring-2 ring-white text-xs font-medium text-white">SL</span>
                    </div>
                    <span class="text-xs text-gray-500">8 Users assigned</span>
                </div>
                <div class="mt-4 flex gap-2">
                    <button class="flex-1 px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition">
                        View Details
                    </button>
                    <button class="px-3 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700 transition" onclick="openPermissionsModal('Sales Staff')">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
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
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Create/Edit Users</p>
                                <p class="text-xs text-gray-500">Can add new staff or change details</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer"> <!-- Unchecked for Supervisor -->
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Requisition Group (Critical for Supervisor vs Baker) -->
                <div class="mb-6">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">Inventory & Requisition</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Request Items (Requisition)</p>
                                <p class="text-xs text-gray-500">Can create requests for raw materials</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Approve Requisitions</p>
                                <p class="text-xs text-gray-500">Can approve/reject requests from Bakers</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked> 
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Manage Suppliers</p>
                                <p class="text-xs text-gray-500">Can edit supplier list and prices</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- System Group -->
                <div class="mb-2">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">System & Reports</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">View Audit Logs</p>
                                <p class="text-xs text-gray-500">Can see who did what in the system</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Download Backups</p>
                                <p class="text-xs text-gray-500">Full database export access</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-chocolate"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
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
    function openPermissionsModal(roleName) {
        document.getElementById('modalTitle').innerText = 'Edit Permissions: ' + roleName;
        document.getElementById('roleModal').classList.remove('hidden');
        
        // In a real app, you would fetch and check/uncheck boxes via AJAX here
        // For this demo, we'll just show the modal with default Supervisor-like settings
    }
</script>

@endsection