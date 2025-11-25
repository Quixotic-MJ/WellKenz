@extends('Purchasing.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vendor Masterlist</h1>
            <p class="text-sm text-gray-500 mt-1">Manage supplier profiles, contact details, and payment terms.</p>
            <div class="flex items-center gap-4 mt-2 text-xs text-gray-600">
                <span class="flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    {{ $stats['active_suppliers'] }} Active
                </span>
                <span class="flex items-center">
                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                    {{ $stats['inactive_suppliers'] }} Inactive
                </span>
                <span class="flex items-center">
                    <i class="fas fa-star text-amber-400 mr-1"></i>
                    {{ number_format($stats['avg_rating'], 1) }} Avg Rating
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('exportModal').classList.remove('hidden')" 
                class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export List
            </button>
            <button onclick="openAddSupplierModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Vendor
            </button>
        </div>
    </div>

    {{-- 2. SEARCH & FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <form method="GET" action="{{ route('purchasing.suppliers.index') }}" class="flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="relative w-full lg:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                    placeholder="Search Vendor, Code, Contact, Email, Phone, or TIN...">
            </div>
            
            <div class="flex items-center gap-3 w-full lg:w-auto flex-wrap">
                <select name="status" class="block py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                
                <select name="payment_terms" class="block py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Payment Terms</option>
                    @foreach($paymentTerms as $terms)
                        <option value="{{ $terms }}" {{ request('payment_terms') == $terms ? 'selected' : '' }}>
                            Net {{ $terms }} Days
                        </option>
                    @endforeach
                </select>
                
                <select name="rating" class="block py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Ratings</option>
                    <option value="5" {{ request('rating') === '5' ? 'selected' : '' }}>5 Stars</option>
                    <option value="4" {{ request('rating') === '4' ? 'selected' : '' }}>4+ Stars</option>
                    <option value="3" {{ request('rating') === '3' ? 'selected' : '' }}>3+ Stars</option>
                    <option value="2" {{ request('rating') === '2' ? 'selected' : '' }}>2+ Stars</option>
                    <option value="1" {{ request('rating') === '1' ? 'selected' : '' }}>1+ Star</option>
                </select>
                
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-md hover:bg-chocolate-dark transition">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                
                <a href="{{ route('purchasing.suppliers.index') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>

    {{-- 3. SUPPLIER TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor Profile</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Primary Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terms & Tax</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                                        {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $supplier->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">Code: {{ $supplier->supplier_code }}</div>
                                        @if($supplier->city || $supplier->province)
                                            <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                                <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i> 
                                                {{ $supplier->city }}{{ $supplier->city && $supplier->province ? ', ' : '' }}{{ $supplier->province }}
                                            </div>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium mt-1 {{ $supplier->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supplier->contact_person ?: 'N/A' }}</div>
                                @if($supplier->phone)
                                    <div class="text-xs text-gray-500 mt-1"><i class="fas fa-phone mr-1"></i> {{ $supplier->phone }}</div>
                                @endif
                                @if($supplier->mobile)
                                    <div class="text-xs text-gray-500"><i class="fas fa-mobile-alt mr-1"></i> {{ $supplier->mobile }}</div>
                                @endif
                                @if($supplier->email)
                                    <div class="text-xs text-gray-500"><i class="fas fa-envelope mr-1"></i> {{ $supplier->email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($supplier->address)
                                    <div class="text-xs text-gray-600">{{ Str::limit($supplier->address, 50) }}</div>
                                @else
                                    <div class="text-xs text-gray-400">No address provided</div>
                                @endif
                                @if($supplier->postal_code)
                                    <div class="text-xs text-gray-500 mt-1">Postal: {{ $supplier->postal_code }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($supplier->tax_id)
                                    <div class="text-xs text-gray-500 mb-1">TIN: <span class="font-mono text-gray-700">{{ $supplier->tax_id }}</span></div>
                                @endif
                                @if($supplier->payment_terms)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $supplier->payment_terms <= 15 ? 'bg-red-100 text-red-800 border border-red-200' : ($supplier->payment_terms <= 30 ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                        Net {{ $supplier->payment_terms }} Days
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        No Terms Set
                                    </span>
                                @endif
                                @if($supplier->credit_limit > 0)
                                    <div class="text-xs text-gray-500 mt-1">Credit: ₱{{ number_format($supplier->credit_limit, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($supplier->rating)
                                    <div class="flex justify-center items-center space-x-1 text-amber-400 text-xs mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $supplier->rating)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star text-gray-300"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-[10px] text-gray-400">
                                        @if($supplier->rating >= 5) Excellent
                                        @elseif($supplier->rating >= 4) Very Good
                                        @elseif($supplier->rating >= 3) Good
                                        @elseif($supplier->rating >= 2) Fair
                                        @else Poor
                                        @endif
                                    </span>
                                @else
                                    <span class="text-[10px] text-gray-400">Not Rated</span>
                                @endif
                                @if($supplier->purchase_orders_count > 0)
                                    <div class="text-xs text-blue-600 mt-1">{{ $supplier->purchase_orders_count }} Orders</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editSupplier({{ $supplier->id }})" 
                                        class="text-chocolate hover:text-chocolate-dark bg-white border border-gray-200 hover:bg-gray-50 p-2 rounded transition" 
                                        title="Edit Supplier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleSupplierStatus({{ $supplier->id }})" 
                                        class="bg-white border border-gray-200 hover:bg-gray-50 p-2 rounded transition {{ $supplier->is_active ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-500' }}" 
                                        title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }} Supplier">
                                        <i class="fas {{ $supplier->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                    @php
                                        $hasBatches = isset($supplier->batches[0]) && $supplier->batches[0]->batch_count > 0;
                                        $hasSupplierItems = isset($supplier->supplierItems[0]) && $supplier->supplierItems[0]->supplier_item_count > 0;
                                        $canDelete = $supplier->purchase_orders_count == 0 && !$hasBatches && !$hasSupplierItems;
                                    @endphp
                                    
                                    @if($canDelete)
                                        <button onclick="deleteSupplier({{ $supplier->id }})" 
                                            class="text-red-600 hover:text-red-700 bg-white border border-gray-200 hover:bg-gray-50 p-2 rounded transition" 
                                            title="Delete Supplier">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button disabled 
                                            class="text-gray-300 bg-gray-100 p-2 rounded cursor-not-allowed" 
                                            title="Cannot delete - has @if($supplier->purchase_orders_count > 0)purchase orders @endif@if($hasBatches) @if($supplier->purchase_orders_count > 0)and @endifinventory batches @endif@if($hasSupplierItems) @if($supplier->purchase_orders_count > 0 || $hasBatches)and @endifsupplier items @endif">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-building text-gray-300 text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No suppliers found</h3>
                                    <p class="text-gray-500 mb-4">Get started by adding your first supplier.</p>
                                    <button onclick="openAddSupplierModal()" 
                                        class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition">
                                        <i class="fas fa-plus mr-2"></i> Add New Vendor
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($suppliers->hasPages())
            <div class="px-6 py-3 bg-gray-50 border-t">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ADD/EDIT SUPPLIER MODAL -->
<div id="supplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeSupplierModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <form id="supplierForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="supplierId" value="">
                
                <div class="bg-chocolate px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Add New Vendor</h3>
                    <button type="button" onclick="closeSupplierModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Company Name -->
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Supplier Code -->
                        <div class="sm:col-span-3">
                            <label for="supplier_code" class="block text-sm font-medium text-gray-700">Supplier Code <span class="text-red-500">*</span></label>
                            <input type="text" name="supplier_code" id="supplier_code" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Contact Person -->
                        <div class="sm:col-span-3">
                            <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Phone -->
                        <div class="sm:col-span-2">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Mobile -->
                        <div class="sm:col-span-2">
                            <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile</label>
                            <input type="text" name="mobile" id="mobile"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-2">
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="is_active" id="is_active"
                                class="mt-1 block w-full border-gray-300 bg-white rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <!-- Tax ID -->
                        <div class="sm:col-span-3">
                            <label for="tax_id" class="block text-sm font-medium text-gray-700">Tax ID (TIN)</label>
                            <input type="text" name="tax_id" id="tax_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Payment Terms -->
                        <div class="sm:col-span-3">
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms (Days)</label>
                            <select name="payment_terms" id="payment_terms"
                                class="mt-1 block w-full border-gray-300 bg-white rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="">Select Terms</option>
                                <option value="0">COD</option>
                                <option value="15">Net 15 Days</option>
                                <option value="30">Net 30 Days</option>
                                <option value="45">Net 45 Days</option>
                                <option value="60">Net 60 Days</option>
                                <option value="90">Net 90 Days</option>
                            </select>
                        </div>

                        <!-- Credit Limit -->
                        <div class="sm:col-span-3">
                            <label for="credit_limit" class="block text-sm font-medium text-gray-700">Credit Limit (₱)</label>
                            <input type="number" name="credit_limit" id="credit_limit" min="0" step="0.01"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Rating -->
                        <div class="sm:col-span-3">
                            <label for="rating" class="block text-sm font-medium text-gray-700">Rating (1-5 Stars)</label>
                            <select name="rating" id="rating"
                                class="mt-1 block w-full border-gray-300 bg-white rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                <option value="">No Rating</option>
                                <option value="1">1 Star - Poor</option>
                                <option value="2">2 Stars - Fair</option>
                                <option value="3">3 Stars - Good</option>
                                <option value="4">4 Stars - Very Good</option>
                                <option value="5">5 Stars - Excellent</option>
                            </select>
                        </div>

                        <!-- City -->
                        <div class="sm:col-span-2">
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="city" id="city"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Province -->
                        <div class="sm:col-span-2">
                            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                            <input type="text" name="province" id="province"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Postal Code -->
                        <div class="sm:col-span-2">
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input type="text" name="postal_code" id="postal_code"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-6">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm"></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitBtn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-save mr-2"></i> Save Vendor
                    </button>
                    <button type="button" onclick="closeSupplierModal()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EXPORT MODAL -->
<div id="exportModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('exportModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-download text-green-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Export Supplier List</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">Choose export format for current filtered results.</p>
                            <div class="space-y-2">
                                <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'csv'])) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-file-csv mr-2 text-green-600"></i> Export as CSV
                                </a>
                                <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'excel'])) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Export as Excel
                                </a>
                                <a href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->except('page'), ['export' => 'pdf'])) }}" 
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-file-pdf mr-2 text-red-600"></i> Export as PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Custom modal styles */
.custom-modal {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Confirmation dialog styles */
.confirmation-dialog {
    background: white;
    border-radius: 8px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-width: 400px;
    width: 90%;
}

.confirmation-dialog.warning {
    border-left: 4px solid #f59e0b;
}

.confirmation-dialog.danger {
    border-left: 4px solid #ef4444;
}

.confirmation-dialog.info {
    border-left: 4px solid #3b82f6;
}

/* Loading spinner */
.loading-spinner {
    border: 2px solid #f3f4f6;
    border-top: 2px solid #d97706;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast notifications */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    padding: 16px;
    max-width: 400px;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    border-left: 4px solid #10b981;
}

.toast.error {
    border-left: 4px solid #ef4444;
}

.toast.warning {
    border-left: 4px solid #f59e0b;
}

/* Button loading states */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

/* Form validation styles */
.form-group.error input,
.form-group.error select,
.form-group.error textarea {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.error-message {
    color: #ef4444;
    font-size: 14px;
    margin-top: 4px;
}

/* Enhanced modal animations */
.modal-enter {
    opacity: 0;
    transform: scale(0.9);
}

.modal-enter-active {
    opacity: 1;
    transform: scale(1);
    transition: opacity 300ms, transform 300ms;
}

.modal-exit {
    opacity: 1;
    transform: scale(1);
}

.modal-exit-active {
    opacity: 0;
    transform: scale(0.9);
    transition: opacity 300ms, transform 300ms;
}
</style>
@endpush

@push('scripts')
<script>
// Toast notification system
function showToast(message, type = 'success', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${type === 'success' ? '<i class="fas fa-check-circle text-green-500"></i>' : 
                  type === 'error' ? '<i class="fas fa-exclamation-circle text-red-500"></i>' : 
                  '<i class="fas fa-exclamation-triangle text-yellow-500"></i>'}
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900">${message}</p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                        class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove after duration
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Helper function to validate and convert ID parameters
function validateId(id, entityName = 'ID') {
    const numericId = parseInt(id);
    if (isNaN(numericId) || numericId <= 0) {
        console.error(`Invalid ${entityName}:`, id);
        showToast(`Error: Invalid ${entityName} provided`, 'error');
        return null;
    }
    return numericId;
}

// Professional confirmation dialog
function showConfirmationDialog(title, message, type = 'warning', onConfirm, options = {}) {
    const dialog = document.createElement('div');
    dialog.className = 'fixed inset-0 z-50 overflow-y-auto custom-modal flex items-center justify-center';
    
    const iconMap = {
        warning: 'fa-exclamation-triangle text-yellow-500',
        danger: 'fa-exclamation-circle text-red-500', 
        info: 'fa-info-circle text-blue-500'
    };
    
    const bgColorMap = {
        warning: 'bg-yellow-50',
        danger: 'bg-red-50',
        info: 'bg-blue-50'
    };
    
    const buttonColorMap = {
        warning: 'bg-yellow-600 hover:bg-yellow-700',
        danger: 'bg-red-600 hover:bg-red-700',
        info: 'bg-blue-600 hover:bg-blue-700'
    };
    
    const confirmText = options.confirmText || 'Confirm';
    const cancelText = options.cancelText || 'Cancel';
    
    dialog.innerHTML = `
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" data-role="overlay"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ${bgColorMap[type]} sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas ${iconMap[type]} text-2xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">${title}</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">${message}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" data-role="confirm-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 ${buttonColorMap[type]} text-base font-medium text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    <span class="btn-text">${confirmText}</span>
                </button>
                <button type="button" data-role="cancel-btn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    ${cancelText}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(dialog);

    const confirmBtn = dialog.querySelector('[data-role="confirm-btn"]');
    const cancelBtn = dialog.querySelector('[data-role="cancel-btn"]');
    const overlay = dialog.querySelector('[data-role="overlay"]');

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => confirmAction(confirmBtn, onConfirm));
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => closeConfirmationDialog(cancelBtn));
    }
    if (overlay) {
        overlay.addEventListener('click', () => closeConfirmationDialog(overlay));
    }
    return dialog;
}

function closeConfirmationDialog(element) {
    const dialog = element.closest('.custom-modal');
    if (dialog) {
        dialog.remove();
    }
}

function confirmAction(button, callback) {
    // Add loading state
    const btnText = button.querySelector('.btn-text');
    const originalText = btnText.textContent;
    button.classList.add('btn-loading');
    btnText.innerHTML = '<span class="loading-spinner"></span>Processing...';
    
    // Execute callback after a brief delay for UX
    setTimeout(() => {
        if (typeof callback === 'function') {
            callback();
        }
        closeConfirmationDialog(button);
    }, 500);
}

// Enhanced modal management with animations
function openAddSupplierModal() {
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    
    // Reset form and clear errors
    form.reset();
    clearFormErrors();
    form.action = '{{ route("purchasing.suppliers.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('supplierId').value = '';
    
    // Update modal title and submit button
    document.getElementById('modal-title').textContent = 'Add New Vendor';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save mr-2"></i> Save Vendor';
    
    // Show modal with animation
    modal.classList.remove('hidden');
}

function editSupplier(supplierId) {
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    
    // Validate and convert supplier ID
    const numericId = validateId(supplierId, 'Supplier ID');
    if (!numericId) return;
    
    // Show loading state
    showToast('Loading supplier data...', 'info');
    
    // Load supplier data via AJAX and populate form
    fetch(`/purchasing/api/suppliers/${numericId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            populateForm(data);
            
            // Update form properties
            form.action = `/purchasing/suppliers/${numericId}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('supplierId').value = numericId;
            
            // Update modal title and submit button
            document.getElementById('modal-title').textContent = 'Edit Vendor';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save mr-2"></i> Update Vendor';
            
            // Show modal with animation
            modal.classList.remove('hidden');
            
            showToast('Supplier data loaded successfully', 'success');
        })
        .catch(error => {
            console.error('Error loading supplier data:', error);
            showToast('Error loading supplier data. Please try again.', 'error');
        });
}

function closeSupplierModal() {
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    
    // Add exit animation
    modal.classList.add('hidden');
    
    // Reset form after animation
    setTimeout(() => {
        form.reset();
        clearFormErrors();
    }, 200);
}

function toggleSupplierStatus(supplierId) {
    // Validate and convert supplier ID
    const numericId = validateId(supplierId, 'Supplier ID');
    if (!numericId) return;
    
    showConfirmationDialog(
        'Change Supplier Status',
        'Are you sure you want to change this supplier\'s status? This will affect their ability to receive new purchase orders.',
        'warning',
        () => toggleSupplierStatusConfirmed(numericId),
        {
            confirmText: 'Change Status',
            cancelText: 'Keep Current'
        }
    );
}

function toggleSupplierStatusConfirmed(numericId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/purchasing/suppliers/${numericId}/toggle-status`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PATCH';
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}

function deleteSupplier(supplierId) {
    // Validate and convert supplier ID
    const numericId = validateId(supplierId, 'Supplier ID');
    if (!numericId) return;
    
    showConfirmationDialog(
        'Delete Supplier',
        'Are you sure you want to delete this supplier? This action cannot be undone and will permanently remove all supplier data.',
        'danger',
        () => deleteSupplierConfirmed(numericId),
        {
            confirmText: 'Delete Permanently',
            cancelText: 'Cancel'
        }
    );
}

function deleteSupplierConfirmed(numericId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/purchasing/suppliers/${numericId}`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}

function populateForm(data) {
    document.getElementById('name').value = data.name || '';
    document.getElementById('supplier_code').value = data.supplier_code || '';
    document.getElementById('contact_person').value = data.contact_person || '';
    document.getElementById('email').value = data.email || '';
    document.getElementById('phone').value = data.phone || '';
    document.getElementById('mobile').value = data.mobile || '';
    document.getElementById('is_active').value = data.is_active ? '1' : '0';
    document.getElementById('tax_id').value = data.tax_id || '';
    document.getElementById('payment_terms').value = data.payment_terms || '';
    document.getElementById('credit_limit').value = data.credit_limit || '';
    document.getElementById('rating').value = data.rating || '';
    document.getElementById('city').value = data.city || '';
    document.getElementById('province').value = data.province || '';
    document.getElementById('postal_code').value = data.postal_code || '';
    document.getElementById('address').value = data.address || '';
    document.getElementById('notes').value = data.notes || '';
}

// Form validation and error handling
function validateForm() {
    clearFormErrors();
    let isValid = true;
    
    const requiredFields = ['name', 'supplier_code'];
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        }
    });
    
    const emailField = document.getElementById('email');
    if (emailField.value && !isValidEmail(emailField.value)) {
        showFieldError(emailField, 'Please enter a valid email address');
        isValid = false;
    }
    
    return isValid;
}

function showFieldError(input, message) {
    const formGroup = input.closest('.sm\\:col-span-6') || input.parentElement;
    formGroup.classList.add('error');
    
    let errorDiv = formGroup.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        formGroup.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function clearFormErrors() {
    document.querySelectorAll('.error').forEach(group => {
        group.classList.remove('error');
    });
    document.querySelectorAll('.error-message').forEach(error => {
        error.remove();
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Enhanced form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('supplierForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showToast('Please correct the errors in the form', 'error');
                return;
            }
            
            // Add loading state
            submitBtn.classList.add('btn-loading');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading-spinner"></span>Processing...';
        });
    }
    
    // Auto-generate supplier code when company name is entered
    const nameField = document.getElementById('name');
    const supplierCodeField = document.getElementById('supplier_code');
    
    if (nameField && supplierCodeField) {
        nameField.addEventListener('input', function() {
            const name = this.value;
            
            if (name && !supplierCodeField.value) {
                // Generate supplier code from name
                const code = name
                    .replace(/[^a-zA-Z0-9]/g, '') // Remove special characters
                    .toUpperCase()
                    .substring(0, 8); // Max 8 characters
                const timestamp = Date.now().toString().slice(-4); // Last 4 digits of timestamp
                supplierCodeField.value = code + timestamp;
            }
        });
    }
    
    // Handle success/error messages from session
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
});
</script>
@endpush