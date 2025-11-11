@extends('Purchasing.layout.app')

@section('title', 'Supplier Management - WellKenz ERP')

@section('breadcrumb', 'Supplier Management')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">Supplier Management</h1>
                <p class="text-text-muted mt-2">Manage supplier records, contact information, and product catalogs.</p>
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
                    <i class="fas fa-building text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Suppliers</p>
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-star text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Preferred Suppliers</p>
            <p class="text-3xl font-bold text-text-dark mt-2">42</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-boxes text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Product Categories</p>
            <p class="text-3xl font-bold text-text-dark mt-2">28</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending Reviews</p>
            <p class="text-3xl font-bold text-text-dark mt-2">12</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Supplier Directory -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Supplier Directory</h3>
                <div class="flex space-x-2">
                    <input type="text" placeholder="Search suppliers..." class="px-3 py-2 border border-border-soft rounded text-sm">
                    <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- Supplier Card -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Global Materials Inc.</p>
                                <p class="text-xs text-text-muted mt-1">Contact: John Smith • j.smith@globalmaterials.com</p>
                                <p class="text-xs text-text-muted mt-1">Phone: (555) 123-4567 • Location: Chicago, IL</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Raw Materials</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Steel</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Aluminum</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Rating: 4.8/5</p>
                                <p class="text-xs text-text-muted">On-time: 96%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Card -->
                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Tech Solutions Ltd.</p>
                                <p class="text-xs text-text-muted mt-1">Contact: Sarah Chen • s.chen@techsolutions.com</p>
                                <p class="text-xs text-text-muted mt-1">Phone: (555) 987-6543 • Location: San Francisco, CA</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">IT Equipment</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Servers</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Networking</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Rating: 4.5/5</p>
                                <p class="text-xs text-text-muted">On-time: 92%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Card -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Office Pro Supplies</p>
                                <p class="text-xs text-text-muted mt-1">Contact: Mike Johnson • m.johnson@officepro.com</p>
                                <p class="text-xs text-text-muted mt-1">Phone: (555) 456-7890 • Location: New York, NY</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Furniture</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Office Supplies</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Stationery</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Rating: 4.2/5</p>
                                <p class="text-xs text-text-muted">On-time: 88%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Card -->
                <div class="flex items-start justify-between p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-bold text-text-dark">Industrial Tools Co.</p>
                                <p class="text-xs text-text-muted mt-1">Contact: Robert Brown • r.brown@industrialtools.com</p>
                                <p class="text-xs text-text-muted mt-1">Phone: (555) 234-5678 • Location: Houston, TX</p>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Tools</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Equipment</span>
                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Safety Gear</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-text-muted">Rating: 3.9/5</p>
                                <p class="text-xs text-text-muted">On-time: 82%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Supplier Actions</h3>
            
            <div class="space-y-3">
                <button class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Supplier
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-file-import mr-2"></i>
                    Import Suppliers
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-tags mr-2 text-chocolate"></i>
                    Manage Categories
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-download mr-2 text-chocolate"></i>
                    Export Directory
                </button>
            </div>

            <!-- Supplier Categories -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Supplier Categories</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">Raw Materials</p>
                        <p class="text-xs text-text-muted">24 suppliers</p>
                    </div>
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">IT Equipment</p>
                        <p class="text-xs text-text-muted">18 suppliers</p>
                    </div>
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">Office Supplies</p>
                        <p class="text-xs text-text-muted">32 suppliers</p>
                    </div>
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">Industrial Tools</p>
                        <p class="text-xs text-text-muted">15 suppliers</p>
                    </div>
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">Packaging</p>
                        <p class="text-xs text-text-muted">22 suppliers</p>
                    </div>
                    <div class="p-2 bg-gray-100 text-center rounded">
                        <p class="text-xs font-bold text-text-dark">Safety Equipment</p>
                        <p class="text-xs text-text-muted">14 suppliers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Supplier Performance -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-chart-line text-caramel mr-2"></i>
                Supplier Performance
            </h3>
            
            <div class="space-y-4">
                <!-- Performance Item -->
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Global Materials Inc.</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: 96%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">4.8/5</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: 94%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">94%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-green-600 text-white text-xs font-bold">EXCELLENT</span>
                    </div>
                </div>

                <!-- Performance Item -->
                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Tech Solutions Ltd.</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: 90%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">4.5/5</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: 88%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">88%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold">GOOD</span>
                    </div>
                </div>

                <!-- Performance Item -->
                <div class="p-4 border-l-4 border-orange-500 bg-orange-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Industrial Tools Co.</p>
                            <div class="flex items-center space-x-4 mt-2">
                                <div>
                                    <p class="text-xs text-text-muted">Quality Rating</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-orange-500 h-2 rounded-full" style="width: 78%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">3.9/5</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-text-muted">On-time Delivery</p>
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-orange-500 h-2 rounded-full" style="width: 82%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-text-dark ml-2">82%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 bg-orange-500 text-white text-xs font-bold">NEEDS REVIEW</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Supplier Activities -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-history text-caramel mr-2"></i>
                Recent Supplier Activities
            </h3>
            
            <div class="space-y-3">
                <div class="p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">New Supplier Registered</p>
                            <p class="text-xs text-text-muted mt-1">Eco Packaging Solutions • Contact: Lisa Wang</p>
                            <p class="text-xs text-text-muted mt-1">Products: Sustainable packaging materials</p>
                            <p class="text-xs text-text-muted mt-1">Added: Today, 10:30 AM</p>
                        </div>
                        <button class="px-3 py-1 bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition">
                            REVIEW
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Contact Information Updated</p>
                            <p class="text-xs text-text-muted mt-1">Office Pro Supplies • New phone number</p>
                            <p class="text-xs text-text-muted mt-1">Updated: (555) 456-7890 → (555) 456-7891</p>
                            <p class="text-xs text-text-muted mt-1">Modified: Yesterday, 3:15 PM</p>
                        </div>
                        <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold">UPDATED</span>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Product Catalog Expanded</p>
                            <p class="text-xs text-text-muted mt-1">Tech Solutions Ltd. • Added new server models</p>
                            <p class="text-xs text-text-muted mt-1">New categories: Cloud Infrastructure, Security</p>
                            <p class="text-xs text-text-muted mt-1">Updated: 2 days ago</p>
                        </div>
                        <button class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold hover:bg-yellow-700 transition">
                            VIEW CATALOG
                        </button>
                    </div>
                </div>

                <div class="p-4 border-l-4 border-red-500 bg-red-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-text-dark">Supplier Review Required</p>
                            <p class="text-xs text-text-muted mt-1">Industrial Tools Co. • Performance below threshold</p>
                            <p class="text-xs text-text-muted mt-1">On-time delivery dropped to 82% this quarter</p>
                            <p class="text-xs text-text-muted mt-1">Flagged: 3 days ago</p>
                        </div>
                        <button class="px-3 py-1 bg-red-600 text-white text-xs font-bold hover:bg-red-700 transition">
                            SCHEDULE REVIEW
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
    <!-- Functional Suppliers Table -->
    <div class="bg-white border-2 border-border-soft p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-xl font-bold text-text-dark">Suppliers</h3>
            <button class="px-3 py-2 bg-caramel text-white rounded hover:bg-caramel-dark" onclick="openAddSupplier()">
                <i class="fas fa-plus mr-1"></i> Add Supplier
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Contact</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="suppliersBody" class="divide-y divide-gray-200">
                    @foreach(\App\Models\Supplier::orderBy('sup_name')->get() as $sup)
                    <tr data-id="{{ $sup->sup_id }}">
                        <td class="px-3 py-2">{{ $sup->sup_name }}</td>
                        <td class="px-3 py-2">{{ $sup->sup_email ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $sup->contact_person ? ($sup->contact_person.' • ') : ''}}{{ $sup->contact_number ?? '' }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded {{ $sup->sup_status==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ ucfirst($sup->sup_status) }}</span>
                        </td>
                        <td class="px-3 py-2 text-center space-x-2">
                            <button class="px-2 py-1 text-xs bg-gray-700 text-white rounded" onclick="openEditSupplier({{ $sup->sup_id }})">Edit</button>
                            <button class="px-2 py-1 text-xs bg-red-600 text-white rounded" onclick="deleteSupplier({{ $sup->sup_id }})">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Supplier Modal -->
    <div id="supplierModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h4 id="supplierModalTitle" class="text-lg font-semibold">Add Supplier</h4>
                <button onclick="closeSupplierModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="supplierForm" class="p-4">
                @csrf
                <input type="hidden" id="sup_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Name</label>
                        <input type="text" id="sup_name" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Email</label>
                        <input type="email" id="sup_email" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-700 mb-1">Address</label>
                        <input type="text" id="sup_address" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Contact Person</label>
                        <input type="text" id="contact_person" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Contact Number</label>
                        <input type="text" id="contact_number" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-700 mb-1">Status</label>
                        <select id="sup_status" class="w-full border rounded px-3 py-2">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeSupplierModal()" class="px-3 py-2 border rounded">Cancel</button>
                    <button type="submit" class="px-3 py-2 bg-caramel text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
function openAddSupplier(){
    document.getElementById('supplierModalTitle').textContent = 'Add Supplier';
    document.getElementById('supplierForm').reset();
    document.getElementById('sup_id').value = '';
    document.getElementById('supplierModal').classList.remove('hidden');
}
function openEditSupplier(id){
    const tr = document.querySelector(`tr[data-id="${id}"]`);
    document.getElementById('supplierModalTitle').textContent = 'Edit Supplier';
    document.getElementById('sup_id').value = id;
    document.getElementById('sup_name').value = tr.children[0].textContent.trim();
    document.getElementById('sup_email').value = tr.children[1].textContent.trim() === '-' ? '' : tr.children[1].textContent.trim();
    // Address not present in table; leave blank
    document.getElementById('contact_person').value = '';
    document.getElementById('contact_number').value = '';
    const statusBadge = tr.children[3].querySelector('span').textContent.trim().toLowerCase();
    document.getElementById('sup_status').value = statusBadge === 'active' ? 'active' : 'inactive';
    document.getElementById('supplierModal').classList.remove('hidden');
}
function closeSupplierModal(){ document.getElementById('supplierModal').classList.add('hidden'); }

document.getElementById('supplierForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const id = document.getElementById('sup_id').value;
    const payload = {
        sup_name: document.getElementById('sup_name').value.trim(),
        sup_email: document.getElementById('sup_email').value.trim() || null,
        sup_address: document.getElementById('sup_address').value.trim() || null,
        contact_person: document.getElementById('contact_person').value.trim() || null,
        contact_number: document.getElementById('contact_number').value.trim() || null,
        sup_status: document.getElementById('sup_status').value
    };
    try{
        let res;
        if (id){
            res = await fetch(`{{ url('purchasing/supplier') }}/${id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: JSON.stringify(payload)});
        }else{
            res = await fetch(`{{ route('supplier.store') }}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: JSON.stringify(payload)});
        }
        const data = await res.json();
        if(!res.ok || data.success===false) throw new Error(data.message || 'Save failed');
        location.reload();
    }catch(err){ alert(err.message); }
});

async function deleteSupplier(id){
    if(!confirm('Delete this supplier? If referenced by POs, it will be set to inactive.')) return;
    try{
        const res = await fetch(`{{ url('purchasing/supplier') }}/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'} });
        const data = await res.json();
        if(!res.ok || data.success===false) throw new Error(data.message || 'Delete failed');
        alert(data.message || 'Updated');
        location.reload();
    }catch(e){ alert(e.message); }
}
</script>
@endpush
@endsection