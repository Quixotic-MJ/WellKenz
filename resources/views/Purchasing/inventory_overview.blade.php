@extends('Purchasing.layout.app')

@section('title', 'Inventory Management - WellKenz ERP')

@section('breadcrumb', 'Inventory Management')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Inventory Management</h1>
                <p class="text-text-muted mt-2">Track all inventory items, quantities, and reorder levels.</p>
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
            <p class="text-3xl font-bold text-text-dark mt-2">2,347</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-warehouse text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</p>
            <p class="text-3xl font-bold text-text-dark mt-2">$458K</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Monthly Turnover</p>
            <p class="text-3xl font-bold text-text-dark mt-2">18.5%</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Inventory Items -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Inventory Items</h3>
                <div class="flex space-x-2">
                    <input type="text" placeholder="Search items..." class="px-3 py-2 border border-border-soft rounded text-sm">
                    <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Item</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">SKU</th>
                            <th class="text-left py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Category</th>
                            <th class="text-right py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Current Stock</th>
                            <th class="text-right py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Reorder Level</th>
                            <th class="text-right py-3 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                                <p class="text-xs text-text-muted">25kg bags</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">FLR-AP-25KG</td>
                            <td class="py-3 text-sm text-text-dark">Raw Materials</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">15</p>
                                <p class="text-xs text-red-600">kg</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">50</p>
                                <p class="text-xs text-text-muted">kg</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">LOW STOCK</span>
                            </td>
                        </tr>

                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                                <p class="text-xs text-text-muted">Premium dark, 5kg bags</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">CHC-DK-5KG</td>
                            <td class="py-3 text-sm text-text-dark">Ingredients</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">2</p>
                                <p class="text-xs text-red-600">kg</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">10</p>
                                <p class="text-xs text-text-muted">kg</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">CRITICAL</span>
                            </td>
                        </tr>

                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">Cake Boxes (Large)</p>
                                <p class="text-xs text-text-muted">White corrugated</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">BOX-CAKE-LG</td>
                            <td class="py-3 text-sm text-text-dark">Packaging</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">25</p>
                                <p class="text-xs text-orange-600">units</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">100</p>
                                <p class="text-xs text-text-muted">units</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded">BELOW MIN</span>
                            </td>
                        </tr>

                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">Vanilla Extract</p>
                                <p class="text-xs text-text-muted">Pure, 1L bottles</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">VAN-PURE-1L</td>
                            <td class="py-3 text-sm text-text-dark">Ingredients</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">45</p>
                                <p class="text-xs text-green-600">bottles</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">20</p>
                                <p class="text-xs text-text-muted">bottles</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">IN STOCK</span>
                            </td>
                        </tr>

                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">Butter Unsalted</p>
                                <p class="text-xs text-text-muted">Premium, 500g blocks</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">BTR-UNS-500G</td>
                            <td class="py-3 text-sm text-text-dark">Dairy</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">78</p>
                                <p class="text-xs text-green-600">blocks</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">30</p>
                                <p class="text-xs text-text-muted">blocks</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded">IN STOCK</span>
                            </td>
                        </tr>

                        <!-- Item Row -->
                        <tr class="hover:bg-cream-bg">
                            <td class="py-3">
                                <p class="text-sm font-bold text-text-dark">Baking Powder</p>
                                <p class="text-xs text-text-muted">Food grade, 2kg cans</p>
                            </td>
                            <td class="py-3 text-sm text-text-dark">BAK-PWD-2KG</td>
                            <td class="py-3 text-sm text-text-dark">Ingredients</td>
                            <td class="py-3 text-right">
                                <p class="text-sm font-bold text-text-dark">8</p>
                                <p class="text-xs text-orange-600">cans</p>
                            </td>
                            <td class="py-3 text-right">
                                <p class="text-sm text-text-dark">15</p>
                                <p class="text-xs text-text-muted">cans</p>
                            </td>
                            <td class="py-3 text-right">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded">LOW STOCK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions & Stock Status -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Inventory Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Item
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Stock Take
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-sync-alt mr-2 text-chocolate"></i>
                    Update Stock Levels
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-file-export mr-2 text-chocolate"></i>
                    Export Inventory
                </button>
            </div>

            <!-- Stock Status Overview -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Status Overview</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">In Stock (Optimal)</span>
                            <span class="text-sm font-bold text-text-dark">1,845 items</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 78%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Low Stock</span>
                            <span class="text-sm font-bold text-text-dark">23 items</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: 15%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Critical Stock</span>
                            <span class="text-sm font-bold text-text-dark">8 items</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: 5%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Out of Stock</span>
                            <span class="text-sm font-bold text-text-dark">2 items</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gray-400 h-2 rounded-full" style="width: 2%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white border-2 border-red-200 p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Low Stock Alerts
            </h3>
            
            <div class="space-y-4">
                <!-- Alert Item -->
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Chocolate Chips (Premium Dark)</p>
                            <p class="text-xs text-text-muted mt-1">SKU: CHC-DK-5KG • Current: 2 kg • Min: 10 kg</p>
                            <p class="text-xs text-text-muted mt-1">Last Ordered: Nov 15, 2024 • Supplier: Sweet Supplies Co.</p>
                        </div>
                        <button class="px-3 py-1 bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                            REORDER
                        </button>
                    </div>
                </div>

                <!-- Alert Item -->
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                            <p class="text-xs text-text-muted mt-1">SKU: FLR-AP-25KG • Current: 15 kg • Min: 50 kg</p>
                            <p class="text-xs text-text-muted mt-1">Last Ordered: Nov 18, 2024 • Supplier: Baker's Flour Mill</p>
                        </div>
                        <button class="px-3 py-1 bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                            REORDER
                        </button>
                    </div>
                </div>

                <!-- Alert Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Cake Boxes (Large)</p>
                            <p class="text-xs text-text-muted mt-1">SKU: BOX-CAKE-LG • Current: 25 units • Min: 100 units</p>
                            <p class="text-xs text-text-muted mt-1">Last Ordered: Nov 10, 2024 • Supplier: Packaging Pro Ltd.</p>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                            REORDER
                        </button>
                    </div>
                </div>

                <!-- Alert Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Baking Powder</p>
                            <p class="text-xs text-text-muted mt-1">SKU: BAK-PWD-2KG • Current: 8 cans • Min: 15 cans</p>
                            <p class="text-xs text-text-muted mt-1">Last Ordered: Nov 12, 2024 • Supplier: Chemical Supplies Inc.</p>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                            REORDER
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Categories -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-tags text-caramel mr-2"></i>
                Inventory by Category
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 border-l-4 border-green-500">
                    <p class="text-sm font-bold text-text-dark">Raw Materials</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">856</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
                
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500">
                    <p class="text-sm font-bold text-text-dark">Ingredients</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">642</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
                
                <div class="p-4 bg-purple-50 border-l-4 border-purple-500">
                    <p class="text-sm font-bold text-text-dark">Packaging</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">389</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
                
                <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500">
                    <p class="text-sm font-bold text-text-dark">Dairy</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">215</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
                
                <div class="p-4 bg-indigo-50 border-l-4 border-indigo-500">
                    <p class="text-sm font-bold text-text-dark">Equipment</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">143</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
                
                <div class="p-4 bg-pink-50 border-l-4 border-pink-500">
                    <p class="text-sm font-bold text-text-dark">Supplies</p>
                    <p class="text-2xl font-bold text-text-dark mt-1">102</p>
                    <p class="text-xs text-text-muted">items</p>
                </div>
            </div>

            <!-- Recent Stock Updates -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Recent Stock Updates</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Vanilla Extract received +24</span>
                        <span class="text-text-muted text-xs">Today, 10:30 AM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Butter issued -12</span>
                        <span class="text-text-muted text-xs">Today, 09:15 AM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Flour issued -8 kg</span>
                        <span class="text-text-muted text-xs">Yesterday, 04:20 PM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-text-dark">Chocolate Chips received +15 kg</span>
                        <span class="text-text-muted text-xs">Nov 25, 2024</span>
                    </div>
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