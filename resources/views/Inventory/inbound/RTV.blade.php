@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Return to Vendor (Dock Log)</h1>
            <p class="text-sm text-gray-500 mt-1">Log items rejected immediately during the receiving process.</p>
        </div>
        <button class="flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm">
            <i class="fas fa-times-circle mr-2"></i> Log New Rejection
        </button>
    </div>

    {{-- 2. RTV LOG TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejection Reason</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Rejected</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                
                <tr class="hover:bg-red-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 10:45 AM</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-mono text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">#PO-2023-102</span>
                        <div class="text-[10px] text-gray-400 mt-1">Prime Packaging</div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900">Cake Boxes (10x10)</p>
                        <p class="text-xs text-gray-500">SKU: PCK-BX-10</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mb-1">
                            Damaged
                        </span>
                        <p class="text-xs text-gray-500 italic">"Water damage on outer carton"</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="text-sm font-bold text-red-600">50 pcs</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-chocolate hover:underline text-xs">Print Slip</button>
                    </td>
                </tr>

                <tr class="hover:bg-red-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-mono text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">#PO-2023-099</span>
                        <div class="text-[10px] text-gray-400 mt-1">Cebu Dairy Corp</div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-gray-900">Fresh Milk</p>
                        <p class="text-xs text-gray-500">SKU: D-MLK-001</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mb-1">
                            Near Expiry
                        </span>
                        <p class="text-xs text-gray-500 italic">"Less than 3 days shelf life"</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="text-sm font-bold text-red-600">10 L</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-chocolate hover:underline text-xs">Print Slip</button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection