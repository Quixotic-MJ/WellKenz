@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase History & Spending Analysis</h1>
            <p class="text-sm text-gray-500 mt-1">Breakdown of procurement costs by category and time period.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white border border-gray-300 rounded-lg px-3 py-2 shadow-sm">
                <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                <select class="text-sm font-medium text-gray-700 focus:outline-none bg-transparent border-none p-0">
                    <option>This Month (Oct 2023)</option>
                    <option>Last Month (Sep 2023)</option>
                    <option>Q3 2023</option>
                    <option>Year to Date</option>
                </select>
            </div>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </div>
    </div>

    {{-- 2. SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border-l-4 border-blue-500 rounded-lg p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Spend (Period)</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">₱ 185,420.00</h2>
            <p class="text-xs text-gray-500 mt-1"><span class="text-green-600 font-bold"><i class="fas fa-arrow-down"></i> 5%</span> vs last month</p>
        </div>
        <div class="bg-white border-l-4 border-amber-500 rounded-lg p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Highest Category</p>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">Dairy & Cold</h2>
            <p class="text-xs text-gray-500 mt-1">₱ 85,200.00 (46% of total)</p>
        </div>
        <div class="bg-white border-l-4 border-purple-500 rounded-lg p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Top Supplier</p>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">Cebu Dairy Corp.</h2>
            <p class="text-xs text-gray-500 mt-1">8 Purchase Orders</p>
        </div>
    </div>

    {{-- 3. VISUAL BREAKDOWN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Category Spend Bar Chart -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-6">Spending by Category</h3>
            <div class="space-y-5">
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Dairy & Cold Products</span>
                        <span class="font-bold text-gray-900">₱ 85,200</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-blue-500 h-4 rounded-full" style="width: 46%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Dry Goods (Flour, Sugar)</span>
                        <span class="font-bold text-gray-900">₱ 55,600</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-amber-400 h-4 rounded-full" style="width: 30%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Packaging Materials</span>
                        <span class="font-bold text-gray-900">₱ 25,400</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-gray-500 h-4 rounded-full" style="width: 14%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Miscellaneous / Other</span>
                        <span class="font-bold text-gray-900">₱ 19,220</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-purple-400 h-4 rounded-full" style="width: 10%"></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Monthly Trend (Mini) -->
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col justify-between">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-4">Monthly Trend</h3>
            <div class="flex items-end justify-between h-48 space-x-2">
                <div class="w-1/5 bg-blue-100 rounded-t h-[60%] relative group">
                    <div class="absolute -top-6 w-full text-center text-[10px] text-gray-500">Jul</div>
                </div>
                <div class="w-1/5 bg-blue-200 rounded-t h-[75%] relative group">
                    <div class="absolute -top-6 w-full text-center text-[10px] text-gray-500">Aug</div>
                </div>
                <div class="w-1/5 bg-blue-300 rounded-t h-[50%] relative group">
                    <div class="absolute -top-6 w-full text-center text-[10px] text-gray-500">Sep</div>
                </div>
                <div class="w-1/5 bg-chocolate rounded-t h-[90%] relative group">
                    <div class="absolute -top-6 w-full text-center text-[10px] font-bold text-chocolate">Oct</div>
                </div>
            </div>
            <p class="text-center text-xs text-gray-500 mt-4">Preparing for Holiday Season Peak</p>
        </div>

    </div>

    {{-- 4. DETAILED HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-800 uppercase">Transaction Log</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 24, 2023</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">#PO-2023-105</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cebu Dairy Corp.</td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Dairy</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₱ 12,500.00</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-chocolate hover:underline">View</a>
                    </td>
                </tr>
                 <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 22, 2023</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">#PO-2023-104</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Golden Grain</td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Dry Goods</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₱ 8,400.00</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-chocolate hover:underline">View</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection