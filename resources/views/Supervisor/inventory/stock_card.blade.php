@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ITEM CONTEXT --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('supervisor.inventory.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-chocolate hover:border-chocolate transition shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-900">Stock Card: Eggs (Large)</h1>
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Dairy & Cold</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">SKU: <span class="font-mono text-gray-700">ING-EGG-L</span> • Unit: <span class="font-mono text-gray-700">Tray (30pcs)</span></p>
            </div>
        </div>
        
        <!-- Item Selector (Quick Switch) -->
        <div class="relative w-full md:w-64">
            <select class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-chocolate focus:border-chocolate rounded-md shadow-sm bg-white">
                <option selected>Eggs (Large)</option>
                <option>Fresh Milk</option>
                <option>Bread Flour</option>
                <option>White Sugar</option>
            </select>
        </div>
    </div>

    {{-- 2. SNAPSHOT METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Current Stock -->
        <div class="bg-white border-l-4 border-blue-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Current Balance</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-gray-900">24.0</span>
                <span class="text-sm text-gray-500">Trays</span>
            </div>
        </div>

        <!-- Reorder Level -->
        <div class="bg-white border-l-4 border-amber-400 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Reorder Level</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-amber-600">10.0</span>
                <span class="text-sm text-gray-500">Trays</span>
            </div>
        </div>

        <!-- Monthly Usage -->
        <div class="bg-white border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg. Daily Usage</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-gray-900">4.5</span>
                <span class="text-sm text-gray-500">Trays/Day</span>
            </div>
        </div>

        <!-- Last Restock -->
        <div class="bg-white border-l-4 border-purple-500 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Last Restock</p>
            <div class="mt-1">
                <span class="text-lg font-bold text-gray-900">Oct 20</span>
                <span class="text-xs text-gray-500 block">4 days ago</span>
            </div>
        </div>
    </div>

    {{-- 3. TRANSACTION HISTORY --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Filter:</span>
                <button class="px-3 py-1 text-xs font-medium bg-chocolate text-white rounded-full shadow-sm">All</button>
                <button class="px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full">Stock In</button>
                <button class="px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full">Stock Out</button>
                <button class="px-3 py-1 text-xs font-medium bg-white text-gray-600 border border-gray-300 hover:bg-gray-100 rounded-full">Adjustments</button>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate">
                <span class="text-gray-400">-</span>
                <input type="date" class="text-xs border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction Type</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Change</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Running Balance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User / Actor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Row 1: Production Use (Stock Out) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Oct 24, 08:30 AM
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">#REQ-1024</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mr-2"></div>
                                <span class="text-sm font-medium text-gray-900">Production Use</span>
                            </div>
                            <p class="text-xs text-gray-500 ml-4">Wedding Cake Order</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-5.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">24.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Baker John
                        </td>
                    </tr>

                    {{-- Row 2: Spoilage (Adjustment) --}}
                    <tr class="bg-red-50/30 hover:bg-red-50 transition-colors border-l-4 border-l-red-400">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Oct 23, 04:15 PM
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-red-600 bg-red-100 px-2 py-1 rounded">#ADJ-099</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                                <span class="text-sm font-medium text-red-800">Spoilage / Damage</span>
                            </div>
                            <p class="text-xs text-red-600 ml-4">Broken during transport</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-1.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">29.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Supervisor Mike
                        </td>
                    </tr>

                    {{-- Row 3: Stock In (Purchase) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Oct 20, 10:00 AM
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-green-600 bg-green-50 px-2 py-1 rounded">#PO-2023-090</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                                <span class="text-sm font-medium text-gray-900">Purchase Receive</span>
                            </div>
                            <p class="text-xs text-gray-500 ml-4">Supplier: Local Farms Inc.</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-green-600">+20.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">30.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Receiver Staff
                        </td>
                    </tr>

                    {{-- Row 4: Production Use --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Oct 19, 08:00 AM
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">#REQ-1018</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mr-2"></div>
                                <span class="text-sm font-medium text-gray-900">Production Use</span>
                            </div>
                            <p class="text-xs text-gray-500 ml-4">Daily Bread Batch</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-5.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">10.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Baker John
                        </td>
                    </tr>
                    
                     {{-- Row 5: Expired (Write-off) --}}
                    <tr class="hover:bg-gray-50 transition-colors bg-gray-50/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            Oct 15, 05:00 PM
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs text-red-600 bg-red-100 px-2 py-1 rounded">#EXP-004</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-gray-500 mr-2"></div>
                                <span class="text-sm font-medium text-gray-600">Expired / Write-off</span>
                            </div>
                            <p class="text-xs text-gray-500 ml-4">Batch #882 (Unused)</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-2.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">15.0</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Supervisor Mike
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">128</span> transactions</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Next</button>
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection