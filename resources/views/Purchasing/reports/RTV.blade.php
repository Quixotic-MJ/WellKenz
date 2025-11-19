@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Return to Vendor (RTV) Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Track returned items and credit note status.</p>
        </div>
        <button class="flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-sm text-sm font-medium">
            <i class="fas fa-undo mr-2"></i> Create Return
        </button>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div class="bg-white border border-gray-200 p-4 rounded-lg flex justify-between items-center shadow-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase">Total Returned (YTD)</p>
                <p class="text-xl font-bold text-gray-900">₱ 15,400.00</p>
            </div>
            <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-history"></i>
            </div>
        </div>
        <div class="bg-white border border-red-200 p-4 rounded-lg flex justify-between items-center shadow-sm bg-red-50/50">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase">Pending Credits</p>
                <p class="text-xl font-bold text-red-700">₱ 2,800.00</p>
            </div>
            <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    {{-- RTV TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RTV Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Original PO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items / Reason</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                
                {{-- Pending Return --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900 font-bold">#RTV-23-005</td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-blue-600">#PO-2023-088</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Golden Grain Supplies</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">White Sugar (1 Sack)</div>
                        <div class="text-xs text-red-500 italic">"Damaged/Wet upon delivery"</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₱ 2,800.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                            Pending Credit
                        </span>
                    </td>
                </tr>

                {{-- Completed Return --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900 font-bold">#RTV-23-004</td>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-blue-600">#PO-2023-080</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cebu Dairy Corp.</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">Heavy Cream (2L)</div>
                        <div class="text-xs text-gray-500 italic">"Near Expiry Date"</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">₱ 960.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Credit Received
                        </span>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection