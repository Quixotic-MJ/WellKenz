@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER (RESTRICTED STYLE) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-red-50 border border-red-200 p-4 rounded-lg">
        <div>
            <h1 class="text-2xl font-bold text-red-900 flex items-center">
                <i class="fas fa-lock mr-3"></i> Direct Issuance
            </h1>
            <p class="text-sm text-red-700 mt-1">Restricted Action: Issue stock without a prior request.</p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-xs font-bold text-red-400 uppercase tracking-wider">Security Level</p>
            <p class="text-sm font-bold text-red-800">Supervisor Override Required</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- 2. ISSUANCE FORM --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-2">Issuance Details</h3>
                
                <form>
                    <div class="space-y-5">
                        
                        <!-- Recipient -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Issued To (Staff)</label>
                                <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option>Baker John Doe</option>
                                    <option>Maria (Pastry)</option>
                                    <option>Rico (Bread)</option>
                                    <option>Other / External</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department / Section</label>
                                <input type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="e.g. Main Kitchen">
                            </div>
                        </div>

                        <!-- Item Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Item</label>
                            <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                <option value="" disabled selected>Search inventory...</option>
                                <option>Eggs (Large)</option>
                                <option>Bread Flour</option>
                                <option>White Sugar</option>
                            </select>
                            <div class="mt-2 p-3 bg-gray-50 rounded border border-gray-200 flex justify-between items-center text-sm">
                                <span class="text-gray-500">Current Availability:</span>
                                <span class="font-bold text-gray-900">150.50 kg</span>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Issue</label>
                                <input type="number" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm font-bold text-red-600" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                                <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option>Emergency Production</option>
                                    <option>Replacement (Spoilage)</option>
                                    <option>Sample / Testing</option>
                                    <option>Transfer Out</option>
                                </select>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                            <textarea rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Why was a requisition not created?"></textarea>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- 3. SECURITY OVERRIDE PANEL --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm p-6 h-full flex flex-col justify-center">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-shield text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Authorization</h3>
                    <p class="text-xs text-gray-500 mt-1">A supervisor must enter their PIN to authorize this direct issuance.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 text-center">Supervisor PIN</label>
                        <input type="password" class="block w-full text-center tracking-[0.5em] text-xl font-bold border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 py-3" placeholder="••••">
                    </div>
                    
                    <button class="w-full py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition shadow-sm flex items-center justify-center">
                        <i class="fas fa-key mr-2"></i> Authorize & Issue
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection