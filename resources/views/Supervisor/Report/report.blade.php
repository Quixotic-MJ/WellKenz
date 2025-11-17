@extends('Supervisor.layout.app')

@section('title', 'Reports - WellKenz ERP')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="space-y-6">

        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Supervisor Reports</h1>
                    <p class="text-sm text-gray-500 mt-1">Generate analytical summaries – read-only</p>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span class="w-2 h-2 bg-indigo-400 rounded-full"></span>
                    <span>Read-only</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button onclick="loadReport('requisition-summary')"
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-indigo-100 rounded flex items-center justify-center">
                        <i class="fas fa-file-alt text-indigo-600"></i>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Requisition Summary</p>
                <p class="text-xs text-gray-500 mt-1">By status / employee</p>
            </button>

            <button onclick="loadReport('item-request-trends')"
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600"></i>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Item-Request Trends</p>
                <p class="text-xs text-gray-500 mt-1">Approved / rejected counts</p>
            </button>

            <button onclick="loadReport('purchase-summary')"
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Purchase-Order Summary</p>
                <p class="text-xs text-gray-500 mt-1">Value / status / supplier</p>
            </button>

            <button onclick="loadReport('inventory-health')"
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-amber-100 rounded flex items-center justify-center">
                        <i class="fas fa-heartbeat text-amber-600"></i>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900">Inventory Health</p>
                <p class="text-xs text-gray-500 mt-1">Low stock & expiry alerts</p>
            </button>
        </div>

        <div id="reportContainer" class="bg-white border border-gray-200 rounded-lg p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 id="reportTitle" class="text-lg font-semibold text-gray-900"></h3>
                <button onclick="window.print()"
                    class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded no-print">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
            </div>
            {{-- This div is used to inject the HTML from the print views --}}
            <div id="reportContent" class="text-sm text-gray-700"></div>
        </div>

        <div id="transactionContainer" class="bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Inventory Transactions</h3>
                <div class="relative">
                    <input type="text" id="txSearch" placeholder="Search transactions…" onkeyup="searchTx(this.value)"
                        class="pl-9 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <button type="button" onclick="clearTxSearch()" id="txClearBtn"
                        class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"><i
                            class="fas fa-times text-xs"></i></button>
                </div>
            </div>

            {{-- ****** START: DESIGN UPDATE (Replaced Table) ****** --}}
            <div class="divide-y divide-gray-200" id="txTableBody">
                @forelse ($transactions as $tx)
                    @php
                        $icon = [
                            'in' => 'fas fa-arrow-down',
                            'out' => 'fas fa-arrow-up',
                            'adjustment' => 'fas fa-edit',
                        ][$tx->trans_type] ?? 'fas fa-exchange-alt';
                        
                        $color = [
                            'in' => 'text-green-500 bg-green-50',
                            'out' => 'text-red-500 bg-red-50',
                            'adjustment' => 'text-amber-500 bg-amber-50',
                        ][$tx->trans_type] ?? 'text-gray-500 bg-gray-50';
                    @endphp
                    <div class="tx-row flex items-start p-6 hover:bg-gray-50 transition">
                        <div class="mr-4 pt-1 flex-shrink-0">
                            <span class="flex items-center justify-center h-10 w-10 rounded-full {{ $color }}">
                                <i class="{{ $icon }}"></i>
                            </span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $tx->item->item_name ?? 'Unknown Item' }}
                                    <span class="ml-2 font-mono {{ $tx->trans_type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                        ({{ $tx->trans_type === 'in' ? '+' : '-' }}{{ $tx->quantity }})
                                    </span>
                                </p>
                                {{-- **FIX: Corrected the format call** --}}
                                <span class="text-xs text-gray-500 flex-shrink-0 ml-4">{{ $tx->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                Ref: <span class="font-mono">{{ $tx->trans_ref ?? 'N/A' }}</span>
                                {{-- **FIX: Removed $tx->balance_qty** --}}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">User: <strong>{{ $tx->user->name ?? 'System' }}</strong></p>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">
                        <i class="fas fa-exchange-alt text-3xl mb-3 opacity-50"></i>
                        <p>No transactions found.</p>
                    </div>
                @endforelse
            </div>
            {{-- ****** END: DESIGN UPDATE ****** --}}


            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="txVisibleCount">{{ $transactions->count() }}</span> of {{ $transactions->count() }}
                transactions
            </div>
        </div>

    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            .printable-area, .printable-area * { visibility: visible; }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px; /* Add padding for printing */
                margin: 0;
            }
            .printable-area .no-print { display: none; }
            /* Ensure print styles from the loaded content are applied */
            .print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .print-table th, .print-table td { padding: 6px 4px; text-align: left; border: 1px solid #ccc; }
            .print-table th { background-color: #f5f5f5 !important; -webkit-print-color-adjust: exact; }
            .summary-bar { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; }
            .summary-bar div { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px; font-size: 11px; }
            .alert-box { background-color: #fff3cd !important; -webkit-print-color-adjust: exact; border: 1px solid #ffeaa7; padding: 8px; margin-bottom: 10px; border-radius: 4px; font-size: 11px; }
        }
    </style>

    {{-- ****** START: JAVASCRIPT UPDATE ****** --}}
    <script>
        /* helpers */
        function showMessage(msg, type = 'success') {
            const div = type === 'success' ? document.getElementById('successMessage') : document.getElementById(
                'errorMessage');
            div.textContent = msg;
            div.classList.remove('hidden');
            setTimeout(() => div.classList.add('hidden'), 3000);
        }

        /* dynamic report loader */
        function loadReport(type) {
            // **FIX: Calling the new 'generate' route **
            const url = `{{ route('supervisor.reports.generate', ['report' => '__REPORT_TYPE__']) }}`.replace('__REPORT_TYPE__', type);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => {
                if (!r.ok) {
                    // Try to parse error as JSON
                    return r.json().then(err => { 
                        throw new Error(err.message || 'Failed to load report. Check server logs.') 
                    });
                }
                return r.json(); // This should be { title: '...', html: '...' }
            })
            .then(res => {
                if (res.html) {
                    document.getElementById('reportTitle').textContent = res.title;
                    document.getElementById('reportContent').innerHTML = res.html;
                    
                    document.getElementById('transactionContainer').classList.remove('printable-area');
                    document.getElementById('reportContainer').classList.add('printable-area');
                    
                    document.getElementById('transactionContainer').classList.add('hidden'); // Hide default table
                    document.getElementById('reportContainer').classList.remove('hidden'); // Show report
                    document.getElementById('reportContainer').scrollIntoView({
                        behavior: 'smooth'
                    });
                } else {
                    throw new Error('Received invalid data from server.');
                }
            })
            .catch((err) => {
                console.error(err);
                showMessage(err.message || 'Report could not be loaded.', 'error');
            });
        }

        // Set the default printable area on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('transactionContainer').classList.add('printable-area');
            document.getElementById('reportContainer').classList.remove('printable-area');
        });

        /* transaction search */
        function searchTx(q) {
            const Q = q.toLowerCase();
            const rows = document.querySelectorAll('.tx-row');
            let visible = 0;
            rows.forEach(r => {
                const ok = r.textContent.toLowerCase().includes(Q);
                r.style.display = ok ? 'flex' : 'none'; // <-- Updated to 'flex'
                if (ok) visible++;
            });
            document.getElementById('txVisibleCount').textContent = visible;
            const btn = document.getElementById('txClearBtn');
            Q ? btn.classList.remove('hidden') : btn.classList.add('hidden');
        }

        function clearTxSearch() {
            document.getElementById('txSearch').value = '';
            searchTx('');
            document.getElementById('txClearBtn').classList.add('hidden');
        }
    </script>
    {{-- ****** END: JAVASCRIPT UPDATE ****** --}}
@endsection