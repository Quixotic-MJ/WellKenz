@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('inventory.reports') }}" class="mr-4 text-blue-600 hover:text-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $item->item_name }} - Stock Card Report</h1>
            <p class="text-gray-600 mt-1">Detailed stock movement history for {{ $item->item_code }}</p>
        </div>
    </div>

    <!-- Report Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500">Report Generated</p>
                <p class="text-lg font-semibold text-gray-900">{{ now()->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Item Code</p>
                <p class="text-lg font-semibold text-gray-900">{{ $item->item_code }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Current Stock</p>
                <p class="text-lg font-semibold text-blue-600">{{ $item->item_stock }} {{ $item->item_unit }}</p>
            </div>
        </div>
    </div>

    <!-- Stock Card Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Stock Card</h3>
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Print Report
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $balance = 0;
                        // Sort transactions by date (oldest first)
                        $sortedTransactions = $transactions->sortBy('trans_date');
                    @endphp
                    
                    @forelse($sortedTransactions as $transaction)
                    @php
                        if ($transaction->trans_type === 'in') {
                            $balance += $transaction->trans_quantity;
                        } else {
                            $balance -= $transaction->trans_quantity;
                        }
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->trans_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->trans_ref }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction->trans_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->trans_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            {{ $transaction->trans_type === 'in' ? number_format($transaction->trans_quantity, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                            {{ $transaction->trans_type === 'out' ? number_format($transaction->trans_quantity, 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($balance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->trans_remarks ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Final Balance:</td>
                        <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ number_format($item->item_stock, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $totalIn = $transactions->where('trans_type', 'in')->sum('trans_quantity');
                $totalOut = $transactions->where('trans_type', 'out')->sum('trans_quantity');
                $totalTransactions = $transactions->count();
            @endphp
            <div>
                <p class="text-sm text-gray-500">Total Stock In</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($totalIn, 2) }} {{ $item->item_unit }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Stock Out</p>
                <p class="text-xl font-bold text-red-600">{{ number_format($totalOut, 2) }} {{ $item->item_unit }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Transactions</p>
                <p class="text-xl font-bold text-blue-600">{{ $totalTransactions }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Net Movement</p>
                <p class="text-xl font-bold {{ $totalIn - $totalOut >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $totalIn - $totalOut >= 0 ? '+' : '' }}{{ number_format($totalIn - $totalOut, 2) }} {{ $item->item_unit }}
                </p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        font-size: 12px;
    }
    .bg-gray-50 {
        background-color: #f9fafb !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection