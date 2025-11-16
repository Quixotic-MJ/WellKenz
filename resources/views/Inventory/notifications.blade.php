@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">View and manage your notifications</p>
        </div>
        <button onclick="markAllAsRead()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Mark All as Read
        </button>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Recent Notifications</h3>
                <div class="flex space-x-2">
                    <select id="filter-status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="divide-y divide-gray-200" id="notifications-list">
            @forelse($notifications as $notification)
            <div class="p-6 {{ $notification->is_read ? 'bg-white' : 'bg-blue-50' }} hover:bg-gray-50 cursor-pointer" onclick="viewNotification({{ $notification->notif_id }})">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <h4 class="text-sm font-medium text-gray-900">{{ $notification->notif_title }}</h4>
                            @if(!$notification->is_read)
                            <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $notification->notif_content }}</p>
                        <p class="text-xs text-gray-500 mt-2">{{ $notification->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="markAsRead(event, {{ $notification->notif_id }})" class="text-sm text-blue-600 hover:text-blue-700">
                            Mark Read
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-gray-500">
                No notifications found
            </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function viewNotification(id) {
        window.location.href = `{{ route('inventory.notifications.show', ['id' => '']) }}${id}`;
    }

    function markAsRead(event, id) {
        event.stopPropagation();
        fetch(`{{ url('inventory/notifications') }}/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    function markAllAsRead() {
        fetch('{{ route("inventory.notifications.markAllRead") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }
</script>
@endpush
@endsection