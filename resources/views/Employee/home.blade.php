@extends('Employee.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-24">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-display font-bold text-gray-900">My Hub</h1>
            <p class="text-gray-500 mt-1">Welcome back, <span class="font-bold text-chocolate">{{ $user->name ?? 'Employee' }}</span>!</p>
        </div>
        <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-xl border border-gray-100 self-start sm:self-center">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</span>
            <span class="h-4 w-px bg-gray-300"></span>
            <p class="text-sm font-bold text-green-600 flex items-center">
                <span class="relative flex h-2.5 w-2.5 mr-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                On Duty
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- LEFT COLUMN (Main Content) --}}
        <div class="xl:col-span-2 space-y-6">
            
            {{-- 2. CRITICAL WIDGET: TO RECEIVE (Incoming from Warehouse) --}}
            <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                <div class="absolute top-0 left-0 w-1 h-full bg-amber-400"></div>
                
                <div class="p-5 border-b border-amber-100 bg-amber-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wider flex items-center">
                            <i class="fas fa-truck-loading text-amber-500 mr-2.5 text-lg"></i> Incoming Deliveries
                        </h3>
                        <p class="text-xs text-amber-700/80 mt-1">Items currently in transit from inventory.</p>
                    </div>
                    @if($incomingDeliveries->count() > 0)
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full border border-amber-200 shadow-sm">
                            {{ $incomingDeliveries->count() }} Pending
                        </span>
                    @endif
                </div>
                
                <div class="p-5">
                    @if($incomingDeliveries->count() > 0)
                        @foreach($incomingDeliveries->take(1) as $delivery)
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                <div class="p-4">
                                    <div class="flex justify-between items-start gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                                    REF: #{{ $delivery->requisition_number }}
                                                </span>
                                                <span class="text-[10px] text-gray-400">
                                                    • {{ $delivery->approved_at ? $delivery->approved_at->diffForHumans() : 'Recently' }}
                                                </span>
                                            </div>
                                            <h4 class="text-base font-bold text-gray-900 leading-tight">
                                                {{ $delivery->requisitionItems->first()?->item?->name ?? 'Assorted Items' }}
                                            </h4>
                                            @if($delivery->requisitionItems->count() > 1)
                                                <p class="text-xs text-gray-500 mt-1 font-medium">
                                                    + {{ $delivery->requisitionItems->count() - 1 }} other items
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 border border-green-100">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-medium text-green-700 flex items-center">
                                        <i class="fas fa-check-circle mr-1.5"></i> Ready to Receive
                                    </span>
                                    <form action="{{ route('employee.requisitions.confirm-receipt', $delivery) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors shadow-sm hover:shadow flex items-center">
                                            Confirm Receipt <i class="fas fa-arrow-right ml-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-6">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-300">
                                <i class="fas fa-check text-xl"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No pending deliveries</p>
                            <p class="text-xs text-gray-400 mt-1">You are all caught up!</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. STATUS WIDGET: ACTIVE REQUESTS --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Active Requests</h3>
                    <a href="{{ route('employee.requisitions.history') }}" class="text-xs font-bold text-chocolate hover:text-chocolate-dark hover:underline">
                        History <i class="fas fa-chevron-right ml-1 text-[10px]"></i>
                    </a>
                </div>

                <div class="divide-y divide-gray-50">
                    @if($activeRequisitions->count() > 0)
                        @foreach($activeRequisitions as $requisition)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{-- Icon based on status --}}
                                        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center {{ $requisition->status === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-blue-50 text-blue-600' }}">
                                            <i class="fas {{ $requisition->status === 'pending' ? 'fa-hourglass-half' : 'fa-clipboard-check' }}"></i>
                                        </div>
                                        
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">
                                                {{ $requisition->requisitionItems->first()?->item?->name ?? 'Unknown Item' }}
                                                @if($requisition->requisitionItems->count() > 1)
                                                    <span class="text-gray-400 font-normal text-xs ml-1">+{{ $requisition->requisitionItems->count() - 1 }} more</span>
                                                @endif
                                            </h4>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $requisition->status === 'pending' ? 'Submitted ' . $requisition->created_at->diffForHumans() : 'Approved, preparing items...' }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Status Badge --}}
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                            {{ $requisition->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $requisition->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-8 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-300">
                                <i class="fas fa-inbox text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-500">No active requests</p>
                            <button onclick="window.location.href='{{ route('employee.requisitions.create') }}'" class="mt-2 text-xs font-bold text-chocolate hover:underline">
                                Create New Request
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (Sidebar) --}}
        <div class="space-y-6">
            {{-- 4. QUICK ACTIONS --}}
            <div class="grid grid-cols-2 xl:grid-cols-1 gap-4">
                <a href="{{ route('employee.requisitions.create') }}" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md hover:border-chocolate/30 transition-all cursor-pointer relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-chocolate mb-3 group-hover:scale-110 group-hover:rotate-3 transition-transform relative z-10">
                        <i class="fas fa-plus text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 relative z-10">Request Stock</h3>
                    <p class="text-xs text-gray-500 mt-1 relative z-10 text-center">Ingredients & Supplies</p>
                </a>

                <a href="{{ route('employee.production.log') }}" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md hover:border-green-500/30 transition-all cursor-pointer relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 mb-3 group-hover:scale-110 group-hover:-rotate-3 transition-transform relative z-10">
                        <i class="fas fa-clipboard-check text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 relative z-10">Log Production</h3>
                    <p class="text-xs text-gray-500 mt-1 relative z-10 text-center">Record daily output</p>
                </a>
            </div>

            {{-- 5. RECIPE SHORTCUT --}}
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl shadow-md p-1 overflow-hidden text-white">
                <div class="flex items-center justify-between p-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-book-open text-xl text-white/90"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">Recipe of the Day</p>
                            <h3 class="text-lg font-bold text-white truncate max-w-[150px] sm:max-w-xs xl:max-w-[150px]">
                                {{ $recipeOfTheDay->name ?? 'No recipe featured' }}
                            </h3>
                        </div>
                    </div>
                    <div>
                        @if($recipeOfTheDay)
                            <a href="{{ route('employee.recipes.index') }}" class="flex items-center justify-center w-10 h-10 bg-white text-gray-900 rounded-full hover:bg-gray-200 transition-colors shadow-sm">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @else
                            <a href="{{ route('employee.recipes.index') }}" class="text-xs font-bold text-gray-400 hover:text-white underline decoration-gray-500/50">View All</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection