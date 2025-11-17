@extends('Admin.layout.app')

@section('title', 'Reports & Analytics - WellKenz ERP')
@section('breadcrumb', 'Reports and Analytics')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
                <p class="text-sm text-gray-500 mt-1">Generate system-wide reports for management and auditing</p>
            </div>
        </div>
    </div>

    <!-- date range bar -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <form id="dateRangeForm" class="flex items-center space-x-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">From</label>
                <input type="date" name="start" id="startDate" value="{{ now()->subDays(30)->format('Y-m-d') }}" class="ml-2 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">To</label>
                <input type="date" name="end" id="endDate" value="{{ now()->format('Y-m-d') }}" class="ml-2 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Format</label>
                <select name="format" id="format" class="ml-2 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                    <option value="web">Web Table</option>
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
        </form>
    </div>

    <!-- report cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- 1. User Activity -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">User Activity</h3>
            <p class="text-sm text-gray-500 mb-4">Log-ins & account status within date range</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Active</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $activeUsers }}</p>
                </div>
                <button onclick="generateReport('user-activity')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 2. Item Requests -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Item Requests</h3>
            <p class="text-sm text-gray-500 mb-4">Summary by status & date</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $pendingItemRequests }}</p>
                </div>
                <button onclick="generateReport('item-requests')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 3. Requisition History -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Requisition History</h3>
            <p class="text-sm text-gray-500 mb-4">Pending, approved, rejected, completed</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $pendingRequisitions }}</p>
                </div>
                <button onclick="generateReport('requisitions')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 4. Purchase Order Overview -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Purchase Orders</h3>
            <p class="text-sm text-gray-500 mb-4">Ordered, delivered, cancelled</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Ordered</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $orderedPOs }}</p>
                </div>
                <button onclick="generateReport('purchase-orders')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 5. Inventory Movements -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Inventory Movements</h3>
            <p class="text-sm text-gray-500 mb-4">Stock-in / out summary</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Movements</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $movementCount }}</p>
                </div>
                <button onclick="generateReport('inventory-movements')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 6. Expiry & Low-Stock -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Expiry & Low-Stock</h3>
            <p class="text-sm text-gray-500 mb-4">From get_expiry_alerts(), get_low_stock_items()</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Low-Stock</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $lowStockCount }}</p>
                </div>
                <button onclick="generateReport('expiry-low-stock')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 7. Supplier Performance -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Supplier Performance</h3>
            <p class="text-sm text-gray-500 mb-4">Total orders, late deliveries</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Suppliers</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $suppliersCount }}</p>
                </div>
                <button onclick="generateReport('supplier-performance')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 8. Weekly Stock-In Summary -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Weekly Stock-In Summary</h3>
            <p class="text-sm text-gray-500 mb-4">stock_in_summary() output</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">This Week</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $weeklyStockInCount }}</p>
                </div>
                <button onclick="generateReport('weekly-stock-in')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 9. Negative Stock -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Negative Stock</h3>
            <p class="text-sm text-gray-500 mb-4">Items below zero</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Items</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $negativeStockCount }}</p>
                </div>
                <button onclick="generateReport('negative-stock')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

        <!-- 10. AR Issuance -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col">
            <h3 class="text-base font-semibold text-gray-900 mb-3">AR Issuance</h3>
            <p class="text-sm text-gray-500 mb-4">Acknowledgement Receipts (Issued)</p>
            <div class="mt-auto flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Issued</p>
                    <p class="text-xl font-semibold text-gray-900">{{ $arIssuedCount }}</p>
                </div>
                <button onclick="generateReport('ar-issuance')" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition text-xs font-medium rounded">Generate</button>
            </div>
        </div>

    </div>

    <!-- on-screen result area -->
    <div id="reportResult" class="hidden bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 id="reportTitle" class="text-lg font-semibold text-gray-900"></h3>
            <button onclick="document.getElementById('reportResult').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="reportTable" class="overflow-x-auto"></div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* light helpers */
function showMessage(msg, type = 'success'){
    const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById('errorMessage');
    div.textContent = msg; div.classList.remove('hidden');
    setTimeout(()=> div.classList.add('hidden'), 3000);
}
let reportChart = null;
function renderChart(cfg){
    if(!cfg) return;
    const container = document.getElementById('reportTable');
    let canvas = document.getElementById('reportChart');
    if(!canvas){
        const wrapper = document.createElement('div');
        wrapper.className = 'my-4 h-72';
        canvas = document.createElement('canvas');
        canvas.id = 'reportChart';
        wrapper.appendChild(canvas);
        container.prepend(wrapper);
    }
    const ctx = canvas.getContext('2d');
    if(reportChart) reportChart.destroy();
    reportChart = new Chart(ctx, {
        type: cfg.type || 'bar',
        data: { labels: cfg.labels || [], datasets: cfg.datasets || [] },
        options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ position:'bottom' } } }
    });
}
/* helper: hit report endpoint and render / download */
function generateReport(report){

    const start = document.getElementById('startDate').value;
    const end   = document.getElementById('endDate').value;
    const fmt   = document.getElementById('format').value;

    const url = `/admin/reports/${report}?start=${start}&end=${end}&format=${fmt}`;

    if(fmt==='pdf' || fmt==='csv'){
        window.open(url,'_blank');
        return;
    }

    /* web table */
    fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json())
        .then(res=>{
            if(res.html){
                document.getElementById('reportTitle').textContent = res.title;
                document.getElementById('reportTable').innerHTML   = res.html;
                if(res.chart){ renderChart(res.chart); }
                document.getElementById('reportResult').classList.remove('hidden');
                window.scrollTo({top:document.getElementById('reportResult').offsetTop, behavior:'smooth'});
            }else if(res.empty){
                document.getElementById('reportTitle').textContent = res.title;
                document.getElementById('reportTable').innerHTML   = res.html;
                document.getElementById('reportResult').classList.remove('hidden');
            }else{
                showMessage('No data for selected range','error');
            }
        })
        .catch(()=>showMessage('Error generating report','error'));
}

/* keep date range sensible */
document.getElementById('endDate').addEventListener('change',()=>{
    const start = document.getElementById('startDate').value;
    const end   = document.getElementById('endDate').value;
    if(start && end && new Date(start) > new Date(end)){
        document.getElementById('endDate').value = start;
    }
});
</script>
@endsection