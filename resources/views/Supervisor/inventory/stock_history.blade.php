@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Stock History</h1>
            <p class="text-sm text-gray-500">Complete inventory movement history</p>
        </div>
        <div class="flex space-x-3">
            <button class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Export History
            </button>
        </div>
    </div>

    {{-- 2. STOCK ITEMS LIST --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800 uppercase">Inventory Items</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Point</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->currentStockRecord)
                                    <span class="text-sm font-bold text-gray-900">
                                        {{ number_format($item->currentStockRecord->current_quantity, 2) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">No Stock Record</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->unit->symbol ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->category->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->reorder_point, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('supervisor.inventory.history.item', $item->id) }}" 
                                   class="text-chocolate hover:text-chocolate-dark">
                                    View History
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-box-open text-3xl mb-3 text-gray-300"></i>
                                <p class="text-lg font-medium">No items found</p>
                                <p class="text-sm">Check back later or add some items to inventory.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection