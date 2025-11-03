@extends('Admin.layout.app')

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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Pending Approvals -->
        <a href="{{ route('Admin_approvals') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Approvals</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $pendingApprovals ?? '7' }}</p>
            <p class="text-xs text-yellow-600 mt-1">Awaiting your review</p>
        </a>

        <!-- Active POs -->
        <a href="{{ route('Admin_purchasing') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active POs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $activePOs ?? '12' }}</p>
            <p class="text-xs text-blue-600 mt-1">In progress</p>
        </a>

        <!-- Low-stock Items -->
        <a href="{{ route('Admin_inventory') }}?filter=low_stock" class="bg-white border-2 border-red-200 rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low-stock Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $lowStockItems ?? '9' }}</p>
            <p class="text-xs text-red-600 mt-1">Needs attention</p>
        </a>

        <!-- Notifications -->
        <a href="{{ route('Admin_notifications') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-bell text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Notifications</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $notificationsCount ?? '5' }}</p>
            <p class="text-xs text-indigo-600 mt-1">Unread messages</p>
        </a>
    </div>

    <!-- Second Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Approved Requisitions -->
        <a href="{{ route('Admin_requisition') }}?status=approved" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $approvedRequisitions ?? '8' }}</p>
            <p class="text-xs text-green-600 mt-1">Ready for PO creation</p>
        </a>

        <!-- Stock Summary -->
        <a href="{{ route('Admin_inventory') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-warehouse text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Summary</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $totalInventoryItems ?? '2,347' }}</p>
            <p class="text-xs text-purple-600 mt-1">Total items</p>
        </a>

        <!-- Pending AR -->
        <a href="{{ route('Admin_ar_pending') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-file-invoice text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending AR</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $pendingAR ?? '15' }}</p>
            <p class="text-xs text-orange-600 mt-1">Awaiting processing</p>
        </a>

        <!-- Completed POs -->
        <a href="{{ route('Admin_purchasing') }}?status=completed" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-teal-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-clipboard-list text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Completed POs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $completedPOs ?? '23' }}</p>
            <p class="text-xs text-teal-600 mt-1">This month</p>
        </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Requisitions</h3>
                <a href="{{ route('Admin_requisition') }}" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider flex items-center">
                    View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-caramel bg-cream-bg rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Baking Ingredients - Bulk Order</p>
                        <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0012</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: Maria Garcia</p>
                    </div>
                    <span class="px-3 py-1 bg-caramel text-white text-xs font-bold rounded-full">PENDING</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Kitchen Equipment</p>
                        <p class="text-xs text-text-muted mt-1">Operations Department • REQ-2024-0011</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: Robert Johnson</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded-full">APPROVED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Packaging Materials</p>
                        <p class="text-xs text-text-muted mt-1">Packaging Department • REQ-2024-0010</p>
                        <p class="text-xs text-text-muted mt-2">Requested by: Sarah Chen</p>
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
                <a href="{{ route('Admin_requisition') }}" class="block w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-plus-circle mr-2"></i>
                    New Requisition
                </a>

                <a href="{{ route('Admin_purchasing') }}" class="block w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Purchase Orders
                </a>

                <a href="{{ route('Admin_inventory') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                    <i class="fas fa-warehouse mr-2 text-chocolate"></i>
                    Inventory Check
                </a>

                <a href="{{ route('Admin_report') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                    <i class="fas fa-chart-bar mr-2 text-chocolate"></i>
                    View Reports
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
        <!-- Purchase Orders -->
        <div class="bg-white border-2 border-border-soft rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Active Purchase Orders</h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0456</p>
                    <p class="text-xs text-text-muted mt-1">Flour & Sugar • Baker's Supply Co. • $2,450</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full">DELIVERED</span>
                        <span class="text-xs text-text-muted">Today</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0457</p>
                    <p class="text-xs text-text-muted mt-1">Packaging • PackPro Inc. • $1,230</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">IN TRANSIT</span>
                        <span class="text-xs text-text-muted">ETA: Tomorrow</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50 rounded-lg">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0458</p>
                    <p class="text-xs text-text-muted mt-1">Equipment • KitchenTech • $8,750</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold rounded-full">PROCESSING</span>
                        <span class="text-xs text-text-muted">2 days ago</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white border-2 border-red-200 rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Low Stock Alerts
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                            <p class="text-xs text-text-muted mt-1">Current: 15 kg • Minimum: 50 kg</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: 30%"></div>
                            </div>
                        </div>
                        <button class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition rounded-full">
                            REORDER
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                            <p class="text-xs text-text-muted mt-1">Current: 2 kg • Minimum: 10 kg</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: 20%"></div>
                            </div>
                        </div>
                        <button class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition rounded-full">
                            REORDER
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Cake Boxes (Large)</p>
                            <p class="text-xs text-text-muted mt-1">Current: 25 units • Minimum: 100 units</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: 25%"></div>
                            </div>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition rounded-full">
                            REORDER
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection