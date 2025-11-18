@extends('Purchasing.layout.app')

@section('title','Procurement Reports - WellKenz ERP')
@section('breadcrumb','Procurement Reports')

@section('content')
<div class="space-y-6 print:space-y-4">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 print:border-0 print:p-0">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Procurement Reports</h1>
                <p class="text-sm text-gray-500 mt-1">Generate purchasing-related summaries – read-only</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                <span>Read-only</span>
            </div>
        </div>
    </div>

    <!-- 2. quick tiles -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 print:hidden">
        <button onclick="loadReport('po-by-supplier')" 
            class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">PO by Supplier</p>
            <p class="text-xs text-gray-500 mt-1">Value / count</p>
        </button>

        <button onclick="loadReport('po-by-status')" 
            class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                    <i class="fas fa-chart-pie text-green-600"></i>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">PO by Status</p>
            <p class="text-xs text-gray-500 mt-1">Draft / ordered / delivered</p>
        </button>

        <button onclick="loadReport('delivery-performance')" 
            class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded flex items-center justify-center">
                    <i class="fas fa-truck text-amber-600"></i>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">Delivery Performance</p>
            <p class="text-xs text-gray-500 mt-1">On-time vs overdue</p>
        </button>

        <button onclick="loadReport('monthly-spend')" 
            class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-indigo-100 rounded flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-indigo-600"></i>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">Monthly Spend</p>
            <p class="text-xs text-gray-500 mt-1">Historical totals</p>
        </button>
    </div>

    <!-- 3. dynamic report container -->
    <div id="reportContainer" class="bg-white border border-gray-200 rounded-lg p-6 hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 id="reportTitle" class="text-lg font-semibold text-gray-900"></h3>
            <div class="flex items-center space-x-2">
                <button onclick="openPrintView()" class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
                <button onclick="downloadCSV()" class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                    <i class="fas fa-download mr-1"></i>CSV
                </button>
            </div>
        </div>
        <div id="reportContent" class="text-sm text-gray-700"></div>
    </div>

    <!-- 4. fallback: recent POs (default view) -->
    <div class="bg-white border border-gray-200 rounded-lg print:hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Recent Purchase Orders</h3>
            <a href="{{ route('purchasing.approved.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">View All →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="recentPOTable">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PO Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total (₱)</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="recentPOTableBody">
                    @foreach($recentPOs as $po)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">PO-{{ $po->po_ref }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $po->sup_name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded
                                @if($po->po_status=='draft') bg-gray-100 text-gray-700
                                @elseif($po->po_status=='ordered') bg-blue-100 text-blue-700
                                @elseif($po->po_status=='delivered') bg-green-100 text-green-700
                                @elseif($po->po_status=='cancelled') bg-rose-100 text-rose-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($po->po_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">₱ {{ number_format($po->total_amount,2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M d, Y') : '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
            Showing {{ $recentPOs->count() }} of {{ $recentPOs->total() }} purchase orders
        </div>
    </div>

</div>

<script>
/* ===== helpers ===== */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}

/* ===== load dynamic report ===== */
let currentReportType = null;
function loadReport(type){
    currentReportType = type;
    fetch(`/purchasing/reports/${type}`,{
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(res => {
        document.getElementById('reportTitle').textContent = res.title;
        document.getElementById('reportContent').innerHTML = res.html;
        document.getElementById('reportContainer').classList.remove('hidden');
        document.getElementById('reportContainer').scrollIntoView({behavior:'smooth'});
    })
    .catch(() => showMessage('Report unavailable','error'));
}

/* ===== open print view ===== */
function openPrintView(){
    if(!currentReportType){
        showMessage('Please load a report first','error');
        return;
    }
    window.open(`/purchasing/reports/${currentReportType}/print`, '_blank');
}

/* ===== csv export ===== */
function downloadCSV(){
    const table = document.querySelector('#reportContent table');
    if(!table){ showMessage('No data to export','error'); return; }
    let csv = [];
    table.querySelectorAll('tr').forEach(tr => {
        let row = [];
        tr.querySelectorAll('th,td').forEach(td => row.push(td.textContent.trim()));
        csv.push(row.join(','));
    });
    const blob = new Blob([csv.join('\n')], {type:'text/csv'});
    const url  = window.URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = document.getElementById('reportTitle').textContent.replace(/\s+/g,'_')+'.csv';
    a.click(); window.URL.revokeObjectURL(url);
}

</script>
@endsection