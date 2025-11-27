@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & EXPORT --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">System Audit Logs</h1>
            <p class="text-sm text-gray-500">A secured, read-only record of all critical system activities and security events.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-cream-bg text-xs font-bold text-chocolate border border-border-soft mr-2">
                <i class="fas fa-lock mr-2"></i> Immutable Record
            </span>
            <button onclick="exportAuditLogs()" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-download mr-2 opacity-70 group-hover:opacity-100"></i> CSV
            </button>
            <button onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-print mr-2 opacity-70 group-hover:opacity-100"></i> Print
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" id="auditFilterForm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-chocolate mb-1">Search Description</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                               placeholder="e.g. 'Sugar' or 'Spillage'"
                               onchange="document.getElementById('auditFilterForm').submit()">
                    </div>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-chocolate mb-1">User / Actor</label>
                    <div class="relative">
                        <select name="user" 
                                class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer"
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
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-chocolate mb-1">Module</label>
                    <div class="relative">
                        <select name="module" 
                                class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer"
                                onchange="document.getElementById('auditFilterForm').submit()">
                            <option value="">All Modules</option>
                            <option value="auth" {{ request('module') == 'auth' ? 'selected' : '' }}>Authentication / Security</option>
                            <option value="inventory" {{ request('module') == 'inventory' ? 'selected' : '' }}>Inventory / Items</option>
                            <option value="finance" {{ request('module') == 'finance' ? 'selected' : '' }}>Finance / Pricing</option>
                            <option value="users" {{ request('module') == 'users' ? 'selected' : '' }}>User Management</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-chocolate mb-1">Date Range</label>
                    <div class="flex gap-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel">
                        <span class="self-center text-gray-400">-</span>
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel">
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-border-soft">
                <div class="text-xs font-bold text-chocolate uppercase tracking-wide">
                    Showing <span class="text-caramel">{{ $auditLogs->count() }}</span> of {{ $totalLogs }} logs
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-chocolate hover:bg-cream-bg rounded-lg transition-colors">
                        Clear Filters
                    </a>
                    <button type="submit" class="px-4 py-2 bg-chocolate text-white text-xs font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-sm">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. AUDIT LOG FEED --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Timestamp</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Actor</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Event / Action</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Module</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-border-soft">
                    @forelse($auditLogs as $log)
                        <tr class="group transition-colors {{ $log->action == 'DELETE' ? 'bg-red-50/50 hover:bg-red-50' : 'hover:bg-cream-bg' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-chocolate">{{ $log->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($log->user)
                                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-chocolate to-caramel flex items-center justify-center text-white font-bold text-xs shadow-sm ring-2 ring-white">
                                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">{{ $log->user->name }}</div>
                                            <div class="text-xs text-caramel font-bold uppercase tracking-wide">{{ $log->user->role }}</div>
                                        </div>
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs">
                                            <i class="fas fa-robot"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">SYSTEM</div>
                                            <div class="text-xs text-gray-400">Automated Task</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badgeColor = match($log->action) {
                                        'DELETE' => 'bg-red-100 text-red-800 border-red-200',
                                        'UPDATE' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-green-100 text-green-800 border-green-200',
                                    };
                                    $icon = match($log->action) {
                                        'DELETE' => 'fa-trash-alt',
                                        'UPDATE' => 'fa-edit',
                                        default => 'fa-plus-circle',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $badgeColor }} mb-1">
                                    <i class="fas {{ $icon }} mr-1.5"></i> {{ $log->action }}
                                </span>
                                <div class="text-sm font-medium text-chocolate">{{ ucwords(str_replace('_', ' ', $log->table_name)) }}</div>
                                <div class="text-[10px] text-gray-400 font-mono mt-0.5">ID: #{{ $log->record_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $moduleIcons = [
                                        'users' => 'fas fa-user-shield',
                                        'items' => 'fas fa-box',
                                        'purchase_orders' => 'fas fa-shopping-cart',
                                    ];
                                    $moduleNames = [
                                        'users' => 'Auth',
                                        'items' => 'Inventory',
                                        'purchase_orders' => 'Finance',
                                    ];
                                @endphp
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="w-6 h-6 rounded bg-cream-bg flex items-center justify-center mr-2 text-chocolate border border-border-soft">
                                        <i class="{{ $moduleIcons[$log->table_name] ?? 'fas fa-database' }} text-xs"></i> 
                                    </span>
                                    {{ $moduleNames[$log->table_name] ?? 'System' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($log->action == 'DELETE')
                                    <button onclick="exportAuditLogProof({{ $log->id }})" class="inline-flex items-center px-2 py-1 text-xs font-bold text-chocolate border border-chocolate/20 rounded hover:bg-chocolate hover:text-white transition-colors mr-2">
                                        <i class="fas fa-file-pdf mr-1"></i> Proof
                                    </button>
                                @endif
                                
                                <button onclick='openDetailModal(@json($log))' class="text-gray-400 hover:text-caramel bg-white hover:bg-cream-bg p-2 rounded-lg border border-transparent hover:border-border-soft transition-all shadow-sm">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-search text-chocolate/30 text-2xl"></i>
                                    </div>
                                    <p class="font-display text-lg font-bold text-chocolate">No audit logs found</p>
                                    <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($auditLogs->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>

{{-- 4. DETAILS MODAL --}}
<div id="detailModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeDetailModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-border-soft">
            
            <div class="px-6 py-4 border-b border-border-soft bg-chocolate flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-display font-bold text-white" id="modalTitle">Log Details</h3>
                    <p class="text-xs text-white/70 mt-0.5 font-mono">Record ID: #<span id="modalId"></span></p>
                </div>
                <button onclick="closeDetailModal()" class="text-white/60 hover:text-white transition-colors focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="px-6 py-6 bg-cream-bg max-h-[70vh] overflow-y-auto custom-scrollbar">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg border border-border-soft shadow-sm">
                        <div class="text-xs font-bold text-caramel uppercase tracking-widest mb-1">Timestamp</div>
                        <div class="text-sm font-bold text-chocolate" id="modalTimestamp"></div>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-border-soft shadow-sm">
                        <div class="text-xs font-bold text-caramel uppercase tracking-widest mb-1">Actor</div>
                        <div class="text-sm font-bold text-chocolate" id="modalActor"></div>
                    </div>
                </div>

                <div id="modalContentContainer">
                    </div>

            </div>

            <div class="bg-white px-6 py-4 border-t border-border-soft flex flex-row-reverse">
                <button type="button" onclick="closeDetailModal()" class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-5 py-2 bg-white text-sm font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-xl rounded-xl pointer-events-auto ring-1 ring-black/5 border border-border-soft overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-info-circle text-chocolate text-xl"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-bold text-chocolate">Notification</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-chocolate focus:outline-none transition-colors">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
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
    if (val === true || val === 'true') return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-green-100 text-green-800">Yes</span>';
    if (val === false || val === 'false') return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-gray-200 text-gray-600">No</span>';
    if (val === null || val === 'null' || val === '' || val === undefined) return '<span class="text-gray-300 italic">—</span>';
    if (typeof val === 'object') return formatObjectValue(val);
    return val;
};

const formatObjectValue = (obj) => {
    if (obj === null || typeof obj !== 'object') return '—';
    
    // Check if user profile
    if (obj.id && (obj.employee_id || obj.position || obj.department)) {
        return formatUserProfile(obj);
    }
    
    const keys = Object.keys(obj);
    if (keys.length <= 3) {
        return `<div class="space-y-1">${keys.map(key => `
            <span class="inline-block bg-white border border-border-soft rounded px-2 py-1 mr-1 mb-1 text-xs">
                <span class="font-bold text-chocolate">${formatKey(key)}:</span> 
                <span class="text-gray-600">${formatValue(obj[key])}</span>
            </span>`).join('')}</div>`;
    }
    
    const summary = keys.slice(0, 2).map(key => `<span class="text-xs font-medium text-chocolate">${formatKey(key)}</span>`).join(', ');
    return `<div class="text-xs text-gray-500 italic">${summary} and ${keys.length - 2} more fields...</div>`;
};

const formatUserProfile = (profile) => {
    // Simplified for brevity in JS, styling matched to theme
    let html = '<div class="space-y-1">';
    if(profile.employee_id) html += `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 mr-1 border border-blue-100"><i class="fas fa-id-card mr-1"></i>${profile.employee_id}</span>`;
    if(profile.position) html += `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 mr-1 border border-green-100"><i class="fas fa-briefcase mr-1"></i>${profile.position}</span>`;
    html += '</div>';
    return html;
};

const ignoredKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'remember_token', 'password_hash', 'user_id'];

function openDetailModal(log) {
    const modal = document.getElementById('detailModal');
    
    document.getElementById('modalId').innerText = log.record_id;
    document.getElementById('modalTimestamp').innerText = new Date(log.created_at).toLocaleString();
    document.getElementById('modalActor').innerText = log.user ? `${log.user.name} (${log.user.role})` : 'SYSTEM';

    let oldValues = {};
    let newValues = {};
    
    try { oldValues = typeof log.old_values === 'string' ? JSON.parse(log.old_values) : (log.old_values || {}); } catch(e) {}
    try { newValues = typeof log.new_values === 'string' ? JSON.parse(log.new_values) : (log.new_values || {}); } catch(e) {}

    const container = document.getElementById('modalContentContainer');
    
    if (log.action === 'UPDATE') {
        container.innerHTML = renderDiffTable(oldValues, newValues);
    } else if (log.action === 'CREATE') {
        container.innerHTML = renderAttributeList(newValues, 'New Record Attributes', 'bg-green-50 text-green-800 border-green-200');
    } else if (log.action === 'DELETE') {
        container.innerHTML = renderAttributeList(oldValues, 'Deleted Record Attributes', 'bg-red-50 text-red-800 border-red-200');
    } else {
        if (Object.keys(newValues).length > 0) {
             container.innerHTML = renderAttributeList(newValues, 'Action Details', 'bg-cream-bg text-chocolate border-border-soft');
        } else {
            container.innerHTML = '<div class="text-sm text-gray-400 italic text-center py-8 border-2 border-dashed border-border-soft rounded-lg">No additional data logged.</div>';
        }
    }

    modal.classList.remove('hidden');
}

function renderDiffTable(oldData, newData) {
    const allKeys = new Set([...Object.keys(oldData), ...Object.keys(newData)]);
    const filteredKeys = [...allKeys].filter(k => !ignoredKeys.includes(k));

    if (filteredKeys.length === 0) {
        return '<div class="text-sm text-gray-400 italic text-center py-8 border-2 border-dashed border-border-soft rounded-lg">No business data changes detected.</div>';
    }

    let rows = '';
    filteredKeys.forEach(key => {
        const oldVal = oldData[key];
        const newVal = newData[key];
        const isChanged = oldVal != newVal;
        const bgClass = isChanged ? 'bg-amber-50/50' : '';
        const textClass = isChanged ? 'font-bold text-chocolate' : 'text-gray-500';

        rows += `
            <tr class="${bgClass} hover:bg-cream-bg transition-colors border-b border-gray-100 last:border-0">
                <td class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">${formatKey(key)}</td>
                <td class="px-4 py-3 text-sm text-gray-600 w-1/3 break-all font-mono">${formatValue(oldVal)}</td>
                <td class="px-4 py-3 text-sm ${textClass} w-1/3 break-all font-mono">
                    ${formatValue(newVal)}
                    ${isChanged ? '<i class="fas fa-exclamation-circle text-amber-500 ml-2 text-xs" title="Changed"></i>' : ''}
                </td>
            </tr>
        `;
    });

    return `
        <div class="bg-white rounded-lg border border-border-soft shadow-sm overflow-hidden">
            <div class="bg-cream-bg px-4 py-2 border-b border-border-soft flex justify-between items-center">
                <span class="text-xs font-bold text-chocolate uppercase tracking-widest">Changes Detected</span>
            </div>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-border-soft">
                        <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Field</th>
                        <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Old Value</th>
                        <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">New Value</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function renderAttributeList(data, title, headerClass) {
    const keys = Object.keys(data).filter(k => !ignoredKeys.includes(k));
    
    if (keys.length === 0) return '<div class="text-sm text-gray-400 italic text-center py-4">No details available.</div>';

    let rows = keys.map(key => `
        <tr class="hover:bg-cream-bg transition-colors border-b border-gray-100 last:border-0">
            <td class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3 bg-gray-50/30">
                ${formatKey(key)}
            </td>
            <td class="px-4 py-3 text-sm text-chocolate break-all font-mono">
                ${formatValue(data[key])}
            </td>
        </tr>
    `).join('');

    return `
        <div class="bg-white rounded-lg border border-border-soft shadow-sm overflow-hidden">
            <div class="px-4 py-2 border-b ${headerClass} flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-widest">${title}</span>
            </div>
            <table class="min-w-full">
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") closeDetailModal();
});

// Auto-submit triggers
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value) document.getElementById('auditFilterForm').submit();
    });
});

let searchTimeout;
document.querySelector('input[name="search"]').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('auditFilterForm').submit();
    }, 500);
});
</script>
@endsection 