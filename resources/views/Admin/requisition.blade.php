@extends('Admin.layout.app')

@section('title', 'Requisition Management - WellKenz ERP')

@section('breadcrumb')
<div class="flex items-center space-x-2 text-sm">
    <span class="text-text-muted">Procurement</span>
    <span class="text-border-soft">/</span>
    <span class="text-text-dark font-semibold">Requisition Management</span>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Requisition Management</h1>
            <p class="text-text-muted mt-2">Review and manage all staff requisition requests</p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Filter Dropdown -->
            <div class="relative">
                <select class="appearance-none bg-white border-2 border-border-soft px-4 py-2 pr-8 rounded-lg text-sm text-text-dark focus:outline-none focus:border-caramel transition-colors">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="revision">Needs Revision</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-3 text-text-muted text-xs"></i>
            </div>
            
            <!-- Export Button -->
            <button class="flex items-center space-x-2 px-4 py-2 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition-colors rounded-lg">
                <i class="fas fa-download text-text-muted"></i>
                <span class="text-sm font-semibold text-text-dark">Export</span>
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">156</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-clipboard-list text-gray-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">23</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">89</p>
                </div>
                <div class="w-10 h-10 bg-green-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border-2 border-border-soft p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Rejected</p>
                    <p class="text-2xl font-display font-bold text-text-dark mt-1">12</p>
                </div>
                <div class="w-10 h-10 bg-red-100 flex items-center justify-center rounded-full">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="bg-white shadow-sm border-2 border-border-soft rounded-lg overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-bold text-text-dark">All Requisitions</h3>
                <div class="flex items-center space-x-3">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" placeholder="Search requisitions..." 
                            class="pl-9 pr-4 py-2 bg-white border border-border-soft placeholder-text-muted text-text-dark text-sm focus:outline-none focus:border-caramel transition-colors w-64 rounded-lg">
                        <i class="fas fa-search absolute left-3 top-2.5 text-text-muted text-xs"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-border-soft">
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Requisition ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <!-- Pending Requisition -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-text-dark">REQ-2024-0012</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-xs font-bold">SM</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Sarah Martinez</div>
                                    <div class="text-xs text-text-muted">Senior Baker</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Production</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-text-dark">Flour, Sugar, Chocolate Chips</div>
                            <div class="text-xs text-text-muted">3 items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$450.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 15, 2024</div>
                            <div class="text-xs text-text-muted">2 days ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full uppercase">
                                <i class="fas fa-clock mr-1"></i>
                                Pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openRequisitionModal('REQ-2024-0012')" class="flex items-center space-x-1 px-3 py-1.5 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-eye"></i>
                                    <span>Review</span>
                                </button>
                                <div class="relative">
                                    <button onclick="toggleActionMenu('action-menu-1')" class="p-1.5 text-text-muted hover:text-text-dark hover:bg-border-soft rounded transition-colors">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="action-menu-1" class="hidden absolute right-0 mt-1 w-48 bg-white shadow-lg border-2 border-border-soft rounded-lg z-10">
                                        <button class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50 flex items-center space-x-2">
                                            <i class="fas fa-check w-4"></i>
                                            <span>Approve</span>
                                        </button>
                                        <button class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 flex items-center space-x-2">
                                            <i class="fas fa-edit w-4"></i>
                                            <span>Request Revision</span>
                                        </button>
                                        <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2">
                                            <i class="fas fa-times w-4"></i>
                                            <span>Reject</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Approved Requisition -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-text-dark">REQ-2024-0011</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-chocolate flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-xs font-bold">MW</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Mike Wilson</div>
                                    <div class="text-xs text-text-muted">Head Chef</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Kitchen</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-text-dark">Mixer, Baking Pans, Utensils</div>
                            <div class="text-xs text-text-muted">5 items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$1,250.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 14, 2024</div>
                            <div class="text-xs text-text-muted">3 days ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                                <i class="fas fa-check mr-1"></i>
                                Approved
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openRequisitionModal('REQ-2024-0011')" class="flex items-center space-x-1 px-3 py-1.5 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-eye"></i>
                                    <span>View</span>
                                </button>
                                <button class="flex items-center space-x-1 px-3 py-1.5 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>PDF</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Needs Revision -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-text-dark">REQ-2024-0010</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-xs font-bold">JR</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Jessica Rivera</div>
                                    <div class="text-xs text-text-muted">Pastry Chef</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Pastry</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-text-dark">Fondant, Food Coloring, Decorations</div>
                            <div class="text-xs text-text-muted">8 items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$320.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 13, 2024</div>
                            <div class="text-xs text-text-muted">4 days ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase">
                                <i class="fas fa-edit mr-1"></i>
                                Revision Needed
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openRequisitionModal('REQ-2024-0010')" class="flex items-center space-x-1 px-3 py-1.5 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-eye"></i>
                                    <span>Review</span>
                                </button>
                                <button class="flex items-center space-x-1 px-3 py-1.5 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition-colors">
                                    <i class="fas fa-comment"></i>
                                    <span>Add Note</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Rejected Requisition -->
                    <tr class="hover:bg-cream-bg transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-text-dark">REQ-2024-0009</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-chocolate flex items-center justify-center rounded-full flex-shrink-0">
                                    <span class="text-white text-xs font-bold">TD</span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-text-dark">Thomas Davis</div>
                                    <div class="text-xs text-text-muted">Junior Baker</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Production</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-text-dark">Specialty Ingredients</div>
                            <div class="text-xs text-text-muted">4 items</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-text-dark">$180.00</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-text-dark">Dec 12, 2024</div>
                            <div class="text-xs text-text-muted">5 days ago</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                <i class="fas fa-times mr-1"></i>
                                Rejected
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <button onclick="openRequisitionModal('REQ-2024-0009')" class="flex items-center space-x-1 px-3 py-1.5 bg-caramel text-white text-xs font-semibold rounded hover:bg-caramel-dark transition-colors">
                                    <i class="fas fa-eye"></i>
                                    <span>View</span>
                                </button>
                                <button class="flex items-center space-x-1 px-3 py-1.5 border border-border-soft text-text-muted text-xs font-semibold rounded hover:border-chocolate hover:text-text-dark transition-colors">
                                    <i class="fas fa-redo"></i>
                                    <span>Restore</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <div class="text-sm text-text-muted">
                    Showing 1 to 4 of 156 requisitions
                </div>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        Previous
                    </button>
                    <button class="px-3 py-1 bg-caramel text-white border border-caramel rounded">
                        1
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        2
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        3
                    </button>
                    <button class="px-3 py-1 border border-border-soft text-text-muted rounded hover:border-chocolate hover:text-text-dark transition-colors">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requisition Detail Modal -->
<div id="requisitionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Requisition Details</h3>
                <button onclick="closeRequisitionModal()" class="text-text-muted hover:text-text-dark transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div id="modalContent">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeRequisitionModal()" class="px-6 py-2 border-2 border-border-soft text-text-dark hover:border-chocolate transition-colors rounded-lg">
                Close
            </button>
            <button class="px-6 py-2 bg-caramel text-white hover:bg-caramel-dark transition-colors rounded-lg">
                Export as PDF
            </button>
        </div>
    </div>
</div>

<style>
    .font-display {
        font-family: 'Playfair Display', serif;
    }
</style>

<script>
    function toggleActionMenu(menuId) {
        const menu = document.getElementById(menuId);
        menu.classList.toggle('hidden');
        
        // Close other open menus
        document.querySelectorAll('[id^="action-menu-"]').forEach(otherMenu => {
            if (otherMenu.id !== menuId) {
                otherMenu.classList.add('hidden');
            }
        });
    }

    function openRequisitionModal(requisitionId) {
        // In a real application, you would fetch the requisition details via AJAX
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <div class="space-y-6">
                <!-- Requisition Header -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-cream-bg p-4 rounded-lg">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Requisition ID</p>
                        <p class="text-lg font-bold text-text-dark">${requisitionId}</p>
                    </div>
                    <div class="bg-cream-bg p-4 rounded-lg">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Status</p>
                        <span class="inline-flex px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full uppercase mt-1">
                            <i class="fas fa-clock mr-1"></i>
                            Pending
                        </span>
                    </div>
                    <div class="bg-cream-bg p-4 rounded-lg">
                        <p class="text-xs text-text-muted uppercase tracking-wider">Total Amount</p>
                        <p class="text-lg font-bold text-text-dark">$450.00</p>
                    </div>
                </div>

                <!-- Requester Info -->
                <div class="bg-white border-2 border-border-soft p-6 rounded-lg">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Requester Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-text-muted">Requested By</p>
                            <p class="text-text-dark font-semibold">Sarah Martinez - Senior Baker</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Department</p>
                            <p class="text-text-dark font-semibold">Production</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Request Date</p>
                            <p class="text-text-dark font-semibold">December 15, 2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Required By</p>
                            <p class="text-text-dark font-semibold">December 20, 2024</p>
                        </div>
                    </div>
                </div>

                <!-- Items List -->
                <div class="bg-white border-2 border-border-soft p-6 rounded-lg">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Requested Items</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border border-border-soft rounded-lg">
                            <div>
                                <p class="font-semibold text-text-dark">All-Purpose Flour</p>
                                <p class="text-sm text-text-muted">50kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-text-dark">Qty: 5</p>
                                <p class="text-sm text-text-muted">$150.00</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 border border-border-soft rounded-lg">
                            <div>
                                <p class="font-semibold text-text-dark">Granulated Sugar</p>
                                <p class="text-sm text-text-muted">25kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-text-dark">Qty: 4</p>
                                <p class="text-sm text-text-muted">$200.00</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 border border-border-soft rounded-lg">
                            <div>
                                <p class="font-semibold text-text-dark">Chocolate Chips</p>
                                <p class="text-sm text-text-muted">5kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-text-dark">Qty: 2</p>
                                <p class="text-sm text-text-muted">$100.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition-colors rounded-lg flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Reject</span>
                    </button>
                    <button class="px-6 py-2 bg-blue-500 text-white hover:bg-blue-600 transition-colors rounded-lg flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Request Revision</span>
                    </button>
                    <button class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition-colors rounded-lg flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Approve</span>
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('requisitionModal').classList.remove('hidden');
    }

    function closeRequisitionModal() {
        document.getElementById('requisitionModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('requisitionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRequisitionModal();
        }
    });

    // Close action menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="action-menu-"]') && !e.target.closest('button[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="action-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // Set dashboard as active by default
    document.addEventListener('DOMContentLoaded', function() {
        setActiveMenu('menu-requisition');
    });
</script>
@endsection