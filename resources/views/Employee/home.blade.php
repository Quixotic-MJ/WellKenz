@extends('Employee.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 pb-24 font-sans text-gray-600">
    
    {{-- 1. WELCOME BANNER --}}
    <div class="bg-gradient-to-r from-chocolate to-caramel rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-display font-bold mb-2">
                    Good {{ date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') }}, {{ $user->name ?? 'Employee' }}!
                </h1>
                <p class="text-white/80 text-lg">
                    {{ now()->format('l, F j, Y') }} • Current Shift
                </p>
            </div>
            <div class="hidden sm:block">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-tie text-3xl text-white/80"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. STATUS CARDS (KPIs) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Pending Requests --}}
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Pending Requests</p>
                    <p class="text-4xl font-bold text-amber-600 mt-2">{{ $my_pending_reqs }}</p>
                </div>
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clock text-2xl text-amber-600"></i>
                </div>
            </div>
        </div>

        {{-- Ready to Pick --}}
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Ready to Pick</p>
                    <p class="text-4xl font-bold text-green-600 mt-2">{{ $my_approved_reqs }}</p>
                </div>
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. QUICK ACTIONS GRID --}}
    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-display font-bold text-chocolate mb-6">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- New Requisition --}}
            <a href="{{ route('employee.requisitions.create') }}" 
               class="group bg-gradient-to-br from-chocolate to-caramel rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-plus text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">New Requisition</h3>
                    <p class="text-white/80 text-sm">Request ingredients & supplies</p>
                </div>
            </a>

            {{-- Recipe Book --}}
            <a href="{{ route('employee.recipes.index') }}" 
               class="group bg-gradient-to-br from-caramel to-amber-500 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-book-open text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Recipe Book</h3>
                    <p class="text-white/80 text-sm">View recipes & procedures</p>
                </div>
            </a>

            {{-- My Profile --}}
            <a href="{{ route('profile.index') }}" 
               class="group bg-gradient-to-br from-amber-500 to-yellow-500 rounded-2xl p-8 text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-user text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">My Profile</h3>
                    <p class="text-white/80 text-sm">Manage your account</p>
                </div>
            </a>
        </div>
    </div>

    {{-- 4. RECENT ACTIVITY SECTION --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100">
            <h2 class="text-2xl font-display font-bold text-chocolate">Recent Activity</h2>
            <p class="text-gray-500 text-sm mt-1">Your latest requisitions</p>
        </div>

        <div class="overflow-x-auto">
            @if($recent_requisitions->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req Number</th>
                            <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-8 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($recent_requisitions as $requisition)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-chocolate/10 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-file-alt text-chocolate"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">REQ-{{ str_pad($requisition->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $requisition->requisitionItems->count() }} item{{ $requisition->requisitionItems->count() != 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $requisition->created_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $requisition->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    @if($requisition->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <i class="fas fa-clock mr-1"></i> Pending
                                        </span>
                                    @elseif($requisition->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Ready to Pick
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($requisition->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <a href="{{ route('employee.requisitions.history') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-chocolate text-sm font-medium rounded-lg text-chocolate bg-white hover:bg-chocolate hover:text-white transition-colors">
                                        <i class="fas fa-eye mr-2"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No recent requisitions</h3>
                    <p class="text-gray-500 mb-4">Start by creating your first requisition</p>
                    <a href="{{ route('employee.requisitions.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-chocolate text-white font-medium rounded-lg hover:bg-caramel transition-colors">
                        <i class="fas fa-plus mr-2"></i> New Requisition
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection