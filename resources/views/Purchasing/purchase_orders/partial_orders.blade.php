@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between bg-red-50 border border-red-200 p-4 rounded-lg">
        <div>
            <h1 class="text-2xl font-bold text-red-900">Partial / Backorders</h1>
            <p class="text-sm text-red-700 mt-1">Critical Attention: Orders received with missing items.</p>
        </div>
        <div class="h-10 w-10 bg-red-200 rounded-full flex items-center justify-center text-red-700">
            <i class="fas fa-exclamation-triangle text-xl"></i>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Missing Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-mono text-sm text-gray-900 font-bold">#PO-2023-095</div>
                        <div class="text-xs text-gray-500">Received: Oct 20</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Cebu Dairy Corp.
                    </td>
                    <td class="px-6 py-4">
                        <ul class="list-disc list-inside text-sm text-red-600">
                            <li>Heavy Cream (5L missing)</li>
                        </ul>
                        <p class="text-xs text-gray-500 mt-1">Vendor Note: "Stockout, will deliver Oct 25"</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                            Pending Completion
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded border border-blue-200">
                            Receive Remaining
                        </button>
                        <button class="text-gray-500 hover:text-gray-700 ml-2 text-xs">
                            Cancel Balance
                        </button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection