@extends('Employee.layout.app')

@section('title', 'Baker Requisitions - WellKenz ERP')

@section('breadcrumb', 'Baker Requisitions')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Baker's Station</h1>
                <p class="text-text-muted mt-2">Request ingredients, materials, and supplies for your baking needs.</p>
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
                    <i class="fas fa-seedling text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">This Week's Requests</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-text-dark mt-2">6</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">2</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Urgent Needs</p>
            <p class="text-3xl font-bold text-text-dark mt-2">3</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Baker Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">My Baking Requisitions</h3>
                <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
            </div>
            
            <div class="space-y-4">
                <!-- Baker Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Chocolate Chip Cookies - Bulk Ingredients</p>
                        <p class="text-xs text-text-muted mt-1">Production Batch #BC-245 • For: Weekend Special</p>
                        <p class="text-xs text-text-muted mt-1">Flour (25kg), Chocolate Chips (15kg), Butter (10kg), Vanilla Extract (2L)</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Today, 07:30 AM • Needed By: Tomorrow</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-text-muted">Priority: </span>
                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded">URGENT</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold">PENDING</span>
                </div>

                <!-- Baker Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Birthday Cake Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Custom Order #ORD-789 • For: Customer Birthday</p>
                        <p class="text-xs text-text-muted mt-1">Cake Flour (5kg), Food Coloring Set, Fondant (3kg), Sprinkles (500g)</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Yesterday • Approved: Today, 09:15 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Approved by Pastry Manager</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">APPROVED</span>
                </div>

                <!-- Baker Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Bread Production - Daily Batch</p>
                        <p class="text-xs text-text-muted mt-1">Regular Production • For: Daily Bread Sales</p>
                        <p class="text-xs text-text-muted mt-1">Bread Flour (50kg), Yeast (2kg), Salt (5kg), Olive Oil (5L)</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 25, 2024 • Approved: Nov 25, 2024</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Recurring order approved</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">APPROVED</span>
                </div>

                <!-- Baker Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Specialty Pastry Ingredients</p>
                        <p class="text-xs text-text-muted mt-1">New Recipe Development • For: Seasonal Menu</p>
                        <p class="text-xs text-text-muted mt-1">Almond Flour (10kg), Premium Chocolate (8kg), Fresh Berries (6kg)</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 24, 2024 • Needed By: Dec 1, 2024</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-text-muted">Priority: </span>
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded">MEDIUM</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">UNDER REVIEW</span>
                </div>

                <!-- Baker Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Packaging Materials Restock</p>
                        <p class="text-xs text-text-muted mt-1">Monthly Restock • For: Product Packaging</p>
                        <p class="text-xs text-text-muted mt-1">Cake Boxes (200), Cookie Bags (500), Ribbon (50m), Labels (1000)</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 22, 2024 • Approved: Nov 23, 2024</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Delivered this morning</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">COMPLETED</span>
                </div>
            </div>
        </div>

        <!-- Quick Baker Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Baker's Quick Actions</h3>
            
            <div class="space-y-3">
                <button onclick="openModal()" class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    New Ingredient Request
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-box-open mr-2"></i>
                    Request Packaging
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-utensils mr-2 text-chocolate"></i>
                    Equipment Request
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-history mr-2 text-chocolate"></i>
                    View Order History
                </button>
            </div>

            <!-- Stock Alerts -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Stock Alerts</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm p-2 bg-red-50 rounded">
                        <span class="text-text-dark">Chocolate Chips</span>
                        <span class="text-red-600 font-bold">LOW</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-orange-50 rounded">
                        <span class="text-text-dark">All-Purpose Flour</span>
                        <span class="text-orange-600 font-bold">MEDIUM</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-red-50 rounded">
                        <span class="text-text-dark">Vanilla Extract</span>
                        <span class="text-red-600 font-bold">LOW</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-green-50 rounded">
                        <span class="text-text-dark">Butter</span>
                        <span class="text-green-600 font-bold">OK</span>
                    </div>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Quick Templates</h4>
                <div class="space-y-2">
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                        <i class="fas fa-bread-slice text-caramel mr-2"></i>
                        Daily Bread Batch
                    </button>
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                        <i class="fas fa-birthday-cake text-caramel mr-2"></i>
                        Cake Order Supplies
                    </button>
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                        <i class="fas fa-cookie text-caramel mr-2"></i>
                        Cookie Production
                    </button>
                    <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                        <i class="fas fa-box text-caramel mr-2"></i>
                        Packaging Restock
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Common Ingredients & Quick Orders -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-list-alt text-caramel mr-2"></i>
                Common Ingredients & Quick Order
            </h3>
            
            <div class="space-y-4">
                <!-- Common Ingredients Grid -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button class="p-3 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition text-center rounded">
                        <i class="fas fa-wheat text-green-600 mb-1"></i>
                        <p class="text-xs font-bold">Flour</p>
                    </button>
                    <button class="p-3 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition text-center rounded">
                        <i class="fas fa-egg text-blue-600 mb-1"></i>
                        <p class="text-xs font-bold">Dairy & Eggs</p>
                    </button>
                    <button class="p-3 bg-purple-50 border border-purple-200 text-purple-700 hover:bg-purple-100 transition text-center rounded">
                        <i class="fas fa-candy-cane text-purple-600 mb-1"></i>
                        <p class="text-xs font-bold">Sweeteners</p>
                    </button>
                    <button class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 hover:bg-yellow-100 transition text-center rounded">
                        <i class="fas fa-mortar-pestle text-yellow-600 mb-1"></i>
                        <p class="text-xs font-bold">Spices</p>
                    </button>
                </div>

                <!-- Quick Reorder Items -->
                <div class="space-y-3">
                    <div class="p-3 border-l-4 border-green-500 bg-green-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">All-Purpose Flour</p>
                                <p class="text-xs text-text-muted">Last ordered: 25kg on Nov 20, 2024</p>
                            </div>
                            <button class="px-3 py-1 bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition">
                                REORDER
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border-l-4 border-orange-500 bg-orange-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Chocolate Chips</p>
                                <p class="text-xs text-text-muted">Last ordered: 10kg on Nov 18, 2024</p>
                            </div>
                            <button class="px-3 py-1 bg-orange-500 text-white text-xs font-bold hover:bg-orange-600 transition">
                                REORDER
                            </button>
                        </div>
                    </div>

                    <div class="p-3 border-l-4 border-blue-500 bg-blue-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Butter Unsalted</p>
                                <p class="text-xs text-text-muted">Last ordered: 20kg on Nov 22, 2024</p>
                            </div>
                            <button class="px-3 py-1 bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                                REORDER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baker's Tips & Recent Activity -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-lightbulb text-caramel mr-2"></i>
                Baker's Tips & Activity
            </h3>
            
            <div class="space-y-4">
                <!-- Baker's Tips -->
                <div class="p-4 bg-cream-bg border border-border-soft rounded">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-2">Baker's Tips</h4>
                    <ul class="text-xs text-text-muted space-y-1">
                        <li>• Order specialty ingredients 5-7 days in advance</li>
                        <li>• Check stock levels before large batch production</li>
                        <li>• Use urgent priority for time-sensitive orders</li>
                        <li>• Include recipe notes for specialty items</li>
                    </ul>
                </div>

                <!-- Recent Activity -->
                <div class="space-y-3">
                    <div class="flex items-center text-xs p-2 bg-green-50 rounded">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">Birthday cake supplies approved</span>
                        <span class="text-text-muted ml-auto">2h ago</span>
                    </div>
                    <div class="flex items-center text-xs p-2 bg-yellow-50 rounded">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">Chocolate chips request submitted</span>
                        <span class="text-text-muted ml-auto">4h ago</span>
                    </div>
                    <div class="flex items-center text-xs p-2 bg-blue-50 rounded">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-text-dark">Packaging materials delivered</span>
                        <span class="text-text-muted ml-auto">1d ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="requisitionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-2xl font-bold text-text-dark">New Ingredient Requisition</h3>
            <button onclick="closeModal()" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form class="space-y-6">
            <!-- Requisition Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Product Type *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="">Select product type...</option>
                        <option value="flour">Flour & Grains</option>
                        <option value="dairy">Dairy Products</option>
                        <option value="sweeteners">Sweeteners & Sugars</option>
                        <option value="chocolate">Chocolate & Cocoa</option>
                        <option value="fruits">Fruits & Nuts</option>
                        <option value="spices">Spices & Flavorings</option>
                        <option value="packaging">Packaging Materials</option>
                        <option value="equipment">Baking Equipment</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Priority Level *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="normal">Normal (3-5 days)</option>
                        <option value="urgent">Urgent (1-2 days)</option>
                        <option value="critical">Critical (Today)</option>
                    </select>
                </div>
            </div>

            <!-- Ingredient Details -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Ingredient/Material *</label>
                <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                       placeholder="e.g., All-Purpose Flour" required>
            </div>

            <!-- Quantity & Unit -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Quantity *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., 25" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Unit *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="l">Liters (L)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="units">Units</option>
                        <option value="pack">Packs</option>
                        <option value="box">Boxes</option>
                    </select>
                </div>
            </div>

            <!-- Production Details -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">For Production *</label>
                <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                       placeholder="e.g., Weekend Special, Birthday Cake Order, Daily Bread Batch">
            </div>

            <!-- Needed By Date -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Needed By Date *</label>
                <input type="date" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                       min="{{ date('Y-m-d') }}">
            </div>

            <!-- Additional Notes -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Additional Notes</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="4" placeholder="Any special requirements, recipe notes, or additional information..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-4 pt-4 border-t border-border-soft">
                <button type="button" onclick="closeModal()" class="flex-1 p-3 border-2 border-border-soft text-text-dark hover:bg-gray-50 transition text-center font-semibold rounded">
                    Cancel
                </button>
                <button type="submit" class="flex-1 p-3 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold rounded">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Submit Requisition
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
    function openModal() {
        document.getElementById('requisitionModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('requisitionModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('requisitionModal').addEventListener('click', function(e) {
        if (e.target.id === 'requisitionModal') {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically handle the form submission with AJAX
        alert('Requisition submitted successfully!');
        closeModal();
        // Reset form
        this.reset();
    });
</script>
@endsection