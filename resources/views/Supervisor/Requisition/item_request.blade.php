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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Requests</p>
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
    </div>

    <!-- Pending Requests -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Pending Item Requests</h3>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="searchRequests" placeholder="Search requests..."
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item Details</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="pendingRequestsTable">
                    <!-- Requests will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900">Review Item Request</h3>
                <button onclick="closeApprovalModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6" id="requestDetails">
            <!-- Request details will be loaded here -->
        </div>

        <!-- Approval Form -->
        <div class="p-6 border-t border-gray-200">
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

                    <div id="remarksSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks/Reason</label>
                        <textarea id="remarks" name="remarks" rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Provide remarks for your decision..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApprovalModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded">
                            Submit Decision
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentRequest = null;

document.addEventListener('DOMContentLoaded', function() {
    loadPendingRequests();
    loadStats();

    // Search functionality
    document.getElementById('searchRequests').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#pendingRequestsTable tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Decision radio buttons
    document.querySelectorAll('input[name="decision"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const remarksSection = document.getElementById('remarksSection');
            if (this.value === 'reject') {
                remarksSection.classList.remove('hidden');
                document.getElementById('remarks').required = true;
            } else {
                remarksSection.classList.add('hidden');
                document.getElementById('remarks').required = false;
            }
        });
    });

    // Approval form submission
    document.getElementById('approvalForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const decision = formData.get('decision');
        const requestId = formData.get('request_id');

        const url = decision === 'approve'
            ? `/item-requests/${requestId}/approve`
            : `/item-requests/${requestId}/reject`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closeApprovalModal();
                loadPendingRequests();
                loadStats();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error processing request', 'error');
        });
    });
});

function loadPendingRequests() {
    fetch('/item-requests/pending', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(requests => {
        const tbody = document.getElementById('pendingRequestsTable');
        tbody.innerHTML = '';

        if (requests.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-3 opacity-50"></i>
                        <p>No pending item requests at this time.</p>
                    </td>
                </tr>
            `;
            return;
        }

        requests.forEach(request => {
            const row = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${request.employee.emp_name}</p>
                        <p class="text-xs text-gray-500">${request.employee.emp_position}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${request.item_req_name}</p>
                        <p class="text-xs text-gray-500">${request.item_req_description.substring(0, 50)}...</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${request.item_req_quantity} ${request.item_req_unit}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${new Date(request.created_at).toLocaleDateString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="reviewRequest(${request.item_req_id})"
                            class="px-3 py-1 bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition rounded">
                            Review
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    })
    .catch(error => {
        console.error('Error loading requests:', error);
    });
}

function loadStats() {
    // Load stats - you can implement API endpoints for these
    fetch('/item-requests/pending', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(requests => {
        document.getElementById('pendingCount').textContent = requests.length;
    });
}

function reviewRequest(requestId) {
    fetch(`/item-requests/${requestId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const request = data.request || data; // Handle different response formats

        document.getElementById('requestDetails').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <p class="text-gray-900">${request.employee.emp_name}</p>
                        <p class="text-sm text-gray-500">${request.employee.emp_position}</p>
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
                    <p class="text-gray-900 bg-gray-50 p-3 rounded">${request.item_req_description}</p>
                </div>
            </div>
        `;

        document.getElementById('requestId').value = requestId;
        document.getElementById('approvalModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error loading request details:', error);
        showMessage('Error loading request details', 'error');
    });
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
    document.getElementById('remarksSection').classList.add('hidden');
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
</script>
@endsection