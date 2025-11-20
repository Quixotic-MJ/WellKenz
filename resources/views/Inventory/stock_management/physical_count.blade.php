@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Physical Count (Audit)</h1>
            <p class="text-sm text-gray-500 mt-1">Monthly inventory check. <span class="text-amber-600 font-bold">Audit #OCT-2023-A</span> in progress.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right mr-4 hidden md:block">
                <p class="text-xs text-gray-400 uppercase font-bold">Status</p>
                <p class="text-sm font-bold text-blue-600">Draft / In-Progress</p>
            </div>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-save mr-2"></i> Save Draft
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> Finalize Count
            </button>
        </div>
    </div>

    {{-- 2. FILTER BAR --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-sm font-medium text-gray-700">Filter Location:</span>
            <select class="block w-48 py-1.5 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option>All Locations</option>
                <option selected>Main Warehouse (Rack A-C)</option>
                <option>Walk-in Freezer</option>
                <option>Staging Area</option>
            </select>
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" class="form-checkbox text-chocolate focus:ring-chocolate border-gray-300 rounded mr-2">
                Show Only Variance
            </label>
        </div>
    </div>

    {{-- 3. COUNT SHEET --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Item / SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100 border-l border-gray-200">System Qty (Frozen)</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-200 bg-yellow-50">Actual Count</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Item 1: Match --}}
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">Bread Flour</div>
                            <div class="text-xs text-gray-500">SKU: ING-001</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Rack A-1
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 font-mono bg-gray-50 border-l border-gray-200">
                            150.50 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border-l border-gray-200 bg-yellow-50/50">
                            <input type="number" class="block w-24 ml-auto text-right border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-gray-900" value="150.50">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                0.00
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" class="block w-full border-transparent bg-transparent focus:border-gray-300 focus:bg-white rounded-md text-xs" placeholder="Add note...">
                        </td>
                    </tr>

                    {{-- Item 2: Variance (Missing) --}}
                    <tr class="bg-red-50/30 hover:bg-red-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">White Sugar</div>
                            <div class="text-xs text-gray-500">SKU: RM-SGR-002</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Rack A-2
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 font-mono bg-gray-50 border-l border-gray-200">
                            22.00 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border-l border-gray-200 bg-yellow-50/50">
                            <input type="number" class="block w-24 ml-auto text-right border-red-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm font-bold text-red-600" value="20.00">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-2.00 kg</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" class="block w-full border-red-200 bg-white rounded-md text-xs text-red-600 placeholder-red-300" value="Bag torn/spilled?">
                        </td>
                    </tr>

                    {{-- Item 3: Variance (Surplus) --}}
                    <tr class="bg-blue-50/30 hover:bg-blue-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">Cake Boxes (10x10)</div>
                            <div class="text-xs text-gray-500">SKU: PCK-BX-10</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Rack C-4
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 font-mono bg-gray-50 border-l border-gray-200">
                            500 pcs
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border-l border-gray-200 bg-yellow-50/50">
                            <input type="number" class="block w-24 ml-auto text-right border-blue-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-bold text-blue-600" value="505">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-blue-600">+5 pcs</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" class="block w-full border-transparent bg-transparent focus:border-gray-300 focus:bg-white rounded-md text-xs" placeholder="Add note...">
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection