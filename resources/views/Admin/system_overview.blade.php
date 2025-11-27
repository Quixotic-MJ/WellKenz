@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. Welcome Section --}}
    {{-- Design: Clean, high-end greeting using the Display font and primary chocolate color --}}
    <div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl md:text-4xl font-bold text-chocolate mb-2">
                {{ $greeting }}, {{ $userName }}
            </h1>
            <p class="text-gray-500">Here is your system configuration overview.</p>
        </div>
        <div class="text-left md:text-right">
            <p class="font-display text-xl text-chocolate">{{ $currentDate }}</p>
            <p class="text-sm text-caramel font-medium tracking-wide uppercase mt-1">{{ $currentDay }}</p>
        </div>
    </div>

    {{-- 2. MASTER CONFIG METRICS --}}
    {{-- Design: Uniform cards with 'border-soft', strictly using theme fonts for hierarchy --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        
        {{-- Active Users --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Active Users</p>
                    <p class="font-display text-3xl font-bold text-chocolate">{{ $activeUsers }}</p>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <span class="inline-block w-2 h-2 rounded-full bg-gray-300 mr-2"></span>
                        {{ $inactiveUsers }} Inactive
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center border border-border-soft">
                    <i class="fas fa-users text-chocolate text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Total Items --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Total SKU</p>
                    <p class="font-display text-3xl font-bold text-chocolate">{{ number_format($totalItems) }}</p>
                    <p class="mt-2 text-xs text-gray-500">In {{ $categoryCount }} Categories</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center border border-border-soft">
                    <i class="fas fa-database text-chocolate text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Database Health --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">DB Health</p>
                    {{-- Note: Keeping dynamic status color logic for utility validity, but formatted nicely --}}
                    <p class="font-display text-3xl font-bold text-{{ $databaseHealth['status_color'] }}-600">
                        {{ $databaseHealth['status'] }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Backup: {{ $databaseHealth['last_backup'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-{{ $databaseHealth['status_color'] }}-50 flex items-center justify-center border border-{{ $databaseHealth['status_color'] }}-100">
                    <i class="fas fa-server text-{{ $databaseHealth['status_color'] }}-600 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Security Alerts --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Security</p>
                    <p class="font-display text-3xl font-bold text-chocolate">{{ $securityAlertsCount }}</p>
                    <p class="mt-2 text-xs {{ $securityAlertsCount > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                        {{ $securityAlertsCount > 0 ? 'Action Required' : 'System Secure' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center border border-border-soft">
                    <i class="fas fa-shield-alt text-chocolate text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. OPERATIONAL SNAPSHOT --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Requisition Summary --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex flex-col h-full">
            <div class="flex items-center justify-between mb-6 border-b border-border-soft pb-4">
                <h2 class="font-display text-xl font-bold text-chocolate">Requisitions</h2>
                <a href="{{ route('supervisor.approvals.requisitions') }}" class="text-xs font-bold text-caramel hover:text-chocolate transition-colors uppercase tracking-wider">
                    Manage &rarr;
                </a>
            </div>
            
            <div class="space-y-4 flex-grow">
                {{-- Pending --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-cream-bg border border-border-soft">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <span class="text-sm font-medium text-chocolate">Pending Approval</span>
                    </div>
                    <span class="font-display text-lg font-bold text-chocolate">{{ $requisitions['pendingApproval'] }}</span>
                </div>

                {{-- Approved --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-border-soft">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600">Approved Today</span>
                    </div>
                    <span class="font-sans text-sm font-bold text-gray-900">{{ $requisitions['approvedToday'] }}</span>
                </div>

                {{-- Rejected --}}
                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-border-soft">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-sm text-gray-600">Rejected</span>
                    </div>
                    <span class="font-sans text-sm font-bold text-gray-900">{{ $requisitions['rejected'] }}</span>
                </div>
            </div>
        </div>

        {{-- Purchase Order Statistics --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex flex-col h-full">
            <div class="flex items-center justify-between mb-6 border-b border-border-soft pb-4">
                <h2 class="font-display text-xl font-bold text-chocolate">Purchase Orders</h2>
                <a href="{{ route('purchasing.po.open') }}" class="text-xs font-bold text-caramel hover:text-chocolate transition-colors uppercase tracking-wider">
                    View Orders &rarr;
                </a>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center mb-6">
                <div class="p-4 bg-cream-bg rounded-lg border border-border-soft">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Draft</p>
                    <p class="font-display text-2xl text-chocolate mt-1">{{ $purchaseOrders['draft'] }}</p>
                </div>
                <div class="p-4 bg-cream-bg rounded-lg border border-border-soft">
                    <p class="text-[10px] font-bold text-caramel uppercase tracking-wider">Ordered</p>
                    <p class="font-display text-2xl text-chocolate mt-1">{{ $purchaseOrders['ordered'] }}</p>
                </div>
                <div class="p-4 bg-chocolate rounded-lg border border-chocolate shadow-md">
                    <p class="text-[10px] font-bold text-white/80 uppercase tracking-wider">Delivered</p>
                    <p class="font-display text-2xl text-white mt-1">{{ $purchaseOrders['delivered'] }}</p>
                </div>
            </div>
            
            <div class="mt-auto text-center">
                <p class="text-xs text-gray-500">Avg. Delivery Time</p>
                <p class="font-display text-lg text-chocolate">{{ $purchaseOrders['averageDeliveryTime'] }} Days</p>
            </div>
        </div>
    </div>

    {{-- 4. ALERTS & CONFIG SHORTCUTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Low-Stock Alerts --}}
        <div class="lg:col-span-1 bg-white border border-border-soft rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-lg font-bold text-chocolate">Low Stock</h3>
                <span class="text-xs bg-red-50 text-red-700 px-3 py-1 rounded-full font-bold border border-red-100">{{ $lowStockCount }}</span>
            </div>
            <div class="space-y-3">
                @forelse($lowStockAlerts as $alert)
                <div class="group p-3 border border-border-soft rounded-lg hover:border-caramel transition-colors">
                    <p class="text-sm font-bold text-chocolate group-hover:text-caramel transition-colors">{{ $alert['name'] }}</p>
                    <div class="flex justify-between items-end mt-2">
                        <p class="text-xs text-gray-500">Current: <span class="font-bold text-red-600">{{ number_format($alert['current_stock'], 1) }}</span></p>
                        <p class="text-xs text-gray-400">Reorder: {{ number_format($alert['reorder_level'], 1) }}</p>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center border border-dashed border-border-soft rounded-lg">
                    <p class="text-xs text-gray-400">Inventory levels are healthy.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Near-Expiry Alerts --}}
        <div class="lg:col-span-1 bg-white border border-border-soft rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-lg font-bold text-chocolate">Expiring Soon</h3>
                <span class="text-xs bg-amber-50 text-amber-700 px-3 py-1 rounded-full font-bold border border-amber-100">{{ count($expiringBatches) }}</span>
            </div>
            <div class="space-y-3">
                @forelse($expiringBatches as $batch)
                <div class="group p-3 border border-border-soft rounded-lg hover:border-caramel transition-colors">
                    <p class="text-sm font-bold text-chocolate group-hover:text-caramel transition-colors">{{ $batch['item_name'] }}</p>
                    <p class="text-xs text-amber-600 mt-1 font-medium bg-amber-50 inline-block px-2 py-0.5 rounded">{{ $batch['expiry_status'] }}</p>
                </div>
                @empty
                <div class="p-6 text-center border border-dashed border-border-soft rounded-lg">
                    <p class="text-xs text-gray-400">No immediate expiry risks.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Admin Actions --}}
        <div class="lg:col-span-1 bg-white border border-border-soft rounded-xl p-6 shadow-sm flex flex-col">
            <h3 class="font-display text-lg font-bold text-chocolate mb-5">Shortcuts</h3>
            <div class="space-y-3 flex-grow">
                {{-- Primary Action --}}
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between w-full px-5 py-4 bg-chocolate text-white hover:bg-chocolate-dark hover:shadow-lg transition-all rounded-lg shadow-md group">
                    <span class="text-sm font-medium"><i class="fas fa-user-plus mr-3 text-caramel group-hover:text-white transition-colors"></i>Create New User</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>

                {{-- Secondary Actions --}}
                <a href="{{ route('admin.items.index') }}" class="flex items-center justify-between w-full px-5 py-3 border border-border-soft text-chocolate hover:border-caramel hover:text-caramel hover:bg-cream-bg transition-all rounded-lg">
                    <span class="text-sm font-medium"><i class="fas fa-box-open mr-3 opacity-60"></i>Add New Item</span>
                    <i class="fas fa-chevron-right text-xs opacity-40"></i>
                </a>
                <a href="{{ route('admin.backups.index') }}" class="flex items-center justify-between w-full px-5 py-3 border border-border-soft text-chocolate hover:border-caramel hover:text-caramel hover:bg-cream-bg transition-all rounded-lg">
                    <span class="text-sm font-medium"><i class="fas fa-cloud-download-alt mr-3 opacity-60"></i>Backups</span>
                    <i class="fas fa-chevron-right text-xs opacity-40"></i>
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center justify-between w-full px-5 py-3 border border-border-soft text-chocolate hover:border-caramel hover:text-caramel hover:bg-cream-bg transition-all rounded-lg">
                    <span class="text-sm font-medium"><i class="fas fa-file-contract mr-3 opacity-60"></i>Audit Logs</span>
                    <i class="fas fa-chevron-right text-xs opacity-40"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- 5. LOGS & SECURITY FEED --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Database Transaction Feed --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-lg font-bold text-chocolate">Recent Updates</h3>
                <a href="{{ route('admin.items.index') }}" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider">Masterlist &rarr;</a>
            </div>
            <div class="space-y-4">
                @forelse($recentUpdates as $update)
                <div class="flex items-start gap-4 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-cream-bg border border-border-soft flex-shrink-0 flex items-center justify-center text-chocolate">
                        <i class="{{ $update['icon'] }} text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-chocolate">{{ $update['description'] }}</p>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-gray-500">{{ $update['user_name'] }}</p>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wide">{{ $update['time_ago'] }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-4 text-center">
                    <span class="text-xs text-gray-400 italic">No recent updates found.</span>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Security / Audit Logs --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-lg font-bold text-chocolate">Security Log</h3>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-bold text-caramel hover:text-chocolate uppercase tracking-wider">Full Audit &rarr;</a>
            </div>
            <div class="space-y-4">
                @forelse($securityLogs as $log)
                <div class="flex items-start gap-3">
                    <div class="mt-2 w-1.5 h-1.5 rounded-full {{ $log['color'] }} ring-2 ring-white flex-shrink-0"></div>
                    <div class="flex-1 min-w-0 bg-cream-bg rounded-lg p-3 border border-border-soft">
                        <p class="text-sm text-chocolate font-bold">{{ $log['description'] }}</p>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $log['details'] }}</p>
                        <p class="text-[10px] text-gray-400 mt-2 text-right uppercase tracking-wider">{{ $log['time_ago'] }}</p>
                    </div>
                </div>
                @empty
                <div class="flex items-center justify-center h-32 text-center bg-cream-bg rounded-lg border border-dashed border-border-soft">
                    <div>
                        <i class="fas fa-check-circle text-green-500 mb-2"></i>
                        <p class="text-sm text-chocolate font-medium">All systems normal</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection