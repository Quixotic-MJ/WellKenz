@extends('Inventory.layout.app')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('inventory.items.show', $item->item_id) }}" class="mr-4 text-blue-600 hover:text-blue-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $item->item_name }} - Stock Card</h1>
            <p class="text-gray-600 mt-1">Transaction history for {{ $item->item_code }}</p>
        </div>
    </div>

    <!-- Item Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Current Stock</p>
                <p class="text-2xl font-bold text-blue-600">{{ $item->item_stock }} {{ $item->item_unit }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Reorder Level</p>
                <p class="text-xl font-semibold text-yellow-600">{{ $item->reorder_level }} {{ $item->item_unit }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Min Stock Level</p>
                <p class="text-xl font-semibold text-red-600">{{ $item->min_stock_level }} {{ $item->item_unit }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Max Stock Level</p>
                <p class="text-xl font-semibold text-green-600">{{ $item->max_stock_level ?? 'N/A' }} {{ $item->item_unit }}</p>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Transaction History</h3>
                <div class="flex space-x-2">
                    <button onclick="exportStockCard()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Export Stock Card
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Running Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $index => $transaction)
                    @php
                        $runningBalance = $item->item_stock;
                        // Calculate running balance (this is a simplified version)
                        // In real implementation, you'd calculate from oldest to newest
                        foreach ($transactions as $t) {
                            if ($t->created_at >= $transaction->created_at) break;
                            $runningBalance = $t->trans_type === 'in' 
                                ? $runningBalance - $t->trans_quantity 
                                : $runningBalance + $t->trans_quantity;
                        }
                        $runningBalance = $transaction->trans_type === 'in' 
                            ? $runningBalance + $transaction->trans_quantity 
                            : $runningBalance - $transaction->trans_quantity;
                    @endphp
                    <tr class="{{ $transaction->trans_type === 'in' ? 'bg-green-50' : 'bg-red-50' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->trans_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->trans_ref }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction->trans_type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->trans_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->trans_type === 'in' ? 'text-green-700' : 'text-red-700' }} font-medium">
                            {{ $transaction->trans_type === 'in' ? '+' : '-' }}{{ number_format($transaction->trans_quantity, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($runningBalance, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->trans_remarks ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->user->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No transactions found for this item</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function exportStockCard() {
        alert('Export stock card functionality will be implemented');
    }
</script>
@endpush
@endsection