@extends('Admin.layout.app')

@section('title', 'Employee Management - WellKenz ERP')

@section('breadcrumb', 'Employee Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Employee added successfully!
        </div>

        <div id="errorMessage" class="hidden bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3">
            Error processing request.
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Employee Management</h1>
                <p class="text-text-muted mt-2">Manage employee information and departments</p>
            </div>
            <button onclick="openAddEmployeeModal()"
                class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
                <i class="fas fa-user-plus mr-2"></i>
                Add Employee
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Employees</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalEmployees">12</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="activeEmployees">8</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Bakers</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="bakersCount">5</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">New This Month</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="newThisMonth">2</p>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">All Employees</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Sort Dropdown -->
                        <select onchange="sortTable(this.value)"
                            class="border-2 border-border-soft px-3 py-2 text-sm focus:outline-none focus:border-chocolate transition bg-white">
                            <option value="name_asc">Sort by: Name A-Z</option>
                            <option value="name_desc">Sort by: Name Z-A</option>
                            <option value="position">Sort by: Position</option>
                            <option value="department">Sort by: Department</option>
                            <option value="status">Sort by: Status</option>
                        </select>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search employees..." onkeyup="searchEmployees(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="employeesTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('name')">
                                Name <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('position')">
                                Position <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('department')">
                                Department <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('contact')">
                                Contact <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase cursor-pointer hover:bg-gray-100" onclick="sortTable('status')">
                                Status <i class="fas fa-sort ml-1 text-xs"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="employeesTableBody">
                        <!-- Sample Employee Data -->
                        <tr class="hover:bg-cream-bg transition employee-row" data-name="maria garcia" data-position="head baker" data-department="production" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Maria Garcia</p>
                                <p class="text-xs text-text-muted">maria.garcia@wellkenz.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold">
                                    Head Baker
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-text-dark">Production</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 123-4567</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditEmployeeModal(1)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                        Edit
                                    </button>
                                    <button onclick="openDeactivateModal(1, 'Maria Garcia')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition employee-row" data-name="john smith" data-position="pastry chef" data-department="production" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">John Smith</p>
                                <p class="text-xs text-text-muted">john.smith@wellkenz.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold">
                                    Pastry Chef
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-text-dark">Production</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 234-5678</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditEmployeeModal(2)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                        Edit
                                    </button>
                                    <button onclick="openDeactivateModal(2, 'John Smith')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition employee-row" data-name="robert johnson" data-position="purchasing officer" data-department="purchasing" data-status="active">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Robert Johnson</p>
                                <p class="text-xs text-text-muted">robert.johnson@wellkenz.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    Purchasing Officer
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-text-dark">Purchasing</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 345-6789</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditEmployeeModal(3)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                        Edit
                                    </button>
                                    <button onclick="openDeactivateModal(3, 'Robert Johnson')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Deactivate
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition employee-row" data-name="sarah wilson" data-position="inventory clerk" data-department="inventory" data-status="inactive">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Sarah Wilson</p>
                                <p class="text-xs text-text-muted">sarah.wilson@wellkenz.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">
                                    Inventory Clerk
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-text-dark">Inventory</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 456-7890</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">
                                    INACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditEmployeeModal(4)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                        Edit
                                    </button>
                                    <button onclick="openActivateModal(4, 'Sarah Wilson')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition">
                                        Activate
                                    </button>
                                    <button onclick="openDeleteModal(4, 'Sarah Wilson')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">4</span> of 4 employees</p>
            </div>
        </div>

        <!-- Department Distribution -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Department Distribution</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Production</span>
                        <span class="text-text-muted">2 (50%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-caramel h-2" style="width: 50%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Purchasing</span>
                        <span class="text-text-muted">1 (25%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-caramel h-2" style="width: 25%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-text-dark">Inventory</span>
                        <span class="text-text-muted">1 (25%)</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-caramel h-2" style="width: 25%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Add New Employee</h3>
                    <button onclick="closeAddEmployeeModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="addEmployeeForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Full Name</label>
                        <input type="text" name="emp_name" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Enter full name">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                            <select name="emp_position" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                                <option value="">Select Position</option>
                                <option value="Baker">Baker</option>
                                <option value="Pastry Chef">Pastry Chef</option>
                                <option value="Head Baker">Head Baker</option>
                                <option value="Purchasing Officer">Purchasing Officer</option>
                                <option value="Inventory Clerk">Inventory Clerk</option>
                                <option value="Supervisor / Owner">Supervisor / Owner</option>
                                <option value="Admin / IT staff">Admin / IT staff</option>
                                <option value="Sales Staff">Sales Staff</option>
                                <option value="Delivery Driver">Delivery Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                            <select name="dept_id" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                                <option value="">Select Department</option>
                                <option value="1">Production</option>
                                <option value="2">Purchasing</option>
                                <option value="3">Inventory</option>
                                <option value="4">Sales</option>
                                <option value="5">Administration</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                            <input type="email" name="emp_email" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="email@wellkenz.com">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Contact</label>
                            <input type="text" name="emp_contact" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="+1 (555) 123-4567">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeAddEmployeeModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Add Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Edit Employee</h3>
                    <button onclick="closeEditEmployeeModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="editEmployeeForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Full Name</label>
                        <input type="text" name="emp_name" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Enter full name" id="edit_emp_name">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                            <select name="emp_position" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                id="edit_emp_position">
                                <option value="">Select Position</option>
                                <option value="Baker">Baker</option>
                                <option value="Pastry Chef">Pastry Chef</option>
                                <option value="Head Baker">Head Baker</option>
                                <option value="Purchasing Officer">Purchasing Officer</option>
                                <option value="Inventory Clerk">Inventory Clerk</option>
                                <option value="Supervisor / Owner">Supervisor / Owner</option>
                                <option value="Admin / IT staff">Admin / IT staff</option>
                                <option value="Sales Staff">Sales Staff</option>
                                <option value="Delivery Driver">Delivery Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                            <select name="dept_id" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                id="edit_dept_id">
                                <option value="">Select Department</option>
                                <option value="1">Production</option>
                                <option value="2">Purchasing</option>
                                <option value="3">Inventory</option>
                                <option value="4">Sales</option>
                                <option value="5">Administration</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                            <input type="email" name="emp_email" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="email@wellkenz.com" id="edit_emp_email">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Contact</label>
                            <input type="text" name="emp_contact" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="+1 (555) 123-4567" id="edit_emp_contact">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditEmployeeModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deactivate Employee Modal -->
    <div id="deactivateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Deactivate Employee</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to deactivate <span id="deactivateEmployeeName" class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">The employee will lose system access but their record will be preserved.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeactivateModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="deactivateEmployee()" class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        Deactivate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Employee Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Delete Employee</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Are you sure you want to delete <span id="deleteEmployeeName" class="font-semibold"></span>?</p>
                <p class="text-sm text-text-muted mb-4">This action cannot be undone. All employee data will be permanently removed.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="deleteEmployee()" class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        Delete
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
        // Sample employee data for UI demonstration
        let employees = [
            {
                id: 1,
                name: "Maria Garcia",
                email: "maria.garcia@wellkenz.com",
                position: "Head Baker",
                department: "Production",
                contact: "+1 (555) 123-4567",
                status: "active"
            },
            {
                id: 2,
                name: "John Smith",
                email: "john.smith@wellkenz.com",
                position: "Pastry Chef",
                department: "Production",
                contact: "+1 (555) 234-5678",
                status: "active"
            },
            {
                id: 3,
                name: "Robert Johnson",
                email: "robert.johnson@wellkenz.com",
                position: "Purchasing Officer",
                department: "Purchasing",
                contact: "+1 (555) 345-6789",
                status: "active"
            },
            {
                id: 4,
                name: "Sarah Wilson",
                email: "sarah.wilson@wellkenz.com",
                position: "Inventory Clerk",
                department: "Inventory",
                contact: "+1 (555) 456-7890",
                status: "inactive"
            }
        ];

        let currentEmployeeId = null;
        let currentEmployeeName = null;

        // Modal Functions
        function openAddEmployeeModal() {
            document.getElementById('addEmployeeModal').classList.remove('hidden');
        }

        function closeAddEmployeeModal() {
            document.getElementById('addEmployeeModal').classList.add('hidden');
            document.getElementById('addEmployeeForm').reset();
        }

        function openEditEmployeeModal(employeeId) {
            const employee = employees.find(emp => emp.id === employeeId);
            if (employee) {
                document.getElementById('edit_emp_name').value = employee.name;
                document.getElementById('edit_emp_email').value = employee.email;
                document.getElementById('edit_emp_position').value = employee.position;
                document.getElementById('edit_emp_contact').value = employee.contact;
                
                // Set department based on department name
                const deptSelect = document.getElementById('edit_dept_id');
                for (let option of deptSelect.options) {
                    if (option.text === employee.department) {
                        option.selected = true;
                        break;
                    }
                }
                
                currentEmployeeId = employeeId;
                document.getElementById('editEmployeeModal').classList.remove('hidden');
            }
        }

        function closeEditEmployeeModal() {
            document.getElementById('editEmployeeModal').classList.add('hidden');
            currentEmployeeId = null;
        }

        function openDeactivateModal(employeeId, employeeName) {
            currentEmployeeId = employeeId;
            currentEmployeeName = employeeName;
            document.getElementById('deactivateEmployeeName').textContent = employeeName;
            document.getElementById('deactivateModal').classList.remove('hidden');
        }

        function closeDeactivateModal() {
            document.getElementById('deactivateModal').classList.add('hidden');
            currentEmployeeId = null;
            currentEmployeeName = null;
        }

        function openDeleteModal(employeeId, employeeName) {
            currentEmployeeId = employeeId;
            currentEmployeeName = employeeName;
            document.getElementById('deleteEmployeeName').textContent = employeeName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            currentEmployeeId = null;
            currentEmployeeName = null;
        }

        function openActivateModal(employeeId, employeeName) {
            if (confirm(`Activate ${employeeName}? This will create a system account for the employee.`)) {
                activateEmployee(employeeId);
            }
        }

        // Employee Actions
        function deactivateEmployee() {
            showMessage('Employee deactivated successfully!', 'success');
            closeDeactivateModal();
            // In a real app, you would update the UI to reflect the status change
        }

        function deleteEmployee() {
            showMessage('Employee deleted successfully!', 'success');
            closeDeleteModal();
            // In a real app, you would remove the employee from the table
        }

        function activateEmployee(employeeId) {
            showMessage('Employee activated successfully!', 'success');
            // In a real app, you would update the UI to reflect the status change
        }

        // Form Handling
        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Simulate adding employee
            showMessage('Employee added successfully!', 'success');
            closeAddEmployeeModal();
            this.reset();
        });

        document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate updating employee
            showMessage('Employee updated successfully!', 'success');
            closeEditEmployeeModal();
        });

        // Search functionality
        function searchEmployees(query) {
            const rows = document.querySelectorAll('.employee-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const position = row.getAttribute('data-position');
                const department = row.getAttribute('data-department');
                const searchText = (name + ' ' + position + ' ' + department).toLowerCase();
                
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
            const tbody = document.getElementById('employeesTableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const aValue = a.querySelector('td:first-child').textContent.toLowerCase();
                const bValue = b.querySelector('td:first-child').textContent.toLowerCase();
                
                if (criteria === 'name_asc' || criteria === 'name') {
                    return aValue.localeCompare(bValue);
                } else if (criteria === 'name_desc') {
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
        document.getElementById('addEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddEmployeeModal();
        });

        document.getElementById('editEmployeeModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditEmployeeModal();
        });

        document.getElementById('deactivateModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeactivateModal();
        });

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddEmployeeModal();
                closeEditEmployeeModal();
                closeDeactivateModal();
                closeDeleteModal();
            }
        });
    </script>
@endsection