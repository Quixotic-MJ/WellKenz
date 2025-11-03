@extends('Admin.layout.app')

@section('title', 'Supplier Management - WellKenz ERP')

@section('breadcrumb', 'Supplier Management')

@section('content')
    <div class="space-y-6">
        <!-- Messages -->
        <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3">
            Supplier added successfully!
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Supplier Management</h1>
                <p class="text-text-muted mt-2">Manage supplier information and relationships</p>
            </div>
            <button onclick="openAddSupplierModal()"
                class="px-4 py-2 bg-caramel text-white hover:bg-caramel-dark transition font-semibold">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Supplier
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Suppliers</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="totalSuppliers">24</p>
            </div>

            <div class="bg-white border-2 border-green-200 p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Active</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="activeSuppliers">18</p>
                <p class="text-xs text-green-600 mt-1">With recent orders</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Top Rated</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="topRatedCount">8</p>
                <p class="text-xs text-yellow-600 mt-1">4+ star rating</p>
            </div>

            <div class="bg-white border-2 border-border-soft p-6">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider">This Month</p>
                <p class="text-3xl font-bold text-text-dark mt-2" id="newThisMonth">3</p>
                <p class="text-xs text-text-muted mt-1">New suppliers</p>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="bg-white border-2 border-border-soft">
            <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-xl font-bold text-text-dark">All Suppliers</h3>
                    <div class="flex items-center space-x-4">
                        <!-- Filter Dropdown -->
                        <select onchange="filterSuppliers(this.value)"
                            class="border-2 border-border-soft px-3 py-2 text-sm focus:outline-none focus:border-chocolate transition bg-white">
                            <option value="all">All Suppliers</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="preferred">Preferred</option>
                            <option value="local">Local</option>
                        </select>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search suppliers..." onkeyup="searchSuppliers(this.value)"
                                class="pl-9 pr-4 py-2 border-2 border-border-soft text-sm focus:outline-none focus:border-chocolate transition w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-text-muted text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="suppliersTable">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-border-soft">
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Contact Person</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Contact Info</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Address</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-soft" id="suppliersTableBody">
                        <!-- Sample Supplier Data -->
                        <tr class="hover:bg-cream-bg transition supplier-row" data-status="active" data-category="preferred">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-wheat-alt text-orange-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Baker's Supply Co.</p>
                                        <p class="text-xs text-text-muted">Baking Ingredients</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">John Davis</p>
                                <p class="text-xs text-text-muted">Sales Manager</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 123-4567</p>
                                <p class="text-xs text-text-muted">john.davis@bakersupply.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">123 Flour Street</p>
                                <p class="text-xs text-text-muted">Bakerville, BK 12345</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-xs text-text-muted ml-1">5.0</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-text-dark">42</p>
                                    <p class="text-xs text-text-muted">POs served</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditSupplierModal(1)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        Edit
                                    </button>
                                    <button onclick="viewSupplierDetails(1)"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition supplier-row" data-status="active" data-category="local">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-industry text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Flour Mill Inc.</p>
                                        <p class="text-xs text-text-muted">Grains & Flour</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Sarah Wilson</p>
                                <p class="text-xs text-text-muted">Account Manager</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 234-5678</p>
                                <p class="text-xs text-text-muted">sarah@flourmill.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">456 Grain Avenue</p>
                                <p class="text-xs text-text-muted">Milltown, MT 23456</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                                    <span class="text-xs text-text-muted ml-1">4.5</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-text-dark">28</p>
                                    <p class="text-xs text-text-muted">POs served</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditSupplierModal(2)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        Edit
                                    </button>
                                    <button onclick="viewSupplierDetails(2)"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition supplier-row" data-status="active" data-category="preferred">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cookie text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Sweet Ingredients Ltd.</p>
                                        <p class="text-xs text-text-muted">Flavorings & Sweeteners</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Mike Johnson</p>
                                <p class="text-xs text-text-muted">Regional Director</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 345-6789</p>
                                <p class="text-xs text-text-muted">mike.johnson@sweetingredients.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">789 Sugar Lane</p>
                                <p class="text-xs text-text-muted">Sweetville, SV 34567</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="far fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-xs text-text-muted ml-1">4.0</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-text-dark">35</p>
                                    <p class="text-xs text-text-muted">POs served</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditSupplierModal(3)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        Edit
                                    </button>
                                    <button onclick="viewSupplierDetails(3)"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition supplier-row" data-status="active" data-category="local">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">PackPro Solutions</p>
                                        <p class="text-xs text-text-muted">Packaging Materials</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Lisa Chen</p>
                                <p class="text-xs text-text-muted">Customer Success</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 456-7890</p>
                                <p class="text-xs text-text-muted">lisa.chen@packpro.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">321 Box Street</p>
                                <p class="text-xs text-text-muted">Packington, PK 45678</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>
                                    <i class="far fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-xs text-text-muted ml-1">3.5</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-text-dark">19</p>
                                    <p class="text-xs text-text-muted">POs served</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                    ACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditSupplierModal(4)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        Edit
                                    </button>
                                    <button onclick="viewSupplierDetails(4)"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr class="hover:bg-cream-bg transition supplier-row" data-status="inactive" data-category="local">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cow text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-text-dark">Dairy Fresh Co.</p>
                                        <p class="text-xs text-text-muted">Dairy Products</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-text-dark">Robert Brown</p>
                                <p class="text-xs text-text-muted">Owner</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">+1 (555) 567-8901</p>
                                <p class="text-xs text-text-muted">robert@dairyfresh.com</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-text-dark">654 Milk Road</p>
                                <p class="text-xs text-text-muted">Creamfield, CF 56789</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                    <i class="far fa-star text-yellow-400 text-sm"></i>
                                    <i class="far fa-star text-yellow-400 text-sm"></i>
                                    <span class="text-xs text-text-muted ml-1">3.0</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-text-dark">7</p>
                                    <p class="text-xs text-text-muted">POs served</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">
                                    INACTIVE
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditSupplierModal(5)"
                                        class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition rounded">
                                        Edit
                                    </button>
                                    <button onclick="viewSupplierDetails(5)"
                                        class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition rounded">
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
                <p class="text-sm text-text-muted">Showing <span id="visibleCount">5</span> of 24 suppliers</p>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Add New Supplier</h3>
                    <button onclick="closeAddSupplierModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="addSupplierForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Supplier Name</label>
                            <input type="text" name="supplier_name" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Enter supplier name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Category</label>
                            <select name="category" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate">
                                <option value="">Select Category</option>
                                <option value="baking_ingredients">Baking Ingredients</option>
                                <option value="packaging">Packaging Materials</option>
                                <option value="dairy">Dairy Products</option>
                                <option value="equipment">Equipment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Contact Person</label>
                            <input type="text" name="contact_person" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Full name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                            <input type="text" name="position"
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Job title">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Phone Number</label>
                            <input type="tel" name="phone" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="+1 (555) 123-4567">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                            <input type="email" name="email" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="contact@supplier.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Address</label>
                        <textarea name="address" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate resize-none"
                            placeholder="Full street address"
                            rows="3"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">City</label>
                            <input type="text" name="city" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Postal Code</label>
                            <input type="text" name="postal_code" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="ZIP/Postal code">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeAddSupplierModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Add Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="editSupplierModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b-2 border-border-soft">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-bold text-text-dark">Edit Supplier</h3>
                    <button onclick="closeEditSupplierModal()" class="text-text-muted hover:text-text-dark">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="editSupplierForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Supplier Name</label>
                            <input type="text" name="supplier_name" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Enter supplier name" id="edit_supplier_name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Category</label>
                            <select name="category" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                id="edit_category">
                                <option value="">Select Category</option>
                                <option value="baking_ingredients">Baking Ingredients</option>
                                <option value="packaging">Packaging Materials</option>
                                <option value="dairy">Dairy Products</option>
                                <option value="equipment">Equipment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Contact Person</label>
                            <input type="text" name="contact_person" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Full name" id="edit_contact_person">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Position</label>
                            <input type="text" name="position"
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="Job title" id="edit_position">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Phone Number</label>
                            <input type="tel" name="phone" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="+1 (555) 123-4567" id="edit_phone">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Email</label>
                            <input type="email" name="email" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="contact@supplier.com" id="edit_email">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-dark mb-2">Address</label>
                        <textarea name="address" required
                            class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate resize-none"
                            placeholder="Full street address"
                            rows="3" id="edit_address"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">City</label>
                            <input type="text" name="city" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="City" id="edit_city">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Postal Code</label>
                            <input type="text" name="postal_code" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                placeholder="ZIP/Postal code" id="edit_postal_code">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Status</label>
                            <select name="status" required
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                id="edit_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-text-dark mb-2">Rating</label>
                            <select name="rating"
                                class="w-full border-2 border-border-soft px-4 py-2 focus:outline-none focus:border-chocolate"
                                id="edit_rating">
                                <option value="5">5 Stars - Excellent</option>
                                <option value="4">4 Stars - Very Good</option>
                                <option value="3">3 Stars - Good</option>
                                <option value="2">2 Stars - Fair</option>
                                <option value="1">1 Star - Poor</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditSupplierModal()"
                            class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition">
                            Update Supplier
                        </button>
                    </div>
                </form>
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
        // Sample supplier data
        const suppliers = {
            1: {
                name: "Baker's Supply Co.",
                category: "baking_ingredients",
                contactPerson: "John Davis",
                position: "Sales Manager",
                phone: "+1 (555) 123-4567",
                email: "john.davis@bakersupply.com",
                address: "123 Flour Street",
                city: "Bakerville",
                postalCode: "BK 12345",
                status: "active",
                rating: 5,
                activity: 42
            },
            2: {
                name: "Flour Mill Inc.",
                category: "baking_ingredients",
                contactPerson: "Sarah Wilson",
                position: "Account Manager",
                phone: "+1 (555) 234-5678",
                email: "sarah@flourmill.com",
                address: "456 Grain Avenue",
                city: "Milltown",
                postalCode: "MT 23456",
                status: "active",
                rating: 4.5,
                activity: 28
            }
        };

        let currentSupplierId = null;

        // Modal Functions
        function openAddSupplierModal() {
            document.getElementById('addSupplierModal').classList.remove('hidden');
        }

        function closeAddSupplierModal() {
            document.getElementById('addSupplierModal').classList.add('hidden');
            document.getElementById('addSupplierForm').reset();
        }

        function openEditSupplierModal(supplierId) {
            currentSupplierId = supplierId;
            const supplier = suppliers[supplierId];
            
            if (supplier) {
                document.getElementById('edit_supplier_name').value = supplier.name;
                document.getElementById('edit_category').value = supplier.category;
                document.getElementById('edit_contact_person').value = supplier.contactPerson;
                document.getElementById('edit_position').value = supplier.position;
                document.getElementById('edit_phone').value = supplier.phone;
                document.getElementById('edit_email').value = supplier.email;
                document.getElementById('edit_address').value = supplier.address;
                document.getElementById('edit_city').value = supplier.city;
                document.getElementById('edit_postal_code').value = supplier.postalCode;
                document.getElementById('edit_status').value = supplier.status;
                document.getElementById('edit_rating').value = Math.floor(supplier.rating);
                
                document.getElementById('editSupplierModal').classList.remove('hidden');
            }
        }

        function closeEditSupplierModal() {
            document.getElementById('editSupplierModal').classList.add('hidden');
            currentSupplierId = null;
        }

        function viewSupplierDetails(supplierId) {
            showMessage(`Viewing details for supplier ID: ${supplierId}`, 'success');
            // In real app, would navigate to supplier details page
        }

        // Filter functionality
        function filterSuppliers(filter) {
            const rows = document.querySelectorAll('.supplier-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const category = row.getAttribute('data-category');

                let shouldShow = false;

                switch(filter) {
                    case 'all':
                        shouldShow = true;
                        break;
                    case 'active':
                        shouldShow = status === 'active';
                        break;
                    case 'inactive':
                        shouldShow = status === 'inactive';
                        break;
                    case 'preferred':
                        shouldShow = category === 'preferred';
                        break;
                    case 'local':
                        shouldShow = category === 'local';
                        break;
                    default:
                        shouldShow = true;
                }

                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Search functionality
        function searchSuppliers(query) {
            const rows = document.querySelectorAll('.supplier-row');
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

        // Form Handling
        document.getElementById('addSupplierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showMessage('Supplier added successfully!', 'success');
            closeAddSupplierModal();
        });

        document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showMessage('Supplier updated successfully!', 'success');
            closeEditSupplierModal();
        });

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
        document.getElementById('addSupplierModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddSupplierModal();
        });

        document.getElementById('editSupplierModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditSupplierModal();
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddSupplierModal();
                closeEditSupplierModal();
            }
        });

        // Initialize filters
        document.addEventListener('DOMContentLoaded', function() {
            filterSuppliers('all');
        });
    </script>
@endsection