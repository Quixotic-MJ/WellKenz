@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receive Delivery</h1>
            <p class="text-sm text-gray-500 mt-1">Inbound Logistics • <span class="text-chocolate font-bold">Blind Count Mode</span></p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white border border-gray-300 rounded-lg px-3 py-2 shadow-sm">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" placeholder="Scan PO Barcode..." class="text-sm font-medium text-gray-700 focus:outline-none bg-transparent border-none p-0 w-48">
            </div>
        </div>
    </div>

    {{-- 2. PO SELECTION / CONTEXT --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">PO #2023-102</h3>
                <p class="text-sm text-gray-500">Supplier: <span class="font-medium text-gray-800">Prime Packaging Corp.</span></p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Expected Today
                </span>
            </div>
        </div>

        {{-- BLIND INPUT TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Item Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">Batch / Expiry</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase w-1/6">Condition</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase w-1/6">Physical Count</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Item 1 --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center text-gray-500 mr-3">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Cake Boxes (10x10)</p>
                                    <p class="text-xs text-gray-500">SKU: PCK-BX-10</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <input type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs" placeholder="Batch # (Optional)">
                                <input type="date" class="block w-full border-gray-200 bg-gray-50 rounded-md shadow-sm text-xs text-gray-400 cursor-not-allowed" disabled title="Non-perishable">
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <select class="border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs">
                                <option>Good</option>
                                <option>Damaged</option>
                                <option>Wet/Stained</option>
                            </select>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end">
                                <input type="number" class="block w-24 text-right border-2 border-blue-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg font-bold text-gray-900 p-2" placeholder="0">
                                <span class="ml-2 text-xs text-gray-500">pcs</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Item 2 (Perishable) --}}
                    <tr class="hover:bg-gray-50 transition-colors bg-amber-50/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded bg-amber-100 flex items-center justify-center text-amber-600 mr-3">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Heavy Cream</p>
                                    <p class="text-xs text-gray-500">SKU: D-CRM-001</p>
                                    <span class="text-[10px] text-red-500 font-bold uppercase"><i class="fas fa-exclamation-circle"></i> Perishable</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <input type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs" placeholder="Batch #">
                                <input type="date" class="block w-full border-red-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 text-xs" required>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <select class="border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs">
                                <option>Good</option>
                                <option>Thawed (Reject)</option>
                                <option>Leaking</option>
                            </select>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end">
                                <input type="number" class="block w-24 text-right border-2 border-blue-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg font-bold text-gray-900 p-2" placeholder="0">
                                <span class="ml-2 text-xs text-gray-500">L</span>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-4 flex justify-end gap-3">
            <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                Save Progress
            </button>
            <button class="px-6 py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 shadow-sm flex items-center">
                <i class="fas fa-check-double mr-2"></i> Submit Count
            </button>
        </div>
    </div>

</div>
@endsection