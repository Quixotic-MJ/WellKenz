@extends('Supervisor.layout.app')

@section('title', 'Reports - WellKenz ERP')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="space-y-6">

        <!-- toast -->
        <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
        <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

        <!-- header card -->
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

        <!-- quick tiles -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button onclick="loadReport('requisition-summary')"
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
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
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
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
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
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
                class="bg-white border border-gray-200 rounded-lg p-5 text-left hover:shadow transition">
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

        <!-- dynamic report container -->
        <div id="reportContainer" class="bg-white border border-gray-200 rounded-lg p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 id="reportTitle" class="text-lg font-semibold text-gray-900"></h3>
                <button onclick="window.print()"
                    class="px-3 py-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm rounded">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
            </div>
            <div id="reportContent" class="text-sm text-gray-700"></div>
        </div>

        <!-- fallback: transaction history (default) -->
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

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="txTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="txTableBody">
                        @foreach ($transactions as $tx)
                            <tr class="hover:bg-gray-50 transition tx-row">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->item->item_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $tx->trans_type }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $tx->trans_quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->balance_qty ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->trans_ref ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Showing <span id="txVisibleCount">{{ $transactions->count() }}</span> of {{ $transactions->count() }}
                transactions
            </div>
        </div>

    </div>

    <style>
        @media print {

            /* Hide everything on the page by default */
            body * {
                visibility: hidden;
            }

            /* Make the printable container and its children visible */
            .printable-area,
            .printable-area * {
                visibility: visible;
            }

            /* Position the printable area at the top of the page for printing */
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Hide the print button itself on the printout */
            .printable-area .no-print {
                display: none;
            }
        }
    </style>

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
            fetch(`/supervisor/reports/${type}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(res => {
                    document.getElementById('reportTitle').textContent = res.title;
                    document.getElementById('reportContent').innerHTML = res.html;
                    document.getElementById('transactionContainer').classList.remove('printable-area');
                    document.getElementById('reportContainer').classList.add('printable-area');
                    document.getElementById('reportContainer').classList.remove('hidden');
                    document.getElementById('reportContainer').scrollIntoView({
                        behavior: 'smooth'
                    });
                })
                .catch(() => showMessage('Report unavailable', 'error'));
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
                r.style.display = ok ? '' : 'none';
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
@endsection
