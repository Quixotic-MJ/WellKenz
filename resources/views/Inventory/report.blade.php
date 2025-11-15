@extends('Inventory.layout.app')

@section('title','Inventory Reports - WellKenz ERP')
@section('breadcrumb','Inventory Reports')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- 1. header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Inventory Reports</h1>
                <p class="text-sm text-gray-500 mt-1">Generate stock, movement & expiry summaries – read-only</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                <span>Read-only</span>
            </div>
        </div>
    </div>

    <!-- 2. report tiles -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach([
            ['key'=>'stock-level','icon'=>'fas fa-boxes','label'=>'Stock Level','color'=>'blue'],
            ['key'=>'stock-in','icon'=>'fas fa-sign-in-alt','label'=>'Stock-In','color'=>'green'],
            ['key'=>'stock-out','icon'=>'fas fa-sign-out-alt','label'=>'Stock-Out','color'=>'rose'],
            ['key'=>'expiry','icon'=>'fas fa-calendar-times','label'=>'Expiry','color'=>'amber'],
            ['key'=>'low-stock','icon'=>'fas fa-exclamation-triangle','label'=>'Low Stock','color'=>'yellow'],
            ['key'=>'adjustments','icon'=>'fas fa-sliders-h','label'=>'Adjustments','color'=>'gray']
        ] as $tile)
        <button onclick="loadReport('{{ $tile['key'] }}')" 
            class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
            <div class="flex items-center justify-between mb-2">
                <div class="w-10 h-10 bg-{{ $tile['color'] }}-100 rounded flex items-center justify-center">
                    <i class="{{ $tile['icon'] }} text-{{ $tile['color'] }}-600"></i>
                </div>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">{{ $tile['label'] }}</p>
        </button>
        @endforeach
    </div>

    <!-- 3. date range picker (global) -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Date Range:</label>
            <input type="date" id="fromDate" value="{{ now()->subMonth()->toDateString() }}" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
            <span class="text-gray-500">to</span>
            <input type="date" id="toDate" value="{{ now()->toDateString() }}" class="text-sm border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
            <button onclick="applyDateRange()" class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded text-sm">
                <i class="fas fa-filter mr-1"></i>Apply
            </button>
            <button onclick="resetDateRange()" class="px-3 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm">
                <i class="fas fa-undo mr-1"></i>Reset
            </button>
        </div>
    </div>

    <!-- 4. dynamic report container -->
    <div id="reportContainer" class="bg-white border border-gray-200 rounded-lg p-6 hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 id="reportTitle" class="text-lg font-semibold text-gray-900"></h3>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
                <button onclick="downloadCSV()" class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                    <i class="fas fa-download mr-1"></i>CSV
                </button>
            </div>
        </div>
        <div id="reportContent" class="text-sm text-gray-700"></div>
    </div>

    <!-- 5. fallback: daily summary (default view) -->
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Daily Inventory Summary <span id="summaryDate">{{ now()->format('M d, Y') }}</span></h3>
            <button onclick="loadDailySummary()" class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6" id="summaryCards">
            <!-- cards populated by ajax -->
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-boxes text-blue-600"></i>
                </div>
                <p class="text-sm text-gray-500">Total Items</p>
                <p class="text-2xl font-bold text-gray-900" id="totalItems">-</p>
            </div>
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-sign-in-alt text-green-600"></i>
                </div>
                <p class="text-sm text-gray-500">Stock-In Today</p>
                <p class="text-2xl font-bold text-gray-900" id="stockInToday">-</p>
            </div>
            <div class="text-center p-4 border border-gray-200 rounded-lg">
                <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-sign-out-alt text-rose-600"></i>
                </div>
                <p class="text-sm text-gray-500">Stock-Out Today</p>
                <p class="text-2xl font-bold text-gray-900" id="stockOutToday">-</p>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Top 5 Movements Today</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="summaryTable">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                            <th class="text-left py-2">Item</th>
                            <th class="text-left py-2">Type</th>
                            <th class="text-right py-2">Qty</th>
                            <th class="text-right py-2">Balance</th>
                        </tr>
                    </thead>
                    <tbody id="summaryTableBody">
                        <!-- populated by ajax -->
                    </tbody>
                </table>
            </div>
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

/* ===== date range ===== */
let fromDate = document.getElementById('fromDate').value;
let toDate   = document.getElementById('toDate').value;

function applyDateRange(){
    fromDate = document.getElementById('fromDate').value;
    toDate   = document.getElementById('toDate').value;
    if(new Date(fromDate) > new Date(toDate)){ showMessage('From date must be before To date','error'); return; }
    // reload current report if one is open
    if(document.getElementById('reportContainer').classList.contains('hidden') === false){
        const currentType = document.querySelector('#reportContainer').dataset.type;
        if(currentType) loadReport(currentType);
    }
}
function resetDateRange(){
    document.getElementById('fromDate').value = '{{ now()->subMonth()->toDateString() }}';
    document.getElementById('toDate').value   = '{{ now()->toDateString() }}';
    fromDate = document.getElementById('fromDate').value;
    toDate   = document.getElementById('toDate').value;
}

/* ===== load dynamic report ===== */
function loadReport(type){
    document.getElementById('reportContainer').dataset.type = type;
    fetch(`/inventory/reports/${type}?from=${fromDate}&to=${toDate}`,{
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

/* ===== csv export ===== */
function downloadCSV(){
    const table = document.querySelector('#reportContent table');
    if(!table){ showMessage('No data to export','error'); return; }
    let csv = [];
    table.querySelectorAll('tr').forEach(tr=>{
        let row = [];
        tr.querySelectorAll('th,td').forEach(td=>row.push(td.textContent.trim()));
        csv.push(row.map(field=>`"${field.replace(/"/g,'""')}"`).join(','));
    });
    const blob = new Blob([csv.join('\n')], {type:'text/csv'});
    const url  = window.URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = document.getElementById('reportTitle').textContent.replace(/\s+/g,'_')+'.csv';
    a.click(); window.URL.revokeObjectURL(url);
}

/* ===== daily summary (default) ===== */
function loadDailySummary(){
    fetch('/inventory/reports/daily-summary',{
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(res => {
        document.getElementById('summaryDate').textContent = res.date;
        document.getElementById('totalItems').textContent  = res.totalItems;
        document.getElementById('stockInToday').textContent = res.stockInToday;
        document.getElementById('stockOutToday').textContent = res.stockOutToday;

        let rows='';
        res.topMovements.forEach(m=>{
            rows+=`<tr class="border-t border-gray-100">
                <td class="py-2 text-sm text-gray-900">${m.item_name}</td>
                <td class="py-2 text-sm text-gray-600 capitalize">${m.trans_type}</td>
                <td class="py-2 text-sm text-right ${m.trans_type==='in'?'text-green-700':'text-rose-700'}">${m.trans_quantity}</td>
                <td class="py-2 text-sm text-right text-gray-600">${m.balance_qty ?? '-'}</td>
            </tr>`;
        });
        document.getElementById('summaryTableBody').innerHTML = rows;
    })
    .catch(() => showMessage('Unable to load summary','error'));
}

/* ===== print friendly ===== */
function beforePrint(){
    document.getElementById('reportContainer')?.classList.remove('hidden');
}
function afterPrint(){
    /* optional reset */
}
window.onbeforeprint = beforePrint;
window.onafterprint  = afterPrint;

/* ===== initialise ===== */
loadDailySummary();
</script>
@endsection