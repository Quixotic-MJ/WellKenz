@extends('Inventory.layout.app')

@section('title', 'Inventory Overview - WellKenz ERP')

@section('breadcrumb', 'Inventory Overview')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Inventory Overview</h1>
                <p class="text-text-muted mt-2">Complete view of all inventory items, current quantities, and reorder levels.</p>
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
                    <i class="fas fa-boxes text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">142</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-500 flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">In Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">118</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">16</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-times-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Out of Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Inventory Items Table -->
        <div class="lg:col-span-3 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">All Inventory Items</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Categories</option>
                        <option>Flours & Grains</option>
                        <option>Flavorings & Extracts</option>
                        <option>Dairy Products</option>
                        <option>Additives & Preservatives</option>
                        <option>Packaging Materials</option>
                    </select>
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Status</option>
                        <option>In Stock</option>
                        <option>Low Stock</option>
                        <option>Out of Stock</option>
                        <option>Critical</option>
                    </select>
                    <input type="text" placeholder="Search items..." class="px-3 py-2 border border-border-soft rounded text-sm w-48">
                </div>
            </div>
            
            <!-- Inventory Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Item Details</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Category</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Current Stock</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Reorder Level</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Unit</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <!-- Item - Good Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">All Purpose Flour</p>
                                    <p class="text-xs text-text-muted">STK-FLOUR-001 • Warehouse A</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded">Flours & Grains</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">120 units</p>
                                    <p class="text-xs text-green-600 font-bold">✓ Good stock level</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">20 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">25kg bags</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    IN STOCK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-edit mr-1"></i>
                                    Adjust
                                </button>
                            </td>
                        </tr>

                        <!-- Item - Low Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Vanilla Extract - Pure</p>
                                    <p class="text-xs text-text-muted">STK-VAN-008 • Warehouse B</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded">Flavorings</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">8 units</p>
                                    <p class="text-xs text-yellow-600 font-bold">⚠ Below reorder level</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">10 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">1L bottles</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    LOW STOCK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Reorder
                                </button>
                            </td>
                        </tr>

                        <!-- Item - Critical Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Almond Flour - Premium</p>
                                    <p class="text-xs text-text-muted">STK-ALM-012 • Warehouse A</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded">Flours & Grains</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">2 units</p>
                                    <p class="text-xs text-red-600 font-bold">🚨 Critical level</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">15 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">5kg bags</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    CRITICAL
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Urgent Order
                                </button>
                            </td>
                        </tr>

                        <!-- Item - Good Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Butter - Unsalted</p>
                                    <p class="text-xs text-text-muted">STK-BUT-003 • Cold Storage</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">Dairy</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">85 units</p>
                                    <p class="text-xs text-green-600 font-bold">✓ Good stock level</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">25 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">1kg blocks</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    IN STOCK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-edit mr-1"></i>
                                    Adjust
                                </button>
                            </td>
                        </tr>

                        <!-- Item - Out of Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Cocoa Powder - Dutch</p>
                                    <p class="text-xs text-text-muted">STK-COC-007 • Warehouse B</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-bold rounded">Flavorings</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">0 units</p>
                                    <p class="text-xs text-red-600 font-bold">✗ Out of stock</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">12 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">2kg bags</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    OUT OF STOCK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Reorder
                                </button>
                            </td>
                        </tr>

                        <!-- Item - Low Stock -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">Baking Powder</p>
                                    <p class="text-xs text-text-muted">STK-BKP-004 • Warehouse A</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded">Additives</span>
                            </td>
                            <td class="py-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-text-dark">5 units</p>
                                    <p class="text-xs text-yellow-600 font-bold">⚠ Below reorder level</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">8 units</p>
                            </td>
                            <td class="py-4">
                                <p class="text-sm text-text-dark">500g cans</p>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded flex items-center w-24">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    LOW STOCK
                                </span>
                            </td>
                            <td class="py-4">
                                <button class="text-xs text-caramel hover:text-caramel-dark font-bold flex items-center">
                                    <i class="fas fa-shopping-cart mr-1"></i>
                                    Reorder
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-text-muted">Showing 1-6 of 142 items</p>
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
            
            <!-- Stock Status Filter -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Stock Status</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">All Items</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">In Stock</span>
                        <span class="ml-auto text-xs text-text-muted">118</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Low Stock</span>
                        <span class="ml-auto text-xs text-text-muted">16</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">Out of Stock</span>
                        <span class="ml-auto text-xs text-text-muted">8</span>
                    </label>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Categories</h4>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                        <span class="ml-2 text-sm text-text-dark">All Categories</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Flours & Grains</span>
                        <span class="ml-auto text-xs text-text-muted">28</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Flavorings</span>
                        <span class="ml-auto text-xs text-text-muted">42</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Dairy Products</span>
                        <span class="ml-auto text-xs text-text-muted">18</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Additives</span>
                        <span class="ml-auto text-xs text-text-muted">24</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                        <span class="ml-2 text-sm text-text-dark">Packaging</span>
                        <span class="ml-auto text-xs text-text-muted">30</span>
                    </label>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-6">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Quick Actions</h4>
                <div class="space-y-2">
                    <button onclick="openAddItemModal()" class="w-full p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold text-sm rounded flex items-center justify-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Add New Item
                    </button>
                    <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark rounded flex items-center justify-center">
                        <i class="fas fa-download mr-2 text-chocolate"></i>
                        Export Inventory
                    </button>
                    <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark rounded flex items-center justify-center">
                        <i class="fas fa-print mr-2 text-chocolate"></i>
                        Print Report
                    </button>
                </div>
            </div>

            <!-- Stock Summary -->
            <div class="pt-6 border-t border-border-soft">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Stock Summary</h4>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">In Stock</span>
                            <span class="text-xs font-bold text-text-dark">83%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 83%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Low Stock</span>
                            <span class="text-xs font-bold text-text-dark">11%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 11%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-xs text-text-muted">Out of Stock</span>
                            <span class="text-xs font-bold text-text-dark">6%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: 6%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reorder -->
            <div class="pt-6 border-t border-border-soft">
                <h4 class="font-display text-sm font-bold text-text-dark mb-3">Quick Reorder</h4>
                <div class="space-y-2">
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-red-50 rounded transition flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-shopping-cart text-red-600 mr-2"></i>
                            Almond Flour
                        </span>
                        <span class="text-xs text-red-600 font-bold">2 units</span>
                    </button>
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-yellow-50 rounded transition flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-shopping-cart text-yellow-600 mr-2"></i>
                            Vanilla Extract
                        </span>
                        <span class="text-xs text-yellow-600 font-bold">8 units</span>
                    </button>
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-yellow-50 rounded transition flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-shopping-cart text-yellow-600 mr-2"></i>
                            Baking Powder
                        </span>
                        <span class="text-xs text-yellow-600 font-bold">5 units</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Inventory Value -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Total Inventory Value</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">$24,850</p>
                    <p class="text-xs text-text-muted mt-1">across all items</p>
                </div>
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Items Needing Reorder -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Items Needing Reorder</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">24</p>
                    <p class="text-xs text-text-muted mt-1">below reorder level</p>
                </div>
                <div class="w-20 h-20 bg-yellow-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Stock Health Score -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Health Score</h4>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold text-text-dark">88%</p>
                    <p class="text-xs text-text-muted mt-1">optimal stock levels</p>
                </div>
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-heart text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-2xl font-bold text-text-dark">Add New Inventory Item</h3>
            <button onclick="closeAddItemModal()" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form class="space-y-6">
            <!-- Item Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Item Name *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., All Purpose Flour" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">SKU/Code *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., STK-FLOUR-001" required>
                </div>
            </div>

            <!-- Category & Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Category *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="">Select category...</option>
                        <option value="flours">Flours & Grains</option>
                        <option value="flavorings">Flavorings & Extracts</option>
                        <option value="dairy">Dairy Products</option>
                        <option value="additives">Additives & Preservatives</option>
                        <option value="packaging">Packaging Materials</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Storage Location *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="warehouse_a">Warehouse A</option>
                        <option value="warehouse_b">Warehouse B</option>
                        <option value="cold_storage">Cold Storage</option>
                        <option value="production">Production Area</option>
                        <option value="other">Other Location</option>
                    </select>
                </div>
            </div>

            <!-- Stock Levels -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Current Stock *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="0" min="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Reorder Level *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="10" min="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Maximum Stock</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="100" min="0">
                </div>
            </div>

            <!-- Unit Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Unit of Measure *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="l">Liters (L)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="units">Units</option>
                        <option value="bags">Bags</option>
                        <option value="boxes">Boxes</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Unit Cost</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="0.00" min="0" step="0.01">
                </div>
            </div>

            <!-- Supplier Information -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Supplier</label>
                <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                       placeholder="Primary supplier name">
            </div>

            <!-- Additional Notes -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Notes</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="3" placeholder="Any special storage instructions, shelf life information, or additional notes..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-4 pt-4 border-t border-border-soft">
                <button type="button" onclick="closeAddItemModal()" class="flex-1 p-3 border-2 border-border-soft text-text-dark hover:bg-gray-50 transition text-center font-semibold rounded">
                    Cancel
                </button>
                <button type="submit" class="flex-1 p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded">
                    <i class="fas fa-save mr-2"></i>
                    Save Item
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
    function openAddItemModal() {
        document.getElementById('addItemModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAddItemModal() {
        document.getElementById('addItemModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('addItemModal').addEventListener('click', function(e) {
        if (e.target.id === 'addItemModal') {
            closeAddItemModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddItemModal();
        }
    });

    // Form submission
    document.querySelector('#addItemModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically handle the form submission with AJAX
        alert('Inventory item added successfully!');
        closeAddItemModal();
        // Reset form
        this.reset();
    });
</script>
@endsection