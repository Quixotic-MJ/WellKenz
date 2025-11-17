@extends('Employee.layout.app')

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
                <p class="text-sm text-gray-500 mt-1">All relevant activity updates for you</p>
            </div>
        </div>
    </div>

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

        {{-- ****** START: THIS IS THE NEW NOTIFICATION LIST (replaces <table>) ****** --}}
        <div class="divide-y divide-gray-200" id="notificationsTableBody">
            @forelse($notifications as $n)
                @php
                    // Set icon and color based on module
                    $icon = [
                        'requisition' => 'fas fa-file-alt',
                        'item_request' => 'fas fa-tags',
                        'acknowledgment' => 'fas fa-receipt',
                        'announcement' => 'fas fa-bullhorn',
                    ][$n->related_type] ?? 'fas fa-bell';

                    $iconColor = [
                        'requisition' => 'text-blue-500 bg-blue-50',
                        'item_request' => 'text-indigo-500 bg-indigo-50',
                        'acknowledgment' => 'text-green-500 bg-green-50',
                        'announcement' => 'text-purple-500 bg-purple-50',
                    ][$n->related_type] ?? 'text-gray-500 bg-gray-50';
                @endphp

                {{-- This div replaces the <tr>. All data- attributes are preserved for JS filtering --}}
                <div class="notif-row flex items-start p-6 {{ !$n->is_read ? 'bg-blue-50 hover:bg-blue-100' : 'bg-white hover:bg-gray-50' }} transition"
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
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-bell text-3xl mb-3 opacity-50"></i>
                    <p>No notifications found.</p>
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

    @include('Employee.Notification.view')

</div>

{{-- ****** START: JAVASCRIPT UPDATES ****** --}}
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
        r.style.display = ok ? 'flex' : 'none'; // <-- Updated from '' to 'flex'
        if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function filterRead(val){
    const rows = document.querySelectorAll('.notif-row');
    let visible = 0;
    rows.forEach(r=>{
        const ok = val==='all' || r.dataset.read===val;
        r.style.display = ok ? 'flex' : 'none'; // <-- Updated from '' to 'flex'
        if(ok) visible++;
    });
    document.getElementById('visibleCount').textContent = visible;
}
function searchTable(q){
    const Q = q.toLowerCase(); const rows = document.querySelectorAll('.notif-row'); let visible=0;
    rows.forEach(r=>{
        const ok = r.textContent.toLowerCase().includes(Q);
        r.style.display = ok ? 'flex' : 'none'; // <-- Updated from '' to 'flex'
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
    const rows=Array.from(tbody.querySelectorAll('.notif-row:not([style*="display: none"])')); // <-- Updated from 'tr' to '.notif-row'
    rows.sort((a,b)=>{
        const A=a.dataset[f], B=b.dataset[f];
        return sortDir==='asc'?A.localeCompare(B):B.localeCompare(A);
    });
    rows.forEach(r=>tbody.appendChild(r));
    // Note: Sorting headers are removed, so this part won't run, which is fine.
    document.querySelectorAll('thead th i').forEach(i=>i.className='fas fa-sort ml-1 text-xs');
    const th=document.querySelector(`th[onclick="sortTable('${f}')"] i`);
    if(th) th.className=sortDir==='asc'?'fas fa-sort-up ml-1 text-xs':'fas fa-sort-down ml-1 text-xs';
}

/* modal openers */
function openViewModal(id){
    currentId=id;
    // **FIX**: Added fetch logic to load notification data
    // **This assumes you will create a route at /staff/notifications/{id} in your web.php**
    fetch(`/staff/notifications/${id}`, {
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.ok?r.json():Promise.reject(r))
    .then(res=>{
        const body = document.getElementById('viewNotificationBody');
        if(res.success){ // Assuming a {success: true, notification: {...}} structure
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
    .catch(()=>showMessage('Error loading notification. The controller route might be missing.','error'));
}

function markRead(id){
    // **FIX**: Changed URL from /employee/... to /staff/... to match web.php
    fetch(`/staff/notifications/${id}/mark-read`,{
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
            setTimeout(()=>location.reload(),500);
        }else{
            showMessage(res.message||'Error: The controller route might be missing.','error');
        }
    })
    .catch(()=>showMessage('Error: The controller route might be missing.','error'));
}
</script>
{{-- ****** END: JAVASCRIPT UPDATES ****** --}}
@endsection