@extends('Employee.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-24 font-sans text-gray-600">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-display font-bold text-chocolate">My Hub</h1>
            <p class="text-gray-500 mt-1">Welcome back, <span class="font-bold text-caramel">{{ $user->name ?? 'Employee' }}</span>!</p>
        </div>
        
        {{-- Status Badge --}}
        <div class="flex items-center gap-3 bg-white px-5 py-2.5 rounded-xl border border-border-soft shadow-sm self-start sm:self-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Current Status</span>
            <span class="h-4 w-px bg-border-soft"></span>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                </span>
                <span class="text-sm font-bold text-chocolate">On Duty</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN (Main Content) --}}
        <div class="xl:col-span-2 space-y-8">
            
            {{-- 2. CRITICAL WIDGET: INCOMING DELIVERIES --}}
           
            {{-- 3. STATUS WIDGET: ACTIVE REQUESTS --}}
            <div class="bg-white rounded-xl border border-border-soft shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-soft bg-white flex justify-between items-center">
                    <h3 class="font-display text-lg font-bold text-chocolate">Active Requests</h3>
                    <a href="{{ route('employee.requisitions.history') }}" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider transition-colors">
                        View History &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border-soft">
                    @if($activeRequisitions->count() > 0)
                        @foreach($activeRequisitions as $requisition)
                            <div class="p-5 hover:bg-cream-bg transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        {{-- Icon Status --}}
                                        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center border
                                            {{ $requisition->status === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-blue-50 text-blue-600 border-blue-100' }}">
                                            <i class="fas {{ $requisition->status === 'pending' ? 'fa-hourglass-half' : 'fa-clipboard-check' }}"></i>
                                        </div>
                                        
                                        <div>
                                            <h4 class="text-sm font-bold text-chocolate">
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                        {{ $requisition->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $requisition->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-10 text-center">
                            <p class="text-sm text-gray-500 italic">No active requests found.</p>
                            <a href="{{ route('employee.requisitions.create') }}" class="inline-block mt-3 text-xs font-bold text-chocolate hover:text-caramel border-b border-chocolate hover:border-caramel transition-colors pb-0.5">
                                Create New Request
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (Sidebar) --}}
        <div class="space-y-6">
            
            {{-- 4. QUICK ACTIONS --}}
            <div class="grid grid-cols-2 xl:grid-cols-1 gap-4">
                <a href="{{ route('employee.requisitions.create') }}" class="group block p-5 bg-white border border-border-soft rounded-xl shadow-sm hover:shadow-md hover:border-caramel transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-cream-bg rounded-lg flex items-center justify-center text-caramel group-hover:bg-caramel group-hover:text-white transition-colors">
                            <i class="fas fa-plus text-lg"></i>
                        </div>
                        <i class="fas fa-arrow-right text-gray-300 group-hover:text-caramel transition-colors transform group-hover:translate-x-1"></i>
                    </div>
                    <h3 class="font-display font-bold text-chocolate">Request Stock</h3>
                    <p class="text-xs text-gray-500 mt-1">Order ingredients & supplies</p>
                </a>

              
            </div>

            {{-- 5. RECIPE SHORTCUT --}}
            <div class="bg-chocolate rounded-xl shadow-lg p-6 relative overflow-hidden text-white group">
                {{-- Decorative circles --}}
                <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-4 right-4 w-12 h-12 bg-white/5 rounded-full"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-book-open text-caramel"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/70">Featured Recipe</p>
                    </div>
                    
                    <h3 class="font-display text-xl font-bold mb-1 truncate">
                        {{ $recipeOfTheDay->name ?? 'Bakery Classics' }}
                    </h3>
                    
                    <div class="h-px w-full bg-white/20 my-4"></div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-white/80">View standard procedure</span>
                        <a href="{{ $recipeOfTheDay ? route('employee.recipes.index') : '#' }}" class="w-8 h-8 rounded-full bg-white text-chocolate flex items-center justify-center hover:bg-caramel hover:text-white transition-colors shadow-sm">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection