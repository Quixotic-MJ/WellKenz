@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Order Drafts</h1>
            <p class="text-sm text-gray-500 mt-1">Incomplete orders not yet sent for approval.</p>
        </div>
        <a href="{{ route('purchasing.po.create') }}" class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> New PO
        </a>
    </div>

    {{-- DRAFTS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draft ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items Summary</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-600">#DFT-005</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Local Farms Inc.</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Fresh Eggs, Milk... (3 items)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">₱ 3,200.00</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                        <a href="#" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>

                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-600">#DFT-004</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Prime Packaging</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Ribbons, Tape (5 items)</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">₱ 1,500.00</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                        <a href="#" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
@endsection