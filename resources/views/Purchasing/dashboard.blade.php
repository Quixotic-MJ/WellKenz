@extends('Purchasing.layout.app')

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
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">42</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-truck text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Deliveries</p>
            <p class="text-3xl font-bold text-text-dark mt-2">18</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-star text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Supplier Rating</p>
            <p class="text-3xl font-bold text-text-dark mt-2">4.8</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Overdue Deliveries</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Approved Requisitions Pending Purchase -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Approved Requisitions Pending Purchase</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Industrial Mixers - Commercial Grade</p>
                        <p class="text-xs text-text-muted mt-1">Production Department • REQ-2024-0015 • $12,500</p>
                        <p class="text-xs text-text-muted mt-1">Approved: 2 days ago</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">READY FOR PO</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Organic Ingredients - Q3 Stock</p>
                        <p class="text-xs text-text-muted mt-1">Quality Department • REQ-2024-0014 • $8,750</p>
                        <p class="text-xs text-text-muted mt-1">Approved: 1 day ago</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">READY FOR PO</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Safety Equipment & Gear</p>
                        <p class="text-xs text-text-muted mt-1">HR Department • REQ-2024-0013 • $3,200</p>
                        <p class="text-xs text-text-muted mt-1">Approved: 4 hours ago</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">QUOTATION STAGE</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Packaging Materials - Seasonal</p>
                        <p class="text-xs text-text-muted mt-1">Marketing Department • REQ-2024-0012 • $5,400</p>
                        <p class="text-xs text-text-muted mt-1">Approved: 3 days ago</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">READY FOR PO</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-file-purchase-order mr-2"></i>
                    Create Purchase Order
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-truck-loading mr-2"></i>
                    Track Deliveries
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-star mr-2 text-chocolate"></i>
                    Rate Suppliers
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-chart-line mr-2 text-chocolate"></i>
                    Performance Reports
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pending Deliveries -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Pending Deliveries</h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0489 • Baker's Supply Co.</p>
                    <p class="text-xs text-text-muted mt-1">Flour & Baking Ingredients • $3,450</p>
                    <p class="text-xs text-text-muted mt-1">Estimated Delivery: Tomorrow</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-blue-600 text-white text-xs font-bold">IN TRANSIT</span>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0490 • Packaging Pro Ltd.</p>
                    <p class="text-xs text-text-muted mt-1">Custom Boxes & Wrappers • $2,180</p>
                    <p class="text-xs text-text-muted mt-1">Estimated Delivery: 3 days</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-yellow-600 text-white text-xs font-bold">PROCESSING</span>
                </div>

                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0485 • Equipment Masters</p>
                    <p class="text-xs text-text-muted mt-1">Industrial Ovens • $15,750</p>
                    <p class="text-xs text-text-muted mt-1">Estimated Delivery: Overdue by 2 days</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-red-600 text-white text-xs font-bold">DELAYED</span>
                </div>
            </div>
        </div>

        <!-- Supplier Performance -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-trophy text-caramel mr-2"></i>
                Supplier Performance
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Baker's Supply Co.</p>
                            <p class="text-xs text-text-muted mt-1">Rating: 4.9/5 • On-time Delivery: 98%</p>
                            <p class="text-xs text-text-muted mt-1">Last Order: PO-2024-0489 • $3,450</p>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">EXCELLENT</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Quality Ingredients Inc.</p>
                            <p class="text-xs text-text-muted mt-1">Rating: 4.3/5 • On-time Delivery: 85%</p>
                            <p class="text-xs text-text-muted mt-1">Last Order: PO-2024-0487 • $2,890</p>
                        </div>
                        <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold">GOOD</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Equipment Masters</p>
                            <p class="text-xs text-text-muted mt-1">Rating: 3.7/5 • On-time Delivery: 72%</p>
                            <p class="text-xs text-text-muted mt-1">Current: PO-2024-0485 • $15,750 (Delayed)</p>
                        </div>
                        <span class="px-2 py-1 bg-orange-600 text-white text-xs font-bold">NEEDS IMPROVEMENT</span>
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