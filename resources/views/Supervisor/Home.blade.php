@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Manager Home</h1>
            <p class="text-sm text-gray-500">Operational overview for <span class="font-bold text-caramel">{{ date('F d, Y') }}</span></p>
        </div>
        <div class="flex space-x-3">
            <button class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 group">
                <i class="fas fa-clipboard-check mr-2 group-hover:scale-110 transition-transform"></i> Review Approvals
            </button>
        </div>
    </div>

    {{-- 2. TOP WIDGETS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- WIDGET 1: CRITICAL STOCK (Red List) --}}
        <div class="bg-white rounded-xl shadow-sm border border-border-soft flex flex-col h-full overflow-hidden">
            <div class="px-5 py-4 border-b border-border-soft bg-red-50 flex justify-between items-center">
                <div>
                    <h3 class="font-display text-sm font-bold text-red-800 uppercase tracking-wider">Critical Stock</h3>
                    <p class="text-[10px] text-red-600 font-bold flex items-center mt-0.5">
                        <i class="fas fa-arrow-down mr-1"></i> Below reorder point
                    </p>
                </div>
                <span class="bg-white border border-red-200 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {{ $criticalStockItems->count() }} Items
                </span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-0 custom-scrollbar max-h-[250px]">
                @if(isset($criticalStockItems) && $criticalStockItems->count() > 0)
                    <div class="divide-y divide-red-50">
                        @foreach($criticalStockItems as $item)
                            <div class="flex justify-between items-center p-4 hover:bg-red-50/30 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-red-500 ring-2 ring-red-100"></div>
                                    <span class="text-xs font-bold text-gray-700 group-hover:text-red-700 transition-colors">{{ $item['name'] }}</span>
                                </div>
                                <span class="text-xs font-mono font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100">{{ $item['quantity'] }} {{ $item['unit'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col justify-center items-center h-full py-12 text-center">
                        <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mb-3 text-green-600 border border-green-100">
                            <i class="fas fa-check text-xl"></i>
                        </div>
                        <p class="text-sm font-bold text-chocolate">Stock levels healthy</p>
                        <p class="text-xs text-gray-400 mt-1">No critical items reported.</p>
                    </div>
                @endif
            </div>
            <div class="p-3 border-t border-border-soft bg-gray-50 text-center">
                <a href="#" class="text-xs font-bold text-chocolate hover:text-caramel uppercase tracking-widest flex items-center justify-center transition-colors group">
                    <i class="fas fa-plus-circle mr-2 group-hover:scale-110 transition-transform"></i> Create Rush Requisition
                </a>
            </div>
        </div>

        {{-- WIDGET 2: PENDING APPROVALS --}}
        <div class="bg-white rounded-xl shadow-sm border border-border-soft overflow-hidden flex flex-col h-full relative group">
            {{-- Decorative Background --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-chocolate/5 rounded-bl-full -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>

            <div class="px-5 py-4 border-b border-border-soft bg-cream-bg relative z-10">
                <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-wider">Pending Approvals</h3>
                <p class="text-xs text-gray-500 mt-1">Items requiring immediate attention.</p>
            </div>
            
            <div class="flex-1 flex flex-col items-center justify-center py-10 relative z-10">
                <div class="text-center">
                    <span class="font-display text-7xl font-bold text-chocolate drop-shadow-sm leading-none block">{{ $pendingApprovals['total'] }}</span>
                    <span class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] border-t border-caramel/20 pt-2 mt-2 inline-block">Action Items</span>
                </div>
            </div>
            
            <div class="p-4 bg-gray-50 border-t border-border-soft grid grid-cols-2 gap-4 relative z-10">
                <button class="flex flex-col items-center justify-center p-3 bg-white border border-border-soft rounded-xl hover:border-amber-300 hover:shadow-md transition-all group/btn">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 group-hover/btn:text-amber-600">Requisitions</span>
                    <span class="bg-amber-50 text-amber-700 font-bold text-sm px-3 py-0.5 rounded-full border border-amber-100">{{ $pendingApprovals['requisitions'] }}</span>
                </button>
                <button class="flex flex-col items-center justify-center p-3 bg-white border border-border-soft rounded-xl hover:border-blue-300 hover:shadow-md transition-all group/btn">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 group-hover/btn:text-blue-600">Purch. Requests</span>
                    <span class="bg-blue-50 text-blue-700 font-bold text-sm px-3 py-0.5 rounded-full border border-blue-100">{{ $pendingApprovals['purchase_requests'] }}</span>
                </button>
            </div>
        </div>

    </div>

    {{-- 3. ACTION SECTION --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Recent Requisitions List --}}
        <div class="lg:col-span-2 bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-wide flex items-center">
                    <i class="fas fa-inbox mr-2.5 text-caramel"></i> Inbox: Requisitions
                </h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-chocolate hover:underline transition-colors decoration-caramel/30 underline-offset-2">View All Requests</a>
            </div>
            <div class="divide-y divide-border-soft">
                
                @forelse($recentRequisitions as $requisition)
                    <div class="p-5 hover:bg-cream-bg/30 transition-colors group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-white border border-border-soft flex items-center justify-center text-chocolate font-bold text-[10px] shadow-sm flex-shrink-0 group-hover:border-caramel transition-colors">
                                    REQ
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <p class="text-sm font-bold text-gray-900">{{ $requisition['requester_name'] }}</p>
                                        <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded border border-gray-200 font-medium">{{ $requisition['time_ago'] }}</span>
                                    </div>
                                    @if($requisition['main_item'])
                                        <p class="text-sm text-gray-600">Requesting: <span class="font-bold text-chocolate">{{ $requisition['main_item']['name'] }}</span></p>
                                    @endif
                                    @if($requisition['purpose'])
                                        <p class="text-xs text-gray-400 italic mt-1">"{{ $requisition['purpose'] }}"</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 self-end sm:self-center opacity-80 group-hover:opacity-100 transition-opacity">
                                <form method="POST" action="{{ route('supervisor.requisitions.approve', $requisition['id']) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-border-soft text-green-600 text-xs font-bold rounded-lg hover:bg-green-50 hover:border-green-200 hover:text-green-700 transition-all shadow-sm">
                                        <i class="fas fa-check mr-1.5"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('supervisor.requisitions.reject', $requisition['id']) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-white border border-border-soft text-gray-500 text-xs font-bold rounded-lg hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-all shadow-sm">
                                        Reject
                                    </button>
                                </form>
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:text-chocolate hover:bg-cream-bg transition-all ml-1">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center flex flex-col items-center">
                        <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                            <i class="fas fa-inbox text-3xl text-chocolate/30"></i>
                        </div>
                        <h3 class="font-display text-lg font-bold text-chocolate">No Pending Requisitions</h3>
                        <p class="text-xs text-gray-400 mt-1">You are all caught up!</p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- Inventory Actions --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-border-soft rounded-xl shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-cream-bg rounded-bl-full -mr-8 -mt-8 z-0"></div>
                
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center relative z-10">
                    <i class="fas fa-boxes mr-2 text-caramel"></i> Inventory Control
                </h3>
                
                <div class="space-y-4 relative z-10">
                    <a href="#" class="flex items-center p-4 rounded-xl border border-border-soft hover:border-red-200 hover:bg-red-50/40 transition-all group shadow-sm bg-white">
                        <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center text-red-500 mr-4 group-hover:scale-110 transition-transform shadow-sm border border-red-100">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 group-hover:text-red-700 transition-colors">Report Spoilage</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 font-medium">Create write-off ticket</p>
                        </div>
                    </a>

                    <a href="#" class="flex items-center p-4 rounded-xl border border-border-soft hover:border-caramel/50 hover:bg-cream-bg/40 transition-all group shadow-sm bg-white">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 mr-4 group-hover:scale-110 transition-transform shadow-sm border border-blue-100">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 group-hover:text-chocolate transition-colors">Stock Count</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 font-medium">Start daily inventory check</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Custom Scrollbar Style --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.02);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e8dfd4;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #c48d3f;
    }
</style>
@endsection