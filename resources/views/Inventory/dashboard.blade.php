@extends('Inventory.layout.app')

@section('title', 'Stock Management - WellKenz ERP')

@section('breadcrumb', 'Stock Management')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Stock Management Dashboard</h1>
                <p class="text-text-muted mt-2">Monitor stock levels, track movements, and manage inventory in real-time.</p>
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
                    <i class="fas fa-boxes text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Stock Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Low Stock Alerts</p>
            <p class="text-3xl font-bold text-text-dark mt-2">12</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-box-open text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Out of Stock</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Critical Items</p>
            <p class="text-3xl font-bold text-text-dark mt-2">5</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Stock Movements -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Stock Movements</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Movements</option>
                        <option>Stock In</option>
                        <option>Stock Out</option>
                        <option>Adjustments</option>
                    </select>
                    <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- Stock Movement Item - Stock In -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Flour - All Purpose (25kg bags)</p>
                        <p class="text-xs text-text-muted mt-1">Baking Ingredients • STK-FLOUR-001 • Warehouse A</p>
                        <p class="text-xs text-text-muted mt-1">Stock In: +50 units • Updated: Today, 08:30 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ New stock level: 120 units</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">STOCK IN</span>
                </div>

                <!-- Stock Movement Item - Stock Out -->
                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chips - Dark (10kg)</p>
                        <p class="text-xs text-text-muted mt-1">Baking Ingredients • STK-CHOC-015 • Production</p>
                        <p class="text-xs text-text-muted mt-1">Stock Out: -15 units • Updated: Today, 07:45 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-blue-600 font-bold">↳ Used for: Chocolate Cookie Batch #245</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">STOCK OUT</span>
                </div>

                <!-- Stock Movement Item - Low Stock Alert -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Vanilla Extract - Pure (1L)</p>
                        <p class="text-xs text-text-muted mt-1">Flavorings • STK-VAN-008 • Warehouse B</p>
                        <p class="text-xs text-text-muted mt-1">Current Stock: 8 units • Minimum: 10 units</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-text-muted">Alert: </span>
                            <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded">LOW STOCK</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold">ALERT</span>
                </div>

                <!-- Stock Movement Item - Stock In -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Butter - Unsalted (1kg blocks)</p>
                        <p class="text-xs text-text-muted mt-1">Dairy • STK-BUT-003 • Cold Storage</p>
                        <p class="text-xs text-text-muted mt-1">Stock In: +80 units • Updated: Yesterday, 03:15 PM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Supplier: Fresh Dairy Co.</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">STOCK IN</span>
                </div>

                <!-- Stock Movement Item - Critical Alert -->
                <div class="flex items-start justify-between p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Almond Flour - Premium (5kg)</p>
                        <p class="text-xs text-text-muted mt-1">Specialty Ingredients • STK-ALM-012 • Warehouse A</p>
                        <p class="text-xs text-text-muted mt-1">Current Stock: 2 units • Minimum: 15 units</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-red-600 font-bold">🚨 CRITICAL - Reorder immediately</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold">CRITICAL</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <button onclick="openStockModal()" class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Stock Item
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-edit mr-2"></i>
                    Stock Adjustment
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-download mr-2 text-chocolate"></i>
                    Stock Report
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-bell mr-2 text-chocolate"></i>
                    Set Alerts
                </button>
            </div>

            <!-- Stock Alerts -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Alerts</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded">
                        <div>
                            <p class="text-sm font-bold text-text-dark">Almond Flour</p>
                            <p class="text-xs text-text-muted">2 units left</p>
                        </div>
                        <span class="px-2 py-1 bg-red-500 text-white text-xs font-bold rounded">CRITICAL</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <div>
                            <p class="text-sm font-bold text-text-dark">Vanilla Extract</p>
                            <p class="text-xs text-text-muted">8 units left</p>
                        </div>
                        <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded">LOW</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <div>
                            <p class="text-sm font-bold text-text-dark">Cocoa Powder</p>
                            <p class="text-xs text-text-muted">12 units left</p>
                        </div>
                        <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded">LOW</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-200 rounded">
                        <div>
                            <p class="text-sm font-bold text-text-dark">Baking Powder</p>
                            <p class="text-xs text-text-muted">5 units left</p>
                        </div>
                        <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold rounded">MEDIUM</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Health</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-green-50 rounded">
                        <p class="text-2xl font-bold text-text-dark">87%</p>
                        <p class="text-xs text-text-muted">In Stock</p>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded">
                        <p class="text-2xl font-bold text-text-dark">5%</p>
                        <p class="text-xs text-text-muted">Critical</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock Activity Timeline -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-stream text-caramel mr-2"></i>
                Recent Stock Activity
            </h3>
            
            <div class="space-y-4">
                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-down text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Flour Stock Received</p>
                        <p class="text-xs text-text-muted mt-1">STK-FLOUR-001 • +50 units (Warehouse A)</p>
                        <p class="text-xs text-text-muted mt-1">Today, 08:30 AM • New total: 120 units</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-up text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chips Used</p>
                        <p class="text-xs text-text-muted mt-1">STK-CHOC-015 • -15 units (Production)</p>
                        <p class="text-xs text-text-muted mt-1">Today, 07:45 AM • Batch #245</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Low Stock Alert</p>
                        <p class="text-xs text-text-muted mt-1">STK-VAN-008 • 8 units remaining</p>
                        <p class="text-xs text-text-muted mt-1">Today, 06:00 AM • Below minimum level</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Butter Delivery</p>
                        <p class="text-xs text-text-muted mt-1">STK-BUT-003 • +80 units (Cold Storage)</p>
                        <p class="text-xs text-text-muted mt-1">Yesterday, 03:15 PM • Fresh Dairy Co.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Categories & Resources -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-boxes text-caramel mr-2"></i>
                Stock Categories & Tools
            </h3>
            
            <div class="space-y-4">
                <!-- Stock Categories -->
                <div>
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Stock Categories</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="p-3 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition text-center rounded">
                            <i class="fas fa-wheat text-blue-600 mb-1"></i>
                            <p class="text-xs font-bold">Flours</p>
                        </button>
                        <button class="p-3 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition text-center rounded">
                            <i class="fas fa-cube text-green-600 mb-1"></i>
                            <p class="text-xs font-bold">Flavorings</p>
                        </button>
                        <button class="p-3 bg-purple-50 border border-purple-200 text-purple-700 hover:bg-purple-100 transition text-center rounded">
                            <i class="fas fa-egg text-purple-600 mb-1"></i>
                            <p class="text-xs font-bold">Dairy</p>
                        </button>
                        <button class="p-3 bg-orange-50 border border-orange-200 text-orange-700 hover:bg-orange-100 transition text-center rounded">
                            <i class="fas fa-cookie text-orange-600 mb-1"></i>
                            <p class="text-xs font-bold">Additives</p>
                        </button>
                    </div>
                </div>

                <!-- Stock Management Tools -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Stock Tools</h4>
                    <div class="space-y-2">
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-calculator text-caramel mr-2"></i>
                            Stock Valuation
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-chart-bar text-caramel mr-2"></i>
                            Stock Reports
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-redo text-caramel mr-2"></i>
                            Reorder Points
                        </button>
                    </div>
                </div>

                <!-- Stock Tips -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-2">Stock Management Tips</h4>
                    <ul class="text-xs text-text-muted space-y-1">
                        <li>• Set reorder points for critical items</li>
                        <li>• Conduct monthly stock counts</li>
                        <li>• Monitor shelf life for perishables</li>
                        <li>• Track stock movement patterns</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Management Modal -->
