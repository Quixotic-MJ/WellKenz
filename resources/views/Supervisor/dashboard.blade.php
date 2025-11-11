@extends('Supervisor.layout.app')

@section('title', 'Dashboard - WellKenz ERP')
@section('breadcrumb', 'Dashboard')

@section('content')
@php
/* ----------  KPI DATA  ---------- */
$pendingReqs      = DB::table('requisitions')->where('req_status', 'pending')->count();
$approvedWeek     = DB::table('requisitions')
                        ->where('req_status', 'approved')
                        ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count();
$activePOs        = DB::table('purchase_orders')->whereIn('po_status', ['ordered', 'delivered'])->count();
$lowStockItems    = DB::table('items')
                        ->whereRaw('item_stock <= reorder_level')
                        ->where('is_active', true)
                        ->count();
$activeEmployees  = DB::table('users')->where('status', 'active')->count();

/* ----------  PENDING APPROVALS  ---------- */
$pendingList = DB::table('requisitions as r')
    ->join('users as u', 'u.user_id', '=', 'r.requested_by')
    ->where('r.req_status', 'pending')
    ->select('r.req_id','r.req_ref','u.name as employee','u.position as dept_name',
             'r.req_purpose','r.req_priority','r.created_at')
    ->orderBy('r.created_at','asc')
    ->limit(10)
    ->get();

/* ----------  RECENT POs  ---------- */
$recentPOs = DB::table('purchase_orders as po')
    ->join('suppliers as s', 's.sup_id', '=', 'po.sup_id')
    ->leftJoin('requisitions as r', 'r.req_id', '=', 'po.req_id')
    ->select('po.po_ref','s.sup_name as supplier_name','po.total_amount','po.po_status',
             'po.expected_delivery_date as delivery_date','r.req_ref')
    ->orderBy('po.created_at','desc')
    ->limit(10)
    ->get();

/* ----------  INVENTORY SNAPSHOT  ---------- */
$inventorySnap = DB::table('items as i')
    ->select('i.item_name','i.item_stock','i.reorder_level')
    ->where('i.is_active', true)
    ->orderBy('i.item_name')
    ->limit(15)
    ->get();

/* ----------  NOTIFICATIONS  ---------- */
$notifications = DB::table('notifications as n')
    ->join('users as u', 'u.user_id', '=', 'n.user_id')
    ->where('n.user_id', auth()->id())
    ->where('n.is_read', false)
    ->select('n.notif_content as message','n.created_at','n.notif_title as sender')
    ->orderBy('n.created_at','desc')
    ->limit(5)
    ->get();
@endphp

<div class="space-y-6">
    {{-- 1.  HEADER  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Welcome back, {{ session('emp_name') }}!</h1>
                <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y • g:i A') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('Supervisor_Requisition') }}" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 text-sm font-medium rounded"><i class="fas fa-file-signature mr-2"></i>View Requisitions</a>
                <a href="{{ route('Supervisor_Purchase_Order') }}" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium text-gray-700 rounded"><i class="fas fa-shopping-cart mr-2"></i>Purchase Orders</a>
                <a href="{{ route('Supervisor_Report') }}" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-sm font-medium text-gray-700 rounded"><i class="fas fa-chart-bar mr-2"></i>Reports</a>
            </div>
        </div>
    </div>

    {{-- 2.  KPI CARDS  --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['title'=>'Pending Requisitions','value'=>$pendingReqs,'icon'=>'fas fa-clock','bg'=>'bg-amber-50','text'=>'text-amber-700','url'=>route('Supervisor_Requisition')],
            ['title'=>'Approved This Week','value'=>$approvedWeek,'icon'=>'fas fa-check-circle','bg'=>'bg-green-50','text'=>'text-green-700'],
            ['title'=>'Active Purchase Orders','value'=>$activePOs,'icon'=>'fas fa-shopping-cart','bg'=>'bg-blue-50','text'=>'text-blue-700','url'=>route('Supervisor_Purchase_Order')],
            ['title'=>'Low-Stock Items','value'=>$lowStockItems,'icon'=>'fas fa-exclamation-triangle','bg'=>'bg-red-50','text'=>'text-red-700','url'=>route('items.low_stock')],
            ['title'=>'Active Employees','value'=>$activeEmployees,'icon'=>'fas fa-users','bg'=>'bg-gray-50','text'=>'text-gray-700']
        ] as $kpi)
        <a @if(isset($kpi['url'])) href="{{ $kpi['url'] }}" @endif class="block p-4 rounded-lg border border-gray-200 {{ $kpi['bg'] }} hover:shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs {{ $kpi['text'] }} uppercase tracking-wider">{{ $kpi['title'] }}</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $kpi['value'] }}</p>
                </div>
                <div class="w-10 h-10 {{ $kpi['bg'] }} flex items-center justify-center rounded"><i class="{{ $kpi['icon'] }} {{ $kpi['text'] }}"></i></div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- 3.  PENDING APPROVALS  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Pending Approvals</h3>
            <a href="{{ route('Supervisor_Requisition') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View All →</a>
        </div>
        @if($pendingList->isEmpty())
            <p class="text-sm text-gray-500">No requisitions awaiting approval.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th>Req No</th><th>Employee</th><th>Position</th><th>Purpose</th><th>Priority</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pendingList as $r)
                        <tr>
                            <td class="px-3 py-2">{{ $r->req_ref }}</td>
                            <td class="px-3 py-2">{{ $r->employee }}</td>
                            <td class="px-3 py-2">{{ $r->dept_name }}</td>
                            <td class="px-3 py-2 truncate max-w-xs" title="{{ $r->req_purpose }}">{{ \Illuminate\Support\Str::limit($r->req_purpose,30) }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-xs rounded @if($r->req_priority=='high') bg-red-100 text-red-700 @elseif($r->req_priority=='medium') bg-amber-100 text-amber-700 @else bg-gray-100 text-gray-700 @endif">{{ ucfirst($r->req_priority) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</td>
                            <td class="px-3 py-2 flex gap-2">
                                <button onclick="quickApprove({{ $r->req_id }})" class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Approve</button>
                                <button onclick="quickReject({{ $r->req_id }})" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Reject</button>
                                <button onclick="reviewRequisition({{ $r->req_id }})" class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded hover:bg-gray-200">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 4.  RECENT PURCHASE ORDERS  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Purchase Orders</h3>
            <a href="{{ route('Supervisor_Purchase_Order') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View All →</a>
        </div>
        @if($recentPOs->isEmpty())
            <p class="text-sm text-gray-500">No purchase orders yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th>PO No</th><th>Supplier</th><th>Amount</th><th>Status</th><th>Delivery</th><th>Linked Req</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentPOs as $po)
                        <tr>
                            <td class="px-3 py-2">{{ $po->po_ref }}</td>
                            <td class="px-3 py-2">{{ $po->supplier_name }}</td>
                            <td class="px-3 py-2">₱{{ number_format($po->total_amount,2) }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-1 text-xs rounded @if($po->po_status=='ordered') bg-amber-100 text-amber-700 @elseif($po->po_status=='delivered') bg-green-100 text-green-700 @else bg-gray-100 text-gray-700 @endif">{{ ucfirst($po->po_status) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('M d, Y') : '-' }}</td>
                            <td class="px-3 py-2">{{ $po->req_ref ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 5.  INVENTORY SNAPSHOT  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Inventory Snapshot</h3>
            <a href="{{ route('items.low_stock') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View Full Inventory →</a>
        </div>
        @if($inventorySnap->isEmpty())
            <p class="text-sm text-gray-500">No inventory data available.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase"><tr><th>Item</th><th>Current Stock</th><th>Reorder Level</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($inventorySnap as $inv)
                        @php
                            $status = 'Sufficient'; $badge = 'bg-green-100 text-green-700';
                            if ($inv->item_stock <= 0) { $status = 'Out of Stock'; $badge = 'bg-red-100 text-red-700'; }
                            elseif ($inv->item_stock <= $inv->reorder_level) { $status = 'Low Stock'; $badge = 'bg-amber-100 text-amber-700'; }
                        @endphp
                        <tr>
                            <td class="px-3 py-2">{{ $inv->item_name }}</td>
                            <td class="px-3 py-2">{{ $inv->item_stock }}</td>
                            <td class="px-3 py-2">{{ $inv->reorder_level }}</td>
                            <td class="px-3 py-2"><span class="px-2 py-1 text-xs rounded {{ $badge }}">{{ $status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 6.  NOTIFICATIONS  --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">View All →</a>
        </div>
        @if($notifications->isEmpty())
            <p class="text-sm text-gray-500">No new notifications.</p>
        @else
            <div class="space-y-3">
                @foreach($notifications as $n)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0"><i class="fas fa-bell text-gray-600 text-xs"></i></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">{{ $n->message }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $n->sender }} • {{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Approval Modal (shared behavior with Requisition page) -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-gray-900" id="modalTitle">Review Requisition</h3>
                <button onclick="closeApprovalModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-6" id="requisitionDetails"></div>

        <div class="p-6 border-t border-gray-200" id="approvalFormSection">
            <form id="approvalForm">
                @csrf
                <input type="hidden" id="requisitionId" name="requisition_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Decision</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="decision" value="approve" class="text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-700">Approve</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="decision" value="reject" class="text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">Reject</span>
                            </label>
                        </div>
                    </div>

                    <div id="rejectReasonSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                        <textarea id="req_reject_reason" name="req_reject_reason" rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            placeholder="Please provide a reason for rejecting this requisition..." required></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApprovalModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">Cancel</button>
                        <button type="submit" id="submitDecisionBtn"
                            class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 transition rounded">Submit Decision</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 hidden" id="viewOnlySection">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-800">Requisition Already Processed</h4>
                        <p class="text-sm text-blue-700 mt-1">This requisition has already been processed and cannot be modified.</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeApprovalModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition rounded">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Toast messages -->
    <div id="toastContainer" class="fixed top-4 right-4 space-y-2"></div>
    
</div>

@push('scripts')
<script>
let currentRequisition = null;

function showToast(message, type = 'success'){
    const container = document.getElementById('toastContainer');
    if (!container) return alert(message);
    const div = document.createElement('div');
    div.className = `px-4 py-2 rounded shadow text-sm ${type==='success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
    div.textContent = message;
    container.appendChild(div);
    setTimeout(()=>{ div.remove(); }, 3000);
}

function openApprovalModal(){ document.getElementById('approvalModal').classList.remove('hidden'); }
function closeApprovalModal(){ document.getElementById('approvalModal').classList.add('hidden'); }

function reviewRequisition(requisitionId){
    fetch(`/supervisor/requisitions/${requisitionId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r=>{ if(!r.ok){ throw new Error('Failed to load requisition'); } return r.json(); })
    .then(requisition => {
        currentRequisition = requisition;
        document.getElementById('requisitionId').value = requisition.req_id;
        document.getElementById('modalTitle').textContent = requisition.req_status === 'pending' ? 'Review Requisition' : 'Requisition Details';
        const approvalFormSection = document.getElementById('approvalFormSection');
        const viewOnlySection = document.getElementById('viewOnlySection');
        if (requisition.req_status === 'pending') { approvalFormSection.classList.remove('hidden'); viewOnlySection.classList.add('hidden'); }
        else { approvalFormSection.classList.add('hidden'); viewOnlySection.classList.remove('hidden'); }

        document.getElementById('requisitionDetails').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                        <p class="text-gray-900 font-semibold">${requisition.requester ? requisition.requester.name : 'N/A'}</p>
                        <p class="text-sm text-gray-600">${requisition.requester ? requisition.requester.position : ''}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requisition Date</label>
                        <p class="text-gray-900">${new Date(requisition.req_date).toLocaleDateString()}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                        <p class="text-gray-900 font-semibold">${requisition.req_ref}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold capitalize rounded">${requisition.req_priority}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                    <p class="text-gray-900 bg-gray-50 p-3 rounded">${requisition.req_purpose}</p>
                </div>
            </div>`;

        openApprovalModal();
    })
    .catch(err=>{ console.error(err); showToast('Error loading requisition', 'error'); });
}

function quickApprove(id){
    fetch(`/supervisor/requisitions/${id}/status`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ req_status: 'approved' })
    })
    .then(r=>r.json())
    .then(d=>{ if(d.success){ showToast(d.message || 'Requisition approved'); location.reload(); } else { throw new Error(d.message || 'Failed'); } })
    .catch(e=>{ showToast(e.message, 'error'); });
}

function quickReject(id){
    const reason = prompt('Enter rejection reason:');
    if (reason === null) return; // cancelled
    if (!reason.trim()) { showToast('Rejection reason is required', 'error'); return; }
    fetch(`/supervisor/requisitions/${id}/status`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ req_status: 'rejected', req_reject_reason: reason })
    })
    .then(r=>r.json())
    .then(d=>{ if(d.success){ showToast(d.message || 'Requisition rejected'); location.reload(); } else { throw new Error(d.message || 'Failed'); } })
    .catch(e=>{ showToast(e.message, 'error'); });
}

// Radio decision controls within modal
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('input[name="decision"]').forEach(radio => {
        radio.addEventListener('change', function(){
            const rejectReasonSection = document.getElementById('rejectReasonSection');
            if (this.value === 'reject') { rejectReasonSection.classList.remove('hidden'); document.getElementById('req_reject_reason').required = true; }
            else { rejectReasonSection.classList.add('hidden'); document.getElementById('req_reject_reason').required = false; }
        });
    });

    document.getElementById('approvalForm').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        const decision = formData.get('decision');
        const requisitionId = formData.get('requisition_id');
        const rejectReason = formData.get('req_reject_reason');
        if (decision === 'reject' && (!rejectReason || !rejectReason.trim())) { showToast('Please provide a rejection reason', 'error'); return; }
        const submitBtn = document.getElementById('submitDecisionBtn');
        submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        fetch(`/supervisor/requisitions/${requisitionId}/status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ req_status: decision === 'approve' ? 'approved' : 'rejected', req_reject_reason: rejectReason })
        })
        .then(r=>r.json())
        .then(d=>{
            if (d.success) { showToast(d.message || 'Decision saved'); closeApprovalModal(); location.reload(); }
            else { throw new Error(d.message || 'Failed to save decision'); }
        })
        .catch(err=>{ showToast(err.message, 'error'); submitBtn.disabled = false; submitBtn.innerHTML = 'Submit Decision'; })
    });
});
</script>
@endpush
@endsection