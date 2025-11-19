@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Inventory Adjustments</h1>
            <p class="text-sm text-gray-500 mt-1">Record write-offs for spoilage, breakage, or correct inventory variances.</p>
        </div>
        <!-- Quick Stats -->
        <div class="flex gap-4">
            <div class="text-right">
                <p class="text-xs text-gray-400 uppercase">Total Loss (Today)</p>
                <p class="text-lg font-bold text-red-600">₱ 1,250.00</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 2. ADJUSTMENT FORM (Left Column) --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                    <i class="fas fa-pen-square mr-2 text-chocolate"></i> Create Ticket
                </h3>
                
                <form>
                    <div class="space-y-5">
                        
                        <!-- Item Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Item <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm rounded-md">
                                    <option value="" disabled selected>Search item...</option>
                                    <option value="sugar">White Sugar (50kg)</option>
                                    <option value="eggs">Eggs (Large)</option>
                                    <option value="milk">Fresh Milk</option>
                                </select>
                                <!-- Contextual Stock Display -->
                                <div class="mt-1 flex justify-between text-xs">
                                    <span class="text-gray-500">Current Stock:</span>
                                    <span class="font-bold text-gray-800">150.00 kg</span>
                                </div>
                            </div>
                        </div>

                        <!-- Adjustment Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                            <div class="flex gap-3">
                                <!-- Radio Option: Deduction (Default) -->
                                <label class="relative flex-1 cursor-pointer group">
                                    <input type="radio" name="adj_type" value="out" class="peer sr-only" checked>
                                    <div class="flex items-center justify-center p-2 text-gray-500 bg-white border border-gray-200 rounded-lg peer-checked:border-red-500 peer-checked:text-red-600 peer-checked:bg-red-50 transition">
                                        <i class="fas fa-minus-circle mr-2"></i> Remove (Loss)
                                    </div>
                                </label>
                                <!-- Radio Option: Addition -->
                                <label class="relative flex-1 cursor-pointer group">
                                    <input type="radio" name="adj_type" value="in" class="peer sr-only">
                                    <div class="flex items-center justify-center p-2 text-gray-500 bg-white border border-gray-200 rounded-lg peer-checked:border-green-500 peer-checked:text-green-600 peer-checked:bg-green-50 transition">
                                        <i class="fas fa-plus-circle mr-2"></i> Add (Return)
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quantity & Unit -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                                <input type="number" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                <input type="text" class="block w-full border-gray-200 bg-gray-50 rounded-md sm:text-sm text-gray-500 cursor-not-allowed" value="kg" disabled>
                            </div>
                        </div>

                        <!-- Reason Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason Code <span class="text-red-500">*</span></label>
                            <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="" disabled selected>Select a reason...</option>
                                <optgroup label="Inventory Loss (Write-off)">
                                    <option value="spoilage">Spoilage / Expired</option>
                                    <option value="damage">Damaged / Broken</option>
                                    <option value="spillage">Spillage (Production)</option>
                                    <option value="theft">Theft / Missing</option>
                                </optgroup>
                                <optgroup label="Inventory Correction">
                                    <option value="audit_correction">Audit Variance Correction</option>
                                    <option value="found">Found Item</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks / Details <span class="text-red-500">*</span></label>
                            <textarea rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Describe what happened (e.g., 'Sack tore while unloading')..."></textarea>
                        </div>

                        <!-- Proof Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Attach Photo (Optional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition cursor-pointer">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-camera text-gray-400 text-2xl"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <span class="relative bg-white rounded-md font-medium text-chocolate hover:text-chocolate-dark">
                                            Upload a file
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-6">
                        <button type="button" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-chocolate hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                            Submit Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. RECENT HISTORY (Right Column) --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800 uppercase">Recent Write-offs & Adjustments</h3>
                    <div class="flex space-x-2">
                        <span class="text-xs text-gray-500 bg-white border border-gray-200 px-2 py-1 rounded">This Month</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item / Reason</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Adjustment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            
                            {{-- Row 1: Spillage (The requested example) --}}
                            <tr class="hover:bg-red-50 transition-colors border-l-4 border-red-400">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    Today, 10:30 AM
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">White Sugar</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Reason: <span class="text-red-600 font-semibold">Spillage</span></div>
                                    <div class="text-[10px] text-gray-400 italic mt-0.5">"Bag ripped open in storage room"</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-red-600">-5.00 kg</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock mr-1"></i> Pending Review
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-gray-400 hover:text-chocolate"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>

                            {{-- Row 2: Spoilage --}}
                            <tr class="hover:bg-red-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    Oct 23, 09:00 AM
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">Fresh Milk</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Reason: <span class="text-red-600 font-semibold">Spoilage / Expired</span></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-red-600">-2.00 L</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Approved
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-gray-400 hover:text-chocolate"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>

                            {{-- Row 3: Found Item (Correction) --}}
                            <tr class="hover:bg-green-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    Oct 20, 05:00 PM
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">Vanilla Extract</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Reason: <span class="text-green-600 font-semibold">Found Item</span></div>
                                    <div class="text-[10px] text-gray-400 italic mt-0.5">"Misplaced during last count"</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-green-600">+1.00 L</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Approved
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-gray-400 hover:text-chocolate"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 text-center">
                    <a href="#" class="text-xs font-medium text-gray-500 hover:text-gray-700">View All Adjustments</a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection