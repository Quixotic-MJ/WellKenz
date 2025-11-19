@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Procurement Home</h1>
            <p class="text-sm text-gray-500">Purchasing overview for {{ date('F d, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('purchasing.po.create') }}" class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus-circle mr-2"></i> Create Purchase Order
            </a>
        </div>
    </div>

    {{-- 2. TOP WIDGETS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- WIDGET 1: LOW STOCK ALERTS (Triggers Buying) --}}
        <div class="bg-white border-t-4 border-red-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Low Stock Alerts</h3>
                    <p class="text-[10px] text-red-500 font-bold mt-0.5">
                        <i class="fas fa-arrow-down mr-1"></i> Below Reorder Level
                    </p>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">3 Items</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                <ul class="space-y-2">
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div>
                            <span class="text-xs font-bold text-gray-700 block">White Sugar (50kg)</span>
                            <span class="text-[10px] text-gray-500">Stock: <span class="font-bold text-red-600">5</span> / Min: 15</span>
                        </div>
                        <button class="text-xs bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white px-2 py-1 rounded transition">
                            Order
                        </button>
                    </li>
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div>
                            <span class="text-xs font-bold text-gray-700 block">Vanilla Extract</span>
                            <span class="text-[10px] text-gray-500">Stock: <span class="font-bold text-red-600">1</span> / Min: 5</span>
                        </div>
                        <button class="text-xs bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white px-2 py-1 rounded transition">
                            Order
                        </button>
                    </li>
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div>
                            <span class="text-xs font-bold text-gray-700 block">Cake Boxes (10x10)</span>
                            <span class="text-[10px] text-gray-500">Stock: <span class="font-bold text-red-600">40</span> / Min: 100</span>
                        </div>
                        <button class="text-xs bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white px-2 py-1 rounded transition">
                            Order
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- WIDGET 2: OPEN PO VALUE (Financial Commitment) --}}
        <div class="bg-white border-t-4 border-blue-500 rounded-lg shadow-sm p-5 flex flex-col justify-between h-full">
            <div>
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Open PO Value</h3>
                <p class="text-xs text-gray-500 mt-1">Total outgoing cash commitment</p>
            </div>
            <div class="text-center py-4">
                <span class="text-4xl font-black text-gray-800 tracking-tight">₱ 185,420.00</span>
                <div class="flex justify-center items-center gap-2 mt-2">
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">8 Active POs</span>
                </div>
            </div>
            <div class="mt-2 text-center">
                <p class="text-[10px] text-gray-400">Based on Approved & Sent orders awaiting delivery.</p>
            </div>
        </div>

        {{-- WIDGET 3: OVERDUE DELIVERIES (The Shame List) --}}
        <div class="bg-white border-t-4 border-amber-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Overdue Deliveries</h3>
                    <p class="text-[10px] text-amber-600 font-bold mt-0.5">
                        <i class="fas fa-clock mr-1"></i> Late Suppliers
                    </p>
                </div>
                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full">2 Late</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                <ul class="space-y-2">
                    
                    <li class="p-3 bg-amber-50 rounded border-l-4 border-amber-400 relative group">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-800 block">Golden Grain Supplies</span>
                                <span class="text-[10px] text-gray-500 font-mono">PO #2023-088</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-white px-1.5 rounded border border-red-100">
                                +2 Days
                            </span>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <a href="#" class="text-[10px] font-bold text-blue-600 hover:underline flex items-center">
                                <i class="fas fa-phone-alt mr-1"></i> Call Vendor
                            </a>
                        </div>
                    </li>

                    <li class="p-3 bg-amber-50 rounded border-l-4 border-amber-400 relative group">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-800 block">Cebu Dairy Corp.</span>
                                <span class="text-[10px] text-gray-500 font-mono">PO #2023-091</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-white px-1.5 rounded border border-red-100">
                                +1 Day
                            </span>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <a href="#" class="text-[10px] font-bold text-blue-600 hover:underline flex items-center">
                                <i class="fas fa-envelope mr-1"></i> Email Follow-up
                            </a>
                        </div>
                    </li>

                </ul>
            </div>
        </div>

    </div>

    {{-- 3. RECENT ACTIVITY / QUICK ACTIONS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Recent POs List --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-800 uppercase">Recent Purchase Orders</h3>
                <a href="{{ route('purchasing.po.history') }}" class="text-xs text-blue-600 hover:underline font-medium">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                            PO
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">#PR-2023-102 <span class="text-gray-400 font-normal mx-1">•</span> Prime Packaging</p>
                            <p class="text-xs text-gray-500">Items: 500 Cake Boxes • Total: ₱ 7,500.00</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Awaiting Delivery
                    </span>
                </div>

                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-xs">
                            DFT
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">#Draft-005 <span class="text-gray-400 font-normal mx-1">•</span> Local Farms Inc.</p>
                            <p class="text-xs text-gray-500">Items: Fresh Eggs, Milk • Total: ₱ 3,200.00</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        Draft
                    </span>
                </div>

            </div>
        </div>

        {{-- Quick Vendor Search --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Vendor Lookup</h3>
                <div class="relative">
                    <input type="text" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" placeholder="Find supplier...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <p class="text-[10px] text-gray-400 uppercase font-bold">Frequent Contacts</p>
                    <a href="#" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded transition group">
                        <span class="text-sm text-gray-700 group-hover:text-chocolate">Golden Grain Supplies</span>
                        <i class="fas fa-phone text-xs text-gray-300 group-hover:text-chocolate"></i>
                    </a>
                    <a href="#" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded transition group">
                        <span class="text-sm text-gray-700 group-hover:text-chocolate">Prime Packaging</span>
                        <i class="fas fa-phone text-xs text-gray-300 group-hover:text-chocolate"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection