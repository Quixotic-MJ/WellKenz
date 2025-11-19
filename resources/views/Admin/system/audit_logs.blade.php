@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & EXPORT --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Audit Logs</h1>
            <p class="text-sm text-gray-500 mt-1">A secured, read-only record of all critical system activities and security events.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400 mr-2"><i class="fas fa-lock mr-1"></i> Immutable Record</span>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-download mr-2"></i> Download CSV
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Report
            </button>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search Description</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                    <input type="text" class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs" placeholder="e.g. 'Sugar' or 'Spillage'">
                </div>
            </div>

            <!-- Actor Filter -->
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">User / Actor</label>
                <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
                    <option value="">All Users</option>
                    <option value="admin">Admin User</option>
                    <option value="supervisor">Supervisor Mike</option>
                    <option value="system">SYSTEM (Automated)</option>
                </select>
            </div>

            <!-- Module Filter -->
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Module</label>
                <select class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
                    <option value="">All Modules</option>
                    <option value="auth">Authentication / Security</option>
                    <option value="inventory">Inventory / Items</option>
                    <option value="finance">Finance / Pricing</option>
                    <option value="users">User Management</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Date Range</label>
                <input type="date" value="2023-10-20" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs">
            </div>
        </div>
    </div>

    {{-- 3. AUDIT LOG FEED --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event / Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">

                    {{-- LOG 1: THE SMOKING GUN (Inventory Manipulation) --}}
                    <tr class="bg-red-50 hover:bg-red-100 transition-colors border-l-4 border-red-500">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-bold text-gray-900">Oct 20, 2023 (Fri)</div>
                            <div class="text-xs">14:00:23 PM</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">SM</div>
                                <div class="ml-3">
                                    <div class="text-sm font-bold text-gray-900">Supervisor Mike</div>
                                    <div class="text-xs text-gray-500">Supervisor</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-200 text-red-800 mb-1 border border-red-300">
                                <i class="fas fa-exclamation-circle mr-1"></i> STOCK ADJUSTMENT
                            </span>
                            <div class="text-sm text-gray-900">Manual Stock Deduction: <span class="font-bold underline">White Sugar</span></div>
                            <div class="text-xs text-gray-600 mt-1">
                                <strong>Adjustment:</strong> <span class="text-red-600 font-bold text-sm">-50.00 kg</span>
                            </div>
                            <div class="text-xs text-gray-500 italic">Reason: "Spillage / Damaged in storage"</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-warehouse mr-2 text-gray-400"></i> Inventory
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-chocolate hover:text-chocolate-dark transition font-bold text-xs border border-chocolate/30 px-2 py-1 rounded">
                                <i class="fas fa-file-pdf mr-1"></i> Export Proof
                            </button>
                        </td>
                    </tr>

                    {{-- LOG 2: UPDATE (Price Change) --}}
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-medium text-gray-900">Oct 24, 2023</div>
                            <div class="text-xs">08:30:00 AM</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs">AD</div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Admin User</div>
                                    <div class="text-xs text-gray-500">Administrator</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mb-1">
                                UPDATE PRICE
                            </span>
                            <div class="text-sm text-gray-900">Updated Cost for <span class="font-semibold">Cake Flour</span></div>
                            <div class="text-xs text-gray-500">Old: ₱900.00 → New: ₱950.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-coins mr-2 text-gray-400"></i> Finance
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 hover:text-chocolate transition">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- LOG 3: SECURITY (Failed Login) --}}
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-medium text-gray-900">Oct 23, 2023</div>
                            <div class="text-xs">11:55:00 PM</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-xs">?</div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">Unknown / Guest</div>
                                    <div class="text-xs text-gray-500">IP: 112.201.44.12</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-800 text-white mb-1">
                                SECURITY ALERT
                            </span>
                            <div class="text-sm text-gray-900">Failed Login Attempt (3x)</div>
                            <div class="text-xs text-red-500 font-bold">Target: 'admin_backup'</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-shield-alt mr-2 text-gray-400"></i> Auth
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 hover:text-chocolate transition">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- LOG 4: SYSTEM (Backup) --}}
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="font-medium text-gray-900">Oct 23, 2023</div>
                            <div class="text-xs">03:00:00 AM</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">SYSTEM</div>
                                    <div class="text-xs text-gray-500">Automated Task</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                MAINTENANCE
                            </span>
                            <div class="text-sm text-gray-900">Daily Database Backup Completed</div>
                            <div class="text-xs text-gray-500">Size: 45MB • Duration: 2s</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-server mr-2 text-gray-400"></i> System
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-gray-400 hover:text-chocolate transition">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- Simple Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">2,840</span> logs</p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</button>
                    <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>

</div>
@endsection