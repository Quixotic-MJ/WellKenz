@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Warehouse Home</h1>
            <p class="text-sm text-gray-500">Overview for {{ date('F d, Y') }} • <span class="text-green-600 font-medium">Shift A (Morning)</span></p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.inbound.receive') }}" class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-truck-loading mr-2"></i> Receive Delivery
            </a>
        </div>
    </div>

    {{-- 2. OPERATIONAL WIDGETS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- WIDGET 1: INCOMING DELIVERIES (The Schedule) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Incoming Deliveries</h3>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">3 Expected</span>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="space-y-3">
                    <!-- Truck 1 -->
                    <div class="flex items-start p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                        <div class="flex-shrink-0 mr-3 text-center">
                            <span class="text-xs font-bold text-blue-800 block">10:00</span>
                            <span class="text-[10px] text-blue-600 uppercase">AM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-gray-900">Golden Grain Supplies</h4>
                            <p class="text-xs text-gray-500">PO #2023-102 • 25 Sacks Flour</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-[10px] bg-white border border-blue-200 text-blue-600 px-2 py-1 rounded">Arriving</span>
                        </div>
                    </div>

                    <!-- Truck 2 -->
                    <div class="flex items-start p-3 bg-gray-50 border-l-4 border-gray-300 rounded opacity-75">
                        <div class="flex-shrink-0 mr-3 text-center">
                            <span class="text-xs font-bold text-gray-600 block">01:30</span>
                            <span class="text-[10px] text-gray-500 uppercase">PM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-gray-900">Prime Packaging</h4>
                            <p class="text-xs text-gray-500">PO #2023-105 • 500 Boxes</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-[10px] bg-white border border-gray-200 text-gray-500 px-2 py-1 rounded">Scheduled</span>
                        </div>
                    </div>

                     <!-- Truck 3 -->
                     <div class="flex items-start p-3 bg-gray-50 border-l-4 border-gray-300 rounded opacity-75">
                        <div class="flex-shrink-0 mr-3 text-center">
                            <span class="text-xs font-bold text-gray-600 block">03:00</span>
                            <span class="text-[10px] text-gray-500 uppercase">PM</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-gray-900">Cebu Dairy Corp.</h4>
                            <p class="text-xs text-gray-500">PO #2023-108 • Milk & Cream</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-[10px] bg-white border border-gray-200 text-gray-500 px-2 py-1 rounded">Scheduled</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="text-xs font-bold text-blue-600 hover:underline">View Full Schedule</a>
            </div>
        </div>

        {{-- WIDGET 2: EXPIRING SOON (Push Out / FEFO) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full border-t-4 border-t-red-500">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Expiring Soon</h3>
                    <p class="text-[10px] text-red-500 font-bold mt-0.5">Priority: FEFO (First Expired, First Out)</p>
                </div>
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto pr-1">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-100">
                        <tr class="group">
                            <td class="py-2">
                                <p class="text-xs font-bold text-gray-800">Fresh Milk</p>
                                <p class="text-[10px] text-gray-500">Batch #BM-882</p>
                            </td>
                            <td class="py-2 text-right">
                                <span class="text-xs font-bold text-red-600 block">Today</span>
                                <span class="text-[10px] text-gray-400">2.0 L Left</span>
                            </td>
                            <td class="py-2 text-right pl-2">
                                <button class="text-chocolate hover:text-chocolate-dark text-xs underline">Pick</button>
                            </td>
                        </tr>
                        <tr class="group">
                            <td class="py-2">
                                <p class="text-xs font-bold text-gray-800">Heavy Cream</p>
                                <p class="text-[10px] text-gray-500">Batch #CRM-101</p>
                            </td>
                            <td class="py-2 text-right">
                                <span class="text-xs font-bold text-amber-600 block">3 Days</span>
                                <span class="text-[10px] text-gray-400">5.0 L Left</span>
                            </td>
                            <td class="py-2 text-right pl-2">
                                <button class="text-chocolate hover:text-chocolate-dark text-xs underline">Pick</button>
                            </td>
                        </tr>
                         <tr class="group">
                            <td class="py-2">
                                <p class="text-xs font-bold text-gray-800">Cream Cheese</p>
                                <p class="text-[10px] text-gray-500">Batch #CHZ-220</p>
                            </td>
                            <td class="py-2 text-right">
                                <span class="text-xs font-bold text-amber-600 block">5 Days</span>
                                <span class="text-[10px] text-gray-400">2.5 kg Left</span>
                            </td>
                            <td class="py-2 text-right pl-2">
                                <button class="text-chocolate hover:text-chocolate-dark text-xs underline">Pick</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- WIDGET 3: PENDING REQUISITIONS (Pick List) --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full border-t-4 border-t-amber-500">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Pending Requisitions</h3>
                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full">3 To Pack</span>
            </div>
            
            <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                
                <!-- Req 1 -->
                <div class="p-3 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition cursor-pointer group">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs font-bold text-gray-900">#REQ-1024</span>
                        <span class="text-[10px] text-gray-400"><i class="far fa-clock"></i> 2h ago</span>
                    </div>
                    <p class="text-xs text-gray-600">To: <span class="font-medium">Main Kitchen (Baker John)</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">1 Item (50kg Sugar)</span>
                        <button class="text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition">Start Picking</button>
                    </div>
                </div>

                <!-- Req 2 -->
                <div class="p-3 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition cursor-pointer group">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs font-bold text-gray-900">#REQ-1025</span>
                        <span class="text-[10px] text-gray-400"><i class="far fa-clock"></i> 30m ago</span>
                    </div>
                    <p class="text-xs text-gray-600">To: <span class="font-medium">Pastry Section (Maria)</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">2 Items</span>
                        <button class="text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition">Start Picking</button>
                    </div>
                </div>

                 <!-- Req 3 -->
                <div class="p-3 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition cursor-pointer group">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-xs font-bold text-gray-900">#REQ-1026</span>
                        <span class="text-[10px] text-gray-400"><i class="far fa-clock"></i> 10m ago</span>
                    </div>
                    <p class="text-xs text-gray-600">To: <span class="font-medium">Bread Section (Rico)</span></p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">1 Item</span>
                        <button class="text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition">Start Picking</button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- 3. QUICK STATS ROW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-chocolate text-white p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs opacity-75 uppercase">Inventory Value</p>
                <p class="text-lg font-bold">₱ 1.2M</p>
            </div>
            <i class="fas fa-coins text-2xl opacity-20"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Space Utilized</p>
                <p class="text-lg font-bold text-gray-800">85%</p>
            </div>
            <i class="fas fa-warehouse text-gray-300 text-2xl"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Pending Put-away</p>
                <p class="text-lg font-bold text-gray-800">0 Items</p>
            </div>
            <i class="fas fa-box-open text-gray-300 text-2xl"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Staff on Duty</p>
                <p class="text-lg font-bold text-gray-800">3</p>
            </div>
            <i class="fas fa-users text-gray-300 text-2xl"></i>
        </div>
    </div>

</div>
@endsection