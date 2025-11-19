@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & SUMMARY --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Request Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Review and approve procurement plans from the Purchasing Officer.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                3 Pending
            </div>
            <div class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">
                ₱ 45,200.00 Total Value
            </div>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Search PO number, supplier...">
        </div>

        <!-- Filters -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                <option value="pending" selected>Status: Pending</option>
                <option value="approved">Status: Approved</option>
                <option value="rejected">Status: Rejected</option>
                <option value="high_value">High Value (> ₱10k)</option>
            </select>
        </div>
    </div>

    {{-- 3. PURCHASE REQUESTS LIST --}}
    <div class="space-y-6">

        {{-- PR CARD 1: HIGH VALUE (Warning) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow border-l-4 border-l-red-500 relative">
            <!-- High Value Badge -->
            <div class="absolute top-0 right-0 bg-red-100 text-red-600 text-[10px] font-bold px-3 py-1 rounded-bl-lg border-b border-l border-red-200">
                <i class="fas fa-money-bill-wave mr-1"></i> HIGH VALUE
            </div>

            <div class="p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left: Header Info -->
                    <div class="lg:w-1/4 space-y-4 border-r border-gray-100 pr-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">
                                PO
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">#PR-2023-099</h3>
                                <p class="text-xs text-gray-500">Purchasing Officer</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">
                                <span class="block text-[10px] uppercase text-gray-400 font-bold">Supplier</span>
                                <span class="font-medium"><i class="fas fa-truck text-gray-400 mr-1"></i> Golden Grain Supplies</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="block text-[10px] uppercase text-gray-400 font-bold">Payment Terms</span>
                                <span class="font-medium text-green-600">Net 30 Days</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="block text-[10px] uppercase text-gray-400 font-bold">Expected Delivery</span>
                                <span class="font-medium">Oct 26, 2023</span>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: Items Table -->
                    <div class="lg:w-2/4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Items Requested</h4>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">Sack of Bread Flour</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-600">20</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">₱ 950.00</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">₱ 19,000.00</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">White Sugar (50kg)</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-600">5</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-500">₱ 2,800.00</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">₱ 14,000.00</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-gray-200 bg-gray-50/50">
                                <tr>
                                    <td colspan="3" class="px-3 py-3 text-right text-sm font-bold text-gray-700">Total Estimate:</td>
                                    <td class="px-3 py-3 text-right text-base font-bold text-chocolate">₱ 33,000.00</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="mt-3">
                            <p class="text-xs text-gray-500 italic"><i class="fas fa-info-circle mr-1"></i> Note: Bulk order for upcoming holiday season preparation.</p>
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="lg:w-1/4 flex flex-col justify-center space-y-3 border-l border-gray-100 pl-4">
                        <div class="text-center mb-2">
                            <p class="text-xs text-gray-400 uppercase">Action Required</p>
                        </div>
                        <button class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium text-sm flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i> Approve PO
                        </button>
                        <button class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm flex items-center justify-center">
                            <i class="fas fa-comment-alt mr-2"></i> Request Revision
                        </button>
                        <button class="w-full py-2.5 bg-white border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition shadow-sm font-medium text-sm flex items-center justify-center">
                            <i class="fas fa-times-circle mr-2"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PR CARD 2: STANDARD --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left -->
                    <div class="lg:w-1/4 space-y-4 border-r border-gray-100 pr-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold border border-gray-200">
                                PO
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">#PR-2023-100</h3>
                                <p class="text-xs text-gray-500">Purchasing Officer</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="text-sm text-gray-600">
                                <span class="block text-[10px] uppercase text-gray-400 font-bold">Supplier</span>
                                <span class="font-medium"><i class="fas fa-truck text-gray-400 mr-1"></i> Prime Packaging</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="block text-[10px] uppercase text-gray-400 font-bold">Payment Terms</span>
                                <span class="font-medium text-amber-600">COD (Cash on Delivery)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Middle -->
                    <div class="lg:w-2/4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Items Requested</h4>
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">Cake Boxes (10x10)</td>
                                    <td class="px-3 py-2 text-right text-sm text-gray-600">500 pcs</td>
                                    <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">₱ 7,500.00</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-gray-200 bg-gray-50/50">
                                <tr>
                                    <td colspan="2" class="px-3 py-3 text-right text-sm font-bold text-gray-700">Total Estimate:</td>
                                    <td class="px-3 py-3 text-right text-base font-bold text-chocolate">₱ 7,500.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Right -->
                    <div class="lg:w-1/4 flex flex-col justify-center space-y-3 border-l border-gray-100 pl-4">
                         <button class="w-full py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-medium text-sm flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i> Approve PO
                        </button>
                         <button class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition shadow-sm font-medium text-sm flex items-center justify-center">
                            <i class="fas fa-times-circle mr-2"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection