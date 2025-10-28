@extends('Admin.layout.app')

@section('title', 'Purchasing - WellKenz ERP')

@section('breadcrumb', 'Purchasing Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Purchase Orders</h1>
            <p class="text-text-muted mt-2">Manage supplier orders and deliveries</p>
        </div>
        <button class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
            <i class="fas fa-plus-circle mr-2"></i>
            New Purchase Order
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Expenses</p>
            <p class="text-3xl font-bold text-text-dark mt-2">$48,250</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active POs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">24</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Delivery</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active Suppliers</p>
            <p class="text-3xl font-bold text-text-dark mt-2">15</p>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white border-2 border-border-soft">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-xl font-bold text-text-dark">Purchase Orders</h3>
                <div class="relative">
                    <input type="text" placeholder="Search..." 
                        class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">PO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Order Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <!-- Delivered -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0456</p>
                            <p class="text-xs text-text-muted">REQ-2024-0012</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Baker's Supply Co.</p>
                            <p class="text-xs text-text-muted">Preferred</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Flour, Sugar, Chocolate</p>
                            <p class="text-xs text-text-muted">3 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">$2,450.00</p>
                            <p class="text-xs text-green-600">Paid</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 10, 2024</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold">
                                DELIVERED
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openPODetail('PO-2024-0456')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                View
                            </button>
                        </td>
                    </tr>

                    <!-- In Transit -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0457</p>
                            <p class="text-xs text-text-muted">REQ-2024-0011</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">PackPro Inc.</p>
                            <p class="text-xs text-text-muted">Packaging</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Boxes, Wrappers, Labels</p>
                            <p class="text-xs text-text-muted">5 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">$1,230.00</p>
                            <p class="text-xs text-orange-600">Pending</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 12, 2024</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold">
                                IN TRANSIT
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openPODetail('PO-2024-0457')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                Track
                            </button>
                        </td>
                    </tr>

                    <!-- Processing -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0458</p>
                            <p class="text-xs text-text-muted">REQ-2024-0010</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">KitchenTech Ltd.</p>
                            <p class="text-xs text-text-muted">Equipment</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Mixers, Ovens, Tools</p>
                            <p class="text-xs text-text-muted">8 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">$8,750.00</p>
                            <p class="text-xs text-orange-600">50% Paid</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 14, 2024</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">
                                PROCESSING
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openPODetail('PO-2024-0458')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                View
                            </button>
                        </td>
                    </tr>

                    <!-- Delayed -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">PO-2024-0459</p>
                            <p class="text-xs text-text-muted">REQ-2024-0009</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Fresh Ingredients Co.</p>
                            <p class="text-xs text-text-muted">Perishables</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dairy, Eggs, Fruits</p>
                            <p class="text-xs text-text-muted">6 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">$980.00</p>
                            <p class="text-xs text-orange-600">Pending</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 8, 2024</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-bold">
                                DELAYED
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openPODetail('PO-2024-0459')" class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                Escalate
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Showing 1 to 4 of 24 purchase orders</p>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-caramel text-white">1</button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">
                        2
                    </button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Supplier Performance -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-4">Top Suppliers</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border-2 border-border-soft">
                    <div>
                        <p class="text-sm font-semibold text-text-dark">Baker's Supply Co.</p>
                        <p class="text-xs text-text-muted">On-time: 98%</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-text-dark">$12,450</p>
                        <p class="text-xs text-text-muted">Total Spent</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border-2 border-border-soft">
                    <div>
                        <p class="text-sm font-semibold text-text-dark">PackPro Inc.</p>
                        <p class="text-xs text-text-muted">On-time: 92%</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-text-dark">$8,230</p>
                        <p class="text-xs text-text-muted">Total Spent</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border-2 border-border-soft">
                    <div>
                        <p class="text-sm font-semibold text-text-dark">KitchenTech Ltd.</p>
                        <p class="text-xs text-text-muted">On-time: 85%</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-text-dark">$15,750</p>
                        <p class="text-xs text-text-muted">Total Spent</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-4">Monthly Expenses</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-text-dark">December 2024</span>
                        <span class="text-sm font-bold text-text-dark">$18,250</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-caramel h-2" style="width: 85%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-text-dark">November 2024</span>
                        <span class="text-sm font-bold text-text-dark">$15,890</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-chocolate h-2" style="width: 70%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-text-dark">October 2024</span>
                        <span class="text-sm font-bold text-text-dark">$14,110</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2">
                        <div class="bg-green-500 h-2" style="width: 62%"></div>
                    </div>
                </div>

                <div class="pt-4 border-t-2 border-border-soft">
                    <div class="flex justify-between">
                        <span class="text-sm font-bold text-text-dark">Quarter Total</span>
                        <span class="text-lg font-bold text-caramel">$48,250</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PO Detail Modal -->
<div id="poModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Purchase Order Details</h3>
                <button onclick="closePODetail()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6" id="poContent">
            <!-- Content loaded here -->
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closePODetail()" class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                Close
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                Print PO
            </button>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    
    .font-display {
        font-family: 'Playfair Display', serif;
    }

    .cream-bg { background-color: #faf7f3; }
    .text-dark { color: #1a1410; }
    .text-muted { color: #8b7355; }
    .chocolate { background-color: #3d2817; }
    .caramel { background-color: #c48d3f; }
    .caramel-dark { background-color: #a67332; }
    .border-soft { border-color: #e8dfd4; }
</style>

<script>
    function openPODetail(poNumber) {
        document.getElementById('poContent').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">PO Number</p>
                        <p class="text-lg font-bold text-text-dark">${poNumber}</p>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Status</p>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold mt-1">IN TRANSIT</span>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Total</p>
                        <p class="text-lg font-bold text-text-dark">$1,230.00</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="border-2 border-border-soft p-6">
                        <h4 class="font-display text-lg font-bold text-text-dark mb-4">Supplier</h4>
                        <div class="space-y-2">
                            <p><span class="text-text-muted">Company:</span> <span class="font-semibold">PackPro Inc.</span></p>
                            <p><span class="text-text-muted">Contact:</span> <span class="font-semibold">John Smith</span></p>
                            <p><span class="text-text-muted">Phone:</span> <span class="font-semibold">(555) 123-4567</span></p>
                        </div>
                    </div>

                    <div class="border-2 border-border-soft p-6">
                        <h4 class="font-display text-lg font-bold text-text-dark mb-4">Delivery</h4>
                        <div class="space-y-2">
                            <p><span class="text-text-muted">Order Date:</span> <span class="font-semibold">Dec 12, 2024</span></p>
                            <p><span class="text-text-muted">Expected:</span> <span class="font-semibold">Dec 18, 2024</span></p>
                            <p><span class="text-text-muted">Tracking:</span> <span class="font-semibold">TRK-7845-2291</span></p>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-border-soft p-6">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Items</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <span>Cake Boxes (Large) - 200 units</span>
                            <span class="font-bold">$500.00</span>
                        </div>
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <span>Gift Wrap - 100 units</span>
                            <span class="font-bold">$180.00</span>
                        </div>
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <span>Product Labels - 500 units</span>
                            <span class="font-bold">$225.00</span>
                        </div>
                    </div>
                    <div class="flex justify-between pt-4 mt-4 border-t-2 border-border-soft">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-2xl font-bold text-caramel">$1,230.00</span>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('poModal').classList.remove('hidden');
    }

    function closePODetail() {
        document.getElementById('poModal').classList.add('hidden');
    }

    document.getElementById('poModal').addEventListener('click', function(e) {
        if (e.target === this) closePODetail();
    });
</script>
@endsection