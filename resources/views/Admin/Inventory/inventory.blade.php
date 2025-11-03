@extends('Admin.layout.app')

@section('title', 'Inventory Management - WellKenz ERP')

@section('breadcrumb', 'Inventory Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Stock updated successfully!
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Inventory Management</h1>
                <p class="text-text-muted mt-2">Monitor and manage all inventory items in real-time</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 bg-red-500 text-white text-sm font-semibold rounded-full" id="lowStockCount">
                    {{ $lowStockCount ?? '8' }} Low Stock
                </span>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Items</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalItems">156</p>
                <p class="text-xs text-text-muted mt-1">Across all categories</p>
            </div>

            <div class="bg-white border-2 border-red-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="lowStockItems">8</p>
                <p class="text-xs text-red-600 mt-1">Below reorder level</p>
            </div>

            <div class="bg-white border-2 border-yellow-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Warning</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="warningStock">12</p>
                <p class="text-xs text-yellow-600 mt-1">Near reorder level</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalValue">$28,450</p>
                <p class="text-xs text-text-muted mt-1">Current inventory</p>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Category</label>
                        <select onchange="filterInventory()" id="categoryFilter"
                            class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-40">
                            <option value="all">All Categories</option>
                            <option value="flour_grains">Flour & Grains</option>
                            <option value="sweeteners">Sweeteners</option>
                            <option value="dairy">Dairy</option>
                            <option value="packaging">Packaging</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>

                    <!-- Stock Status Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Stock Status</label>
                        <select onchange="filterInventory()" id="statusFilter"
                            class="border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate transition bg-white min-w-48">
                            <option value="all">All Items</option>
                            <option value="low">Low Stock</option>
                            <option value="warning">Warning</option>
                            <option value="normal">Normal</option>
                            <option value="overstock">Overstock</option>
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

                <!-- Bulk Actions -->
                <div class="flex items-center space-x-3">
                    <button onclick="openBulkStockModal('in')"
                        class="px-4 py-2 bg-green-500 text-white hover:bg-green-600 transition font-semibold">
                        <i class="fas fa-arrow-down mr-2"></i>
                        Bulk Stock In
                    </button>
                    <button onclick="openBulkStockModal('out')"
                        class="px-4 py-2 bg-blue-500 text-white hover:bg-blue-600 transition font-semibold">
                        <i class="fas fa-arrow-up mr-2"></i>
                        Bulk Stock Out
                    </button>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">All Inventory Items</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search inventory..." onkeyup="searchInventory(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="inventoryTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Reorder Level</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="inventoryTableBody">
                        <!-- Sample Inventory Data -->
                        <tr class="hover:bg-cream-bg transition inventory-row bg-red-50" 
                            data-category="flour_grains" 
                            data-status="low">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-wheat-alt text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                                        <p class="text-xs text-text-muted">SKU: FLR-001</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    Flour & Grains
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-lg font-bold text-red-600">15 kg</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: 15%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">50 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">
                                    LOW STOCK
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">2 hours ago</p>
                                <p class="text-xs text-text-muted">by Maria Garcia</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openStockModal('in', 1, 'All-Purpose Flour')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Stock In
                                    </button>
                                    <button onclick="openStockModal('out', 1, 'All-Purpose Flour')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        Stock Out
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition inventory-row bg-yellow-50" 
                            data-category="sweeteners" 
                            data-status="warning">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-brown-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cookie text-brown-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                                        <p class="text-xs text-text-muted">SKU: CHC-002</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-brown-100 text-brown-700 text-xs font-bold rounded-full">
                                    Sweeteners
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-lg font-bold text-yellow-600">8 kg</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 40%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">10 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    WARNING
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Yesterday</p>
                                <p class="text-xs text-text-muted">by John Smith</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openStockModal('in', 2, 'Chocolate Chips')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Stock In
                                    </button>
                                    <button onclick="openStockModal('out', 2, 'Chocolate Chips')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        Stock Out
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition inventory-row" 
                            data-category="dairy" 
                            data-status="normal">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cow text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Unsalted Butter</p>
                                        <p class="text-xs text-text-muted">SKU: BTR-003</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">
                                    Dairy
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-lg font-bold text-green-600">25 kg</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 62%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">15 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    NORMAL
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">Today</p>
                                <p class="text-xs text-text-muted">by Robert Johnson</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openStockModal('in', 3, 'Unsalted Butter')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Stock In
                                    </button>
                                    <button onclick="openStockModal('out', 3, 'Unsalted Butter')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        Stock Out
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition inventory-row" 
                            data-category="packaging" 
                            data-status="normal">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Packaging Boxes</p>
                                        <p class="text-xs text-text-muted">SKU: PKG-004</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    Packaging
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-lg font-bold text-green-600">450 units</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">units</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">200 units</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    NORMAL
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">3 days ago</p>
                                <p class="text-xs text-text-muted">by Sarah Wilson</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openStockModal('in', 4, 'Packaging Boxes')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Stock In
                                    </button>
                                    <button onclick="openStockModal('out', 4, 'Packaging Boxes')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        Stock Out
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition inventory-row bg-green-50" 
                            data-category="flour_grains" 
                            data-status="overstock">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bread-slice text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Whole Wheat Flour</p>
                                        <p class="text-xs text-text-muted">SKU: FLR-005</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">
                                    Flour & Grains
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-lg font-bold text-green-600">120 kg</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 95%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">40 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">
                                    OVERSTOCK
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">1 week ago</p>
                                <p class="text-xs text-text-muted">by David Brown</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openStockModal('in', 5, 'Whole Wheat Flour')"
                                        class="px-3 py-1 bg-green-500 text-white text-xs font-semibold hover:bg-green-600 transition rounded">
                                        Stock In
                                    </button>
                                    <button onclick="openStockModal('out', 5, 'Whole Wheat Flour')"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        Stock Out
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">5</span> of 156 inventory items</p>
            </div>
        </div>
    </div>

    <!-- Stock In/Out Modal -->
    <div id="stockModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-md w-full">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-xl font-bold text-text-dark" id="stockModalTitle">Stock Adjustment</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-caramel rounded-lg flex items-center justify-center" id="stockModalIcon">
                        <i class="fas fa-arrow-down text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-text-dark font-semibold" id="stockItemName"></p>
                        <p class="text-sm text-text-muted">Current Stock: <span id="currentStock">0</span></p>
                    </div>
                </div>

                <form id="stockForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Quantity</label>
                        <input type="number" name="quantity" required min="1"
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Enter quantity">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Reference</label>
                        <input type="text" name="reference"
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="PO number or reference">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Notes</label>
                        <textarea name="notes"
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate resize-none"
                            placeholder="Additional notes..."
                            rows="3"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeStockModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition" id="stockSubmitBtn">
                            Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Stock Modal -->
    <div id="bulkStockModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <h3 class="font-display text-2xl font-bold text-text-dark" id="bulkStockModalTitle">Bulk Stock Adjustment</h3>
            </div>
            <div class="p-6">
                <p class="text-text-dark mb-4">Select items for bulk stock adjustment:</p>
                
                <div class="space-y-3 mb-6">
                    <div class="flex items-center space-x-3 p-3 border-2 border-border-soft rounded-lg">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-caramel">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                            <p class="text-xs text-text-muted">Current: 15 kg</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 p-3 border-2 border-border-soft rounded-lg">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-caramel">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                            <p class="text-xs text-text-muted">Current: 8 kg</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 p-3 border-2 border-border-soft rounded-lg">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-caramel">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Unsalted Butter</p>
                            <p class="text-xs text-text-muted">Current: 25 kg</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Quantity for All Selected Items</label>
                        <input type="number" name="bulk_quantity" required min="1"
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Enter quantity">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Reference</label>
                        <input type="text" name="bulk_reference"
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                            placeholder="Bulk reference number">
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeBulkStockModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="button" onclick="processBulkStock()" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Process Bulk
                        </button>
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

        /* Custom checkbox styling */
        .form-checkbox:checked {
            background-color: #c48d3f;
            border-color: #c48d3f;
        }
    </style>

    <script>
        // Sample inventory data
        const inventoryItems = {
            1: {
                name: "All-Purpose Flour",
                sku: "FLR-001",
                category: "flour_grains",
                currentStock: 15,
                unit: "kg",
                reorderLevel: 50,
                lastUpdated: "2 hours ago",
                updatedBy: "Maria Garcia"
            },
            2: {
                name: "Chocolate Chips",
                sku: "CHC-002",
                category: "sweeteners",
                currentStock: 8,
                unit: "kg",
                reorderLevel: 10,
                lastUpdated: "Yesterday",
                updatedBy: "John Smith"
            },
            3: {
                name: "Unsalted Butter",
                sku: "BTR-003",
                category: "dairy",
                currentStock: 25,
                unit: "kg",
                reorderLevel: 15,
                lastUpdated: "Today",
                updatedBy: "Robert Johnson"
            }
        };

        let currentItemId = null;
        let currentActionType = null;

        // Filter Functions
        function filterInventory() {
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.inventory-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const category = row.getAttribute('data-category');
                const status = row.getAttribute('data-status');

                let categoryMatch = categoryFilter === 'all' || category === categoryFilter;
                let statusMatch = statusFilter === 'all' || status === statusFilter;

                if (categoryMatch && statusMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        function resetFilters() {
            document.getElementById('categoryFilter').value = 'all';
            document.getElementById('statusFilter').value = 'all';
            filterInventory();
            showMessage('Filters reset successfully!', 'success');
        }

        // Search functionality
        function searchInventory(query) {
            const rows = document.querySelectorAll('.inventory-row');
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

        // Stock Modal Functions
        function openStockModal(action, itemId, itemName) {
            currentItemId = itemId;
            currentActionType = action;
            
            const item = inventoryItems[itemId];
            if (item) {
                document.getElementById('stockItemName').textContent = itemName;
                document.getElementById('currentStock').textContent = `${item.currentStock} ${item.unit}`;
                
                const modal = document.getElementById('stockModal');
                const icon = document.getElementById('stockModalIcon');
                const title = document.getElementById('stockModalTitle');
                const submitBtn = document.getElementById('stockSubmitBtn');
                
                if (action === 'in') {
                    title.textContent = 'Stock In';
                    icon.innerHTML = '<i class="fas fa-arrow-down text-white text-lg"></i>';
                    icon.className = 'w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center';
                    submitBtn.textContent = 'Stock In';
                    submitBtn.className = 'px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition';
                } else {
                    title.textContent = 'Stock Out';
                    icon.innerHTML = '<i class="fas fa-arrow-up text-white text-lg"></i>';
                    icon.className = 'w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center';
                    submitBtn.textContent = 'Stock Out';
                    submitBtn.className = 'px-6 py-2 bg-blue-500 text-white hover:bg-blue-600 transition';
                }
                
                document.getElementById('stockForm').reset();
                modal.classList.remove('hidden');
            }
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.add('hidden');
            currentItemId = null;
            currentActionType = null;
        }

        function openBulkStockModal(action) {
            currentActionType = action;
            const modal = document.getElementById('bulkStockModal');
            const title = document.getElementById('bulkStockModalTitle');
            
            if (action === 'in') {
                title.textContent = 'Bulk Stock In';
            } else {
                title.textContent = 'Bulk Stock Out';
            }
            
            modal.classList.remove('hidden');
        }

        function closeBulkStockModal() {
            document.getElementById('bulkStockModal').classList.add('hidden');
            currentActionType = null;
        }

        // Form Handling
        document.getElementById('stockForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const quantity = formData.get('quantity');
            
            let message = '';
            if (currentActionType === 'in') {
                message = `Stock in of ${quantity} units processed successfully!`;
            } else {
                message = `Stock out of ${quantity} units processed successfully!`;
            }
            
            showMessage(message, 'success');
            closeStockModal();
        });

        function processBulkStock() {
            showMessage('Bulk stock adjustment processed successfully!', 'success');
            closeBulkStockModal();
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

        // Close modals when clicking outside
        document.getElementById('stockModal').addEventListener('click', function(e) {
            if (e.target === this) closeStockModal();
        });

        document.getElementById('bulkStockModal').addEventListener('click', function(e) {
            if (e.target === this) closeBulkStockModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStockModal();
                closeBulkStockModal();
            }
        });

        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            filterInventory();
        });
    </script>
@endsection