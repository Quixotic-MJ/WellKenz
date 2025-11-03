@extends('Admin.layout.app')

@section('title', 'Purchase Orders - WellKenz ERP')

@section('breadcrumb', 'Purchase Orders')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Status updated successfully!
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Purchase Orders</h1>
                <p class="text-text-muted mt-2">Manage and track all purchase orders</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 bg-caramel text-white text-sm font-semibold rounded-full">
                    {{ $totalPOs ?? '18' }} Active
                </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalValue">$28,450</p>
                <p class="text-xs text-text-muted mt-1">All purchase orders</p>
            </div>

            <div class="bg-white border-2 border-blue-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Delivery</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="pendingDelivery">6</p>
                <p class="text-xs text-blue-600 mt-1">Awaiting shipment</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Delivered</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="deliveredCount">8</p>
                <p class="text-xs text-green-600 mt-1">This month</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">In Transit</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="inTransitCount">4</p>
                <p class="text-xs text-orange-600 mt-1">On the way</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center space-x-6">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Delivery Status</label>
                    <select onchange="filterPurchaseOrders()" id="statusFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-40">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Supplier Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Supplier</label>
                    <select onchange="filterPurchaseOrders()" id="supplierFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-48">
                        <option value="all">All Suppliers</option>
                        <option value="bakers_supply">Baker's Supply Co.</option>
                        <option value="flour_mill">Flour Mill Inc.</option>
                        <option value="sweet_ingredients">Sweet Ingredients Ltd.</option>
                        <option value="pack_pro">PackPro Solutions</option>
                        <option value="dairy_fresh">Dairy Fresh Co.</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label class="block text-sm font-semibold text-text-dark mb-2">Date Range</label>
                    <select onchange="filterPurchaseOrders()" id="dateFilter"
                        class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-48">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                    </select>
                </div>

                <!-- Reset Filters -->
                <div class="flex items-end">
                    <button onclick="resetFilters()"
                        class="px-4 py-2 border-2 border-border-soft hover:border-chocolate transition text-text-dark font-semibold">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">All Purchase Orders</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search purchase orders..." onkeyup="searchPurchaseOrders(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="purchaseOrdersTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">PO Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Delivery Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Linked Requisition</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Order Date</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">ETA</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="purchaseOrdersTableBody">
                        <!-- Sample Purchase Order Data -->
                        <tr class="hover:bg-cream-bg transition po-row" 
                            data-status="pending" 
                            data-supplier="bakers_supply"
                            data-date="today">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PO-2024-0456</p>
                                <p class="text-xs text-text-muted">Baking Ingredients</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Baker's Supply Co.</p>
                                <p class="text-xs text-text-muted">John Davis</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$2,450.00</p>
                                <p class="text-xs text-text-muted">15 items</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    PENDING
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">REQ-2024-0012</p>
                                <p class="text-xs text-text-muted">Maria Garcia</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">10:30 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Oct 15, 2024</p>
                                <p class="text-xs text-text-muted">3 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openPODetailsModal(1)"
                                    class="px-4 py-2 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded-lg">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition po-row" 
                            data-status="in_transit" 
                            data-supplier="flour_mill"
                            data-date="yesterday">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PO-2024-0457</p>
                                <p class="text-xs text-text-muted">Flour & Grains</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Flour Mill Inc.</p>
                                <p class="text-xs text-text-muted">Sarah Wilson</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$1,850.75</p>
                                <p class="text-xs text-text-muted">8 items</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    IN TRANSIT
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">REQ-2024-0009</p>
                                <p class="text-xs text-text-muted">Production Dept</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">2:15 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Oct 14, 2024</p>
                                <p class="text-xs text-text-success">Tomorrow</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openPODetailsModal(2)"
                                    class="px-4 py-2 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded-lg">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition po-row" 
                            data-status="delivered" 
                            data-supplier="sweet_ingredients"
                            data-date="week">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PO-2024-0458</p>
                                <p class="text-xs text-text-muted">Sweeteners & Flavors</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Sweet Ingredients Ltd.</p>
                                <p class="text-xs text-text-muted">Mike Johnson</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$3,200.50</p>
                                <p class="text-xs text-text-muted">12 items</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    DELIVERED
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">REQ-2024-0011</p>
                                <p class="text-xs text-text-muted">John Smith</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">3 days ago</p>
                                <p class="text-xs text-text-muted">9:45 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Oct 12, 2024</p>
                                <p class="text-xs text-text-success">Completed</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openPODetailsModal(3)"
                                    class="px-4 py-2 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded-lg">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition po-row" 
                            data-status="confirmed" 
                            data-supplier="pack_pro"
                            data-date="today">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PO-2024-0459</p>
                                <p class="text-xs text-text-muted">Packaging Materials</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PackPro Solutions</p>
                                <p class="text-xs text-text-muted">Lisa Chen</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$1,230.25</p>
                                <p class="text-xs text-text-muted">5 items</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">
                                    CONFIRMED
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">REQ-2024-0013</p>
                                <p class="text-xs text-text-muted">Emily Chen</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">8:30 AM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Oct 18, 2024</p>
                                <p class="text-xs text-text-muted">6 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openPODetailsModal(4)"
                                    class="px-4 py-2 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded-lg">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition po-row" 
                            data-status="pending" 
                            data-supplier="dairy_fresh"
                            data-date="yesterday">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">PO-2024-0460</p>
                                <p class="text-xs text-text-muted">Dairy Products</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Dairy Fresh Co.</p>
                                <p class="text-xs text-text-muted">Robert Brown</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">$980.00</p>
                                <p class="text-xs text-text-muted">7 items</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    PENDING
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">REQ-2024-0014</p>
                                <p class="text-xs text-text-muted">David Brown</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">4:20 PM</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Oct 16, 2024</p>
                                <p class="text-xs text-text-muted">4 days</p>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openPODetailsModal(5)"
                                    class="px-4 py-2 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded-lg">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">5</span> of 18 purchase orders</p>
            </div>
        </div>
    </div>

    <!-- Purchase Order Details Modal -->
    <div id="poDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Purchase Order Details</h3>
                    <button onclick="closePODetailsModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- PO Header -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <h4 class="text-lg font-bold text-text-dark mb-4">Order Information</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">PO Reference:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailPoRef">PO-2024-0456</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Supplier:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailSupplier">Baker's Supply Co.</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Order Date:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailOrderDate">Oct 12, 2024</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Linked Requisition:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailRequisition">REQ-2024-0012</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-text-dark mb-4">Delivery Information</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Delivery Status:</span>
                                <span class="text-sm font-bold" id="detailStatus">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">PENDING</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Estimated Delivery:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailETA">Oct 15, 2024</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Actual Delivery:</span>
                                <span class="text-sm font-bold text-text-muted" id="detailActualDelivery">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-text-muted">Tracking Number:</span>
                                <span class="text-sm font-bold text-text-dark" id="detailTracking">TRK-784512</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PO Items -->
                <div class="mb-8">
                    <h4 class="text-lg font-bold text-text-dark mb-4">Order Items</h4>
                    <div class="bg-gray-50 rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-text-muted uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="poItemsTable">
                                <!-- Items will be populated by JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr class="bg-white border-t-2 border-gray-200">
                                    <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-text-muted">Subtotal:</td>
                                    <td class="px-4 py-3 text-sm font-bold text-text-dark" id="detailSubtotal">$2,250.00</td>
                                </tr>
                                <tr class="bg-white">
                                    <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-text-muted">Shipping:</td>
                                    <td class="px-4 py-3 text-sm font-bold text-text-dark" id="detailShipping">$200.00</td>
                                </tr>
                                <tr class="bg-white">
                                    <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-text-muted">Total Amount:</td>
                                    <td class="px-4 py-3 text-sm font-bold text-text-dark text-lg" id="detailTotal">$2,450.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Delivery Progress -->
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-text-dark mb-4">Delivery Progress</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-semibold text-text-dark">Order Placed</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-semibold text-text-dark">Confirmed</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="text-sm font-semibold text-text-dark">In Transit</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-gray-300 rounded-full"></div>
                                <span class="text-sm font-semibold text-text-muted">Delivered</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-caramel h-2 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t-2 border-border-soft">
                    <button onclick="closePODetailsModal()"
                        class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                        Close
                    </button>
                    <button onclick="updatePOStatus()" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                        Update Status
                    </button>
                    <button onclick="downloadPO()" class="px-6 py-2 bg-blue-500 text-white hover:bg-blue-600 transition">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </button>
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

        .bg-caramel {
            background-color: #c48d3f;
        }

        .bg-caramel-dark {
            background-color: #a67332;
        }

        .bg-chocolate {
            background-color: #3d2817;
        }

        .border-border-soft {
            border-color: #e8dfd4;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        // Sample purchase order data
        const purchaseOrders = {
            1: {
                poRef: "PO-2024-0456",
                supplier: "Baker's Supply Co.",
                contact: "John Davis",
                orderDate: "Oct 12, 2024",
                time: "10:30 AM",
                requisition: "REQ-2024-0012",
                requester: "Maria Garcia",
                status: "pending",
                eta: "Oct 15, 2024",
                actualDelivery: "-",
                tracking: "TRK-784512",
                items: [
                    { name: "All-Purpose Flour", description: "High-quality bread flour", quantity: "25 kg", unitPrice: "$45.00", total: "$1,125.00" },
                    { name: "Whole Wheat Flour", description: "Organic whole wheat", quantity: "15 kg", unitPrice: "$52.00", total: "$780.00" },
                    { name: "Baking Powder", description: "Aluminum-free", quantity: "5 kg", unitPrice: "$12.50", total: "$62.50" },
                    { name: "Active Dry Yeast", description: "Instant yeast packets", quantity: "2 kg", unitPrice: "$28.00", total: "$56.00" },
                    { name: "Salt", description: "Sea salt fine grain", quantity: "10 kg", unitPrice: "$8.50", total: "$85.00" }
                ],
                subtotal: "$2,250.00",
                shipping: "$200.00",
                total: "$2,450.00"
            },
            2: {
                poRef: "PO-2024-0457",
                supplier: "Flour Mill Inc.",
                contact: "Sarah Wilson",
                orderDate: "Oct 11, 2024",
                time: "2:15 PM",
                requisition: "REQ-2024-0009",
                requester: "Production Dept",
                status: "in_transit",
                eta: "Oct 14, 2024",
                actualDelivery: "-",
                tracking: "TRK-784513",
                items: [
                    { name: "Bread Flour", description: "Professional grade", quantity: "50 kg", unitPrice: "$28.00", total: "$1,400.00" },
                    { name: "Rye Flour", description: "Dark rye flour", quantity: "10 kg", unitPrice: "$35.00", total: "$350.00" },
                    { name: "Semolina", description: "Durum wheat semolina", quantity: "5 kg", unitPrice: "$20.50", total: "$102.50" }
                ],
                subtotal: "$1,852.50",
                shipping: "$198.25",
                total: "$2,050.75"
            }
        };

        let currentPOId = null;

        // Filter Functions
        function filterPurchaseOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const supplierFilter = document.getElementById('supplierFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            const rows = document.querySelectorAll('.po-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const supplier = row.getAttribute('data-supplier');
                const date = row.getAttribute('data-date');

                let statusMatch = statusFilter === 'all' || status === statusFilter;
                let supplierMatch = supplierFilter === 'all' || supplier === supplierFilter;
                let dateMatch = dateFilter === 'all' || shouldShowByDate(date, dateFilter);

                if (statusMatch && supplierMatch && dateMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        function shouldShowByDate(date, filter) {
            switch(filter) {
                case 'today':
                    return date === 'today';
                case 'week':
                    return date === 'today' || date === 'yesterday' || date === 'week';
                case 'month':
                    return true;
                case 'quarter':
                    return true;
                default:
                    return true;
            }
        }

        function resetFilters() {
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('supplierFilter').value = 'all';
            document.getElementById('dateFilter').value = 'all';
            filterPurchaseOrders();
            showMessage('Filters reset successfully!', 'success');
        }

        // Search functionality
        function searchPurchaseOrders(query) {
            const rows = document.querySelectorAll('.po-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query.toLowerCase()) || query === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Modal Functions
        function openPODetailsModal(poId) {
            currentPOId = poId;
            const po = purchaseOrders[poId];
            
            if (po) {
                // Set basic information
                document.getElementById('detailPoRef').textContent = po.poRef;
                document.getElementById('detailSupplier').textContent = po.supplier;
                document.getElementById('detailOrderDate').textContent = po.orderDate;
                document.getElementById('detailRequisition').textContent = po.requisition;
                document.getElementById('detailETA').textContent = po.eta;
                document.getElementById('detailActualDelivery').textContent = po.actualDelivery;
                document.getElementById('detailTracking').textContent = po.tracking;
                
                // Set status with appropriate badge
                const statusElement = document.getElementById('detailStatus');
                statusElement.innerHTML = getStatusBadge(po.status);
                
                // Set financial information
                document.getElementById('detailSubtotal').textContent = po.subtotal;
                document.getElementById('detailShipping').textContent = po.shipping;
                document.getElementById('detailTotal').textContent = po.total;
                
                // Populate items table
                const itemsTable = document.getElementById('poItemsTable');
                itemsTable.innerHTML = '';
                
                po.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-100';
                    row.innerHTML = `
                        <td class="px-4 py-3 text-sm font-bold text-text-dark">${item.name}</td>
                        <td class="px-4 py-3 text-sm text-text-muted">${item.description}</td>
                        <td class="px-4 py-3 text-sm text-text-dark">${item.quantity}</td>
                        <td class="px-4 py-3 text-sm text-text-dark">${item.unitPrice}</td>
                        <td class="px-4 py-3 text-sm font-bold text-text-dark">${item.total}</td>
                    `;
                    itemsTable.appendChild(row);
                });
                
                document.getElementById('poDetailsModal').classList.remove('hidden');
            }
        }

        function closePODetailsModal() {
            document.getElementById('poDetailsModal').classList.add('hidden');
            currentPOId = null;
        }

        function getStatusBadge(status) {
            const statusConfig = {
                'pending': { class: 'bg-yellow-100 text-yellow-700', text: 'PENDING' },
                'confirmed': { class: 'bg-purple-100 text-purple-700', text: 'CONFIRMED' },
                'in_transit': { class: 'bg-blue-100 text-blue-700', text: 'IN TRANSIT' },
                'delivered': { class: 'bg-green-100 text-green-700', text: 'DELIVERED' },
                'cancelled': { class: 'bg-red-100 text-red-700', text: 'CANCELLED' }
            };
            
            const config = statusConfig[status] || statusConfig.pending;
            return `<span class="px-2 py-1 ${config.class} text-xs font-bold rounded-full">${config.text}</span>`;
        }

        // Action Functions
        function updatePOStatus() {
            showMessage('Purchase order status updated successfully!', 'success');
            // In real app, would open status update modal
        }

        function downloadPO() {
            showMessage('Purchase order PDF download started!', 'success');
            // In real app, would trigger PDF download
        }

        // Utility Functions
        function showMessage(message, type) {
            const messageDiv = document.getElementById('successMessage');
            messageDiv.textContent = message;
            messageDiv.classList.remove('hidden');
            
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }

        // Close modal when clicking outside
        document.getElementById('poDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) closePODetailsModal();
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePODetailsModal();
            }
        });

        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            filterPurchaseOrders();
        });
    </script>
@endsection