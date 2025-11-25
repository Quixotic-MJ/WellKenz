@extends('Inventory.layout.app')

@section('title', 'Return to Vendor (RTV) - Dock Log')

@section('content')
<div class="space-y-6" id="rtv-main-container">

    {{-- 1. HEADER WITH FILTER CONTROLS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-undo-alt text-red-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Return to Vendor (Dock Log)</h1>
                    <p class="text-sm text-gray-500 mt-1">Log items rejected immediately during the receiving process</p>
                </div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Search Box -->
            <div class="relative">
                <input type="text" 
                       id="search-input" 
                       placeholder="Search RTV transactions..." 
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-chocolate focus:border-chocolate w-full sm:w-64 transition-colors"
                       value="{{ request('search') }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            
            <!-- Filter Dropdown -->
            <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors bg-white">
                <option value="all">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Credit</option>
                <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>In Process</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Credit Received</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : ' ' }}>Cancelled</option>
            </select>
            
            <!-- Log New Rejection Button -->
            <button onclick="openNewRtvModal()" 
                    class="flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Log New Rejection
            </button>
        </div>
    </div>

    {{-- 2. SUMMARY STATISTICS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_transactions']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-amber-100 rounded-lg">
                    <i class="fas fa-clock text-amber-600 text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-amber-600">{{ number_format($summary['pending_transactions']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-cog text-blue-600 text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">In Process</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['processed_transactions']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($summary['completed_transactions']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-peso-sign text-red-600 text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Value</p>
                    <p class="text-2xl font-bold text-red-600">₱{{ number_format($summary['total_value'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. RTV LOG TABLE --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">RTV Number</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">PO Reference</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Value</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="rtv-table-body">
                    @forelse($rtvRecords as $rtv)
                    <tr class="hover:bg-gray-50 transition-colors group" data-rtv-id="{{ $rtv->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rtv-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" value="{{ $rtv->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">{{ $rtv->return_date->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $rtv->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm text-blue-700 bg-blue-50 px-3 py-1 rounded-lg border border-blue-200">
                                {{ $rtv->rtv_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $rtv->supplier->name }}</div>
                            <div class="text-xs text-gray-500">{{ $rtv->supplier->supplier_code }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rtv->purchaseOrder)
                                <span class="font-mono text-xs text-purple-700 bg-purple-50 px-2 py-1 rounded border border-purple-200">
                                    {{ $rtv->purchaseOrder->po_number }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="font-semibold">{{ $rtv->rtvItems->count() }}</span> item(s)
                            </div>
                            <div class="text-xs text-gray-500 truncate max-w-32">
                                @foreach($rtv->rtvItems->take(2) as $item)
                                    {{ $item->item->name }}@if(!$loop->last), @endif
                                @endforeach
                                @if($rtv->rtvItems->count() > 2) ... @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $rtv->status_badge['class'] }}">
                                {{ $rtv->status_badge['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-bold text-gray-900">{{ $rtv->formatted_total_value }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- View Details Button -->
                                <button onclick="viewRtvDetails({{ $rtv->id }})" 
                                        class="text-blue-600 hover:text-blue-900 p-2 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors"
                                        title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                
                                <!-- Print Slip Button -->
                                <button onclick="printRtvSlip({{ $rtv->id }})" 
                                        class="text-green-600 hover:text-green-900 p-2 rounded-lg bg-green-50 hover:bg-green-100 transition-colors"
                                        title="Print Slip">
                                    <i class="fas fa-print text-sm"></i>
                                </button>
                                
                                <!-- Edit Button (only for pending) -->
                                @if($rtv->status === 'pending')
                                <button onclick="editRtv({{ $rtv->id }})" 
                                        class="text-amber-600 hover:text-amber-900 p-2 rounded-lg bg-amber-50 hover:bg-amber-100 transition-colors"
                                        title="Edit RTV">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                @endif
                                
                                <!-- Delete Button (only for pending) -->
                                @if($rtv->status === 'pending')
                                <button onclick="deleteRtv({{ $rtv->id }})" 
                                        class="text-red-600 hover:text-red-900 p-2 rounded-lg bg-red-50 hover:bg-red-100 transition-colors"
                                        title="Delete RTV">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="p-4 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-inbox text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No RTV transactions found</h3>
                                <p class="text-gray-500 mb-6 max-w-sm text-center">Start by logging your first return to vendor transaction to track rejected items during receiving.</p>
                                <button onclick="openNewRtvModal()" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <i class="fas fa-plus mr-2"></i> Log New Rejection
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($rtvRecords->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($rtvRecords->previousPageUrl())
                        <a href="{{ $rtvRecords->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">Previous</a>
                    @endif
                    @if($rtvRecords->nextPageUrl())
                        <a href="{{ $rtvRecords->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">Next</a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-semibold">{{ $rtvRecords->firstItem() }}</span> to 
                            <span class="font-semibold">{{ $rtvRecords->lastItem() }}</span> of 
                            <span class="font-semibold">{{ $rtvRecords->total() }}</span> results
                        </p>
                    </div>
                    <div>
                        {{ $rtvRecords->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- 4. BULK ACTIONS BAR (Hidden by default) --}}
    <div id="bulk-actions-bar" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white border border-gray-200 rounded-xl shadow-2xl px-6 py-4 z-50 backdrop-blur-sm bg-opacity-95">
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700 font-medium">
                <span id="selected-count">0</span> item(s) selected
            </span>
            <div class="h-4 w-px bg-gray-300"></div>
            <button onclick="bulkDeleteRtv()" 
                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-semibold rounded-lg text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                <i class="fas fa-trash mr-2"></i> Delete Selected
            </button>
            <button onclick="exportSelectedRtv()" 
                    class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-semibold rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                <i class="fas fa-download mr-2"></i> Export
            </button>
            <button onclick="clearSelection()" 
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

{{-- 5. MODAL CONTAINERS --}}

<!-- New RTV Modal -->
<div id="new-rtv-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeNewRtvModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <form id="new-rtv-form" class="max-h-screen overflow-y-auto">
                <div class="bg-white">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 bg-white bg-opacity-20 rounded-lg mr-3">
                                    <i class="fas fa-plus-circle text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Log New Rejection</h3>
                                    <p class="text-red-100 text-sm">Return items to supplier</p>
                                </div>
                            </div>
                            <button type="button" onclick="closeNewRtvModal()" class="text-white hover:text-gray-200 p-1">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Supplier Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Supplier *</label>
                                <select id="supplier-select" name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors bg-white">
                                    <option value="">Select Supplier...</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Choose the supplier from whom items are being returned</p>
                            </div>
                            
                            <!-- Purchase Order Selection -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Order</label>
                                <select id="po-select" name="purchase_order_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors bg-white">
                                    <option value="">Select PO (Optional)...</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Link to existing purchase order if applicable</p>
                            </div>
                            
                            <!-- Return Date -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Return Date *</label>
                                <input type="date" name="return_date" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Date when items are being returned</p>
                            </div>

                            <!-- Total Value Display -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Estimated Total Value</label>
                                <div class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-50 text-gray-700 font-semibold">
                                    ₱<span id="estimated-total">0.00</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Auto-calculated from items</p>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors" placeholder="Additional notes about this return..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Optional: Add any additional information about this return</p>
                        </div>
                        
                        <!-- Items Section -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">Returned Items</h4>
                                    <p class="text-sm text-gray-500">Add items being returned to supplier</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="openBulkAddModal()" class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-semibold rounded-lg text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                                        <i class="fas fa-list mr-2"></i> Bulk Add Items
                                    </button>
                                    <button type="button" onclick="addRtvItem()" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-semibold rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-plus mr-2"></i> Add Single Item
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Actions Bar -->
                            <div id="quick-actions-bar" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm font-semibold text-blue-800">Quick Actions:</span>
                                        <button onclick="loadPOItems()" class="text-sm text-blue-700 hover:text-blue-900 underline">
                                            <i class="fas fa-download mr-1"></i> Load Items from PO
                                        </button>
                                        <button onclick="clearAllItems()" class="text-sm text-red-700 hover:text-red-900 underline">
                                            <i class="fas fa-trash mr-1"></i> Clear All Items
                                        </button>
                                    </div>
                                    <span class="text-xs text-blue-600" id="current-po-info"></span>
                                </div>
                            </div>
                            
                            <!-- Items Table -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">Item Details</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[100px]">Quantity</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[120px]">Unit Cost</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[100px]">Total Cost</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[150px]">Reason</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[80px]">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rtv-items-container" class="bg-white divide-y divide-gray-200">
                                            <!-- Items will be added dynamically here -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Mobile Card View (hidden on desktop) -->
                                <div id="mobile-items-container" class="hidden sm:hidden">
                                    <!-- Mobile items will be shown here -->
                                </div>
                                
                                <!-- Empty State -->
                                <div id="empty-items-state" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <div class="p-3 bg-gray-100 rounded-full mb-3">
                                            <i class="fas fa-box-open text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 mb-1">No items added yet</h3>
                                        <p class="text-xs text-gray-500 mb-4">Start by adding items to return to supplier</p>
                                        <button type="button" onclick="addRtvItem()" class="inline-flex items-center px-3 py-2 border border-blue-300 text-xs font-semibold rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <i class="fas fa-plus mr-1"></i> Add First Item
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Summary -->
                            <div id="items-summary" class="hidden mt-6 p-4 bg-gray-50 rounded-lg border">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-700">Total Items:</span>
                                    <span class="text-sm font-bold text-gray-900" id="items-count">0</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-semibold text-gray-700">Estimated Total:</span>
                                    <span class="text-lg font-bold text-green-600" id="items-total">₱0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="closeNewRtvModal()" class="w-full sm:w-auto px-6 py-3 border border-gray-300 text-base font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="submit-rtv-btn" class="w-full sm:w-auto px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i> Create RTV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View RTV Details Modal -->
<div id="view-rtv-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewRtvModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-white bg-opacity-20 rounded-lg mr-3">
                                <i class="fas fa-eye text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">RTV Details</h3>
                                <p class="text-blue-100 text-sm">Return to Vendor transaction information</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeViewRtvModal()" class="text-white hover:text-gray-200 p-1">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <div id="rtv-details-content">
                        <!-- Content will be loaded via AJAX -->
                        <div class="flex justify-center items-center py-16">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-chocolate"></div>
                            <span class="ml-4 text-gray-600 text-lg">Loading RTV details...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button type="button" onclick="closeViewRtvModal()" class="px-6 py-3 border border-gray-300 text-base font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Items Modal -->
<div id="bulk-add-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeBulkAddModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-2 bg-white bg-opacity-20 rounded-lg mr-3">
                                <i class="fas fa-list text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Bulk Add Items</h3>
                                <p class="text-green-100 text-sm">Select multiple items to add at once</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeBulkAddModal()" class="text-white hover:text-gray-200 p-1">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Search and Filters -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Search Items</label>
                                <input type="text" id="bulk-search" placeholder="Search by item name or code..." 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors">
                            </div>
                            <div class="sm:w-48">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <select id="bulk-category-filter" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                        <div id="bulk-items-list">
                            <div class="p-8 text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-chocolate mx-auto mb-4"></div>
                                <p class="text-gray-600">Loading items...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div id="bulk-pagination" class="mt-4 hidden">
                        <!-- Pagination will be rendered here -->
                    </div>

                    <!-- Items Info Summary -->
                    <div id="bulk-items-info" class="mt-4 p-3 bg-gray-50 rounded-lg border">
                        <div class="flex justify-between items-center text-sm text-gray-600">
                            <span id="bulk-items-count">Showing 0 items</span>
                            <div class="space-x-2">
                                <button onclick="bulkSelectAll()" class="text-blue-700 hover:text-blue-900 underline">
                                    Select All
                                </button>
                                <button onclick="bulkClearSelection()" class="text-red-700 hover:text-red-900 underline">
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Items Summary -->
                    <div id="bulk-selected-summary" class="hidden mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-blue-800">
                                <span id="bulk-selected-count">0</span> items selected
                            </span>
                            <div class="space-x-2">
                                <button onclick="bulkSelectAll()" class="text-sm text-blue-700 hover:text-blue-900 underline">
                                    Select All
                                </button>
                                <button onclick="bulkClearSelection()" class="text-sm text-red-700 hover:text-red-900 underline">
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                <button type="button" onclick="closeBulkAddModal()" class="w-full sm:w-auto px-6 py-3 border border-gray-300 text-base font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="addSelectedItemsToRtv()" class="w-full sm:w-auto px-6 py-3 border border-transparent text-base font-semibold rounded-lg text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2"></i> Add Selected Items (<span id="bulk-add-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ============================================================================
// RTV JavaScript Functions - Enhanced for WellKenz Bakery ERP
// ============================================================================

// Global variables
let rtvItemsCounter = 0;
let currentRtvData = {};
let isFormSubmitting = false;

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeRtvPage();
});

// Initialize RTV page functionality
function initializeRtvPage() {
    // Set up search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterRtvData();
            }, 500);
        });
    }
    
    // Set up status filter
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterRtvData();
        });
    }
    
    // Set up select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            toggleSelectAll(this.checked);
        });
    }
    
    // Set up form submission
    const newRtvForm = document.getElementById('new-rtv-form');
    if (newRtvForm) {
        newRtvForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!isFormSubmitting) {
                submitNewRtv();
            }
        });
    }
    
    // Load suppliers for dropdown
    loadSuppliers();
}

// Filter RTV data based on search and status
function filterRtvData() {
    const searchTerm = document.getElementById('search-input')?.value || '';
    const status = document.getElementById('status-filter')?.value || 'all';
    
    const params = new URLSearchParams();
    if (searchTerm) params.set('search', searchTerm);
    if (status !== 'all') params.set('status', status);
    
    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

// Enhanced Modal Functions
function openNewRtvModal() {
    document.getElementById('new-rtv-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Add fade-in animation
    setTimeout(() => {
        document.querySelector('#new-rtv-modal .inline-block').classList.add('scale-100');
        document.querySelector('#new-rtv-modal .inline-block').classList.remove('scale-95');
    }, 10);
    
    // Reset form
    resetNewRtvForm();
    
    // Load suppliers
    loadSuppliers();
    
    // Focus first input
    setTimeout(() => {
        document.getElementById('supplier-select')?.focus();
    }, 300);
}

function closeNewRtvModal() {
    document.querySelector('#new-rtv-modal .inline-block').classList.add('scale-95');
    document.querySelector('#new-rtv-modal .inline-block').classList.remove('scale-100');
    
    setTimeout(() => {
        document.getElementById('new-rtv-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        isFormSubmitting = false;
    }, 150);
}

function openViewRtvModal(rtvId) {
    document.getElementById('view-rtv-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Add fade-in animation
    setTimeout(() => {
        document.querySelector('#view-rtv-modal .inline-block').classList.add('scale-100');
        document.querySelector('#view-rtv-modal .inline-block').classList.remove('scale-95');
    }, 10);
    
    // Load RTV details
    loadRtvDetails(rtvId);
}

function closeViewRtvModal() {
    document.querySelector('#view-rtv-modal .inline-block').classList.add('scale-95');
    document.querySelector('#view-rtv-modal .inline-block').classList.remove('scale-100');
    
    setTimeout(() => {
        document.getElementById('view-rtv-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 150);
}

// Load suppliers for dropdown
async function loadSuppliers() {
    const select = document.getElementById('supplier-select');
    if (!select) return;
    
    try {
        const response = await fetch('/inventory/inbound/rtv/suppliers');
        const data = await response.json();
        
        if (data.success) {
            // Keep the first option
            select.innerHTML = '<option value="">Select Supplier...</option>';
            
            data.data.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.id;
                option.textContent = `${supplier.name} (${supplier.supplier_code})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading suppliers:', error);
        showToast('Error loading suppliers', 'error');
    }
}

// Load items for RTV form
async function loadItemsForRtvForm(searchTerm = '') {
    try {
        const url = new URL('/inventory/inbound/rtv/items', window.location.origin);
        if (searchTerm) url.searchParams.set('search', searchTerm);
        
        const response = await fetch(url.toString());
        const data = await response.json();
        
        return data.success ? data.data : [];
    } catch (error) {
        console.error('Error loading items:', error);
        return [];
    }
}

// Enhanced Add RTV item to form
async function addRtvItem(itemData = null) {
    const container = document.getElementById('rtv-items-container');
    if (!container) return;
    
    const itemId = `rtv-item-${++rtvItemsCounter}`;
    
    // Default values or provided data
    const itemName = itemData?.name || '';
    const itemCode = itemData?.item_code || '';
    const itemId_value = itemData?.id || '';
    const quantity = itemData?.quantity || '';
    const unitCost = itemData?.unit_cost || '';
    const unitSymbol = itemData?.unit?.symbol || '';
    
    const itemHtml = `
        <tr class="rtv-item-row hover:bg-gray-50 transition-colors" id="${itemId}">
            <td class="px-4 py-4">
                <div class="flex flex-col space-y-2">
                    <div class="text-xs text-gray-500 font-medium">Item #${rtvItemsCounter}</div>
                    <select name="items[${rtvItemsCounter}][item_id]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors item-select bg-white">
                        <option value="">Select Item...</option>
                        ${itemData ? `<option value="${itemId_value}" selected>${itemName} (${itemCode}) - Stock: ${itemData.current_stock || 'N/A'}</option>` : ''}
                    </select>
                </div>
            </td>
            <td class="px-4 py-4">
                <input type="number" name="items[${rtvItemsCounter}][quantity_returned]" required min="0.001" step="0.001" value="${quantity}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors" placeholder="0.000" onchange="updateItemTotal('${itemId}')">
            </td>
            <td class="px-4 py-4">
                <input type="number" name="items[${rtvItemsCounter}][unit_cost]" required min="0.01" step="0.01" value="${unitCost}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors" placeholder="0.00" onchange="updateItemTotal('${itemId}')">
            </td>
            <td class="px-4 py-4">
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-700 font-semibold text-right" id="item-total-${itemId}">
                    ₱0.00
                </div>
            </td>
            <td class="px-4 py-4">
                <input type="text" name="items[${rtvItemsCounter}][reason]" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-chocolate focus:border-chocolate transition-colors" placeholder="Reason for return..." value="${itemData?.default_reason || ''}">
            </td>
            <td class="px-4 py-4 text-right">
                <button type="button" onclick="removeRtvItem('${itemId}')" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Remove Item">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    
    // Load items for the new dropdown if no item data provided
    if (!itemData) {
        const newSelect = container.querySelector(`#${itemId} .item-select`);
        if (newSelect) {
            await populateItemSelect(newSelect);
        }
    }
    
    // Update totals
    updateItemTotal(itemId);
    updateItemsSummary();
    
    return itemId;
}

// Update item total cost
function updateItemTotal(itemId) {
    const itemElement = document.getElementById(itemId);
    if (!itemElement) return;
    
    const quantity = parseFloat(itemElement.querySelector('input[name*="[quantity_returned]"]').value) || 0;
    const unitCost = parseFloat(itemElement.querySelector('input[name*="[unit_cost]"]').value) || 0;
    const total = quantity * unitCost;
    
    const totalElement = document.getElementById(`item-total-${itemId}`);
    if (totalElement) {
        totalElement.textContent = `₱${total.toFixed(2)}`;
    }
    
    // Update form's estimated total
    updateFormTotal();
    updateItemsSummary();
}

// Update form estimated total
function updateFormTotal() {
    const totalElements = document.querySelectorAll('[id^="item-total-"]');
    let total = 0;
    
    totalElements.forEach(element => {
        const value = parseFloat(element.textContent.replace('₱', '').replace(',', '')) || 0;
        total += value;
    });
    
    const estimatedTotalElement = document.getElementById('estimated-total');
    if (estimatedTotalElement) {
        estimatedTotalElement.textContent = total.toFixed(2);
    }
}

// Update items summary
function updateItemsSummary() {
    const items = document.querySelectorAll('.rtv-item-row');
    const itemsCount = items.length;
    let total = 0;
    
    // Show/hide empty state
    const emptyState = document.getElementById('empty-items-state');
    const tableContainer = document.querySelector('.border.border-gray-200.rounded-lg');
    
    if (itemsCount > 0) {
        if (emptyState) emptyState.style.display = 'none';
        if (tableContainer) tableContainer.classList.remove('hidden');
    } else {
        if (emptyState) emptyState.style.display = 'block';
        if (tableContainer) tableContainer.classList.remove('hidden'); // Keep table visible for better UX
    }
    
    items.forEach(item => {
        const totalElement = item.querySelector('[id^="item-total-"]');
        if (totalElement) {
            const value = parseFloat(totalElement.textContent.replace('₱', '').replace(',', '')) || 0;
            total += value;
        }
    });
    
    const summaryElement = document.getElementById('items-summary');
    const itemsCountElement = document.getElementById('items-count');
    const itemsTotalElement = document.getElementById('items-total');
    
    if (summaryElement && itemsCountElement && itemsTotalElement) {
        if (itemsCount > 0) {
            summaryElement.classList.remove('hidden');
            itemsCountElement.textContent = itemsCount;
            itemsTotalElement.textContent = `₱${total.toFixed(2)}`;
        } else {
            summaryElement.classList.add('hidden');
        }
    }
}

// Remove RTV item from form
function removeRtvItem(itemId) {
    const item = document.getElementById(itemId);
    if (item) {
        item.remove();
        updateItemsSummary();
        updateFormTotal();
    }
}

// Populate item select dropdown
async function populateItemSelect(selectElement, searchTerm = '') {
    try {
        const items = await loadItemsForRtvForm(searchTerm);
        
        // Clear existing options except the first one
        selectElement.innerHTML = '<option value="">Select Item...</option>';
        
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.name} (${item.item_code}) - Stock: ${item.current_stock}`;
            option.dataset.unit = item.unit.symbol;
            option.dataset.cost = item.cost_price;
            selectElement.appendChild(option);
        });
    } catch (error) {
        console.error('Error populating item select:', error);
    }
}

// Reset new RTV form
function resetNewRtvForm() {
    const form = document.getElementById('new-rtv-form');
    if (form) {
        form.reset();
    }
    
    const container = document.getElementById('rtv-items-container');
    if (container) {
        container.innerHTML = '';
    }
    
    rtvItemsCounter = 0;
    updateItemsSummary();
    updateFormTotal();
    
    // Ensure empty state is visible
    const emptyState = document.getElementById('empty-items-state');
    if (emptyState) {
        emptyState.style.display = 'block';
    }
}

// Enhanced Submit new RTV
async function submitNewRtv() {
    if (isFormSubmitting) return;
    
    const submitBtn = document.getElementById('submit-rtv-btn');
    const originalText = submitBtn.innerHTML;
    
    try {
        isFormSubmitting = true;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating RTV...';
        submitBtn.disabled = true;
        
        // Validate form
        const items = document.querySelectorAll('.rtv-item-row');
        if (items.length === 0) {
            showToast('Please add at least one item to return', 'warning');
            return;
        }
        
        const formData = new FormData(document.getElementById('new-rtv-form'));
        
        const response = await fetch('/inventory/inbound/rtv', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message || 'RTV transaction created successfully', 'success');
            closeNewRtvModal();
            
            // Reload page to show new RTV
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Error creating RTV', 'error');
        }
        
    } catch (error) {
        console.error('Error creating RTV:', error);
        showToast('Error creating RTV transaction', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        isFormSubmitting = false;
    }
}

// Load RTV details for viewing
async function loadRtvDetails(rtvId) {
    const contentDiv = document.getElementById('rtv-details-content');
    
    try {
        const response = await fetch(`/inventory/inbound/rtv/${rtvId}/details`);
        const data = await response.json();
        
        if (data.success) {
            const rtv = data.data;
            
            contentDiv.innerHTML = `
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="border-b border-gray-200 pb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">${rtv.rtv_number}</h3>
                                <p class="text-gray-600 mt-1">Return to Vendor Details</p>
                            </div>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ${rtv.status_badge.class}">
                                ${rtv.status_badge.label}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Supplier Information</p>
                                <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                    <p class="text-lg font-semibold text-gray-900">${rtv.supplier_name}</p>
                                    <p class="text-sm text-gray-600">${rtv.supplier_code}</p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Return Date</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">${rtv.return_date_formatted}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Purchase Order</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">${rtv.po_number || 'N/A'}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Created By</p>
                                <p class="text-lg font-semibold text-gray-900 mt-1">${rtv.created_by}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Returned Items (${rtv.total_items})</h4>
                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item Details</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Cost</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    ${rtv.items.map(item => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">${item.item_name}</p>
                                                    <p class="text-xs text-gray-500">${item.item_code}</p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-900">${item.formatted_quantity}</td>
                                            <td class="px-4 py-4 text-sm text-gray-900">${item.formatted_unit_cost}</td>
                                            <td class="px-4 py-4 text-sm font-semibold text-gray-900">${item.formatted_total_cost}</td>
                                            <td class="px-4 py-4 text-sm text-gray-900 max-w-xs">${item.reason}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Total -->
                        <div class="mt-6 flex justify-end">
                            <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 rounded-lg border border-green-200">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm font-semibold text-green-800">Total Return Value:</span>
                                    <span class="text-2xl font-bold text-green-700">${rtv.formatted_total_value}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    ${rtv.notes ? `
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Notes</p>
                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-sm text-gray-900">${rtv.notes}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            contentDiv.innerHTML = `
                <div class="text-center py-12">
                    <div class="p-4 bg-red-100 rounded-full inline-block mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading RTV Details</h3>
                    <p class="text-gray-600">${data.message || 'Failed to load RTV details'}</p>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('Error loading RTV details:', error);
        contentDiv.innerHTML = `
            <div class="text-center py-12">
                <div class="p-4 bg-red-100 rounded-full inline-block mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading RTV Details</h3>
                <p class="text-gray-600">An unexpected error occurred while loading the details.</p>
            </div>
        `;
    }
}

// Enhanced Action Functions
function viewRtvDetails(rtvId) {
    openViewRtvModal(rtvId);
}

function printRtvSlip(rtvId) {
    window.open(`/inventory/inbound/rtv/${rtvId}/print`, '_blank');
}

function editRtv(rtvId) {
    showToast('Edit functionality coming soon!', 'info');
}

function deleteRtv(rtvId) {
    if (confirm('Are you sure you want to delete this RTV transaction? This action cannot be undone.')) {
        performDeleteRtv(rtvId);
    }
}

async function performDeleteRtv(rtvId) {
    try {
        const response = await fetch(`/inventory/inbound/rtv/${rtvId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            // Remove row from table
            const row = document.querySelector(`tr[data-rtv-id="${rtvId}"]`);
            if (row) {
                row.remove();
            }
        } else {
            showToast(data.message || 'Error deleting RTV', 'error');
        }
        
    } catch (error) {
        console.error('Error deleting RTV:', error);
        showToast('Error deleting RTV transaction', 'error');
    }
}

// Enhanced Selection Functions
function toggleSelectAll(checked) {
    const checkboxes = document.querySelectorAll('.rtv-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
    
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const selectedCheckboxes = document.querySelectorAll('.rtv-checkbox:checked');
    const bulkBar = document.getElementById('bulk-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    
    const count = selectedCheckboxes.length;
    
    if (count > 0) {
        bulkBar.classList.remove('hidden');
        selectedCountSpan.textContent = count;
    } else {
        bulkBar.classList.add('hidden');
    }
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.rtv-checkbox, #select-all');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateBulkActionsBar();
}

function bulkDeleteRtv() {
    const selectedCheckboxes = document.querySelectorAll('.rtv-checkbox:checked');
    const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        showToast('No RTV transactions selected', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${ids.length} RTV transaction(s)? This action cannot be undone.`)) {
        // Implement bulk delete logic here
        showToast('Bulk delete functionality coming soon!', 'info');
    }
}

function exportSelectedRtv() {
    const selectedCheckboxes = document.querySelectorAll('.rtv-checkbox:checked');
    const ids = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        showToast('No RTV transactions selected', 'warning');
        return;
    }
    
    // Implement export functionality here
    showToast('Export functionality coming soon!', 'info');
}

// Enhanced Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColors = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transform transition-all duration-300 translate-x-full ${bgColors[type] || 'bg-gray-500'} text-white max-w-md`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="${icons[type] || icons.info} mr-3"></i>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 5000);
}

// ============================================================================
// BULK OPERATIONS AND ENHANCED FUNCTIONALITY
// ============================================================================

// Load Purchase Order items automatically
async function loadPOItems() {
    const poSelect = document.getElementById('po-select');
    if (!poSelect || !poSelect.value) {
        showToast('Please select a Purchase Order first', 'warning');
        return;
    }

    const poId = poSelect.value;
    
    try {
        const response = await fetch(`/inventory/inbound/rtv/purchase-orders/${poId}/items`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            // Clear existing items
            clearAllItems();
            
            // Add all PO items
            let addedCount = 0;
            for (const poItem of data.data) {
                await addRtvItem({
                    id: poItem.item.id,
                    name: poItem.item.name,
                    item_code: poItem.item.item_code,
                    unit_cost: poItem.unit_price,
                    current_stock: poItem.current_stock || 'N/A',
                    unit: poItem.item.unit,
                    quantity: poItem.quantity_ordered,
                    default_reason: `Return from PO ${poItem.po_number}`
                });
                addedCount++;
            }
            
            showToast(`Added ${addedCount} items from PO ${poItem.po_number}`, 'success');
            
            // Show current PO info
            const currentPoInfo = document.getElementById('current-po-info');
            if (currentPoInfo) {
                currentPoInfo.textContent = `PO: ${data.data[0].po_number}`;
            }
            
        } else {
            showToast('No items found for this Purchase Order', 'warning');
        }
        
    } catch (error) {
        console.error('Error loading PO items:', error);
        showToast('Error loading Purchase Order items', 'error');
    }
}

// Clear all items
function clearAllItems() {
    const container = document.getElementById('rtv-items-container');
    if (container) {
        container.innerHTML = '';
    }
    rtvItemsCounter = 0;
    updateItemsSummary();
    updateFormTotal();
}

// Bulk Add Items Modal Functions
function openBulkAddModal() {
    document.getElementById('bulk-add-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Add fade-in animation
    setTimeout(() => {
        document.querySelector('#bulk-add-modal .inline-block').classList.add('scale-100');
        document.querySelector('#bulk-add-modal .inline-block').classList.remove('scale-95');
    }, 10);
    
    // Reset pagination variables
    currentPage = 1;
    currentSearchTerm = '';
    currentCategoryId = '';
    
    // Clear search and filter inputs
    const searchInput = document.getElementById('bulk-search');
    const categorySelect = document.getElementById('bulk-category-filter');
    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = '';
    
    // Load items for bulk selection
    loadBulkItems();
}

function closeBulkAddModal() {
    document.querySelector('#bulk-add-modal .inline-block').classList.add('scale-95');
    document.querySelector('#bulk-add-modal .inline-block').classList.remove('scale-100');
    
    setTimeout(() => {
        document.getElementById('bulk-add-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 150);
}

// Global variables for pagination
let currentPage = 1;
let itemsPerPage = 20;
let totalPages = 1;
let currentSearchTerm = '';
let currentCategoryId = '';

// Load items for bulk selection with pagination
async function loadBulkItems(searchTerm = '', categoryId = '', page = 1) {
    const itemsList = document.getElementById('bulk-items-list');
    
    // Update global variables
    currentSearchTerm = searchTerm;
    currentCategoryId = categoryId;
    currentPage = page;
    
    try {
        // Show loading state
        showLoadingState(itemsList);
        
        const url = new URL('/inventory/inbound/rtv/items', window.location.origin);
        if (searchTerm) url.searchParams.set('search', searchTerm);
        if (categoryId) url.searchParams.set('category_id', categoryId);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', itemsPerPage);
        
        const response = await fetch(url.toString());
        const data = await response.json();
        
        if (data.success) {
            const items = data.data;
            totalPages = Math.ceil(data.total / itemsPerPage);
            renderBulkItemsList(items);
            renderPagination();
            updateItemsInfo(data.total, items.length);
            loadCategoriesForFilter();
        } else {
            showErrorState(itemsList, 'No items found');
            updateItemsInfo(0, 0);
        }
        
    } catch (error) {
        console.error('Error loading bulk items:', error);
        showErrorState(itemsList, 'Error loading items');
    }
}

// Show loading state
function showLoadingState(container) {
    container.innerHTML = `
        <div class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-chocolate mx-auto mb-4"></div>
            <p class="text-gray-600">Loading items...</p>
        </div>
    `;
}

// Show error state
function showErrorState(container, message) {
    container.innerHTML = `
        <div class="p-8 text-center">
            <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
            <p class="text-red-600">${message}</p>
        </div>
    `;
}

// Render pagination controls
function renderPagination() {
    const paginationContainer = document.getElementById('bulk-pagination');
    if (!paginationContainer || totalPages <= 1) {
        if (paginationContainer) paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'block';
    
    let paginationHtml = '<div class="flex items-center justify-center space-x-2">';
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `
            <button onclick="loadBulkItems('${currentSearchTerm}', '${currentCategoryId}', ${currentPage - 1})" 
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </button>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        paginationHtml += `<button onclick="loadBulkItems('${currentSearchTerm}', '${currentCategoryId}', 1)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">1</button>`;
        if (startPage > 2) {
            paginationHtml += '<span class="px-2 text-gray-500">...</span>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'bg-chocolate text-white' : 'border border-gray-300 hover:bg-gray-50';
        paginationHtml += `<button onclick="loadBulkItems('${currentSearchTerm}', '${currentCategoryId}', ${i})" class="px-3 py-2 text-sm rounded-lg ${activeClass}">${i}</button>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += '<span class="px-2 text-gray-500">...</span>';
        }
        paginationHtml += `<button onclick="loadBulkItems('${currentSearchTerm}', '${currentCategoryId}', ${totalPages})" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">${totalPages}</button>`;
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `
            <button onclick="loadBulkItems('${currentSearchTerm}', '${currentCategoryId}', ${currentPage + 1})" 
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </button>
        `;
    }
    
    paginationHtml += '</div>';
    paginationContainer.innerHTML = paginationHtml;
}

// Update items info display
function updateItemsInfo(totalItems, showingItems) {
    const infoElement = document.getElementById('bulk-items-count');
    if (infoElement) {
        if (totalItems === 0) {
            infoElement.textContent = 'No items found';
        } else {
            const startItem = (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalItems);
            infoElement.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} items`;
        }
    }
}

// Render bulk items list
function renderBulkItemsList(items) {
    const itemsList = document.getElementById('bulk-items-list');
    
    if (items.length === 0) {
        itemsList.innerHTML = `
            <div class="p-8 text-center">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">No items match your search criteria</p>
            </div>
        `;
        return;
    }
    
    const itemsHtml = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 w-12">
                        <input type="checkbox" id="bulk-select-all" onchange="toggleBulkSelectAll()" class="rounded border-gray-300 text-chocolate focus:ring-chocolate">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Current Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                ${items.map(item => `
                    <tr class="hover:bg-gray-50 bulk-item-row">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="bulk-item-checkbox rounded border-gray-300 text-chocolate focus:ring-chocolate" 
                                   value="${item.id}" 
                                   data-item='${JSON.stringify(item)}'
                                   onchange="updateBulkSelection()">
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                                <p class="text-xs text-gray-500">${item.item_code}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.current_stock || 'N/A'}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">₱${parseFloat(item.cost_price || 0).toFixed(2)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.unit?.symbol || 'pcs'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    itemsList.innerHTML = itemsHtml;
    updateBulkSelection();
}

// Load categories for filter
async function loadCategoriesForFilter() {
    const categorySelect = document.getElementById('bulk-category-filter');
    if (!categorySelect) return;
    
    try {
        const response = await fetch('/inventory/inbound/rtv/categories');
        const data = await response.json();
        
        if (data.success) {
            categorySelect.innerHTML = '<option value="">All Categories</option>';
            
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = `${category.name} (${category.items_count} items)`;
                categorySelect.appendChild(option);
            });
        } else {
            console.error('Failed to load categories:', data.message);
            // Fallback to empty state
            categorySelect.innerHTML = '<option value="">All Categories</option>';
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        // Fallback to empty state
        categorySelect.innerHTML = '<option value="">All Categories</option>';
    }
}

// Toggle bulk select all
function toggleBulkSelectAll() {
    const selectAllCheckbox = document.getElementById('bulk-select-all');
    const itemCheckboxes = document.querySelectorAll('.bulk-item-checkbox');
    
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkSelection();
}

// Update bulk selection
function updateBulkSelection() {
    const selectedCheckboxes = document.querySelectorAll('.bulk-item-checkbox:checked');
    const selectedCount = selectedCheckboxes.length;
    
    const summaryElement = document.getElementById('bulk-selected-summary');
    const countElement = document.getElementById('bulk-selected-count');
    const addCountElement = document.getElementById('bulk-add-count');
    
    if (selectedCount > 0) {
        summaryElement.classList.remove('hidden');
        countElement.textContent = selectedCount;
        addCountElement.textContent = selectedCount;
    } else {
        summaryElement.classList.add('hidden');
    }
    
    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('bulk-select-all');
    const totalCheckboxes = document.querySelectorAll('.bulk-item-checkbox').length;
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = selectedCount === totalCheckboxes && totalCheckboxes > 0;
        selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCheckboxes;
    }
}

// Bulk select all items
function bulkSelectAll() {
    const itemCheckboxes = document.querySelectorAll('.bulk-item-checkbox');
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateBulkSelection();
}

// Clear bulk selection
function bulkClearSelection() {
    const itemCheckboxes = document.querySelectorAll('.bulk-item-checkbox');
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkSelection();
}

// Add selected items to RTV
function addSelectedItemsToRtv() {
    const selectedCheckboxes = document.querySelectorAll('.bulk-item-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        showToast('Please select at least one item', 'warning');
        return;
    }
    
    let addedCount = 0;
    selectedCheckboxes.forEach(checkbox => {
        const itemData = JSON.parse(checkbox.dataset.item);
        addRtvItem({
            id: itemData.id,
            name: itemData.name,
            item_code: itemData.item_code,
            unit_cost: itemData.cost_price,
            current_stock: itemData.current_stock,
            unit: itemData.unit
            // Removed default_reason to allow users to enter proper reason
        });
        addedCount++;
    });
    
    showToast(`Added ${addedCount} items to RTV`, 'success');
    closeBulkAddModal();
}

// Show/hide quick actions based on PO selection
function updateQuickActionsBar() {
    const poSelect = document.getElementById('po-select');
    const quickActionsBar = document.getElementById('quick-actions-bar');
    
    if (poSelect && poSelect.value) {
        quickActionsBar.classList.remove('hidden');
    } else {
        quickActionsBar.classList.add('hidden');
    }
}

// Event delegation for dynamic elements
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('rtv-checkbox')) {
        updateBulkActionsBar();
    }
    
    // Handle PO selection change
    if (e.target.id === 'po-select') {
        updateQuickActionsBar();
    }
    
    // Handle bulk search and filter
    if (e.target.id === 'bulk-search' || e.target.id === 'bulk-category-filter') {
        clearTimeout(window.bulkSearchTimeout);
        window.bulkSearchTimeout = setTimeout(() => {
            const searchTerm = document.getElementById('bulk-search').value;
            const categoryId = document.getElementById('bulk-category-filter').value;
            loadBulkItems(searchTerm, categoryId, 1); // Reset to page 1 when searching/filtering
        }, 500);
    }
});

// Prevent form submission on Enter key in text inputs
document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'INPUT' && e.target.type !== 'submit' && e.key === 'Enter') {
        e.preventDefault();
    }
});

</script>
@endpush