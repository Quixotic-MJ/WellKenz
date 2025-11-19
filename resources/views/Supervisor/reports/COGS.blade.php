@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cost of Goods Sold (COGS)</h1>
            <p class="text-sm text-gray-500 mt-1">Financial analysis of inventory consumption and profitability.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Date Picker for Report Period -->
            <div class="flex items-center bg-white border border-gray-300 rounded-lg px-3 py-2 shadow-sm">
                <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                <select class="text-sm font-medium text-gray-700 focus:outline-none bg-transparent border-none p-0">
                    <option>Today (Oct 24)</option>
                    <option>Yesterday</option>
                    <option selected>This Week</option>
                    <option>This Month</option>
                </select>
            </div>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Export Report
            </button>
        </div>
    </div>

    {{-- 2. FINANCIAL SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Total COGS -->
        <div class="bg-white border-l-4 border-red-500 rounded-lg p-5 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total COGS</p>
                    <h2 class="text-3xl font-bold text-gray-900 mt-1">₱ 45,250.00</h2>
                    <p class="text-xs text-gray-500 mt-1">Cost of ingredients used</p>
                </div>
                <div class="bg-red-50 p-3 rounded-full">
                    <i class="fas fa-coins text-red-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Sales (Mocked) -->
        <div class="bg-white border-l-4 border-blue-500 rounded-lg p-5 shadow-sm">
             <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Sales</p>
                    <h2 class="text-3xl font-bold text-gray-900 mt-1">₱ 128,500.00</h2>
                    <p class="text-xs text-gray-500 mt-1">Revenue from POS</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-full">
                    <i class="fas fa-cash-register text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Gross Margin -->
        <div class="bg-white border-l-4 border-green-500 rounded-lg p-5 shadow-sm">
             <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gross Margin</p>
                    <h2 class="text-3xl font-bold text-green-600 mt-1">64.8%</h2>
                    <p class="text-xs text-gray-500 mt-1">Profitability Indicator</p>
                </div>
                <div class="bg-green-50 p-3 rounded-full">
                    <i class="fas fa-chart-pie text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- 3. COST BREAKDOWN BY CATEGORY --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-4">Cost Distribution</h3>
            
            <div class="space-y-4">
                <!-- Dairy -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Dairy & Cold (High Value)</span>
                        <span class="font-bold text-gray-900">₱ 22,100 (49%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 49%"></div>
                    </div>
                </div>

                <!-- Dry Goods -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Dry Goods</span>
                        <span class="font-bold text-gray-900">₱ 15,400 (34%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: 34%"></div>
                    </div>
                </div>

                <!-- Packaging -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Packaging</span>
                        <span class="font-bold text-gray-900">₱ 7,750 (17%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-gray-500 h-2 rounded-full" style="width: 17%"></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-3 bg-blue-50 rounded border border-blue-100 text-xs text-blue-800">
                <i class="fas fa-info-circle mr-1"></i> <strong>Insight:</strong> Dairy costs are 5% higher than average this week due to the Wedding Cake orders.
            </div>
        </div>

        {{-- 4. ITEMIZED COGS TABLE --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-bold text-gray-800 uppercase">Top Consumed Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Used</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        
                        {{-- Item 1 --}}
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Heavy Cream</div>
                                <div class="text-xs text-gray-500">Dairy</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                25.0 L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                ₱ 480.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₱ 12,000.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                26.5%
                            </td>
                        </tr>

                        {{-- Item 2 --}}
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Butter (Unsalted)</div>
                                <div class="text-xs text-gray-500">Dairy</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                40.0 kg
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                ₱ 250.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₱ 10,000.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                22.1%
                            </td>
                        </tr>

                        {{-- Item 3 --}}
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Cake Flour</div>
                                <div class="text-xs text-gray-500">Dry Goods</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                150.0 kg
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                ₱ 38.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₱ 5,700.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                12.6%
                            </td>
                        </tr>

                        {{-- Item 4 --}}
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">Eggs (Large)</div>
                                <div class="text-xs text-gray-500">Dairy</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                20 Trays
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                ₱ 240.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₱ 4,800.00
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                10.6%
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 text-center">
                <a href="#" class="text-xs font-medium text-chocolate hover:text-chocolate-dark">View Full Cost Report</a>
            </div>
        </div>

    </div>
</div>
@endsection