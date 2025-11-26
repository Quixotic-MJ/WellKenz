 @extends('Employee.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 relative pb-20">

    {{-- 1. HEADER & TABS --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500 mt-1">Stay updated with production alerts and system messages.</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="markAllAsRead" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-check-double mr-2"></i> Mark all read
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex overflow-x-auto bg-gray-50 px-6">
            @php
                $tabs = [
                    'all' => ['label' => 'All', 'count' => $stats['total'], 'icon' => 'fas fa-list'],
                    'approvals' => ['label' => 'Approvals', 'count' => $stats['approvals'] ?? 0, 'icon' => 'fas fa-clipboard-check'],
                    'fulfillments' => ['label' => 'Fulfillment', 'count' => $stats['fulfillments'] ?? 0, 'icon' => 'fas fa-box'],
                    'unread' => ['label' => 'Unread', 'count' => $stats['unread'], 'icon' => 'fas fa-envelope'],
                    'high' => ['label' => 'High Priority', 'count' => $stats['high_priority'], 'icon' => 'fas fa-exclamation-triangle'],
                    'urgent' => ['label' => 'Urgent', 'count' => $stats['urgent'], 'icon' => 'fas fa-fire'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ request()->fullUrlWithQuery(['filter' => $key]) }}" 
                   class="flex items-center py-4 px-4 border-b-2 text-sm font-medium whitespace-nowrap transition-colors
                   {{ $filter === $key ? 'border-chocolate text-chocolate' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="{{ $tab['icon'] }} mr-2 text-xs"></i>
                    {{ $tab['label'] }}
                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $filter === $key ? 'bg-chocolate/10 text-chocolate' : 'bg-gray-200 text-gray-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- 2. NOTIFICATION LIST --}}
    <div class="space-y-3">
        @forelse(($notifications ?? collect()) as $notification)
            <div class="notification-item group relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-200 {{ !$notification->is_read ? 'bg-blue-50/30' : '' }}"
                 data-notification-id="{{ $notification->id }}"
                 data-read="{{ $notification->is_read ? 'true' : 'false' }}">
                
                <div class="flex items-start gap-4">
                    {{-- Checkbox --}}
                    <div class="pt-1 flex items-center h-full">
                        <input type="checkbox" 
                               class="notification-checkbox h-4 w-4 text-chocolate focus:ring-chocolate border-gray-300 rounded cursor-pointer transition-transform transform hover:scale-110"
                               data-notification-id="{{ $notification->id }}">
                    </div>

                    {{-- Icon --}}
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $notification->getIconClass() }} shadow-sm ring-4 ring-white">
                            <i class="{{ explode(' ', $notification->getIconClass())[0] }}"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 {{ !$notification->is_read ? 'text-chocolate' : '' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    @if(!$notification->is_read)
                                        <span class="inline-block w-2 h-2 bg-chocolate rounded-full"></span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $notification->getPriorityBadgeColor() }}">
                                        {{ $notification->priority }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $notification->message }}</p>
                            </div>
                            
                            <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">
                                {{ $notification->getTimeAgoAttribute() }}
                            </span>
                        </div>

                        {{-- Metadata & Actions Row --}}
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                @if($notification->metadata && count($notification->metadata) > 0)
                                    @foreach(array_slice($notification->metadata, 0, 3) as $key => $value)
                                        <div class="flex items-center bg-gray-100 px-2 py-1 rounded">
                                            <span class="font-medium text-gray-700 mr-1">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> 
                                            <span class="truncate max-w-[150px]">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                @if($notification->expires_at && $notification->expires_at->isBefore(now()->addDays(1)))
                                    <span class="text-amber-600 flex items-center">
                                        <i class="fas fa-clock mr-1"></i> Expires {{ $notification->expires_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            {{-- Hover Actions --}}
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                @if($notification->action_url)
                                    <a href="{{ $notification->action_url }}" class="inline-flex items-center text-xs font-medium text-chocolate hover:text-chocolate-dark underline decoration-chocolate/30">
                                        <i class="fas fa-external-link-alt mr-1"></i>
                                        {{ $notification->getActionButtonText() }}
                                    </a>
                                    <span class="text-gray-300">|</span>
                                @endif
                                
                                <button class="mark-read-unread text-xs font-medium text-gray-500 hover:text-chocolate transition-colors" title="{{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}">
                                    <i class="fas fa-envelope{{ $notification->is_read ? '-open' : '' }} mr-1"></i>
                                    {{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}
                                </button>
                                <span class="text-gray-300">|</span>
                                <button class="delete-notification text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-bell text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                    <p class="text-sm text-gray-500 text-center max-w-sm">
                        @if($filter === 'all')
                            You don't have any notifications yet. You'll receive updates about production alerts, requisitions, and system messages here.
                        @elseif($filter === 'unread')
                            You're all caught up! No unread notifications to show.
                        @elseif($filter === 'high')
                            No high priority notifications at the moment.
                        @else
                            No urgent notifications to display.
                        @endif
                    </p>
                    @if($filter !== 'all')
                        <a href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}" 
                           class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                            <i class="fas fa-list mr-2"></i> View All Notifications
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    {{-- 3. PAGINATION --}}
    @if(isset($notifications) && method_exists($notifications, 'hasPages') && $notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->appends(['filter' => $filter])->links() }}
        </div>
    @elseif(!empty($notifications) && $notifications->count() > 0)
        {{-- Show pagination info for cases where notifications exist but no links needed --}}
        <div class="bg-white px-4 py-3 flex items-center justify-between border border-gray-200 rounded-lg shadow-sm sm:px-6 mt-6">
            <p class="text-sm text-gray-700">
                Showing 
                <span class="font-medium">{{ $notifications->firstItem() ?? 1 }}</span> 
                to 
                <span class="font-medium">{{ $notifications->lastItem() ?? $notifications->count() }}</span> 
                of 
                <span class="font-medium">{{ $notifications->total() }}</span> 
                notifications
            </p>
        </div>
    @endif

    {{-- 4. STICKY BULK ACTIONS BAR --}}
    <div id="bulkActionsBar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40 hidden transition-all duration-300 ease-in-out">
        <div class="bg-gray-900 text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6">
            <div class="flex items-center gap-2 text-sm font-medium border-r border-gray-700 pr-6">
                <div class="bg-white text-gray-900 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold" id="selectedCount">0</div>
                <span>Selected</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="bulkMarkRead" class="p-2 rounded-full hover:bg-gray-700 text-gray-300 hover:text-white transition-colors tooltip" title="Mark Read">
                    <i class="fas fa-envelope-open"></i>
                </button>
                <button id="bulkMarkUnread" class="p-2 rounded-full hover:bg-gray-700 text-gray-300 hover:text-white transition-colors tooltip" title="Mark Unread">
                    <i class="fas fa-envelope"></i>
                </button>
                <button id="bulkDelete" class="p-2 rounded-full hover:bg-red-600/20 text-gray-300 hover:text-red-400 transition-colors tooltip" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <button id="cancelBulk" class="ml-2 text-xs text-gray-400 hover:text-white uppercase font-bold tracking-wide">
                Cancel
            </button>
        </div>
    </div>

</div>

{{-- UI COMPONENTS --}}

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-chocolate/10 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation text-chocolate"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-white border-l-4 border-chocolate rounded shadow-lg p-4 flex items-center w-80">
        <div class="flex-shrink-0">
            <i id="toastIcon" class="fas fa-check-circle text-chocolate"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-gray-900" id="toastTitle">Success</p>
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
    let confirmCallback = null;

    // Update tab counts after actions
    function updateTabCounts() {
        // This would typically fetch updated counts from the server
        // For now, we'll recalculate based on current UI state
        const totalItems = document.querySelectorAll('.notification-item').length;
        const unreadItems = document.querySelectorAll('.notification-item[data-read="false"]').length;
        
        // You could also make an AJAX call to get actual counts from server
        // fetch('/employee/notifications/counts')
        //     .then(response => response.json())
        //     .then(data => {
        //         document.querySelector('[data-filter="all"] .badge')?.textContent = data.total;
        //         document.querySelector('[data-filter="unread"] .badge')?.textContent = data.unread;
        //     });
    }

    /* --- UI HELPER FUNCTIONS --- */

    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        
        document.getElementById('toastTitle').innerText = title;
        document.getElementById('toastMessage').innerText = message;
        
        // Reset classes
        icon.className = type === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500';
        toast.querySelector('.border-l-4').className = `bg-white border-l-4 rounded shadow-lg p-4 flex items-center w-80 ${type === 'success' ? 'border-green-500' : 'border-red-500'}`;

        toast.classList.remove('hidden');
        // Trigger reflow
        void toast.offsetWidth;
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
                    // Actual API call
                    fetch('{{ route("employee.notifications.mark-all-read") }}', {
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
                            showToast('Success', 'All notifications marked as read');
                            // Update UI
                            document.querySelectorAll('.notification-item').forEach(item => {
                                if (item.dataset.read === 'false') {
                                    item.dataset.read = 'true';
                                    item.classList.remove('bg-blue-50/30');
                                    const h3 = item.querySelector('h3');
                                    if (h3) h3.classList.remove('text-chocolate');
                                    const dot = h3?.nextElementSibling;
                                    if (dot && dot.tagName === 'SPAN') dot.remove();
                                    const btn = item.querySelector('.mark-read-unread');
                                    if (btn) btn.textContent = 'Mark Unread';
                                }
                            });
                            // Update tab counts
                            updateTabCounts();
                        } else {
                            showToast('Error', 'Failed to mark notifications as read', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred while updating notifications', 'error');
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
                
                // Actual API call
                fetch(`/employee/notifications/${notificationId}/mark-${action}`, {
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
                            item.classList.remove('bg-blue-50/30');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.remove('text-chocolate');
                            const dot = h3?.nextElementSibling;
                            if (dot && dot.tagName === 'SPAN') dot.remove();
                            markReadUnreadBtn.textContent = 'Mark Unread';
                        } else {
                            // Marked as Unread
                            item.classList.add('bg-blue-50/30');
                            const h3 = item.querySelector('h3');
                            if (h3) h3.classList.add('text-chocolate');
                            markReadUnreadBtn.textContent = 'Mark Read';
                        }
                        showToast('Success', 'Notification updated');
                        updateTabCounts();
                    } else {
                        showToast('Error', 'Failed to update notification', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred while updating notification', 'error');
                });
            });
        }

        // Delete
        const deleteBtn = item.querySelector('.delete-notification');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                openConfirmModal('Delete Notification', 'This action cannot be undone.', function() {
                    // Demo mode - simulate deletion
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                    showToast('Deleted', 'Notification removed');
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
            // Demo mode - simulate bulk operation
            setTimeout(() => {
                showToast('Success', `${selectedIds.length} notifications ${operation.replace('_', ' ')}`);
                if (operation === 'delete') {
                    document.querySelectorAll('.notification-checkbox:checked').forEach(cb => {
                        const item = cb.closest('.notification-item');
                        item.style.opacity = '0';
                        setTimeout(() => item.remove(), 300);
                    });
                }
                checkboxes.forEach(cb => cb.checked = false);
                updateBulkBar();
            }, 500);
        });
    }

    document.getElementById('bulkMarkRead')?.addEventListener('click', () => performBulk('mark_read'));
    document.getElementById('bulkMarkUnread')?.addEventListener('click', () => performBulk('mark_unread'));
    document.getElementById('bulkDelete')?.addEventListener('click', () => performBulk('delete'));
});
</script>
@endsection