@extends('Admin.layout.app')

@section('title', 'Dashboard - WellKenz ERP')

@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
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
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $greeting }}, {{ session('emp_name') }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Welcome to your {{ session('role') }} dashboard</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-900 font-medium">{{ date('F j, Y') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ date('l') }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Employees -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Employees</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-2">24</p>
                        <p class="text-xs text-gray-400 mt-1">Active staff members</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                        <i class="fas fa-users text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Requisitions -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Pending Requisitions</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-2">8</p>
                        <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                        <i class="fas fa-clock text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Approved POs -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Approved POs</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-2">15</p>
                        <p class="text-xs text-gray-400 mt-1">This month</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                        <i class="fas fa-check-circle text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Low-Stock Items -->
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Low-Stock Items</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-2">6</p>
                        <p class="text-xs text-gray-400 mt-1">Need restocking</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded">
                        <i class="fas fa-exclamation-triangle text-gray-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Charts Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Requisition Trends Chart -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Requisition Trends</h3>
                        <select class="text-sm border border-gray-300 rounded px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    
                    <!-- Chart Container -->
                    <div class="h-64 bg-gray-50 rounded flex items-center justify-center border border-gray-200">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-chart-line text-3xl mb-2"></i>
                            <p class="text-sm">Requisition trends chart</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Levels Chart -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Inventory Levels</h3>
                        <select class="text-sm border border-gray-300 rounded px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option>All Categories</option>
                            <option>Raw Materials</option>
                            <option>Packaging</option>
                            <option>Finished Goods</option>
                        </select>
                    </div>
                    
                    <!-- Chart Container -->
                    <div class="h-64 bg-gray-50 rounded flex items-center justify-center border border-gray-200">
                        <div class="text-center text-gray-400">
                            <i class="fas fa-chart-pie text-3xl mb-2"></i>
                            <p class="text-sm">Inventory levels chart</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

                    <div class="space-y-2">
                        <a href="{{ route('Admin_Requisition') }}"
                            class="block w-full px-4 py-2.5 bg-gray-900 text-white hover:bg-gray-800 transition text-center text-sm font-medium rounded">
                            <i class="fas fa-plus-circle mr-2"></i>
                            New Requisition
                        </a>

                        <a href="{{ route('Admin_Purchase_Order') }}"
                            class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Purchase Orders
                        </a>

                        <a href="{{ route('Admin_Inventory') }}"
                            class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                            <i class="fas fa-warehouse mr-2"></i>
                            Inventory Check
                        </a>

                        <a href="{{ route('Admin_Report') }}"
                            class="block w-full px-4 py-2.5 border border-gray-300 hover:bg-gray-50 transition text-center text-sm font-medium text-gray-700 rounded">
                            <i class="fas fa-chart-bar mr-2"></i>
                            View Reports
                        </a>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt text-gray-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 font-medium">New requisition created</p>
                                <p class="text-xs text-gray-500 mt-0.5">Maria Garcia • 2 hours ago</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-gray-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 font-medium">Purchase order approved</p>
                                <p class="text-xs text-gray-500 mt-0.5">John Smith • 5 hours ago</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-gray-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 font-medium">Inventory updated</p>
                                <p class="text-xs text-gray-500 mt-0.5">Robert Chen • Yesterday</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation text-gray-600 text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 font-medium">Low stock alert</p>
                                <p class="text-xs text-gray-500 mt-0.5">System • 1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Alert Panel -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Low Stock Alerts</h3>

                <div class="space-y-3">
                    <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded">
                        <p class="text-sm font-medium text-gray-900">All-Purpose Flour</p>
                        <p class="text-xs text-gray-600 mt-1">Current: 15kg • Minimum: 50kg</p>
                        <p class="text-xs text-gray-500 mt-1">Last updated: Today</p>
                    </div>

                    <div class="p-4 border-l-4 border-amber-500 bg-amber-50 rounded">
                        <p class="text-sm font-medium text-gray-900">Chocolate Chips</p>
                        <p class="text-xs text-gray-600 mt-1">Current: 8kg • Minimum: 20kg</p>
                        <p class="text-xs text-gray-500 mt-1">Last updated: Yesterday</p>
                    </div>

                    <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded">
                        <p class="text-sm font-medium text-gray-900">Butter</p>
                        <p class="text-xs text-gray-600 mt-1">Current: 12kg • Minimum: 25kg</p>
                        <p class="text-xs text-gray-500 mt-1">Last updated: 2 days ago</p>
                    </div>

                    <div class="p-4 border-l-4 border-amber-500 bg-amber-50 rounded">
                        <p class="text-sm font-medium text-gray-900">Fresh Cream</p>
                        <p class="text-xs text-gray-600 mt-1">Current: 5L • Minimum: 15L</p>
                        <p class="text-xs text-gray-500 mt-1">Last updated: Today</p>
                    </div>
                </div>
            </div>

            <!-- Recent Requisitions -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Requisitions</h3>
                    <a href="{{ route('Admin_Requisition') }}"
                        class="text-xs font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wider">
                        View All →
                    </a>
                </div>

                <div class="space-y-3">
                    <div class="p-4 border border-gray-200 rounded">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Baking Ingredients - Bulk Order</p>
                                <p class="text-xs text-gray-500 mt-1">Production Department • REQ-2024-0012</p>
                                <p class="text-xs text-gray-500 mt-1">Requested by: Maria Garcia</p>
                            </div>
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-medium rounded ml-2">PENDING</span>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Packaging Materials</p>
                                <p class="text-xs text-gray-500 mt-1">Packaging Department • REQ-2024-0013</p>
                                <p class="text-xs text-gray-500 mt-1">Requested by: John Smith</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded ml-2">APPROVED</span>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Emergency Flour Supply</p>
                                <p class="text-xs text-gray-500 mt-1">Production Department • REQ-2024-0014</p>
                                <p class="text-xs text-gray-500 mt-1">Requested by: Robert Chen</p>
                            </div>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded ml-2">URGENT</span>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Dairy Products Restock</p>
                                <p class="text-xs text-gray-500 mt-1">Production Department • REQ-2024-0015</p>
                                <p class="text-xs text-gray-500 mt-1">Requested by: Sarah Lee</p>
                            </div>
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded ml-2">DRAFT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection