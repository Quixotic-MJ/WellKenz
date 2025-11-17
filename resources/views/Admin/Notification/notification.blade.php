@extends('Admin.layout.app')

@section('title', 'Notifications - WellKenz ERP')
@section('breadcrumb', 'Notifications')

@section('content')
<div class="space-y-6">

    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor all activity alerts across the system</p>
            </div>
            <button onclick="openComposeModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-bullhorn mr-2"></i>Send Announcement
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $totalNotifications ?? 0 }}</p>
        </div>
        <div class="bg-white border border-amber-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Unread</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $unreadNotifications ?? 0 }}</p>
        </div>
        <div class="bg-white border border-green-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Read</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $readNotifications ?? 0 }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wider">This Week</p>
            <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $weekNotifications ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">All Notifications</h3>
            <div class="flex items-center space-x-3">
                <select onchange="filterModule(this.value)" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="all">All Modules</option>
                    <option value="requisition">Requisition</option>
                    <option value="purchase_order">Purchase Order</option>
                    <option value="memo">Memo</option>
                    <option value="item_request">Item Request</option>
                    <option value="inventory">Inventory</option>
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

        {{-- ****** START: THIS IS THE NEW NOTIFICATION LIST ****** --}}
        <div class="divide-y divide-gray-200" id="notificationsTableBody">
            @forelse($notifications as $n)
                @php
                    // Set icon and color based on module
                    $icon = [
                        'requisition' => 'fas fa-file-alt',
                        'item_request' => 'fas fa-tags',
                        'purchase_order' => 'fas fa-shopping-cart',
                        'memo' => 'fas fa-sticky-note',
                        'inventory' => 'fas fa-boxes',
                        'announcement' => 'fas fa-bullhorn',
                    ][$n->related_type] ?? 'fas fa-bell';

                    $iconColor = [
                        'requisition' => 'text-blue-500 bg-blue-50',
                        'item_request' => 'text-indigo-500 bg-indigo-50',
                        'purchase_order' => 'text-green-500 bg-green-50',
                        'memo' => 'text-yellow-500 bg-yellow-50',
                        'inventory' => 'text-gray-500 bg-gray-50',
                        'announcement' => 'text-purple-500 bg-purple-50',
                    ][$n->related_type] ?? 'text-gray-500 bg-gray-50';
                @endphp

                {{-- This div replaces the <tr>. All data- attributes are preserved for JS filtering --}}
                <div class="notif-row flex items-start p-6 {{ !$n->is_read ? 'bg-amber-50 hover:bg-amber-100' : 'bg-white hover:bg-gray-50' }} transition"
                     data-module="{{ $n->related_type }}"
                     data-read="{{ $n->is_read ? 'read' : 'unread' }}"
                     data-date="{{ $n->created_at->format('Y-m-d H:i') }}">
                    
                    <div class="mr-4 pt-1 flex-shrink-0">
                        <span class="flex items-center justify-center h-10 w-10 rounded-full {{ $iconColor }}">
                            <i class="{{ $icon }}"></i>
                        </span>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">{{ $n->notif_title }}</p>
                            <span class="text-xs text-gray-500 flex-shrink-0 ml-4">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $n->notif_content }}</p>
                        <div class="text-xs text-gray-500 mt-2">
                            <span>User: <strong>{{ $n->user->name ?? 'System' }}</strong></span>
                            <span class="mx-2">|</span>
                            <span>Module: <strong>{{ ucfirst(str_replace('_',' ',$n->related_type)) }}</strong></span>
                        </div>
                    </div>

                    <div class="ml-4 pl-4 flex-shrink-0 pt-1">
                        <div class="flex items-center space-x-2">
                            <button onclick="openViewModal({{ $n->notif_id }})"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            @if(!$n->is_read)
                                <button onclick="markRead({{ $n->notif_id }})"
                                    class="p-2 text-green-600 hover:bg-green-50 rounded transition" title="Mark read">
                                    <i class="fas fa-check text-sm"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    No notifications found.
                </div>
            @endforelse
        </div>
        {{-- ****** END: NEW NOTIFICATION LIST ****** --}}


        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $notifications->count() }}</span> of {{ $notifications->total() }} notifications
        </div>
        
        {{-- Add pagination links --}}
        @if ($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    @include('Admin.Notification.view')
    @include('Admin.Notification.compose')

</div>

{{-- The JavaScript is unchanged as it works with the new structure --}}
<script>
/* light helpers */
let currentId = null;

function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
function closeModals(){
    ['viewNotificationModal','composeNotificationModal'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
    currentId = null;
}
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeModals(); });

/* search / filter */
function filterModule(val){
    const rows = document.querySelectorAll('.notif-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.module===val;
        r.style.display = ok ? 'flex' : 'none'; // Use 'flex' instead of ''
        if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterRead(val){
    const rows = document.querySelectorAll('.notif-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.read===val;
        r.style.display = ok ? 'flex' : 'none'; // Use 'flex' instead of ''
        if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.notif-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? 'flex' : 'none'; // Use 'flex' instead of ''
        if(ok) visible++;
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
    const rows=Array.from(tbody.querySelectorAll('.notif-row')); // Changed from <tr>
    rows.sort((a,b)=>{
        const A=a.dataset[f], B=b.dataset[f];
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* modal openers */
function openViewModal(id){
    currentId=id;
    fetch(`/admin/notifications/${id}`,{
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.ok?r.json():Promise.reject(r))
    .then(res=>{
        const body = document.getElementById('viewNotificationBody');
        if(res.success){
            const n = res.notification;
            body.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <p class="text-gray-900 font-semibold">${n.title}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded">${n.content}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                            <p class="text-gray-900">${n.related_type || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <p class="text-gray-900">${n.user || 'System'}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timestamp</label>
                        <p class="text-gray-900">${n.created_at}</p>
                    </div>
                </div>`;
            document.getElementById('viewNotificationModal').classList.remove('hidden');
        }else{
            showMessage('Error loading notification details','error');
        }
    })
    .catch(()=>showMessage('Error loading notification','error'));
}
function openComposeModal(){
    document.getElementById('composeNotificationModal').classList.remove('hidden');
}

/* mark read */
function markRead(id){
    fetch(`/admin/notifications/${id}/mark-read`,{
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
            setTimeout(()=>location.reload(),500); // Reload to show the new "read" state
        }else{
            showMessage(res.message||'Error','error');
        }
    })
    .catch(()=>showMessage('Error','error'));
}

// Simple handler for the compose modal
document.addEventListener('DOMContentLoaded', function() {
    const composeForm = document.querySelector('#composeNotificationModal form');
    if (composeForm) {
        composeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModals();
                    showMessage('Announcement sent successfully!');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showMessage(res.message || 'Failed to send announcement', 'error');
                }
            })
            .catch(() => showMessage('An error occurred', 'error'));
        });
    }
});
</script>
@endsection