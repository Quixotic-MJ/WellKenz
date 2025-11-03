@extends('Purchasing.layout.app')

@section('title', 'Dashboard - WellKenz ERP')

@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Good morning, Procurement Team</h1>
                <p class="text-text-muted mt-2">Supervisor-approved requisitions awaiting your action.</p>
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
                    <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">POs to Create</p>
            <p class="text-3xl font-bold text-text-dark mt-2">28</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-handshake text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Supplier Quotes</p>
            <p class="text-3xl font-bold text-text-dark mt-2">15</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Urgent Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">7</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Supervisor Approved Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Supervisor Approved Requisitions</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Raw Materials - Production Batch #245</p>
                        <p class="text-xs text-text-muted mt-1">Manufacturing Dept • REQ-MFG-2024-0015 • $45,800</p>
                        <p class="text-xs text-text-muted mt-1">Approved by: Sarah Johnson • 2 days ago</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">READY FOR PROCUREMENT</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">IT Equipment - Workstation Upgrades</p>
                        <p class="text-xs text-text-muted mt-1">IT Department • REQ-IT-2024-0034 • $22,150</p>
                        <p class="text-xs text-text-muted mt-1">Approved by: Michael Chen • 1 day ago</p>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">READY FOR PROCUREMENT</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Office Furniture - New Hire Setup</p>
                        <p class="text-xs text-text-muted mt-1">HR Department • REQ-HR-2024-0078 • $8,450</p>
                        <p class="text-xs text-text-muted mt-1">Approved by: David Wilson • 4 hours ago</p>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">AWAITING QUOTES</span>
                </div>

                <div class="flex items-start justify-between p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Safety Equipment - Emergency Restock</p>
                        <p class="text-xs text-text-muted mt-1">Operations Dept • REQ-OPS-2024-0092 • $12,300</p>
                        <p class="text-xs text-text-muted mt-1">Approved by: Maria Rodriguez • 3 days ago • URGENT</p>
                    </div>
                    <span class="px-3 py-1 bg-orange-600 text-white text-xs font-bold">PRIORITY PROCUREMENT</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Procurement Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-file-purchase-order mr-2"></i>
                    Process Requisition
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-quote-right mr-2"></i>
                    Request Quotes
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-truck mr-2 text-chocolate"></i>
                    Track Orders
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-users mr-2 text-chocolate"></i>
                    Supplier Contacts
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Procurement Pipeline -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Procurement Pipeline</h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <p class="text-sm font-bold text-text-dark">REQ-MFG-2024-0012 • Raw Materials</p>
                    <p class="text-xs text-text-muted mt-1">3 Quotes Received • Best: Global Materials Inc. • $28,400</p>
                    <p class="text-xs text-text-muted mt-1">Next Step: Quote Evaluation</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-blue-600 text-white text-xs font-bold">QUOTE STAGE</span>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <p class="text-sm font-bold text-text-dark">REQ-IT-2024-0028 • Server Equipment</p>
                    <p class="text-xs text-text-muted mt-1">Quote Approved • Tech Solutions Ltd. • $15,670</p>
                    <p class="text-xs text-text-muted mt-1">Next Step: Create Purchase Order</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-yellow-600 text-white text-xs font-bold">APPROVED FOR PO</span>
                </div>

                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <p class="text-sm font-bold text-text-dark">REQ-OPS-2024-0085 • Maintenance Tools</p>
                    <p class="text-xs text-text-muted mt-1">PO-2024-0512 Created • Industrial Tools Co. • $7,890</p>
                    <p class="text-xs text-text-muted mt-1">Next Step: Order Confirmation</p>
                    <span class="inline-block mt-2 px-2 py-1 bg-green-600 text-white text-xs font-bold">PO ISSUED</span>
                </div>
            </div>
        </div>

        <!-- Recent Supplier Quotes -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-file-contract text-caramel mr-2"></i>
                Recent Supplier Quotes
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Global Materials Inc.</p>
                            <p class="text-xs text-text-muted mt-1">Raw Materials • $28,400 • Valid until: 30 days</p>
                            <p class="text-xs text-text-muted mt-1">For: REQ-MFG-2024-0012</p>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">BEST QUOTE</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Tech Solutions Ltd.</p>
                            <p class="text-xs text-text-muted mt-1">IT Equipment • $15,670 • Valid until: 14 days</p>
                            <p class="text-xs text-text-muted mt-1">For: REQ-IT-2024-0028</p>
                        </div>
                        <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold">UNDER REVIEW</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Office Pro Supplies</p>
                            <p class="text-xs text-text-muted mt-1">Furniture • $9,240 • Valid until: 7 days</p>
                            <p class="text-xs text-text-muted mt-1">For: REQ-HR-2024-0078 • Expiring Soon</p>
                        </div>
                        <span class="px-2 py-1 bg-orange-600 text-white text-xs font-bold">ACTION NEEDED</span>
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