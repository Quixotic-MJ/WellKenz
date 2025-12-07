@extends('Purchasing.layout.app')

@section('title', 'Notifications - Purchasing')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 relative pb-24 font-sans text-gray-600">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1 font-sans">Stay updated with production alerts, system messages, and requisition status.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="markAllAsRead" 
                    class="inline-flex items-center px-4 py-2 bg-white border border-border-soft hover:bg-cream-bg text-chocolate text-sm font-medium rounded-lg transition-all shadow-sm">
                <i class="fas fa-check-double mr-2"></i> Mark all read
            </button>
        </div>
    </div>

    {{-- 2. MAIN CARD --}}
    <div class="bg-white rounded-xl border border-border-soft shadow-sm overflow-hidden">
        
        {{-- Filter Tabs --}}
        <div class="border-b border-border-soft bg-gray-50/50 px-6">
            <nav class="flex space-x-8 overflow-x-auto" aria-label="Tabs">
                @php
                    $tabs = [
                        'all' => ['label' => 'All', 'count' => $stats['total']],
                        'unread' => ['label' => 'Unread', 'count' => $stats['unread']],
                        'high' => ['label' => 'High Priority', 'count' => $stats['high_priority']],
                        'urgent' => ['label' => 'Urgent', 'count' => $stats['urgent']],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <a href="{{ request()->fullUrlWithQuery(['filter' => $key]) }}" 
                       data-filter="{{ $key }}"
                       class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-all
                       {{ $filter === $key ? 'border-caramel text-chocolate' : 'border-transparent text-gray-500 hover:text-chocolate hover:border-gray-300' }}">
                        {{ $tab['label'] }}
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs transition-colors
                        {{ $filter === $key ? 'bg-chocolate text-white' : 'bg-gray-200 text-gray-600 group-hover:bg-gray-300' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Notification List --}}
        <div class="divide-y divide-gray-100">
            @forelse(($notifications ?? collect()) as $notification)
                <div class="notification-item group relative bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all duration-300
                            {{ !$notification->is_read ? 'bg-blue-50' : '' }}"
                     data-notification-id="{{ $notification->id }}"
                     data-read="{{ $notification->is_read ? 'true' : 'false' }}">
                    
                    <div class="flex items-start gap-5">
                        {{-- Checkbox --}}
                        <div class="pt-1 flex items-center">
                            <input type="checkbox" 
                                   class="notification-checkbox h-4 w-4 text-chocolate focus:ring-caramel border-gray-300 rounded cursor-pointer transition-transform transform hover:scale-110"
                                   data-notification-id="{{ $notification->id }}">
                        </div>

                        {{-- Icon --}}
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100 text-gray-500 shadow-sm">
                                <i class="{{ $notification->getIconClass() }}"></i>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <h3 class="text-sm font-bold {{ !$notification->is_read ? 'text-gray-900' : 'text-gray-600' }}">
                                            {{ $notification->title }}
                                        </h3>
                                        @if(!$notification->is_read)
                                            <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0" title="Unread"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $notification->message }}</p>
                                    
                                    {{-- Metadata Row --}}
                                    @if($notification->metadata && count($notification->metadata) > 0)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach(array_slice($notification->metadata, 0, 3) as $key => $value)
                                            <div class="inline-flex items-center px-2 py-1 rounded bg-gray-50 border border-gray-100 text-xs text-gray-500">
                                                <span class="font-bold mr-1 text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> 
                                                <span class="truncate max-w-[120px]">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                        
                                        @if($notification->expires_at && $notification->expires_at->isBefore(now()->addDays(1)))
                                            <span class="inline-flex items-center text-xs text-amber-600 font-medium">
                                                <i class="fas fa-clock mr-1"></i> Exp: {{ $notification->expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                
                                {{-- Time & Actions --}}
                                <div class="flex flex-row sm:flex-col items-center sm:items-end gap-3 sm:gap-1 mt-2 sm:mt-0 pl-0 sm:pl-4">
                                    <span class="text-xs text-gray-400 whitespace-nowrap font-medium">
                                        {{ method_exists($notification, 'getTimeAgoAttribute') ? $notification->getTimeAgoAttribute() : $notification->created_at->diffForHumans() }}
                                    </span>
                                    
                                    <div class="flex items-center gap-3 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                        @if($notification->action_url)
                                            <a href="{{ $notification->action_url }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                                View Details
                                            </a>
                                            <span class="text-gray-300 text-xs">|</span>
                                        @endif
                                        
                                        <button class="mark-read-unread text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors" 
                                                title="{{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}">
                                            {{ $notification->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <div class="mx-auto w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 text-chocolate/50">
                        <i class="fas fa-bell-slash text-2xl"></i>
                    </div>
                    <h3 class="font-display text-lg font-medium text-chocolate mb-2">No notifications found</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">
                        @if($filter === 'all')
                            You don't have any notifications yet.
                        @elseif($filter === 'unread')
                            You're all caught up! No unread notifications.
                        @else
                            No notifications match your current filter.
                        @endif
                    </p>
                    @if($filter !== 'all')
                        <a href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}" 
                           class="mt-4 inline-flex items-center px-4 py-2 border border-border-soft shadow-sm text-sm font-medium rounded-lg text-chocolate bg-white hover:bg-cream-bg transition-all">
                            Clear Filters
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if(isset($notifications) && method_exists($notifications, 'hasPages') && $notifications->hasPages())
            <div class="bg-gray-50 border-t border-border-soft px-6 py-4">
                {{ $notifications->appends(['filter' => $filter])->links() }}
            </div>
        @elseif(!empty($notifications) && $notifications->count() > 0)
            <div class="bg-gray-50 border-t border-border-soft px-6 py-3 flex items-center justify-between text-sm text-gray-500">
                <p>Showing <span class="font-medium">{{ $notifications->count() }}</span> results</p>
            </div>
        @endif
    </div>

    {{-- 3. BULK ACTIONS BAR (Sticky) --}}
    <div id="bulkActionsBar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40 hidden transition-all duration-300 ease-in-out">
        <div class="bg-chocolate text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6 border border-chocolate-dark">
            <div class="flex items-center gap-3 text-sm font-medium border-r border-white/20 pr-6">
                <div class="bg-white text-chocolate rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold" id="selectedCount">0</div>
                <span>Selected</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="bulkMarkRead" class="p-2 rounded-full hover:bg-white/10 text-white transition-colors tooltip" title="Mark Read">
                    <i class="fas fa-envelope-open"></i>
                </button>
                <button id="bulkMarkUnread" class="p-2 rounded-full hover:bg-white/10 text-white transition-colors tooltip" title="Mark Unread">
                    <i class="fas fa-envelope"></i>
                </button>
                <button id="bulkDelete" class="p-2 rounded-full hover:bg-red-500/20 text-red-200 hover:text-white transition-colors tooltip" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <button id="cancelBulk" class="ml-2 text-xs text-caramel hover:text-white uppercase font-bold tracking-wide transition-colors">
                Cancel
            </button>
        </div>
    </div>

</div>

{{-- MODALS --}}

<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-chocolate/30 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-border-soft">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-cream-bg sm:mx-0 sm:h-10 sm:w-10 text-caramel">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-display font-medium text-chocolate" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-white border-l-4 border-chocolate rounded-lg shadow-xl p-4 flex items-center w-80 ring-1 ring-black ring-opacity-5">
        <div class="flex-shrink-0">
            <i id="toastIcon" class="fas fa-check-circle text-chocolate"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-chocolate" id="toastTitle">Success</p>
            <p class="text-xs text-gray-500" id="toastMessage">Operation completed.</p>
        </div>
        <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set notifications as active menu item
    if(typeof setActiveMenu === 'function') setActiveMenu('menu-purchasing-notifications');
    let confirmCallback = null;

    // Update tab counts after actions
    function updateTabCounts() {
        fetch('{{ route("purchasing.notifications.stats") }}')
            .then(response => response.json())
            .then(data => {
                document.querySelector('[data-filter="all"] span')?.textContent = data.total;
                document.querySelector('[data-filter="unread"] span')?.textContent = data.unread;
                document.querySelector('[data-filter="high"] span')?.textContent = data.high_priority;
                document.querySelector('[data-filter="urgent"] span')?.textContent = data.urgent;
                
                if (typeof window.notificationStats !== 'undefined') {
                    window.notificationStats = data;
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
    }

    /* --- UI HELPER FUNCTIONS --- */

    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        
        document.getElementById('toastTitle').innerText = title;
        document.getElementById('toastMessage').innerText = message;
        
        // Reset classes
        icon.className = type === 'success' ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600';
        // Ensure toast border color matches type
        toast.querySelector('.border-l-4').className = `bg-white border-l-4 rounded-lg shadow-xl p-4 flex items-center w-80 ring-1 ring-black ring-opacity-5 ${type === 'success' ? 'border-green-600' : 'border-red-600'}`;

        toast.classList.remove('hidden');
        void toast.offsetWidth; // Trigger reflow
        toast.classList.remove('translate-y-full', 'opacity-0');

        setTimeout(hideToast, 3000);
    }

    window.hideToast = function() {
        const toast = document.getElementById('toast');
        toast.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);
    };

    function openConfirmModal(title, message, callback) {
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmMessage').innerText = message;
        confirmCallback = callback;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    window.closeConfirmModal = function() {
        document.getElementById('confirmModal').classList.add('hidden');
        confirmCallback = null;
    };

    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (confirmCallback) confirmCallback();
        closeConfirmModal();
    });

    /* --- CORE FUNCTIONALITY --- */

    // Mark all as read
    const markAllBtn = document.getElementById('markAllAsRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            openConfirmModal(
                'Mark All as Read',
                'Are you sure you want to mark all notifications as read?',
                function() {
                    fetch('{{ route("purchasing.notifications.mark_all_read") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast('Success', 'All notifications marked as read');
                            // Update UI
                            document.querySelectorAll('.notification-item').forEach(item => {
                                if (item.dataset.read === 'false') {
                                    item.dataset.read = 'true';
                                    // Update styling for READ state
                                    item.classList.remove('bg-cream-bg');
                                    item.classList.add('bg-white');
                                    
                                    const h3 = item.querySelector('h3');
                                    if (h3) h3.classList.remove('text-chocolate');
                                    
                                    const dot = h3?.nextElementSibling;
                                    if (dot && dot.tagName === 'SPAN') dot.remove();
                                    
                                    const btn = item.querySelector('.mark-read-unread');
                                    if (btn) btn.textContent = 'Mark Unread';
                                }
                            });
                            updateTabCounts();
                        } else {
                            showToast('Error', 'Failed to mark notifications as read', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error', 'An error occurred', 'error');
                    });
                }
            );
        });
    }

    // Individual Actions
    document.querySelectorAll('.notification-item').forEach(item => {
        const notificationId = item.dataset.notificationId;
        
        // Read/Unread Toggle
        const markReadUnreadBtn = item.querySelector('.mark-read-unread');
        if (markReadUnreadBtn) {
            markReadUnreadBtn.addEventListener('click', function() {
                const isRead = item.dataset.read === 'true';
                const action = isRead ? 'unread' : 'read';
                
                fetch(`{{ route('purchasing.notifications') }}/${notificationId}/mark-${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.dataset.read = (!isRead).toString();
                        if(!isRead) {
                            // Marked as Read
                            item.classList.remove('bg-cream-bg');
                            item.classList.add('bg-white');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.remove('text-chocolate');
                            const dot = h3?.nextElementSibling;
                            if (dot && dot.tagName === 'SPAN') dot.remove();
                            markReadUnreadBtn.textContent = 'Mark Unread';
                        } else {
                            // Marked as Unread
                            item.classList.remove('bg-white');
                            item.classList.add('bg-cream-bg');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.add('text-chocolate');
                            // Add dot back if missing
                            if (!h3?.nextElementSibling || h3.nextElementSibling.tagName !== 'SPAN') {
                                const dot = document.createElement('span');
                                dot.className = 'inline-block w-2 h-2 bg-caramel rounded-full ml-2 align-middle';
                                h3?.insertAdjacentElement('afterend', dot);
                            }
                            markReadUnreadBtn.textContent = 'Mark Read';
                        }
                        showToast('Success', 'Notification updated');
                        updateTabCounts();
                    } else {
                        showToast('Error', 'Failed to update notification', 'error');
                    }
                })
                .catch(error => {
                    showToast('Error', 'An error occurred', 'error');
                });
            });
        }

        // Delete
        const deleteBtn = item.querySelector('.delete-notification');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                openConfirmModal('Delete Notification', 'This action cannot be undone.', function() {
                    fetch(`{{ route('purchasing.notifications') }}/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.style.opacity = '0';
                            setTimeout(() => item.remove(), 300);
                            showToast('Deleted', 'Notification removed');
                            updateTabCounts();
                        } else {
                            showToast('Error', data.message || 'Failed to delete notification', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Error', 'An error occurred', 'error');
                    });
                });
            });
        }
    });

    // Bulk Actions Logic
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    const cancelBulk = document.getElementById('cancelBulk');

    function updateBulkBar() {
        const count = document.querySelectorAll('.notification-checkbox:checked').length;
        selectedCount.textContent = count;
        if (count > 0) {
            bulkActionsBar.classList.remove('hidden', 'translate-y-full');
        } else {
            bulkActionsBar.classList.add('hidden', 'translate-y-full');
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));
    
    if(cancelBulk) {
        cancelBulk.addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    function performBulk(operation) {
        const selectedIds = Array.from(document.querySelectorAll('.notification-checkbox:checked')).map(cb => cb.dataset.notificationId);
        if (selectedIds.length === 0) return;

        let title = 'Bulk Action';
        let msg = 'Are you sure?';
        if (operation === 'delete') { title = 'Delete Selected'; msg = `Delete ${selectedIds.length} notifications?`; }
        else { title = 'Update Selected'; msg = `Update ${selectedIds.length} notifications?`; }

        openConfirmModal(title, msg, function() {
            fetch('{{ route("purchasing.notifications.bulk_operations") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: operation,
                    notification_ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    
                    document.querySelectorAll('.notification-checkbox:checked').forEach(cb => {
                        const item = cb.closest('.notification-item');
                        
                        if (operation === 'delete') {
                            item.style.opacity = '0';
                            setTimeout(() => item.remove(), 300);
                        } else if (operation === 'mark_read') {
                            item.dataset.read = 'true';
                            item.classList.remove('bg-cream-bg');
                            item.classList.add('bg-white');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.remove('text-chocolate');
                            const dot = h3?.nextElementSibling;
                            if (dot && dot.tagName === 'SPAN') dot.remove();
                            const btn = item.querySelector('.mark-read-unread');
                            if (btn) btn.textContent = 'Mark Unread';
                        } else if (operation === 'mark_unread') {
                            item.dataset.read = 'false';
                            item.classList.remove('bg-white');
                            item.classList.add('bg-cream-bg');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.add('text-chocolate');
                            if (!h3?.nextElementSibling || h3.nextElementSibling.tagName !== 'SPAN') {
                                const dot = document.createElement('span');
                                dot.className = 'inline-block w-2 h-2 bg-caramel rounded-full ml-2 align-middle';
                                h3?.insertAdjacentElement('afterend', dot);
                            }
                            const btn = item.querySelector('.mark-read-unread');
                            if (btn) btn.textContent = 'Mark Read';
                        }
                    });
                    
                    checkboxes.forEach(cb => cb.checked = false);
                    updateBulkBar();
                    updateTabCounts();
                } else {
                    showToast('Error', data.message || 'Bulk operation failed', 'error');
                }
            })
            .catch(error => {
                showToast('Error', 'An error occurred during bulk operation', 'error');
            });
        });
    }

    document.getElementById('bulkMarkRead')?.addEventListener('click', () => performBulk('mark_read'));
    document.getElementById('bulkMarkUnread')?.addEventListener('click', () => performBulk('mark_unread'));
    document.getElementById('bulkDelete')?.addEventListener('click', () => performBulk('delete'));
});
</script>
@endsection