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
            <p class="text-gray-500">System Administration Control Panel</p>
        </div>
        <div class="text-left md:text-right">
            <p class="font-display text-xl text-chocolate">{{ $currentDate }}</p>
            <p class="text-sm text-caramel font-medium tracking-wide uppercase mt-1">{{ $currentDay }}</p>
        </div>
    </div>

    {{-- 2. TOP ROW KPI CARDS (3 Cards) --}}
    {{-- Design: Large, prominent cards focusing on system health --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- System Status --}}
        <div class="bg-white border border-border-soft rounded-xl p-8 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">System Status</p>
                    <p class="font-display text-4xl font-bold text-{{ $databaseHealth['status_color'] }}-600 mb-2">
                        {{ ucfirst($databaseHealth['status']) }}
                    </p>
                    <p class="text-sm text-gray-600">{{ $databaseHealth['message'] }}</p>
                </div>
                <div class="w-16 h-16 rounded-full bg-{{ $databaseHealth['status_color'] }}-50 flex items-center justify-center border-2 border-{{ $databaseHealth['status_color'] }}-100">
                    <i class="fas fa-server text-{{ $databaseHealth['status_color'] }}-600 text-2xl"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">Last Backup: {{ $databaseHealth['last_backup'] }}</p>
            </div>
        </div>

        {{-- User Base --}}
        <div class="bg-white border border-border-soft rounded-xl p-8 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">User Base</p>
                    <p class="font-display text-4xl font-bold text-chocolate mb-2">{{ $activeUsers }}</p>
                    <p class="text-sm text-gray-600">Active Users</p>
                </div>
                <div class="w-16 h-16 rounded-full bg-cream-bg flex items-center justify-center border-2 border-border-soft">
                    <i class="fas fa-users text-chocolate text-2xl"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">{{ $inactiveUsers }} Inactive • {{ $totalUsers }} Total</p>
            </div>
        </div>

        {{-- Security Watch --}}
        <div class="bg-white border border-border-soft rounded-xl p-8 shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Security Watch</p>
                    <p class="font-display text-4xl font-bold text-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-600 mb-2">
                        {{ $securityAlertsCount }}
                    </p>
                    <p class="text-sm text-gray-600">{{ $securityAlertsCount > 0 ? 'Alerts Detected' : 'System Secure' }}</p>
                </div>
                <div class="w-16 h-16 rounded-full bg-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-50 flex items-center justify-center border-2 border-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-100">
                    <i class="fas fa-shield-alt text-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-600 text-2xl"></i>
                </div>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">{{ $auditLogVolume }} system events in last 24h</p>
            </div>
        </div>
    </div>

    {{-- 3. QUICK ACTIONS GRID (2x3 Admin Tasks) --}}
    {{-- Design: Large, clickable cards for common admin functions --}}
    <div class="bg-white border border-border-soft rounded-xl p-8 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <h2 class="font-display text-2xl font-bold text-chocolate">Quick Actions</h2>
            <p class="text-sm text-gray-500">Common System Administration Tasks</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- User Management --}}
            <a href="{{ route('admin.users.index') }}" class="group bg-gradient-to-br from-chocolate to-chocolate-dark text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-users-cog text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">User Management</h3>
                        <p class="text-white/80 text-sm">Manage user accounts, roles, and permissions</p>
                    </div>
                </div>
            </a>

            {{-- Notifications --}}
            <a href="{{ route('admin.notifications.index') }}" class="group bg-gradient-to-br from-caramel to-amber-600 text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-bell text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">Notifications</h3>
                        <p class="text-white/80 text-sm">Manage system notifications and alerts</p>
                    </div>
                </div>
            </a>

            {{-- Audit Logs --}}
            <a href="{{ route('admin.audit-logs.index') }}" class="group bg-gradient-to-br from-slate-600 to-slate-700 text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-file-contract text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">Audit Logs</h3>
                        <p class="text-white/80 text-sm">Monitor system activities and security events</p>
                    </div>
                </div>
            </a>

            {{-- Master Data --}}
            <a href="{{ route('admin.items.index') }}" class="group bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-database text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">Master Data</h3>
                        <p class="text-white/80 text-sm">Manage items, categories, and units</p>
                    </div>
                </div>
            </a>

            {{-- Suppliers --}}
            <a href="{{ route('admin.suppliers.index') }}" class="group bg-gradient-to-br from-violet-600 to-violet-700 text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-truck text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">Suppliers</h3>
                        <p class="text-white/80 text-sm">Manage supplier information and relationships</p>
                    </div>
                </div>
            </a>

            {{-- System Health --}}
            <a href="#" onclick="location.reload()" class="group bg-gradient-to-br from-orange-600 to-orange-700 text-white rounded-xl p-8 hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                <div class="flex flex-col items-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <i class="fas fa-heartbeat text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold mb-2">System Health</h3>
                        <p class="text-white/80 text-sm">Refresh monitoring data and diagnostics</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- 4. SYSTEM STATISTICS SUMMARY --}}
    {{-- Design: Clean summary cards showing system metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        
        {{-- Total Items --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Total SKU</p>
                    <p class="font-display text-3xl font-bold text-chocolate">{{ number_format($totalItems) }}</p>
                    <p class="mt-2 text-xs text-gray-500">In {{ $categoryCount }} Categories</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center border border-border-soft">
                    <i class="fas fa-box-open text-chocolate text-lg"></i>
                </div>
            </div>
        </div>

        {{-- System Events --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">System Events</p>
                    <p class="font-display text-3xl font-bold text-chocolate">{{ $auditLogVolume }}</p>
                    <p class="mt-2 text-xs text-gray-500">Last 24 Hours</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-cream-bg flex items-center justify-center border border-border-soft">
                    <i class="fas fa-chart-line text-chocolate text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Database Status --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Database</p>
                    <p class="font-display text-3xl font-bold text-{{ $databaseHealth['status_color'] }}-600">
                        {{ ucfirst($databaseHealth['status']) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Connection Status</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-{{ $databaseHealth['status_color'] }}-50 flex items-center justify-center border border-{{ $databaseHealth['status_color'] }}-100">
                    <i class="fas fa-database text-{{ $databaseHealth['status_color'] }}-600 text-lg"></i>
                </div>
            </div>
        </div>

        {{-- Security Level --}}
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-caramel uppercase tracking-widest mb-2">Security</p>
                    <p class="font-display text-3xl font-bold text-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-600">
                        {{ $securityAlertsCount > 0 ? 'Alert' : 'Secure' }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Current Status</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-50 flex items-center justify-center border border-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-100">
                    <i class="fas fa-shield-alt text-{{ $securityAlertsCount > 0 ? 'red' : 'green' }}-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection