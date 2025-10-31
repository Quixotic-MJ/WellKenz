@extends('Purchasing.layout.app')

@section('title', 'Notifications - WellKenz ERP')

@section('breadcrumb', 'Notifications')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Notifications</h1>
            <p class="text-text-muted mt-2">Stay updated on requisitions, stock levels, and deliveries</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="markAllAsRead()" class="px-4 py-2 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition">
                <i class="fas fa-check-double mr-2"></i>
                <span class="font-semibold">Mark All Read</span>
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total</p>
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-orange-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Unread</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">12</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Stock Alerts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Notifications List -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Unread -->
            <div class="bg-white border-2 border-border-soft">
                <div class="px-6 py-4 border-b-2 border-border-soft bg-red-50">
                    <div class="flex items-center justify-between">
                        <h3 class="font-display text-xl font-bold text-text-dark">Unread</h3>
                        <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold">23</span>
                    </div>
                </div>
                
                <div class="divide-y divide-border-soft">
                    <!-- Critical Stock Alert -->
                    <div class="p-6 hover:bg-cream-bg transition border-l-4 border-red-500 cursor-pointer" onclick="openNotification('1')">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-red-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">Critical Stock Alert</p>
                                    <p class="text-sm text-text-muted mt-1">All-Purpose Flour is below minimum (15kg/50kg)</p>
                                    <span class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">URGENT</span>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">10 min ago</span>
                        </div>
                    </div>

                    <!-- New Requisition -->
                    <div class="p-6 hover:bg-cream-bg transition border-l-4 border-caramel cursor-pointer" onclick="openNotification('2')">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-caramel flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clipboard-list text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">New Requisition</p>
                                    <p class="text-sm text-text-muted mt-1">REQ-2024-0015 • Baking ingredients • $450</p>
                                    <span class="inline-block mt-2 px-2 py-1 bg-caramel/20 text-caramel text-xs font-bold">PENDING</span>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">25 min ago</span>
                        </div>
                    </div>

                    <!-- Delivery -->
                    <div class="p-6 hover:bg-cream-bg transition border-l-4 border-green-500 cursor-pointer" onclick="openNotification('3')">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-green-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">Delivery Completed</p>
                                    <p class="text-sm text-text-muted mt-1">PO-2024-0456 from Baker's Supply Co.</p>
                                    <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">DELIVERED</span>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">1 hour ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent -->
            <div class="bg-white border-2 border-border-soft">
                <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                    <h3 class="font-display text-xl font-bold text-text-dark">Recent</h3>
                </div>
                
                <div class="divide-y divide-border-soft">
                    <div class="p-6 hover:bg-cream-bg transition cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-8 h-8 bg-orange-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">Low Stock Warning</p>
                                    <p class="text-sm text-text-muted mt-1">Fresh Eggs (8/10 trays)</p>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">2 hours ago</span>
                        </div>
                    </div>

                    <div class="p-6 hover:bg-cream-bg transition cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-8 h-8 bg-blue-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check-circle text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">Requisition Approved</p>
                                    <p class="text-sm text-text-muted mt-1">REQ-2024-0014 for packaging</p>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">3 hours ago</span>
                        </div>
                    </div>

                    <div class="p-6 hover:bg-cream-bg transition cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="w-8 h-8 bg-chocolate flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user-plus text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-text-dark">New Staff Member</p>
                                    <p class="text-sm text-text-muted mt-1">Jessica Rivera joined Pastry</p>
                                </div>
                            </div>
                            <span class="text-xs text-text-muted whitespace-nowrap">5 hours ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Review Requisitions
                    </button>

                    <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                        <i class="fas fa-warehouse mr-2 text-chocolate"></i>
                        Check Stock
                    </button>

                    <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                        <i class="fas fa-truck mr-2 text-chocolate"></i>
                        Track Deliveries
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">By Category</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500"></div>
                            <span class="text-sm text-text-dark">Urgent</span>
                        </div>
                        <span class="text-sm font-bold text-text-dark">3</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-caramel"></div>
                            <span class="text-sm text-text-dark">Requisitions</span>
                        </div>
                        <span class="text-sm font-bold text-text-dark">12</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-orange-500"></div>
                            <span class="text-sm text-text-dark">Stock</span>
                        </div>
                        <span class="text-sm font-bold text-text-dark">8</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500"></div>
                            <span class="text-sm text-text-dark">Deliveries</span>
                        </div>
                        <span class="text-sm font-bold text-text-dark">15</span>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Preferences</h3>
                <div class="space-y-3">
                    <label class="flex items-center justify-between">
                        <span class="text-sm text-text-dark">Requisition Alerts</span>
                        <input type="checkbox" checked class="w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between">
                        <span class="text-sm text-text-dark">Stock Warnings</span>
                        <input type="checkbox" checked class="w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between">
                        <span class="text-sm text-text-dark">Delivery Updates</span>
                        <input type="checkbox" checked class="w-4 h-4">
                    </label>
                    <label class="flex items-center justify-between">
                        <span class="text-sm text-text-dark">Email Notifications</span>
                        <input type="checkbox" checked class="w-4 h-4">
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Notification Details</h3>
                <button onclick="closeNotification()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6" id="notificationContent">
            <!-- Content loaded here -->
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeNotification()" class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                Close
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                Take Action
            </button>
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

<script>
    function openNotification(id) {
        document.getElementById('notificationContent').innerHTML = `
            <div class="space-y-4">
                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <p class="font-bold text-text-dark">Critical Stock Alert</p>
                    <p class="text-sm text-text-muted mt-2">All-Purpose Flour is critically low</p>
                </div>

                <div class="border-2 border-border-soft p-4">
                    <h5 class="font-semibold text-text-dark mb-3">Details</h5>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-text-muted">Current Stock:</span>
                            <span class="font-semibold text-red-600">15 kg</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-text-muted">Minimum:</span>
                            <span class="font-semibold">50 kg</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-text-muted">Category:</span>
                            <span class="font-semibold">Baking Ingredients</span>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border-2 border-yellow-200 p-4">
                    <h5 class="font-semibold text-text-dark mb-2">Action Required</h5>
                    <p class="text-sm">Create a purchase order immediately to restock this ingredient.</p>
                </div>
            </div>
        `;
        document.getElementById('notificationModal').classList.remove('hidden');
    }

    function closeNotification() {
        document.getElementById('notificationModal').classList.add('hidden');
    }

    function markAllAsRead() {
        if (confirm('Mark all notifications as read?')) {
            alert('All notifications marked as read!');
        }
    }

    document.getElementById('notificationModal').addEventListener('click', function(e) {
        if (e.target === this) closeNotification();
    });
</script>
@endsection