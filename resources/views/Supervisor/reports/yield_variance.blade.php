@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Yield Variance Report</h1>
            <p class="text-sm text-gray-500 mt-1">Compare theoretical recipe usage against actual inventory consumption.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Export CSV
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Report
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date Range</label>
                <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option>Today</option>
                    <option selected>This Week</option>
                    <option>This Month</option>
                    <option>Custom Range</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Product Category</label>
                <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option>All Products</option>
                    <option>Cakes</option>
                    <option>Breads</option>
                    <option>Pastries</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Ingredient Filter</label>
                <input type="text" placeholder="e.g. Butter" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
            </div>
            <div class="md:col-span-1 flex items-end">
                <button class="w-full py-2 px-4 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- 3. KEY METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Efficiency Score -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Production Efficiency</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">96.5%</p>
                <p class="text-xs text-green-600 mt-1 font-medium"><i class="fas fa-arrow-up mr-1"></i> 1.2% vs last week</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>

        <!-- Total Variance Loss -->
        <div class="bg-white border-l-4 border-red-500 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Variance Loss</p>
                <p class="text-3xl font-bold text-red-600 mt-1">-₱ 1,850.00</p>
                <p class="text-xs text-gray-500 mt-1">Cost of over-usage/waste</p>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>

        <!-- Top Issue -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Highest Variance Item</p>
                <p class="text-xl font-bold text-gray-900 mt-1">White Sugar</p>
                <p class="text-xs text-red-600 mt-1 font-bold">-5.2 kg (Over-usage)</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>
    </div>

    {{-- 4. VARIANCE TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingredient</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Theoretical (Recipe)</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual (Stock Out)</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Impact</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Row 1: Significant Loss (Red) --}}
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">Chocolate Cake (100 pcs)</div>
                            <div class="text-xs text-gray-500">Batch #prod-882 • Oct 24</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">White Sugar</div>
                            <div class="text-xs text-gray-500">Dry Goods</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            20.00 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            22.50 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-red-600">-2.50 kg</span>
                            <span class="text-xs text-red-400 block">(12.5%)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                            -₱ 140.00
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Waste
                            </span>
                        </td>
                    </tr>

                    {{-- Row 2: Good / Efficient (Green) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">Pandesal (500 pcs)</div>
                            <div class="text-xs text-gray-500">Batch #prod-883 • Oct 24</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Bread Flour</div>
                            <div class="text-xs text-gray-500">Dry Goods</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            15.00 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            14.90 kg
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-green-600">+0.10 kg</span>
                            <span class="text-xs text-green-500 block">(0.6%)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                            +₱ 3.80
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Efficient
                            </span>
                        </td>
                    </tr>

                    {{-- Row 3: Minor Variance (Yellow) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">Strawberry Tart (50 pcs)</div>
                            <div class="text-xs text-gray-500">Batch #prod-881 • Oct 23</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">Fresh Milk</div>
                            <div class="text-xs text-gray-500">Dairy</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                            5.00 L
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                            5.20 L
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-amber-600">-0.20 L</span>
                            <span class="text-xs text-amber-500 block">(4.0%)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-amber-600">
                            -₱ 18.00
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Check
                            </span>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">45</span> batches</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</button>
                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</button>
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection