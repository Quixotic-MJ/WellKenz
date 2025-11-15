@extends('Admin.layout.app')

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
                <p class="text-sm text-gray-500 mt-1">Monitor all activity alerts across the system</p>
            </div>
            <button onclick="openComposeModal()"
                class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-medium rounded">
                <i class="fas fa-bullhorn mr-2"></i>Send Announcement
            </button>
        </div>
    </div>

    <!-- live counts -->
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

    <!-- notifications table -->
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

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="notificationsTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer" onclick="sortTable('date')">Timestamp <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Module</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Content</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="notificationsTableBody">
                    @foreach($notifications as $n)
                    <tr class="hover:bg-gray-50 transition notif-row"
                        data-module="{{ $n->related_type }}"
                        data-read="{{ $n->is_read ? 'read' : 'unread' }}"
                        data-date="{{ $n->created_at->format('Y-m-d H:i') }}">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $n->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst(str_replace('_',' ',$n->related_type)) }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $n->notif_title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($n->notif_content,60) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $n->user->name ?? 'System' }}</td>
                        <td class="px-6 py-4">
                            @if($n->is_read)
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Read</span>
                            @else
                                <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Unread</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <button onclick="openViewModal({{ $n->notification_id }})"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition" title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @if(!$n->is_read)
                                    <button onclick="markRead({{ $n->notification_id }})"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded transition" title="Mark read">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing <span id="visibleCount">{{ $notifications->count() }}</span> of {{ $notifications->total() }} notifications
        </div>
    </div>

    <!-- ====== MODALS  ====== -->
    @include('Admin.Notification.view')
    @include('Admin.Notification.compose')

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

/* modal openers */
function openViewModal(id){
    currentId=id;
    /* ajax fetch then fill modal */
    document.getElementById('viewNotificationModal').classList.remove('hidden');
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
            setTimeout(()=>location.reload(),500);
        }else{
            showMessage(res.message||'Error','error');
        }
    })
    .catch(()=>showMessage('Error','error'));
}
</script>
@endsection