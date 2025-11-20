@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Hub</h1>
            <p class="text-sm text-gray-500">Welcome back, <span class="font-bold text-chocolate">Baker John</span>!</p>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold text-gray-400 uppercase">Shift Status</span>
            <p class="text-sm font-bold text-green-600 flex items-center justify-end">
                <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span> On Duty
            </p>
        </div>
    </div>

    {{-- 2. CRITICAL WIDGET: TO RECEIVE (Incoming from Warehouse) --}}
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg shadow-sm p-5 relative overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wider flex items-center">
                    <i class="fas fa-truck-loading mr-2"></i> Incoming Deliveries
                </h3>
                <p class="text-xs text-amber-700 mt-1">Items currently being brought by Inventory.</p>
            </div>
            <span class="bg-white text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">
                1 Arriving
            </span>
        </div>
        
        <div class="mt-4 bg-white rounded-lg border border-amber-200 p-3 shadow-sm">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-bold text-gray-900">White Sugar (50kg)</p>
                    <p class="text-xs text-gray-500">Ref: #REQ-1024</p>
                </div>
                <div class="text-right">
                    <span class="block text-xs font-bold text-green-600">Picked & Moving</span>
                    <span class="text-[10px] text-gray-400">Est. 5 mins</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 flex justify-end">
                <button class="text-xs bg-amber-500 text-white px-3 py-1.5 rounded hover:bg-amber-600 transition shadow-sm">
                    Confirm Receipt
                </button>
            </div>
        </div>
    </div>

    {{-- 3. STATUS WIDGET: ACTIVE REQUESTS --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">My Active Requests</h3>
            <a href="{{ route('staff.requisitions.history') }}" class="text-xs text-blue-600 hover:underline">View History</a>
        </div>

        <div class="space-y-3">
            <!-- Request 1: Pending -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Bread Flour (25kg)</p>
                        <p class="text-[10px] text-gray-500">Submitted 2 hrs ago</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-gray-500 bg-white border border-gray-300 px-2 py-1 rounded">
                    Waiting Approval
                </span>
            </div>

            <!-- Request 2: Approved -->
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center text-blue-700">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Heavy Cream (10L)</p>
                        <p class="text-[10px] text-blue-600">Approved by Supervisor</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-blue-700 bg-white border border-blue-200 px-2 py-1 rounded">
                    Queued for Picking
                </span>
            </div>
        </div>
    </div>

    {{-- 4. QUICK ACTIONS GRID (Touch Friendly) --}}
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('staff.requisitions.create') }}" class="flex flex-col items-center justify-center p-6 bg-white border-2 border-dashed border-chocolate rounded-xl hover:bg-orange-50 transition group cursor-pointer">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-chocolate mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-plus text-xl"></i>
            </div>
            <span class="text-sm font-bold text-gray-800">Request Ingredients</span>
            <span class="text-[10px] text-gray-500">Restock your station</span>
        </a>

        <a href="{{ route('staff.production.log') }}" class="flex flex-col items-center justify-center p-6 bg-white border-2 border-dashed border-green-500 rounded-xl hover:bg-green-50 transition group cursor-pointer">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-clipboard-check text-xl"></i>
            </div>
            <span class="text-sm font-bold text-gray-800">Log Output</span>
            <span class="text-[10px] text-gray-500">Record finished goods</span>
        </a>
    </div>

    {{-- 5. RECIPE SHORTCUT --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-book-open text-gray-400 text-lg"></i>
            <div>
                <p class="text-sm font-bold text-gray-900">Recipe of the Day</p>
                <p class="text-xs text-gray-500">Soft Roll Dough (Standard)</p>
            </div>
        </div>
        <button class="text-xs text-chocolate font-bold hover:underline">View Card</button>
    </div>

</div>
@endsection