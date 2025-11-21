@extends('Admin.layout.app')

@section('content')
<div class="space-y-6 relative">

    {{-- 1. HEADER & EXPORT --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Audit Logs</h1>
            <p class="text-sm text-gray-500 mt-1">A secured, read-only record of all critical system activities and security events.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400 mr-2"><i class="fas fa-lock mr-1"></i> Immutable Record</span>
            <button onclick="exportAuditLogs()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Download CSV
            </button>
            <button onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Report
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs') }}" id="auditFilterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search Description</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-xs"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs" 
                               placeholder="e.g. 'Sugar' or 'Spillage'"
                               onchange="document.getElementById('auditFilterForm').submit()">
                    </div>
                </div>

                <!-- Actor Filter -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">User / Actor</label>
                    <select name="user" 
                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs"
                            onchange="document.getElementById('auditFilterForm').submit()">
                        <option value="">All Users</option>
                        <option value="system" {{ request('user') == 'system' ? 'selected' : '' }}>SYSTEM (Automated)</option>
                        @foreach($users->groupBy('role') as $role => $roleUsers)
                            <optgroup label="{{ ucfirst($role) }}">
                                @foreach($roleUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <!-- Module Filter -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Module</label>
                    <select name="module" 
                            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs"
                            onchange="document.getElementById('auditFilterForm').submit()">
                        <option value="">All Modules</option>
                        <option value="auth" {{ request('module') == 'auth' ? 'selected' : '' }}>Authentication / Security</option>
                        <option value="inventory" {{ request('module') == 'inventory' ? 'selected' : '' }}>Inventory / Items</option>
                        <option value="finance" {{ request('module') == 'finance' ? 'selected' : '' }}>Finance / Pricing</option>
                        <option value="users" {{ request('module') == 'users' ? 'selected' : '' }}>User Management</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
                    </div>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200">
                <div class="text-xs text-gray-500">
                    Showing <span class="font-medium">{{ $auditLogs->count() }}</span> of <span class="font-medium">{{ $totalLogs }}</span> total logs
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.audit-logs') }}" class="inline-flex items-center px-3 py-1 text-xs text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times mr-1"></i> Clear Filters
                    </a>
                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-chocolate text-white text-xs rounded hover:bg-chocolate-dark">
                        <i class="fas fa-filter mr-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. AUDIT LOG FEED --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event / Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    @forelse($auditLogs as $log)
                        <tr class="{{ $log->action == 'DELETE' ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }} transition-colors {{ $log->action == 'DELETE' ? 'border-l-4 border-red-500' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="font-bold text-gray-900">{{ $log->created_at->format('M j, Y') }}</div>
                                <div class="text-xs">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($log->user)
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">{{ $log->user->name }}</div>
                                            <div class="text-xs text-gray-500 capitalize">{{ $log->user->role }}</div>
                                        </div>
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                                            <i class="fas fa-robot"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">SYSTEM</div>
                                            <div class="text-xs text-gray-500">Automated Task</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mb-1 {{ $log->action == 'DELETE' ? 'bg-red-200 text-red-800 border border-red-300' : ($log->action == 'UPDATE' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                                    @if($log->action == 'DELETE')
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                    @elseif($log->action == 'UPDATE')
                                        <i class="fas fa-edit mr-1"></i>
                                    @else
                                        <i class="fas fa-plus mr-1"></i>
                                    @endif
                                    {{ $log->action }}
                                </span>
                                <div class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $log->table_name)) }}</div>
                                <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                    ID: #{{ $log->record_id }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $moduleIcons = [
                                        'users' => 'fas fa-user-shield',
                                        'user_profiles' => 'fas fa-id-card',
                                        'items' => 'fas fa-box',
                                        'stock_movements' => 'fas fa-exchange-alt',
                                        'current_stock' => 'fas fa-warehouse',
                                        'batches' => 'fas fa-tags',
                                        'purchase_orders' => 'fas fa-shopping-cart',
                                        'purchase_order_items' => 'fas fa-list',
                                    ];
                                    $moduleNames = [
                                        'users' => 'Auth',
                                        'user_profiles' => 'Auth',
                                        'items' => 'Inventory',
                                        'stock_movements' => 'Inventory',
                                        'current_stock' => 'Inventory',
                                        'batches' => 'Inventory',
                                        'purchase_orders' => 'Finance',
                                        'purchase_order_items' => 'Finance',
                                    ];
                                @endphp
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="{{ $moduleIcons[$log->table_name] ?? 'fas fa-database' }} mr-2 text-gray-400"></i> 
                                    {{ $moduleNames[$log->table_name] ?? 'System' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($log->action == 'DELETE')
                                    <button onclick="exportAuditLogProof({{ $log->id }})" class="text-chocolate hover:text-chocolate-dark transition font-bold text-xs border border-chocolate/30 px-2 py-1 rounded mr-2">
                                        <i class="fas fa-file-pdf mr-1"></i> Proof
                                    </button>
                                @endif
                                
                                {{-- Pass the full log object as JSON to the function --}}
                                <button onclick='openDetailModal(@json($log))' class="text-gray-400 hover:text-chocolate transition p-2 rounded hover:bg-gray-100" title="View Full Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-search text-3xl mb-3 text-gray-300"></i>
                                <div class="text-lg font-medium">No audit logs found</div>
                                <div class="text-sm">Try adjusting your filters or search criteria</div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($auditLogs->hasPages())
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $auditLogs->firstItem() ?? 0 }}</span> 
                            to <span class="font-medium">{{ $auditLogs->lastItem() ?? 0 }}</span> 
                            of <span class="font-medium">{{ $auditLogs->total() }}</span> results
                        </p>
                    </div>
                    <div>
                        {{ $auditLogs->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

{{-- 4. DETAILS MODAL --}}
<div id="detailModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDetailModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            
            <!-- Header -->
            <div id="modalHeader" class="px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200 bg-white">
                <div>
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">Log Details</h3>
                    <p class="text-xs text-gray-500 mt-1">Record ID: #<span id="modalId"></span></p>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="px-4 py-5 sm:p-6 bg-gray-50 max-h-[75vh] overflow-y-auto">
                
                <!-- Metadata Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white p-3 rounded border border-gray-200 shadow-sm">
                        <div class="text-xs text-gray-400 uppercase font-bold mb-1">Timestamp</div>
                        <div class="text-sm font-mono text-gray-800" id="modalTimestamp"></div>
                    </div>
                    <div class="bg-white p-3 rounded border border-gray-200 shadow-sm">
                        <div class="text-xs text-gray-400 uppercase font-bold mb-1">Actor</div>
                        <div class="text-sm text-gray-800" id="modalActor"></div>
                    </div>
                </div>

                <!-- Dynamic Content Area (Diff Table or Attribute List) -->
                <div id="modalContentContainer" class="mb-4">
                    <!-- Content injected by JS -->
                </div>

            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                <button type="button" onclick="closeDetailModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION -->
<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-info-circle text-chocolate"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-medium text-gray-900">System Notification</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for dynamic functionality --}}
<script>

/* --- TOAST SYSTEM --- */
function showToast(title, message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastTitle').innerText = title;
    document.getElementById('toastMessage').innerText = message;
    
    toast.classList.remove('hidden');
    void toast.offsetWidth; // Force reflow
    toast.classList.remove('translate-y-2', 'opacity-0');

    setTimeout(() => hideToast(), 3000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('translate-y-2', 'opacity-0');
    setTimeout(() => toast.classList.add('hidden'), 300);
}

/* --- EXPORT LOGIC --- */
function exportAuditLogs() {
    showToast('Export Started', 'Generating CSV file. Your download will begin shortly.');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.audit-logs.export") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportAuditLogProof(logId) {
    showToast('Generating Proof', 'Preparing PDF document for download...');
    window.open(`{{ url('admin/audit-logs') }}/${logId}/export`, '_blank');
}

/* --- MODAL LOGIC --- */

// Helpers for formatting keys and values
const formatKey = (key) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatValue = (val) => {
    if (val === true || val === 'true') return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Yes</span>';
    if (val === false || val === 'false') return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span>';
    if (val === null || val === 'null' || val === '') return '<span class="text-gray-400 italic">—</span>';
    if (typeof val === 'object') return JSON.stringify(val);
    return val;
};

// Keys to ignore in the display (Technical timestamps and IDs)
const ignoredKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'remember_token'];

function openDetailModal(log) {
    const modal = document.getElementById('detailModal');
    
    // 1. Metadata
    document.getElementById('modalId').innerText = log.record_id;
    document.getElementById('modalTimestamp').innerText = new Date(log.created_at).toLocaleString();
    document.getElementById('modalActor').innerText = log.user ? `${log.user.name} (${log.user.role})` : 'SYSTEM';

    // 2. Parse Data
    let oldValues = {};
    let newValues = {};
    
    try { oldValues = typeof log.old_values === 'string' ? JSON.parse(log.old_values) : (log.old_values || {}); } catch(e) {}
    try { newValues = typeof log.new_values === 'string' ? JSON.parse(log.new_values) : (log.new_values || {}); } catch(e) {}

    const container = document.getElementById('modalContentContainer');
    container.innerHTML = ''; // Clear previous

    // 3. Render Context-Aware Tables
    if (log.action === 'UPDATE') {
        container.innerHTML = renderDiffTable(oldValues, newValues);
    } else if (log.action === 'CREATE') {
        container.innerHTML = renderAttributeList(newValues, 'New Record Attributes', 'bg-green-50 text-green-800');
    } else if (log.action === 'DELETE') {
        container.innerHTML = renderAttributeList(oldValues, 'Deleted Record Attributes', 'bg-red-50 text-red-800');
    } else {
        // Fallback for custom actions
        if (Object.keys(newValues).length > 0) {
             container.innerHTML = renderAttributeList(newValues, 'Action Details', 'bg-blue-50 text-blue-800');
        } else {
            container.innerHTML = '<div class="text-sm text-gray-500 italic text-center py-4">No additional data logged.</div>';
        }
    }

    // Show modal
    modal.classList.remove('hidden');
}

function renderDiffTable(oldData, newData) {
    // Find all unique keys combined, excluding ignored ones
    const allKeys = new Set([...Object.keys(oldData), ...Object.keys(newData)]);
    const filteredKeys = [...allKeys].filter(k => !ignoredKeys.includes(k));

    if (filteredKeys.length === 0) {
        return '<div class="text-sm text-gray-500 italic text-center py-4">No changes detected in business data.</div>';
    }

    let rows = '';
    let hasChanges = false;

    filteredKeys.forEach(key => {
        const oldVal = oldData[key];
        const newVal = newData[key];
        
        // Compare values loosely to catch '1' vs 1
        const isChanged = oldVal != newVal;
        if(isChanged) hasChanges = true;

        // For updates, usually we prefer to show only Changed fields, 
        // but showing context (unchanged fields) is sometimes helpful. 
        // Here we will highlight changes.
        const bgClass = isChanged ? 'bg-yellow-50' : '';
        const textClass = isChanged ? 'font-semibold text-gray-900' : 'text-gray-500';

        rows += `
            <tr class="${bgClass} hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100 w-1/4">
                    ${formatKey(key)}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700 border-b border-gray-100 w-1/3 break-all">
                    ${formatValue(oldVal)}
                </td>
                <td class="px-4 py-3 text-sm ${textClass} border-b border-gray-100 w-1/3 break-all">
                    ${formatValue(newVal)}
                </td>
            </tr>
        `;
    });

    return `
        <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex justify-between items-center">
                <span class="text-xs font-bold text-gray-700 uppercase">Changes Detected</span>
            </div>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Value</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    ${rows}
                </tbody>
            </table>
        </div>
    `;
}

function renderAttributeList(data, title, headerClass) {
    const keys = Object.keys(data).filter(k => !ignoredKeys.includes(k));
    
    if (keys.length === 0) {
        return '<div class="text-sm text-gray-500 italic text-center py-4">No details available.</div>';
    }

    let rows = keys.map(key => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100 w-1/3">
                ${formatKey(key)}
            </td>
            <td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-100 break-all">
                ${formatValue(data[key])}
            </td>
        </tr>
    `).join('');

    return `
        <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-2 border-b border-gray-200 flex justify-between items-center ${headerClass}">
                <span class="text-xs font-bold uppercase">${title}</span>
            </div>
            <table class="min-w-full">
                <tbody class="divide-y divide-gray-100 bg-white">
                    ${rows}
                </tbody>
            </table>
        </div>
    `;
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeDetailModal();
    }
});

/* --- AUTO FILTERS --- */
// Auto-submit form when date inputs change
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value) {
            document.getElementById('auditFilterForm').submit();
        }
    });
});

// Search debounce
let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('auditFilterForm').submit();
    }, 500);
});
</script>
@endsection