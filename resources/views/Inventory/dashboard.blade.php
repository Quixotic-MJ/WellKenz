@extends('Inventory.layout.app')

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
        <!-- Low-stock Alerts -->
        <a href="{{ route('Inventory_Low_Stock_Alert_notification') }}" class="bg-white border-2 border-red-200 rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low-stock Alerts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $lowStockAlerts ?? '23' }}</p>
            <p class="text-xs text-red-600 mt-1">Needs immediate attention</p>
        </a>

        <!-- Stock Summary -->
        <a href="{{ route('Inventory_List') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-warehouse text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Summary</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $totalInventoryItems ?? '2,347' }}</p>
            <p class="text-xs text-blue-600 mt-1">Total items in inventory</p>
        </a>

        <!-- Recent Stock In -->
        <a href="{{ route('Inventory_Stock_in') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-arrow-down text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Recent Stock In</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $recentStockIn ?? '18' }}</p>
            <p class="text-xs text-green-600 mt-1">Today's entries</p>
        </a>
    </div>

    <!-- Second Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stock Out Today -->
        <a href="{{ route('Inventory_Stock_out') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center rounded-lg">
                    <i class="fas fa-arrow-up text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Out Today</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $stockOutToday ?? '45' }}</p>
            <p class="text-xs text-text-muted mt-1">Items issued today</p>
        </a>

        <!-- Critical Items -->
        <a href="{{ route('Inventory_Low_Stock_Alert_notification') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-exclamation-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Critical Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $criticalItems ?? '12' }}</p>
            <p class="text-xs text-orange-600 mt-1">Below minimum stock</p>
        </a>

        <!-- Notifications -->
        <a href="{{ route('Inventory_Notification') }}" class="bg-white border-2 border-border-soft rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-bell text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Notifications</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $notificationsCount ?? '8' }}</p>
            <p class="text-xs text-indigo-600 mt-1">Unread messages</p>
        </a>

        <!-- Expiring Soon -->
        <a href="{{ route('Inventory_List') }}?filter=expiring" class="bg-white border-2 border-yellow-200 rounded-lg p-6 hover-lift transition-all duration-200 cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-500 flex items-center justify-center rounded-lg">
                    <i class="fas fa-calendar-exclamation text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Expiring Soon</p>
            <p class="text-3xl font-bold text-text-dark mt-2">{{ $expiringSoon ?? '7' }}</p>
            <p class="text-xs text-yellow-600 mt-1">Within 30 days</p>
        </a>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Stock Movements -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Stock Movements</h3>
                <a href="{{ route('Inventory_Report') }}" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider flex items-center">
                    View Report <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">All-Purpose Flour - Stock In</p>
                        <p class="text-xs text-text-muted mt-1">Bulk Ingredients • 500 kg • PO-2024-0456</p>
                        <p class="text-xs text-text-muted mt-2">Processed by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold rounded-full">COMPLETED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-red-500 bg-red-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chips - Stock Out</p>
                        <p class="text-xs text-text-muted mt-1">Production Department • 25 kg • REQ-2024-0012</p>
                        <p class="text-xs text-text-muted mt-2">Issued by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full">ISSUED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Vanilla Extract - Stock In</p>
                        <p class="text-xs text-text-muted mt-1">Flavorings • 20 L • PO-2024-0457</p>
                        <p class="text-xs text-text-muted mt-2">Processed by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">IN PROGRESS</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-orange-500 bg-orange-50 rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Cake Boxes - Stock Adjustment</p>
                        <p class="text-xs text-text-muted mt-1">Packaging • -15 units • Damaged Items</p>
                        <p class="text-xs text-text-muted mt-2">Adjusted by: {{ session('emp_name') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-orange-600 text-white text-xs font-bold rounded-full">ADJUSTED</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <a href="{{ route('Inventory_Stock_in') }}" class="block w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-arrow-down mr-2"></i>
                    Stock In
                </a>

                <a href="{{ route('Inventory_Stock_out') }}" class="block w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold rounded-lg hover-lift">
                    <i class="fas fa-arrow-up mr-2"></i>
                    Stock Out
                </a>

                <a href="{{ route('Inventory_List') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
                    <i class="fas fa-clipboard-list mr-2 text-chocolate"></i>
                    Inventory List
                </a>

                <a href="{{ route('Inventory_Report') }}" class="block w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark rounded-lg hover-lift">
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
        <!-- Low Stock Alerts -->
        <div class="bg-white border-2 border-red-200 rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Critical Low Stock Alerts
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
                        <a href="{{ route('Inventory_Stock_in') }}" class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition rounded-full">
                            STOCK IN
                        </a>
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
                        <a href="{{ route('Inventory_Stock_in') }}" class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition rounded-full">
                            STOCK IN
                        </a>
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
                        <a href="{{ route('Inventory_Stock_in') }}" class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition rounded-full">
                            STOCK IN
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Expiry Alerts -->
        <div class="bg-white border-2 border-yellow-200 rounded-lg p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-calendar-exclamation text-yellow-500 mr-2"></i>
                Items Expiring Soon
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Fresh Cream</p>
                            <p class="text-xs text-text-muted mt-1">Expires: Dec 28, 2024 • Qty: 15 L</p>
                            <p class="text-xs text-red-600 mt-1">⚠️ 3 days remaining</p>
                        </div>
                        <span class="px-3 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full">
                            URGENT
                        </span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Eggs (Tray)</p>
                            <p class="text-xs text-text-muted mt-1">Expires: Jan 5, 2025 • Qty: 8 trays</p>
                            <p class="text-xs text-orange-600 mt-1">⚠️ 10 days remaining</p>
                        </div>
                        <span class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full">
                            WARNING
                        </span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Butter</p>
                            <p class="text-xs text-text-muted mt-1">Expires: Jan 15, 2025 • Qty: 12 kg</p>
                            <p class="text-xs text-blue-600 mt-1">ℹ️ 20 days remaining</p>
                        </div>
                        <span class="px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                            MONITOR
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection