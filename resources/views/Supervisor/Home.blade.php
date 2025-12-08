@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Supervisor Dashboard</h1>
            <p class="text-sm text-gray-500">Management overview for {{ date('F d, Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('supervisor.approvals.purchase-requests') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-shopping-cart mr-2"></i> Purchase Request Approvals
            </a>
            <a href="{{ route('supervisor.requisitions.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
                <i class="fas fa-clipboard-check mr-2"></i> View All Approvals
            </a>
        </div>
    </div>

    {{-- TOP ROW: 3 KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Pending Requisitions Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Requisitions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending_requisitions'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-amber-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs text-gray-500">Items requiring approval</p>
            </div>
        </div>

        {{-- Pending Purchase Requests Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Purchase Requests</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending_purchase_requests'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs text-gray-500">Purchase requests pending</p>
            </div>
        </div>

        {{-- Critical Stock Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Critical Stock</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['critical_stock'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs text-gray-500">Items below reorder point</p>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- MAIN SECTION: Pending Requisitions Table (2/3 width) --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Requisitions</h3>
                <p class="text-sm text-gray-500">Items requiring supervisor approval</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentRequisitions as $requisition)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $requisition['requisition_number'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $requisition['requester_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $requisition['department'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $requisition['request_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <form method="POST" action="{{ route('supervisor.requisitions.approve', $requisition['id']) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                                <i class="fas fa-check mr-1"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('supervisor.requisitions.reject', $requisition['id']) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                                <i class="fas fa-times mr-1"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">No Pending Requisitions</h3>
                                        <p class="text-sm text-gray-500">All requisitions are up to date!</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SIDEBAR: Pending Purchase Requests (1/3 width) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Purchase Requests</h3>
                <p class="text-sm text-gray-500">Urgent requests first</p>
            </div>
            
            <div class="p-6">
                @forelse($recentPurchaseRequests as $request)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900">{{ $request->pr_number }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($request->priority === 'urgent') bg-red-100 text-red-800
                                        @elseif($request->priority === 'high') bg-orange-100 text-orange-800
                                        @elseif($request->priority === 'normal') bg-gray-100 text-gray-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $request->requestedBy->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $request->department }}</p>
                                <p class="text-sm font-semibold text-gray-900 mt-2">
                                    @if($request->total_estimated_cost)
                                        ₱{{ number_format($request->total_estimated_cost, 2) }}
                                    @else
                                        ₱0.00
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr class="my-4 border-gray-200">
                    @endif
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-circle text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">No Pending Requests</p>
                        <p class="text-xs text-gray-500 mt-1">All purchase requests are processed</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection