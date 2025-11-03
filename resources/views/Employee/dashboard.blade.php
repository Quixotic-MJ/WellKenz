@extends('Employee.layout.app')

@section('title', 'Dashboard - WellKenz ERP')

@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft rounded-lg p-8">
        <div class="flex items-center justify-between">
            <div>
                @php
                    $hour = date('H');
                    $greeting = 'Good ';
                    if ($hour < 12) {
                        $greeting .= 'morning';
                    } elseif ($hour < 17) {
                        $greeting .= 'afternoon';
                    } else {
                        $greeting .= 'evening';
                    }
                @endphp
                <h1 class="font-display text-3xl font-bold text-text-dark">{{ $greeting }}, {{ session('emp_name') }}</h1>
                <p class="text-text-muted mt-2">Welcome to your {{ session('role') }} dashboard. Here's your overview for today.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-text-dark font-semibold">{{ date('F j, Y') }}</p>
                <p class="text-xs text-text-muted mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- My Pending Requisitions -->
        <a href="{{ route('Staff_Requisition_Record') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">My Pending Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $myPendingRequisitions ?? '5' }}</p>
            <p class="text-xs text-yellow-600 mt-1">Awaiting approval</p>
        </a>

        <!-- Approved Items -->
        <a href="{{ route('Staff_Requisition_Record') }}?status=approved" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $approvedItems ?? '12' }}</p>
            <p class="text-xs text-green-600 mt-1">Ready for processing</p>
        </a>

        <!-- Notifications -->
        <a href="{{ route('Staff_Notification') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-bell text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Notifications</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $notificationsCount ?? '8' }}</p>
            <p class="text-xs text-indigo-600 mt-1">Unread messages</p>
        </a>
    </div>

    <!-- Second Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Requisitions -->
        <a href="{{ route('Staff_Requisition_Record') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center rounded-lg">
                    <i class="fas fa-clipboard-list text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $totalRequisitions ?? '45' }}</p>
            <p class="text-xs text-text-muted mt-1">All time</p>
        </a>

        <!-- Item Requests -->
        <a href="{{ route('Staff_Item_Request') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Item Requests</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $itemRequests ?? '7' }}</p>
            <p class="text-xs text-blue-600 mt-1">My requests</p>
        </a>

        <!-- Receipts -->
        <a href="{{ route('Staff_Reciept') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-receipt text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Acknowledgement Receipts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $receiptsCount ?? '23' }}</p>
            <p class="text-xs text-text-muted mt-1">Received items</p>
        </a>

        <!-- Completed Requisitions -->
        <a href="{{ route('Staff_Requisition_Record') }}?status=completed" class="bg-white border-2 border-green-200 rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-check-double text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Completed Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $completedRequisitions ?? '15' }}</p>
            <p class="text-xs text-green-600 mt-1">Successfully processed</p>
        </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Requisitions</h3>
                <a href="{{ route('Staff_Requisition_Record') }}" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider flex items-center">
                    View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-caramel bg-cream-bg rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Baking Ingredients - Bulk Order</p>
                        <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0012</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-caramel text-white text-xs font-bold rounded-full">PENDING</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Kitchen Equipment</p>
                        <p class="text-xs text-text-muted mt-1">Operations Department • REQ-2024-0011</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded-full">APPROVED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Packaging Materials</p>
                        <p class="text-xs text-text-muted mt-1">Packaging Department • REQ-2024-0010</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">UNDER REVIEW</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-chocolate bg-cream-bg rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Office Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Administration • REQ-2024-0009</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-chocolate text-white text-xs font-bold rounded-full">PENDING</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <a href="{{ route('Staff_Create_Requisition') }}" class="block w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-plus-circle mr-2"></i>
                    New Requisition
                </a>

                <a href="{{ route('Staff_Item_Request') }}" class="block w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Item Request
                </a>

                <a href="{{ route('Staff_Requisition_Record') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                    <i class="fas fa-history mr-2 text-chocolate"></i>
                    My Requisitions
                </a>

                <a href="{{ route('Staff_Reciept') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                    <i class="fas fa-receipt mr-2 text-chocolate"></i>
                    Acknowledgement Receipts
                </a>
            </div>

            <!-- User Info Card -->
            <div class="mt-6 p-4 bg-cream-bg border border-border-soft rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                        <span class="text-white text-sm font-bold">
                            {{ substr(session('emp_name'), 0, 1) }}{{ substr(strstr(session('emp_name'), ' ') ?: '', 1, 1) }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">{{ session('emp_name') }}</p>
                        <p class="text-xs text-text-muted">{{ session('emp_position') }}</p>
                        <p class="text-xs text-text-muted mt-1">{{ session('username') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Item Requests -->
        <div class="bg-white border-2 border-border-soft rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Recent Item Requests</h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">IR-2024-0456</p>
                    <p class="text-xs text-text-muted mt-1">Chocolate Chips • 5 kg • Urgent</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full">APPROVED</span>
                        <span class="text-xs text-text-muted">Today</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">IR-2024-0457</p>
                    <p class="text-xs text-text-muted mt-1">Vanilla Extract • 2 L • Normal</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">PENDING</span>
                        <span class="text-xs text-text-muted">Yesterday</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">IR-2024-0458</p>
                    <p class="text-xs text-text-muted mt-1">Cake Boxes • 50 units • Medium</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold rounded-full">UNDER REVIEW</span>
                        <span class="text-xs text-text-muted">2 days ago</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Receipts -->
        <div class="bg-white border-2 border-green-200 rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-receipt text-green-500 mr-2"></i>
                Recent Acknowledgement Receipts
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">AR-2024-0789</p>
                            <p class="text-xs text-text-muted mt-1">Baking Ingredients • 15 items</p>
                            <p class="text-xs text-text-muted mt-1">Received: Today, 10:30 AM</p>
                        </div>
                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                            COMPLETE
                        </span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">AR-2024-0788</p>
                            <p class="text-xs text-text-muted mt-1">Kitchen Equipment • 3 items</p>
                            <p class="text-xs text-text-muted mt-1">Received: Yesterday, 3:15 PM</p>
                        </div>
                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                            COMPLETE
                        </span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">AR-2024-0787</p>
                            <p class="text-xs text-text-muted mt-1">Office Supplies • 8 items</p>
                            <p class="text-xs text-text-muted mt-1">Received: Dec 12, 2024</p>
                        </div>
                        <span class="px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                            PARTIAL
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection