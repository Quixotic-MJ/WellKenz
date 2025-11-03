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
                    <h1 class="font-display text-3xl font-bold text-text-dark">{{ $greeting }}, {{ session('emp_name') }}
                    </h1>
                    <p class="text-text-muted mt-2">Welcome to your {{ session('role') }} dashboard. Here's your bakery overview for today.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-text-dark font-semibold">{{ date('F j, Y') }}</p>
                    <p class="text-xs text-text-muted mt-1">{{ date('l') }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Employees -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-caramel flex items-center justify-center rounded-lg">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Employees</p>
                <p class="text-3xl font-bold text-text-dark mt-2">24</p>
                <p class="text-xs text-text-muted mt-1">Active staff members</p>
            </div>

            <!-- Pending Requisitions -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-500 flex items-center justify-center rounded-lg">
                        <i class="fas fa-clock text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Requisitions</p>
                <p class="text-3xl font-bold text-text-dark mt-2">8</p>
                <p class="text-xs text-text-muted mt-1">Awaiting approval</p>
            </div>

            <!-- Approved POs -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-500 flex items-center justify-center rounded-lg">
                        <i class="fas fa-check-circle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved POs</p>
                <p class="text-3xl font-bold text-text-dark mt-2">15</p>
                <p class="text-xs text-text-muted mt-1">This month</p>
            </div>

            <!-- Low-Stock Items -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-500 flex items-center justify-center rounded-lg">
                        <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                    </div>
                </div>
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low-Stock Items</p>
                <p class="text-3xl font-bold text-text-dark mt-2">6</p>
                <p class="text-xs text-text-muted mt-1">Need restocking</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Charts Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Requisition Trends Chart -->
                <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display text-xl font-bold text-text-dark">Requisition Trends</h3>
                        <select class="text-xs border border-border-soft rounded px-3 py-1 bg-cream-bg">
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    
                    <!-- Chart Container -->
                    <div class="h-80 bg-cream-bg rounded-lg p-4 flex items-center justify-center">
                        <div class="text-center text-text-muted">
                            <i class="fas fa-chart-line text-4xl mb-2 text-caramel"></i>
                            <p class="text-sm">Requisition trends chart would be displayed here</p>
                            <p class="text-xs mt-1">Showing monthly requisition patterns and comparisons</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Levels Chart -->
                <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-display text-xl font-bold text-text-dark">Inventory Levels</h3>
                        <select class="text-xs border border-border-soft rounded px-3 py-1 bg-cream-bg">
                            <option>All Categories</option>
                            <option>Raw Materials</option>
                            <option>Packaging</option>
                            <option>Finished Goods</option>
                        </select>
                    </div>
                    
                    <!-- Chart Container -->
                    <div class="h-80 bg-cream-bg rounded-lg p-4 flex items-center justify-center">
                        <div class="text-center text-text-muted">
                            <i class="fas fa-chart-pie text-4xl mb-2 text-chocolate"></i>
                            <p class="text-sm">Inventory levels chart would be displayed here</p>
                            <p class="text-xs mt-1">Showing stock levels across different product categories</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities & Quick Actions -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                    <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>

                    <div class="space-y-3">
                        <a href="{{ route('Admin_Requisition') }}"
                            class="block w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded-lg hover-lift">
                            <i class="fas fa-plus-circle mr-2"></i>
                            New Requisition
                        </a>

                        <a href="{{ route('Admin_Purchase_Order') }}"
                            class="block w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold rounded-lg hover-lift">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Purchase Orders
                        </a>

                        <a href="{{ route('Admin_Inventory') }}"
                            class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                            <i class="fas fa-warehouse mr-2 text-chocolate"></i>
                            Inventory Check
                        </a>

                        <a href="{{ route('Admin_Report') }}"
                            class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                            <i class="fas fa-chart-bar mr-2 text-chocolate"></i>
                            View Reports
                        </a>
                    </div>
                </div>

                <!-- Recent Activities Table -->
                <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                    <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                        <i class="fas fa-history text-caramel mr-2"></i>
                        Recent Activities
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border-soft">
                                    <th class="text-left text-xs font-bold text-text-muted uppercase tracking-wider py-3">Activity</th>
                                    <th class="text-left text-xs font-bold text-text-muted uppercase tracking-wider py-3">User</th>
                                    <th class="text-left text-xs font-bold text-text-muted uppercase tracking-wider py-3">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-soft">
                                <tr>
                                    <td class="py-3 text-sm text-text-dark">New requisition created</td>
                                    <td class="py-3 text-sm text-text-muted">Maria Garcia</td>
                                    <td class="py-3 text-sm text-text-muted">2 hours ago</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-sm text-text-dark">Purchase order approved</td>
                                    <td class="py-3 text-sm text-text-muted">John Smith</td>
                                    <td class="py-3 text-sm text-text-muted">5 hours ago</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-sm text-text-dark">Inventory updated</td>
                                    <td class="py-3 text-sm text-text-muted">Robert Chen</td>
                                    <td class="py-3 text-sm text-text-muted">Yesterday</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-sm text-text-dark">Low stock alert</td>
                                    <td class="py-3 text-sm text-text-muted">System</td>
                                    <td class="py-3 text-sm text-text-muted">1 day ago</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-sm text-text-dark">New employee added</td>
                                    <td class="py-3 text-sm text-text-muted">Admin</td>
                                    <td class="py-3 text-sm text-text-muted">2 days ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Alert Panel -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Low Stock Alerts
                </h3>

                <div class="space-y-3">
                    <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                        <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                        <p class="text-xs text-text-muted mt-1">Current stock: 15kg (Minimum: 50kg)</p>
                        <p class="text-xs text-text-muted mt-2">Last updated: Today</p>
                    </div>

                    <div class="p-4 border-l-4 border-amber-500 bg-amber-50 rounded-lg">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                        <p class="text-xs text-text-muted mt-1">Current stock: 8kg (Minimum: 20kg)</p>
                        <p class="text-xs text-text-muted mt-2">Last updated: Yesterday</p>
                    </div>

                    <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                        <p class="text-sm font-bold text-text-dark">Butter</p>
                        <p class="text-xs text-text-muted mt-1">Current stock: 12kg (Minimum: 25kg)</p>
                        <p class="text-xs text-text-muted mt-2">Last updated: 2 days ago</p>
                    </div>
                </div>
            </div>

            <!-- Recent Requisitions -->
            <div class="bg-white border-2 border-border-soft rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-display text-xl font-bold text-text-dark">Recent Requisitions</h3>
                    <a href="{{ route('Admin_Requisition') }}"
                        class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider flex items-center">
                        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start justify-between p-4 border-l-4 border-amber-500 bg-amber-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Baking Ingredients - Bulk Order</p>
                            <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0012</p>
                            <p class="text-xs text-text-muted mt-2">Requested by: Maria Garcia</p>
                        </div>
                        <span class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full">PENDING</span>
                    </div>

                    <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Packaging Materials</p>
                            <p class="text-xs text-text-muted mt-1">Packaging Department • REQ-2024-0013</p>
                            <p class="text-xs text-text-muted mt-2">Requested by: John Smith</p>
                        </div>
                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">APPROVED</span>
                    </div>

                    <div class="flex items-start justify-between p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Emergency Flour Supply</p>
                            <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0014</p>
                            <p class="text-xs text-text-muted mt-2">Requested by: Robert Chen</p>
                        </div>
                        <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">URGENT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection