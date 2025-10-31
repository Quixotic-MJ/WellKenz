@extends('Employee.layout.app')

@section('title', 'Baker Notifications - WellKenz ERP')

@section('breadcrumb', 'Baker Notifications')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Baker's Notification Center</h1>
                <p class="text-text-muted mt-2">Stay updated on your requisition status and deliveries.</p>
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
                    <i class="fas fa-bell text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">New Notifications</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved Today</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-truck text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Deliveries Today</p>
            <p class="text-3xl font-bold text-text-dark mt-2">2</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Actions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">4</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Notification Center -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Notifications</h3>
                <div class="flex space-x-2">
                    <button class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">
                        Mark All Read
                    </button>
                    <button class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">
                        View All
                    </button>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- Notification Item - Delivery -->
                <div class="flex items-start p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-truck-loading text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Delivery Received - Chocolate Chip Order</p>
                        <p class="text-xs text-text-muted mt-1">Your chocolate chips (15kg) have been delivered to the baking station.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0245 • Delivered: Today, 08:45 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Ready for use in production</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full">NEW</span>
                </div>

                <!-- Notification Item - Approved -->
                <div class="flex items-start p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Requisition Approved - Weekend Special Ingredients</p>
                        <p class="text-xs text-text-muted mt-1">Your request for flour, butter, and vanilla extract has been approved.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0246 • Approved: Today, 08:30 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Order placed with supplier</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold rounded-full">NEW</span>
                </div>

                <!-- Notification Item - Rejected -->
                <div class="flex items-start p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-times-circle text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Requisition Rejected - Premium Almond Flour</p>
                        <p class="text-xs text-text-muted mt-1">Your request for premium almond flour has been rejected due to budget constraints.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0243 • Rejected: Today, 08:15 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-red-600 font-bold">✗ Suggestion: Use regular almond flour instead</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded-full">ATTENTION</span>
                </div>

                <!-- Notification Item - Delivery Scheduled -->
                <div class="flex items-start p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Delivery Scheduled - Cake Boxes</p>
                        <p class="text-xs text-text-muted mt-1">Your cake boxes (200 units) are scheduled for delivery tomorrow morning.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0240 • Expected: Tomorrow, 09:00 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-blue-600 font-bold">📦 Will be delivered to packaging station</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded-full">SCHEDULED</span>
                </div>

                <!-- Notification Item - Approval Needed -->
                <div class="flex items-start p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Approval Pending - Birthday Cake Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Your request for fondant and food coloring is awaiting manager approval.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0247 • Submitted: Yesterday, 03:45 PM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-yellow-600 font-bold">⏳ Estimated approval: Today EOD</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-yellow-600 text-white text-xs font-bold rounded-full">PENDING</span>
                </div>

                <!-- Notification Item - Partial Delivery -->
                <div class="flex items-start p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Partial Delivery - Baking Equipment</p>
                        <p class="text-xs text-text-muted mt-1">Only 3 out of 5 requested mixing bowls were delivered. Rest are backordered.</p>
                        <p class="text-xs text-text-muted mt-1">Requisition: REQ-BAKE-0238 • Delivered: Yesterday, 02:30 PM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-orange-600 font-bold">⚠ Remaining items expected in 5-7 days</span>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold rounded-full">PARTIAL</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alert Settings -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Notification Settings</h3>
            
            <div class="space-y-4">
                <!-- Alert Preferences -->
                <div>
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Alert Preferences</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                            <span class="ml-2 text-sm text-text-dark">Requisition Approvals</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                            <span class="ml-2 text-sm text-text-dark">Requisition Rejections</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                            <span class="ml-2 text-sm text-text-dark">Delivery Notifications</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                            <span class="ml-2 text-sm text-text-dark">Delivery Delays</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                            <span class="ml-2 text-sm text-text-dark">Urgent Requisitions</span>
                        </label>
                    </div>
                </div>

                <!-- Notification Methods -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Notification Methods</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel" checked>
                            <span class="ml-2 text-sm text-text-dark">In-App Notifications</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                            <span class="ml-2 text-sm text-text-dark">Email Alerts</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-border-soft text-caramel focus:ring-caramel">
                            <span class="ml-2 text-sm text-text-dark">SMS Text Messages</span>
                        </label>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Quick Actions</h4>
                    <div class="space-y-2">
                        <button class="w-full p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold text-sm">
                            <i class="fas fa-bell-slash mr-2"></i>
                            Snooze Notifications
                        </button>
                        <button class="w-full p-3 border border-border-soft hover:bg-cream-bg transition text-center font-semibold text-sm text-text-dark">
                            <i class="fas fa-cog mr-2 text-chocolate"></i>
                            Notification Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- Today's Summary -->
            <div class="mt-6 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Today's Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">New Notifications</span>
                        <span class="text-sm font-bold text-text-dark">5</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Approved</span>
                        <span class="text-sm font-bold text-green-600">3</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Rejected</span>
                        <span class="text-sm font-bold text-red-600">1</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-text-muted">Delivered</span>
                        <span class="text-sm font-bold text-blue-600">2</span>
                    </div>
                </div>
            </div>

            <!-- Urgent Alerts -->
            <div class="mt-6 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Urgent Alerts</h4>
                <div class="space-y-2">
                    <div class="p-3 bg-red-50 border border-red-200 rounded">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chips Running Low</p>
                        <p class="text-xs text-text-muted">Current stock: 2kg • Reorder immediately</p>
                    </div>
                    <div class="p-3 bg-orange-50 border border-orange-200 rounded">
                        <p class="text-sm font-bold text-text-dark">Flour Delivery Delayed</p>
                        <p class="text-xs text-text-muted">Expected tomorrow instead of today</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Delivery Tracking -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-shipping-fast text-caramel mr-2"></i>
                Delivery Tracking
            </h3>
            
            <div class="space-y-4">
                <!-- Delivery Item -->
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Chocolate Chips & Vanilla Extract</p>
                            <p class="text-xs text-text-muted mt-1">REQ-BAKE-0245 • Delivered: Today, 08:45 AM</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Ordered</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Shipped</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-green-600 font-bold">Delivered</span>
                                </div>
                            </div>
                            <p class="text-xs text-green-600 mt-2">✓ Received at baking station, ready for use</p>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">COMPLETE</span>
                    </div>
                </div>

                <!-- Delivery Item -->
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Cake Boxes & Packaging</p>
                            <p class="text-xs text-text-muted mt-1">REQ-BAKE-0240 • Expected: Tomorrow, 09:00 AM</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Ordered</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Shipped</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-blue-600 font-bold">In Transit</span>
                                </div>
                            </div>
                            <p class="text-xs text-blue-600 mt-2">📦 Out for delivery, expected tomorrow morning</p>
                        </div>
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold">IN TRANSIT</span>
                    </div>
                </div>

                <!-- Delivery Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Mixing Bowls Set</p>
                            <p class="text-xs text-text-muted mt-1">REQ-BAKE-0238 • Partial Delivery: Yesterday</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Ordered</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-text-muted">Partially Shipped</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                                    <span class="text-xs text-orange-600 font-bold">Backordered</span>
                                </div>
                            </div>
                            <p class="text-xs text-orange-600 mt-2">⚠ 2 bowls backordered, expected in 5-7 days</p>
                        </div>
                        <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold">PARTIAL</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Status & Quick Response -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-reply text-caramel mr-2"></i>
                Quick Responses Needed
            </h3>
            
            <div class="space-y-4">
                <!-- Response Needed Item -->
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Premium Almond Flour Rejected</p>
                            <p class="text-xs text-text-muted mt-1">REQ-BAKE-0243 • Budget constraints</p>
                            <p class="text-xs text-text-muted mt-1">Suggestion: Use regular almond flour instead</p>
                            <div class="mt-3 space-y-2">
                                <button class="w-full p-2 bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition rounded">
                                    ✓ Accept Alternative
                                </button>
                                <button class="w-full p-2 bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition rounded">
                                    📞 Discuss with Manager
                                </button>
                                <button class="w-full p-2 bg-gray-600 text-white text-xs font-bold hover:bg-gray-700 transition rounded">
                                    ✗ Cancel Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response Needed Item -->
                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Birthday Cake Supplies Pending</p>
                            <p class="text-xs text-text-muted mt-1">REQ-BAKE-0247 • Awaiting your confirmation</p>
                            <p class="text-xs text-text-muted mt-1">Urgent: Needed for tomorrow's orders</p>
                            <div class="mt-3 space-y-2">
                                <button class="w-full p-2 bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition rounded">
                                    ✓ Confirm Urgency
                                </button>
                                <button class="w-full p-2 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition rounded">
                    ⏰ Expedite Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="mt-6 p-4 bg-cream-bg border border-border-soft rounded">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-2">Get Notified Faster</h4>
                    <div class="space-y-2">
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-white rounded transition">
                            <i class="fas fa-mobile-alt text-caramel mr-2"></i>
                            Enable Push Notifications
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-white rounded transition">
                            <i class="fas fa-envelope text-caramel mr-2"></i>
                            Setup Email Digest
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-white rounded transition">
                            <i class="fas fa-slack text-caramel mr-2"></i>
                            Connect to Slack
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