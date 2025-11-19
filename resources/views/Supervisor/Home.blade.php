@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manager Home</h1>
            <p class="text-sm text-gray-500">Operational overview for {{ date('F d, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <button class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-clipboard-check mr-2"></i> Review Approvals
            </button>
        </div>
    </div>

    {{-- 2. TOP WIDGETS (The Core Metrics) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- WIDGET 1: CRITICAL STOCK (Red List - < 24h) --}}
        <div class="bg-white border-t-4 border-red-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Critical Stock</h3>
                    <p class="text-[10px] text-red-500 font-bold flex items-center mt-0.5">
                        <i class="fas fa-hourglass-half mr-1"></i> Less than 24h supply
                    </p>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">4 Items</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                <ul class="space-y-2">
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-xs font-bold text-gray-700">Fresh Milk</span>
                        </div>
                        <span class="text-xs font-bold text-red-600">2 L</span>
                    </li>
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-xs font-bold text-gray-700">Eggs (Large)</span>
                        </div>
                        <span class="text-xs font-bold text-red-600">12 Pcs</span>
                    </li>
                    <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-xs font-bold text-gray-700">Strawberries</span>
                        </div>
                        <span class="text-xs font-bold text-red-600">0.5 kg</span>
                    </li>
                     <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                            <span class="text-xs font-bold text-gray-700">Yeast</span>
                        </div>
                        <span class="text-xs font-bold text-red-600">100 g</span>
                    </li>
                </ul>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-center">
                <a href="#" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wide">
                    <i class="fas fa-plus-circle mr-1"></i> Create Rush Requisition
                </a>
            </div>
        </div>

        {{-- WIDGET 2: USAGE VS SALES (Visual Graph) --}}
        <div class="bg-white border-t-4 border-blue-500 rounded-lg shadow-sm p-5 flex flex-col h-full">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Usage vs. Sales</h3>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">Last 3 Days</span>
            </div>
            <p class="text-[10px] text-gray-500 mb-4">Comparing Inventory Usage (Orange) vs Product Sales (Blue).</p>
            
            <!-- CSS Bar Chart -->
            <div class="flex-1 flex items-end justify-around w-full space-x-2 pb-2 border-b border-gray-200">
                <!-- Day 1 -->
                <div class="flex flex-col items-center space-y-1 w-1/4 group relative">
                    <div class="w-full flex items-end justify-center space-x-1 h-28">
                        <div class="bg-blue-500 w-3 h-[80%] rounded-t shadow-sm transition-all hover:bg-blue-600 relative group-hover:opacity-90" title="Sales: 80%"></div>
                        <div class="bg-orange-400 w-3 h-[75%] rounded-t shadow-sm transition-all hover:bg-orange-500 relative group-hover:opacity-90" title="Usage: 75%"></div>
                    </div>
                    <span class="text-[10px] text-gray-500 font-medium">Mon</span>
                </div>
                <!-- Day 2 -->
                <div class="flex flex-col items-center space-y-1 w-1/4 group">
                    <div class="w-full flex items-end justify-center space-x-1 h-28">
                        <div class="bg-blue-500 w-3 h-[60%] rounded-t shadow-sm transition-all hover:bg-blue-600" title="Sales: 60%"></div>
                        <div class="bg-orange-400 w-3 h-[65%] rounded-t shadow-sm transition-all hover:bg-orange-500" title="Usage: 65%"></div>
                    </div>
                    <span class="text-[10px] text-gray-500 font-medium">Tue</span>
                </div>
                <!-- Day 3 -->
                <div class="flex flex-col items-center space-y-1 w-1/4 group">
                    <div class="w-full flex items-end justify-center space-x-1 h-28">
                        <div class="bg-blue-500 w-3 h-[95%] rounded-t shadow-sm transition-all hover:bg-blue-600" title="Sales: 95%"></div>
                        <div class="bg-orange-400 w-3 h-[92%] rounded-t shadow-sm transition-all hover:bg-orange-500" title="Usage: 92%"></div>
                    </div>
                    <span class="text-[10px] text-gray-800 font-bold">Today</span>
                </div>
            </div>

            <div class="mt-3 flex justify-center gap-4 text-[10px] uppercase tracking-wide font-semibold">
                <div class="flex items-center"><div class="w-2 h-2 bg-blue-500 rounded mr-1.5"></div> Sales</div>
                <div class="flex items-center"><div class="w-2 h-2 bg-orange-400 rounded mr-1.5"></div> Usage</div>
            </div>
        </div>

        {{-- WIDGET 3: PENDING APPROVALS (Big Number) --}}
        <div class="bg-white border-t-4 border-amber-500 rounded-lg shadow-sm p-5 flex flex-col justify-between h-full">
            <div>
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Pending Approvals</h3>
                <p class="text-xs text-gray-500 mt-1">Items requiring your immediate attention.</p>
            </div>
            <div class="text-center py-6">
                <span class="text-6xl font-black text-gray-800 tracking-tight">5</span>
                <p class="text-sm text-amber-600 font-bold mt-2 uppercase tracking-wide">Action Items</p>
            </div>
            <div class="space-y-2">
                <button class="w-full py-2 bg-amber-50 text-amber-700 text-xs font-bold rounded hover:bg-amber-100 transition flex items-center justify-center border border-amber-100">
                    Requisitions (5)
                </button>
                <button class="w-full py-2 bg-white text-gray-600 text-xs font-bold rounded hover:bg-gray-50 transition flex items-center justify-center border border-gray-200">
                    Purchase Requests (0)
                </button>
            </div>
        </div>

    </div>

    {{-- 3. ACTION SECTION (Context for Supervisor) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Recent Requisitions List --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-800 uppercase">Inbox: Requisitions</h3>
                <a href="#" class="text-xs text-blue-600 hover:underline font-medium">View All Requests</a>
            </div>
            <div class="divide-y divide-gray-100">
                
                {{-- Req 1 --}}
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs group-hover:bg-amber-200 transition">
                            REQ
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-gray-900">Baker John Doe</p>
                                <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 rounded">2 mins ago</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">Requesting: <span class="font-bold text-chocolate">50kg White Sugar</span></p>
                            <p class="text-[10px] text-gray-400 italic mt-0.5">"For Wedding Cake Order #882"</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded transition font-bold border border-green-200">
                            Approve
                        </button>
                        <button class="text-xs bg-white text-gray-600 hover:bg-red-50 hover:text-red-600 px-3 py-1.5 rounded transition font-medium border border-gray-200 hover:border-red-200">
                            Reject
                        </button>
                        <button class="text-gray-400 hover:text-chocolate px-2">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                {{-- Req 2 --}}
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs group-hover:bg-amber-200 transition">
                            REQ
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-gray-900">Maria (Pastry)</p>
                                <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 rounded">1 hr ago</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">Requesting: <span class="font-bold text-chocolate">10L Heavy Cream</span></p>
                            <p class="text-[10px] text-gray-400 italic mt-0.5">"Stock Replenishment"</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded transition font-bold border border-green-200">
                            Approve
                        </button>
                        <button class="text-xs bg-white text-gray-600 hover:bg-red-50 hover:text-red-600 px-3 py-1.5 rounded transition font-medium border border-gray-200 hover:border-red-200">
                            Reject
                        </button>
                        <button class="text-gray-400 hover:text-chocolate px-2">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- Inventory Actions --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Inventory Control</h3>
                
                <a href="#" class="flex items-center p-3 mb-3 rounded-lg border border-gray-200 hover:bg-red-50 hover:border-red-200 transition group">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600 mr-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700 group-hover:text-red-700">Report Spoilage</p>
                        <p class="text-[10px] text-gray-500">Create write-off ticket</p>
                    </div>
                </a>

                <a href="#" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 transition group">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mr-3 group-hover:scale-110 transition-transform">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-700 group-hover:text-blue-700">Stock Count</p>
                        <p class="text-[10px] text-gray-500">Start daily inventory check</p>
                    </div>
                </a>
            </div>

            <div class="bg-gradient-to-br from-chocolate to-chocolate-dark rounded-lg shadow-sm p-5 text-white">
                 <h3 class="text-xs font-bold text-white/80 uppercase tracking-wider mb-2">Quick Report</h3>
                 <p class="text-sm font-medium mb-3">Download today's production yield report.</p>
                 <button class="w-full py-2 bg-white/20 hover:bg-white/30 rounded text-xs font-bold border border-white/30 transition">
                    <i class="fas fa-download mr-1"></i> Download PDF
                 </button>
            </div>
        </div>

    </div>

</div>
@endsection