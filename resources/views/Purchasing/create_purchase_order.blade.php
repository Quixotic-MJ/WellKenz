@extends('Purchasing.layout.app')

@section('title', 'Purchase Orders - WellKenz ERP')

@section('breadcrumb', 'Purchase Orders')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Purchase Order Management</h1>
                <p class="text-text-muted mt-2">Create, track, and manage all purchase orders.</p>
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
                    <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total POs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-edit text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Draft POs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-truck text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">In Transit</p>
            <p class="text-3xl font-bold text-text-dark mt-2">42</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Delayed Orders</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Purchase Orders -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Purchase Orders</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">PO-2024-0515 • Global Materials Inc.</p>
                        <p class="text-xs text-text-muted mt-1">Raw Materials • $45,800 • Linked to: REQ-MFG-2024-0015</p>
                        <p class="text-xs text-text-muted mt-1">Created: Today • Expected Delivery: Nov 30, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">ISSUED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">PO-2024-0514 • Tech Solutions Ltd.</p>
                        <p class="text-xs text-text-muted mt-1">IT Equipment • $22,150 • Linked to: REQ-IT-2024-0034</p>
                        <p class="text-xs text-text-muted mt-1">Created: Yesterday • Expected Delivery: Dec 5, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold">PENDING APPROVAL</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">PO-2024-0513 • Office Pro Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Office Furniture • $8,450 • Linked to: REQ-HR-2024-0078</p>
                        <p class="text-xs text-text-muted mt-1">Created: 2 days ago • Expected Delivery: Dec 2, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">CONFIRMED</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-purple-500 bg-purple-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">PO-2024-0512 • Industrial Tools Co.</p>
                        <p class="text-xs text-text-muted mt-1">Maintenance Tools • $7,890 • Linked to: REQ-OPS-2024-0085</p>
                        <p class="text-xs text-text-muted mt-1">Created: 3 days ago • Expected Delivery: Nov 28, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-purple-600 text-white text-xs font-bold">IN TRANSIT</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">PO Quick Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create New PO
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-search mr-2"></i>
                    Search POs
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-truck mr-2 text-chocolate"></i>
                    Track Shipments
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-file-export mr-2 text-chocolate"></i>
                    Export PO Report
                </button>
            </div>

            <!-- PO Status Summary -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">PO Status Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Draft</span>
                        <span class="text-sm font-bold text-text-dark">23</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Pending Approval</span>
                        <span class="text-sm font-bold text-text-dark">15</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Issued</span>
                        <span class="text-sm font-bold text-text-dark">42</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Confirmed</span>
                        <span class="text-sm font-bold text-text-dark">38</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">In Transit</span>
                        <span class="text-sm font-bold text-text-dark">28</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- PO Tracking -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-shipping-fast text-caramel mr-2"></i>
                PO Tracking & Status
            </h3>
            
            <div class="space-y-4">
                <!-- PO Tracking Item -->
                <div class="border-l-4 border-purple-500 bg-purple-50 p-4">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0512 • Industrial Tools Co.</p>
                    <p class="text-xs text-text-muted mt-1">Maintenance Tools • $7,890</p>
                    
                    <!-- Tracking Progress -->
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                            <span>Created</span>
                            <span>Confirmed</span>
                            <span>Shipped</span>
                            <span>Delivered</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Nov 20</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 21</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 22</span>
                            <span class="text-xs text-text-muted">Est: Nov 28</span>
                        </div>
                    </div>
                    <span class="inline-block mt-2 px-2 py-1 bg-purple-600 text-white text-xs font-bold">IN TRANSIT</span>
                </div>

                <!-- PO Tracking Item -->
                <div class="border-l-4 border-green-500 bg-green-50 p-4">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0510 • Quality Ingredients Inc.</p>
                    <p class="text-xs text-text-muted mt-1">Raw Materials • $12,340</p>
                    
                    <!-- Tracking Progress -->
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                            <span>Created</span>
                            <span>Confirmed</span>
                            <span>Shipped</span>
                            <span>Delivered</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Nov 15</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 16</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 18</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 25</span>
                        </div>
                    </div>
                    <span class="inline-block mt-2 px-2 py-1 bg-green-600 text-white text-xs font-bold">DELIVERED</span>
                </div>

                <!-- PO Tracking Item -->
                <div class="border-l-4 border-red-500 bg-red-50 p-4">
                    <p class="text-sm font-bold text-text-dark">PO-2024-0505 • Equipment Masters</p>
                    <p class="text-xs text-text-muted mt-1">Industrial Ovens • $15,750</p>
                    
                    <!-- Tracking Progress -->
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-text-muted mb-2">
                            <span>Created</span>
                            <span>Confirmed</span>
                            <span>Shipped</span>
                            <span>Delivered</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: 50%"></div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Nov 10</span>
                            <span class="text-xs text-green-600 font-bold">✓ Nov 12</span>
                            <span class="text-xs text-red-600 font-bold">Delayed</span>
                            <span class="text-xs text-red-600 font-bold">Overdue</span>
                        </div>
                    </div>
                    <span class="inline-block mt-2 px-2 py-1 bg-red-600 text-white text-xs font-bold">DELAYED</span>
                </div>
            </div>
        </div>

        <!-- Requisitions Ready for PO -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-clipboard-check text-caramel mr-2"></i>
                Requisitions Ready for PO Creation
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">REQ-MFG-2024-0018 • Production Materials</p>
                            <p class="text-xs text-text-muted mt-1">Manufacturing Dept • $28,500 • Approved: Today</p>
                            <p class="text-xs text-text-muted mt-1">Best Quote: Global Materials Inc. • $27,800</p>
                        </div>
                        <button class="px-3 py-1 bg-caramel text-white text-xs font-bold hover:bg-caramel-dark transition">
                            CREATE PO
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">REQ-IT-2024-0036 • Network Equipment</p>
                            <p class="text-xs text-text-muted mt-1">IT Department • $15,200 • Approved: Yesterday</p>
                            <p class="text-xs text-text-muted mt-1">Best Quote: Tech Solutions Ltd. • $14,900</p>
                        </div>
                        <button class="px-3 py-1 bg-caramel text-white text-xs font-bold hover:bg-caramel-dark transition">
                            CREATE PO
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">REQ-OPS-2024-0095 • Safety Equipment</p>
                            <p class="text-xs text-text-muted mt-1">Operations Dept • $9,800 • Approved: 2 days ago</p>
                            <p class="text-xs text-text-muted mt-1">Quotes Under Review • 3 suppliers</p>
                        </div>
                        <button class="px-3 py-1 bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                            REVIEW QUOTES
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">REQ-HR-2024-0082 • Office Supplies</p>
                            <p class="text-xs text-text-muted mt-1">HR Department • $4,500 • Approved: 3 days ago</p>
                            <p class="text-xs text-text-muted mt-1">Waiting for final quote • Urgent request</p>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                            EXPEDITE
                        </button>
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