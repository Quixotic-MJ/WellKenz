@extends('Admin.layout.app')

@section('title', 'Review Requests - WellKenz ERP')

@section('breadcrumb', 'Review Requests')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Request approved successfully!
        </div>

        <div id="errorMessage" class="hidden bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3">
            Error processing request.
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Review Item Requests</h1>
                <p class="text-text-muted mt-2">Approve or reject pending item requests from employees</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 bg-caramel text-white text-sm font-semibold rounded-full">
                    {{ $pendingCount ?? '5' }} Pending
                </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Review</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="pendingCount">5</p>
            </div>

            <div class="bg-white border-2 border-yellow-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">This Week</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="thisWeekCount">8</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approval Rate</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="approvalRate">72%</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Avg. Response Time</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="avgResponseTime">4h</p>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">Pending Item Requests</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Filter Dropdown -->
                        <select onchange="filterRequests(this.value)"
                            class="border-2 border-border-soft px-3 py-2 text-sm focus:outline-none focus:border-chocolate transition bg-white">
                            <option value="all">All Requests</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search requests..." onkeyup="searchRequests(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="requestsTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Requester</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="requestsTableBody">
                        <!-- Sample Request Data -->
                        <tr class="hover:bg-cream-bg transition request-row" data-item="flour" data-requester="maria" data-priority="high" data-date="today">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-wheat-alt text-orange-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                                        <p class="text-xs text-text-muted">Baking Supplies</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">High-quality flour for bread production</p>
                                <p class="text-xs text-text-muted">Required for weekly batch</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                                    25 kg
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Maria Garcia</p>
                                <p class="text-xs text-text-muted">Head Baker</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">10:30 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    URGENT
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openApproveModal(1, 'All-Purpose Flour', 'Maria Garcia')"
                                        class="px-4 py-2 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded-lg">
                                        <i class="fas fa-check mr-1"></i>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(1, 'All-Purpose Flour', 'Maria Garcia')"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded-lg">
                                        <i class="fas fa-times mr-1"></i>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition request-row" data-item="chocolate" data-requester="john" data-priority="medium" data-date="today">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-brown-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cookie text-brown-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                                        <p class="text-xs text-text-muted">Baking Ingredients</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Semi-sweet chocolate chips for cookies</p>
                                <p class="text-xs text-text-muted">New cookie recipe development</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                                    5 kg
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">John Smith</p>
                                <p class="text-xs text-text-muted">Pastry Chef</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">09:15 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    MEDIUM
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openApproveModal(2, 'Chocolate Chips', 'John Smith')"
                                        class="px-4 py-2 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded-lg">
                                        <i class="fas fa-check mr-1"></i>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(2, 'Chocolate Chips', 'John Smith')"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded-lg">
                                        <i class="fas fa-times mr-1"></i>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition request-row" data-item="packaging" data-requester="emily" data-priority="low" data-date="yesterday">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Packaging Boxes</p>
                                        <p class="text-xs text-text-muted">Retail Packaging</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Custom branded boxes for retail products</p>
                                <p class="text-xs text-text-muted">Size: 8x8x4 inches</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                                    200 units
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Emily Chen</p>
                                <p class="text-xs text-text-muted">Sales Staff</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">3:45 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    LOW
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openApproveModal(3, 'Packaging Boxes', 'Emily Chen')"
                                        class="px-4 py-2 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded-lg">
                                        <i class="fas fa-check mr-1"></i>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(3, 'Packaging Boxes', 'Emily Chen')"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded-lg">
                                        <i class="fas fa-times mr-1"></i>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition request-row" data-item="vanilla" data-requester="david" data-priority="high" data-date="yesterday">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-mortar-pestle text-purple-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Vanilla Extract</p>
                                        <p class="text-xs text-text-muted">Flavoring</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Pure Madagascar vanilla extract</p>
                                <p class="text-xs text-text-muted">For premium cake line</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                                    2 liters
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">David Brown</p>
                                <p class="text-xs text-text-muted">Baker</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">1:20 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    URGENT
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openApproveModal(4, 'Vanilla Extract', 'David Brown')"
                                        class="px-4 py-2 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded-lg">
                                        <i class="fas fa-check mr-1"></i>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(4, 'Vanilla Extract', 'David Brown')"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded-lg">
                                        <i class="fas fa-times mr-1"></i>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition request-row" data-item="butter" data-requester="robert" data-priority="medium" data-date="2days">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cow text-yellow-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Unsalted Butter</p>
                                        <p class="text-xs text-text-muted">Dairy Products</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Premium unsalted butter for pastries</p>
                                <p class="text-xs text-text-muted">European style, high fat content</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-bold rounded-full">
                                    15 kg
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Robert Johnson</p>
                                <p class="text-xs text-text-muted">Purchasing Officer</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2 days ago</p>
                                <p class="text-xs text-text-muted">4:10 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    MEDIUM
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openApproveModal(5, 'Unsalted Butter', 'Robert Johnson')"
                                        class="px-4 py-2 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded-lg">
                                        <i class="fas fa-check mr-1"></i>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal(5, 'Unsalted Butter', 'Robert Johnson')"
                                        class="px-4 py-2 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition rounded-lg">
                                        <i class="fas fa-times mr-1"></i>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">5</span> of 5 pending requests</p>
            </div>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Approve Request</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-text-dark font-semibold" id="approveItemName"></p>
                        <p class="text-sm text-text-muted">Requested by: <span id="approveRequesterName"></span></p>
                    </div>
                </div>
                <p class="text-text-dark mb-4">Are you sure you want to approve this item request?</p>
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
                    <button type="button" onclick="approveRequest()" class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition">
                        <i class="fas fa-check mr-2"></i>
                        Approve Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark">Reject Request</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-text-dark font-semibold" id="rejectItemName"></p>
                        <p class="text-sm text-text-muted">Requested by: <span id="rejectRequesterName"></span></p>
                    </div>
                </div>
                <p class="text-text-dark mb-4">Are you sure you want to reject this item request?</p>
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
                    <button type="button" onclick="rejectRequest()" class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                        <i class="fas fa-times mr-2"></i>
                        Reject Request
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
        // Sample request data for UI demonstration
        let requests = [
            {
                id: 1,
                itemName: "All-Purpose Flour",
                category: "Baking Supplies",
                description: "High-quality flour for bread production",
                additionalInfo: "Required for weekly batch",
                quantity: "25 kg",
                requester: "Maria Garcia",
                position: "Head Baker",
                date: "Today",
                time: "10:30 AM",
                priority: "high"
            },
            {
                id: 2,
                itemName: "Chocolate Chips",
                category: "Baking Ingredients",
                description: "Semi-sweet chocolate chips for cookies",
                additionalInfo: "New cookie recipe development",
                quantity: "5 kg",
                requester: "John Smith",
                position: "Pastry Chef",
                date: "Today",
                time: "09:15 AM",
                priority: "medium"
            },
            {
                id: 3,
                itemName: "Packaging Boxes",
                category: "Retail Packaging",
                description: "Custom branded boxes for retail products",
                additionalInfo: "Size: 8x8x4 inches",
                quantity: "200 units",
                requester: "Emily Chen",
                position: "Sales Staff",
                date: "Yesterday",
                time: "3:45 PM",
                priority: "low"
            },
            {
                id: 4,
                itemName: "Vanilla Extract",
                category: "Flavoring",
                description: "Pure Madagascar vanilla extract",
                additionalInfo: "For premium cake line",
                quantity: "2 liters",
                requester: "David Brown",
                position: "Baker",
                date: "Yesterday",
                time: "1:20 PM",
                priority: "high"
            },
            {
                id: 5,
                itemName: "Unsalted Butter",
                category: "Dairy Products",
                description: "Premium unsalted butter for pastries",
                additionalInfo: "European style, high fat content",
                quantity: "15 kg",
                requester: "Robert Johnson",
                position: "Purchasing Officer",
                date: "2 days ago",
                time: "4:10 PM",
                priority: "medium"
            }
        ];

        let currentRequestId = null;
        let currentItemName = null;
        let currentRequesterName = null;

        // Modal Functions
        function openApproveModal(requestId, itemName, requesterName) {
            currentRequestId = requestId;
            currentItemName = itemName;
            currentRequesterName = requesterName;
            
            document.getElementById('approveItemName').textContent = itemName;
            document.getElementById('approveRequesterName').textContent = requesterName;
            document.getElementById('approveNotes').value = '';
            
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            currentRequestId = null;
            currentItemName = null;
            currentRequesterName = null;
        }

        function openRejectModal(requestId, itemName, requesterName) {
            currentRequestId = requestId;
            currentItemName = itemName;
            currentRequesterName = requesterName;
            
            document.getElementById('rejectItemName').textContent = itemName;
            document.getElementById('rejectRequesterName').textContent = requesterName;
            document.getElementById('rejectReason').value = '';
            
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            currentRequestId = null;
            currentItemName = null;
            currentRequesterName = null;
        }

        // Request Actions
        function approveRequest() {
            const notes = document.getElementById('approveNotes').value;
            
            // Simulate approval
            showMessage(`Request for "${currentItemName}" approved successfully!`, 'success');
            closeApproveModal();
            
            // In a real app, you would remove the request from the table
            // and update the pending count
            updatePendingCount(-1);
        }

        function rejectRequest() {
            const reason = document.getElementById('rejectReason').value;
            
            if (!reason.trim()) {
                alert('Please provide a rejection reason.');
                return;
            }
            
            // Simulate rejection
            showMessage(`Request for "${currentItemName}" rejected.`, 'success');
            closeRejectModal();
            
            // In a real app, you would remove the request from the table
            // and update the pending count
            updatePendingCount(-1);
        }

        // Search functionality
        function searchRequests(query) {
            const rows = document.querySelectorAll('.request-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const item = row.getAttribute('data-item');
                const requester = row.getAttribute('data-requester');
                const searchText = (item + ' ' + requester).toLowerCase();
                
                if (searchText.includes(query.toLowerCase()) || query === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Filter functionality
        function filterRequests(filter) {
            const rows = document.querySelectorAll('.request-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const priority = row.getAttribute('data-priority');
                const date = row.getAttribute('data-date');
                
                let shouldShow = false;
                
                switch(filter) {
                    case 'all':
                        shouldShow = true;
                        break;
                    case 'today':
                        shouldShow = date === 'today';
                        break;
                    case 'week':
                        shouldShow = date === 'today' || date === 'yesterday' || date === '2days';
                        break;
                    case 'urgent':
                        shouldShow = priority === 'high';
                        break;
                    default:
                        shouldShow = true;
                }
                
                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('visibleCount').textContent = visibleCount;
            showMessage(`Filtered by: ${filter}`, 'success');
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

        function updatePendingCount(change) {
            const currentCount = parseInt(document.getElementById('pendingCount').textContent);
            const newCount = Math.max(0, currentCount + change);
            document.getElementById('pendingCount').textContent = newCount;
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
    </script>
@endsection