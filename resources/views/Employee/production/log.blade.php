@extends('Employee.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daily Production Log</h1>
            <p class="text-sm text-gray-500 mt-1">Record finished goods for {{ date('F d, Y') }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-xs text-gray-400 uppercase font-bold">Shift Total</p>
                <p class="text-lg font-bold text-chocolate">150 Units</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 2. ENTRY FORM (Left) --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                    <i class="fas fa-plus-circle mr-2 text-chocolate"></i> New Entry
                </h3>
                
                <form>
                    <div class="space-y-5">
                        
                        <!-- Product Select -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate py-2">
                                <option value="" disabled selected>Select product...</option>
                                <option>Egg Pie (Whole)</option>
                                <option>Chocolate Cake (8")</option>
                                <option>Soft Roll (Pack of 12)</option>
                                <option>Cheese Ensaymada</option>
                            </select>
                        </div>

                        <!-- Batch Number (Auto/Manual) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch / Lot #</label>
                            <div class="relative">
                                <input type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" value="BATCH-{{ date('ymd') }}-01">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Good -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-green-700 mb-1">Good Output</label>
                                <input type="number" class="block w-full border-green-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-lg font-bold text-green-800" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-red-600 mb-1">Rejects / Waste</label>
                                <input type="number" class="block w-full border-red-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-lg font-bold text-red-600" placeholder="0">
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Any issues during baking?"></textarea>
                        </div>

                        <button class="w-full py-3 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Log Production
                        </button>

                    </div>
                </form>
            </div>
        </div>

        {{-- 3. TODAY'S LOG (Right) --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-800 uppercase">Recorded Today</h3>
                <button class="text-xs text-chocolate hover:underline">Download Report</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Good</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reject</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        
                        {{-- Entry 1 --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                10:30 AM
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">Egg Pie (Whole)</p>
                                <p class="text-xs text-gray-500">Batch: 231024-02</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                                50 pcs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-400">
                                0
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Logged</span>
                            </td>
                        </tr>

                        {{-- Entry 2 --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                08:15 AM
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">Soft Roll (12s)</p>
                                <p class="text-xs text-gray-500">Batch: 231024-01</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                                100 packs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-500">
                                5 packs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Logged</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection