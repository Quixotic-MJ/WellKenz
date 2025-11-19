@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. Welcome card --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm relative overflow-hidden">
        <!-- Decorative background pattern -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-orange-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 -mr-16 -mt-16"></div>
        
        <div class="flex items-center justify-between relative z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Good Morning, Admin</h1>
                <p class="text-sm text-gray-500 mt-1">Here is your system configuration overview.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-900 font-medium">October 24, 2023</p>
                <p class="text-xs text-gray-500 mt-1">Tuesday</p>
            </div>
        </div>
    </div>

    {{-- 2. MASTER CONFIG METRICS (Updated as requested) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        
        {{-- Active Users --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Users</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">24</p>
                    <p class="text-xs text-green-600 mt-1 flex items-center">
                        <i class="fas fa-check-circle mr-1"></i> All Active
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-50 flex items-center justify-center rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Items in Database --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Items (SKU)</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">1,450</p>
                    <p class="text-xs text-gray-500 mt-1">Across 12 Categories</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 flex items-center justify-center rounded-lg">
                    <i class="fas fa-database text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Database Health --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Database Health</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">Good</p>
                    <p class="text-xs text-gray-500 mt-1">Last Backup: 2h ago</p>
                </div>
                <div class="w-12 h-12 bg-green-50 flex items-center justify-center rounded-lg">
                    <i class="fas fa-server text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Recent Security Alerts --}}
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Security Alerts</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
                    <p class="text-xs text-gray-500 mt-1">System Secure</p>
                </div>
                <div class="w-12 h-12 bg-red-50 flex items-center justify-center rounded-lg">
                    <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. OPERATIONAL SNAPSHOT --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Requisition Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Requisition Status</h3>
                <a href="#" class="text-xs text-blue-600 hover:underline">Manage Requisitions →</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <span class="text-sm text-gray-600">Pending Approval</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">5</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600">Approved Today</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full">12</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span class="text-sm text-gray-600">Rejected</span>
                    </div>
                    <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-bold rounded-full">1</span>
                </div>
            </div>
        </div>

        {{-- Purchase Order Statistics --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Purchase Orders</h3>
                <a href="#" class="text-xs text-blue-600 hover:underline">View Orders →</a>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-xs text-gray-500 uppercase">Draft</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">3</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-500 uppercase">Ordered</p>
                    <p class="text-xl font-bold text-blue-800 mt-1">8</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                    <p class="text-xs text-green-500 uppercase">Delivered</p>
                    <p class="text-xl font-bold text-green-800 mt-1">45</p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">Avg. Delivery Time: <span class="font-semibold text-gray-900">2.5 Days</span></p>
            </div>
        </div>
    </div>

    {{-- 4. ALERTS & CONFIG SHORTCUTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Low-Stock Alerts --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Low Stock Alerts</h3>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">3</span>
            </div>
            <div class="space-y-3">
                <!-- Static Item 1 -->
                <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                    <p class="text-sm font-bold text-gray-900">All-Purpose Flour (Sack)</p>
                    <div class="flex justify-between items-end mt-1">
                        <p class="text-xs text-gray-600">Current: <span class="font-bold text-red-600">2</span></p>
                        <p class="text-xs text-gray-500">Reorder Lvl: 10</p>
                    </div>
                </div>
                <!-- Static Item 2 -->
                <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                    <p class="text-sm font-bold text-gray-900">White Sugar (50kg)</p>
                    <div class="flex justify-between items-end mt-1">
                        <p class="text-xs text-gray-600">Current: <span class="font-bold text-red-600">5</span></p>
                        <p class="text-xs text-gray-500">Reorder Lvl: 15</p>
                    </div>
                </div>
                <!-- Static Item 3 -->
                <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                    <p class="text-sm font-bold text-gray-900">Vanilla Extract (Liter)</p>
                    <div class="flex justify-between items-end mt-1">
                        <p class="text-xs text-gray-600">Current: <span class="font-bold text-red-600">1</span></p>
                        <p class="text-xs text-gray-500">Reorder Lvl: 5</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Near-Expiry Alerts --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Expiring Soon</h3>
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold">2</span>
            </div>
            <div class="space-y-3">
                <!-- Static Item 1 -->
                <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded">
                    <p class="text-sm font-bold text-gray-900">Fresh Milk (Batch #882)</p>
                    <p class="text-xs text-amber-700 mt-1 font-medium">Expires: Tomorrow</p>
                </div>
                <!-- Static Item 2 -->
                <div class="p-3 border-l-4 border-amber-500 bg-amber-50 rounded">
                    <p class="text-sm font-bold text-gray-900">Heavy Cream (Batch #901)</p>
                    <p class="text-xs text-amber-700 mt-1 font-medium">Expires: Oct 28, 2023</p>
                </div>
                <div class="p-4 text-center">
                    <p class="text-xs text-gray-400 italic">No other items expiring within 7 days.</p>
                </div>
            </div>
        </div>

        {{-- Quick Admin Actions --}}
        <div class="lg:col-span-1 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Config Shortcuts</h3>
            <div class="space-y-2">
                <a href="#" class="flex items-center justify-between w-full px-4 py-3 bg-gray-900 text-white hover:bg-gray-800 transition rounded-lg shadow-sm group">
                    <span class="text-sm font-medium"><i class="fas fa-user-plus mr-2 text-gray-400 group-hover:text-white"></i>Create New User</span>
                    <i class="fas fa-chevron-right text-xs opacity-50"></i>
                </a>
                <a href="#" class="flex items-center justify-between w-full px-4 py-3 border border-gray-200 hover:bg-gray-50 transition rounded-lg text-gray-700">
                    <span class="text-sm font-medium"><i class="fas fa-box-open mr-2 text-gray-400"></i>Add New Item (SKU)</span>
                    <i class="fas fa-chevron-right text-xs opacity-50"></i>
                </a>
                <a href="#" class="flex items-center justify-between w-full px-4 py-3 border border-gray-200 hover:bg-gray-50 transition rounded-lg text-gray-700">
                    <span class="text-sm font-medium"><i class="fas fa-cloud-download-alt mr-2 text-gray-400"></i>Download Backup</span>
                    <i class="fas fa-chevron-right text-xs opacity-50"></i>
                </a>
                <a href="#" class="flex items-center justify-between w-full px-4 py-3 border border-gray-200 hover:bg-gray-50 transition rounded-lg text-gray-700">
                    <span class="text-sm font-medium"><i class="fas fa-file-contract mr-2 text-gray-400"></i>View Audit Logs</span>
                    <i class="fas fa-chevron-right text-xs opacity-50"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- 5. LOGS & SECURITY FEED --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Database Transaction Feed --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Recent Database Updates</h3>
                <a href="#" class="text-xs font-bold text-blue-600 uppercase tracking-wider">View Masterlist →</a>
            </div>
            <div class="space-y-0 divide-y divide-gray-100">
                <!-- Static Entry 1 -->
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-edit text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Price Updated: Cake Flour</p>
                            <p class="text-xs text-gray-500">New Price: ₱950.00 / Sack</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">10 mins ago</span>
                </div>
                <!-- Static Entry 2 -->
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fas fa-plus text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">New Item Added</p>
                            <p class="text-xs text-gray-500">"Pink Birthday Candles"</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">1 hr ago</span>
                </div>
                <!-- Static Entry 3 -->
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-trash text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Supplier Removed</p>
                            <p class="text-xs text-gray-500">ABC Packaging Corp.</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">2 hrs ago</span>
                </div>
            </div>
        </div>

        {{-- Security / Audit Logs --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">System Security Log</h3>
                <a href="#" class="text-xs font-bold text-gray-600 uppercase tracking-wider">Full Audit →</a>
            </div>
            <div class="space-y-4">
                <!-- Log 1 -->
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 font-medium">Manual Backup Completed</p>
                        <p class="text-xs text-gray-600 mt-0.5">Admin initiated a full database backup.</p>
                        <p class="text-xs text-gray-400 mt-0.5">Just now</p>
                    </div>
                </div>
                <!-- Log 2 -->
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 font-medium">User Role Modified</p>
                        <p class="text-xs text-gray-600 mt-0.5">User "J. Doe" promoted to Supervisor.</p>
                        <p class="text-xs text-gray-400 mt-0.5">3 hours ago</p>
                    </div>
                </div>
                <!-- Log 3 -->
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 font-medium">Failed Login Attempt</p>
                        <p class="text-xs text-gray-600 mt-0.5">3 failed attempts from IP 192.168.1.45</p>
                        <p class="text-xs text-gray-400 mt-0.5">Yesterday at 4:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection