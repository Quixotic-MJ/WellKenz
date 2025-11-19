@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
            <p class="text-sm text-gray-500 mt-1">Step 1: Select Vendor & Add Items</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-save mr-2"></i> Save Draft
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-paper-plane mr-2"></i> Submit for Approval
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 2. PO HEADER INFO (Left Col) --}}
        <div class="lg:col-span-2 space-y-6">
            <!-- Vendor Selection Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-100 pb-2 mb-4">Vendor Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Select Supplier</label>
                        <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm py-2">
                            <option value="" disabled selected>Choose vendor...</option>
                            <option value="1">Golden Grain Supplies</option>
                            <option value="2">Prime Packaging Corp.</option>
                            <option value="3">Cebu Dairy Corp.</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Terms</label>
                        <input type="text" class="block w-full border-gray-200 bg-gray-50 rounded-md sm:text-sm text-gray-500 cursor-not-allowed" value="Net 30 Days (Auto-filled)" disabled>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Shipping Address</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded border border-gray-200">
                            <strong>WellKenz Main Commissary</strong><br>
                            123 Baker Street, Culinary District, Cebu City
                        </p>
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-800 uppercase">Order Items</h3>
                    <button class="text-xs text-blue-600 hover:underline font-bold">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-5/12">Item</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Qty</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Unit Price</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-2/12">Total</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-1/12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- Row 1 -->
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="text" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="Search item...">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" value="10">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" value="950.00">
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-gray-900">
                                    9,500.00
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Row 2 -->
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="text" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="Search item..." value="White Sugar (50kg)">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" value="5">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm text-right focus:ring-chocolate focus:border-chocolate" value="2800.00">
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-gray-900">
                                    14,000.00
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-right text-sm font-bold text-gray-700">Grand Total</td>
                                <td class="px-3 py-3 text-right text-base font-bold text-chocolate">₱ 23,500.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- 3. SIDEBAR INFO (Right Col) --}}
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Details -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase border-b border-gray-100 pb-2 mb-4">Order Logistics</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">PO Number</label>
                        <input type="text" class="block w-full border-gray-200 bg-gray-50 rounded-md sm:text-sm text-gray-500" value="PO-2023-103 (Auto)" disabled>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Order Date</label>
                        <input type="date" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Expected Delivery</label>
                        <input type="date" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Notes to Vendor</label>
                        <textarea rows="3" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="e.g. Deliver to rear entrance..."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Workflow Guide -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                    <div class="text-xs text-blue-800">
                        <p class="font-bold mb-1">Workflow:</p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Save as <strong>Draft</strong> if incomplete.</li>
                            <li><strong>Submit</strong> to send to Supervisor.</li>
                            <li>Once approved, you can <strong>Email</strong> to vendor.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection