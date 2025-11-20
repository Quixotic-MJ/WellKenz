@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fulfill Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Pick and issue stock for approved kitchen requisitions.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">3 Ready to Pick</span>
        </div>
    </div>

    {{-- 2. REQUESTS QUEUE --}}
    <div class="space-y-4">

        {{-- Request 1 (Expanded / Active) --}}
        <div class="bg-white border border-blue-200 rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 flex justify-between items-center border-b border-blue-100">
                <div class="flex items-center gap-4">
                    <span class="font-mono font-bold text-blue-700 bg-white px-2 py-1 rounded border border-blue-200">#REQ-1024</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Baker John Doe</h3>
                        <p class="text-xs text-gray-500">Wedding Cake Production</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase font-bold">Required By</p>
                    <p class="text-sm font-bold text-gray-900">Today, 2:00 PM</p>
                </div>
            </div>

            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-100 mb-4">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Requested Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Suggested Batch (FEFO)</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-4 py-3">
                                <p class="text-sm font-bold text-gray-900">White Sugar</p>
                                <p class="text-xs text-gray-500">SKU: RM-SGR-002</p>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-chocolate">50.00 kg</td>
                            <td class="px-4 py-3">
                                <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate text-xs">
                                    <option selected>Batch #231001 (Exp: Oct 30) - Loc: A1</option>
                                    <option>Batch #231015 (Exp: Nov 15) - Loc: B2</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    <span class="ml-2 text-xs text-gray-700 font-medium">Picked</span>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button class="px-6 py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 shadow-sm flex items-center">
                        <i class="fas fa-box-open mr-2"></i> Confirm Issuance
                    </button>
                </div>
            </div>
        </div>

        {{-- Request 2 (Collapsed) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition overflow-hidden opacity-75">
            <div class="px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <span class="font-mono font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">#REQ-1025</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Maria (Pastry)</h3>
                        <p class="text-xs text-gray-500">Stock Replenishment</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">2 Items</span>
                    <button class="text-chocolate hover:text-chocolate-dark font-bold text-sm">Expand <i class="fas fa-chevron-down ml-1"></i></button>
                </div>
            </div>
        </div>

        {{-- Request 3 (Collapsed) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition overflow-hidden opacity-75">
            <div class="px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <span class="font-mono font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded border border-gray-200">#REQ-1026</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Rico (Bread)</h3>
                        <p class="text-xs text-gray-500">Daily Production</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">1 Item</span>
                    <button class="text-chocolate hover:text-chocolate-dark font-bold text-sm">Expand <i class="fas fa-chevron-down ml-1"></i></button>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection