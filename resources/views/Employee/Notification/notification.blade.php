@extends('Employee.layout.app')

@section('title', 'Notifications - WellKenz ERP')
@section('breadcrumb', 'Notifications')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500 mt-1">All relevant activity updates for you</p>
            </div>
        </div>
    </div>

    <!-- live counts -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalCount }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Unread</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $unreadCount }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Read</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $readCount }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $thisWeekCount }}</p>
        </div>
    </div>

    <!-- notifications table -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">My Notifications</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterModule(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Types</option>
                    <option value="requisition">Requisition</option>
                    <option value="item_request">Item Request</option>
                    <option value="acknowledgment">Acknowledgment</option>
                    <option value="announcement">Announcement</option>
                </select>
                <select onchange="filterRead(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search notifications…" onkeyup="searchTable(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearSearch()" id="clearBtn" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="notificationsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('date')">Date <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Content</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="notificationsTableBody">
                    @forelse($notifications as $n)
                    <tr class="hover:bg-gray-50 transition notif-row
                        @if(!$n->is_read) bg-blue-50 @endif"
                        data-module="{{ $n->related_type }}"
                        data-read="{{ $n->is_read ? 'read' : 'unread' }}"
                        data-date="{{ $n->created_at->format('Y-m-d H:i') }}"
                        data-notification-id="{{ $n->notif_id }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $n->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst(str_replace('_',' ',$n->related_type)) }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $n->notif_title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($n->notif_content,60) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($n->is_read)
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Read</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Unread</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <!-- Eye icon for viewing -->
                                <button onclick="openViewModal({{ $n->notif_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                
                                <!-- Check mark for marking as read -->
                                @if(!$n->is_read)
                                    <button onclick="markRead({{ $n->notif_id }})"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded transition" title="Mark as Read">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-bell text-3xl mb-3 opacity-50"></i>
                            <p>No notifications found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $notifications->count() }}</span> of {{ $notifications->count() }} notifications
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Employee.Notification.view')

</div>

<script>
/* light helpers */
let currentId = null;

function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}

function closeModals(){
    ['viewNotificationModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}

document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterModule(val){
    const rows = document.querySelectorAll('.notif-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.module===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}

function filterRead(val){
    const rows = document.querySelectorAll('.notif-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.read===val;
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}

function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.notif-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? '' : 'none'; if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
    const btn = document.getElementById('clearBtn');
    Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
}

function clearSearch(){
    document.getElementById('searchInput').value=''; searchTable(''); document.getElementById('clearBtn').classList.add('hidden');
}

/* sort */
let sortField='date', sortDir='desc';
function sortTable(f){
    if(sortField===f) sortDir=sortDir==='asc'?'desc':'asc'; else {sortField=f; sortDir='asc';}
    const tbody=document.getElementById('notificationsTableBody');
    const rows=Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
    rows.sort((a,b)=>{
        const A=a.dataset[f], B=b.dataset[f];
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* mark as read function */
function markRead(id){
    console.log('Marking notification as read:', id);
    
    // Use the correct route that goes to NotificationController
    fetch(`/employee/notifications/${id}/mark-read`, {
        method:'POST',
        headers:{
            'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            showMessage('Marked as read');
            // Update UI without reload
            const row = document.querySelector(`tr[data-notification-id="${id}"]`);
            if (row) {
                // Update status badge
                const statusCell = row.querySelector('td:nth-child(5)');
                statusCell.innerHTML = '<span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Read</span>';
                
                // Remove mark as read button
                const markReadBtn = row.querySelector('button[onclick^="markRead"]');
                if (markReadBtn) markReadBtn.remove();
                
                // Update row data attribute and background
                row.dataset.read = 'read';
                row.classList.remove('bg-blue-50');
                
                // Update counters
                updateNotificationCounters();
            }
        }else{
            showMessage(res.message||'Error','error');
        }
    })
    .catch(()=>showMessage('Error','error'));
}

function updateNotificationCounters() {
    // Update the unread count display
    const unreadRows = document.querySelectorAll('.notif-row[data-read="unread"]');
    const unreadCountElement = document.querySelector('.bg-white.border.border-amber-200 .text-2xl');
    if (unreadCountElement) {
        unreadCountElement.textContent = unreadRows.length;
    }
    
    // Update the read count display
    const readRows = document.querySelectorAll('.notif-row[data-read="read"]');
    const readCountElement = document.querySelector('.bg-white.border.border-green-200 .text-2xl');
    if (readCountElement) {
        readCountElement.textContent = readRows.length;
    }
}

/* modal openers */
function openViewModal(id){
    console.log('=== OPENING VIEW MODAL ===');
    console.log('Notification ID:', id);
    
    currentId = id;
    
    // Show loading state
    document.getElementById('viewNotificationModal').classList.remove('hidden');
    document.getElementById('viewNotificationBody').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Loading notification details...</span>
        </div>
    `;

    // Build the URL
    const url = `/employee/notifications/${id}/details`;
    console.log('Fetching from URL:', url);

    // Fetch notification details via AJAX
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response OK:', response.ok);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Response text:', text);
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Received data:', data);
        
        if (data.success && data.notification) {
            const notification = data.notification;
            console.log('Notification data:', notification);
            
            document.getElementById('viewNotificationBody').innerHTML = `
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Date & Time</label>
                                <p class="text-sm text-gray-900 mt-1">${notification.formatted_date}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Type</label>
                                <p class="text-sm text-gray-900 mt-1">${notification.type_formatted}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                                <p class="text-sm mt-1">
                                    ${notification.is_read ? 
                                        '<span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Read</span>' : 
                                        '<span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Unread</span>'
                                    }
                                </p>
                            </div>
                            ${notification.related_link ? `
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Related To</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    <a href="${notification.related_link}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">View Related Item</a>
                                </p>
                            </div>
                            ` : '<div></div>'}
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Title</label>
                        <h4 class="text-lg font-semibold text-gray-900 mt-1">${notification.notif_title}</h4>
                    </div>
                    
                    <div class="border-t pt-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Content</label>
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700 whitespace-pre-wrap">${notification.notif_content}</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Automatically mark as read when viewing if it's unread
            if (!notification.is_read) {
                console.log('Auto-marking as read:', id);
                markRead(id);
            }
        } else {
            throw new Error(data.message || 'Failed to load notification details');
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        document.getElementById('viewNotificationBody').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"></i>
                <p class="text-red-600 font-semibold">Failed to load notification details</p>
                <p class="text-gray-500 text-sm mt-2">Error: ${error.message}</p>
                <div class="mt-4 space-x-2">
                    <button onclick="openViewModal(${id})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Try Again
                    </button>
                    <button onclick="closeModals()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                        Close
                    </button>
                </div>
            </div>
        `;
    });
}
</script>
@endsection