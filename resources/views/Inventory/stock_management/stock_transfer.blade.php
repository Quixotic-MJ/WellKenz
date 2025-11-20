@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Internal Stock Transfer</h1>
            <p class="text-sm text-gray-500 mt-1">Move inventory between warehouse sections or to production kitchens.</p>
        </div>
        <button class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
            <i class="fas fa-check mr-2"></i> Confirm Transfer
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- 2. TRANSFER CONFIG (Left) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 border-b border-gray-100 pb-2">Movement Details</h3>
                
                <div class="space-y-4">
                    <!-- Source -->
                    <div class="relative border-l-2 border-gray-300 pl-4 ml-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From (Source)</label>
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <i class="fas fa-warehouse text-gray-400 mr-3"></i>
                            <span class="text-sm font-medium text-gray-900">Main Warehouse</span>
                        </div>
                    </div>

                    <!-- Arrow -->
                    <div class="flex justify-center -my-2 relative z-10">
                        <div class="bg-white p-1 rounded-full border border-gray-200 text-gray-400">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>

                    <!-- Destination -->
                    <div class="relative border-l-2 border-chocolate pl-4 ml-2">
                        <label class="block text-xs font-bold text-chocolate uppercase mb-1">To (Destination)</label>
                        <select class="block w-full border-chocolate rounded-lg shadow-sm focus:ring-chocolate focus:border-chocolate p-3 bg-white text-sm font-medium">
                            <option>Main Kitchen (Production)</option>
                            <option>Pastry Section</option>
                            <option>Bakery Section</option>
                            <option>Staff Cafeteria</option>
                            <option>Scrap / Disposal Area</option>
                        </select>
                    </div>

                    <!-- Reference -->
                    <div class="pt-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Transfer Reference / Notes</label>
                        <textarea rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="e.g. Replenishment for Morning Shift..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. ITEMS TO MOVE (Right) --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-800 uppercase">Items to Transfer</h3>
                    <button class="text-xs text-blue-600 font-bold hover:underline"><i class="fas fa-plus mr-1"></i> Add Another Item</button>
                </div>

                <div class="space-y-4">
                    
                    <!-- Item Row 1 -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex-1 w-full">
                            <label class="block text-xs text-gray-500 mb-1">Item</label>
                            <div class="relative">
                                <input type="text" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" value="White Sugar (50kg)">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-xs text-gray-400">Available: 20</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-full sm:w-32">
                             <label class="block text-xs text-gray-500 mb-1">Batch</label>
                             <select class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate text-xs">
                                 <option>Oldest (FEFO)</option>
                                 <option>Batch A</option>
                                 <option>Batch B</option>
                             </select>
                        </div>
                        <div class="w-full sm:w-24">
                            <label class="block text-xs text-gray-500 mb-1">Qty</label>
                            <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate font-bold" value="2">
                        </div>
                        <button class="text-gray-400 hover:text-red-600 mt-5">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Item Row 2 -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex-1 w-full">
                            <label class="block text-xs text-gray-500 mb-1">Item</label>
                            <div class="relative">
                                <input type="text" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate" placeholder="Search item...">
                            </div>
                        </div>
                        <div class="w-full sm:w-32">
                             <label class="block text-xs text-gray-500 mb-1">Batch</label>
                             <select class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate text-xs" disabled>
                                 <option>Auto-Select</option>
                             </select>
                        </div>
                        <div class="w-full sm:w-24">
                            <label class="block text-xs text-gray-500 mb-1">Qty</label>
                            <input type="number" class="block w-full border-gray-300 rounded-md sm:text-sm focus:ring-chocolate focus:border-chocolate font-bold" placeholder="0">
                        </div>
                        <button class="text-gray-400 hover:text-red-600 mt-5">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection