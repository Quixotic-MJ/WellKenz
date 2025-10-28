@extends('Admin.layout.app')

@section('title', 'Dashboard - WellKenz ERP')

@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Good morning, John</h1>
                <p class="text-text-muted mt-2">Your procurement overview for today.</p>
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
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Purchase Orders</p>
            <p class="text-3xl font-bold text-text-dark mt-2">89</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-warehouse text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Inventory Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">2,347</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Requisitions</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-caramel bg-cream-bg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Baking Ingredients - Bulk Order</p>
                        <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0012</p>
                    </div>
                    <span class="px-3 py-1 bg-caramel text-white text-xs font-bold">PENDING</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Kitchen Equipment</p>
                        <p class="text-xs text-text-muted mt-1">Operations Department • REQ-2024-0011</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">APPROVED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Packaging Materials</p>
                        <p class="text-xs text-text-muted mt-1">Packaging Department • REQ-2024-0010</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">REVIEW</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-chocolate bg-cream-bg">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Office Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Administration • REQ-2024-0009</p>
                    </div>
                    <span class="px-3 py-1 bg-chocolate text-white text-xs font-bold">PENDING</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    New Requisition
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Purchase Orders
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-warehouse mr-2 text-chocolate"></i>
                    Inventory Check
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-chart-bar mr-2 text-chocolate"></i>
                    View Reports
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Purchase Orders -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Active Purchase Orders</h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0456</p>
                    <p class="text-xs text-text-muted mt-1">Flour & Sugar • Baker's Supply Co. • $2,450</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-green-600 text-white text-xs font-bold">DELIVERED</span>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0457</p>
                    <p class="text-xs text-text-muted mt-1">Packaging • PackPro Inc. • $1,230</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-blue-600 text-white text-xs font-bold">IN TRANSIT</span>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0458</p>
                    <p class="text-xs text-text-muted mt-1">Equipment • KitchenTech • $8,750</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-yellow-600 text-white text-xs font-bold">PROCESSING</span>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white border-2 border-red-200 p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Low Stock Alerts
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                            <p class="text-xs text-text-muted mt-1">Current: 15 kg • Minimum: 50 kg</p>
                        </div>
                        <button class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition">
                            REORDER
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                            <p class="text-xs text-text-muted mt-1">Current: 2 kg • Minimum: 10 kg</p>
                        </div>
                        <button class="px-3 py-1 bg-red-500 text-white text-xs font-bold hover:bg-red-600 transition">
                            REORDER
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Cake Boxes (Large)</p>
                            <p class="text-xs text-text-muted mt-1">Current: 25 units • Minimum: 100 units</p>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                            REORDER
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }

    .cream-bg {
        background-color: #faf7f3;
    }
    
    .text-dark {
        color: #1a1410;
    }
    
    .text-muted {
        color: #8b7355;
    }
    
    .chocolate {
        background-color: #3d2817;
    }
    
    .chocolate-dark {
        background-color: #2a1a0f;
    }
    
    .caramel {
        background-color: #c48d3f;
    }
    
    .caramel-dark {
        background-color: #a67332;
    }
    
    .border-soft {
        border-color: #e8dfd4;
    }
</style>
@endsection