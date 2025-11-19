@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase History</h1>
            <p class="text-sm text-gray-500 mt-1">Archive of all completed and fully delivered orders.</p>
        </div>
        <div class="flex items-center space-x-3">
            <input type="text" placeholder="Search history..." class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-chocolate focus:border-chocolate">
            <button class="bg-white border border-gray-300 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </div>
    </div>

    {{-- HISTORY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Completed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 15, 2023</td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">#PO-2023-085</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Golden Grain Supplies</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱ 12,500.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                            Completed
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-chocolate hover:text-chocolate-dark"><i class="fas fa-eye"></i> View</a>
                    </td>
                </tr>

                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Oct 10, 2023</td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">#PO-2023-082</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Prime Packaging</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">₱ 5,200.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                            Completed
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-chocolate hover:text-chocolate-dark"><i class="fas fa-eye"></i> View</a>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
@endsection