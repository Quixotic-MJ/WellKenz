@extends('Supervisor.layout.app')

@section('title', 'Item Requests - WellKenz ERP')

@section('breadcrumb', 'Item Requests')

@section('content')
<div class="space-y-6">
    <!-- Messages -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">Item Request Approval</h1>
            <p class="text-gray-500 mt-2">Review and approve custom item requests from employees</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Approvals</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="pendingCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Approved Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="approvedTodayCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rejected This Week</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="rejectedWeekCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total This Month</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="totalMonthCount">0</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-xl font-semibold text-gray-900">Filters</h3>
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <input type="text" id="searchRequests" placeholder="Search by name, description, employee..."
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition w-full">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                </div>

                <select id="statusFilter" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>

                <input type="date" id="dateFilter" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition">

                <button onclick="clearFilters()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded text-sm">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Item Requests Table -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Item Requests</h3>
            <div class="flex items-center space-x-4">
                <button onclick="loadRequests()" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded text-sm">
                    <i class="fas fa-refresh mr-2"></i>Refresh
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="requestsTable">
                    <!-- Requests will be loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-6" id="paginationContainer">
            <!-- Pagination will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900" id="modalTitle">Review Item Request</h3>
                <button onclick="closeApprovalModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6" id="requestDetails">
            <!-- Request details will be loaded here -->
        </div>

        <!-- Approval Form -->
        <div class="p-6 border-t border-gray-200" id="approvalFormSection">
            <form id="approvalForm">
                @csrf
                <input type="hidden" id="requestId" name="request_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="decision" value="approve" class="text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">Approve</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="decision" value="reject" class="text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">Reject</span>
                            </label>
                        </div>
                    </div>

                    <div id="rejectReasonSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                        <textarea id="req_reject_reason" name="req_reject_reason" rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Please provide a reason for rejecting this request..." required></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApprovalModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                            Cancel
                        </button>
                        <button type="submit" id="submitDecisionBtn"
                            class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded">
                            Submit Decision
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- View Only Section (for processed requests) -->
        <div class="p-6 border-t border-gray-200 hidden" id="viewOnlySection">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-800">Request Already Processed</h4>
                        <p class="text-sm text-blue-700 mt-1">This request has already been processed and cannot be modified.</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeApprovalModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequest = null;
let currentPage = 1;
let totalPages = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadRequests();
    loadStats();

    // Search functionality
    document.getElementById('searchRequests').addEventListener('input', debounce(function(e) {
        currentPage = 1;
        loadRequests();
    }, 500));

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 1;
        loadRequests();
    });

    document.getElementById('dateFilter').addEventListener('change', function() {
        currentPage = 1;
        loadRequests();
    });

    // Decision radio buttons
    document.querySelectorAll('input[name="decision"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rejectReasonSection = document.getElementById('rejectReasonSection');

            if (this.value === 'reject') {
                rejectReasonSection.classList.remove('hidden');
                document.getElementById('req_reject_reason').required = true;
            } else {
                rejectReasonSection.classList.add('hidden');
                document.getElementById('req_reject_reason').required = false;
            }
        });
    });

    // Approval form submission
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const decision = formData.get('decision');
        const requestId = formData.get('request_id');
        const rejectReason = formData.get('req_reject_reason');

        // Validate reject reason if rejecting
        if (decision === 'reject' && (!rejectReason || rejectReason.trim() === '')) {
            showMessage('Please provide a rejection reason', 'error');
            return;
        }

        // Disable submit button to prevent multiple submissions
        const submitBtn = document.getElementById('submitDecisionBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

        const url = `/supervisor/item-requests/${requestId}/status`;

        fetch(url, {
            method: 'POST',
            body: JSON.stringify({
                item_req_status: decision === 'approve' ? 'approved' : 'rejected',
                remarks: rejectReason,
                _token: '{{ csrf_token() }}'
            }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('Access denied. You do not have permission to approve requests.');
                }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closeApprovalModal();
                loadRequests();
                loadStats();
            } else {
                showMessage(data.message || 'Error processing request', 'error');
                // Re-enable submit button on error
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit Decision';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error processing request: ' + error.message, 'error');
            // Re-enable submit button on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Decision';
        });
    });
});

function loadRequests() {
    const search = document.getElementById('searchRequests').value;
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;

    let url = `/supervisor/item-requests?page=${currentPage}`;

    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (status !== 'all') url += `&status=${status}`;
    if (date) url += `&date=${date}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('Access denied. You do not have permission to view requests.');
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Handle both paginated response and direct array
        let requests = [];
        let meta = null;

        if (data.data !== undefined) {
            // Paginated response
            requests = data.data;
            meta = data.meta;
        } else if (Array.isArray(data)) {
            // Direct array response
            requests = data;
        } else {
            // Single object or error
            if (data.error) {
                throw new Error(data.error);
            }
            requests = [data];
        }

        const tbody = document.getElementById('requestsTable');
        tbody.innerHTML = '';

        if (!requests || requests.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                        <p>No item requests found.</p>
                    </td>
                </tr>
            `;
            return;
        }

        requests.forEach(request => {
            const statusColors = {
                'pending': 'bg-amber-100 text-amber-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800'
            };

            const statusColor = statusColors[request.item_req_status] || 'bg-gray-100 text-gray-800';

            // Determine button text and actions based on status
            let actionButtons = '';
            if (request.item_req_status === 'pending') {
                actionButtons = `
                    <button onclick="reviewRequest(${request.item_req_id})"
                        class="px-3 py-1 bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition rounded">
                        Review
                    </button>
                    <button onclick="quickApprove(${request.item_req_id})"
                        class="px-3 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700 transition rounded">
                        Quick Approve
                    </button>
                `;
            } else {
                actionButtons = `
                    <button onclick="reviewRequest(${request.item_req_id})"
                        class="px-3 py-1 bg-gray-600 text-white text-xs font-medium hover:bg-gray-700 transition rounded">
                        View Details
                    </button>
                `;
            }

            const row = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${request.requester ? request.requester.name : 'N/A'}</p>
                        <p class="text-xs text-gray-500">${request.requester ? request.requester.position : ''}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${request.item_req_name}</p>
                        <p class="text-xs text-gray-500 max-w-xs truncate">${request.item_req_description ? request.item_req_description.substring(0, 50) + (request.item_req_description.length > 50 ? '...' : '') : 'No description'}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${request.item_req_quantity} ${request.item_req_unit}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2 py-1 ${statusColor} text-xs font-semibold capitalize rounded">
                            ${request.item_req_status}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${new Date(request.created_at).toLocaleDateString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        // Update pagination
        if (meta) {
            currentPage = meta.current_page;
            totalPages = meta.last_page;
            updatePagination(meta);
        } else {
            // Hide pagination if no meta data
            document.getElementById('paginationContainer').innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error loading requests:', error);
        showMessage('Error loading requests: ' + error.message, 'error');
    });
}

function updatePagination(meta) {
    const container = document.getElementById('paginationContainer');
    if (!meta || meta.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <div class="flex items-center space-x-2">
            <button onclick="changePage(1)" ${meta.current_page === 1 ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded text-sm ${meta.current_page === 1 ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50'}">
                First
            </button>
            <button onclick="changePage(${meta.current_page - 1})" ${meta.current_page === 1 ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded text-sm ${meta.current_page === 1 ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50'}">
                Previous
            </button>

            <span class="text-sm text-gray-700">
                Page ${meta.current_page} of ${meta.last_page}
            </span>

            <button onclick="changePage(${meta.current_page + 1})" ${meta.current_page === meta.last_page ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded text-sm ${meta.current_page === meta.last_page ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50'}">
                Next
            </button>
            <button onclick="changePage(${meta.last_page})" ${meta.current_page === meta.last_page ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded text-sm ${meta.current_page === meta.last_page ? 'bg-gray-100 text-gray-400' : 'hover:bg-gray-50'}">
                Last
            </button>
        </div>
        <div class="text-sm text-gray-700">
            Showing ${meta.from} to ${meta.to} of ${meta.total} results
        </div>
    `;
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadRequests();
}

function loadStats() {
    fetch('/supervisor/item-requests/stats', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('Access denied. You do not have permission to view statistics.');
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        document.getElementById('pendingCount').textContent = data.pending || 0;
        document.getElementById('approvedTodayCount').textContent = data.approved_today || 0;
        document.getElementById('rejectedWeekCount').textContent = data.rejected_week || 0;
        document.getElementById('totalMonthCount').textContent = data.total_month || 0;
    })
    .catch(error => {
        console.error('Error loading stats:', error);
        showMessage('Error loading statistics: ' + error.message, 'error');
    });
}

function reviewRequest(requestId) {
    fetch(`/supervisor/item-requests/${requestId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('Access denied. You do not have permission to view request details.');
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }

        const request = data;
        currentRequest = request;

        // Update modal title based on status
        const modalTitle = document.getElementById('modalTitle');
        if (request.item_req_status === 'pending') {
            modalTitle.textContent = 'Review Item Request';
        } else {
            modalTitle.textContent = 'Item Request Details';
        }

        // Show/hide form based on status
        const approvalFormSection = document.getElementById('approvalFormSection');
        const viewOnlySection = document.getElementById('viewOnlySection');

        if (request.item_req_status === 'pending') {
            approvalFormSection.classList.remove('hidden');
            viewOnlySection.classList.add('hidden');
        } else {
            approvalFormSection.classList.add('hidden');
            viewOnlySection.classList.remove('hidden');
        }

        document.getElementById('requestDetails').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <p class="text-gray-900 font-semibold">${request.requester ? request.requester.name : 'N/A'}</p>
                        <p class="text-sm text-gray-600">${request.requester ? request.requester.position : ''}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Request Date</label>
                        <p class="text-gray-900">${new Date(request.created_at).toLocaleDateString()}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
                        <p class="text-gray-900 font-semibold">${request.item_req_name}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity & Unit</label>
                        <p class="text-gray-900">${request.item_req_quantity} ${request.item_req_unit}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded">${request.item_req_description || 'No description provided'}</p>
                </div>

                ${request.item_req_status === 'rejected' && request.item_req_reject_reason ? `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle text-red-400 text-lg mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-red-800 mb-1">Rejection Reason</h4>
                            <p class="text-red-700 whitespace-pre-wrap">${request.item_req_reject_reason}</p>
                            ${request.approver ? `
                                <p class="text-xs text-red-600 mt-2">
                                    Rejected by: ${request.approver.name} on ${new Date(request.updated_at).toLocaleDateString()}
                                </p>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}

                ${request.item_req_status === 'approved' && request.approver ? `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-lg mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-green-800 mb-1">Approval Information</h4>
                            <p class="text-green-700">
                                Approved by: ${request.approver.name} on ${new Date(request.updated_at).toLocaleDateString()}
                            </p>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        document.getElementById('requestId').value = requestId;
        document.getElementById('approvalModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error loading request details:', error);
        showMessage('Error loading request details: ' + error.message, 'error');
    });
}

function quickApprove(requestId) {
    if (!confirm('Are you sure you want to approve this item request?')) return;

    fetch(`/supervisor/item-requests/${requestId}/status`, {
        method: 'POST',
        body: JSON.stringify({
            item_req_status: 'approved',
            remarks: 'Approved via quick action',
            _token: '{{ csrf_token() }}'
        }),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('Access denied. You do not have permission to approve requests.');
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            loadRequests();
            loadStats();
        } else {
            showMessage(data.message || 'Error approving request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error approving request: ' + error.message, 'error');
    });
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
    document.getElementById('rejectReasonSection').classList.add('hidden');

    // Reset submit button
    const submitBtn = document.getElementById('submitDecisionBtn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = 'Submit Decision';
}

function clearFilters() {
    document.getElementById('searchRequests').value = '';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('dateFilter').value = '';
    currentPage = 1;
    loadRequests();
}

function showMessage(message, type) {
    const messageDiv = type === 'success' ?
        document.getElementById('successMessage') :
        document.getElementById('errorMessage');

    if (messageDiv) {
        messageDiv.textContent = message;
        messageDiv.classList.remove('hidden');

        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 5000);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endsection