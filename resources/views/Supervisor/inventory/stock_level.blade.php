@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Live Stock Levels</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time view of current warehouse inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Stock Sheet
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- 2. METRICS SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total SKU -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">1,450</p>
            </div>
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <!-- Good Stock -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-green-600 uppercase tracking-wider">Healthy Stock</p>
                <p class="text-2xl font-bold text-green-700 mt-1">1,420</p>
            </div>
            <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white border-l-4 border-amber-400 border-y border-r border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Low Stock</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">25</p>
            </div>
            <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center text-amber-600">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <!-- Critical / Out -->
        <div class="bg-white border-l-4 border-red-500 border-y border-r border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Critical / Out</p>
                <p class="text-2xl font-bold text-red-700 mt-1">5</p>
            </div>
            <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search Item Name, SKU...">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Categories</option>
                <option value="dry">Dry Goods</option>
                <option value="dairy">Dairy & Cold</option>
                <option value="packaging">Packaging</option>
            </select>
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="">All Statuses</option>
                <option value="good">Good</option>
                <option value="low">Low</option>
                <option value="critical">Critical</option>
            </select>
        </div>
    </div>

    {{-- 4. INVENTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Physical Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Movement</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    {{-- Item 1: Good Stock --}}
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">150.5 kg</div>
                            <!-- Visual Health Bar -->
                            <div class="w-24 h-1.5 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: 85%"></div>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Reorder at 50 kg</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Good
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            2 hours ago <span class="text-xs text-gray-400">(Stock Out)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-border-soft px-3 py-1.5 rounded hover:bg-cream-bg transition">
                                View Card
                            </a>
                        </td>
                    </tr>

                    {{-- Item 2: Low Stock --}}
                    <tr class="hover:bg-gray-50 transition-colors bg-amber-50/30">
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-amber-700">22.0 kg</div>
                            <!-- Visual Health Bar -->
                            <div class="w-24 h-1.5 bg-gray-200 rounded-full mt-1 overflow-hidden">
                                <div class="bg-amber-500 h-1.5 rounded-full" style="width: 30%"></div>
                            </div>
                            <div class="text-[10px] text-amber-600 mt-0.5 font-medium">Near Reorder (20 kg)</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                <i class="fas fa-exclamation-circle mr-1"></i> Low Stock
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Yesterday <span class="text-xs text-gray-400">(Stock Out)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-border-soft px-3 py-1.5 rounded hover:bg-cream-bg transition">
                                View Card
                            </a>
                        </td>
                    </tr>

                    {{-- Item 3: Critical Stock --}}
                    <tr class="hover:bg-gray-50 transition-colors bg-red-50/30 border-l-4 border-l-red-400">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-purple-50 rounded flex items-center justify-center text-purple-600">
                                    <i class="fas fa-wine-bottle"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Vanilla Extract</div>
                                    <div class="text-xs text-gray-500">SKU: RM-VAN-050</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Liquids
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-red-700">1.5 L</div>
                            <!-- Visual Health Bar -->
                            <div class="w-24 h-1.5 bg-red-100 rounded-full mt-1 overflow-hidden">
                                <div class="bg-red-600 h-1.5 rounded-full" style="width: 10%"></div>
                            </div>
                            <div class="text-[10px] text-red-600 mt-0.5 font-bold">Below Reorder (5 L)</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Critical
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            3 days ago <span class="text-xs text-gray-400">(Adjustment)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-border-soft px-3 py-1.5 rounded hover:bg-cream-bg transition">
                                View Card
                            </a>
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
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-chocolate text-white text-sm font-medium">1</button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">2</button>
                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection