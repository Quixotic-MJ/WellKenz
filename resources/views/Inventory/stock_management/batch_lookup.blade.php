@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & SEARCH --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Batch Locator</h1>
        <p class="text-sm text-gray-500 mb-6">Find exactly where specific items or batches are stored in the warehouse.</p>
        
        <div class="max-w-2xl mx-auto relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-lg"></i>
            </div>
            <input type="text" class="block w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-full shadow-sm focus:ring-chocolate focus:border-chocolate text-lg" placeholder="Scan Barcode or Type Item Name / Batch #..." autofocus>
            <button class="absolute inset-y-1 right-1 px-6 bg-chocolate text-white font-medium rounded-full hover:bg-chocolate-dark transition">
                Search
            </button>
        </div>
    </div>

    {{-- 2. SEARCH RESULTS (Example) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Result 1: Active Batch --}}
        <div class="bg-white border border-l-4 border-l-green-500 border-y border-r border-gray-200 rounded-lg shadow-sm hover:shadow-md transition p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-700 text-xl">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Bread Flour</h3>
                        <p class="text-sm text-gray-500">SKU: ING-001</p>
                    </div>
                </div>
                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full">Active</span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold">Batch Number</p>
                    <p class="font-mono text-gray-800 font-bold mt-1">BF-2023-10-01</p>
                </div>
                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold">Expiry Date</p>
                    <p class="text-gray-800 font-medium mt-1">Dec 01, 2024</p>
                </div>
                <div class="bg-blue-50 p-3 rounded border border-blue-100 col-span-2 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-blue-600 uppercase font-bold">Warehouse Location</p>
                        <p class="text-lg text-blue-900 font-bold mt-1"><i class="fas fa-map-marker-alt mr-2"></i> Rack A-1, Shelf 2</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-600 uppercase font-bold">Qty Here</p>
                        <p class="text-lg text-blue-900 font-bold">25 Sacks</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Result 2: Near Expiry --}}
        <div class="bg-white border border-l-4 border-l-red-500 border-y border-r border-gray-200 rounded-lg shadow-sm hover:shadow-md transition p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-700 text-xl">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Fresh Milk</h3>
                        <p class="text-sm text-gray-500">SKU: D-MLK-001</p>
                    </div>
                </div>
                <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full animate-pulse">Expiring Soon</span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold">Batch Number</p>
                    <p class="font-mono text-gray-800 font-bold mt-1">BM-2023-882</p>
                </div>
                <div class="bg-red-50 p-3 rounded border border-red-100">
                    <p class="text-xs text-red-400 uppercase font-bold">Expiry Date</p>
                    <p class="text-red-800 font-bold mt-1">Oct 24, 2023 (Today)</p>
                </div>
                <div class="bg-blue-50 p-3 rounded border border-blue-100 col-span-2 flex justify-between items-center">
                    <div>
                        <p class="text-xs text-blue-600 uppercase font-bold">Warehouse Location</p>
                        <p class="text-lg text-blue-900 font-bold mt-1"><i class="fas fa-map-marker-alt mr-2"></i> Walk-in Freezer (Dairy Section)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-600 uppercase font-bold">Qty Here</p>
                        <p class="text-lg text-blue-900 font-bold">2.0 L</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection