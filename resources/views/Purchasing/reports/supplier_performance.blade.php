@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supplier Performance</h1>
            <p class="text-sm text-gray-500 mt-1">Evaluate lead times, quality issues, and overall reliability.</p>
        </div>
    </div>

    {{-- SCORECARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Avg. Lead Time</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">2.5 Days</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">On-Time Delivery</p>
            <p class="text-2xl font-bold text-green-600 mt-1">94%</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Rejection Rate</p>
            <p class="text-2xl font-bold text-green-600 mt-1">1.2%</p>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Active Vendors</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">12</p>
        </div>
    </div>

    {{-- PERFORMANCE TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Avg. Lead Time</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">On-Time %</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quality Issues (YTD)</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rating</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                
                {{-- Vendor 1: Excellent --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Prime Packaging Corp.</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Packaging</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">1 Day</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 font-bold">100%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">0</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </td>
                </tr>

                {{-- Vendor 2: Good --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Cebu Dairy Corp.</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dairy</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">2 Days</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 font-bold">95%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">2</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star text-gray-300"></i>
                    </td>
                </tr>

                {{-- Vendor 3: Problematic --}}
                <tr class="hover:bg-red-50 bg-red-50/30 border-l-4 border-l-red-400">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">Golden Grain Supplies</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Dry Goods</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 font-bold">4 Days</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 font-bold">75%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 font-bold">5</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star text-gray-300"></i><i class="far fa-star text-gray-300"></i><i class="far fa-star text-gray-300"></i>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection