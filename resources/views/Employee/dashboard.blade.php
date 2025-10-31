@extends('Employee.layout.app')

@section('title', 'My Requisitions - WellKenz ERP')

@section('breadcrumb', 'My Requisitions')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-white border-2 border-border-soft p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-display text-3xl font-bold text-text-dark">My Requisition Dashboard</h1>
                <p class="text-text-muted mt-2">Track and manage all your requisition requests in one place.</p>
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
                    <i class="fas fa-clipboard-list text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total Requisitions</p>
            <p class="text-3xl font-bold text-text-dark mt-2">42</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-chocolate flex items-center justify-center">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">8</p>
        </div>

        <div class="bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-caramel flex items-center justify-center">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-text-dark mt-2">28</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-500 flex items-center justify-center">
                    <i class="fas fa-truck text-white text-lg"></i>
                </div>
            </div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">In Delivery</p>
            <p class="text-3xl font-bold text-text-dark mt-2">6</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requisitions -->
        <div class="lg:col-span-2 bg-white border-2 border-border-soft p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-xl font-bold text-text-dark">Recent Requisitions</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-border-soft rounded text-sm">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Approved</option>
                        <option>Rejected</option>
                        <option>In Delivery</option>
                    </select>
                    <a href="#" class="text-xs font-bold text-caramel hover:text-caramel-dark uppercase tracking-wider">View All</a>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-yellow-500 bg-yellow-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Laptop Upgrade - Dell XPS 15</p>
                        <p class="text-xs text-text-muted mt-1">IT Equipment • REQ-2024-0042 • $1,850</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Today, 09:30 AM • Current Approver: Sarah Johnson</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-text-muted">Priority: </span>
                            <span class="ml-2 px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded">HIGH</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-600 text-white text-xs font-bold">UNDER REVIEW</span>
                </div>

                <!-- Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Office Chair Ergonomic</p>
                        <p class="text-xs text-text-muted mt-1">Furniture • REQ-2024-0041 • $450</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Yesterday • Approved: Today, 10:15 AM</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Approved by Michael Chen</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">APPROVED</span>
                </div>

                <!-- Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-purple-500 bg-purple-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Software Licenses - Adobe Creative Cloud</p>
                        <p class="text-xs text-text-muted mt-1">Software • REQ-2024-0040 • $1,200</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 25, 2024 • In Procurement</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-purple-600 font-bold">🔄 Purchase order being created</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-purple-600 text-white text-xs font-bold">PROCESSING</span>
                </div>

                <!-- Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-blue-500 bg-blue-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Conference Room Monitor 85"</p>
                        <p class="text-xs text-text-muted mt-1">AV Equipment • REQ-2024-0039 • $2,500</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 22, 2024 • Expected Delivery: Dec 5, 2024</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-blue-600 font-bold">📦 Shipped, tracking available</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold">IN TRANSIT</span>
                </div>

                <!-- Requisition Item -->
                <div class="flex items-start justify-between p-4 border-l-4 border-green-500 bg-green-50">
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Team Building Activity Supplies</p>
                        <p class="text-xs text-text-muted mt-1">Event Materials • REQ-2024-0038 • $800</p>
                        <p class="text-xs text-text-muted mt-1">Submitted: Nov 20, 2024 • Delivered: Nov 25, 2024</p>
                        <div class="flex items-center mt-2">
                            <span class="text-xs text-green-600 font-bold">✓ Delivered and acknowledged</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-green-600 text-white text-xs font-bold">COMPLETED</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Status -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">Quick Actions</h3>
            
            <div class="space-y-3">
                <button onclick="openRequisitionModal()" class="w-full p-4 bg-caramel text-white hover:bg-caramel-dark transition text-center font-semibold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create New Requisition
                </button>

                <button class="w-full p-4 bg-chocolate text-white hover:bg-chocolate-dark transition text-center font-semibold">
                    <i class="fas fa-copy mr-2"></i>
                    Clone Previous
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-download mr-2 text-chocolate"></i>
                    Export Report
                </button>

                <button class="w-full p-4 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition text-center font-semibold text-text-dark">
                    <i class="fas fa-history mr-2 text-chocolate"></i>
                    View History
                </button>
            </div>

            <!-- Status Overview -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Status Overview</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Approved</span>
                            <span class="text-sm font-bold text-text-dark">28 (67%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 67%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Pending</span>
                            <span class="text-sm font-bold text-text-dark">8 (19%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 19%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">In Progress</span>
                            <span class="text-sm font-bold text-text-dark">4 (10%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 10%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-text-muted">Rejected</span>
                            <span class="text-sm font-bold text-text-dark">2 (4%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: 4%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-8 pt-6 border-t border-border-soft">
                <h4 class="font-display text-lg font-bold text-text-dark mb-4">Quick Stats</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-green-50 rounded">
                        <p class="text-2xl font-bold text-text-dark">79%</p>
                        <p class="text-xs text-text-muted">Approval Rate</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded">
                        <p class="text-2xl font-bold text-text-dark">2.1</p>
                        <p class="text-xs text-text-muted">Avg. Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Requisition Timeline -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6">
                <i class="fas fa-stream text-caramel mr-2"></i>
                Recent Activity Timeline
            </h3>
            
            <div class="space-y-4">
                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Office Chair Approved</p>
                        <p class="text-xs text-text-muted mt-1">REQ-2024-0041 • Approved by Michael Chen</p>
                        <p class="text-xs text-text-muted mt-1">Today, 10:15 AM • Moved to Procurement</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Laptop Upgrade Under Review</p>
                        <p class="text-xs text-text-muted mt-1">REQ-2024-0042 • Waiting for IT Manager</p>
                        <p class="text-xs text-text-muted mt-1">Today, 09:30 AM • Estimated: 1-2 days</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Monitor Shipped</p>
                        <p class="text-xs text-text-muted mt-1">REQ-2024-0039 • In transit from supplier</p>
                        <p class="text-xs text-text-muted mt-1">Yesterday, 03:45 PM • Expected: Dec 5, 2024</p>
                    </div>
                </div>

                <!-- Timeline Item -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-xs"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-text-dark">Software Purchase Initiated</p>
                        <p class="text-xs text-text-muted mt-1">REQ-2024-0040 • PO created with vendor</p>
                        <p class="text-xs text-text-muted mt-1">Nov 26, 2024 • Processing payment</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Templates & Resources -->
        <div class="bg-white border-2 border-border-soft p-6">
            <h3 class="font-display text-xl font-bold text-text-dark mb-6 flex items-center">
                <i class="fas fa-rocket text-caramel mr-2"></i>
                Quick Start & Resources
            </h3>
            
            <div class="space-y-4">
                <!-- Quick Templates -->
                <div>
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Quick Templates</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="p-3 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition text-center rounded">
                            <i class="fas fa-laptop text-blue-600 mb-1"></i>
                            <p class="text-xs font-bold">IT Equipment</p>
                        </button>
                        <button class="p-3 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 transition text-center rounded">
                            <i class="fas fa-chair text-green-600 mb-1"></i>
                            <p class="text-xs font-bold">Furniture</p>
                        </button>
                        <button class="p-3 bg-purple-50 border border-purple-200 text-purple-700 hover:bg-purple-100 transition text-center rounded">
                            <i class="fas fa-utensils text-purple-600 mb-1"></i>
                            <p class="text-xs font-bold">Supplies</p>
                        </button>
                        <button class="p-3 bg-orange-50 border border-orange-200 text-orange-700 hover:bg-orange-100 transition text-center rounded">
                            <i class="fas fa-tools text-orange-600 mb-1"></i>
                            <p class="text-xs font-bold">Tools</p>
                        </button>
                    </div>
                </div>

                <!-- Help Resources -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-3">Help & Resources</h4>
                    <div class="space-y-2">
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-question-circle text-caramel mr-2"></i>
                            Requisition Guidelines
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-file-alt text-caramel mr-2"></i>
                            Approval Workflow
                        </button>
                        <button class="w-full text-left p-2 text-sm text-text-dark hover:bg-cream-bg rounded transition">
                            <i class="fas fa-phone-alt text-caramel mr-2"></i>
                            Contact Procurement
                        </button>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="pt-4 border-t border-border-soft">
                    <h4 class="font-display text-sm font-bold text-text-dark mb-2">Quick Tips</h4>
                    <ul class="text-xs text-text-muted space-y-1">
                        <li>• Attach quotes for items over $500</li>
                        <li>• Use business justification for faster approval</li>
                        <li>• Check budget codes before submitting</li>
                        <li>• Follow up after 2 business days</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requisition Modal -->
<div id="requisitionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-display text-2xl font-bold text-text-dark">Create New Requisition</h3>
            <button onclick="closeRequisitionModal()" class="text-text-muted hover:text-text-dark">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form class="space-y-6">
            <!-- Requisition Type -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Requisition Type *</label>
                <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                    <option value="">Select type...</option>
                    <option value="it">IT Equipment</option>
                    <option value="furniture">Office Furniture</option>
                    <option value="supplies">Office Supplies</option>
                    <option value="software">Software & Licenses</option>
                    <option value="tools">Tools & Equipment</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Item Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Item Name *</label>
                    <input type="text" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="e.g., Dell XPS 15 Laptop" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Estimated Cost *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="0.00" min="0" step="0.01" required>
                </div>
            </div>

            <!-- Priority & Department -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Priority Level *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="low">Low (1-2 weeks)</option>
                        <option value="normal">Normal (5-7 days)</option>
                        <option value="high">High (2-3 days)</option>
                        <option value="urgent">Urgent (24 hours)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Department *</label>
                    <select class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel">
                        <option value="it">IT Department</option>
                        <option value="hr">Human Resources</option>
                        <option value="finance">Finance</option>
                        <option value="operations">Operations</option>
                        <option value="marketing">Marketing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Quantity & Needed By -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Quantity *</label>
                    <input type="number" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           placeholder="1" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-dark mb-2">Needed By Date *</label>
                    <input type="date" class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                           min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <!-- Justification -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Business Justification *</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="4" placeholder="Explain why this item is needed and how it will benefit the business..." required></textarea>
            </div>

            <!-- Additional Notes -->
            <div>
                <label class="block text-sm font-bold text-text-dark mb-2">Additional Notes</label>
                <textarea class="w-full px-3 py-2 border border-border-soft rounded text-sm focus:ring-2 focus:ring-caramel focus:border-caramel" 
                          rows="3" placeholder="Any specifications, vendor preferences, or additional information..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex space-x-4 pt-4 border-t border-border-soft">
                <button type="button" onclick="closeRequisitionModal()" class="flex-1 p-3 border-2 border-border-soft text-text-dark hover:bg-gray-50 transition text-center font-semibold rounded">
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
    function openRequisitionModal() {
        document.getElementById('requisitionModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeRequisitionModal() {
        document.getElementById('requisitionModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('requisitionModal').addEventListener('click', function(e) {
        if (e.target.id === 'requisitionModal') {
            closeRequisitionModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRequisitionModal();
        }
    });

    // Form submission
    document.querySelector('#requisitionModal form').addEventListener('submit', function(e) {
        e.preventDefault();
        // Here you would typically handle the form submission with AJAX
        alert('Requisition submitted successfully!');
        closeRequisitionModal();
        // Reset form
        this.reset();
    });
</script>
@endsection