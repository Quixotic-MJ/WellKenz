@extends('Inventory.layout.app')

@section('title', 'Stock In - WellKenz ERP')

@section('breadcrumb', 'Stock In')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Stock In Management</h1>
                <p class="text-text-muted mt-2">Record newly received items from suppliers and link them to purchase orders.</p>
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
                    <i class="fas fa-truck-loading text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Today's Receipts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Completed This Week</p>
            <p class="text-3xl font-bold text-text-dark mt-2">24</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-500 flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Receipts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Overdue Deliveries</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Stock In Records Table -->
        <div class="lg:col-span-3 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Stock In Records</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Suppliers</option>
                        <option>Fresh Dairy Co.</option>
                        <option>Baking Supplies Ltd</option>
                        <option>Premium Ingredients Inc</option>
                        <option>Packaging Solutions</option>
                    </select>
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Status</option>
                        <option>Received</option>
                        <option>Partially Received</option>
                        <option>Pending</option>
                        <option>Quality Check</option>
                    </select>
                    <input type="text" placeholder="Search PO or item..." class="px-3 py-2 border border-border-soft rounded text-sm w-48">
                </div>
            </div>
            
            <!-- Stock In Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Purchase Order & Items</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Supplier</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Received Date</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Quantity Received</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <!-- Stock In Record - Received -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0158</p>
                                    <p class="text-xs text-text-muted">All Purpose Flour (25kg) × 50 bags</p>
                                    <p class="text-xs text-text-muted">Chocolate Chips (10kg) × 20 bags</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Baking Supplies Ltd</p>
                                    <p class="text-xs text-text-muted">Contact: John Smith</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Today, 08:30 AM</p>
                                    <p class="text-xs text-green-600 font-bold">✓ On time</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Full Delivery</p>
                                    <p class="text-xs text-green-600 font-bold">50/50 bags flour</p>
                                    <p class="text-xs text-green-600 font-bold">20/20 bags chips</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    FULLY RECEIVED
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <!-- Stock In Record - Partially Received -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0162</p>
                                    <p class="text-xs text-text-muted">Butter Unsalted (1kg) × 80 blocks</p>
                                    <p class="text-xs text-text-muted">Heavy Cream (1L) × 40 bottles</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Fresh Dairy Co.</p>
                                    <p class="text-xs text-text-muted">Contact: Maria Rodriguez</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Today, 10:15 AM</p>
                                    <p class="text-xs text-yellow-600 font-bold">⚠ Partial delivery</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Partial Delivery</p>
                                    <p class="text-xs text-yellow-600 font-bold">50/80 blocks butter</p>
                                    <p class="text-xs text-yellow-600 font-bold">40/40 bottles cream</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-clock mr-1"></i>
                                    PARTIAL RECEIPT
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-edit mr-1"></i>
                                    Update Receipt
                                </button>
                            </td>
                        </tr>

                        <!-- Stock In Record - Quality Check -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0159</p>
                                    <p class="text-xs text-text-muted">Vanilla Extract (1L) × 15 bottles</p>
                                    <p class="text-xs text-text-muted">Almond Flour (5kg) × 10 bags</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Premium Ingredients Inc</p>
                                    <p class="text-xs text-text-muted">Contact: David Chen</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Yesterday, 02:45 PM</p>
                                    <p class="text-xs text-blue-600 font-bold">🔍 Quality inspection</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Full Delivery</p>
                                    <p class="text-xs text-text-muted">15/15 bottles extract</p>
                                    <p class="text-xs text-text-muted">10/10 bags flour</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-search mr-1"></i>
                                    QUALITY CHECK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-clipboard-check mr-1"></i>
                                    Approve Quality
                                </button>
                            </td>
                        </tr>

                        <!-- Stock In Record - Pending -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0165</p>
                                    <p class="text-xs text-text-muted">Cake Boxes × 200 units</p>
                                    <p class="text-xs text-text-muted">Cookie Bags × 500 units</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Packaging Solutions</p>
                                    <p class="text-xs text-text-muted">Contact: Sarah Wilson</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Expected: Tomorrow</p>
                                    <p class="text-xs text-text-muted">Scheduled delivery</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Not Received</p>
                                    <p class="text-xs text-text-muted">Awaiting delivery</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-hourglass-half mr-1"></i>
                                    PENDING
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-truck mr-1"></i>
                                    Track Shipment
                                </button>
                            </td>
                        </tr>

                        <!-- Stock In Record - Overdue -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0155</p>
                                    <p class="text-xs text-text-muted">Baking Powder (500g) × 30 cans</p>
                                    <p class="text-xs text-text-muted">Cocoa Powder (2kg) × 25 bags</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Baking Supplies Ltd</p>
                                    <p class="text-xs text-text-muted">Contact: John Smith</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Overdue: 2 days</p>
                                    <p class="text-xs text-red-600 font-bold">🚨 Past due date</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Not Received</p>
                                    <p class="text-xs text-red-600 font-bold">Delivery delayed</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    OVERDUE
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-phone mr-1"></i>
                                    Contact Supplier
                                </button>
                            </td>
                        </tr>

                        <!-- Stock In Record - Received -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">PO-2024-0160</p>
                                    <p class="text-xs text-text-muted">Eggs (Large) × 20 trays</p>
                                    <p class="text-xs text-text-muted">Milk Whole (4L) × 15 bags</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Fresh Dairy Co.</p>
                                    <p class="text-xs text-text-muted">Contact: Maria Rodriguez</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Nov 28, 2024</p>
                                    <p class="text-xs text-green-600 font-bold">✓ Completed</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Full Delivery</p>
                                    <p class="text-xs text-green-600 font-bold">20/20 trays eggs</p>
                                    <p class="text-xs text-green-600 font-bold">15/15 bags milk</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center w-32">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    COMPLETED
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-file-invoice mr-1"></i>
                                    View Invoice
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-text-muted">Showing 1-6 of 42 stock in records</p>
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
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Receipt Status</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">All Status</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Fully Received</span>
                        <span class="ml-auto text-xs text-text-muted">18</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Partial Receipt</span>
                        <span class="ml-auto text-xs text-text-muted">5</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Quality Check</span>
                        <span class="ml-auto text-xs text-text-muted">3</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Pending</span>
                        <span class="ml-auto text-xs text-text-muted">8</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Overdue</span>
                        <span class="ml-auto text-xs text-text-muted">3</span>
                    </label>
                </div>
            </div>

            <!-- Supplier Filter -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Suppliers</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">All Suppliers</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Baking Supplies Ltd</span>
                        <span class="ml-auto text-xs text-text-muted">15</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Fresh Dairy Co.</span>
                        <span class="ml-auto text-xs text-text-muted">12</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Premium Ingredients</span>
                        <span class="ml-auto text-xs text-text-muted">8</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Packaging Solutions</span>
                        <span class="ml-auto text-xs text-text-muted">7</span>
                    </label>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Quick Actions</h4>
                <div class="space-y-2">
                    <button onclick="openStockInModal()" class="w-full p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold text-sm rounded flex items-center justify-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        New Stock In
                    </button>
                    <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark rounded flex items-center justify-center">
                        <i class="fas fa-download mr-2 text-chocolate"></i>
                        Export Receipts
                    </button>
                    <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark rounded flex items-center justify-center">
                        <i class="fas fa-print mr-2 text-chocolate"></i>
                        Print Reports
                    </button>
                    <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark rounded flex items-center justify-center">
                        <i class="fas fa-clipboard-list mr-2 text-chocolate"></i>
                        Pending PO List
                    </button>
                </div>
            </div>

            <!-- Receipt Summary -->
            <div class="pt-6 border-t border-border-soft">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">This Week's Receipts</h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Completed</span>
                            <span class="text-xs font-bold text-text-dark">67%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 67%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">In Progress</span>
                            <span class="text-xs font-bold text-text-dark">21%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 21%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Pending</span>
                            <span class="text-xs font-bold text-text-dark">12%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 12%"></div>
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
                        <span class="text-text-dark">PO-2024-0158 received</span>
                        <span class="text-text-muted ml-auto">2h ago</span>
                    </div>
                    <div class="flex items-center text-xs">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">PO-2024-0162 partial</span>
                        <span class="text-text-muted ml-auto">4h ago</span>
                    </div>
                    <div class="flex items-center text-xs">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">PO-2024-0159 quality check</span>
                        <span class="text-text-muted ml-auto">1d ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Value Received -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Total Value Received</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">$8,450</p>
                    <p class="text-xs text-text-muted mt-1">this week</p>
                </div>
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- On-Time Delivery Rate -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">On-Time Delivery Rate</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">89%</p>
                    <p class="text-xs text-text-muted mt-1">of all deliveries</p>
                </div>
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Processing Time -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Avg. Processing Time</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">2.1</p>
                    <p class="text-xs text-text-muted mt-1">hours per receipt</p>
                </div>
                <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock In Modal -->
<div id="stockInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-2xl font-bold text-text-dark">Record New Stock In</h3>
            <button onclick="closeStockInModal()" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form class="space-y-6">
            <!-- Purchase Order Selection -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Select Purchase Order *</label>
                <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                    <option value="">Select Purchase Order...</option>
                    <option value="po-0165">PO-2024-0165 - Packaging Solutions (Pending)</option>
                    <option value="po-0163">PO-2024-0163 - Fresh Dairy Co. (Expected Today)</option>
                    <option value="po-0161">PO-2024-0161 - Baking Supplies Ltd (Tomorrow)</option>
                    <option value="po-0157">PO-2024-0157 - Premium Ingredients (Overdue)</option>
                </select>
            </div>

            <!-- Supplier & Delivery Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Supplier</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm bg-gray-50" 
                           value="Packaging Solutions" readonly>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Delivery Date & Time *</label>
                    <input type="datetime-local" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           value="{{ date('Y-m-d\TH:i') }}">
                </div>
            </div>

            <!-- Received Items -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Received Items *</label>
                <div class="border border-border-soft rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-bold text-text-muted uppercase">Item</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-text-muted uppercase">Ordered Qty</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-text-muted uppercase">Received Qty</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-text-muted uppercase">Unit</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-text-muted uppercase">Batch No.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft">
                            <tr>
                                <td class="py-3 px-4">
                                    <p class="text-sm font-bold text-text-dark">Cake Boxes</p>
                                    <p class="text-xs text-text-muted">Large white with window</p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-sm text-text-dark">200</p>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" class="w-20 px-2 py-1 border border-border-soft rounded text-sm" 
                                           value="200" min="0" max="200">
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-sm text-text-dark">Units</p>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text" class="w-24 px-2 py-1 border border-border-soft rounded text-sm" 
                                           placeholder="Batch No.">
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4">
                                    <p class="text-sm font-bold text-text-dark">Cookie Bags</p>
                                    <p class="text-xs text-text-muted">Medium size, clear</p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-sm text-text-dark">500</p>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="number" class="w-20 px-2 py-1 border border-border-soft rounded text-sm" 
                                           value="500" min="0" max="500">
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-sm text-text-dark">Units</p>
                                </td>
                                <td class="py-3 px-4">
                                    <input type="text" class="w-24 px-2 py-1 border border-border-soft rounded text-sm" 
                                           placeholder="Batch No.">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Delivery Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Delivery Reference</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., Delivery note number">
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Received By *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           value="" readonly>
                </div>
            </div>

            <!-- Quality Check -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Quality Check</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">All items received in good condition</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Packaging intact</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Expiry dates verified</span>
                    </label>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Receiving Notes</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="3" placeholder="Any notes about the delivery, quality issues, or special instructions..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-4 pt-4 border-t border-border-soft">
                <button type="button" onclick="closeStockInModal()" class="flex-1 p-3 border-2 border-border-soft text-text-dark hover:bg-gray-50 transition text-center font-semibold rounded">
                    Cancel
                </button>
                <button type="submit" class="flex-1 p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirm Receipt
                </button>
            </div>
        </form>
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

<script>
    function openStockInModal() {
        document.getElementById('stockInModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeStockInModal() {
        document.getElementById('stockInModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('stockInModal').addEventListener('click', function(e) {
        if (e.target.id === 'stockInModal') {
            closeStockInModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockInModal();
        }
    });

    // Form submission
    document.querySelector('#stockInModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically handle the form submission with AJAX
        alert('Stock in recorded successfully!');
        closeStockInModal();
        // Reset form
        this.reset();
    });
</script>
@endsection