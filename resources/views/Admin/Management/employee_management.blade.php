@extends('Admin.layout.app')

@section('title', 'Employee Management - WellKenz ERP')

@section('breadcrumb', 'Employee Management')

@section('content')
    <div class="space-y-6">
        <!-- Main Content -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Employees Table -->
            <div class="xl:col-span-3">
                <div class="bg-white border-2 border-border-soft rounded-lg">
                    <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <h3 class="font-display text-xl font-bold text-text-dark">All Employees
                                ({{ $employees->count() }})</h3>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <select
                                    class="border border-border-soft rounded px-3 py-2 text-sm focus:outline-none focus:border-chocolate transition bg-white w-full sm:w-auto"
                                    id="sortSelect">
                                    <option value="name_asc">Name A-Z</option>
                                    <option value="name_desc">Name Z-A</option>
                                    <option value="position">Position</option>
                                    <option value="status">Status</option>
                                </select>

                                <div class="relative w-full sm:w-64">
                                    <input type="text" placeholder="Search employees..."
                                        class="pl-9 pr-4 py-2 border border-border-soft rounded text-sm focus:outline-none focus:border-chocolate transition w-full"
                                        id="searchInput">
                                    <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-border-soft">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100 sort-header"
                                        data-sort="name">
                                        Employee
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100 sort-header"
                                        data-sort="position">
                                        Position
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100 sort-header"
                                        data-sort="contact">
                                        Contact
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100 sort-header"
                                        data-sort="status">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase w-32">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft" id="employeesTable">
                                @forelse($employees as $employee)
                                    <tr class="employee-row hover:bg-cream-bg transition-colors"
                                        data-name="{{ strtolower($employee->emp_name) }}"
                                        data-position="{{ strtolower($employee->emp_position) }}"
                                        data-status="{{ $employee->emp_status }}"
                                        data-employee-id="{{ $employee->emp_id }}">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0 mr-3">
                                                    <span
                                                        class="text-white text-sm font-bold">{{ $employee->initials }}</span>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-text-dark truncate">
                                                        {{ $employee->emp_name }}</p>
                                                    <p class="text-xs text-text-muted truncate">{{ $employee->emp_email }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-text-dark">{{ $employee->emp_position }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-text-dark">{{ $employee->emp_contact }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border {{ $employee->emp_status === 'active' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200' }}">
                                                {{ $employee->emp_status === 'active' ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    class="p-2 text-caramel hover:bg-caramel hover:text-white rounded transition-colors edit-btn"
                                                    title="Edit" data-employee-id="{{ $employee->emp_id }}"
                                                    data-employee-name="{{ $employee->emp_name }}">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button
                                                    class="p-2 text-chocolate hover:bg-chocolate hover:text-white rounded transition-colors view-btn"
                                                    title="View" data-employee-id="{{ $employee->emp_id }}">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <button
                                                    class="p-2 text-red-500 hover:bg-red-500 hover:text-white rounded transition-colors delete-btn"
                                                    title="Delete" data-employee-id="{{ $employee->emp_id }}"
                                                    data-employee-name="{{ $employee->emp_name }}">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                                            <i class="fas fa-users text-4xl mb-3 opacity-50"></i>
                                            <p>No employees found. Click "Add New Employee" to get started.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-border-soft bg-cream-bg">
                        <p class="text-sm text-text-muted">Showing <span id="visibleCount">{{ $employees->count() }}</span>
                            of {{ $employees->count() }} employees</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                    <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
                    <div class="space-y-3">
                        <button
                            class="w-full p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded-lg hover-lift flex items-center justify-center"
                            id="addEmployeeBtn">
                            <i class="fas fa-user-plus mr-2"></i>
                            Add Employee
                        </button>
                        <button
                            class="w-full p-3 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift flex items-center justify-center"
                            id="generateReportBtn">
                            <i class="fas fa-chart-bar mr-2 text-chocolate"></i>
                            Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Hires -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                    <i class="fas fa-user-clock text-caramel mr-2"></i>
                    Recent Hires
                </h3>
                <div class="space-y-4">
                    @php
                        $recentHires = $employees->sortByDesc('created_at')->take(5);
                    @endphp
                    @forelse($recentHires as $employee)
                        <div class="flex items-center justify-between p-3 bg-cream-bg rounded-lg">
                            <div class="flex items-center min-w-0">
                                <div
                                    class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0 mr-3">
                                    <span class="text-white text-sm font-bold">{{ $employee->initials }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-text-dark truncate">{{ $employee->emp_name }}</p>
                                    <p class="text-xs text-text-muted truncate">{{ $employee->emp_position }}</p>
                                </div>
                            </div>
                            <span
                                class="text-xs text-text-muted whitespace-nowrap ml-2">{{ $employee->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-text-muted">
                            <i class="fas fa-user-plus text-4xl mb-3 opacity-50"></i>
                            <p>No recent hires</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Employee Status Overview -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                    <i class="fas fa-chart-pie text-chocolate mr-2"></i>
                    Employee Status Overview
                </h3>
                <div class="space-y-4">
                    @php
                        $activeEmployees = $employees->where('emp_status', 'active')->count();
                        $inactiveEmployees = $employees->where('emp_status', 'inactive')->count();
                        $bakersCount = $employees->where('emp_position', 'Baker')->count();
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-text-dark">Active Employees</span>
                        </div>
                        <span class="text-lg font-bold text-text-dark">{{ $activeEmployees }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-times text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-text-dark">Inactive Employees</span>
                        </div>
                        <span class="text-lg font-bold text-text-dark">{{ $inactiveEmployees }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-bread-slice text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-text-dark">Bakers</span>
                        </div>
                        <span class="text-lg font-bold text-text-dark">{{ $bakersCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Add New Employee</h3>
                    <button class="text-text-muted hover:text-text-dark transition-colors close-add-modal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="addEmployeeForm" method="POST" action="{{ route('employees.store') }}">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="emp_name" class="block text-sm font-medium text-text-dark mb-2">Full Name
                                *</label>
                            <input type="text" id="emp_name" name="emp_name" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                        <div>
                            <label for="emp_position" class="block text-sm font-medium text-text-dark mb-2">Position
                                *</label>
                            <select id="emp_position" name="emp_position" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                                <option value="">Select Position</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position }}">{{ $position }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="emp_email" class="block text-sm font-medium text-text-dark mb-2">Email *</label>
                            <input type="email" id="emp_email" name="emp_email" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                        <div>
                            <label for="emp_contact" class="block text-sm font-medium text-text-dark mb-2">Contact Number
                                *</label>
                            <input type="text" id="emp_contact" name="emp_contact" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t-2 border-border-soft bg-cream-bg rounded-b-lg flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border-2 border-border-soft text-text-dark hover:bg-gray-50 rounded transition-colors close-add-modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark rounded transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Edit Employee</h3>
                    <button class="text-text-muted hover:text-text-dark transition-colors close-edit-modal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="editEmployeeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_emp_name" class="block text-sm font-medium text-text-dark mb-2">Full Name
                                *</label>
                            <input type="text" id="edit_emp_name" name="emp_name" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                        <div>
                            <label for="edit_emp_position" class="block text-sm font-medium text-text-dark mb-2">Position
                                *</label>
                            <select id="edit_emp_position" name="emp_position" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                                <option value="">Select Position</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position }}">{{ $position }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit_emp_email" class="block text-sm font-medium text-text-dark mb-2">Email
                                *</label>
                            <input type="email" id="edit_emp_email" name="emp_email" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                        <div>
                            <label for="edit_emp_contact" class="block text-sm font-medium text-text-dark mb-2">Contact
                                Number *</label>
                            <input type="text" id="edit_emp_contact" name="emp_contact" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                        </div>
                        <div>
                            <label for="edit_emp_status" class="block text-sm font-medium text-text-dark mb-2">Status
                                *</label>
                            <select id="edit_emp_status" name="emp_status" required
                                class="w-full px-3 py-2 border border-border-soft rounded focus:outline-none focus:border-chocolate transition">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t-2 border-border-soft bg-cream-bg rounded-b-lg flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border-2 border-border-soft text-text-dark hover:bg-gray-50 rounded transition-colors close-edit-modal">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark rounded transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div id="viewEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Employee Details</h3>
                    <button class="text-text-muted hover:text-text-dark transition-colors close-modal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6" id="employeeDetailsContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="p-6 border-t-2 border-border-soft bg-cream-bg rounded-b-lg flex justify-end space-x-3">
                <button
                    class="px-4 py-2 border-2 border-border-soft text-text-dark hover:bg-gray-50 rounded transition-colors close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteEmployeeModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-md mx-4">
            <div class="p-6 border-b-2 border-border-soft bg-red-50 rounded-t-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-text-dark">Confirm Deletion</h3>
                </div>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to delete <span id="deleteEmployeeName"
                        class="font-semibold"></span>? This action cannot be undone.</p>
                <p class="text-sm text-text-muted">This will permanently remove the employee record.</p>
            </div>
            <div class="p-6 border-t-2 border-border-soft bg-cream-bg rounded-b-lg flex justify-end space-x-3">
                <button
                    class="px-4 py-2 border-2 border-border-soft text-text-dark hover:bg-gray-50 rounded transition-colors close-delete-modal">
                    Cancel
                </button>
                <button class="px-4 py-2 bg-red-500 text-white hover:bg-red-600 rounded transition-colors"
                    id="confirmDeleteBtn">
                    <i class="fas fa-trash mr-2"></i>Delete Employee
                </button>
            </div>
        </div>
    </div>

    <script>
        // Debug function
        function debugEmployeeRequest(url, employeeId) {
            console.log('Making request to:', url);
            console.log('Employee ID:', employeeId);
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.employee-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const position = row.getAttribute('data-position');

                const matches = name.includes(searchTerm) || position.includes(searchTerm);

                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        });

        // Sort functionality
        document.querySelectorAll('.sort-header').forEach(header => {
            header.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                sortTable(sortBy);
            });
        });

        function sortTable(sortBy) {
            const tbody = document.getElementById('employeesTable');
            const rows = Array.from(tbody.querySelectorAll('.employee-row'));

            rows.sort((a, b) => {
                let aValue, bValue;

                switch (sortBy) {
                    case 'name':
                        aValue = a.getAttribute('data-name');
                        bValue = b.getAttribute('data-name');
                        break;
                    case 'position':
                        aValue = a.getAttribute('data-position');
                        bValue = b.getAttribute('data-position');
                        break;
                    case 'status':
                        aValue = a.getAttribute('data-status');
                        bValue = b.getAttribute('data-status');
                        break;
                    default:
                        aValue = a.getAttribute('data-name');
                        bValue = b.getAttribute('data-name');
                }

                return aValue.localeCompare(bValue);
            });

            // Clear and re-append sorted rows
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            rows.forEach(row => tbody.appendChild(row));
        }

        // Add Employee Modal
        document.getElementById('addEmployeeBtn').addEventListener('click', function() {
            document.getElementById('addEmployeeModal').classList.remove('hidden');
        });

        // Close Add Modal
        document.querySelectorAll('.close-add-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('addEmployeeModal').classList.add('hidden');
            });
        });

        // Close Edit Modal
        document.querySelectorAll('.close-edit-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('editEmployeeModal').classList.add('hidden');
            });
        });

        // View Employee Functionality
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-employee-id');
                console.log('View button clicked for employee ID:', employeeId);
                showEmployeeDetails(employeeId);
            });
        });

        // Edit Employee Functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-employee-id');
                console.log('Edit button clicked for employee ID:', employeeId);
                editEmployee(employeeId);
            });
        });

        // Delete Employee Functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-employee-id');
                const employeeName = this.getAttribute('data-employee-name');
                console.log('Delete button clicked for employee ID:', employeeId);
                confirmDelete(employeeId, employeeName);
            });
        });

        // Quick Actions
        document.getElementById('generateReportBtn').addEventListener('click', function() {
            alert('Generate Report functionality will be implemented soon!');
        });

        // Modal functionality
        function showEmployeeDetails(employeeId) {
            const url = `/employees/${employeeId}`;
            debugEmployeeRequest(url, employeeId);

            fetch(url)
                .then(response => {
                    console.log('View Response status:', response.status);
                    console.log('View Response ok:', response.ok);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('View Data received:', data);
                    const employee = data.employee;
                    const content = `
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-20 h-20 bg-caramel flex items-center justify-center rounded-full">
                                <span class="text-white text-2xl font-bold">${employee.initials || 'NA'}</span>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-text-dark">${employee.emp_name}</h4>
                                <p class="text-text-muted">${employee.emp_position}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h5 class="font-semibold text-text-dark mb-3">Personal Information</h5>
                                <div class="space-y-2">
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Email</label>
                                        <p class="text-text-dark">${employee.emp_email}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Phone</label>
                                        <p class="text-text-dark">${employee.emp_contact}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Status</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${employee.emp_status === 'active' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'}">
                                            ${employee.emp_status === 'active' ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h5 class="font-semibold text-text-dark mb-3">Employment Details</h5>
                                <div class="space-y-2">
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Employee ID</label>
                                        <p class="text-text-dark">EMP-${employee.emp_id.toString().padStart(4, '0')}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Hire Date</label>
                                        <p class="text-text-dark">${new Date(employee.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-text-muted uppercase tracking-wider">Position</label>
                                        <p class="text-text-dark">${employee.emp_position}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                    document.getElementById('employeeDetailsContent').innerHTML = content;
                    document.getElementById('viewEmployeeModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching employee details:', error);
                    alert('Error loading employee details: ' + error.message);
                });
        }

        function editEmployee(employeeId) {
            const url = `/employees/${employeeId}/edit`;
            debugEmployeeRequest(url, employeeId);

            fetch(url)
                .then(response => {
                    console.log('Edit Response status:', response.status);
                    console.log('Edit Response ok:', response.ok);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Edit Data received:', data);
                    if (data.success) {
                        const employee = data.employee;

                        // Populate the edit form
                        document.getElementById('edit_emp_name').value = employee.emp_name;
                        document.getElementById('edit_emp_email').value = employee.emp_email;
                        document.getElementById('edit_emp_contact').value = employee.emp_contact;
                        document.getElementById('edit_emp_position').value = employee.emp_position;
                        document.getElementById('edit_emp_status').value = employee.emp_status;

                        // Set the form action
                        document.getElementById('editEmployeeForm').action = `/employees/${employeeId}`;

                        // Show the edit modal
                        document.getElementById('editEmployeeModal').classList.remove('hidden');
                    } else {
                        alert('Error loading employee data: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching employee details:', error);
                    alert('Error loading employee details: ' + error.message);
                });
        }

        function confirmDelete(employeeId, employeeName) {
            document.getElementById('deleteEmployeeName').textContent = employeeName;
            document.getElementById('deleteEmployeeModal').classList.remove('hidden');

            // Set up the confirm delete button
            document.getElementById('confirmDeleteBtn').onclick = function() {
                deleteEmployee(employeeId, employeeName);
            };
        }

        function deleteEmployee(employeeId, employeeName) {
            const url = `/employees/${employeeId}`;
            debugEmployeeRequest(url, employeeId);

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    console.log('Delete Response status:', response.status);
                    console.log('Delete Response ok:', response.ok);
                    return response.json();
                })
                .then(data => {
                    console.log('Delete Data received:', data);
                    if (data.success) {
                        alert(`Employee ${employeeName} has been deleted successfully!`);
                        location.reload();
                    } else {
                        alert('Error deleting employee: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting employee:', error);
                    alert('Error deleting employee');
                })
                .finally(() => {
                    document.getElementById('deleteEmployeeModal').classList.add('hidden');
                });
        }

        // Close modals
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('viewEmployeeModal').classList.add('hidden');
            });
        });

        document.querySelectorAll('.close-delete-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('deleteEmployeeModal').classList.add('hidden');
            });
        });

        // Close modals when clicking outside
        document.getElementById('viewEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('deleteEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('editEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('viewEmployeeModal').classList.add('hidden');
                document.getElementById('deleteEmployeeModal').classList.add('hidden');
                document.getElementById('addEmployeeModal').classList.add('hidden');
                document.getElementById('editEmployeeModal').classList.add('hidden');
            }
        });

        // Add Employee Form Submission
        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        document.getElementById('addEmployeeModal').classList.add('hidden');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating employee');
                });
        });

        // Edit Employee Form Submission
        document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': 'PUT'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        document.getElementById('editEmployeeModal').classList.add('hidden');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating employee');
                });
        });
    </script>

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

        .bg-chocolate-dark {
            background-color: #2a1c10;
        }

        .border-border-soft {
            border-color: #e8dfd4;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .min-w-0 {
            min-width: 0;
        }

        .employee-row:hover {
            background-color: #faf7f3;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        #viewEmployeeModal,
        #deleteEmployeeModal,
        #addEmployeeModal,
        #editEmployeeModal {
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
@endsection
