@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Open Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Approved and sent orders awaiting delivery.</p>
        </div>
    </div>

    {{-- STATUS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg flex justify-between items-center">
            <span class="text-blue-800 font-bold text-sm">Awaiting Delivery</span>
            <span class="text-2xl font-bold text-blue-900">8</span>
        </div>
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg flex justify-between items-center">
            <span class="text-amber-800 font-bold text-sm">Delayed / Overdue</span>
            <span class="text-2xl font-bold text-amber-900">2</span>
        </div>
    </div>

    {{-- OPEN ORDERS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                {{-- Order 1: On Time --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-blue-600 font-bold">#PO-2023-102</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Prime Packaging</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 26, 2023</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱ 7,500.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Sent / On Track
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-green-600 hover:text-green-900 border border-green-200 bg-green-50 px-3 py-1 rounded">
                            Receive Items
                        </button>
                    </td>
                </tr>

                {{-- Order 2: Late --}}
                <tr class="bg-amber-50/50 hover:bg-amber-50 border-l-4 border-l-amber-400">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-blue-600 font-bold">#PO-2023-088</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Golden Grain Supplies</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-bold">Oct 22, 2023 (+2 Days)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱ 18,400.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Overdue
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-gray-600 hover:text-gray-900 mr-2">Follow Up</button>
                        <button class="text-green-600 hover:text-green-900 border border-green-200 bg-green-50 px-3 py-1 rounded">
                            Receive Items
                        </button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
@endsection