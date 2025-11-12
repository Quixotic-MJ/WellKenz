@extends('Admin.layout.app')

@section('content')
    <div class="space-y-6">

        <!-- toast messages -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- page header -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">User Account Management</h1>
                    <p class="text-sm text-gray-500 mt-1">Control all login accounts and roles across the system</p>
                </div>
                <button onclick="openCreateUserModal()"
                    class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </div>
        </div>

        <!-- live counts -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Accounts</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalUsers ?? $users->count() }}</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Active</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $activeUsers ?? 0 }}</p>
            </div>
            <div class="bg-white border border-purple-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Admins</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $adminsCount ?? 0 }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Inactive</p>
                <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $inactiveUsers ?? 0 }}</p>
            </div>
        </div>

        <!-- accounts table -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">All Accounts</h3>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search accounts…"
                            onkeyup="searchUsers(this.value)"
                            class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <button type="button" onclick="clearSearch()" id="clearSearchBtn"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="usersTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('username')">Username <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('name')">Name <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('position')">Position <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('role')">Role <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100"
                                onclick="sortTable('status')">Status <i class="fas fa-sort ml-1"></i></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="usersTableBody">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition user-row"
                                data-username="{{ strtolower($user->username) }}" data-name="{{ strtolower($user->name) }}"
                                data-position="{{ strtolower($user->position) }}" data-role="{{ strtolower($user->role) }}"
                                data-status="{{ strtolower($user->status) }}" data-email="{{ strtolower($user->email) }}">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $user->username }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->contact }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $user->position }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge =
                                            [
                                                'admin' => 'bg-purple-100 text-purple-700',
                                                'employee' => 'bg-gray-100 text-gray-700',
                                                'inventory' => 'bg-blue-100 text-blue-700',
                                                'purchasing' => 'bg-green-100 text-green-700',
                                                'supervisor' => 'bg-yellow-100 text-yellow-700',
                                            ][$user->role] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span
                                        class="inline-block px-2 py-1 {{ $badge }} text-xs font-semibold capitalize rounded">{{ $user->role }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->status === 'active')
                                        <span
                                            class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Active</span>
                                    @else
                                        <span
                                            class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button class="p-2 text-blue-600 hover:bg-blue-50 rounded transition edit-btn"
                                            title="Edit" data-user-id="{{ $user->user_id }}"><i
                                                class="fas fa-edit text-sm"></i></button>
                                        <button
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded transition change-password-btn"
                                            title="Reset Password" data-user-id="{{ $user->user_id }}"><i
                                                class="fas fa-key text-sm"></i></button>
                                        @if ($user->status === 'active')
                                            <button
                                                class="p-2 text-red-600 hover:bg-red-50 rounded transition deactivate-btn"
                                                title="Deactivate" data-user-id="{{ $user->user_id }}"><i
                                                    class="fas fa-user-slash text-sm"></i></button>
                                        @else
                                            <button
                                                class="p-2 text-green-600 hover:bg-green-50 rounded transition activate-btn"
                                                title="Activate" data-user-id="{{ $user->user_id }}"><i
                                                    class="fas fa-user-check text-sm"></i></button>
                                        @endif
                                        <button class="p-2 text-red-800 hover:bg-red-50 rounded transition delete-btn"
                                            title="Delete" data-user-id="{{ $user->user_id }}"><i
                                                class="fas fa-trash text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="visibleCount">{{ $users->count() }}</span> of {{ $users->count() }} accounts
            </div>
        </div>

        <!-- ===== MODALS  ===== -->
        @include('Admin.User.create')
        @include('Admin.User.edit')
        @include('Admin.User.password')
        @include('Admin.User.deactivate')
        @include('Admin.User.activate')
        @include('Admin.User.delete')

    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showSuccessMessage('{{ session('success') }}');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showErrorMessage('{{ session('error') }}');
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showErrorMessage('{{ $errors->first() }}');
            });
        </script>
    @endif

    <script>
        /* ==========================================================================
       LIGHTWEIGHT JS HELPERS (search, sort, modal open/close)
       ========================================================================== */
        let currentUserId = null;

        /* ---- search ---- */
        function searchUsers(q) {
            const term = q.toLowerCase().trim();
            const rows = document.querySelectorAll('.user-row');
            let visible = 0;
            rows.forEach(tr => {
                const ok = tr.dataset.username.includes(term) ||
                    tr.dataset.name.includes(term) ||
                    tr.dataset.position.includes(term) ||
                    tr.dataset.role.includes(term) ||
                    tr.dataset.status.includes(term) ||
                    tr.dataset.email.includes(term);
                tr.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            document.getElementById('visibleCount').textContent = visible;
            const clear = document.getElementById('clearSearchBtn');
            term ? clear.classList.remove('hidden') : clear.classList.add('hidden');
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchUsers('');
        }

        /* ---- sort ---- */
        let sortField = 'username',
            sortDir = 'asc';

        function sortTable(field) {
            if (sortField === field) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            else {
                sortField = field;
                sortDir = 'asc';
            }

            const tbody = document.getElementById('usersTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.style.display !== 'none');
            rows.sort((a, b) => {
                const A = a.dataset[field].toLowerCase();
                const B = b.dataset[field].toLowerCase();
                return sortDir === 'asc' ? A.localeCompare(B) : B.localeCompare(A);
            });
            rows.forEach(r => tbody.appendChild(r));
            /* update tiny arrow */
            document.querySelectorAll('thead th i').forEach(i => i.className = 'fas fa-sort ml-1 text-xs');
            const th = document.querySelector(`th[onclick="sortTable('${field}')"] i`);
            if (th) th.className = sortDir === 'asc' ? 'fas fa-sort-up ml-1 text-xs' : 'fas fa-sort-down ml-1 text-xs';
        }

        /* ---- modal helpers ---- */
        const modals = ['createUserModal', 'editUserModal', 'passwordModal', 'deactivateModal', 'activateModal',
            'deleteModal'
        ];

        function closeModals() {
            modals.forEach(id => document.getElementById(id).classList.add('hidden'));
            currentUserId = null;
        }

        function openCreateUserModal() {
            closeModals();
            document.getElementById('createUserModal').classList.remove('hidden');
        }

        function openEditUserModal(id) {
            closeModals();
            currentUserId = id;
            fetch(`/admin/users/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(user => {
                    document.getElementById('edit_user_id').value = user.user_id;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_name').value = user.name;
                    document.getElementById('edit_position').value = user.position;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_contact').value = user.contact;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('editUserForm').action = `/admin/users/${id}`;
                    document.getElementById('editUserModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Failed to load user data.');
                });
        }

        function openPasswordModal(id) {
            closeModals();
            currentUserId = id;
            fetch(`/admin/users/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(user => {
                    document.getElementById('changePasswordUsername').textContent = user.username;
                    document.getElementById('change_password_user_id').value = user.user_id;
                    document.getElementById('changePasswordForm').action = `/admin/users/${id}/password`;
                    document.getElementById('passwordModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Failed to load user data.');
                });
        }

        function openDeactivateModal(id) {
            closeModals();
            currentUserId = id;
            fetch(`/admin/users/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(user => {
                    document.getElementById('deactivateUserName').textContent = user.name;
                    document.getElementById('deactivateModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Failed to load user data.');
                });
        }

        function openActivateModal(id) {
            closeModals();
            currentUserId = id;
            fetch(`/admin/users/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(user => {
                    document.getElementById('activateUserName').textContent = user.name;
                    document.getElementById('activateModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Failed to load user data.');
                });
        }

        function openDeleteModal(id) {
            closeModals();
            currentUserId = id;
            fetch(`/admin/users/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(user => {
                    document.getElementById('deleteUserName').textContent = user.name;
                    document.getElementById('deleteModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Failed to load user data.');
                });
        }

        /* ---- allow click-out & ESC ---- */
        modals.forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('click', e => {
                if (e.target === el) closeModals();
            });
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModals();
        });

        /* ---- message helpers ---- */
        function showSuccessMessage(message) {
            const el = document.getElementById('successMessage');
            el.textContent = message;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 5000);
        }

        function showErrorMessage(message) {
            const el = document.getElementById('errorMessage');
            el.textContent = message;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 5000);
        }

        /* ---- action functions ---- */
        function toggleUserStatus() {
            fetch(`/admin/users/${currentUserId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message);
                        closeModals();
                        location.reload();
                    } else {
                        showErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('An error occurred.');
                });
        }

        function deleteUser() {
            fetch(`/admin/users/${currentUserId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessMessage(data.message);
                        closeModals();
                        location.reload();
                    } else {
                        showErrorMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('An error occurred.');
                });
        }

        /* ---- attach button event listeners ---- */
        document.addEventListener('DOMContentLoaded', function() {
            // Edit buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openEditUserModal(userId);
                });
            });

            // Change password buttons
            document.querySelectorAll('.change-password-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openPasswordModal(userId);
                });
            });

            // Deactivate buttons
            document.querySelectorAll('.deactivate-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openDeactivateModal(userId);
                });
            });

            // Activate buttons
            document.querySelectorAll('.activate-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openActivateModal(userId);
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openDeleteModal(userId);
                });
            });

            // Form listeners
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message);
                            closeModals();
                            location.reload();
                        } else {
                            showErrorMessage(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred.');
                    });
            });
            document.getElementById('createUserForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message);
                            closeModals();
                            location.reload();
                        } else {
                            showErrorMessage(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('An error occurred.');
                    });
            });
        });
    </script>
@endsection
