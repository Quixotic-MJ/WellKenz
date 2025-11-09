@extends('Employee.layout.app')

@section('title', 'My Requisitions - WellKenz ERP')

@section('breadcrumb', 'My Requisitions')

@section('content')
<div class="space-y-6">
    <!-- Messages -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">My Requisitions</h1>
            <p class="text-gray-500 mt-2">Track all your submitted requisitions and their status</p>
        </div>
        <a href="{{ route('requisitions.create') }}" 
           class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 text-sm font-medium rounded">
            <i class="fas fa-plus mr-2"></i>New Requisition
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Requisitions</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="totalCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="pendingCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="approvedCount">0</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rejected</p>
            <p class="text-3xl font-bold text-gray-900 mt-2" id="rejectedCount">0</p>
        </div>
    </div>

    <!-- Requisitions List -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">My Requisitions</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="requisitionsTable">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-3 opacity-50"></i>
                            <p>Loading requisitions...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Requisition Details Modal -->
<div id="requisitionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-6xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">Requisition Details</h3>
                <button onclick="closeRequisitionModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6" id="requisitionDetails">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadMyRequisitions();
    loadStats();

    // Search functionality
    document.getElementById('searchRequisitions').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#requisitionsTable tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

function loadMyRequisitions() {
    fetch('{{ route("requisitions.my_requisitions") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(requisitions => {
        console.log('Requisitions loaded:', requisitions);
        const tbody = document.getElementById('requisitionsTable');
        tbody.innerHTML = '';

        if (!requisitions || requisitions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-3 opacity-50"></i>
                        <p>No requisitions found. <a href="{{ route('requisitions.create') }}" class="text-blue-600 hover:text-blue-800">Create your first requisition</a></p>
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

            const statusColors = {
                'pending': 'bg-amber-100 text-amber-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800',
                'completed': 'bg-blue-100 text-blue-800',
                'processing': 'bg-purple-100 text-purple-800'
            };

            const priorityColor = priorityColors[requisition.req_priority] || 'bg-gray-100 text-gray-800';
            const statusColor = statusColors[requisition.req_status] || 'bg-gray-100 text-gray-800';
            
            // Format date
            const date = new Date(requisition.created_at);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            // Count total items
            const totalItems = requisition.items ? requisition.items.length : 0;

            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 transition';
            row.innerHTML = `
                <td class="px-6 py-4">
                    <p class="text-sm font-semibold text-gray-900">${requisition.req_ref || 'N/A'}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-900 truncate max-w-xs" title="${requisition.req_purpose || ''}">
                        ${requisition.req_purpose ? requisition.req_purpose.substring(0, 50) + (requisition.req_purpose.length > 50 ? '...' : '') : 'No purpose provided'}
                    </p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-900">${totalItems} items</p>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 ${priorityColor} text-xs font-semibold capitalize rounded">
                        ${requisition.req_priority || 'Not set'}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 ${statusColor} text-xs font-semibold capitalize rounded">
                        ${requisition.req_status || 'pending'}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-900">${formattedDate}</p>
                </td>
                <td class="px-6 py-4">
                    <button onclick="viewRequisition(${requisition.req_id})"
                        class="px-3 py-1 bg-gray-600 text-white text-xs font-medium hover:bg-gray-700 transition rounded">
                        View Details
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error loading requisitions:', error);
        const tbody = document.getElementById('requisitionsTable');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-3 text-red-500"></i>
                    <p>Error loading requisitions. Please try again.</p>
                    <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                    <button onclick="loadMyRequisitions()" class="mt-2 px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                        Retry
                    </button>
                </td>
            </tr>
        `;
    });
}

function loadStats() {
    fetch('{{ route("requisitions.my_requisitions") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(requisitions => {
        // Check if requisitions is an array
        if (!Array.isArray(requisitions)) {
            console.error('Expected array but got:', requisitions);
            return;
        }

        const stats = {
            total: requisitions.length,
            pending: requisitions.filter(r => r.req_status === 'pending').length,
            approved: requisitions.filter(r => r.req_status === 'approved').length,
            rejected: requisitions.filter(r => r.req_status === 'rejected').length
        };

        document.getElementById('totalCount').textContent = stats.total;
        document.getElementById('pendingCount').textContent = stats.pending;
        document.getElementById('approvedCount').textContent = stats.approved;
        document.getElementById('rejectedCount').textContent = stats.rejected;
    })
    .catch(error => {
        console.error('Error loading stats:', error);
        // Set default values on error
        document.getElementById('totalCount').textContent = '0';
        document.getElementById('pendingCount').textContent = '0';
        document.getElementById('approvedCount').textContent = '0';
        document.getElementById('rejectedCount').textContent = '0';
    });
}