<div id="stockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-2xl font-bold text-text-dark">Add Stock Item</h3>
            <button onclick="closeStockModal()" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form class="space-y-6">
            <!-- Item Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Item Name *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., All Purpose Flour" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">SKU/Code *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., STK-FLOUR-001" required>
                </div>
            </div>

            <!-- Category & Location -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Category *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="">Select category...</option>
                        <option value="flours">Flours & Grains</option>
                        <option value="flavorings">Flavorings & Extracts</option>
                        <option value="dairy">Dairy Products</option>
                        <option value="additives">Additives & Preservatives</option>
                        <option value="packaging">Packaging Materials</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Storage Location *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="warehouse_a">Warehouse A</option>
                        <option value="warehouse_b">Warehouse B</option>
                        <option value="cold_storage">Cold Storage</option>
                        <option value="production">Production Area</option>
                        <option value="other">Other Location</option>
                    </select>
                </div>
            </div>

            <!-- Stock Levels -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Current Stock *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="0" min="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Minimum Stock *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="10" min="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Maximum Stock</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="100" min="0">
                </div>
            </div>

            <!-- Unit Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Unit of Measure *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="l">Liters (L)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="units">Units</option>
                        <option value="bags">Bags</option>
                        <option value="boxes">Boxes</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Unit Cost</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="0.00" min="0" step="0.01">
                </div>
            </div>

            <!-- Supplier Information -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Supplier</label>
                <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                       placeholder="Primary supplier name">
            </div>

            <!-- Additional Notes -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Notes</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="3" placeholder="Any special storage instructions, shelf life information, or additional notes..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-4 pt-4 border-t border-border-soft">
                <button type="button" onclick="closeStockModal()" class="flex-1 p-3 border-2 border-border-soft text-text-dark hover:bg-gray-50 transition text-center font-semibold rounded">
                    Cancel
                </button>
                <button type="submit" class="flex-1 p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded">
                    <i class="fas fa-save mr-2"></i>
                    Save Stock Item
                </button>
            </div>
        </form>
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

<script>
    function openStockModal() {
        document.getElementById('stockModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('stockModal').addEventListener('click', function(e) {
        if (e.target.id === 'stockModal') {
            closeStockModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockModal();
        }
    });

    // Form submission
    document.querySelector('#stockModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically handle the form submission with AJAX
        alert('Stock item added successfully!');
        closeStockModal();
        // Reset form
        this.reset();
    });
</script>
@endsection