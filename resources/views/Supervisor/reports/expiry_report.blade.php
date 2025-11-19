@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Expiry Monitoring Report</h1>
            <p class="text-sm text-gray-500 mt-1">Track expiring batches to minimize waste and prioritize usage.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i> Print "Use First" List
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-bullhorn mr-2"></i> Alert Bakers
            </button>
        </div>
    </div>

    {{-- 2. RISK SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Critical (Today/Tomorrow) -->
        <div class="bg-white border-l-4 border-red-500 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Critical (< 48 hrs)</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">3 Batches</p>
                <p class="text-xs text-gray-500 mt-1">Action required immediately</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-hourglass-end text-xl"></i>
            </div>
        </div>

        <!-- Warning (Next 7 Days) -->
        <div class="bg-white border-l-4 border-amber-500 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Warning (7 Days)</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">8 Batches</p>
                <p class="text-xs text-gray-500 mt-1">Plan into production schedule</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                <i class="fas fa-calendar-week text-xl"></i>
            </div>
        </div>

        <!-- Value at Risk -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Value at Risk</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">₱ 4,250.00</p>
                <p class="text-xs text-gray-500 mt-1">Potential loss if unused</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-coins text-xl"></i>
            </div>
        </div>
    </div>

    {{-- 3. EXPIRY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Show:</span>
                <select class="block w-40 py-1.5 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
                    <option>Next 7 Days</option>
                    <option>Next 30 Days</option>
                    <option>Already Expired</option>
                </select>
            </div>
            <div class="relative w-full md:w-64">
                <input type="text" class="block w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs" placeholder="Filter by item...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item / Batch Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Countdown</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Qty</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Priority Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    {{-- Row 1: CRITICAL (Today) --}}
                    <tr class="bg-red-50 hover:bg-red-100 transition-colors border-l-4 border-red-500">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-white rounded border border-red-200 flex items-center justify-center text-red-600 font-bold text-xs">
                                    MILK
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Fresh Milk</div>
                                    <div class="text-xs text-gray-500">Batch #BM-2023-882</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">
                            Oct 24, 2023
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-bold text-white bg-red-600 rounded animate-pulse">
                                EXPIRES TODAY
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">2.0 L</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-bold text-red-800 uppercase tracking-wide">Use Immediately</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-white bg-chocolate hover:bg-chocolate-dark px-3 py-1 rounded text-xs shadow-sm transition">
                                Use Now
                            </button>
                        </td>
                    </tr>

                    {{-- Row 2: CRITICAL (Tomorrow) --}}
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500 text-lg">
                                    <i class="fas fa-bread-slice"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Fresh Yeast</div>
                                    <div class="text-xs text-gray-500">Batch #YST-005</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Oct 25, 2023
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-bold text-red-600 bg-red-100 rounded border border-red-200">
                                1 Day Left
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">500 g</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-bold text-red-600">High Priority</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-chocolate/30 px-3 py-1 rounded hover:bg-orange-50 transition">
                                Use Now
                            </button>
                        </td>
                    </tr>

                    {{-- Row 3: WARNING (3 Days) --}}
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500 text-lg">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Heavy Cream</div>
                                    <div class="text-xs text-gray-500">Batch #CRM-101</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Oct 27, 2023
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-medium text-amber-700 bg-amber-100 rounded">
                                3 Days Left
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">5.0 L</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-medium text-amber-600">Plan Usage</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-500 hover:text-gray-700 font-bold text-xs border border-gray-300 px-3 py-1 rounded hover:bg-gray-50 transition">
                                Details
                            </button>
                        </td>
                    </tr>

                    {{-- Row 4: WARNING (5 Days) --}}
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-500 text-lg">
                                    <i class="fas fa-lemon"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">Cream Cheese</div>
                                    <div class="text-xs text-gray-500">Batch #CHZ-220</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Oct 29, 2023
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs font-medium text-amber-700 bg-amber-100 rounded">
                                5 Days Left
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">2.5 kg</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-xs font-medium text-amber-600">Monitor</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-500 hover:text-gray-700 font-bold text-xs border border-gray-300 px-3 py-1 rounded hover:bg-gray-50 transition">
                                Details
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        <!-- Footer Note -->
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center italic">
                Note: Items marked "Use Immediately" should be prioritized in today's production or transferred to the staff cafeteria to avoid waste.
            </p>
        </div>
    </div>

</div>
@endsection