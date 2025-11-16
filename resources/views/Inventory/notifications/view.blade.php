@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('inventory.notifications.index') }}" class="mr-4 text-blue-600 hover:text-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notification Details</h1>
            <p class="text-gray-600 mt-1">View notification details and take actions</p>
        </div>
    </div>

    <!-- Notification Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="border-b border-gray-200 pb-4 mb-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $notification->notif_title }}</h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>{{ $notification->created_at->format('M d, Y H:i') }}</span>
                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
                    </div>
                </div>
                @if(!$notification->is_read)
                <button onclick="markAsRead()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Mark as Read
                </button>
                @endif
            </div>
        </div>
        
        <div class="prose max-w-none">
            <p class="text-gray-700 whitespace-pre-wrap">{{ $notification->notif_message }}</p>
        </div>

        @if($notification->notif_url)
        <div class="mt-6 pt-4 border-t border-gray-200">
            <a href="{{ $notification->notif_url }}" class="text-blue-600 hover:text-blue-700 font-medium">
                Go to related page →
            </a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function markAsRead() {
        fetch('{{ route("inventory.notifications.mark-read", $notification->notification_id) }}', {
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
</script>
@endpush
@endsection