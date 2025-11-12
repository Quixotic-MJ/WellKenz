@extends('Employee.layout.app')

@section('title', 'Item Request - WellKenz ERP')

@section('breadcrumb', 'Item Request')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-gray-900">Custom Item Request</h1>
                <p class="text-gray-500 mt-2">Request new items to be added to inventory</p>
            </div>
        </div>

        <!-- Request Form -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">New Item Request</h3>

            <form id="itemRequestForm" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Name *</label>
                        <input type="text" name="item_req_name" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Enter item name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
                        <select name="item_req_unit" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Select Unit</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="L">Liter (L)</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="box">Box (box)</option>
                            <option value="pack">Pack (pack)</option>
                            <option value="bag">Bag (bag)</option>
                            <option value="can">Can (can)</option>
                            <option value="bottle">Bottle (bottle)</option>
                            <option value="roll">Roll (roll)</option>
                            <option value="set">Set (set)</option>
                            <option value="unit">Unit (unit)</option>
                            <option value="pair">Pair (pair)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Requested *</label>
                    <input type="number" name="item_req_quantity" min="1" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        placeholder="Enter quantity">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description & Specifications *</label>
                    <textarea name="item_req_description" rows="4" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        placeholder="Provide detailed description, specifications, brand preferences, intended use, and why this item is needed"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Be specific about the item specifications to help with procurement
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Approval Process</p>
                            <p class="text-xs text-blue-600 mt-1">
                                Your request will be reviewed by supervisors. Once approved, the item will be added to the
                                inventory
                                and can be included in future requisitions.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="submitBtn"
                        class="px-6 py-3 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>

        <!-- My Requests History -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">My Item Requests</h3>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" id="searchRequests" placeholder="Search requests..."
                            class="pl-9 pr-4 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition w-64">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    </div>
                    <select id="statusFilter" onchange="filterRequests()"
                        class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date Requested
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="requestsTableBody">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-3 opacity-50"></i>
                                <p>Loading your requests...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Request Details Modal -->
    <div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Request Details</h3>
                    <button onclick="closeRequestModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6" id="requestDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadMyRequests();

            // Form submission
            document.getElementById('itemRequestForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitItemRequest();
            });

            // Search functionality
            document.getElementById('searchRequests').addEventListener('input', function(e) {
                filterRequests();
            });
        });

        function submitItemRequest() {
            const form = document.getElementById('itemRequestForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';

            fetch('{{ route('item-requests.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        form.reset();
                        loadMyRequests();
                    } else {
                        showMessage(data.message || 'Error submitting request', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage(error.message || 'Error submitting request. Please try again.', 'error');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Submit Request';
                });
        }

        function loadMyRequests() {
            fetch('{{ route('item-requests.my_requests') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.status}`);
                    }
                    return response.json();
                })
                .then(requests => {
                    console.log('Requests loaded:', requests);
                    const tbody = document.getElementById('requestsTableBody');
                    tbody.innerHTML = '';

                    if (!requests || requests.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-file-alt text-4xl mb-3 opacity-50"></i>
                                    <p>No item requests found. Create your first request above.</p>
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    requests.forEach(request => {
                        const statusColors = {
                            'pending': 'bg-amber-100 text-amber-800 border-amber-200',
                            'approved': 'bg-green-100 text-green-800 border-green-200',
                            'rejected': 'bg-red-100 text-red-800 border-red-200'
                        };

                        const color = statusColors[request.item_req_status] || 'bg-gray-100 text-gray-800 border-gray-200';

                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50 transition';
                        row.setAttribute('data-status', request.item_req_status);
                        row.innerHTML = `
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900">${escapeHtml(request.item_req_name)}</p>
                                ${request.item_req_description ? `
                                    <p class="text-xs text-gray-500 mt-1 truncate max-w-xs" title="${escapeHtml(request.item_req_description)}">
                                        ${escapeHtml(request.item_req_description.substring(0, 60))}${request.item_req_description.length > 60 ? '...' : ''}
                                    </p>
                                ` : ''}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900">${request.item_req_quantity}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">${escapeHtml(request.item_req_unit)}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 ${color} text-xs font-semibold capitalize rounded border">
                                    ${request.item_req_status}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">${new Date(request.created_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                })}</p>
                                <p class="text-xs text-gray-500">${new Date(request.created_at).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="viewRequest(${request.item_req_id})"
                                    class="px-3 py-1 bg-gray-600 text-white text-xs font-medium hover:bg-gray-700 transition rounded">
                                    View Details
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading requests:', error);
                    const tbody = document.getElementById('requestsTableBody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-exclamation-triangle text-2xl mb-3 text-red-500"></i>
                                <p>Error loading requests. Please try again.</p>
                                <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                                <button onclick="loadMyRequests()" class="mt-2 px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                                    Retry
                                </button>
                            </td>
                        </tr>
                    `;
                });
        }

        function filterRequests() {
            const searchTerm = document.getElementById('searchRequests').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#requestsTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.getAttribute('data-status');

                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function viewRequest(requestId) {
            console.log('Loading request details for ID:', requestId);

            fetch(`/item-requests/${requestId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(request => {
                    console.log('Request details loaded:', request);
                    showRequestDetails(request);
                })
                .catch(error => {
                    console.error('Error loading request details:', error);
                    showMessage('Error loading request details: ' + error.message, 'error');
                });
        }

        function showRequestDetails(request) {
            const modal = document.getElementById('requestModal');
            const detailsDiv = document.getElementById('requestDetails');

            // Status badge
            const statusColors = {
                'pending': 'bg-amber-100 text-amber-800 border-amber-200',
                'approved': 'bg-green-100 text-green-800 border-green-200',
                'rejected': 'bg-red-100 text-red-800 border-red-200'
            };

            const statusColor = statusColors[request.item_req_status] || 'bg-gray-100 text-gray-800 border-gray-200';

            // Format dates
            const createdDate = new Date(request.created_at);
            const formattedCreatedDate = createdDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const updatedDate = new Date(request.updated_at);
            const formattedUpdatedDate = updatedDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Get requester and approver names
            const requesterName = request.requester ? request.requester.name : 'You';
            const approverName = request.approver ? request.approver.name : 'Not approved yet';

            detailsDiv.innerHTML = `
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">${escapeHtml(request.item_req_name)}</h2>
                                <p class="text-gray-600">Requested by ${requesterName} on ${formattedCreatedDate}</p>
                            </div>
                            <span class="inline-block px-3 py-1 ${statusColor} text-sm font-semibold capitalize rounded border">
                                ${request.item_req_status}
                            </span>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-balance-scale text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Quantity</p>
                                    <p class="text-lg font-semibold text-gray-900">${request.item_req_quantity}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-ruler text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Unit</p>
                                    <p class="text-lg font-semibold text-gray-900">${escapeHtml(request.item_req_unit)}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Last Updated</p>
                                    <p class="text-sm font-semibold text-gray-900">${formattedUpdatedDate}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-500 mb-2">Description & Specifications</h4>
                        <p class="text-gray-900 bg-gray-50 border border-gray-200 rounded p-3 whitespace-pre-wrap">${escapeHtml(request.item_req_description || 'No description provided')}</p>
                    </div>

                    <!-- Approval Information -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-500 mb-2">Approval Information</h4>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Status:</span> 
                                <span class="capitalize">${request.item_req_status}</span>
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">Requested By:</span> 
                                ${requesterName}
                            </p>
                            ${request.approved_by ? `
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">Approved By:</span> 
                                    ${approverName}
                                </p>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">Next Steps</h4>
                        <div class="space-y-2 text-sm text-blue-700">
                            ${request.item_req_status === 'pending' ? `
                                <p>✓ Your request has been submitted and is awaiting supervisor approval</p>
                                <p>✓ Once approved, this item will be added to the inventory system</p>
                                <p>✓ You'll be able to request this item in future requisitions</p>
                            ` : request.item_req_status === 'approved' ? `
                                <p>✓ Your request has been approved and the item is being added to inventory</p>
                                <p>✓ You can now request this item in regular requisitions</p>
                                <p>✓ The purchasing department will procure this item</p>
                            ` : `
                                <p>✓ Your request has been reviewed but was not approved</p>
                                <p>✓ You may contact your supervisor for more information</p>
                                <p>✓ You can submit a new request with additional information if needed</p>
                            `}
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        function closeRequestModal() {
            document.getElementById('requestModal').classList.add('hidden');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showMessage(message, type) {
            const messageDiv = type === 'success' ?
                document.getElementById('successMessage') :
                document.getElementById('errorMessage');

            if (messageDiv) {
                messageDiv.textContent = message;
                messageDiv.classList.remove('hidden');

                // Scroll to message
                messageDiv.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 5000);
            }
        }

        // Add keyboard event listener for ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRequestModal();
            }
        });
    </script>
@endsection