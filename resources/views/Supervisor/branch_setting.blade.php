@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Minimum Stock Configuration</h1>
            <p class="text-sm text-gray-500 mt-1">Define safety stock thresholds to trigger reorder alerts automatically.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-save mr-2"></i> Save All Changes
            </button>
        </div>
    </div>

    {{-- 2. SEASONAL ADJUSTMENT WIDGET --}}
    <div class="bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-lg p-5 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <i class="fas fa-snowflake text-6xl text-blue-900"></i>
        </div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wider mb-2">
                <i class="fas fa-magic mr-2"></i> Seasonal Buffer Tool
            </h3>
            <p class="text-xs text-blue-700 mb-4 max-w-2xl">
                Preparing for a holiday rush (e.g., Christmas)? Quickly increase reorder levels for entire categories to prevent stockouts.
            </p>
            
            <div class="flex flex-col md:flex-row items-end gap-4">
                <div>
                    <label class="block text-xs font-bold text-blue-800 mb-1">Category</label>
                    <select class="block w-48 py-1.5 px-3 border-blue-200 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option>Dry Goods (Flour, Sugar)</option>
                        <option>Dairy</option>
                        <option>Packaging</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-blue-800 mb-1">Increase By (%)</label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="number" class="block w-24 pl-3 pr-8 py-1.5 border-blue-200 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="20">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-xs">%</span>
                        </div>
                    </div>
                </div>
                <button class="px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded hover:bg-blue-700 transition shadow-sm">
                    Apply Adjustment
                </button>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search Item Name, SKU...">
        </div>

        <!-- Filter -->
        <div class="w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                <option value="dry">Dry Goods</option>
                <option value="dairy">Dairy & Cold</option>
                <option value="packaging">Packaging</option>
            </select>
        </div>
    </div>

    {{-- 4. CONFIGURATION TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Reorder Level (Min)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    {{-- Item 1: High Demand (Needs Adjustment) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-50 rounded flex items-center justify-center text-amber-600">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Bread Flour</div>
                                    <div class="text-xs text-gray-500">SKU: ING-001</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Dry Goods
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-medium text-gray-900">150.5 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" class="block w-full text-right pr-8 border-gray-300 rounded-md focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-chocolate bg-orange-50" value="50">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-xs">kg</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden mr-2">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Healthy</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Item 2: Sugar (Critical - Needs Higher Buffer) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-amber-50 rounded flex items-center justify-center text-amber-600">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">White Sugar</div>
                                    <div class="text-xs text-gray-500">SKU: RM-SGR-002</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                Dry Goods
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-medium text-amber-600">22.0 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" class="block w-full text-right pr-8 border-gray-300 rounded-md focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-chocolate" value="20">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-xs">kg</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden mr-2">
                                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 110%"></div>
                                </div>
                                <span class="text-xs text-amber-600 font-medium">Low Buffer</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Item 3: Packaging --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-600">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Cake Box (10x10)</div>
                                    <div class="text-xs text-gray-500">SKU: PCK-BX-10</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Packaging
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-medium text-gray-900">500 pcs</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" class="block w-full text-right pr-8 border-gray-300 rounded-md focus:ring-chocolate focus:border-chocolate sm:text-sm font-bold text-chocolate" value="100">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-xs">pcs</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden mr-2">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Healthy</span>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">1450</span> results</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Next</button>
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection