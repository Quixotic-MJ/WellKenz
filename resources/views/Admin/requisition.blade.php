@extends('Admin.layout.app')

@section('title', 'Requisitions - WellKenz ERP')

@section('breadcrumb', 'Requisition Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-text-dark">Requisitions</h1>
            <p class="text-text-muted mt-2">Review and manage staff requests</p>
        </div>
        <button class="px-4 py-2 border-2 border-border-soft hover:border-chocolate hover:bg-cream-bg transition font-semibold">
            <i class="fas fa-download mr-2"></i>
            Export
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border-2 border-border-soft p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Total</p>
            <p class="text-3xl font-bold text-text-dark mt-2">156</p>
        </div>

        <div class="bg-white border-2 border-yellow-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Pending</p>
            <p class="text-3xl font-bold text-text-dark mt-2">23</p>
        </div>

        <div class="bg-white border-2 border-green-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Approved</p>
            <p class="text-3xl font-bold text-text-dark mt-2">89</p>
        </div>

        <div class="bg-white border-2 border-red-200 p-6">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wider">Rejected</p>
            <p class="text-3xl font-bold text-text-dark mt-2">12</p>
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="bg-white border-2 border-border-soft">
        <div class="px-6 py-4 border-b-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-xl font-bold text-text-dark">All Requisitions</h3>
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
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">REQ ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-soft">
                    <!-- Pending -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">REQ-2024-0012</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Sarah Martinez</p>
                            <p class="text-xs text-text-muted">Senior Baker</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Production</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Flour, Sugar, Chocolate</p>
                            <p class="text-xs text-text-muted">3 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">$450.00</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 15, 2024</p>
                            <p class="text-xs text-text-muted">2 days ago</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">PENDING</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openRequisitionModal('REQ-2024-0012')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                Review
                            </button>
                        </td>
                    </tr>

                    <!-- Approved -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">REQ-2024-0011</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Mike Wilson</p>
                            <p class="text-xs text-text-muted">Head Chef</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Kitchen</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Mixer, Pans, Utensils</p>
                            <p class="text-xs text-text-muted">5 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">$1,250.00</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 14, 2024</p>
                            <p class="text-xs text-text-muted">3 days ago</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold">APPROVED</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openRequisitionModal('REQ-2024-0011')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                View
                            </button>
                        </td>
                    </tr>

                    <!-- Needs Revision -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">REQ-2024-0010</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Jessica Rivera</p>
                            <p class="text-xs text-text-muted">Pastry Chef</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Pastry</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Fondant, Coloring, Decor</p>
                            <p class="text-xs text-text-muted">8 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">$320.00</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 13, 2024</p>
                            <p class="text-xs text-text-muted">4 days ago</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold">REVISION</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openRequisitionModal('REQ-2024-0010')" class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold hover:bg-blue-600 transition">
                                Review
                            </button>
                        </td>
                    </tr>

                    <!-- Rejected -->
                    <tr class="hover:bg-cream-bg transition">
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">REQ-2024-0009</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-text-dark">Thomas Davis</p>
                            <p class="text-xs text-text-muted">Junior Baker</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Production</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Specialty Ingredients</p>
                            <p class="text-xs text-text-muted">4 items</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-text-dark">$180.00</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-text-dark">Dec 12, 2024</p>
                            <p class="text-xs text-text-muted">5 days ago</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs font-bold">REJECTED</span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openRequisitionModal('REQ-2024-0009')" class="px-3 py-1 bg-caramel text-white text-xs font-semibold hover:bg-caramel-dark transition">
                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t-2 border-border-soft bg-cream-bg">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Showing 1 to 4 of 156 requisitions</p>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Previous</button>
                    <button class="px-3 py-1 bg-caramel text-white">1</button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">2</button>
                    <button class="px-3 py-1 border-2 border-border-soft text-text-muted hover:border-chocolate transition">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Requisition Modal -->
<div id="requisitionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b-2 border-border-soft">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-bold text-text-dark">Requisition Details</h3>
                <button onclick="closeRequisitionModal()" class="text-text-muted hover:text-text-dark">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6" id="modalContent">
            <!-- Content loaded here -->
        </div>
        
        <div class="p-6 border-t-2 border-border-soft bg-cream-bg flex justify-end space-x-3">
            <button onclick="closeRequisitionModal()" class="px-6 py-2 border-2 border-border-soft hover:border-chocolate transition">
                Close
            </button>
            <button class="px-6 py-2 bg-red-500 text-white hover:bg-red-600 transition">
                Reject
            </button>
            <button class="px-6 py-2 bg-green-500 text-white hover:bg-green-600 transition">
                Approve
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
    function openRequisitionModal(requisitionId) {
        document.getElementById('modalContent').innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">REQ ID</p>
                        <p class="text-lg font-bold text-text-dark">${requisitionId}</p>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Status</p>
                        <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold mt-1">PENDING</span>
                    </div>
                    <div class="bg-cream-bg p-4">
                        <p class="text-xs text-text-muted uppercase">Total</p>
                        <p class="text-lg font-bold text-text-dark">$450.00</p>
                    </div>
                </div>

                <div class="border-2 border-border-soft p-6">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Requester Info</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-text-muted">Name</p>
                            <p class="font-semibold">Sarah Martinez - Senior Baker</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Department</p>
                            <p class="font-semibold">Production</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Request Date</p>
                            <p class="font-semibold">Dec 15, 2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-text-muted">Required By</p>
                            <p class="font-semibold">Dec 20, 2024</p>
                        </div>
                    </div>
                </div>

                <div class="border-2 border-border-soft p-6">
                    <h4 class="font-display text-lg font-bold text-text-dark mb-4">Requested Items</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <div>
                                <p class="font-semibold">All-Purpose Flour</p>
                                <p class="text-sm text-text-muted">50kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Qty: 5</p>
                                <p class="text-sm text-text-muted">$150.00</p>
                            </div>
                        </div>
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <div>
                                <p class="font-semibold">Granulated Sugar</p>
                                <p class="text-sm text-text-muted">25kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Qty: 4</p>
                                <p class="text-sm text-text-muted">$200.00</p>
                            </div>
                        </div>
                        <div class="flex justify-between p-3 bg-cream-bg">
                            <div>
                                <p class="font-semibold">Chocolate Chips</p>
                                <p class="text-sm text-text-muted">5kg bags</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Qty: 2</p>
                                <p class="text-sm text-text-muted">$100.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between pt-4 mt-4 border-t-2 border-border-soft">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-2xl font-bold text-caramel">$450.00</span>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('requisitionModal').classList.remove('hidden');
    }

    function closeRequisitionModal() {
        document.getElementById('requisitionModal').classList.add('hidden');
    }

    document.getElementById('requisitionModal').addEventListener('click', function(e) {
        if (e.target === this) closeRequisitionModal();
    });
</script>
@endsection