function viewRequisition(requisitionId) {
    console.log('Loading requisition details for ID:', requisitionId);
    
    fetch(`/requisitions/${requisitionId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(requisition => {
        console.log('Requisition details loaded:', requisition);
        showRequisitionDetails(requisition);
    })
    .catch(error => {
        console.error('Error loading requisition details:', error);
        showMessage('Error loading requisition details: ' + error.message, 'error');
    });
}

function showRequisitionDetails(requisition) {
    const modal = document.getElementById('requisitionModal');
    const detailsDiv = document.getElementById('requisitionDetails');

    // Format dates
    const createdDate = new Date(requisition.created_at);
    const formattedCreatedDate = createdDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const approvedDate = requisition.approved_date ? new Date(requisition.approved_date) : null;
    const formattedApprovedDate = approvedDate ? approvedDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : 'Not approved yet';

    // Status badge
    const statusColors = {
        'pending': 'bg-amber-100 text-amber-800 border-amber-200',
        'approved': 'bg-green-100 text-green-800 border-green-200',
        'rejected': 'bg-red-100 text-red-800 border-red-200',
        'completed': 'bg-blue-100 text-blue-800 border-blue-200'
    };

    const statusColor = statusColors[requisition.req_status] || 'bg-gray-100 text-gray-800 border-gray-200';

    // Priority badge
    const priorityColors = {
        'low': 'bg-green-100 text-green-800 border-green-200',
        'medium': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'high': 'bg-red-100 text-red-800 border-red-200'
    };

    const priorityColor = priorityColors[requisition.req_priority] || 'bg-gray-100 text-gray-800 border-gray-200';

    // Build items list
    let itemsHtml = '';
    let totalItems = 0;
    let totalQuantity = 0;

    if (requisition.items && requisition.items.length > 0) {
        requisition.items.forEach((item, index) => {
            const itemName = item.item ? item.item.item_name : 'Item not found';
            const itemCode = item.item ? item.item.item_code : 'N/A';
            const itemCategory = item.item && item.item.category ? item.item.category.cat_name : 'Uncategorized';
            
            itemsHtml += `
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td>
                    <td class="px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${escapeHtml(itemName)}</p>
                            <p class="text-xs text-gray-500">Code: ${escapeHtml(itemCode)}</p>
                            <p class="text-xs text-gray-500">Category: ${escapeHtml(itemCategory)}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold">${item.req_item_quantity || '0'}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${escapeHtml(item.item_unit || 'N/A')}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-block px-2 py-1 ${getItemStatusColor(item.req_item_status)} text-xs font-semibold capitalize rounded">
                            ${item.req_item_status || 'pending'}
                        </span>
                    </td>
                </tr>
            `;
            totalItems++;
            totalQuantity += parseInt(item.req_item_quantity) || 0;
        });
    } else {
        itemsHtml = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                    <p>No items found for this requisition</p>
                </td>
            </tr>
        `;
    }

    detailsDiv.innerHTML = `
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">${requisition.req_ref || 'N/A'}</h2>
                        <p class="text-gray-600">Submitted on ${formattedCreatedDate}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="inline-block px-3 py-1 ${statusColor} text-sm font-semibold capitalize rounded border">
                            ${requisition.req_status || 'pending'}
                        </span>
                        <span class="inline-block px-3 py-1 ${priorityColor} text-sm font-semibold capitalize rounded border">
                            ${requisition.req_priority || 'Not set'}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Basic Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-cube text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Items</p>
                            <p class="text-lg font-semibold text-gray-900">${totalItems}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-boxes text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Quantity</p>
                            <p class="text-lg font-semibold text-gray-900">${totalQuantity}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-user text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requested By</p>
                            <p class="text-lg font-semibold text-gray-900">${requisition.requester ? requisition.requester.name : 'N/A'}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-amber-100 rounded flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-check text-amber-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Approved Date</p>
                            <p class="text-sm font-semibold text-gray-900">${formattedApprovedDate}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purpose Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-500 mb-2">Purpose / Remarks</h4>
                <p class="text-gray-900 bg-gray-50 border border-gray-200 rounded p-3 whitespace-pre-wrap">${escapeHtml(requisition.req_purpose || 'No purpose provided')}</p>
            </div>

            <!-- Items Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">Requested Items</h4>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded">
                        ${totalItems} items • ${totalQuantity} total quantity
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Item Details</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Progress Tracking -->
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Progress Tracking</h4>
                <div class="space-y-4">
                    ${getProgressSteps(requisition.req_status)}
                </div>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
}

function getItemStatusColor(status) {
    const colors = {
        'pending': 'bg-amber-100 text-amber-800',
        'partially_fulfilled': 'bg-blue-100 text-blue-800',
        'fulfilled': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getProgressSteps(status) {
    const steps = [
        { id: 'submitted', label: 'Submitted', description: 'Requisition has been submitted for review' },
        { id: 'under_review', label: 'Under Review', description: 'Being reviewed by supervisor/manager' },
        { id: 'approved', label: 'Approved', description: 'Approved by management' },
        { id: 'processing', label: 'Processing', description: 'Being processed by purchasing department' },
        { id: 'completed', label: 'Completed', description: 'Items delivered or ready for pickup' }
    ];

    let currentStepIndex = 0;
    switch(status) {
        case 'pending': currentStepIndex = 0; break;
        case 'under_review': currentStepIndex = 1; break;
        case 'approved': currentStepIndex = 2; break;
        case 'processing': currentStepIndex = 3; break;
        case 'completed': currentStepIndex = 4; break;
        default: currentStepIndex = 0;
    }

    return steps.map((step, index) => `
        <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center 
                ${index <= currentStepIndex ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-500'} 
                ${index === currentStepIndex ? 'ring-2 ring-green-200' : ''}">
                ${index < currentStepIndex ? '<i class="fas fa-check text-sm"></i>' : (index + 1)}
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium ${index <= currentStepIndex ? 'text-gray-900' : 'text-gray-500'}">
                    ${step.label}
                </p>
                <p class="text-xs text-gray-500">${step.description}</p>
            </div>
            ${index < steps.length - 1 ? `
                <div class="flex-1 ml-4">
                    <div class="h-0.5 ${index < currentStepIndex ? 'bg-green-600' : 'bg-gray-300'}"></div>
                </div>
            ` : ''}
        </div>
    `).join('');
}

function closeRequisitionModal() {
    document.getElementById('requisitionModal').classList.add('hidden');
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

        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 5000);
    }
}
</script>
@endsection