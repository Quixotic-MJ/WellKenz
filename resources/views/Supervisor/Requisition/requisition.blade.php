@extends('Supervisor.layout.app')

@section('title', 'Requisition Approval - WellKenz ERP')

@section('breadcrumb', 'Requisition Approval')

@section('content')
<div class="space-y-6">
    <!-- Messages -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">Requisition Approval</h1>
            <p class="text-gray-500 mt-2">Review and approve employee requisitions</p>
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
    </div>

    <!-- Pending Requisitions -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Pending Requisitions</h3>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="searchRequisitions" placeholder="Search requisitions..."
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requisition Details</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="pendingRequisitionsTable">
                    <!-- Requisitions will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900">Review Requisition</h3>
                <button onclick="closeApprovalModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6" id="requisitionDetails">
            <!-- Requisition details will be loaded here -->
        </div>

        <!-- Approval Form -->
        <div class="p-6 border-t border-gray-200">
            <form id="approvalForm">
                @csrf
                <input type="hidden" id="requisitionId" name="requisition_id">

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
let currentRequisition = null;

document.addEventListener('DOMContentLoaded', function() {
    loadPendingRequisitions();
    loadStats();

    // Search functionality
    document.getElementById('searchRequisitions').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#pendingRequisitionsTable tr');

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
        const requisitionId = formData.get('requisition_id');

        const url = decision === 'approve'
            ? `/supervisor/requisitions/${requisitionId}/approve`
            : `/supervisor/requisitions/${requisitionId}/reject`;

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
                loadPendingRequisitions();
                loadStats();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error processing requisition', 'error');
        });
    });
});

function loadPendingRequisitions() {
    fetch('/supervisor/requisitions', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const requisitions = data.requisitions || data;
        const tbody = document.getElementById('pendingRequisitionsTable');
        tbody.innerHTML = '';

        if (!requisitions || requisitions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-3 opacity-50"></i>
                        <p>No pending requisitions at this time.</p>
                    </td>
                </tr>
            `;
            return;
        }

        requisitions.forEach(requisition => {
            const priorityColors = {
                'low': 'bg-green-100 text-green-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'high': 'bg-red-100 text-red-800'
            };

            const color = priorityColors[requisition.req_priority] || 'bg-gray-100 text-gray-800';

            const row = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${requisition.requester.employee.emp_name}</p>
                        <p class="text-xs text-gray-500">${requisition.requester.employee.emp_position}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">${requisition.req_ref}</p>
                        <p class="text-xs text-gray-500">${requisition.req_purpose.substring(0, 50)}...</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${requisition.items_count || requisition.items.length} items</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2 py-1 ${color} text-xs font-semibold capitalize rounded">
                            ${requisition.req_priority}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900">${new Date(requisition.created_at).toLocaleDateString()}</p>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="reviewRequisition(${requisition.req_id})"
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
        console.error('Error loading requisitions:', error);
    });
}

function loadStats() {
    // Load stats - you can implement API endpoints for these
    fetch('/supervisor/requisitions', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const requisitions = data.requisitions || data;
        document.getElementById('pendingCount').textContent = requisitions ? requisitions.length : 0;
    });
}

function reviewRequisition(requisitionId) {
    fetch(`/supervisor/requisitions/${requisitionId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const requisition = data.requisition;

        document.getElementById('requisitionDetails').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <p class="text-gray-900 font-semibold">${requisition.requester}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requisition Date</label>
                        <p class="text-gray-900">${requisition.date}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                        <p class="text-gray-900 font-semibold">${requisition.reference}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <span class="inline-block px-2 py-1 ${getPriorityColor(requisition.priority)} text-xs font-semibold capitalize rounded">
                            ${requisition.priority}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded">${requisition.purpose}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">Requested Items</label>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Available Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${requisition.items.map(item => `
                                    <tr>
                                        <td class="px-4 py-2">
                                            <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                                            <p class="text-xs text-gray-500">${item.code}</p>
                                        </td>
                                        <td class="px-4 py-2">
                                            <p class="text-sm text-gray-900">${item.quantity}</p>
                                        </td>
                                        <td class="px-4 py-2">
                                            <p class="text-sm text-gray-900">${item.unit}</p>
                                        </td>
                                        <td class="px-4 py-2">
                                            <p class="text-sm ${item.quantity > item.current_stock ? 'text-amber-600' : 'text-gray-900'}">
                                                ${item.current_stock} ${item.unit}
                                            </p>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('requisitionId').value = requisitionId;
        document.getElementById('approvalModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error loading requisition details:', error);
        showMessage('Error loading requisition details', 'error');
    });
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
    document.getElementById('remarksSection').classList.add('hidden');
}

function getPriorityColor(priority) {
    const colors = {
        'low': 'bg-green-100 text-green-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-red-100 text-red-800'
    };
    return colors[priority] || 'bg-gray-100 text-gray-800';
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