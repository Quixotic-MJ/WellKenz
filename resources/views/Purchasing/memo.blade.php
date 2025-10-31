@extends('Purchasing.layout.app')

@section('title', 'Delivery Acknowledgments - WellKenz ERP')

@section('breadcrumb', 'Delivery Acknowledgments')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Delivery Acknowledgments</h1>
                <p class="text-text-muted mt-2">Track and acknowledge deliveries received from suppliers.</p>
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
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Receipt</p>
            <p class="text-3xl font-bold text-text-dark mt-2">18</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Acknowledged Today</p>
            <p class="text-3xl font-bold text-text-dark mt-2">12</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Discrepancies</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Overdue Acknowledgments</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Acknowledgments -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Pending Delivery Acknowledgments</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <!-- Delivery Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">PO-2024-0515 • Global Materials Inc.</p>
                                <p class="text-xs text-text-muted mt-1">Raw Materials • 45 packages • Received: Today, 09:30 AM</p>
                                <p class="text-xs text-text-muted mt-1">Receiver: John Doe • Dock 2</p>
                                <div class="flex items-center mt-2">
                                    <span class="text-xs text-text-muted">Condition: </span>
                                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Good</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Expected: 45 packages</p>
                                <p class="text-xs text-text-muted">Received: 45 packages</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">PO-2024-0514 • Tech Solutions Ltd.</p>
                                <p class="text-xs text-text-muted mt-1">IT Equipment • 12 packages • Received: Today, 11:15 AM</p>
                                <p class="text-xs text-text-muted mt-1">Receiver: Sarah Smith • Dock 1</p>
                                <div class="flex items-center mt-2">
                                    <span class="text-xs text-text-muted">Condition: </span>
                                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Good</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Expected: 15 packages</p>
                                <p class="text-xs text-text-muted">Received: 12 packages</p>
                                <p class="text-xs text-red-600 font-bold">3 packages short</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">PO-2024-0512 • Industrial Tools Co.</p>
                                <p class="text-xs text-text-muted mt-1">Maintenance Tools • 8 packages • Received: Yesterday, 02:45 PM</p>
                                <p class="text-xs text-text-muted mt-1">Receiver: Mike Johnson • Dock 3</p>
                                <div class="flex items-center mt-2">
                                    <span class="text-xs text-text-muted">Condition: </span>
                                    <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Damaged</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Expected: 8 packages</p>
                                <p class="text-xs text-text-muted">Received: 8 packages</p>
                                <p class="text-xs text-red-600 font-bold">2 packages damaged</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">PO-2024-0510 • Office Pro Supplies</p>
                                <p class="text-xs text-text-muted mt-1">Office Furniture • 25 packages • Received: 2 days ago</p>
                                <p class="text-xs text-text-muted mt-1">Receiver: Lisa Brown • Dock 2</p>
                                <div class="flex items-center mt-2">
                                    <span class="text-xs text-text-muted">Condition: </span>
                                    <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">Partial Damage</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Expected: 25 packages</p>
                                <p class="text-xs text-text-muted">Received: 25 packages</p>
                                <p class="text-xs text-orange-600 font-bold">Overdue for acknowledgment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Delivery Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Acknowledge Delivery
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-truck mr-2"></i>
                    Receive New Delivery
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-file-excel mr-2 text-chocolate"></i>
                    Export Acknowledgments
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-search mr-2 text-chocolate"></i>
                    Search Deliveries
                </button>
            </div>

            <!-- Today's Summary -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Today's Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Deliveries Received</span>
                        <span class="text-sm font-bold text-text-dark">8</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Acknowledged</span>
                        <span class="text-sm font-bold text-text-dark">5</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Pending Review</span>
                        <span class="text-sm font-bold text-text-dark">3</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Discrepancies</span>
                        <span class="text-sm font-bold text-red-600">2</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Acknowledgments -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-check-circle text-caramel mr-2"></i>
                Recent Acknowledgments
            </h3>
            
            <div class="space-y-4">
                <!-- Acknowledgment Item -->
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0509 • Quality Ingredients Inc.</p>
                            <p class="text-xs text-text-muted mt-1">Raw Materials • 32 packages • Fully Received</p>
                            <p class="text-xs text-text-muted mt-1">Acknowledged by: John Doe • Today, 10:20 AM</p>
                            <div class="flex items-center mt-2">
                                <span class="text-xs text-green-600 font-bold">✓ All items received in good condition</span>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">COMPLETED</span>
                    </div>
                </div>

                <!-- Acknowledgment Item -->
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0508 • Packaging Pro Ltd.</p>
                            <p class="text-xs text-text-muted mt-1">Packaging Materials • 56 packages • Fully Received</p>
                            <p class="text-xs text-text-muted mt-1">Acknowledged by: Sarah Smith • Today, 09:45 AM</p>
                            <div class="flex items-center mt-2">
                                <span class="text-xs text-green-600 font-bold">✓ All items received in good condition</span>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">COMPLETED</span>
                    </div>
                </div>

                <!-- Acknowledgment Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0507 • Equipment Masters</p>
                            <p class="text-xs text-text-muted mt-1">Industrial Equipment • 5 packages • Partial Receipt</p>
                            <p class="text-xs text-text-muted mt-1">Acknowledged by: Mike Johnson • Yesterday, 04:30 PM</p>
                            <div class="flex items-center mt-2">
                                <span class="text-xs text-orange-600 font-bold">⚠ 1 package missing, noted for follow-up</span>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold">PENDING RESOLUTION</span>
                    </div>
                </div>

                <!-- Acknowledgment Item -->
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0506 • Safety First Supplies</p>
                            <p class="text-xs text-text-muted mt-1">Safety Equipment • 18 packages • Fully Received</p>
                            <p class="text-xs text-text-muted mt-1">Acknowledged by: Lisa Brown • Yesterday, 02:15 PM</p>
                            <div class="flex items-center mt-2">
                                <span class="text-xs text-green-600 font-bold">✓ All items received in good condition</span>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold">ARCHIVED</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Discrepancies -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Delivery Discrepancies
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0512 • Industrial Tools Co.</p>
                            <p class="text-xs text-text-muted mt-1">2 packages damaged upon receipt</p>
                            <p class="text-xs text-text-muted mt-1">Items: Power Drill (x1), Safety Glasses (x1)</p>
                            <p class="text-xs text-text-muted mt-1">Reported: Today, 11:30 AM • Status: Investigation Required</p>
                        </div>
                        <button class="px-3 py-1 bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                            REPORT ISSUE
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0514 • Tech Solutions Ltd.</p>
                            <p class="text-xs text-text-muted mt-1">3 packages missing from delivery</p>
                            <p class="text-xs text-text-muted mt-1">Items: Network Switches (x3)</p>
                            <p class="text-xs text-text-muted mt-1">Reported: Today, 11:15 AM • Status: Contacted Supplier</p>
                        </div>
                        <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                            FOLLOW UP
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0510 • Office Pro Supplies</p>
                            <p class="text-xs text-text-muted mt-1">Wrong items received in 2 packages</p>
                            <p class="text-xs text-text-muted mt-1">Received: Standard Chairs • Expected: Executive Chairs</p>
                            <p class="text-xs text-text-muted mt-1">Reported: Yesterday, 03:45 PM • Status: Return Initiated</p>
                        </div>
                        <button class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold hover:bg-yellow-700 transition">
                            TRACK RETURN
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-purple-500 bg-purple-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0507 • Equipment Masters</p>
                            <p class="text-xs text-text-muted mt-1">1 package missing from previous delivery</p>
                            <p class="text-xs text-text-muted mt-1">Item: Industrial Oven Control Panel</p>
                            <p class="text-xs text-text-muted mt-1">Reported: 2 days ago • Status: Backordered</p>
                        </div>
                        <button class="px-3 py-1 bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 transition">
                            CHECK STATUS
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