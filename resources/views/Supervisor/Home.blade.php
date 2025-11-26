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
                        <i class="fas fa-hourglass-half mr-1"></i> Less than reorder point
                    </p>
                </div>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">{{ $criticalStockItems->count() }} Items</span>
            </div>
            <div class="flex-1 overflow-y-auto pr-1">
                @if(isset($criticalStockItems) && $criticalStockItems->count() > 0)
                    <ul class="space-y-2">
                        @foreach($criticalStockItems as $item)
                            <li class="flex justify-between items-center p-2 bg-red-50 rounded border border-red-100">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                                    <span class="text-xs font-bold text-gray-700">{{ $item['name'] }}</span>
                                </div>
                                <span class="text-xs font-bold text-red-600">{{ $item['quantity'] }} {{ $item['unit'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="flex justify-center items-center h-full">
                        <div class="text-center">
                            <div class="text-green-500 mb-2">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-700">No critical stock items</p>
                            <p class="text-xs text-green-600 font-medium">Stock levels are healthy!</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-center">
                <a href="#" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wide">
                    <i class="fas fa-plus-circle mr-1"></i> Create Rush Requisition
                </a>
            </div>
        </div>



        {{-- WIDGET 3: PENDING APPROVALS (Big Number) --}}
        <div class="bg-white border-t-4 border-amber-500 rounded-lg shadow-sm p-5 flex flex-col justify-between h-full">
            <div>
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Pending Approvals</h3>
                <p class="text-xs text-gray-500 mt-1">Items requiring your immediate attention.</p>
            </div>
            <div class="text-center py-6">
                <span class="text-6xl font-black text-gray-800 tracking-tight">{{ $pendingApprovals['total'] }}</span>
                <p class="text-sm text-amber-600 font-bold mt-2 uppercase tracking-wide">Action Items</p>
            </div>
            <div class="space-y-2">
                <button class="w-full py-2 bg-amber-50 text-amber-700 text-xs font-bold rounded hover:bg-amber-100 transition flex items-center justify-center border border-amber-100">
                    Requisitions ({{ $pendingApprovals['requisitions'] }})
                </button>
                <button class="w-full py-2 bg-white text-gray-600 text-xs font-bold rounded hover:bg-gray-50 transition flex items-center justify-center border border-gray-200">
                    Purchase Requests ({{ $pendingApprovals['purchase_requests'] }})
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
                
                @forelse($recentRequisitions as $requisition)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-xs group-hover:bg-amber-200 transition">
                                REQ
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-gray-900">{{ $requisition['requester_name'] }}</p>
                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 rounded">{{ $requisition['time_ago'] }}</span>
                                </div>
                                @if($requisition['main_item'])
                                    <p class="text-xs text-gray-600 mt-0.5">Requesting: <span class="font-bold text-chocolate">{{ $requisition['main_item']['name'] }}</span></p>
                                @endif
                                @if($requisition['purpose'])
                                    <p class="text-[10px] text-gray-400 italic mt-0.5">"{{ $requisition['purpose'] }}"</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('supervisor.requisitions.approve', $requisition['id']) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded transition font-bold border border-green-200">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('supervisor.requisitions.reject', $requisition['id']) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs bg-white text-gray-600 hover:bg-red-50 hover:text-red-600 px-3 py-1.5 rounded transition font-medium border border-gray-200 hover:border-red-200">
                                    Reject
                                </button>
                            </form>
                            <button class="text-gray-400 hover:text-chocolate px-2">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="text-gray-400 mb-2">
                            <i class="fas fa-check-circle text-4xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 font-medium">No pending requisitions</p>
                        <p class="text-xs text-gray-500 mt-1">All requisitions are up to date!</p>
                    </div>
                @endforelse

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