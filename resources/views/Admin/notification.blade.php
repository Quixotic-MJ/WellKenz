@extends('Admin.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-24 font-sans text-gray-600 relative">

    {{-- 1. HEADER & TABS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Notifications</h1>
            <p class="text-sm text-gray-500">Stay updated with system alerts, order updates, and activity logs.</p>
        </div>
        <div>
            <button id="markAllAsRead" class="inline-flex items-center justify-center px-5 py-2.5 bg-white border border-border-soft text-chocolate text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm group">
                <i class="fas fa-check-double mr-2 opacity-70 group-hover:opacity-100"></i> Mark all read
            </button>
        </div>
    </div>

    {{-- 2. FILTER TABS --}}
    <div class="border-b border-border-soft">
        <nav class="-mb-px flex space-x-8 overflow-x-auto no-scrollbar" aria-label="Tabs">
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
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-2
                   {{ $filter === $key 
                        ? 'border-chocolate text-chocolate font-bold' 
                        : 'border-transparent text-gray-500 hover:text-caramel hover:border-caramel/50' }}">
                    {{ $tab['label'] }}
                    @if($tab['count'] > 0)
                        <span class="ml-1 py-0.5 px-2.5 rounded-full text-[10px] 
                            {{ $filter === $key ? 'bg-chocolate text-white' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    {{-- 3. NOTIFICATION LIST --}}
    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div class="notification-item group relative bg-white border border-border-soft rounded-xl p-5 hover:shadow-md transition-all duration-300
                        {{ !$notification->is_read ? 'bg-cream-bg border-l-4 border-l-caramel' : 'border-l-4 border-l-transparent' }}"
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
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm ring-4 ring-white
                            {{ str_contains($notification->getIconClass(), 'bg-') ? $notification->getIconClass() : 'bg-white text-chocolate border border-border-soft' }}">
                            {{-- Assuming getIconClass returns font awesome classes or bg classes --}}
                            <i class="{{ explode(' ', $notification->getIconClass())[0] ?? 'fas fa-bell' }}"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h3 class="text-sm font-bold {{ !$notification->is_read ? 'text-chocolate' : 'text-gray-900' }}">
                                        {{ $notification->title }}
                                    </h3>
                                    @if(!$notification->is_read)
                                        <span class="inline-block w-2 h-2 bg-caramel rounded-full animate-pulse" title="Unread"></span>
                                    @endif
                                    
                                    {{-- Priority Badge --}}
                                    @if($notification->priority !== 'normal')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border
                                            {{ $notification->priority === 'urgent' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                            {{ $notification->priority }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $notification->message }}</p>
                                
                                {{-- Metadata Row --}}
                                @if($notification->metadata && count($notification->metadata) > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach(array_slice($notification->metadata, 0, 3) as $key => $value)
                                        <div class="inline-flex items-center px-2 py-1 rounded bg-gray-50 border border-gray-100 text-xs text-gray-500">
                                            <span class="font-bold mr-1 text-chocolate/80">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> 
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
                                    {{ $notification->time_ago }}
                                </span>
                                
                                <div class="flex items-center gap-3 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="text-xs font-bold text-chocolate hover:text-caramel underline decoration-chocolate/30 underline-offset-2 transition-colors">
                                            View
                                        </a>
                                        <span class="text-gray-300 text-xs">|</span>
                                    @endif
                                    
                                    <button class="mark-read-unread text-xs font-medium text-gray-500 hover:text-chocolate transition-colors" 
                                            title="{{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}">
                                        {{ $notification->is_read ? 'Unread' : 'Read' }}
                                    </button>
                                    
                                    <button class="delete-notification text-gray-400 hover:text-red-600 transition-colors ml-1" title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="bg-white rounded-xl border border-dashed border-border-soft p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                    <i class="fas fa-bell-slash text-chocolate/40 text-2xl"></i>
                </div>
                <h3 class="font-display text-lg font-bold text-chocolate">All caught up!</h3>
                <p class="text-gray-500 text-sm mt-1">
                    @if($filter === 'all')
                        You have no notifications at the moment.
                    @else
                        No {{ $filter }} notifications found. <a href="{{ route('admin.notifications.index') }}" class="text-caramel hover:text-chocolate font-medium">View all</a>.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- 4. PAGINATION --}}
    @if($notifications->hasPages())
        <div class="pt-4 border-t border-border-soft">
            {{ $notifications->appends(['filter' => $filter])->links() }}
        </div>
    @endif

    {{-- 5. STICKY BULK ACTIONS BAR --}}
    <div id="bulkActionsBar" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-40 hidden transition-all duration-300 ease-in-out translate-y-20 opacity-0">
        <div class="bg-chocolate text-white rounded-full shadow-2xl px-6 py-3 flex items-center gap-6 border border-chocolate-dark">
            <div class="flex items-center gap-3 text-sm font-medium border-r border-white/20 pr-6">
                <div class="bg-white text-chocolate rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-sm" id="selectedCount">0</div>
                <span class="font-display tracking-wide">Selected</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="bulkMarkRead" class="p-2 rounded-full hover:bg-white/10 text-white/90 hover:text-white transition-colors tooltip" title="Mark Read">
                    <i class="fas fa-envelope-open"></i>
                </button>
                <button id="bulkMarkUnread" class="p-2 rounded-full hover:bg-white/10 text-white/90 hover:text-white transition-colors tooltip" title="Mark Unread">
                    <i class="fas fa-envelope"></i>
                </button>
                <button id="bulkDelete" class="p-2 rounded-full hover:bg-red-500/20 text-white/90 hover:text-red-200 transition-colors tooltip" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <button id="cancelBulk" class="ml-2 text-xs text-white/60 hover:text-white uppercase font-bold tracking-widest transition-colors">
                Cancel
            </button>
        </div>
    </div>

</div>

{{-- UI COMPONENTS --}}

<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-cream-bg border border-border-soft sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation text-caramel text-lg"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confirmTitle">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300 translate-y-full opacity-0">
    <div class="bg-white border-l-4 border-chocolate rounded-lg shadow-xl p-4 flex items-center w-80 ring-1 ring-black/5">
        <div class="flex-shrink-0">
            <i id="toastIcon" class="fas fa-check-circle text-chocolate text-xl"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-bold text-chocolate" id="toastTitle">Success</p>
            <p class="text-xs text-gray-500" id="toastMessage">Operation completed.</p>
        </div>
        <button onclick="hideToast()" class="ml-auto text-gray-400 hover:text-chocolate transition-colors focus:outline-none">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let confirmCallback = null;

    /* --- UI HELPER FUNCTIONS --- */

    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        const border = toast.querySelector('.border-l-4');
        
        document.getElementById('toastTitle').innerText = title;
        document.getElementById('toastMessage').innerText = message;
        
        // Reset classes
        icon.className = type === 'success' ? 'fas fa-check-circle text-green-500 text-xl' : 'fas fa-exclamation-circle text-red-500 text-xl';
        
        // Remove old border colors
        border.classList.remove('border-chocolate', 'border-green-500', 'border-red-500');
        // Add new border color
        if(type === 'success') border.classList.add('border-green-500');
        else if(type === 'error') border.classList.add('border-red-500');
        else border.classList.add('border-chocolate');

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
                    fetch('{{ route("admin.notifications.mark_all_read") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Success', 'All notifications marked as read');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    })
                    .catch(error => showToast('Error', 'An error occurred.', 'error'));
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
                const url = isRead ? 
                    `{{ route('admin.notifications.mark_unread', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId) :
                    `{{ route('admin.notifications.mark_read', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optimistic UI Update
                        item.dataset.read = (!isRead).toString();
                        if(!isRead) {
                            // Marked as Read
                            item.classList.remove('bg-cream-bg', 'border-l-caramel');
                            item.classList.add('border-l-transparent');
                            item.querySelector('h3').classList.remove('text-chocolate');
                            item.querySelector('h3').classList.add('text-gray-900');
                            const dot = item.querySelector('h3').nextElementSibling;
                            if(dot && dot.tagName === 'SPAN' && dot.classList.contains('bg-caramel')) dot.remove();
                            markReadUnreadBtn.textContent = 'Unread';
                        } else {
                            // Marked as Unread
                            item.classList.add('bg-cream-bg', 'border-l-caramel');
                            item.classList.remove('border-l-transparent');
                            item.querySelector('h3').classList.add('text-chocolate');
                            item.querySelector('h3').classList.remove('text-gray-900');
                            markReadUnreadBtn.textContent = 'Read';
                        }
                        showToast('Success', 'Notification updated');
                    }
                });
            });
        }

        // Delete
        const deleteBtn = item.querySelector('.delete-notification');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                openConfirmModal('Delete Notification', 'This action cannot be undone.', function() {
                    fetch(`{{ route('admin.notifications.destroy', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            item.style.opacity = '0';
                            setTimeout(() => item.remove(), 300);
                            showToast('Deleted', 'Notification removed');
                        }
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
            bulkActionsBar.classList.remove('hidden', 'translate-y-20', 'opacity-0');
        } else {
            bulkActionsBar.classList.add('translate-y-20', 'opacity-0');
            setTimeout(() => bulkActionsBar.classList.add('hidden'), 300);
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
            fetch('{{ route("admin.notifications.bulk_operations") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_ids: selectedIds,
                    operation: operation
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', data.message, 'error');
                }
            });
        });
    }

    document.getElementById('bulkMarkRead')?.addEventListener('click', () => performBulk('mark_read'));
    document.getElementById('bulkMarkUnread')?.addEventListener('click', () => performBulk('mark_unread'));
    document.getElementById('bulkDelete')?.addEventListener('click', () => performBulk('delete'));
});
</script>
@endsection