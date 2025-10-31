@extends('Employee.layout.app')

@section('title', 'My Requisitions - WellKenz ERP')

@section('breadcrumb', 'My Requisitions')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">My Baking Requisitions</h1>
                <p class="text-text-muted mt-2">Track all your submitted ingredient and supply requests.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-text-dark font-semibold">{{ date('F j, Y') }}</p>
                <p class="text-xs text-text-muted mt-1">{{ date('l') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">24</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-text-dark mt-2">16</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-times-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Rejected</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Requisitions List -->
        <div class="lg:col-span-3 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">My Submitted Requisitions</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Approved</option>
                        <option>Rejected</option>
                        <option>Delivered</option>
                    </select>
                    <input type="text" placeholder="Search requisitions..." class="px-3 py-2 border border-border-soft rounded text-sm">
                </div>
            </div>
            
            <!-- Requisitions Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Requisition Details</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Items</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Submitted</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Needed By</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <!-- Pending Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Chocolate Chip Cookies - Bulk</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0245 • Weekend Special</p>
                                    <div class="flex items-center text-xs text-text-muted">
                                        <i class="fas fa-user mr-1"></i>
                                        <span>Current Approver: Sarah Johnson</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Flour (25kg), Chocolate Chips (15kg)</p>
                                    <p class="text-xs text-text-muted">Butter (10kg), Vanilla Extract (2L)</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Today, 07:30 AM</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Tomorrow</p>
                                    <div class="flex items-center text-xs text-red-600 font-bold">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <span>URGENT</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        PENDING
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <!-- Approved Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Birthday Cake Supplies</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0244 • Custom Order #789</p>
                                    <div class="flex items-center text-xs text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <span>Approved by: Michael Chen</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Cake Flour (5kg), Food Coloring</p>
                                    <p class="text-xs text-text-muted">Fondant (3kg), Sprinkles (500g)</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Yesterday</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Today</p>
                                    <div class="flex items-center text-xs text-green-600 font-bold">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <span>ON TIME</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-check mr-1"></i>
                                        APPROVED
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-truck mr-1"></i>
                                    Track Delivery
                                </button>
                            </td>
                        </tr>

                        <!-- Approved Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Bread Production - Daily</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0243 • Regular Production</p>
                                    <div class="flex items-center text-xs text-green-600">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        <span>Auto-approved (Recurring)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Bread Flour (50kg), Yeast (2kg)</p>
                                    <p class="text-xs text-text-muted">Salt (5kg), Olive Oil (5L)</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 25, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 26, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-check mr-1"></i>
                                        APPROVED
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-file-invoice mr-1"></i>
                                    View Order
                                </button>
                            </td>
                        </tr>

                        <!-- Rejected Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Premium Almond Flour</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0242 • Recipe Development</p>
                                    <div class="flex items-center text-xs text-red-600">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        <span>Rejected by: David Wilson</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Premium Almond Flour (10kg)</p>
                                    <p class="text-xs text-text-muted">Specialty ingredient request</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 24, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Dec 1, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-times mr-1"></i>
                                        REJECTED
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    View Reason
                                </button>
                            </td>
                        </tr>

                        <!-- Pending Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Specialty Pastry Ingredients</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0241 • Seasonal Menu</p>
                                    <div class="flex items-center text-xs text-text-muted">
                                        <i class="fas fa-user mr-1"></i>
                                        <span>Current Approver: Lisa Rodriguez</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Almond Flour (10kg), Chocolate</p>
                                    <p class="text-xs text-text-muted">Fresh Berries (6kg), Specialty Items</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 24, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Dec 1, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-search mr-1"></i>
                                        UNDER REVIEW
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Check Status
                                </button>
                            </td>
                        </tr>

                        <!-- Delivered Requisition -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Packaging Materials</p>
                                    <p class="text-xs text-text-muted">REQ-BAKE-0240 • Monthly Restock</p>
                                    <div class="flex items-center text-xs text-green-600">
                                        <i class="fas fa-truck mr-1"></i>
                                        <span>Delivered: Today, 08:45 AM</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Cake Boxes (200), Cookie Bags</p>
                                    <p class="text-xs text-text-muted">Ribbon (50m), Labels (1000)</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 22, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm text-text-dark font-medium">Nov 25, 2024</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded flex items-center">
                                        <i class="fas fa-box mr-1"></i>
                                        DELIVERED
                                    </span>
                                </div>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Acknowledge
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-text-muted">Showing 1-6 of 24 requisitions</p>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 border border-border-soft rounded text-sm text-text-muted hover:bg-cream-bg flex items-center">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-caramel text-white rounded text-sm">
                        1
                    </button>
                    <button class="px-3 py-1 border border-border-soft rounded text-sm text-text-muted hover:bg-cream-bg">
                        2
                    </button>
                    <button class="px-3 py-1 border border-border-soft rounded text-sm text-text-muted hover:bg-cream-bg">
                        3
                    </button>
                    <button class="px-3 py-1 border border-border-soft rounded text-sm text-text-muted hover:bg-cream-bg flex items-center">
                        Next
                        <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters & Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Filters & Actions</h3>
            
            <!-- Status Filter -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Filter by Status</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">All Status</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Pending</span>
                        <span class="ml-auto text-xs text-text-muted">5</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Approved</span>
                        <span class="ml-auto text-xs text-text-muted">16</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Rejected</span>
                        <span class="ml-auto text-xs text-text-muted">3</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Delivered</span>
                        <span class="ml-auto text-xs text-text-muted">8</span>
                    </label>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Filter by Date</h4>
                <div class="space-y-2">
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>This Week</option>
                        <option>This Month</option>
                        <option>Last 30 Days</option>
                    </select>
                </div>
            </div>

            <!-- Status Summary -->
            <div class="pt-6 border-t border-border-soft">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Status Summary</h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Approved</span>
                            <span class="text-xs font-bold text-text-dark">67%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 67%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Pending</span>
                            <span class="text-xs font-bold text-text-dark">21%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 21%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Rejected</span>
                            <span class="text-xs font-bold text-text-dark">12%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: 12%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="pt-6 border-t border-border-soft">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Recent Activity</h4>
                <div class="space-y-3">
                    <div class="flex items-center text-xs">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">REQ-BAKE-0244 approved</span>
                        <span class="text-text-muted ml-auto">2h ago</span>
                    </div>
                    <div class="flex items-center text-xs">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">REQ-BAKE-0245 submitted</span>
                        <span class="text-text-muted ml-auto">4h ago</span>
                    </div>
                    <div class="flex items-center text-xs">
                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">REQ-BAKE-0242 rejected</span>
                        <span class="text-text-muted ml-auto">1d ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Approval Rate -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Approval Rate</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">79%</p>
                    <p class="text-xs text-text-muted mt-1">16 out of 21 decisions</p>
                </div>
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Approval Time -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Avg. Approval Time</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">1.5</p>
                    <p class="text-xs text-text-muted mt-1">business days</p>
                </div>
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Value -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Total Value</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">$8,450</p>
                    <p class="text-xs text-text-muted mt-1">across all requisitions</p>
                </div>
                <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }

    .cream-bg {
        background-color: #faf7f3;
    }
    
    .text-text-dark {
        color: #1a1410;
    }
    
    .text-text-muted {
        color: #8b7355;
    }
    
    .bg-chocolate {
        background-color: #3d2817;
    }
    
    .hover\:bg-chocolate-dark:hover {
        background-color: #2a1a0f;
    }
    
    .bg-caramel {
        background-color: #c48d3f;
    }
    
    .hover\:bg-caramel-dark:hover {
        background-color: #a67332;
    }
    
    .border-border-soft {
        border-color: #e8dfd4;
    }
</style>
@endsection