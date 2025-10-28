@extends('Admin.layout.app')

@section('title', 'Inventory - WellKenz ERP')

@section('breadcrumb', 'Inventory Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Inventory</h1>
            <p class="text-text-muted mt-2">Track stock levels and manage inventory</p>
        </div>
        <div class="flex items-center space-x-3">
            <button class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
                <i class="fas fa-plus-circle mr-2"></i>
                New Item
            </button>
            <button class="px-4 py-2 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition font-semibold">
                <i class="fas fa-exchange-alt mr-2"></i>
                Adjustment
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">2,347</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Value</p>
            <p class="text-3xl font-bold text-text-dark mt-2">$84,520</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Categories</p>
            <p class="text-3xl font-bold text-text-dark mt-2">18</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Inventory Table -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">Inventory Items</h3>
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Min</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft">
                        <!-- Low Stock -->
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                                <p class="text-xs text-text-muted">SKU: FLR-001</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">Baking</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">15 kg</p>
                                <div class="w-20 bg-gray-200 h-1.5 mt-1">
                                    <div class="bg-red-500 h-1.5" style="width: 15%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">50 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">LOW</span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openItemDetail('FLR-001')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                    View
                                </button>
                            </td>
                        </tr>

                        <!-- Medium Stock -->
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Fresh Eggs</p>
                                <p class="text-xs text-text-muted">SKU: EGG-001</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">Dairy</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">8 trays</p>
                                <div class="w-20 bg-gray-200 h-1.5 mt-1">
                                    <div class="bg-yellow-500 h-1.5" style="width: 65%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">10 trays</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">MEDIUM</span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openItemDetail('EGG-001')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                    View
                                </button>
                            </td>
                        </tr>

                        <!-- In Stock -->
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Cake Boxes (Large)</p>
                                <p class="text-xs text-text-muted">SKU: BX-012</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold">Packaging</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">245 units</p>
                                <div class="w-20 bg-gray-200 h-1.5 mt-1">
                                    <div class="bg-green-500 h-1.5" style="width: 85%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">100 units</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">IN STOCK</span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openItemDetail('BX-012')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                    View
                                </button>
                            </td>
                        </tr>

                        <!-- Out of Stock -->
                        <tr class="hover:bg-cream-bg transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                                <p class="text-xs text-text-muted">SKU: CHC-005</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">Baking</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">0 kg</p>
                                <div class="w-20 bg-gray-200 h-1.5 mt-1">
                                    <div class="bg-gray-400 h-1.5" style="width: 0%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">10 kg</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold">OUT</span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openItemDetail('CHC-005')" class="px-3 py-1 bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition">
                                    Urgent
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-text-muted">Showing 1 to 4 of 2,347 items</p>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Previous</button>
                        <button class="px-3 py-1 bg-caramel text-white">1</button>
                        <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">2</button>
                        <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Categories -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Categories</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 border-2 border-border-soft hover:border-caramel transition cursor-pointer">
                        <span class="font-semibold text-text-dark">Baking Ingredients</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold">156</span>
                    </div>

                    <div class="flex justify-between items-center p-3 border-2 border-border-soft hover:border-caramel transition cursor-pointer">
                        <span class="font-semibold text-text-dark">Dairy & Eggs</span>
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">45</span>
                    </div>

                    <div class="flex justify-between items-center p-3 border-2 border-border-soft hover:border-caramel transition cursor-pointer">
                        <span class="font-semibold text-text-dark">Packaging</span>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-bold">89</span>
                    </div>

                    <div class="flex justify-between items-center p-3 border-2 border-border-soft hover:border-caramel transition cursor-pointer">
                        <span class="font-semibold text-text-dark">Equipment</span>
                        <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-bold">67</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white border-2 border-border-soft p-6">
                <h3 class="font-display text-xl font-bold text-text-dark mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                        <i class="fas fa-barcode mr-2"></i>
                        Stock Take
                    </button>

                    <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                        <i class="fas fa-file-export mr-2 text-chocolate"></i>
                        Export Report
                    </button>

                    <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                        <i class="fas fa-chart-bar mr-2 text-chocolate"></i>
                        Stock Analytics
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movements -->
    <div class="bg-white border-2 border-border-soft">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <h3 class="font-display text-xl font-bold text-text-dark">Recent Stock Movements</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 15, 2024</p>
                            <p class="text-xs text-text-muted">10:30 AM</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">All-Purpose Flour</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">Usage</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-red-600">-5 kg</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Batch #245</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Sarah Martinez</p>
                        </td>
                    </tr>

                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 14, 2024</p>
                            <p class="text-xs text-text-muted">2:15 PM</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Cake Boxes</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold">Receipt</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-green-600">+200 units</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">PO-2024-0456</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">System</p>
                        </td>
                    </tr>

                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 14, 2024</p>
                            <p class="text-xs text-text-muted">9:45 AM</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Fresh Eggs</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">Usage</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-red-600">-2 trays</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Morning Baking</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Mike Wilson</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Item Modal -->
<div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Item Details</h3>
                <button onclick="closeItemDetail()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6" id="itemContent">
            <!-- Content loaded here -->
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeItemDetail()" class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                Close
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                Edit Item
            </button>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
    
    .font-display { font-family: 'Playfair Display', serif; }
    .cream-bg { background-color: #faf7f3; }
    .text-dark { color: #1a1410; }
    .text-muted { color: #8b7355; }
    .chocolate { background-color: #3d2817; }
    .caramel { background-color: #c48d3f; }
    .caramel-dark { background-color: #a67332; }
    .border-soft { border-color: #e8dfd4; }
</style>

<script>
    function openItemDetail(sku) {
        document.getElementById('itemContent').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">SKU</p>
                        <p class="text-lg font-bold text-text-dark">${sku}</p>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Current Stock</p>
                        <p class="text-lg font-bold text-text-dark">15 kg</p>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Status</p>
                        <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-bold mt-1">LOW STOCK</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="border-2 border-border-soft p-6">
                        <h4 class="font-display text-lg font-bold text-text-dark mb-4">Basic Info</h4>
                        <div class="space-y-2">
                            <p><span class="text-text-muted">Name:</span> <span class="font-semibold">All-Purpose Flour</span></p>
                            <p><span class="text-text-muted">Category:</span> <span class="font-semibold">Baking</span></p>
                            <p><span class="text-text-muted">Unit:</span> <span class="font-semibold">Kilograms (kg)</span></p>
                            <p><span class="text-text-muted">Supplier:</span> <span class="font-semibold">Baker's Supply Co.</span></p>
                        </div>
                    </div>

                    <div class="border-2 border-border-soft p-6">
                        <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Levels</h4>
                        <div class="space-y-2">
                            <p><span class="text-text-muted">Current:</span> <span class="font-semibold">15 kg</span></p>
                            <p><span class="text-text-muted">Minimum:</span> <span class="font-semibold">50 kg</span></p>
                            <p><span class="text-text-muted">Maximum:</span> <span class="font-semibold">200 kg</span></p>
                            <p><span class="text-text-muted">Unit Cost:</span> <span class="font-semibold">$1.20/kg</span></p>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-border-soft p-6">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Recent Movements</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <div>
                                <p class="text-sm font-semibold">Usage - Production</p>
                                <p class="text-xs text-text-muted">Dec 15, 2024 - Batch #245</p>
                            </div>
                            <span class="text-sm font-bold text-red-600">-5 kg</span>
                        </div>
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <div>
                                <p class="text-sm font-semibold">Receipt - Purchase</p>
                                <p class="text-xs text-text-muted">Dec 10, 2024 - PO-0456</p>
                            </div>
                            <span class="text-sm font-bold text-green-600">+50 kg</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('itemModal').classList.remove('hidden');
    }

    function closeItemDetail() {
        document.getElementById('itemModal').classList.add('hidden');
    }

    document.getElementById('itemModal').addEventListener('click', function(e) {
        if (e.target === this) closeItemDetail();
    });
</script>
@endsection