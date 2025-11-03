@extends('Admin.layout.app')

@section('title', 'Requisition Overview - WellKenz ERP')

@section('breadcrumb', 'Requisition Overview')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Status updated successfully!
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Requisition Overview</h1>
                <p class="text-text-muted mt-2">View and manage all employee requisitions</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 bg-caramel text-white text-sm font-semibold rounded-full">
                    {{ $totalRequisitions ?? '24' }} Total
                </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="pendingCount">8</p>
                <p class="text-xs text-yellow-600 mt-1">Awaiting approval</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="approvedCount">12</p>
                <p class="text-xs text-green-600 mt-1">This month</p>
            </div>

            <div class="bg-white border-2 border-red-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Rejected</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="rejectedCount">4</p>
                <p class="text-xs text-red-600 mt-1">Requires review</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalValue">$4,280</p>
                <p class="text-xs text-text-muted mt-1">All requisitions</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center space-x-6">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Status</label>
                    <select onchange="filterRequisitions()" id="statusFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-40">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Department</label>
                    <select onchange="filterRequisitions()" id="departmentFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-48">
                        <option value="all">All Departments</option>
                        <option value="production">Production</option>
                        <option value="purchasing">Purchasing</option>
                        <option value="inventory">Inventory</option>
                        <option value="sales">Sales</option>
                        <option value="administration">Administration</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Priority</label>
                    <select onchange="filterRequisitions()" id="priorityFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-40">
                        <option value="all">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Date Range</label>
                    <select onchange="filterRequisitions()" id="dateFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-48">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>

                <!-- Reset Filters -->
                <div class="flex items-end">
                    <button onclick="resetFilters()"
                        class="px-4 py-2 border-2 border-border-soft hover:border-chocolate transition text-text-dark font-semibold">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Requisitions Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">All Requisitions</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search requisitions..." onkeyup="searchRequisitions(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="requisitionsTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Requester</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Approval Date</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="requisitionsTableBody">
                        <!-- Sample Requisition Data -->
                        <tr class="hover:bg-cream-bg transition requisition-row" 
                            data-status="pending" 
                            data-department="production" 
                            data-priority="high"
                            data-date="today">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">REQ-2024-0012</p>
                                <p class="text-xs text-text-muted">Baking Supplies</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Maria Garcia</p>
                                <p class="text-xs text-text-muted">Head Baker</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Weekly flour and ingredient restock</p>
                                <p class="text-xs text-text-muted">For bread production line</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    Production
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    HIGH
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    PENDING
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">10:30 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-muted">-</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRequisitionDetails(1)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        View
                                    </button>
                                    <button onclick="openApproveModal(1, 'REQ-2024-0012')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(1, 'REQ-2024-0012')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition requisition-row" 
                            data-status="approved" 
                            data-department="production" 
                            data-priority="medium"
                            data-date="yesterday">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">REQ-2024-0011</p>
                                <p class="text-xs text-text-muted">Baking Tools</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">John Smith</p>
                                <p class="text-xs text-text-muted">Pastry Chef</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">New pastry tools and equipment</p>
                                <p class="text-xs text-text-muted">For dessert section expansion</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    Production
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    MEDIUM
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    APPROVED
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">2:15 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">9:00 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRequisitionDetails(2)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition requisition-row" 
                            data-status="rejected" 
                            data-department="sales" 
                            data-priority="low"
                            data-date="2days">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">REQ-2024-0010</p>
                                <p class="text-xs text-text-muted">Marketing Materials</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Emily Chen</p>
                                <p class="text-xs text-text-muted">Sales Staff</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Promotional banners and flyers</p>
                                <p class="text-xs text-text-muted">Summer campaign launch</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    Sales
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    LOW
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    REJECTED
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2 days ago</p>
                                <p class="text-xs text-text-muted">11:45 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">4:30 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRequisitionDetails(3)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition requisition-row" 
                            data-status="approved" 
                            data-department="inventory" 
                            data-priority="high"
                            data-date="week">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">REQ-2024-0009</p>
                                <p class="text-xs text-text-muted">Storage Equipment</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Sarah Wilson</p>
                                <p class="text-xs text-text-muted">Inventory Clerk</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Additional shelving units</p>
                                <p class="text-xs text-text-muted">Warehouse organization project</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                    Inventory
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    HIGH
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    APPROVED
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">3 days ago</p>
                                <p class="text-xs text-text-muted">3:20 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2 days ago</p>
                                <p class="text-xs text-text-muted">10:15 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRequisitionDetails(4)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition requisition-row" 
                            data-status="pending" 
                            data-department="purchasing" 
                            data-priority="medium"
                            data-date="today">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">REQ-2024-0013</p>
                                <p class="text-xs text-text-muted">Office Supplies</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Robert Johnson</p>
                                <p class="text-xs text-text-muted">Purchasing Officer</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Monthly office stationery</p>
                                <p class="text-xs text-text-muted">General office use</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    Purchasing
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    MEDIUM
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    PENDING
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">8:45 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-muted">-</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewRequisitionDetails(5)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        View
                                    </button>
                                    <button onclick="openApproveModal(5, 'REQ-2024-0013')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(5, 'REQ-2024-0013')"
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">5</span> of 24 requisitions</p>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Approve Requisition</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-text-dark font-semibold" id="approveReqNumber"></p>
                        <p class="text-sm text-text-muted">Ready for approval</p>
                    </div>
                </div>
                <p class="text-text-dark mb-4">Are you sure you want to approve this requisition?</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Approval Notes (Optional)</label>
                        <textarea id="approveNotes" 
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate resize-none"
                            placeholder="Add any notes or instructions..."
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeApproveModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="approveRequisition()" class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition">
                        <i class="fas fa-check mr-2"></i>
                        Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Reject Requisition</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-text-dark font-semibold" id="rejectReqNumber"></p>
                        <p class="text-sm text-text-muted">Will be rejected</p>
                    </div>
                </div>
                <p class="text-text-dark mb-4">Are you sure you want to reject this requisition?</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                        <textarea id="rejectReason" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate resize-none"
                            placeholder="Please provide a reason for rejection..."
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeRejectModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Cancel
                    </button>
                    <button type="button" onclick="rejectRequisition()" class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        <i class="fas fa-times mr-2"></i>
                        Reject
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
        let currentRequisitionId = null;
        let currentReqNumber = null;

        // Filter Functions
        function filterRequisitions() {
            const statusFilter = document.getElementById('statusFilter').value;
            const departmentFilter = document.getElementById('departmentFilter').value;
            const priorityFilter = document.getElementById('priorityFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            const rows = document.querySelectorAll('.requisition-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const department = row.getAttribute('data-department');
                const priority = row.getAttribute('data-priority');
                const date = row.getAttribute('data-date');

                let statusMatch = statusFilter === 'all' || status === statusFilter;
                let departmentMatch = departmentFilter === 'all' || department === departmentFilter;
                let priorityMatch = priorityFilter === 'all' || priority === priorityFilter;
                let dateMatch = dateFilter === 'all' || shouldShowByDate(date, dateFilter);

                if (statusMatch && departmentMatch && priorityMatch && dateMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        function shouldShowByDate(date, filter) {
            switch(filter) {
                case 'today':
                    return date === 'today';
                case 'week':
                    return date === 'today' || date === 'yesterday' || date === '2days' || date === 'week';
                case 'month':
                    return true; // All sample data is within month
                default:
                    return true;
            }
        }

        function resetFilters() {
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('departmentFilter').value = 'all';
            document.getElementById('priorityFilter').value = 'all';
            document.getElementById('dateFilter').value = 'all';
            filterRequisitions();
            showMessage('Filters reset successfully!', 'success');
        }

        // Search functionality
        function searchRequisitions(query) {
            const rows = document.querySelectorAll('.requisition-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query.toLowerCase()) || query === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Modal Functions
        function openApproveModal(requisitionId, reqNumber) {
            currentRequisitionId = requisitionId;
            currentReqNumber = reqNumber;
            document.getElementById('approveReqNumber').textContent = reqNumber;
            document.getElementById('approveNotes').value = '';
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            currentRequisitionId = null;
            currentReqNumber = null;
        }

        function openRejectModal(requisitionId, reqNumber) {
            currentRequisitionId = requisitionId;
            currentReqNumber = reqNumber;
            document.getElementById('rejectReqNumber').textContent = reqNumber;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            currentRequisitionId = null;
            currentReqNumber = null;
        }

        // Requisition Actions
        function approveRequisition() {
            const notes = document.getElementById('approveNotes').value;
            showMessage(`Requisition ${currentReqNumber} approved successfully!`, 'success');
            closeApproveModal();
            updateStats('approve');
        }

        function rejectRequisition() {
            const reason = document.getElementById('rejectReason').value;
            if (!reason.trim()) {
                alert('Please provide a rejection reason.');
                return;
            }
            showMessage(`Requisition ${currentReqNumber} rejected.`, 'success');
            closeRejectModal();
            updateStats('reject');
        }

        function viewRequisitionDetails(requisitionId) {
            showMessage(`Viewing details for requisition ID: ${requisitionId}`, 'success');
            // In real app, would navigate to detail page or show modal
        }

        // Stats Update
        function updateStats(action) {
            const pendingCount = document.getElementById('pendingCount');
            const approvedCount = document.getElementById('approvedCount');
            const rejectedCount = document.getElementById('rejectedCount');

            let pending = parseInt(pendingCount.textContent);
            let approved = parseInt(approvedCount.textContent);
            let rejected = parseInt(rejectedCount.textContent);

            if (action === 'approve') {
                pendingCount.textContent = pending - 1;
                approvedCount.textContent = approved + 1;
            } else if (action === 'reject') {
                pendingCount.textContent = pending - 1;
                rejectedCount.textContent = rejected + 1;
            }
        }

        // Utility Functions
        function showMessage(message, type) {
            const messageDiv = document.getElementById('successMessage');
            messageDiv.textContent = message;
            messageDiv.classList.remove('hidden');
            
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }

        // Close modals when clicking outside
        document.getElementById('approveModal').addEventListener('click', function(e) {
            if (e.target === this) closeApproveModal();
        });

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeApproveModal();
                closeRejectModal();
            }
        });

        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            filterRequisitions();
        });
    </script>
@endsection